<?php defined('_JEXEC') or die("Restricted Access");
$params = JComponentHelper::getParams('com_donorcart');
$total = (isset($this->item->items) && is_array($this->item->items))?count($this->item->items):0;
$locked = false;
$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
if($ordermodel->getId()) {
	$order = $ordermodel->getItem();
	if($order->status=='submitted' || $order->status='complete') $locked = true;
}
?>

<table class="dcart-table">
	<thead>
		<tr>
			<th colspan='3'>
				<strong id='dcart-title'><?=JText::_('COM_DONORCART_CART_TITLE')?></strong> (<?=$total?> <?=JText::_(($total == 1)?'COM_DONORCART_ITEM':'COM_DONORCART_ITEMS') ?>)
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan='3'>
				<span class='dcart-subtotal'><?=JText::_('COM_DONORCART_SUBTOTAL')?>: <strong>$<?=number_format($this->item->subtotal, 2)?></strong></span>
				<?php if($total > 0): ?>
					<div class="dcart-cartbuttons">
						<a class="btn btn-warning dcart-link<?=($locked?' order-locked':'')?>" href="<?php echo JRoute::_('index.php?option=com_donorcart&view=cart&task=empty&format=raw&'.JSession::getFormToken().'=1'); ?>"><?=JText::_('COM_DONORCART_EMPTY_CART')?></a>
						<a class="btn btn-success" href="<?=JRoute::_('index.php?option=com_donorcart&view=checkout',false,$params->get('ssl_mode')==2?1:null)?>"><?=JText::_('COM_DONORCART_CHECKOUT_BUTTON_TEXT')?></a>
					</div>
				<?php endif; ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php if($total > 0):
			foreach($this->item->items as $id => $item): ?>
				<tr>
					<td class='dcart-item-qty'><?=$item->qty?></td>
					<td class='dcart-item-name'>
						<?php if($item->url): ?>
							<a href='<?=$item->url?>'><?=$item->name?></a>
						<?php else: ?>
							<?= $item->name ?>
						<?php endif; ?>
					</td>
					<td class='dcart-item-price'>
						<span>$<?=number_format($item->price, 2)?></span>
						<a class='dcart-remove dcart-link<?=($locked?' order-locked':'')?>' href='<?=JRoute::_('index.php?option=com_donorcart&view=cart&task=remove&item='.$id.'&format=raw&'.JSession::getFormToken().'=1')?>'><?=JText::_('COM_DONORCART_REMOVE_ITEM')?></a>
					</td>
				</tr>
			<?php endforeach;
		else: ?>
			<tr>
				<td id='dcart-empty' colspan='3'><?=JText::_('COM_DONORCART_CART_IS_EMPTY')?></td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>