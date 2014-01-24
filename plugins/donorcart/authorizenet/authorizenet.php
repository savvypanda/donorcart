<?php defined('_JEXEC') or die('Restricted Access');

class plgDonorcartAuthorizenet extends JPlugin {
	private $order_id, $errormsg='';

	function __construct(& $subject, $config) {
		require_once (dirname(__FILE__).DIRECTORY_SEPARATOR.'anet_php_sdk/AuthorizeNet.php'); //see https://developer.authorize.net/integration/fifteenminutes/#hosted etc.
		parent::__construct($subject, $config);
	}

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
		if(!$this->params->get('active')) return;
		if(!$order->order_total || !$order->cart_id || !is_object($order->cart) || !is_array($order->cart->items) || empty($order->cart->items)) return 'No payment may be made for an empty order. Please add something to your cart and try checking out again.';

		$amount = ceil($order->order_total*100)/100;
		$api_login_id = $this->params->get("login_id");
		$transaction_key = $this->params->get("transaction_key");
		$fp_timestamp = time();
		$fp_sequence = $order->donorcart_order_id;
		$fingerprint = AuthorizeNetSIM_Form::getFingerprint($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp);

		$mode = $this->params->get('mode','test');
		switch($mode) {
			case 'sandbox':
			case 'test':
				$modepath = 'https://test.authorize.net/gateway/transact.dll';
				break;
			case 'live':
				$modepath = 'https://secure.authorize.net/gateway/transact.dll';
				break;
			//case 'eProcessingNetwork':
			//	$modepath = 'https://www.eProcessingNetwork.Com/cgi-bin/an/order.pl';
			//	break;
			default:
				return "<p>Unable to display payment form. Invalid configuration.</p>";
		}
		$testmode = ($mode=='test')?'TRUE':'FALSE';

		$sitename = JFactory::getApplication()->getCfg('sitename');
		$cancelurl = JRoute::_(JUri::root().'index.php?option=com_donorcart');
		$relayurl = JRoute::_(JUri::root().'index.php?option=com_donorcart&task=postback&tmpl=component');

		$cartcode = '';
		foreach($order->cart->items as $item) {
			$orderpieces=array(
				substr($item->sku,0,30),
				substr($item->name,0,30),
				'',
				$item->qty,
				$item->price,
				'FALSE'
			);
			$cartcode .= '<input type=\'hidden\' name=\'x_line_item\' value=\''.implode('<|>',$orderpieces).'\' />';
		}

		$addresscode = '<input type="hidden" name="x_email" value="'.$order->email.'" />';
		if($order->billing_address_id && is_object($order->billing_address)) {
			$addressarray = array();
			if($order->billing_address->first_name) $addresscode .= '<input type=\'hidden\' name=\'x_first_name\' value=\''.$order->billing_address->first_name.'\' />';
			if($order->billing_address->last_name) $addresscode .= '<input type=\'hidden\' name=\'x_last_name\' value=\''.$order->billing_address->last_name.'\' />';
			if($order->billing_address->address1) $addressarray[] = $order->billing_address->address1;
			if($order->billing_address->address2) $addressarray[] = $order->billing_address->address2;
			if(!empty($addressarray)) $addresscode .= '<input type=\'hidden\' name=\'x_address\' value=\''.implode(', ',$addressarray).'\' />';
			if($order->billing_address->city) $addresscode .= '<input type=\'hidden\' name=\'x_city\' value=\''.$order->billing_address->city.'\' />';
			if($order->billing_address->state) $addresscode .= '<input type=\'hidden\' name=\'x_state\' value=\''.$order->billing_address->state.'\' />';
			if($order->billing_address->zip) $addresscode .= '<input type=\'hidden\' name=\'x_zip\' value=\''.$order->billing_address->zip.'\' />';
			if($order->billing_address->country) $addresscode .= '<input type=\'hidden\' name=\'x_country\' value=\''.$order->billing_address->country.'\' />';
		}
		if($order->shipping_address_id && is_object($order->shipping_address)) {
			$addressarray = array();
			if($order->shipping_address->first_name) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_first_name\' value=\''.$order->shipping_address->first_name.'\' />';
			if($order->shipping_address->last_name) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_last_name\' value=\''.$order->shipping_address->last_name.'\' />';
			if($order->shipping_address->address1) $addressarray[] = $order->shipping_address->address1;
			if($order->shipping_address->address2) $addressarray[] = $order->shipping_address->address2;
			if(!empty($addressarray)) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_address\' value=\''.implode(', ',$addressarray).'\' />';
			if($order->shipping_address->city) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_city\' value=\''.$order->shipping_address->city.'\' />';
			if($order->shipping_address->state) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_state\' value=\''.$order->shipping_address->state.'\' />';
			if($order->shipping_address->zip) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_zip\' value=\''.$order->shipping_address->zip.'\' />';
			if($order->shipping_address->country) $addresscode .= '<input type=\'hidden\' name=\'x_ship_to_country\' value=\''.$order->shipping_address->country.'\' />';
		}

		$form = <<<HEREDOC
			<form name="redirectToAuthorizeNet" id="redirectToAuthorizeNet" method="POST" action="$modepath">
				<p>Amount: $$amount<br />
					Please procede to our secure payments processor...<br />
					<input type="submit" value="Click here for the secure payment form" />
				</p>

				<input type='hidden' name='x_login' value='$api_login_id' />
				<input type='hidden' name='x_fp_hash' value='$fingerprint' />
				<input type='hidden' name='x_fp_timestamp' value='$fp_timestamp' />
				<input type='hidden' name='x_fp_sequence' value='$fp_sequence' />
				<input type='hidden' name='x_version' value='3.1' />
				<input type='hidden' name='x_type' value='AUTH_CAPTURE' />
				<input type='hidden' name='x_show_form' value='PAYMENT_FORM'>
				<input type='hidden' name='x_delim_data' value='FALSE'>
				<input type='hidden' name='x_relay_response' value='TRUE'>
				<input type='hidden' name='x_method' value='CC'>
				<input type='hidden' name='x_test_request' value='$testmode' />
				<input type='hidden' name='x_amount' value='$amount' />

				<input type='hidden' name='x_po_num' value='{$order->donorcart_order_id}' />
				<input type='hidden' name='x_cust_id' value='{$order->user_id}' />
				$cartcode
				$addresscode

				<input type='hidden' name='x_cancel_url' VALUE='$cancelurl'>
				<input type='hidden' name='x_cancel_url_text' value='Cancel and return to $sitename' >
				<input type='hidden' name='x_relay_url' value='$relayurl'>
				<input type='hidden' name='x_relay_always' value='FALSE'>
			</form>
HEREDOC;
		return $form;
	}

	public function onDisplayPaymentInfo($order) {
		if($order->payment_id && is_object($order->payment) && $order->payment->payment_type=='authorizenet') {
			$payment_info = json_decode($order->payment->infohash);
			$str_method = ($payment_info['x_method']=='CC')?'Credit Cart':'E-Check';
			$paymenttext = <<<HEREDOC
				<div>
					<p><strong>Payment Type</strong>: $str_method</p>
					<p><strong>Payment Amount</strong>: {$order->order_total}</p>
					<p><strong>Payment Status</strong>: {$payment_info['x_response_reason_text']}</p>
					<p><strong>Transaction ID</strong>: {$payment_info['x_po_num']}</p>
				</div>
HEREDOC;
			return $paymenttext;
		}
	}


	public function onBeforePostback($order, $plugin_validated) {
		if($plugin_validated || !$this->params->get('active')) return;
		if(JRequest::getInt("x_response_code",false) && JRequest::getInt('x_trans_id',false) && JRequest::getInt('x_po_num',false)) {
			$plugin_validated = 'authorizenet';
/*
$logfile = JPATH_BASE.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'donorcart_authorizenet.log';
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
		if($is_valid && $plugin_validated=='authorizenet') {
			//first we need to verify that this post came from Authorize.Net
			$api_login_id = $this->params->get("login_id");
			$api_hash = $this->params->get("hash");
			$authorizeNetSIM = new AuthorizeNetSIM($api_login_id, $api_hash);
			$authorizeNetSIM->md5_hash = JRequest::getString("x_md5_hash", $authorizeNetSIM->md5_hash);
			if(!$authorizeNetSIM->isAuthorizeNet()) {
				$this->errormsg = "Error code 506. Possible fraud. Transaction ID ".$authorizeNetSIM->transaction_id;
				return false;
			}

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
				$this->errormsg = 'Your credit card transaction was declined with the following message: '.$response_text;
				return false;
			}

			//finally, let's the get the order details and save the payment information
			$order_id = JRequest::getInt('x_po_num',0);
			if($order_id == 0) {
				$this->errormsg = 'Unable to identify order for payment. Please contact the webmaster for assistance.';
				return false;
			}

			$ordermodel = FOFModel::getTmpInstance('orders','DonorcartModel');
			$order = $ordermodel->getItem($order_id);
			if(!is_object($order) || !$order->order_total || !is_object($order->cart) || !is_array($order->cart->items)) return false;
			if($order->payment_id) return false; //If we have already received a payment for this order, we do not want to overwrite it

			$external_reference_id = JRequest::getInt('x_trans_id',0); //JInput::getInt('x_invoice_num',0);
			$user_id = JRequest::getInt('x_cust_id');

			//sanity check. Make sure that the user ID in the request is the same as the user ID on the order
			if(($order->user_id || $user_id) && $order->user_id != $user_id) {
				return false;
			}

			//second sanity check. Make sure that the external reference has not already been used.
			$db = JFactory::getDbo();
			$sql = 'SELECT * FROM #__donorcart_payments WHERE payment_type="authorizenet" AND external_reference='.$db->quote($external_reference_id);
			$db->setQuery($sql);
			$db->query();
			if($db->getNumRows() != 0) return false;

			$paymentmodel = FOFModel::getTmpInstance('payment','DonorcartModel');
			$paymentinfo = $_POST;
			//TODO: get the desired payment information from the post. Don't just use the entire post.
			$paymentdata = array(
				'external_reference' => $external_reference_id,
				'payment_type' => 'authorizenet',
				'status' => 'complete',
				'infohash' => json_encode($paymentinfo)
			);
			if($user_id) $paymentdata['user_id'] = $user_id;
			$paymentmodel->save($paymentdata);
			$orderdata = array(
				'donorcart_order_id' => $order_id,
				'payment_id' => $paymentmodel->getId(),
				'status' => 'complete'
			);
			if(is_callable(array('JApplication', 'getHash'))) {
				$orderdata['viewtoken'] = JApplication::getHash($order->donorcart_order_id.JSession::getFormToken());
			} else {
				$orderdata['viewtoken'] = md5(JFactory::getApplication()->get('secret').$order->donorcart_order_id.JSession::getFormToken());
			}
			$ordermodel->save($orderdata);
		}
	}

	public function onAfterPostback($order, $is_valid, $plugin_validated) {
		if(!$this->params->get('active')) return;
		if($plugin_validated == 'authorizenet') {
			$sitename = JFactory::getApplication()->getCfg('sitename');
			$returnurl = JRoute::_(JUri::base().'index.php?option=com_donorcart');
			if($is_valid) {
				//JFactory::getApplication()->redirect('index.php?option=com_donorcart');
				$return_text = '<p>Payment authorization successful<br />
						Redirecting back to '.$sitename.'<br />
						If your browser does not automatically redirect you, <a href="'.$returnurl.'">Click Here</a></p>
						<script type="text/javascript">setTimeout("window.location=\"'.$returnurl.'\"",5000);</script>';
			} else {
				//JFactory::getApplication()->redirect('index.php?option=com_donorcart', $this->errormsg, 'error');
				$return_text = '<p>Payment authorization failed<br />'.$this->errormsg.'<br />
						<a href="'.$returnurl.'">return to '.$sitename.'</a>';
			}
			return $return_text;
		}
	}
}
