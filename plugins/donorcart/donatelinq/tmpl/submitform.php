<?php defined('_JEXEC') or die('Restricted Access');
$ssl = $params->get('ssl_mode')?1:-1;
$return_url = 'index.php?option=com_donorcart&task=postback&oid='.$order->donorcart_order_id;
if($order->user_id) $return_url .= '&uid='.$order->user_id;
$return_url = JRoute::_($return_url,true,$ssl);
$payment_info = json_decode($order->payment->infohash,true);
$recurring_frequency = isset($payment_info['selFrequency'])?$payment_info['selFrequency']:'One Time';

?>
<form name="cartform" method="post" id="dcart-donatelinq-redirectform" enctype="application/x-www-form-urlencoded" action="<?=$this->params->get('donatelink')?>">
	<input name="returnURL" value="<?=$return_url?>" type="hidden" />
	<input name="merchantid" value="<?=$this->params->get('merchant_id')?>" type="hidden" />
	<input name="pageid" value="<?=$this->params->get('page_id')?>" type="hidden" />
	<input name="Amount" value="<?=$order->order_total?>" type="hidden" />
	<?php
		$cart_array = array('Designation^Amount');
		if(is_object($order->cart) && is_array($order->cart->items)):
			foreach($order->cart->items as $item):
				$cart_array[] = str_replace(array("&",'^','|'),array("and",'',''),$item->name).'^$'.number_format($item->qty*$item->price,2);
			endforeach;
		endif;
	?>
	<input type="hidden" name="gridLineItem" value="<?=implode('|',$cart_array)?>" />
	<input type="hidden" name="Email" value="<?=$order->email?>" />
	<?php if($order->billing_address_id && is_object($order->billing_address)) {
		$addressparts = array();
		if($order->billing_adddress->address1) $addressparts[] = $order->billing_address->address1;
		if($order->billing_adddress->address2) $addressparts[] = $order->billing_address->address2;
		if($order->billing_address->first_name): ?><input type="hidden" name="FirstName" value="<?=str_replace('"','\"',$order->billing_address->first_name)?>" /><?php endif;
		if($order->billing_address->last_name): ?><input type="hidden" name="LastName" value="<?=str_replace('"','\"',$order->billing_address->last_name)?>" /><?php endif;
		if(!empty($addressparts)): ?><input type="hidden" name="Address1" value="<?=str_replace('"','\"',implode(', ',$addressparts))?>" /><?php endif;
		if($order->billing_address->city): ?><input type="hidden" name="City" value="<?=str_replace('"','\"',$order->billing_address->city)?>" /><?php endif;
		if($order->billing_address->state): ?>input type="hidden" name="St" value="<?=str_replace('"','\"',$order->billing_address->state)?>" /><?php endif;
		if($order->billing_address->zip): ?><input type="hidden" name="Zip" value="<?=str_replace('"','\"',$order->billing_address->zip)?>" /><?php endif;
		if($order->billing_address->country): ?><input type="hidden" name="Country" value="<?=str_replace('"','\"',$order->billing_address->country)?>" /><?php endif;
	} else { ?>
		<input type="hidden" name="FirstName" value="" />
		<input type="hidden" name="lastName" value="" />
	<?php }
	if($order->shipping_address_id && is_object($order->shipping_address)) {
		if($order->shipping_address->first_name): ?><input type="hidden" name="mail_first_name" value="<?=str_replace('"','\"',$order->shipping_address->first_name)?>" /><?php endif;
		if($order->shipping_address->last_name): ?><input type="hidden" name="mail_last_name" value="<?=str_replace('"','\"',$order->shipping_address->last_name)?>" /><?php endif;
		if($order->shipping_address->address1): ?><input type="hidden" name="mail_address" value="<?=str_replace('"','\"',$order->shipping_address->address1)?>" /><?php endif;
		if($order->shipping_address->address2): ?><input type="hidden" name="mail_address_two" value="<?=str_replace('"','\"',$order->shipping_address->address2)?>" /><?php endif;
		if($order->shipping_address->city): ?><input type="hidden" name="mail_city" value="<?=str_replace('"','\"',$order->shipping_address->city)?>" /><?php endif;
		if($order->shipping_address->state): ?><input type="hidden" name="mail_state" value="<?=str_replace('"','\"',$order->shipping_address->state)?>" /><?php endif;
		if($order->shipping_address->zip): ?><input type="hidden" name="mail_zip" value="<?=str_replace('"','\"',$order->shipping_address->zip)?>" /><?php endif;
		if($order->shipping_address->country): ?><input type="hidden" name="mail_country" value="<?=str_replace('"','\"',$order->shipping_address->country)?>" /><?php endif;
	} ?>

	<input type="hidden" name="selFrequency" value="<?=$recurring_frequency?>" />
	<?php if($recurring_frequency != 'One Time'): ?><input type="hidden" name="donationStartDate" value="<?=date('m/d/Y')?>" /><?php endif; ?>

	<input type="hidden" name="donationComments" value="<?=htmlentities(substr($order->special_instr,0,500))?>" />
	<input type="hidden" name="custom_7" value="<?=htmlentities(substr($order->special_instr,0,500))?>" />
	<p>You are being redirected to our secure processing server.<br />
		If you are not redirected within 5 seconds <input type="submit" id="dcart-donatelinq-submitformbutton" value="Click here" /></p>
	<script type="text/javascript">
		var redirectform=document.getElementById("dcart-donatelinq-redirectform");
		var submitted=false;
		redirectform.onSubmit=function(){if(submitted)return false;submitted=true;return true};
		window.setTimeout(function(){redirectform.submit()},3000)
	</script>
</form>
