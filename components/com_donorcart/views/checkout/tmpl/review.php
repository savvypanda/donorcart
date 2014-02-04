<?php defined('_JEXEC') or die("Restricted Access");

$shipto_option_flag = $this->params->get('shipto_option');
$billto_option_flag = $this->params->get('billto_option');
$recurring_flag = $this->params->get('allow_recurring_donations',0);
?>
<div id="donorcart_checkout_container">
	<?php if(!$this->user->id && $this->params->get('require_email_for_guest_checkout',false)): ?>
		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_GUEST_EMAIL')?></h3>
		<div><?=$this->item->email?></div>
	<?php endif; ?>
	<?php if($recurring_flag==1) { ?>
		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_RECURRING')?></h3>
		<div><?=JText::_($this->item->recurring?'COM_DONORCART_CHECKOUT_RECURRING_YES':'COM_DONORCART_CHECKOUT_RECURRING_NO')?></div>
	<?php } ?>
	<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_REVIEW')?></h3>
	<div id="donorcart_review">
		<?php if($shipto_option_flag != 0): ?>
			<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_SHIPPING_ADDRESS')?></h3>
			<?php if($this->item->shipping_address_id):
				$address = $this->item->shipping_address;
				$addresstype = '';
				switch($address->address_type){
					case 'house': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_HOUSE'); break;
					case 'apartment': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_APARTMENT'); break;
					case 'box': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_BOX'); break;
					case 'business': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_BUSINESS'); break;
					case 'other': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_OTHER'); break;
					default: $addressstype=''; break;
				}
				$namearray = array();
				if(!empty($address->first_name)) $namearray[] = $address->first_name;
				if(!empty($address->middle_name)) $namearray[] = $address->middle_name;
				if(!empty($address->last_name)) $namearray[] = $address->last_name;

				echo '<ul>';
				if($addresstype): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE')?>: <?=$address->address_type?></li><?php endif;
				if(!empty($namearray)): ?><li><?=JText::_('COM_DONORCART_ADDRESS_NAME')?>: <?=implode(' ',$namearray)?></li><?php endif;
				if($address->business_name): ?><li><?=JText::_('COM_DONORCART_ADDRESS_BUSINESSNAME')?>: <?=$address->business_name?></li><?php endif;
				if($address->address1): ?><li><?=JText::_('COM_DONORCART_ADDRESS_ADDRESS1')?>: <?=$address->address1?></li><?php endif;
				if($address->address2): ?><li><?=JText::_('COM_DONORCART_ADDRESS_ADDRESS2')?>: <?=$address->address2?></li><?php endif;
				if($address->city): ?><li><?=JText::_('COM_DONORCART_ADDRESS_CITY')?>: <?=$address->city?></li><?php endif;
				if($address->state): ?><li><?=JText::_('COM_DONORCART_ADDRESS_STATE')?>: <?=$address->state?></li><?php endif;
				if($address->zip): ?><li><?=JText::_('COM_DONORCART_ADDRESS_ZIP')?>: <?=$address->zip?></li><?php endif;
				if($address->country): ?><li><?=JText::_('COM_DONORCART_ADDRESS_COUNTRY')?>: <?=$address->country?></li><?php endif;
			else: ?>
				<?=JText::_('COM_DONORCART_CHECKOUT_NO_SHIPPING_ADDRESS');?>
			<?php endif; ?>
		<?php endif; ?>

		<?php if($billto_option_flag != 0): ?>
			<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_BILLING_ADDRESS')?></h3>
			<?php if($this->item->billing_address_id):
				$address = $this->item->billing_address;
				$addresstype = '';
				switch($address->address_type){
					case 'house': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_HOUSE'); break;
					case 'apartment': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_APARTMENT'); break;
					case 'box': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_BOX'); break;
					case 'business': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_BUSINESS'); break;
					case 'other': $addresstype=JText::_('COM_DONORCART_ADDRESSTYPE_OTHER'); break;
					default: $addressstype=''; break;
				}
				$namearray = array();
				if(!empty($address->first_name)) $namearray[] = $address->first_name;
				if(!empty($address->middle_name)) $namearray[] = $address->middle_name;
				if(!empty($address->last_name)) $namearray[] = $address->last_name;

				echo '<ul>';
				if($addresstype): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE')?>: <?=$address->address_type?></li><?php endif;
				if(!empty($namearray)): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_NAME')?>: <?=implode(' ',$namearray)?></li><?php endif;
				if($address->business_name): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_BUSINESSNAME')?>: <?=$address->business_name?></li><?php endif;
				if($address->address1): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_ADDRESS1')?>: <?=$address->address1?></li><?php endif;
				if($address->address2): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_ADDRESS2')?>: <?=$address->address2?></li><?php endif;
				if($address->city): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_CITY')?>: <?=$address->city?></li><?php endif;
				if($address->state): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_STATE')?>: <?=$address->state?></li><?php endif;
				if($address->zip): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_ZIP')?>: <?=$address->zip?></li><?php endif;
				if($address->country): ?><li><?=JText::_('COM_DONORCART_ADDRESSTYPE_COUNTRY')?>: <?=$address->country?></li><?php endif;
			else: ?>
				<?=JText::_('COM_DONORCART_CHECKOUT_NO_SHIPPING_ADDRESS');?>
			<?php endif; ?>
		<?php endif; ?>

		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_PAYMENT_INFO')?></h3>
		<?php
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onDisplayPaymentInfo', array($this->item));
		foreach($results as $result):
			if(is_string($result)) echo $result;
		endforeach;
		?>
		<br />
		<br />

		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_SPECIAL_INSTR')?></h3>
		<div><?=($this->item->special_instr?$this->item->special_instr:JText::_('COM_DONORCART_CHECKOUT_NO_SPECIAL_INSTR'))?></div>

		<form method="POST" class="donorcart_action_form" action="<?=JRoute::_('index.php?option=com_donorcart')?>">
			<input type="hidden" name="view" value="checkout" />
			<input type="hidden" name="format" value="raw" />
			<input type="hidden" name="task" value="revise" />
			<?=JHtml::_('form.token')?>
			<input type="submit" value="<?=JText::_('COM_DONORCART_CHECKOUT_ACTION_REVISE')?>" />
			<input type="button" onclick="this.form.task='confirm';this.form.submit();" value="<?=JText::_('COM_DONORCART_CHECKOUT_ACTION_CONFIRM')?>" />
		</form>

		<a href="<?=JRoute::_('index.php?option=com_donorcart&task=submit&'.JSession::getFormToken().'=1')?>">Confirm</a>
	</div>
</div>