<?php defined('_JEXEC') or die('Restricted access'); ?>

<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="adminlist table table-striped">
		<thead>
			<tr>
				<td align="left"><?php if(!empty($this->sponsorlist)): echo JHtml::_('select.genericlist', $this->sponsorlist, 'scattr_sponsor_id', array('onchange'=>'Joomla.submitform();'), '', '', $this->input->get('scattr_sponsor_id',0)); endif; ?></td>
			</tr>
			<tr>
				<th></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_USERID', 'user_id', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_DATE', 'created_on', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_STATUS', 'status', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_RECURRING', 'recurring', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_TOTAL', 'order_total', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_EMAIL', 'email', $this->lists->order_Dir, $this->lists->order) ?></th>
				<th><?php echo JText::_('COM_DONORCART_ORDERS_FIELD_ITEMS'); ?></th>
				<th><?php echo JHTML::_('grid.sort', 'COM_DONORCART_ORDERS_FIELD_ID', 'id', $this->lists->order_Dir, $this->lists->order) ?></th>
			</tr>
			<tr>
				<td align="center"><input type="checkbox" name="checkall-toggle" value="" title="<?= JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" /></td>
				<td></td>
				<td align="left">
					<div style="text-wrap:none;display:inline-block;">
						<?= JText::_('COM_DONORCART_ORDERS_FILTER_FROM') ?>: <?php echo JHtml::_('calendar', $this->startdate, 'startdate', 'startdate', '%Y-%m-%d', array('readonly'=>'true','onchange'=>'document.adminForm.submit();')); ?>
					</div>
					<div style="text-wrap:none;display:inline-block;">
						<?= JText::_('COM_DONORCART_ORDERS_FILTER_TO') ?>: <?php echo JHtml::_('calendar', $this->enddate, 'enddate', 'enddate', '%Y-%m-%d', array('readonly'=>'true','onchange'=>'document.adminForm.submit();')); ?>
					</div>
				</td>
				<td><?=JHtml::_('select.genericlist', $this->statuslist, 'statusfilter', array('onchange'=>'document.adminForm.submit();'), '', '', $this->statusfilter)?></td>
				<td><?=JHtml::_('select.genericlist', array(''=>JText::_('COM_DONORCART_ORDERS_FIELD_RECURRING_SELECTONE'),0=>JText::_('COM_DONORCART_ORDERS_FIELD_RECURRING_ONETIME'),1=>JText::_('COM_DONORCART_ORDERS_FIELD_RECURRING_RECURRING')), 'recurringfilter', array('onchange'=>'document.adminForm.submit();'), '', '', $this->recurringfilter)?></td>
				<td></td>
				<td><input type="text" name="emailfilter" value="<?=$this->emailfilter?>" onchange="document.adminForm.submit();" /></td>
				<td><input type="text" name="itemfilter" value="<?=$this->itemfilter?>" onchange="document.adminForm.submit();" /></td>
				<td></td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php if($this->pagination->total > 0) echo $this->pagination->getListFooter() ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php if(!empty($this->items)):
				$m = 1;
				foreach($this->items as $i => $item):
					$m = 1-$m; ?>
					<tr class="row<?=$m ?>">
						<td align="center"><?=JHtml::_('grid.id',$i,$item->donorcart_order_id);?></td>
						<td><?=$item->user_id?></td>
						<td><?=$item->created_on?></td>
						<td><?=$item->status?></td>
						<td><?=(($item->cart_id && is_object($item->cart))?($item->cart->recurring?'Recurring':'One Time'):'')?></td>
						<td><?=$item->order_total?></td>
						<td><?=$item->email?></td>
						<td><?=(($item->cart_id && is_object($item->cart))?DonorcartHelperFormat::formatItems($item->cart->items):'')?></td>
						<td><a href="index.php?option=com_donorcart&view=order&id=<?=$item->donorcart_order_id?>"><?=$item->donorcart_order_id?></a></td>
					</tr>
				<?php endforeach;
			else: ?>
				<tr>
					<td colspan="20">
						<?php echo  JText::_('COM_DONORCART_COMMON_NORECORDS') ?>
					</td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="option" id="option" value="com_donorcart" />
	<input type="hidden" name="view" id="view" value="orders" />
	<input type="hidden" name="task" id="task" value="browse" />
	<input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" id="filter_order" value="<?php echo $this->lists->order ?>" />
	<input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="<?php echo $this->lists->order_Dir ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>