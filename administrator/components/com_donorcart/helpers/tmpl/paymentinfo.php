<?php defined('_JEXEC') or die('Restricted Access');
$payment_info = json_decode($order->payment->infohash,true);

?>
<p><strong>Payment Amount</strong>: <?=$order->order_total?>
	<br /><strong>Payment Type</strong>: <?=JText::_('PLG_DONORCART_'.strtoupper($this->getName()).'_PAYMENT_SELECTOR_NAME')?><?php
	if($payment_info['pay_cc_fees'] && $this->params->get('cc_fees_option',1)==1): ?><br /><strong>Pay CC Fees?</strong>: <?=$payment_info['pay_cc_fees']?'Yes':'No'?><?php endif;
	if($payment_info['payment_frequency']): ?><br /><strong>Payment Frequency</strong>: <?=$payment_info['payment_frequency']?><?php endif;
?></p>