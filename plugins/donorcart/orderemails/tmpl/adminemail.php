<?php defined('_JEXEC') or die('Restricted Access');
if(!isset($order) || !is_object($order) || !$order->status=='complete') return;
$config = JFactory::getConfig();
$sitename = $config->get('sitename');
if($order->dedication):
	$dedication_data = json_decode($order->dedication,true);
	$dedication_name = isset($dedication_data['name'])?$dedication_data['name']:'<<no name>>';
	$dedication_email = isset($dedication_data['email'])?$dedication_data['email']:'<<no email>>';
	$dedication_note = isset($dedication_data['note'])?$dedication_data['note']:'<<no note>>';
endif;

if($is_html) { ?>
	<h1>You have received a new donation for <?=$sitename?>.</h1>
	<?php if($order->dedication): ?>
		<p>This order was dedicated to <?=htmlspecialchars($dedication_name)?> (<?=htmlspecialchars($dedication_email)?>) with the following note:<br /><?=htmlspecialchars($dedication_note)?></p>
	<?php endif; ?>
	<p>The order details are as follows:<br />
		<strong>Order Total</strong>: $<?=number_format($order->order_total,2)?><br />
		<strong>Order Date</strong>: <?=date('m-d-Y',strtotime($order->completed_on))?><br />
		<strong>Payment Frequency:</strong>: <?=$order->recurring_frequency?><br />
		<strong>Payment Method:</strong> <?=$order->payment_name?><br />
		<strong>Special Instructions</strong>: <?=htmlentities($order->special_instr)?>
	</p>
	<h3>Cart:</h3>
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
				<td>$<?=number_format($item->price,2)?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php /* <h3>Payment Information: </h3> */ ?>
<?php } else { ?>
You have received a new donation for <?=$sitename?>.

<?php if($order->dedication): ?>
This order was dedicated to <?=$dedication_name?> (<?=$dedication_email?>) with the following note:
<?=$dedication_note?>

<?php endif; ?>
The order details are as follows:
Order Total: $<?=number_format($order->order_total,2)?>

Order Date: <?=date('m-d-Y',strtotime($order->completed_on))?>

Payment Frequency: <?=$order->recurring_frequency?>

Payment Method: <?=$order->payment_name?>

Special Instructions: <?=$order->special_instr?>


Cart: Qty x Name (Price)
<?php foreach($order->cart->items as $item): ?>
<?=$item->qty.' x '.$item->name.' ($'.number_format($item->price,2).')'?>

<?php endforeach ?>
<?php }