<?php defined('_JEXEC') or die('Restricted Access');

$allow_recurring_donations = $params->get('allow_recurring_donations',0);
$recurring_options = $this->_get_recurring_options();
$payment = isset($order->payment)?$order->payment:false;
$payment_info = $payment?json_decode($payment->infohash,true):array();
$selected_frequency = array_key_exists('selFrequency',$payment_info)?$payment_info['selFrequency']:'';

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
			<label for="<?=$this->getName()?>_payment_frequency"><?=JText::_('PLG_DONORCART_DONATELINQ_RECURRING_FREQUENCY_LABEL')?>: </label>
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
			<label for="dcart-donatelinq-frequencyselector"><?=JText::_('PLG_DONORCART_DONATELINQ_RECURRING_FREQUENCY_LABEL')?>: </label>
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

$this->_display_processing_fee_form($order, (array_key_exists('pay_cc_fee',$payment_info)?$payment_info['pay_cc_fee']:false)); ?>
<p><small><em><?=JText::_('PLG_DONORCART_DONATELINQ_REDIRECT_AFTER_CONFIRM')?></em></small></p>