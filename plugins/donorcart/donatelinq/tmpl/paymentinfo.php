<?php defined('_JEXEC') or die('Restricted Access');

if(!is_object($order->payment)) return;
$payment_info = json_decode($order->payment->infohash,true);
if(empty($payment_info) || !isset($payment_info['paytype'])) return;

$allow_recurring_donations = $params->get('allow_recurring_donations',0);
$recurring_options = (($allow_recurring_donations==1)?1:0) + count($this->_get_recurring_options());

?>
<p><strong>Payment Amount</strong>: <?=$order->order_total?>
	<?php if($payment_info['pay_cc_fees'] && $this->params->get('cc_fees_option',1)==1): ?><br /><strong>Pay CC Fees?</strong>: <?=$payment_info['pay_cc_fees']?'Yes':'No'?><?php endif;
	if($recurring_options > 1 && ($payment_info['selFrequency'])): ?><br /><strong>Payment Frequency</strong>: <?=$payment_info['selFrequency']; ?><?php endif;
	if(isset($payment_info['paytype'])):
		if($payment_info['paytype']=='EFT') { ?><br /><strong>Payment Type</strong>: EFT<?php }
		elseif($payment_info['paytype']=='CC') { ?><br /><strong>Payment Type</strong>: Credit/Debit<?php }
	endif;
	if(isset($payment_info['name_on_account']) && !empty($payment_info['name_on_account'])): ?><br /><strong>Name on Account</strong>: <?=$payment_info['name_on_account']?><?php endif;
	if(isset($payment_info['lastfour']) && !empty($payment_info['lastfour'])): ?><br /><strong>Last 4 Digits of Account</strong>: <?=$payment_info['lastfour']?><?php endif;
	if(isset($payment_info['Email']) && !empty($payment_info['Email'])): ?><br /><strong>Email</strong>: <?=$payment_info['Email']?><?php endif;
	if(isset($payment_info['Special_Instructions']) && !empty($payment_info['Special_Instructions'])): ?><br /><strong>Special Instructions</strong>: <?=htmlentities($payment_info['Special_Instructions'])?><?php endif; ?>
</p>