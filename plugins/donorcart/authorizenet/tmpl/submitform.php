<?php defined('_JEXEC') or die('Restricted Access');

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

$sitename = JFactory::getApplication()->getCfg('sitename');
$ssl = $params->get('ssl_mode')?1:-1;
$cancelurl = JRoute::_('index.php?option=com_donorcart&task=resetOrder',false,$ssl);
$relayurl = JRoute::_('index.php?option=com_donorcart&task=postback&tmpl=component',false,$ssl);

$api_login_id = $this->params->get("login_id");
$transaction_key = $this->params->get("transaction_key");
$fp_timestamp = time();
$fp_sequence = $order->donorcart_order_id;
$hashstring = $api_login_id."^".$fp_sequence."^".$fp_timestamp."^".$order->order_total."^";
$fingerprint = function_exists('hash_hmac')?hash_hmac("md5", $hashstring, $transaction_key):bin2hex(mhash(MHASH_MD5, $hashstring, $transaction_key));

?>
<form name="dcart-authorizenet-redirectform" id="dcart-authorizenet-redirectform" method="POST" action="<?=$modepath?>">
	<input type='hidden' name='x_login' value='<?=$api_login_id?>' />
	<input type='hidden' name='x_fp_hash' value='<?=$fingerprint?>' />
	<input type='hidden' name='x_fp_timestamp' value='<?=$fp_timestamp?>' />
	<input type='hidden' name='x_fp_sequence' value='<?=$fp_sequence?>' />
	<input type='hidden' name='x_version' value='3.1' />
	<input type='hidden' name='x_type' value='AUTH_CAPTURE' />
	<input type='hidden' name='x_show_form' value='PAYMENT_FORM' />
	<input type='hidden' name='x_delim_data' value='FALSE' />
	<input type='hidden' name='x_relay_response' value='TRUE' />
	<input type='hidden' name='x_relay_url' value='<?=$relayurl?>' />
	<!--input type='hidden' name='x_method' value='CC' /-->
	<input type='hidden' name='x_test_request' value='<?=($mode=='test'?1:0)?>' />
	<input type='hidden' name='x_amount' value='<?=$order->order_total?>' />
	<input type='hidden' name='x_invoice_num' value='<?=$order->donorcart_order_id?>' />
	<input type='hidden' name='x_cust_id' value='<?=$order->user_id?>' />
	<input type='hidden' name='x_cancel_url' value='<?=$cancelurl?>' />
	<input type='hidden' name='x_cancel_url_text' value='Cancel and return to <?=$sitename?>' />
	<input type='hidden' name='x_relay_always' value='FALSE' />
	<input type='hidden' name='x_duplicate_window' value='5' />
	<input type='hidden' name='x_recurring_billing' value='<?=($order->cart->recurring?1:0)?>' />
	<input type='hidden' name='x_description' value='<?=htmlentities(substr($order->special_instr,0,250))?>' />
	<?php if($order->email): ?><input type="hidden" name="x_email" value="<?=$order->email?>" /><?php endif; ?>
	<?php /* <input type='hidden' name='x_phone' value='<?=$order->phone?>' /> */ ?>
	<?php
	foreach($order->cart->items as $item) {
		$orderpieces=array(
			substr($item->sku,0,30),
			substr($item->name,0,30),
			'',
			$item->qty,
			$item->price,
			'FALSE'
		);
		?>
		<input type='hidden' name='x_line_item' value='<?=implode('<|>',$orderpieces)?>' />
	<?php }
	if($order->billing_address_id && is_object($order->billing_address)) {
		if($order->billing_address->first_name): ?><input type='hidden' name='x_first_name' value='<?=$order->billing_address->first_name?>' /><?php endif;
		if($order->billing_address->last_name): ?><input type='hidden' name='x_last_name' value='<?=$order->billing_address->last_name?>' /><?php endif;
		$addressarray = array();
		if($order->billing_address->address1) $addressarray[] = $order->billing_address->address1;
		if($order->billing_address->address2) $addressarray[] = $order->billing_address->address2;
		if($order->billing_address->business_name): ?><input type='hidden' name='x_company' value='<?=$order->billing_address->business_name?>' /><?php endif;
		if(!empty($addressarray)): ?><input type='hidden' name='x_address' value='<?=implode(', ',$addressarray)?>' /><?php endif;
		if($order->billing_address->city): ?><input type='hidden' name='x_city' value='<?=$order->billing_address->city?>' /><?php endif;
		if($order->billing_address->state): ?><input type='hidden' name='x_state' value='<?=$order->billing_address->state?>' /><?php endif;
		if($order->billing_address->zip): ?><input type='hidden' name='x_zip' value='<?=$order->billing_address->zip?>' /><?php endif;
		if($order->billing_address->country): ?><input type='hidden' name='x_country' value='<?=$order->billing_address->country?>' /><?php endif;
	}
	if($order->shipping_address_id && is_object($order->shipping_address)) {
		if($order->shipping_address->first_name): ?><input type='hidden' name='x_ship_to_first_name' value='<?=$order->shipping_address->first_name?>' /><?php endif;
		if($order->shipping_address->last_name): ?><input type='hidden' name='x_ship_to_last_name' value='<?=$order->shipping_address->last_name?>' /><?php endif;
		if($order->shipping_address->business_name): ?><input type='hidden' name='x_ship_to_company' value='<?=$order->shipping_address->business_name?>' /><?php endif;
		$addressarray = array();
		if($order->shipping_address->address1) $addressarray[] = $order->shipping_address->address1;
		if($order->shipping_address->address2) $addressarray[] = $order->shipping_address->address2;
		if(!empty($addressarray)): ?><input type='hidden' name='x_ship_to_address' value='<?=implode(', ',$addressarray)?>' /><?php endif;
		if($order->shipping_address->city): ?><input type='hidden' name='x_ship_to_city' value='<?=$order->shipping_address->city?>' /><?php endif;
		if($order->shipping_address->state): ?><input type='hidden' name='x_ship_to_state' value='<?=$order->shipping_address->state?>' /><?php endif;
		if($order->shipping_address->zip): ?><input type='hidden' name='x_ship_to_zip' value='<?=$order->shipping_address->zip?>' /><?php endif;
		if($order->shipping_address->country): ?><input type='hidden' name='x_ship_to_country' value='<?=$order->shipping_address->country?>' /><?php endif;
	} ?>

	<p>You are being redirected to our secure processing server.<br />
		If you are not redirected within 5 seconds <input type="submit" id="dcart-authorizenet-submitformbutton" value="Click here" /></p>
</form>
<script type="text/javascript">
	var redirectform=document.getElementById("dcart-authorizenet-redirectform");
	var submitted=false;
	redirectform.onSubmit=function(){if(submitted)return false;submitted=true;return true};
	window.setTimeout(function(){redirectform.submit()},3000)
</script>