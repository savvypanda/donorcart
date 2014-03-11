<?php defined('_JEXEC') or die('Restricted Access');

$cc_fee_option = $this->params->get('pay_cc_fee',false);
if($cc_fee_option):
	$cc_fee_total = $this->_calc_cc_processing_fee($order);
	if($cc_fee_total && $cc_fee_total > 0):
		$cc_fee_type = $this->params->get('cc_fee_type','percent');
		$cc_fee_amount = $this->params->get('cc_fee_amount',0);

		$payment = isset($order->payment)?$order->payment:false;
		$payment_info = $payment?json_decode($payment->infohash,true):array();

		$cc_fee_selected = array_key_exists('pay_cc_fee',$payment_info)?$payment_info['pay_cc_fee']:false;
		if(!is_numeric($cc_fee_amount)) {
			$cc_fee_amount = 0;
		} else {
			$cc_fee_amount = round($cc_fee_amount,2);
		}

		if($cc_fee_option==1) {
			$cc_fee_text = $cc_fee_type=='percent'?JText::sprintf('PLG_DONORCART_'.strtoupper($this->getName()).'_PAY_CC_FEE_PERCENT',$cc_fee_amount,$cc_fee_total):JText::sprintf('PLG_DONORCART_'.strtoupper($this->getName()).'_PAY_CC_FEE_FIXED',$cc_fee_total); ?>
			<div class="field checkbox"><input type="checkbox" name="<?=$this->getName()?>_pay_cc_fee" id="<?=$this->getName()?>-pay-cc-fee-option"<?=($cc_fee_selected?' checked="checked"':'')?> value="1"><label for="<?=$this->getName()?>-pay-cc-fee-option"><?=$cc_fee_text?></label></div>
		<?php } elseif($cc_fee_option==2) {
			$cc_fee_text = $cc_fee_type=='percent'?JText::sprintf('PLG_DONORCART_'.strtoupper($this->getName()).'_REQUIRE_CC_FEE_PERCENT',$cc_fee_amount,$cc_fee_total):JText::sprintf('PLG_DONORCART_'.strtoupper($this->getName()).'_REQUIRE_CC_FEE_FIXED',$cc_fee_total); ?>
			<p><small><em><?=$cc_fee_text?></em></small></p>
		<?php }
	endif;
endif;