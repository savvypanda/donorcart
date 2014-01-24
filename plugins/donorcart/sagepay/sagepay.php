<?php defined('_JEXEC') or die('Restricted Access');

class plgDonorcartSagepay extends JPlugin {
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
			return $this->_displaySageP($order, $params);
		}
	}

	public function onReplaceGuestCheckout($order, $params) {
		if($this->params->get('active')) {
			return $this->_displaySageP($order, $params);
		}
	}

	public function onDisplayPaymentInfo($order) {
		$payment_id = $order->payment_id;
		if($payment_id) {
			$paymentmodel = FOFModel::getTmpInstance('payment','DonorcartModel');
			$payment = $paymentmodel->getItem($payment_id);
			if($payment->payment_type='sagepay') {
				$payment_info = json_decode($payment->infohash);
				$recurring_text = (isset($payment_info->is_recurring) && $payment_info->is_recurring)?'recurring':'one-time';

				$paymenttext = <<<HEREDOC
<div>
	<p><strong>Payment Type</strong>: Credit Card</p>
	<p><strong>Payment Amount</strong>: {$order->order_total}</p>
	<p><strong>Recurring</strong>? $recurring_text</p>
</div>
HEREDOC;
				return $paymenttext;
			}
		}
	}

	public function onBeforePostback($order, $plugin_validated) {
		if($plugin_validated || !$this->params->get('active')) return;
		if($merchant_id = JRequest::getVar('M_id',false)) {
			$verify_id = $this->params->get('sage_payments_virtual_terminal_id');
			if($merchant_id == $verify_id) {
				if(JRequest::getVar('transaction_type',false) && JRequest::getVar('T_ordernum',false) && JRequest::getVar('grand_total',false) && JRequest::getVar('P_count',false) && JRequest::getVar('P_part1')) {
					//this is a postback message from Sage Payments
					$plugin_validated = 'sagepay';

$logfile = JPATH_BASE.DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR.'donorcart_sagepay.log';
$handle = fopen($logfile, 'a');
$text = "\n\n[".date('Y-m-d H:i:s')."]  POST:\n".var_export($_POST,true)."\n";
fwrite($handle, $text);
fclose($handle);

					return true;
				}
			}
		}
		return false;
	}

	public function onPostback($order, $is_valid, $plugin_validated) {
		if(!$this->params->get('active')) return;
		if($is_valid && $plugin_validated == 'sagepay') {
			$is_recurring = $this->_sage_get_is_recurring();
			//$address = JRequest::getString('C_address');
			//$address2 = JRequest::getString('C_apt');
			$item1 = JRequest::getString('P_part1','');
			$order_id = substr($item1, 0, strpos($item1, '^'));
			if(!$order_id) {
				$is_valid = false;
				return 'Unable to identify order for payment. Please contact the webmaster for assistance.';
			}

			$ordermodel = FOFModel::getTmpInstance('orders','DonorcartModel');
			$order = $ordermodel->getItem($order_id);
			if(!is_object($order) || !$order->order_total || !is_object($order->cart) || !is_array($order->cart->items)) return false;
			if($order->payment_id) return false; //If we have already received a payment for this order, we do not want to overwrite it

			$external_reference_id = JRequest::getInt('C_reference',false);
			if(!$external_reference_id || !is_numeric($external_reference_id)) return false;

			//Sanity check. Make sure that the external reference has not already been used.
			$db = JFactory::getDbo();
			$sql = 'SELECT * FROM #__donorcart_payments WHERE payment_type="sagepay" AND external_reference='.$db->quote($external_reference_id);
			$db->setQuery($sql);
			$db->query();
			if($db->getNumRows() != 0) return false;

			$paymentmodel = FOFModel::getTmpInstance('payment','DonorcartModel');
			$paymentinfo = $_POST;
			//TODO: get the desired payment information from the post. Don't just use the entire post.
			$paymentinfo['is_recurring'] = $is_recurring;

			$paymentdata = array(
				'external_reference' => $external_reference_id,
				'payment_type' => 'sagepay',
				'status' => 'complete',
				'infohash' => json_encode($paymentinfo)
			);
			if($order->user_id) $paymentdata['user_id'] = $order->user_id;
			$paymentmodel->save($paymentdata);

			//start collecting the order information to save
			$orderdata = array(
				'donorcart_order_id' => $order_id,
				'payment_id' => $paymentmodel->getId(),
				'status' => 'complete',
			);
			if($email = JRequest::getString('C_email', false)) $orderdata['email'] = $email;
			if($special_instr = JRequest::getString('C_memo',false)) $orderdata['special_instr'] = $special_instr;
			if(is_callable(array('JApplication', 'getHash'))) {
				$orderdata['viewtoken'] = JApplication::getHash($order->donorcart_order_id.JSession::getFormToken());
			} else {
				$orderdata['viewtoken'] = md5(JFactory::getApplication()->get('secret').$order->donorcart_order_id.JSession::getFormToken());
			}
			$ordermodel->save($orderdata);

			//finally, if saving the order is successful, process the sponsorship items (if applicable)
			$this->_process_sponsorship_items();
		}
	}

	public function onAfterPostback($order, $is_valid, $plugin_validated) {
		if(!$this->params->get('active')) return;
		if($is_valid && $plugin_validated == 'sagepay') {
			//JFactory::getApplication()->redirect('index.php?option=com_donorcart');
			die('Success');
		}
	}

	private function _displaySageP($order, $params) {
		if(!$order->order_total):
			return 'No payment may be made for an empty order. Please add something to your cart and try checking out again.';
		else:
			//$testmode = $params->get('testmode',1)?'Yes':'No';
			//$ssl = $params->get('ssl_mode')?1:-1;
			//$return_url = JRoute::_('index.php?option=com_donorcart&task=postback',true,$ssl);
			//$fail_url = JRoute::_('index.php?option=com_donorcart&task=postback',true,$ssl);
			$terminal_id = $this->params->get('sage_payments_virtual_terminal_id');

			$cart_code = '';
			$i = 0;
			if(is_object($order->cart) && is_array($order->cart->items)):
				foreach($order->cart->items as $item):
					$i++;
					$cart_code .= '<input type="hidden" name="P_part'.$i.'" value="'.$order->donorcart_order_id.'^'.$item->sku.'" />';
					$cart_code .= '<input type="hidden" name="P_desc'.$i.'" value="'.$item->name.'" />';
					$cart_code .= '<input type="hidden" name="P_qty'.$i.'" value="'.$item->qty.'" />';
					$cart_code .= '<input type="hidden" name="P_price'.$i.'" value="'.$item->price.'" />';
				endforeach;
			endif;
			$cart_code .= '<input type="hidden" name="P_count" value="'.$i.'" />';

			$param_code = '';
			foreach(array('M_image','B_color','BF_color','M_color','F_color','F_font') as $p):
				$pval = $this->params->get($p);
				if(!empty($pval)) $param_code .= '<input type="hidden" name="'.$p.'" value="'.$pval.'" />';
			endforeach;


			$str_recurring = JText::_(' Make this an automatic monthly gift');
			$str_button = JText::_('Click Here to Complete Donation');
			$form = <<<HEREDOC
<form name="cartform" id="cartform" method="post" enctype="application/x-www-form-urlencoded" action="https://www.sagepayments.net/eftcart/forms/order.asp" onsubmit="return sage_form_submit();">
	<fieldset class="em">
		<input type='checkbox' name='is_recurring' id="is_recurring" /><strong>$str_recurring</strong>
	</fieldset>
	<fieldset class="em">
		<input type="submit" name="submit1" id="submit1" class="button" value="$str_button &raquo;" /><br />
		<span id="processing"><em>You will be redirected to our secure processing server</em></span>
	</fieldset>

	<input type="hidden" name="M_id" value="{$terminal_id}" />
	{$cart_code}
	{$param_code}

	<script type="text/javascript">
		function sage_form_submit() {
			products = jQuery('input[name^=P_part]', '#cartform');
			pattern = /-[R1]X$/;
			replace = jQuery('input[name="is_recurring"]', '#cartform').is(':checked')?'-RX':'-1X';
			products.each(function() {
				var sku = jQuery(this).val();
				if(pattern.test(sku)) {
					jQuery(this).val(sku.replace(pattern,replace));
				} else {
					jQuery(this).val(sku+replace);
				}
			});
			return true;
		}
	</script>
</form>
HEREDOC;

			return $form;
		endif;
	}

	private function _process_sponsorship_items() {
		$count = JRequest::getInt('P_count');
		for($i = 1; $i <= $count; $i++) {
			$name = JRequest::getString('P_desc'.$i);
			if(preg_match('/^Sponsor a Child/', $name)) {
				$sku = JRequest::getString('P_part'.$i);
				$sku = substr($sku,strpos($sku,'^')+1,-3);
				$db = JFactory::getDbo();
				$query = 'UPDATE #__sponsorship_children SET sponsored=1, status=\'Sponsored\' WHERE child_id_number='.$db->quote($sku);
				$db->setQuery($query);
				$db->query();
				$query = 'INSERT INTO #__sponsorship_sponsors(child_id_number) VALUES ('.$db->quote($sku).')';
				$db->setQuery($query);
				$db->query();
			}
		}
	}

	private function _sage_get_is_recurring() {
		$count = JRequest::getInt('P_count');
		for($i = 1; $i <= $count; $i++) {
			if(preg_match('/-RX$/', JRequest::getString('P_part'.$i))) {
				return true;
			}
		}
		return false;
	}
}