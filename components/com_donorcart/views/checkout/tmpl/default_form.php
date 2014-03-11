<?php defined('_JEXEC') or die("Restricted Access");

$recurring_option = $this->params->get('allow_recurring_donations',0);
$dedication_option = $this->params->get('allow_dedication_option',1);
$shipto_option_flag = $this->params->get('shipto_option');
$billto_option_flag = $this->params->get('billto_option');
$display_addresses = ($shipto_option_flag!=0 || $billto_option_flag!=0);
$display_both_options = ($shipto_option_flag!=0 && $billto_option_flag!=0);
if($display_addresses) {
	//set the defaults in an array, which we can override if this has already been set
	$billto = $shipto = array(
		'donorcart_address_id' => 0,
		'locked' => 0,
		'address_type' => 'house',
		'first_name' => '',
		'middle_name' => '',
		'last_name' => '',
		'business_name' => '',
		'address1' => '',
		'address2' => '',
		'city' => '',
		'state' => '',
		'zip' => '',
		'country' => 'USA'
	);
	if($this->item->shipping_address_id && is_object($this->item->shipping_address)) {
		$shippingaddress = get_object_vars($this->item->shipping_address);
		$shipto = array_merge($shipto, $shippingaddress);
	}
	if($this->item->billing_address_id && is_object($this->item->billing_address)) {
		$billingaddress = get_object_vars($this->item->billing_address);
		$billto = array_merge($billto, $billingaddress);
	}
}
if($dedication_option) {
	$dedication = $this->item->dedication;
	if($dedication) {
		$dedication_details = json_decode($dedication,true);
	}
	$dedication_name = ($dedication&&isset($dedication_details['name']))?$dedication_details['name']:'';
	$dedication_email = ($dedication&&isset($dedication_details['email']))?$dedication_details['email']:'';
	$dedication_text = ($dedication&&isset($dedication_details['text']))?$dedication_details['text']:'';
}
?>
<form name="donorcart_checkout_form" id="donorcart_checkout_form" class="donorcart_action_form" action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post">
	<?php if(!$this->user->id && $this->params->get('require_email_for_guest_checkout',false)): ?>
		<fieldset>
			<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_GUEST_CHECKOUT')?></legend>
			<p><?=JText::_('COM_DONORCART_CHECKOUT_EMAIL_REQUIRED')?></p>
			<div class="field text">
				<label for="dcart-guestcheckout-email"><?php echo JText::_('Email'); ?></label>
				<input type="text" name="email" id="dcart-guestcheckout-email" />
			</div>
		</fieldset>
	<?php endif; ?>

	<p><?=JText::_('COM_DONORCART_CHECKOUT_FORM_TOP_DESCRIPTION')?></p>

	<?php if($display_addresses): ?>
		<div class="addresses">
			<?php if($shipto_option_flag != 0): ?>
				<div class="shippingaddress">
					<fieldset>
						<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_SHIPPING_ADDRESS')?><?php echo ($shipto_option_flag==1)?JText::_('COM_DONORCART_CHECKOUT_ADDRESS_OPTIONAL'):JText::_('COM_DONORCART_CHECKOUT_ADDRESS_REQUIRED'); ?></legend>
						<?php dcart_display_address('shipto_', $shipto, $this->prior_addresses); ?>
					</fieldset>
				</div>
			<?php endif;
			if($billto_option_flag != 0): ?>
				<div class="billingaddress">
					<fieldset>
						<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_BILLING_ADDRESS')?><?php echo ($billto_option_flag==1)?'(Optional)':'(Required)'; ?></legend>
						<?php if($display_both_options): ?>
							<div class="field checkbox">
								<label for="dcart-use-same-address-for-billto"><?=JText::_('COM_DONORCART_HEADING_USE_SAME_ADDRESS_FOR_BOTH')?></label>
								<input type="checkbox" name="use_same_address_for_billto" id="dcart-use-same-address-for-billto" value="1"<?php if($this->item->shipping_address_id == $this->item->billing_address_id) echo ' checked="checked"'; ?> />
							</div>
						<?php endif; ?>
						<?php dcart_display_address('billto_', $billto, $this->prior_addresses); ?>
					</fieldset>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php
	$selector_html = $form_html = $recurring_html = $fees_html = '';

	JPluginHelper::importPlugin('donorcart');
	$dispatcher = JDispatcher::getInstance();
	$paymentSelectorResults = $dispatcher->trigger('onDisplayPaymentSelector', array($this->item, $this->params));
	$paymentFormResults = $dispatcher->trigger('onDisplayPaymentForm', array($this->item, $this->params));
	if($recurring_option==1) $recurringFormResults = $dispatcher->trigger('onDisplayRecurringOptions', array($this->item, $this->params));
	$feesFormResults = $dispatcher->trigger('onDisplayPaymentFees', array($this->item, $this->params));
	$numPayments = count($paymentSelectorResults);
	if($numPayments != count($paymentFormResults)|| ($recurring_option==1 && $numPayments != count($recurringFormResults)) || $numPayments != count($feesFormResults)) {
		$selector_html = '<h4>'.JText::_('COM_DONORCART_CHECKOUT_NO_PAYMENT_AVAILABLE').'</h4>';
	} else {
		for($i = 0; $i < $numPayments; $i++) {
			if(!$paymentSelectorResults[$i]) {
				unset($paymentSelectorResults[$i]);
				unset($paymentFormResults[$i]);
				if($recurring_option==1) unset($recurringFormResults[$i]);
				unset($feesFormResults[$i]);
			}
		}
		$numPayments = count($paymentSelectorResults);
		if($numPayments == 0) {
			$selector_html = '<h4>'.JText::_('COM_DONORCART_CHECKOUT_NO_PAYMENT_AVAILABLE').'</h4>';
		} elseif($numPayments == 1) {
			$selector_html = '<input type="hidden" name="payment_method" value="'.reset($paymentSelectorResults).'" />';
			$form_html = reset($paymentFormResults);
			if($recurring_option==1) $recurring_html = reset($recurringFormResults);
			$fees_html = reset($feesFormResults);
			/* if(!empty($form_html)): ?>
				<fieldset>
					<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_PAYMENT')?></legend>
					<?=$form_html?>
				</fieldset>
			<?php endif; */
		} else {
			$selector_html = '<h4>'.JText::_('COM_DONORCART_CHECKOUT_HEADING_SELECT_PAYMENT_METHOD').'</h4>';
			foreach($paymentSelectorResults as $i => $name):
				$selector_html .= '<div class="field secondary radio dcart-payment-method-selection"><input type="radio" name="payment_method" id="dcart-payment-method-'.$i.'" value="'.$name.'" /><label for="dcart-payment-method-'.$i.'">'.JText::_('PLG_DONORCART_'.strtoupper($name).'_PAYMENT_SELECTOR_NAME').'</label></div>';
				if(!empty($paymentFormResults[$i])) $form_html .= '<div class="dcart-payment-method-form method-dependent show-'.$name.'">'.$paymentFormResults[$i].'</div>';
				if($recurring_option==1 && !empty($recurringFormResults[$i])) $recurring_html .= '<div class="dcart-recurring-frequency-selector method-dependent show-'.$name.'">'.$recurringFormResults[$i].'</div>';
				if(!empty($feesFormResults[$i])) $fees_html .= '<div class="dcart-fees-option method-dependent show-'.$name.'">'.$feesFormResults[$i].'</div>';
			endforeach;
			/*?>
			<fieldset>
				<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_PAYMENT')?></legend>
				<?php foreach($paymentSelectorResults as $i => $name): ?>
					<div class="field radio dcart_payment_method_selection"><label for="dcart-payment-method-<?=$i?>"><?=$name?></label><input type="radio" name="payment_method" id="dcart-payment-method-<?=$i?>" value=<?=$name?> /></div>
					<div class="dcart_payment_method_form <?=$name?>"><?=$paymentFormResults[$i]?></div>
				<?php endforeach; ?>
			</fieldset>
		<?php */ }
	} ?>
	<fieldset>
		<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_PAYMENT_DETAILS')?></legend>
		<div class="field static">
			<label><?=JText::_('COM_DONORCART_CHECKOUT_ORDER_TOTAL')?></label>
			<p>$<?=$this->item->order_total?> - <a href="#" id="dcart-order-show-details-link">show details</a></p>
		</div>
		<div id="dcart-order-details-container" style="display:none;"><?php echo $this->loadTemplate('cart'); ?></div>
		<?=$selector_html?>
		<?=$fees_html?>
		<?php if($recurring_option==1) { //allow users to decide if they want it to be a recurring or a one-time donation ?>
			<div class="field checkbox">
				<input type="checkbox" name="recurring" id="dcart-checkbox-recurring" <?=(($this->item->recurring_frequency=='One Time')?'':'checked="checked" ')?>/>
				<label for="dcart-checkbox-recurring"><?=JText::_('COM_DONORCART_CHECKOUT_RECURRING_LABEL')?></label>
			</div>
			<?php if($recurring_html) { ?>
				<div class="show-recurring"><?=$recurring_html?></div>
			<?php }
		}
		if($dedication_option): ?>
			<div class="field checkbox">
				<input type="checkbox" name="dedicate" id="dcart-checkbox-dedicate" <?=($this->item->dedication?'checked="checked" ':'')?>/>
				<label for="dcart-checkbox-dedicate"><?=JText::_('COM_DONORCART_CHECKOUT_DEDICATE_LABEL')?></label>
			</div>
			<div class="field secondary text show-dedicate">
				<label for="dcart-dedicate-name"><?=JText::_('COM_DONORCART_CHECKOUT_DEDICATE_NAME')?></label>
				<input type="text" name="dedication_name" id="dcart-dedicate-name" value="<?=$dedication_name?>" />
			</div>
			<div class="field secondary text show-dedicate">
				<label for="dcart-dedicate-email"><?=JText::_('COM_DONORCART_CHECKOUT_DEDICATE_EMAIL')?></label>
				<input type="text" name="dedication_email" id="dcart-dedicate-email" value="<?=$dedication_email?>" />
			</div>
			<div class="field secondary textarea show-dedicate">
				<label for="dcart-dedicate-text"><?=JText::_('COM_DONORCART_CHECKOUT_DEDICATE_TEXT')?></label>
				<textarea name="dedication_text" id="dcart-dedicate-text"><?=$dedication_text?></textarea>
			</div>
		<?php endif; ?>
		<?=$form_html?>
		<div class="field textarea"><label for="dcart-special-instr"><?=JText::_('COM_DONORCART_CHECKOUT_SPECIAL_INSTR')?></label>
			<textarea name="special_instr" id="dcart-special-instr"><?=$this->item->special_instr?></textarea>
		</div>
	</fieldset>

	<!--fieldset>
		<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_SPECIAL_INSTR')?></legend>
	</fieldset-->

	<input type="submit" name="Submit" class="btn btn-success" value="<?=JText::_('COM_DONORCART_CHECKOUT_CONTINUE_ACTION')?>" />
	<input type="hidden" name="task" value="submit" />
	<input type="hidden" name="format" value="raw" />
	<?php echo JHTML::_('form.token'); ?>
</form>

<?php function dcart_display_address($prefix, $defaults, &$prior_addresses) {
	//first we have to come up with names for each of the addresses
	foreach($prior_addresses as $i => $address):
		if(isset($address->addressname)) break;
		if(!empty($address->business_name)) {
			$addressname = $address->business_name;
		} elseif(!empty($address->address1)) {
			$addressparts = array($address->address1);
			if(!empty($address->address2)) $addressparts[] = $address->address2;
			$addressname = implode(', ', $addressparts);
		} elseif(!empty($address->first_name) || !empty($address->last_name)) {
			$addressparts = array();
			if(!empty($address->first_name)) $addressparts[] = $address->first_name;
			if(!empty($address->middle_name)) $addressparts[] = $address->middle_name;
			if(!empty($address->last_name)) $addressparts[] = $address->last_name;
			$addressname = implode(', ', $addressparts);
		} else {
			//we do not want to use this address. It has no information!
			unset($prior_addresses[$i]);
			continue;
		}
		$prior_addresses[$i]->addressname = $addressname;
	endforeach;
	//now that the addresses are named, let's list the options
	if(!empty($prior_addresses)): ?>
		<?php foreach($prior_addresses as $address): ?>
			<div class="addressoption">
				<div class="field radio">
					<input type="radio" name="<?=$prefix?>id" id="dcart-<?=$prefix?>button-<?$address->donorcart_address_id?>" value="<?=$address->donorcart_address_id?>" <?php if($defaults['donorcart_address_id']==$address->donorcart_address_id) $prior_address_selected=true; echo('checked="checked"'); ?> /><label for="dcart-<?=$prefix?>button-<?=$address->donorcart_address_id?>"><?=JText::sprintf('COM_DONORCART_CHECKOUT_USE_THIS_ADDRESS',$address->addressname)?></label>
				</div>
				<?php
					switch($address->address_type){
						case 'house': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_HOUSE'); break;
						case 'apartment': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_APARTMENT'); break;
						case 'box': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_BOX'); break;
						case 'business': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_BUSINESS'); break;
						case 'other': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_OTHER'); break;
						default: $addresstype=''; break;
					}
					$namearray = array();
					if(!empty($address->first_name)) $namearray[] = $address->first_name;
					if(!empty($address->middle_name)) $namearray[] = $address->middle_name;
					if(!empty($address->last_name)) $namearray[] = $address->last_name;
				?>
				<div class="optiondrawer">
					<ul>
						<?php if($addresstype): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE')?>: <?=$address->address_type?></li><?php endif; ?>
						<?php if(!empty($namearray)): ?><li><?=JText::_('COM_DONORCART_ADDRESS_NAME')?>: <?=implode(' ',$namearray)?></li><?php endif; ?>
						<?php if($address->business_name): ?><li><?=JText::_('COM_DONORCART_ADDRESS_BUSINESSNAME')?>: <?=$address->business_name?></li><?php endif; ?>
						<?php if($address->address1): ?><li><?=JText::_('COM_DONORCART_ADDRESS_ADDRESS1')?>: <?=$address->address1?></li><?php endif; ?>
						<?php if($address->address2): ?><li><?=JText::_('COM_DONORCART_ADDRESS_ADDRESS2')?>: <?=$address->address2?></li><?php endif; ?>
						<?php if($address->city): ?><li><?=JText::_('COM_DONORCART_ADDRESS_CITY')?>: <?=$address->city?></li><?php endif; ?>
						<?php if($address->state): ?><li><?=JText::_('COM_DONORCART_ADDRESS_STATE')?>: <?=$address->state?></li><?php endif; ?>
						<?php if($address->zip): ?><li><?=JText::_('COM_DONORCART_ADDRESS_ZIP')?>: <?=$address->zip?></li><?php endif; ?>
						<?php if($address->country): ?><li><?=JText::_('COM_DONORCART_ADDRESS_COUNTRY')?>: <?=$address->country?></li><?php endif; ?>
					</ul>
				</div>
			</div>
		<?php endforeach;
	endif; ?>
	<div class="addressoption">
		<?php if(empty($prior_addresses)): ?>
			<input type="hidden" name="<?=$prefix?>id" value="<?=$defaults['donorcart_address_id']?>" />
		<?php else: ?>
		<div class="field radio">
			<input type="radio" name="<?=$prefix?>id" id="dcart-<?=$prefix?>button-new" value="<?=$defaults['donorcart_address_id']?>" <?php if(!$defaults['locked']) echo('checked="checked"'); ?>/><label for="dcart-<?=$prefix?>button-new"><?=JText::_('COM_DONORCART_CHECKOUT_CREATE_NEW_ADDRESS')?></label>
		</div>
		<div class="optiondrawer">
			<?php endif; ?>
			<div class="field select">
				<label for="dcart-<?=$prefix?>address-type"><?=JText::_('COM_DONORCART_ADDRESSTYPE')?></label>
				<select name="<?=$prefix?>address_type" id="dcart-<?=$prefix?>address-type">
					<option value="house"<?php if($defaults['address_type']=='house')echo(' selected="selected"');?>><?=JText::_('COM_DONORCART_ADDRESSTYPE_HOUSE')?></option>
					<option value="apartment"<?php if($defaults['address_type']=='apartment')echo(' selected="selected"');?>><?=JText::_('COM_DONORCART_ADDRESSTYPE_APARTMENT')?></option>
					<option value="box"<?php if($defaults['address_type']=='box')echo(' selected="selected"');?>><?=JText::_('COM_DONORCART_ADDRESSTYPE_BOX')?></option>
					<option value="business"<?php if($defaults['address_type']=='business')echo(' selected="selected"');?>><?=JText::_('COM_DONORCART_ADDRESSTYPE_BUSINESS')?></option>
					<option value="other"<?php if($defaults['address_type']=='other')echo(' selected="selected"');?>><?=JText::_('COM_DONORCART_ADDRESSTYPE_OTHER')?></option>
				</select>
			</div>
			<div class="field text">
				<label for="dcart-<?=$prefix?>first-name"><?=JText::_('COM_DONORCART_ADDRESS_FIRSTNAME')?></label>
				<input type="text" name="<?=$prefix?>first_name" value="<?=$defaults['first_name']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>middle_name"><?=JText::_('COM_DONORCART_ADDRESS_MIDDLENAME')?></label>
				<input type="text" name="<?=$prefix?>middle_name" value="<?=$defaults['middle_name']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>last_name"><?=JText::_('COM_DONORCART_ADDRESS_LASTNAME')?></label>
				<input type="text" name="<?=$prefix?>last_name" value="<?=$defaults['last_name']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>business_name"><?=JText::_('COM_DONORCART_ADDRESS_BUSINESSNAME')?></label>
				<input type="text" name="<?=$prefix?>business_name" value="<?=$defaults['business_name']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>address1"><?=JText::_('COM_DONORCART_ADDRESS_ADDRESS1')?></label>
				<input type="text" name="<?=$prefix?>address1" value="<?=$defaults['address1']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>address2"><?=JText::_('COM_DONORCART_ADDRESS_ADDRESS2')?></label>
				<input type="text" name="<?=$prefix?>address2" value="<?=$defaults['address2']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>city"><?=JText::_('COM_DONORCART_ADDRESS_CITY')?></label>
				<input type="text" name="<?=$prefix?>city" value="<?=$defaults['city']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>state"><?=JText::_('COM_DONORCART_ADDRESS_STATE')?></label>
				<input type="text" name="<?=$prefix?>state" value="<?=$defaults['state']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>zip"><?=JText::_('COM_DONORCART_ADDRESS_ZIP')?></label>
				<input type="text" name="<?=$prefix?>zip" value="<?=$defaults['zip']?>"/>
			</div>
			<div class="field text">
				<label for="<?=$prefix?>country"><?=JText::_('COM_DONORCART_ADDRESS_COUNTRY')?></label>
				<input type="text" name="<?=$prefix?>country" value="<?=$defaults['country']?>"/>
			</div>
			<?php if(!empty($prior_addresses)): ?>
		</div>
	<?php endif; ?>
	</div>
<?php
}
