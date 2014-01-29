<?php defined('_JEXEC') or die("Restricted Access");
$total = (isset($this->item->items) && is_array($this->item->items))?count($this->item->items):0;
?>

<form name='dcart_cart' method='post' action='<?=JRoute::_('index.php?option=com_donorcart')?>'>
	<fieldset>
		<?= JHTML::_('form.token') ?>
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
						<span id='dcart-subtotal'><?=JText::_('COM_DONORCART_SUBTOTAL')?>:<strong> $<?=number_format($this->subtotal, 2)?></strong></span>
						<input type='submit' id='dcart-checkout' name='dcartCheckout' class='dcart-button' value='<?=JText::_('COM_DONORCART_CHECKOUT_BUTTON_TEXT')?>' />
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if($total > 0):
					foreach($this->item->items as $id => $item): ?>
						<tr>
							<td class='dcart-item-qty'>
								<?=$item->qty?>
								<input name='dcartItem[<?=$id?>][qty]' size='2' type='hidden' value='<?=$item->qty?>' />
							</td>
							<td class='dcart-item-name'>
								<?php if($item->url): ?>
									<a href='<?=$item->url?>'><?=$item->name?></a>
								<?php else: ?>
									<?= $item->name ?>
								<?php endif; ?>
								<input name='dcartItem[<?=$id?>][name]' type='hidden' value='<?=$item->name?>' />
							</td>
							<td class='dcart-item-price'>
								<span>$<?=number_format($item->price, 2)?></span><input name='dcartItem[<?=$id?>][price]' type='hidden' value='<?=$item->price?>' />
								<a class='dcart-remove dcart-link' href='<?=JRoute::_('index.php?option=com_donorcart&view=cart&task=remove&item='.$id.'&format=raw&template=system&'.JSession::getFormToken().'=1')?>'><?=JText::_('COM_DONORCART_REMOVE_ITEM')?></a>
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
		<div id='dcart-buttons'>
			<button onclick="dcartLoader('<?php echo JRoute::_('index.php?option=com_donorcart&view=cart&task=empty&format=raw&template=system&'.JSession::getFormToken().'=1'); ?>');return false;"><?=JText::_('COM_DONORCART_EMPTY_CART')?></button>
		</div>
	</fieldset>
</form>
