<?php defined('_JEXEC') or die('Restricted Access');
$payment_info = json_decode($order->payment->infohash,true);

?>
<p><strong>Payment Amount</strong>: <?=$order->order_total?>
	<?php if($payment_info['pay_cc_fees'] && $this->params->get('cc_fees_option',1)==1): ?><br /><strong>Pay CC Fees?</strong>: <?=$payment_info['pay_cc_fees']?'Yes':'No'?><?php endif;
	if(isset($payment_info['x_method'])): ?><br /><strong>Payment Type</strong>: <?=($payment_info['x_method']=='CC'?'Credit Card':'E-Check')?><?php endif;
	if(isset($payment_info['x_response_reason_text'])): ?><br /><strong>Payment Status</strong>: <?=$payment_info['x_response_reason_text']?><?php endif;

	/*
	if(isset($payment_info['name_on_account']) && !empty($payment_info['name_on_account'])): ?><br /><strong>Name on Account</strong>: <?=$payment_info['name_on_account']?><?php endif;
	if(isset($payment_info['lastfour']) && !empty($payment_info['lastfour'])): ?><br /><strong>Last 4 Digits of Account</strong>: <?=$payment_info['lastfour']?><?php endif;
	if(isset($payment_info['Email']) && !empty($payment_info['Email'])): ?><br /><strong>Email</strong>: <?=$payment_info['Email']?><?php endif;
	if(isset($payment_info['Special_Instructions']) && !empty($payment_info['Special_Instructions'])): ?><br /><strong>Special Instructions</strong>: <?=htmlentities($payment_info['Special_Instructions'])?><?php endif; ?>
	*/
	?>
</p>