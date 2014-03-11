<?php defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php');

class plgDonorcartDonatelinq extends JPluginDonorcart {
	protected $_name = 'donatelinq';

	public function onBeforePostback($plugin_validated) {
		if($plugin_validated || !$this->isActive()) return;
		if(JRequest::getString('pnref',false)) {
			$plugin_validated = $this->getName();
			return true;
		}
	}

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

		$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
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

		//now we will save the billing address if appropriate
		if($user_id && $order->billing_address_id && !$order->billing_address->locked) {
			$addressmodel = FOFModel::getAnInstance('addresses','DonorcartModel');
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
			$addressdata = array_merge((array)$order->billing_address, $addressdata);
			$addressmodel->save($addressdata);
		}

		//finally, save the order
		$ordermodel->save($orderdata);
	}
}