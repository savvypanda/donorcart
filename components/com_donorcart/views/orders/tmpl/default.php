<?php defined('_JEXEC') or die("Restricted Access");
$user = JFactory::getUser();
$order_link = JRoute::_('index.php?option=com_donorcart&view=order&id='.$order->donorcart_order_id);

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
			<td align="center"><a href="<?=$order_link?>"><?php echo $order->donorcart_order_id; ?></a></td>
			<td align="center"><a href="<?=$order_link?>"><?php echo date('m/d/Y',strtotime($order->completed_on?$order->completed_on:$order->created_on)); ?></a></td>
			<td align="right"><a href="<?=$order_link?>">$<?php echo number_format($order->order_total,2); ?></a></td>
			<td><?php if($order->payment_id):
				JPluginHelper::importPlugin('donorcart');
				$dispatcher = JDispatcher::getInstance();
				$results = $dispatcher->trigger('onDisplayPaymentInfo', array($order, $this->params, $order->payment_name));
				$paymenttext = '';
				foreach($results as $result):
					if(is_string($result)) $paymenttext.=$result;
				endforeach;
				if(!empty($paymenttext)) {
					echo $paymenttext;
				} else {
					echo "Payment details not recorded";
				}
			endif; ?></td>
		</tr><? endforeach; ?></tbody>
	</table>
	<?=$this->params->get('history_posttext','')?>
<?php endif; ?>