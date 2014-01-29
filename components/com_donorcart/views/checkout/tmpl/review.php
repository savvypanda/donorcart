<?php defined('_JEXEC') or die("Restricted Access");
$this->display_cart = true;
$this->includeLayout('head','default');

$shipto_option_flag = $this->params->get('shipto_option');
$billto_option_flag = $this->params->get('billto_option');

?>

<h3>Checkout: Review</h3>
<?php $this->includeLayout('returntologin','default'); ?>
<div id="donorcart_review">
	<?php if($shipto_option_flag != 0): ?>
		<h3>Shippping Address:</h3>
		<?php if($this->order->shipping_address_id):
			$address = $this->order->shipping_address;
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
			echo '<li><a href="'.JRoute::_('index.php?option=com_donorcart&step=shipto').'">Change</a></li></ul>';
		else: ?>
			No Shipping Address ::<a href="<?=JRoute::_('index.php?option=com_donorcart&step=shipto')?>">Add</a>
		<?php endif; ?>
	<?php endif; ?>

	<?php if($billto_option_flag != 0): ?>
		<h3>Billing Address:</h3>
		<?php if($this->order->billing_address_id):
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
			echo '<li><a href="'.JRoute::_('index.php?option=com_donorcart&step=billto').'">Change</a></li></ul>';
		else: ?>
			No Billing Address ::<a href="<?=JRoute::_('index.php?option=com_donorcart&step=billto')?>">Add</a>
		<?php endif; ?>
	<?php endif; ?>

	<h3>Payment Information:</h3>
	<?php
	JPluginHelper::importPlugin('donorcart');
	$dispatcher = JDispatcher::getInstance();
	$results = $dispatcher->trigger('onDisplayPaymentInfo', array($this->order));
	foreach($results as $result):
		if(is_string($result)) echo $result;
	endforeach;
	echo '<a href="'.JRoute::_('index.php?option=com_donorcart&step=payment').'">Update</a>';
	?>
	<br />
	<br />

	<a href="<?=JRoute::_('index.php?option=com_donorcart&task=submit&'.JSession::getFormToken().'=1')?>">Confirm</a>
</div>

<?php $this->includeLayout('tail','default'); ?>
