<?php defined('_JEXEC') or die('Restricted Access');
if(!defined($order) || !is_object($order) || !$order->status=='complete') return;
$config = JFactory::getConfig();
$sitename = $config->getValue('config.sitename');

if($is_html) { ?>
	<h1>Thank you for your donation.</h1>
	<p>Your order details are as follows:<br />
		<strong>Order Total</strong>: <?=$order->order_total?><br />
		<strong>Recurring:</strong>: <?=(($order->cart->recurring)?'Recurring':'One Time')?><br />
		<strong>Special Instructions</strong>: <?=htmlentities($order->cart->special_instr)?>
	</p>
	<h3>Your Cart:</h3>
	<table>
		<tbody>
			<tr>
				<td>SKU</td>
				<td>Name</td>
				<td>Qty</td>
				<td>Price</td>
			</tr>
			<?php foreach($order->cart->items as $item): ?>
				<tr>
					<td><?=$item->sku?></td>
					<td><?=$item->name?></td>
					<td><?=$item->qty?></td>
					<td><?=$item->price?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php /* <h3>Payment Information: </h3> */ ?>
	<p>If you have any questions about your order, please let us know.</p><p><br /></p><p>Sincerely,<br />The <?=$sitename?> team</p>
<?php } else { ?>
Thank you for your donation!

Your order details are as follows:
Order Total: <?=$order->order_total?>
Recurring: <?=(($order->cart->recurring)?'Recurring':'One Time')?>
Special Instructions: <?=$order->cart->special_instr?>

Cart:
Qty x Name (Price)<?php foreach($order->cart->items as $item): ?>
	<?=$item->qty.' x '.$item->name.' ($'.$item->price.')'?><?php endforeach ?>
<?php /*
Payment Information: */ ?>
If you have any questions about your order, please let us know.

Sincerely,

The <?=$sitename?> team
<?php }