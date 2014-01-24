<?php defined('_JEXEC') or die('Restricted Access');

class plgDonorcartDonatelinq extends JPlugin {
	public function onGetDonorcartPreferenceOverrides($order, $params) {
		if($this->params->get('active')) {
			$onestep_checkout = $params->get('onestep_checkout');
			if($onestep_checkout) $params->set('shipto_option',0);
			if($onestep_checkout) $params->set('billto_option',0);
			$params->set('review_option',0);
			$params->set('ignore_form_for_payment',1);
		}
	}

	public function onDisplayPaymentForm($order, $params) {
		if($this->params->get('active')) {
			return $this->_displayDonateQ($order, $params);
		}
	}

	public function onReplaceGuestCheckout($order, $params) {
		if($this->params->get('active')) {
			return $this->_displayDonateQ($order, $params);
		}
	}

	public function onDisplayPaymentInfo($order) {
		$payment_id = $order->payment_id;
		if($payment_id) {
			$paymentmodel = FOFModel::getTmpInstance('payment','DonorcartModel');
			$payment = $paymentmodel->getItem($payment_id);
			if($payment->payment_type='donatelinq') {
				$payment_info = json_decode($payment->infohash);

				$cc_charge_text = '';
				$paymenttext = <<<HEREDOC
<div>
	<p><strong>Payment Type</strong>: Credit Card</p>
	<p><strong>Payment Amount</strong>: {$order->order_total}</p>
	{$cc_charge_text}
</div>
HEREDOC;
				return $paymenttext;
			}
		}
	}

	public function onBeforePostback($order, $plugin_validated) {
		if($plugin_validated || !$this->params->get('active')) return;
		if(JRequest::get('pnref',false)) {
			$plugin_validated = 'donatelinq';
/*
$logfile = JPATH_BASE.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'donorcart_donatelinq.log';
$handle = fopen($logfile, 'a');
$text = "\n\n[".date('Y-m-d H:i:s')."]  POST:\n".var_export($_POST,true)."\n";
fwrite($handle, $text);
fclose($handle);
*/
			return true;
		}
		return false;
	}

	public function onPostback($order, $is_valid, $plugin_validated) {
		if(!$this->params->get('active')) return;
		if($is_valid && $plugin_validated == 'donatelinq') {
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
				return "Your credit card transaction was declined with the following error: ".$reason;
			}

			//finally, let's the get the order details and save the payment information
			$order_id = JRequest::getInt('Custom2',false);
			if(!$order_id) {
				$is_valid = false;
				return 'Unable to identify order for payment. Please contact the webmaster for assistance.';
			}

			$ordermodel = FOFModel::getTmpInstance('orders','DonorcartModel');
			$order = $ordermodel->getItem($order_id);
			if(!is_object($order) || !$order->order_total || !is_object($order->cart) || !is_array($order->cart->items)) return false;
			if($order->payment_id) return false; //If we have already received a payment for this order, we do not want to overwrite it

			$external_reference_id = JRequest::getInt('pnref',false);
			if(!$external_reference_id || !is_numeric($external_reference_id)) return false;
			$user_id = JRequest::getInt('Custom1',$order->user_id);

			//sanity check. Make sure that the user on the order is the same as the user in the request
			if(!empty($order->user_id) && $order->user_id != $user_id) {
				return false;
			}

			//second sanity check. Make sure that the external reference has not already been used.
			$db = JFactory::getDbo();
			$sql = 'SELECT * FROM #__donorcart_payments WHERE payment_type="donatelinq" AND external_reference='.$db->quote($external_reference_id);
			$db->setQuery($sql);
			$db->query();
			if($db->getNumRows() != 0) return false;

			$paymentmodel = FOFModel::getTmpInstance('payment','DonorcartModel');
			$paymentinfo = $_POST;
			//TODO: get the desired payment information from the post. Don't just use the entire post.

			//$line_items = JRequest::getString('Custom2',null);  //get the cart items that we passed through a custom string to Cashlinq when we submitted payment.  We have to do this becuase Cashlinq does not pass this information back by default.
			//$note = JRequest::getString('Custom3','');
			//$post_data["line_items"] = $line_items;

			$paymentdata = array(
				'external_reference' => $external_reference_id,
				'payment_type' => 'donatelinq',
				'status' => 'complete',
				'infohash' => json_encode($paymentinfo)
			);
			if($user_id) $paymentdata['user_id'] = $user_id;
			$paymentmodel->save($paymentdata);

			//start collecting the order information to save
			$orderdata = array(
				'donorcart_order_id' => $order_id,
				'payment_id' => $paymentmodel->getId(),
				'status' => 'complete',
			);
			if($user_id) $orderdata['user_id'] = $user_id;
			if($email = JRequest::getString('Email', false)) $orderdata['email'] = $email;
			if($special_instr = JRequest::getString('Special_Instructions',false)) $orderdata['special_instr'] = $special_instr;
			if(is_callable(array('JApplication', 'getHash'))) {
				$orderdata['viewtoken'] = JApplication::getHash($order->donorcart_order_id.JSession::getFormToken());
			} else {
				$orderdata['viewtoken'] = md5(JFactory::getApplication()->get('secret').$order->donorcart_order_id.JSession::getFormToken());
			}


			//now we will save the billing address if the user is logged in and has not already saved their billing address
			if($user_id) {
				$addressmodel = FOFModel::getTmpInstance('addresses','DonorcartModel');
				$addressdata = array_filter(array(
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
					if(!is_object($order->billing_address)) {
						$order->billing_address = $addressmodel->getItem($order->billing_address_id);
					}
					if(!$order->billing_address->locked) {
						$addressdata = array_merge((array)$order->billing_address, $addressdata);
						$addressmodel->save($addressdata);
					}
				} else {
					if($addressmodel->save($addressdata)) {
						$orderdata['billing_address_id'] = $addressmodel->getId();
					}
				}
			}

			//finally, save the order
			$ordermodel->save($orderdata);
		}
	}
	public function onAfterPostback($order, $is_valid, $plugin_validated) {
		if(!$this->params->get('active')) return;
		if($is_valid && $plugin_validated == 'donatelinq') {
			JFactory::getApplication()->redirect('index.php?option=com_donorcart');
		}
	}

	private function _displayDonateQ($order, $params) {
		if(!$order->order_total):
			return 'No payment may be made for an empty order. Please add something to your cart and try checking out again.';
		else:
			$testmode = $params->get('testmode',1)?'Yes':'No';
			$ssl = $params->get('ssl_mode')?1:-1;
			$return_url = JRoute::_('index.php?option=com_donorcart&task=postback',true,$ssl);
			$cc_charges = round($order->order_total * 0.032,2);

			$cart_array = array('Designation^Amount');
			if(is_object($order->cart) && is_array($order->cart->items)):
				foreach($order->cart->items as $item):
					$cart_array[] = str_replace(array("&",'^','|'),array("and",'',''),$item->name).'^$'.number_format($item->qty*$item->price,2);
				endforeach;
			endif;
			$cart_code = '<input type="hidden" name="gridLineItem" value="'.implode('|',$cart_array).'" />';
			$cart_code .= '<input type="hidden" name="cart_original" value="'.implode('|',$cart_array).'" />';


			$address_code = '<input type="hidden" name="Email" value="'.$order->email.'" />';
			if($order->billing_address_id && is_object($order->billing_address)) {
				$addressparts = array();
				if($order->billing_adddress->address1) $addressparts[] = $order->billing_address->address1;
				if($order->billing_adddress->address2) $addressparts[] = $order->billing_address->address2;
				if($order->billing_address->first_name) $address_code .= '<input type="hidden" name="FirstName" value="'.str_replace('"','\"',$order->billing_address->first_name).'" />';
				if($order->billing_address->last_name) $address_code .= '<input type="hidden" name="LastName" value="'.str_replace('"','\"',$order->billing_address->last_name).'" />';
				if(!empty($addressparts)) $address_code .= '<input type="hidden" name="Address1" value="'.str_replace('"','\"',implode(', ',$addressparts)).'" />';
				if($order->billing_address->city) $address_code .= '<input type="hidden" name="City" value="'.str_replace('"','\"',$order->billing_address->city).'" />';
				if($order->billing_address->state) $address_code .= '<input type="hidden" name="St" value="'.str_replace('"','\"',$order->billing_address->state).'" />';
				if($order->billing_address->zip) $address_code .= '<input type="hidden" name="Zip" value="'.str_replace('"','\"',$order->billing_address->zip).'" />';
				if($order->billing_address->country) $address_code .= '<input type="hidden" name="Country" value="'.str_replace('"','\"',$order->billing_address->country).'" />';
			} else {
				$address_code .= '<input type="hidden" name="FirstName" value="" />';
				$address_code .= '<input type="hidden" name="lastName" value="" />';
			}

			if($order->shipping_address_id && is_object($order->shipping_address)) {
				if($order->shipping_address->first_name) $address_code .= '<input type="hidden" name="mail_first_name" value="'.str_replace('"','\"',$order->shipping_address->first_name).'" />';
				if($order->shipping_address->last_name) $address_code .= '<input type="hidden" name="mail_last_name" value="'.str_replace('"','\"',$order->shipping_address->last_name).'" />';
				if($order->shipping_address->address1) $address_code .= '<input type="hidden" name="mail_address" value="'.str_replace('"','\"',$order->shipping_address->address1).'" />';
				if($order->shipping_address->address2) $address_code .= '<input type="hidden" name="mail_address_two" value="'.str_replace('"','\"',$order->shipping_address->address2).'" />';
				if($order->shipping_address->city) $address_code .= '<input type="hidden" name="mail_city" value="'.str_replace('"','\"',$order->shipping_address->city).'" />';
				if($order->shipping_address->state) $address_code .= '<input type="hidden" name="mail_state" value="'.str_replace('"','\"',$order->shipping_address->state).'" />';
				if($order->shipping_address->zip) $address_code .= '<input type="hidden" name="mail_zip" value="'.str_replace('"','\"',$order->shipping_address->zip).'" />';
				if($order->shipping_address->country) $address_code .= '<input type="hidden" name="mail_country" value="'.str_replace('"','\"',$order->shipping_address->country).'" />';
			}

			$custom_code = '<input name="custom_1" value="'.$order->donorcart_order_id.'" type="hidden" />';
			if($order->user_id) $custom_code .= '<input name="custom_0" value="'.$order->user_id.'" type="hidden" />';

			$recurring_options = array();
			if($this->params->get('recur_onetime',false)) $recurring_options['One Time'] = 'One Time';
			if($this->params->get('recur_twoweeks',false)) $recurring_options['2 Weeks'] = '2 Weeks';
			if($this->params->get('recur_weekly',false)) $recurring_options['Weekly'] = 'Weekly';
			if($this->params->get('recur_fourweeks',false)) $recurring_options['4 Weeks'] = '4 Weeks';
			if($this->params->get('recur_monthly',false)) $recurring_options['Monthly'] = 'Monthly';
			if($this->params->get('recur_querterly',false)) $recurring_options['Querterly'] = 'Querterly';
			if($this->params->get('recur_semiannual',false)) $recurring_options['Semi-Annual'] = 'Semi-Annual';
			if($this->params->get('recur_yearly',false)) $recurring_options['Yearly'] = 'Yearly';
			if(empty($recurring_options)) $recurring_options['One Time'] = 'One Time'; // require at least one option
			if(count($recurring_options) == 1) {
				$recurring_code = '<input type="hidden" name="selFrequency" value="One Time" />';
			} else {
				$recurring_code = '<p><strong>Recurring Frequency: </strong><select name="selFrequency">';
				foreach($recurring_options as $value => $text) {
					$recurring_code .= '<option value="'.$value.'">'.$text.'</option>';
				}
				$recurring_code .= '</select></p>';
			}


			$form = <<<HEREDOC
<form name="cartform" method="post" enctype="application/x-www-form-urlencoded" action="{$this->params->get('donatelink')}">
	<input name="returnURL" value="$return_url" type="hidden" />
	<!--input name="show_receipt" value="No" type="hidden" /-->
	<input name="merchantid" value="{$this->params->get('merchant_id')}" type="hidden" />
	<input name="pageid" value="{$this->params->get('page_id')}" type="hidden" />
	<input name="billing" value="index.php" type="hidden" />
	<!--input name="pay_method" value="CC" type="hidden" /-->
	<!--input name="sale_type" value="SALE" type="hidden" /-->
	<input name="Amount" value="$order->order_total" type="hidden" />
	<input name="amount_original" value="$order->order_total" type="hidden" />
	<input name="test_request" value="$testmode" type="hidden" />
	$custom_code
	$cart_code
	$address_code
	$recurring_code
	<strong>Special Instructions:</strong> Enter any additional information here (max 512 characters)<br />
	<textarea name="Special_Instructions" maxlength="512"></textarea><br />
	<div class="checkout_boxx">
		<h2>Help cover credit card fees</h2>
		<p>For each credit card gift received, credit card companies charge around 3.2% of each gift. These fees are charged to missionaries as these costs are associated with their ministries. Your additional gift to cover credit card fees is fully tax-deductible.</p>
		<div class="checkout_box_option">
			<input id="pay_cc_yes" type="radio" name="pay_cc_charges" value="$cc_charges" />
			<input type="hidden" name="paycc_line_item" value="|Credit Card Fee Contribution^$$cc_charges" />
				I'll pay the 3.2% in credit card fee in addition to my donation.
		</div>
		<div class="checkout_box_option">
			<input id="pay_cc_no" type="radio" checked="checked" name="pay_cc_charges" value="0" />
			Please do not increase my gift to cover credit card fees. (You still receive credit for your full donation.)
		</div>
		<input type="submit" value="Continue Checkout" /> You will be transferred to our secure checkout page
	</div>
	<script type="text/javascript">
		jQuery('#pay_cc_yes').click(function() {
			document.cartform.Amount.value = parseFloat(document.cartform.amount_original.value) + parseFloat(jQuery('#pay_cc_yes').attr('value'));
			document.cartform.gridLineItem.value = document.cartform.cart_original.value + document.cartform.paycc_line_item.value;
		});
		jQuery('#pay_cc_no').click(function() {
			document.cartform.Amount.value = document.cartform.amount_original.value;
			document.cartform.gridLineItem.value = document.cartform.cart_original.value;
		});
	</script>
</form>
HEREDOC;

			return $form;
		endif;
	}
}