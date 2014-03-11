<?php defined('_JEXEC') or die("Restricted Access");

$shipto_option_flag = $this->params->get('shipto_option');
$billto_option_flag = $this->params->get('billto_option');
$recurring_flag = $this->params->get('allow_recurring_donations',0);
?>
<div id="donorcart_checkout_container">
	<h2><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_REVIEW')?></h2>
	<?php if($this->item->cart_id && $this->item->cart && is_object($this->item->cart) && $this->item->cart->items && is_array($this->item->cart->items) && !empty($this->item->cart->items)): ?>
		<h3>Order Items:</h3>
		<table>
			<thead><tr>
					<th>Qty</th>
					<th>Name</th>
					<th>Price</th>
					<th>Subtotal</th>
				</tr></thead>
			<tbody><?php foreach($this->item->cart->items as $item): ?><tr>
					<td><?=$item->qty?></td>
					<td><?=(($item->url)?'<a href="'.$item->url.'">'.$item->name.'</a>':$item->name)?></td>
					<td>$<?=number_format($item->price,2)?></td>
					<td>$<?=number_format($item->qty*$item->price,2)?></td>
					</tr><?php endforeach; ?></tbody>
		</table>
	<?php endif; ?>
	<p><strong>SubTotal</strong>: $<?=number_format($this->item->cart->subtotal,2)?><br /><strong>Order Total</strong>: $<?=number_format($this->item->order_total,2)?></p>
	<?php if($recurring_flag==1) { ?>
		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_RECURRING')?></h3>
		<div><?=JText::_('COM_DONORCART_CHECKOUT_RECURRING_'.strtoupper(str_replace(array('-',' '),'_',$this->item->recurring_frequency)))?></div>
	<?php } ?>
	<?php if(!$this->user->id && $this->params->get('require_email_for_guest_checkout',false)): ?>
		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_GUEST_EMAIL')?></h3>
		<div><?=$this->item->email?></div>
	<?php endif; ?>
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

		<?php
		$paymentinfo = '';
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onDisplayPaymentInfo', array($this->item, $this->params, $this->item->payment_name));
		foreach($results as $result):
			if(is_string($result)) {
				$paymentinfo .= $result;
			}
		endforeach;
		if(!empty($paymentinfo)):
			echo '<h3>'.JText::_('COM_DONORCART_CHECKOUT_HEADING_PAYMENT_INFO').'</h3>'.$paymentinfo;
		endif;
		?>

		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_SPECIAL_INSTR')?></h3>
		<div><?=($this->item->special_instr?$this->item->special_instr:JText::_('COM_DONORCART_CHECKOUT_NO_SPECIAL_INSTR'))?></div>

		<form method="POST" class="donorcart_action_form" action="<?=JRoute::_('index.php?option=com_donorcart')?>">
			<input type="hidden" name="view" value="checkout" />
			<input type="hidden" name="format" value="raw" />
			<input type="hidden" name="task" value="" />
			<?=JHtml::_('form.token')?>
			<input type="submit" value="<?=JText::_('COM_DONORCART_CHECKOUT_ACTION_REVISE')?>" />
			<input type="button" onclick="this.form['task'].value='confirm';jQuery(this.form).submit();return false;" value="<?=JText::_('COM_DONORCART_CHECKOUT_ACTION_CONFIRM')?>" />
		</form>

		<!--a href="<?=JRoute::_('index.php?option=com_donorcart&task=submit&'.JSession::getFormToken().'=1')?>">Confirm</a-->
	</div>
</div>