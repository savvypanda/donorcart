<?php defined('_JEXEC') or die('Restricted Access');

$payment = isset($order->payment)?$order->payment:false;
$payment_info = $payment?json_decode($payment->infohash,true):array();

$allow_recurring_donations = $params->get('allow_recurring_donations',0);
$recurring_options = $this->_get_recurring_options();
$selected_frequency = array_key_exists('payment_frequency',$payment_info)?$payment_info['payment_frequency']:'';

$cc_fee_option = $this->params->get('pay_cc_fee');
$cc_fee_type = $this->params->get('cc_fee_type','percent');
$cc_fee_amount = $this->params->get('cc_fee_amount',0);
$cc_fee_total = $this->_calc_cc_processing_fee($order);
if(!is_numeric($cc_fee_amount)) {
	$cc_fee_amount = 0;
} else {
	$cc_fee_amount = round($cc_fee_amount,2);
}


if($allow_recurring_donations==0 || count($recurring_options)==0) { //the user can only select one-time donations ?>
	<input type="hidden" name="<?=$this->getName()?>_payment_frequency" value="One Time">
<?php } elseif($allow_recurring_donations==2) {
	//if all donations are required to be recurring
	if(count($recurring_options)==1) {
		//force the payment frequency to the only option
		reset($recurring_options); ?>
		<input type="hidden" name="<?=$this->getName()?>_payment_frequency" value="<?=key($recurring_options)?>">
	<?php } else {
		//we have more than one option, which may be accomplished by a simple select list ?>
		<div class="field select">
			<label for="<?=$this->getName()?>_payment_frequency">Recurring Frequency: </label>
			<select name="<?=$this->getName()?>_payment_frequency" id="<?=$this->getName()?>_payment_frequency">
				<?php foreach($recurring_options as $value => $text) { ?>
					<option value="<?=$value?>"<?=(($value==$selected_frequency)?' selected="selected"':'')?>><?=$text?></option>
				<?php } ?>
			</select>
		</div>
	<?php }
} else {
	//the user may select between one-time and recurring donations
	if(count($recurring_options)==1) {
		//There is only one recurring option. No select list required.
		reset($recurring_options);
		$recurring_default = key($recurring_options);
		?>
		<input type="hidden" name="<?=$this->getName()?>_payment_frequency" value="One Time">
		<script type="text/javascript">
			(function($){
				var recurring_option = $('#donorcart_checkout_form input[name=recurring]');
				function update_recurring_option() {
					if(recurring_option.is(':checked')) {
						$('#donorcart_checkout_form input[name=<?=$this->getName()?>_payment_frequency]').val('$recurring_default');
					} else {
						$('#donorcart_checkout_form input[name=<?=$this->getName()?>_payment_frequency]').val('One Time');
					}
				}
				update_recurring_option();
				recurring_option.change(update_recurring_option);
			})(jQuery);
		</script>
	<?php
	} else {
		//The user has the option between a one-time donation or multiple recurring donation options
		?>
		<input type="hidden" name="<?=$this->getName()?>_payment_frequency" value="One Time">
		<div class="field select" id="dcart-donatelinq-frequencyouter">
			<label for="dcart-donatelinq-frequencyselector">Recurring Frequency: </label>
			<select id="dcart-donatelinq-frequencyselector">
				<?php foreach($recurring_options as $value => $text) { ?>
					<option value="<?=$value?>"<?=(($value==$selected_frequency)?' selected="selected"':'')?>><?=$text?></option>
				<?php } ?>
			</select>
		</div>
		<script type="text/javascript">
			(function($){
				var recurring_option = $('#donorcart_checkout_form input[name=recurring]');
				var recurring_selector = $('#dcart-donatelinq-frequencyselector');
				var recurring_container = $('#dcart-donatelinq-frequencyouter');
				var recurring_input = $('#donorcart_checkout_form input[name=<?=$this->getName()?>_payment_frequency]');
				function update_recurring_options() {
					if(recurring_option.is(':checked')) {
						recurring_container.show();
						recurring_input.val(recurring_selector.val());
					} else {
						recurring_container.hide();
						recurring_input.val('One Time');
					}
				}
				update_recurring_options();
				recurring_option.change(update_recurring_options);
				recurring_selector.change(update_recurring_options);
			})(jQuery);
		</script>
	<?php
	}
}

//display the "Pay CC Fees" option
if($cc_fee_option==1 && $cc_fee_amount > 0) {
	$pay_cc_fee = array_key_exists('pay_cc_fee',$payment_info)?$payment_info['pay_cc_fee']:false;
	$cc_fee_text = 'Pay the '.($cc_fee_type=='percent'?$cc_fee_amount.'% ($'.number_format($cc_fee_total,2).')':'$'.number_format($cc_fee_total,2)).' credit card processing fee.'; ?>
	<div class="field checkbox"><input type="checkbox" name="<?=$this->getName()?>_pay_cc_fee" id="anet-pay-cc-fee-option"<?=($pay_cc_fee?' checked="checked"':'')?> value="1"><label for="anet-pay-cc-fee-option"><?=$cc_fee_text?></label></div>
<?php }
?>
<p><small><em>After confirming your order, you will be redirected to our secure processing server to enter your payment details.</em></small></p>
<?php if($cc_fee_option==2 && $cc_fee_amount) { ?>
	<p><small><em>Your donation will include a <?=($cc_fee_type=='percent'?$cc_fee_amount.'% ($'.number_format($cc_fee_total,2).')':'$'.number_format($cc_fee_total,2))?> credit card processing fee.</em></small></p>
<?php }
