<?php
defined('_JEXEC') or die();

// Load the Select helper class of our component
$params = JComponentHelper::getParams('com_donorcart');

// Joomla! editor object
$editor = JFactory::getEditor();
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<fieldset>
		<legend><?php echo JText::_('COM_DONORCART_ORDER_LEGEND_USER'); ?></legend>
		<?php if($this->item->user_id && ($order_user = JFactory::getUser($this->item->user_id))): ?>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_USER_ID_LABEL') ?></strong>: <?= $this->item->user_id ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_USERNAME_LABEL') ?></strong>: <?= $order_user->username ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_USER_EMAIL_LABEL') ?></strong>: <?= $order_user->email ?></p>
			<?php if($this->item->email != $order_user->email): ?>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_EMAIL_LABEL') ?></strong>: <?= $this->item->email ?></p>
			<?php endif; ?>
		<?php else: ?>
			<p><?= JText::_('COM_DONORCART_ORDER_NOUSER') ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_EMAIL_LABEL') ?></strong>: <?= $this->item->email ?></p>
		<?php endif; ?>
	</fieldset>

	<fieldset>
		<legend><?= JText::_('COM_DONORCART_ORDER_LEGEND_DETAILS') ?></legend>
		<p><strong><?= JText::_('COM_DONORCART_ORDER_ID_LABEL') ?></strong>: <?php echo $this->item->donorcart_order_id; ?></p>
		<p><strong><?= JText::_('COM_DONORCART_ORDER_STATUS_LABEL') ?></strong>: <?php echo $this->item->status; ?></p>
		<?php if($this->item->completed_on): ?><p><strong><?=JText::_('COM_DONORCART_ORDER_COMPLETED_ON_LABEL') ?></strong>: <?php echo $this->item->completed_on; ?></p><?php endif; ?>
		<p><strong><?= JText::_('COM_DONORCART_ORDER_ORDER_TOTAL_LABEL') ?></strong>: <?php echo $this->item->order_total; ?></p>
		<p><strong><?= JText::_('COM_DONORCART_ORDER_ORDER_RECURRING_LABEL') ?></strong>: <?php if($this->item->cart_id && is_object($this->item->cart)) echo JText::_(($this->item->cart->recurring)?'COM_DONORCART_ORDERS_FIELD_RECURRING_RECURRING':'COM_DONORCART_ORDERS_FIELD_RECURRING_ONETIME'); ?></p>
		<p><strong><?= JText::_('COM_DONORCART_ORDER_SPECIAL_INSTRUCTIONS_LABEL') ?></strong>: <?php echo $this->item->special_instr; ?></p>
		<?php if($this->item->viewtoken): ?>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_LINK_LABEL') ?></strong>: <?php echo str_replace('/administrator','',JRoute::_('index.php',true,($params->get('use_ssl',0)==0)?-1:1)).'?option=com_donorcart&view=order&id='.$this->item->donorcart_order_id.'&viewtoken='.$this->item->viewtoken; ?></p>
		<?php endif; ?>

		<h2><?= JText::_('COM_DONORCART_ORDER_CART_HEADING') ?></h2>
		<table>
			<thead>
				<tr>
					<th><?= JText::_('COM_DONORCART_ORDER_SKU_HEADING') ?></th>
					<th><?= JText::_('COM_DONORCART_ORDER_NAME_HEADING') ?></th>
					<th><?= JText::_('COM_DONORCART_ORDER_PRICE_HEADING') ?></th>
					<th><?= JText::_('COM_DONORCART_ORDER_QTY_HEADING') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if($this->item->cart_id && !empty($this->item->cart->items)):
					foreach($this->item->cart->items as $cart_item): ?>
						<tr>
							<td><?= $cart_item->sku ?></td>
							<td>
								<?php if($cart_item->url): ?>
									<a href="<?= $cart_item->url ?>"><?= $cart_item->name ?></a>
								<?php else:
									echo $cart_item->name;
								endif; ?>
							</td>
							<td><?= $cart_item->price ?></td>
							<td><?= $cart_item->qty ?></td>
						</tr>
					<?php endforeach;
				else: ?>
					<tr colspan="20"><?= JText::_('COM_DONORCART_ORDER_CART_IS_EMPTY') ?></tr>
				<?php endif; ?>
			</tbody>
		</table>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_DONORCART_ORDER_LEGEND_PAYMENT'); ?></legend>
		<?php if($this->item->payment_id):
			JPluginHelper::importPlugin('donorcart');
			$dispatcher = JDispatcher::getInstance();
			$results = $dispatcher->trigger('onDisplayPaymentInfo', array($this->item, $params, $this->item->payment_name));
			$paymenttext = '<p>Payment Name: '.$this->item->payment_name.'</p>';
			foreach($results as $result):
				if(is_string($result)) $paymenttext.=$result;
			endforeach;
			if(!empty($paymenttext)) {
				echo $paymenttext;
			} else {
				echo '<p>'.JText::_('COM_DONORCART_ORDER_NO_PAYMENT').'</p>';
			}
		else: ?>
			<p><?= JText::_('COM_DONORCART_ORDER_NO_PAYMENT') ?></p>
		<?php endif; ?>
	</fieldset>

	<fieldset>
		<legend><?= JText::_('COM_DONORCART_ORDER_LEGEND_ADDRESS') ?></legend>
		<?php if($this->item->shipping_address_id): ?>
			<h2><?= JText::_('COM_DONORCART_ORDER_SHIPPING_ADDRESS_HEADING') ?></h2>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_ADDRESS_TYPE_LABEL') ?></strong>: <?= $this->item->shipping_address->address_type ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_FIRST_NAME_LABEL') ?></strong>: <?= $this->item->shipping_address->first_name ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_MIDDLE_NAME_LABEL') ?></strong>: <?= $this->item->shipping_address->middle_name ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_LAST_NAME_LABEL') ?></strong>: <?= $this->item->shipping_address->last_name ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_BUSINESS_NAME_LABEL') ?></strong>: <?= $this->item->shipping_address->business_name ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_ADDRESS1_LABEL') ?></strong>: <?= $this->item->shipping_address->address1 ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_ADDRESS2_LABEL') ?></strong>: <?= $this->item->shipping_address->address2 ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_CITY_LABEL') ?></strong>: <?= $this->item->shipping_address->city ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_STATE_LABEL') ?></strong>: <?= $this->item->shipping_address->state ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_ZIP_LABEL') ?></strong>: <?= $this->item->shipping_address->zip ?></p>
			<p><strong><?= JText::_('COM_DONORCART_ORDER_COUNTRY_LABEL') ?></strong>: <?= $this->item->shipping_address->country ?></p>
		<?php else: ?>
			<p><?= JText::_('COM_DONORCART_ORDER_BILLING_ADDRESS_HEADING') ?></p>
		<?php endif; ?>
		<?php if($this->item->billing_address_id): ?>
			<h2><?= JText::_('COM_DONORCART_ORDER_NO_BILLING') ?></h2>
			<?php if($this->item->shipping_address_id == $this->item->billing_address_id): ?>
				<p><?= JText::_('COM_DONORCART_ORDER_BILLING_IS_SAME_AS_SHIPPING') ?></p>
			<?php else: ?>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_ADDRESS_TYPE_LABEL') ?></strong>: <?= $this->item->billing_address->address_type ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_FIRST_NAME_LABEL') ?></strong>: <?= $this->item->billing_address->first_name ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_MIDDLE_NAME_LABEL') ?></strong>: <?= $this->item->billing_address->middle_name ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_LAST_NAME_LABEL') ?></strong>: <?= $this->item->billing_address->last_name ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_BUSINESS_NAME_LABEL') ?></strong>: <?= $this->item->billing_address->business_name ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_ADDRESS1_LABEL') ?></strong>: <?= $this->item->billing_address->address1 ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_ADDRESS2_LABEL') ?></strong>: <?= $this->item->billing_address->address2 ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_CITY_LABEL') ?></strong>: <?= $this->item->billing_address->city ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_STATE_LABEL') ?></strong>: <?= $this->item->billing_address->state ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_ZIP_LABEL') ?></strong>: <?= $this->item->billing_address->zip ?></p>
				<p><strong><?= JText::_('COM_DONORCART_ORDER_COUNTRY_LABEL') ?></strong>: <?= $this->item->billing_address->country ?></p>
			<?php endif; ?>
		<?php else: ?>
			<p><?= JText::_('COM_DONORCART_ORDER_NO_BILLING') ?></p>
		<?php endif; ?>
	</fieldset>

	<input type="hidden" name="option" value="com_donorcart" />
	<input type="hidden" name="view" value="order" />
	<input type="hidden" name="id" value="<?= $this->item->donorcart_order_id ?>" />
	<input type="hidden" name="task" value="" />
	<?= JHtml::_('form.token'); ?>
</form>
