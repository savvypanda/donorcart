<?php defined('_JEXEC') or die('Restricted Access');

$allow_recurring_donations = $params->get('allow_recurring_donations',0);
$recurring_options = $this->_get_recurring_options();
if($allow_recurring_donations != 0 && count($recurring_options) > 0) {
	if(count($recurring_options)==1) { ?>
		<input type="hidden" name="<?=$this->getName()?>_payment_frequency" value="<?=$recurring_options[0]?>">
	<?php } else {
		$payment = isset($order->payment)?$order->payment:false;
		$payment_info = $payment?json_decode($payment->infohash,true):array();
		$selected_frequency = array_key_exists('payment_frequency',$payment_info)?$payment_info['payment_frequency']:'';
		?>
		<div class="field select">
			<label for="<?=$this->getName()?>_payment_frequency"><?=JText::_('PLG_DONORCART_'.strtoupper($this->getName()).'_RECURRING_FREQUENCY_LABEL')?></label>
			<select name="<?=$this->getName()?>_payment_frequency" id="<?=$this->getName()?>_payment_frequency">
				<?php foreach($recurring_options as $option) { ?>
					<option value="<?=$option?>"<?=(($option==$selected_frequency)?' selected="selected"':'')?>><?=JText::_('COM_DONORCART_CHECKOUT_RECURRING_'.strtoupper(str_replace(array('-',' '),'_',$option)))?></option>
				<?php } ?>
			</select>
		</div>
	<?php }
}