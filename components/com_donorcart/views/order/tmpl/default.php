<?php defined('_JEXEC') or die('Restricted Access');
$user = JFactory::getUser();
?>

<div class="checkout">
<h1 class="componentheading">Donation Details</h1>
<?=$this->params->get('details_pretext','')?>
<div><?php if($user->id): ?><a href="<?=JRoute::_('index.php?option=com_donorcart&view=orders')?>">&lt;&lt; Back to Donation History</a> <? /* |
    <?php if (JRequest::getString("format")=="print"):?>
        <a href="javascript:window.print()">Send to printer</a> */ ?>
	<?php endif; ?>

	<h3>Order Information</h3>
	<ul>
		<li>Order Date: <?=date("m/d/Y",strtotime($this->item->created_on))?></li>
		<li>Email: <?=$this->item->email?></li>
		<li>Status: <?=$this->item->status?></li>
		<li>Order Total: <?=$this->item->order_total?></li>
		<li>Special Instructions: <?=$this->item->special_instr?></li>
	</ul>

	<?php if($this->item->shipping_address_id && is_object($this->item->shipping_address)): ?>
		<h3>Shippping Address:</h3> <?
			$address = $this->item->shipping_address;
			$addresstype = '';
			switch($address->address_type){
				case 'house': $addresstype='House'; break;
				case 'apartment': $addresstype='Apartment'; break;
				case 'box': $addresstype='PO Box'; break;
				case 'business': $addresstype='Business'; break;
				case 'other': $addresstype='Other'; break;
				default: $addressstype=''; break;
			}
			$namearray = array();
			if(!empty($address->first_name)) $namearray[] = $address->first_name;
			if(!empty($address->middle_name)) $namearray[] = $address->middle_name;
			if(!empty($address->last_name)) $namearray[] = $address->last_name;

			echo '<ul>';
			if($addresstype): ?><li>Address Type: <?=$address->address_type?></li><?php endif;
			if(!empty($namearray)): ?><li>Name: <?=implode(' ',$namearray)?></li><?php endif;
			if($address->business_name): ?><li>Business Name: <?=$address->business_name?></li><?php endif;
			if($address->address1): ?><li>Address: <?=$address->address1?></li><?php endif;
			if($address->address2): ?><li>Address: <?=$address->address2?></li><?php endif;
			if($address->city): ?><li>City: <?=$address->city?></li><?php endif;
			if($address->state): ?><li>State: <?=$address->state?></li><?php endif;
			if($address->zip): ?><li>Zip: <?=$address->zip?></li><?php endif;
			if($address->country): ?><li>Country: <?=$address->country?></li><?php endif;
		?>
	<?php endif; ?>

	<?php if($this->item->billing_address_id && is_object($this->item->billing_address)): ?>
		<h3>Billing Address:</h3> <?
			$address = $this->order->billing_address;
			$addresstype = '';
			switch($address->address_type){
				case 'house': $addresstype='House'; break;
				case 'apartment': $addresstype='Apartment'; break;
				case 'box': $addresstype='PO Box'; break;
				case 'business': $addresstype='Business'; break;
				case 'other': $addresstype='Other'; break;
				default: $addressstype=''; break;
			}
			$namearray = array();
			if(!empty($address->first_name)) $namearray[] = $address->first_name;
			if(!empty($address->middle_name)) $namearray[] = $address->middle_name;
			if(!empty($address->last_name)) $namearray[] = $address->last_name;

			echo '<ul>';
			if($addresstype): ?><li>Address Type: <?=$address->address_type?></li><?php endif;
			if(!empty($namearray)): ?><li>Name: <?=implode(' ',$namearray)?></li><?php endif;
			if($address->business_name): ?><li>Business Name: <?=$address->business_name?></li><?php endif;
			if($address->address1): ?><li>Address: <?=$address->address1?></li><?php endif;
			if($address->address2): ?><li>Address: <?=$address->address2?></li><?php endif;
			if($address->city): ?><li>City: <?=$address->city?></li><?php endif;
			if($address->state): ?><li>State: <?=$address->state?></li><?php endif;
			if($address->zip): ?><li>Zip: <?=$address->zip?></li><?php endif;
			if($address->country): ?><li>Country: <?=$address->country?></li><?php endif;
		?>
	<?php endif; ?>

	<h3>Payment Information:</h3> <?php
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onDisplayPaymentInfo', array($this->item, $this->params, $this->item->payment_name));
		$paymenttext = '';
		foreach($results as $result):
			if(is_string($result)) $paymenttext.=$result;
		endforeach;
		if(!empty($paymenttext)) {
			echo $paymenttext;
		} else {
			echo "<p>No payment data recorded</p>";
		}
	?>

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
				<td><?=$item->price?></td>
				<td>$<?=number_format($item->qty*$item->price,2)?></td>
			</tr><?php endforeach; ?></tbody>
		</table>
	<?php endif; ?>
</div>
<?=$this->params->get('details_posttext','')?>