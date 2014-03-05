<?php defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php');

class plgDonorcartAuthorizenet extends JPluginDonorcart {
	protected $_name = 'authorizenet';

	function __construct(&$subject, $config) {
		//require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'anet_php_sdk/AuthorizeNet.php'); //see https://developer.authorize.net/integration/fifteenminutes/#hosted etc.
		parent::__construct($subject, $config);
	}

	public function onSubmitOrder($order, $params, $payment_name) {
		if(!empty($payment_name) && $payment_name != $this->getName()) return;
		$pay_cc_fee = $this->_handle_processing_fee($order, $payment_name);

		$recurring_frequency = JRequest::getString($this->getName().'_payment_frequency','One Time');
		$infohash = array('payment_frequency'=>$recurring_frequency, 'pay_cc_fee'=>$pay_cc_fee);
		return array(
			'payment_type' => $payment_name,
			'infohash' => json_encode($infohash)
		);
	}

	public function onBeforePostback($plugin_validated) {
		if($plugin_validated || !$this->isActive()) return;
		$md5_hash = JRequest::getString('x_MD5_Hash','');
		if($md5_hash) {
			$invoice_num = JRequest::getInt('x_invoice_num',false);
			$response_code = JRequest::getInt('x_response_code',false);
			$transaction_id = JRequest::getString('x_trans_id',false);
			if($invoice_num && $response_code && $transaction_id !== false) {
				$verification_hash = strtoupper(md5($this->params->get('hash','').$this->params->get('login_id').JRequest::getString('x_trans_id','').JRequest::getString('x_amount','0.00')));
				if($md5_hash==$verification_hash) {
					$plugin_validated = 'authorizenet';
					return true;
				}
			}
		}
		return false;
	}

	public function onPostback($is_valid, $plugin_validated) {
		if($plugin_validated != $this->getName() || !$is_valid) return;

		//now let's confirm that the transaction was accepted
		$response_code = JRequest::getInt("x_response_code",0);
		if($response_code != 1) {
			if(!($response_text = JRequest::getString('x_response_reason_text',false))) {
				switch($response_code) {
					case 2:
						$response_text = 'This transaction has been declined';
						break;
					case 3:
						$response_text = 'There has been an error processing this transaction';
						break;
					case 4:
						$response_text = 'This transaction is being held for review';
						break;
					default:
						$response_text = 'Invalid Response from Payment Gateway.';
						break;
				}
			}
			$is_valid = false;
			return '<p>Your credit card transaction was declined with the following message: '.$response_text.'</p>';
		}

		//finally, let's the get the order details and save the payment information
		$order_id = JRequest::getInt('x_invoice_num',false);
		if(!$order_id) {
			$is_valid = false;
			return '<p>Unable to identify order for payment. Please contact the webmaster for assistance.</p>';
		}

		$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
		$order = $ordermodel->getItem($order_id);
		if(!is_object($order) || !$order->order_total || !is_object($order->cart) || !is_array($order->cart->items) || !$order->payment_id || !is_object($order->payment)) return false;
		if($order->payment_name != $this->getName() || $order->status=='complete') {
			$is_valid = false;
			return '<p>Unable to update payment details on your order. Payment has already been completed. Please contact your administrator for assistance.</p>';
		}

		$external_reference_id = JRequest::getString('x_trans_id',false);
		if($external_reference_id===false || !is_numeric($external_reference_id)) {
			$is_valid = false;
			return '<p>Invalid reference from payment gateway. Please contact your administrator for assistance</p>';
		}
		$user_id = JRequest::getInt('x_cust_id',0);

		//sanity check. Make sure that the user on the order is the same as the user in the request
		if(($order->user_id || $user_id) && $order->user_id != $user_id) {
			$is_valid = false;
			return '<p>403: User does not match order. If you are receiving this message in error, please contact your administrator for assistance.</p>';
		}

		//second sanity check. Make sure that the external reference has not already been used.
		$db = JFactory::getDbo();
		$sql = 'SELECT * FROM #__donorcart_payments WHERE payment_type="authorizenet" AND external_reference='.$db->quote($external_reference_id);
		$db->setQuery($sql);
		$db->query();
		if($db->getNumRows() != 0) {
			$is_valid = false;
			return '<p>Error: This payment reference has already been used. Please contact your administrator for assistance.</p>';
		}

		$paymentmodel = FOFModel::getAnInstance('payment','DonorcartModel');
		$paymentinfo = json_decode($order->payment->infohash,true);
		//$paymentinfo = array_merge($paymentinfo, $_POST);
		//TODO: get the desired payment information from the post. Don't just use the entire post.
		$paymentinfo = array_merge($paymentinfo, $_POST);

		$paymentdata = array(
			'donorcart_payment_id' => $order->payment->donorcart_payment_id,
			'external_reference' => $external_reference_id,
			'payment_type' => 'authorizenet',
			'infohash' => json_encode($paymentinfo)
		);
		$paymentmodel->save($paymentdata);
		$orderdata = array(
			'donorcart_order_id' => $order_id,
			'status' => 'complete'
		);
		if($email = JRequest::getString('x_email', false)) $orderdata['email'] = $email;
		if($special_instr = JRequest::getString('x_description',false)) $orderdata['special_instr'] = $special_instr;

		//now we will save the billing address if appropriate
		if($user_id && $order->billing_address_id && !$order->billing_address->locked) {
			$addressmodel = FOFModel::getAnInstance('addresses','DonorcartModel');
			$addressdata = array(
				'donorcart_address_id' => $order->billing_address_id,
				'user_id' => $user_id,
				'first_name' => JRequest::getString('x_first_name',''),
				'last_name' => JRequest::getString('x_last_name',''),
				'business_name' => JRequest::getString('x_company',''),
				'address1' => JRequest::getString('x_address',''),
				'address2' => '',
				'city' => JRequest::getString('x_city',''),
				'state' => JRequest::getString('x_state',''),
				'zip' => JRequest::getString('x_zip',''),
				'country' => JRequest::getString('x_country',''),
				'locked' => 1
			);
			$addressdata = array_merge((array)$order->billing_address, $addressdata);
			$addressmodel->save($addressdata);
		}
		//and the shipping address
		if($user_id && $order->shipping_address_id && !$order->shipping_address->locked) {
			if(!$addressmodel) $addressmodel = FOFModel::getAnInstance('addresses','DonorcartModel');
			$addressdata = array(
				'donorcart_address_id' => $order->shipping_address_id,
				'user_id' => $user_id,
				'first_name' => JRequest::getString('x_ship_to_first_name',''),
				'last_name' => JRequest::getString('x_ship_to_last_name',''),
				'business_name' => JRequest::getString('x_ship_to_company',''),
				'address1' => JRequest::getString('x_ship_to_address',''),
				'address2' => '',
				'city' => JRequest::getString('x_ship_to_city',''),
				'state' => JRequest::getString('x_ship_to_state',''),
				'zip' => JRequest::getString('x_ship_to_zip',''),
				'country' => JRequest::getString('x_ship_to_country',''),
				'locked' => 1
			);
			$addressdata = array_merge((array)$order->shipping_address, $addressdata);
			$addressmodel->save($addressdata);
		}


		$ordermodel->save($orderdata);
	}

	public function onAfterPostback($is_valid, $plugin_validated) {
		if($plugin_validated == $this->getName() && $is_valid) {
			$sitename = JFactory::getApplication()->getCfg('sitename');
			$ssl = JComponentHelper::getParams('com_donorcart')->get('ssl_mode')?1:-1;
			$returnurl = JRoute::_('index.php?option=com_donorcart',false,$ssl);
			$return_text = '<p>Payment authorization successful<br />
				Redirecting back to '.$sitename.'<br />
				If your browser does not automatically redirect you, <a href="'.$returnurl.'">Click Here</a></p>
				<script type="text/javascript">setTimeout("window.location=\"'.$returnurl.'\"",5000);</script>';
			return $return_text;
		}
	}
}