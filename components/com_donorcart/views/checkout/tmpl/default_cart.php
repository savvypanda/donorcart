<?php defined('_JEXEC') or die("Restricted Access");
$total = (!empty($this->item->cart->items) && is_array($this->item->cart->items))?count($this->item->cart->items):0;
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
				<span id='dcart-subtotal'><?=JText::_('COM_DONORCART_SUBTOTAL')?>:<strong> $<?=number_format($this->item->cart->subtotal, 2)?></strong></span>
				<div class="dcart-cartbuttons">
					<a class="btn btn-warning" href="<?php echo JRoute::_('index.php?option=com_donorcart&task=emptyCart&'.JSession::getFormToken().'=1'); ?>"><?=JText::_('COM_DONORCART_EMPTY_CART')?></a>
				</div>
			</td>
		</tr>
	</tfoot>
	<tbody>
		<?php if($total > 0):
			foreach($this->item->cart->items as $id => $item): ?>
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
							<a class='dcart-remove' href='<?=JRoute::_('index.php?option=com_donorcart&task=remove&item='.$id.'&'.JSession::getFormToken().'=1')?>'><?=JText::_('COM_DONORCART_REMOVE_ITEM')?></a>
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