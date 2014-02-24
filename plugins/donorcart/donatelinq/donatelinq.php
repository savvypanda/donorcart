<?php defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php');

class plgDonorcartDonatelinq extends JPluginDonorcart {
	protected $_name = 'donatelinq';

	private $cc_fees_sku = 'DNLQ-PROCESSING-FEE';
	private $cc_fees_name = 'Processing Fee';

	/*
	 * Displays the payment form (assuming the user selects this payment method)
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 *
	 * @return string The HTML for the payment form
	 */
	public function onDisplayPaymentForm($order, $params) {
		if(!$this->isActive()) return;
		$path = JPluginHelper::getLayoutPath('donorcart', 'donatelinq', 'paymentform');
		$contents = '';
		if(file_exists($path)) {
			ob_start();
			include $path;
			$contents = ob_get_clean();
		}
		return $contents;
	}


	/*
	 * Processes the submitted order and saves the submitted payment info.
	 * This function is fired PRIOR to confirming the order - DO NOT submit the payment here
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 * @param string &$payment_name The name of the payment plugin that was selected for this order
	 *
	 * @return null|boolean|array Null if this payment plugin was not selected
	 * 							  False if the payment form was filled out incorrectly.
	 * 						 	  An array containing the payment details if this payment plugin was selected and the form was filled out correctly.
	 */
	public function onSubmitOrder($order, $params, $payment_name) {
		if(!empty($payment_name) && $payment_name != $this->getName()) return;
		$payment_name=$this->getName();
		$cc_fees_option = $this->params->get('cc_fees_option');
		if($cc_fees_option==0) {
			$pay_cc_fees=0;
		} elseif($cc_fees_option==2) {
			$pay_cc_fees=1;
		} else {
			$pay_cc_fees = JRequest::getInt('pay_cc_fees',0);
		}
		$current_fee_item = false;
		foreach($order->cart->items as $item_id => $item) {
			if($item->sku==$this->cc_fees_sku && $item->name==$this->cc_fees_name) {
				$current_fee_item = $item_id;
			}
		}
		if($pay_cc_fees) {
			$cc_fees_total = $this->_calc_cc_processing_fee($order);
			if(!$current_fee_item) {
				FOFModel::getAnInstance('carts','DonorcartModel')->addItemToCart($this->cc_fees_sku, $this->cc_fees_name, $cc_fees_total, '1', '', '', false, true);
			} elseif($order->cart->items[$current_fee_item]->price != $cc_fees_total) {
				FOFModel::getAnInstance('carts','DonorcartModel')->updateItemInCart($current_fee_item, null, null, $cc_fees_total, '1', null, null, true);
			}
		} else {
			if($current_fee_item) {
				FOFModel::getAnInstance('carts','DonorcartModel')->removeItemFromCart($current_fee_item, true);
			}
		}
		$recurring_frequency = JRequest::getString('selFrequency','One Time');
		$infohash = array('selFrequency'=>$recurring_frequency, 'pay_cc_fees'=>$pay_cc_fees);
		return array(
			'payment_type' => $payment_name,
			'infohash' => json_encode($infohash)
		);
	}

	/*
	 * Processes the submitted order - performing any necessary actions to submit the order to the payment gateway
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 * @param boolean &$is_valid Whether or not the order has passed all validation
	 *
	 * @return null|boolean|string NULL if this payment plugin was not selected
	 * 							   True if the payment was completed successfully
	 * 						  	   The HTML to redirect the user to the payment gateway if more details must be collected (eg: credit card info, etc...)
	 */
	public function onConfirmOrder($order, $params, $is_valid) {
		if($order->payment_name != $this->getName() || !is_object($order->payment) || !$is_valid) return;

		if(!$order->order_total):
			return 'No payment may be made for an empty order. Please add something to your cart and try checking out again.';
		else:
			$ssl = $params->get('ssl_mode')?1:-1;
			$return_url = 'index.php?option=com_donorcart&task=postback&oid='.$order->donorcart_order_id;
			if($order->user_id) $return_url .= '&uid='.$order->user_id;
			$return_url = JRoute::_($return_url,true,$ssl);
			$payment_info = json_decode($order->payment->infohash,true);

			$path = JPluginHelper::getLayoutPath('donorcart', 'donatelinq', 'submitform');
			$contents = '';
			if(file_exists($path)) {
				ob_start();
				include $path;
				$contents = ob_get_clean();
			}
			return $contents;
		endif;
	}


	/*
	 * Displays the payment information after it has been entered
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 * @param string $payment_name The name of the payment plugin that was selected for this order
	 *
	 * @return string The HTML containing the details of the payment to be displayed on the user's screen.
	 */
	public function onDisplayPaymentInfo($order, $params, $payment_name) {
		if($payment_name != $this->getName()) return;
		$path = JPluginHelper::getLayoutPath('donorcart', 'donatelinq', 'paymentinfo');
		$contents = '';
		if(file_exists($path)) {
			ob_start();
			include $path;
			$contents = ob_get_clean();
		}
		return $contents;
	}


	/*
	 * Code to validate a request when returning from the payment gateway
	 *
	 * @param string &$plugin_validated The payment plugin that has already validated this request.
	 * 								   Empty if it has not been validated by any other payment plugins yet.
	 *
	 * @return boolean True if the postback response can be validated by the current plugin.
	 */
	public function onBeforePostback($plugin_validated) {
		if($plugin_validated || !$this->isActive()) return;
		if(JRequest::getString('pnref',false)) {
			$plugin_validated = $this->getName();
			return true;
		}
	}

	/*
	 * Code to process a request when returning from the payment gateway
	 *
	 * @param boolean &$is_valid Whether or not the payment information in the current request is valid
	 * @param string &$plugin_validated The payment plugin that has already validated this request.
	 */
	public function onPostback($is_valid, $plugin_validated) {
		if($plugin_validated != $this->getName() || !$is_valid) return;

		$status = JRequest::getInt('status',0);
		if($status != 0) {
			$is_valid = false;
			//transaction was not successful. show error message
			switch ($status) {
				case 3:
					$reason = "Failed Conversion.";
					break;
				case 7:
					$reason = "Invalid CVS Number.";
					break;
				case 9:
					$reason = "Uncollected Funds.";
					break;
				case 10:
					$reason = "Customer Refused.";
					break;
				case 12:
					$reason = "General Decline. Please check your billing address and other billing information.";
					break;
				case 13:
					$reason = "Bank Not Authorized.";
					break;
				case 14:
					$reason = "General Decline. Please check your billing address and other billing information.";
					break;
				case 19:
					$reason = "Original Trx Not Found.";
					break;
				case 23:
					$reason = "Invalid Account Number.";
					break;
				case 24:
					$reason = "Invalid Expiration Date.";
					break;
				case 26:
					$reason = "Declined. Reason not given. Please contact your Credit Card company to ensure card is active.";
					break;
				case 99:
					$reason = "Declined. Reason not given. Please contact your Credit Card company to ensure card is active.";
					break;
				case 103:
					$reason = "Slow host/timeout.";
					break;
				case ($status >= 1000):
					$reason = "Communication Error.";
					break;
				default:
					$reason = isset($post['message'])?$post['message']:'unknown reason';
			}
			return "<p>Your credit card transaction was declined with the following error: $reason</p>";
		}

		//finally, let's the get the order details and save the payment information
		$order_id = JRequest::getInt('oid',false);
		if(!$order_id) {
			$is_valid = false;
			return '<p>Unable to identify order for payment. Please contact the webmaster for assistance.</p>';
		}

		$ordermodel = FOFModel::getTmpInstance('orders','DonorcartModel');
		$order = $ordermodel->getItem($order_id);
		if(!is_object($order) || !$order->order_total || !is_object($order->cart) || !is_array($order->cart->items) || !is_object($order->payment)) return false;
		if($order->payment_name != $this->getName() || $order->status=='complete') {
			$is_valid = false;
			return '<p>Unable to update payment details on your order. Payment has already been completed. Please contact your administrator for assistance.</p>';
		}

		$external_reference_id = JRequest::getInt('pnref',false);
		if(!$external_reference_id || !is_numeric($external_reference_id)) {
			$is_valid = false;
			return '<p>Invalid reference from payment gateway. Please contact your administrator for assistance</p>';
		}
		$user_id = JRequest::getInt('uid',$order->user_id);

		//sanity check. Make sure that the user on the order is the same as the user in the request
		if(!empty($order->user_id) && $order->user_id != $user_id) {
			$is_valid = false;
			return '<p>403: User does not match order. If you are receiving this message in error, please contact your administrator for assistance.</p>';
		}

		//second sanity check. Make sure that the external reference has not already been used.
		$db = JFactory::getDbo();
		$sql = 'SELECT * FROM #__donorcart_payments WHERE payment_type="donatelinq" AND external_reference='.$db->quote($external_reference_id);
		$db->setQuery($sql);
		$db->query();
		if($db->getNumRows() != 0) {
			$is_valid = false;
			return '<p>Error: This payment reference has already been used. Please contact your administrator for assistance.</p>';
		}

		$paymentmodel = FOFModel::getAnInstance('payment','DonorcartModel');
		$paymentinfo = json_decode($order->payment->infohash,true);
		$paymentinfo['paytype'] = JRequest::getString('paytype','');
		$paymentinfo['lastfour'] = JRequest::getString('lastfour','');
		$paymentinfo['name_on_account'] = JRequest::getString('name_on_account','');
		$paymentinfo['Email'] = JRequest::getString('Email','');
		$paymentinfo['Special_Instructions'] = JRequest::getString('Special_Instructions','');


		//$line_items = JRequest::getString('Custom2',null);  //get the cart items that we passed through a custom string to Cashlinq when we submitted payment.  We have to do this becuase Cashlinq does not pass this information back by default.
		//$note = JRequest::getString('Custom3','');
		//$post_data["line_items"] = $line_items;

		$paymentdata = array(
			'donorcart_payment_id' => $order->payment->donorcart_payment_id,
			'external_reference' => $external_reference_id,
			'payment_type' => 'donatelinq',
			'infohash' => json_encode($paymentinfo)
		);
		$paymentmodel->save($paymentdata);

		//start collecting the order information to save
		$orderdata = array(
			'donorcart_order_id' => $order_id,
			'status' => 'complete',
		);
		if($user_id) $orderdata['user_id'] = $user_id;
		if($email = JRequest::getString('Email', false)) $orderdata['email'] = $email;
		if($special_instr = JRequest::getString('Special_Instructions',false)) $orderdata['special_instr'] = $special_instr;
		/* if(is_callable(array('JApplication', 'getHash'))) {
			$orderdata['viewtoken'] = JApplication::getHash($order->donorcart_order_id.JSession::getFormToken());
		} else {
			$orderdata['viewtoken'] = md5(JFactory::getApplication()->get('secret').$order->donorcart_order_id.JSession::getFormToken());
		} */


		//now we will save the billing address if appropriate
		if($user_id && $order->billing_address_id) {
			$addressmodel = FOFModel::getTmpInstance('addresses','DonorcartModel');
			$addressdata = array_filter(array(
				'donorcart_address_id' => $order->billing_address_id,
				'user_id' => $user_id,
				'first_name' => JRequest::getString('FirstName',''),
				'last_name' => JRequest::getString('LastName',''),
				'address1' => JRequest::getString('Address1',''),
				'address2' => JRequest::getString('Address2',''),
				'city' => JRequest::getString('City',''),
				'state' => JRequest::getString('St',''),
				'zip' => JRequest::getString('Zip',''),
				'country' => JRequest::getString('Country',''),
				'locked' => 1
			));
			if($order->billing_address_id) {
				if(!$order->billing_address->locked) {
					$addressdata = array_merge((array)$order->billing_address, $addressdata);
					$addressmodel->save($addressdata);
				}
			}
		}

		//finally, save the order
		$ordermodel->save($orderdata);
	}

	private function _get_recurring_options() {
		$recurring_options = array();
		if($this->params->get('recur_twoweeks',false)) $recurring_options['2 Weeks'] = '2 Weeks';
		if($this->params->get('recur_weekly',false)) $recurring_options['Weekly'] = 'Weekly';
		if($this->params->get('recur_fourweeks',false)) $recurring_options['4 Weeks'] = '4 Weeks';
		if($this->params->get('recur_monthly',false)) $recurring_options['Monthly'] = 'Monthly';
		if($this->params->get('recur_querterly',false)) $recurring_options['Querterly'] = 'Querterly';
		if($this->params->get('recur_semiannual',false)) $recurring_options['Semi-Annual'] = 'Semi-Annual';
		if($this->params->get('recur_yearly',false)) $recurring_options['Yearly'] = 'Yearly';
		return $recurring_options;
	}

	private function _calc_cc_processing_fee($order) {
		if(!is_object($order->cart) || empty($order->cart->items)) return 0;
		$cc_fees_amount = $this->params->get('cc_fees_amount',0);
		if(!is_numeric($cc_fees_amount)) return 0;
		$cc_fees_amount = round($cc_fees_amount,2)/100;
		$cart_subtotal = 0;
		foreach($order->cart->items as $item) {
			if($item->sku!=$this->cc_fees_sku && $item->name!=$this->cc_fees_name) {
				$cart_subtotal += ($item->qty * $item->price);
			}
		}
		return round($cart_subtotal*$cc_fees_amount,2);
	}
}