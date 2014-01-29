<?php defined('_JEXEC') or die("Restricted Access");

$user = JFactory::getUser();
if(empty($user->id)): ?>
	<h2>Donation History</h2>
	<p>You must be logged in to view your donation history</p>
<?php else: ?>
	<h2>Donation History</h2>
	<?=$this->params->get('history_pretext','')?>
	<table border=1 cellpadding=5 width=100%>
		<thead><tr>
			<th nowrap>Order ID</th>
			<th>Date</th>
			<th nowrap>Total</th>
			<th>Payment Information</th>
		</tr></thead>
		<tbody><? foreach ($this->items as $order) : ?><tr>
			<td align="center"><?php echo $order->donorcart_order_id; ?></td>
			<td align="center"><a href="<?=JRoute::_('index.php?option=com_donorcart&view=order&id='.$order->donorcart_order_id)?>"><?php echo date('m/d/Y',strtotime($order->created_on)); ?></a></td>
			<td align="right">$<?php echo number_format($order->order_total,2); ?></td>
			<td><?php
				JPluginHelper::importPlugin('donorcart');
				$dispatcher = JDispatcher::getInstance();
				$results = $dispatcher->trigger('onDisplayPaymentInfo', array($order));
				$paymenttext = '';
				foreach($results as $result):
					if(is_string($result)) $paymenttext.=$result;
				endforeach;
				if(!empty($paymenttext)) {
					echo $paymenttext;
				} else {
					echo "Payment details not recorded";
				}
			?></td>
		</tr><? endforeach; ?></tbody>
	</table>
	<?=$this->params->get('history_posttext','')?>
<?php endif; ?>