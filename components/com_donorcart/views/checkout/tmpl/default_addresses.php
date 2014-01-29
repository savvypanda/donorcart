<?php defined('_JEXEC') or die('Restricted Access');

//get helper variables from the params
$shipto_option_flag = $this->params->get('shipto_option');
$billto_option_flag = $this->params->get('billto_option');

//skip the entire template if we are supposed to skip both the billto and the shipto addresses.
if($shipto_option_flag!=0 || $billto_option_flag!=0):
	$display_both_options = ($shipto_option_flag!=0 && $billto_option_flag!=0);

	//set the defaults in an array, which we can override if this has already been set
	$billto = $shipto = array(
		'donorcart_address_id' => 0,
		'locked' => 0,
		'address_type' => 'house',
		'first_name' => '',
		'middle_name' => '',
		'last_name' => '',
		'business_name' => '',
		'address1' => '',
		'address2' => '',
		'city' => '',
		'state' => '',
		'zip' => '',
		'country' => 'USA'
	);
	if($this->order->shipping_address_id && is_object($this->order->shipping_address)) {
		$shippingaddress = get_object_vars($this->order->shipping_address);
		$shipto = array_merge($shipto, $shippingaddress);
	}
	if($this->order->billing_address_id && is_object($this->order->billing_address)) {
		$billingaddress = get_object_vars($this->order->billing_address);
		$billto = array_merge($billto, $billingaddress);
	}
	?>

	<h3>Checkout: Addresses</h3>
	<div>
		<div class="addresses">
			<?php if($display_both_options): ?>
				<label for="use_same_address_for_billto">Use the same address for both your billing and shipping addresses?</label>
				<input type="checkbox" name="use_same_address_for_billto" value="1" <?php if($this->order->shipping_address_id == $this->order->billing_address_id) echo 'checked="checked"'; ?> />
			<?php endif; ?>
			<?php if($shipto_option_flag != 0): ?>
				<div class="shippingaddress">
					<fieldset>
						<legend>Shipping Address <?php echo ($shipto_option_flag==1)?'(Optional)':'(Required)'; ?></legend>
						<?php if(!empty($this->prior_addresses)):
							foreach($this->prior_addresses as $address): ?>
								<?php
									if(!empty($address->business_name)) {
										$addressname = $address->business_name;
									} elseif(!empty($address->address1)) {
										$addressparts = array($address->address1);
										if(!empty($address->address2)) $addressparts[] = $address->address2;
										$addressname = implode(', ', $addressparts);
									} elseif(!empty($address->first_name) || !empty($address->last_name)) {
										$addressparts = array();
										if(!empty($address->first_name)) $addressparts[] = $address->first_name;
										if(!empty($address->middle_name)) $addressparts[] = $address->middle_name;
										if(!empty($address->last_name)) $addressparts[] = $address->last_name;
										$addressname = implode(', ', $addressparts);
									} else {
										//we do not want to use this address. It has no information!
										continue;
									}
								?>
								<div class="addressoption">
									<input type="radio" name="shipto_id" id="shiptobutton-<?=$address->donorcart_address_id?>" value="<?=$address->donorcart_address_id?>" <?php if($shipto['donorcart_address_id']==$address->donorcart_address_id) echo('checked="checked"'); ?> /><label for="shiptobutton-<?=$address->donorcart_address_id?>"> Use this address: <?=$addressname?></label>
									<?php
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
									?>
									<div class="optiondrawer">
										<ul>
											<?php if($addresstype): ?><li>Address Type: <?=$address->address_type?></li><?php endif; ?>
											<?php if(!empty($namearray)): ?><li>Name: <?=implode(' ',$namearray)?></li><?php endif; ?>
											<?php if($address->business_name): ?><li>Business Name: <?=$address->business_name?></li><?php endif; ?>
											<?php if($address->address1): ?><li>Address: <?=$address->address1?></li><?php endif; ?>
											<?php if($address->address2): ?><li>Address: <?=$address->address2?></li><?php endif; ?>
											<?php if($address->city): ?><li>City: <?=$address->city?></li><?php endif; ?>
											<?php if($address->state): ?><li>State: <?=$address->state?></li><?php endif; ?>
											<?php if($address->zip): ?><li>Zip: <?=$address->zip?></li><?php endif; ?>
											<?php if($address->country): ?><li>Country: <?=$address->country?></li><?php endif; ?>
										</ul>
									</div>
								</div>
							<?php endforeach;
						endif; ?>
						<div class="addressoption">
							<?php if(empty($this->prior_addresses)): ?>
								<input type="hidden" name="shipto_id" value="<?=$shipto['donorcart_address_id']?>" />
							<?php else: ?>
								<input type="radio" name="shipto_id" id="shiptobutton-new" value="<?=$shipto['donorcart_address_id']?>" <?php if(!$shipto['locked']) echo('checked="checked"'); ?>/><label for="shiptobutton-new"> Create a new address</label>
								<div class="optiondrawer">
							<?php endif; ?>
							<div class="field">
								<label for="ship_address_type">Address Type</label>
								<select name="ship_address_type">
									<option value="house"<?php if($shipto['address_type']=='house')echo(' selected="selected"');?>>House</option>
									<option value="apartment"<?php if($shipto['address_type']=='apartment')echo(' selected="selected"');?>>Apartment</option>
									<option value="box"<?php if($shipto['address_type']=='box')echo(' selected="selected"');?>>PO Box</option>
									<option value="business"<?php if($shipto['address_type']=='business')echo(' selected="selected"');?>>Business</option>
									<option value="other"<?php if($shipto['address_type']=='other')echo(' selected="selected"');?>>Other</option>
								</select>
							</div>
							<div class="field">
								<label for="ship_first_name">First Name</label>
								<input type="text" name="ship_first_name" value="<?=$shipto['first_name']?>"/>
							</div>
							<div class="field">
								<label for="ship_middle_name">Middle Name</label>
								<input type="text" name="ship_middle_name" value="<?=$shipto['middle_name']?>"/>
							</div>
							<div class="field">
								<label for="ship_last_name">Last Name</label>
								<input type="text" name="ship_last_name" value="<?=$shipto['last_name']?>"/>
							</div>
							<div class="field">
								<label for="ship_business_name">Business Name</label>
								<input type="text" name="ship_business_name" value="<?=$shipto['business_name']?>"/>
							</div>
							<div class="field">
								<label for="ship_address1">Address</label>
								<input type="text" name="ship_address1" value="<?=$shipto['address1']?>"/>
							</div>
							<div class="field">
								<label for="ship_address2">Address 2</label>
								<input type="text" name="ship_address2" value="<?=$shipto['address2']?>"/>
							</div>
							<div class="field">
								<label for="ship_city">City</label>
								<input type="text" name="ship_city" value="<?=$shipto['city']?>"/>
							</div>
							<div class="field">
								<label for="ship_state">State</label>
								<input type="text" name="ship_state" value="<?=$shipto['state']?>"/>
							</div>
							<div class="field">
								<label for="ship_zip">Zip</label>
								<input type="text" name="ship_zip" value="<?=$shipto['zip']?>"/>
							</div>
							<div class="field">
								<label for="ship_country">Country</label>
								<input type="text" name="ship_country" value="<?=$shipto['country']?>"/>
							</div>
							<?php if(!empty($this->prior_addresses)): ?>
								</div>
							<?php endif; ?>
						</div>
					</fieldset>
				</div>
			<?php endif;
			if($billto_option_flag != 0): ?>
				<div class="billingaddress">
					<fieldset>
						<legend>Billing Address <?php echo ($billto_option_flag==1)?'(Optional)':'(Required)'; ?></legend>
						<?php if(!empty($this->prior_addresses)):
							foreach($this->prior_addresses as $address): ?>
								<?php
									if(!empty($address->business_name)) {
										$addressname = $address->business_name;
									} elseif(!empty($address->address1)) {
										$addressparts = array($address->address1);
										if(!empty($address->address2)) $addressparts[] = $address->address2;
										$addressname = implode(', ', $addressparts);
									} elseif(!empty($address->first_name) || !empty($address->last_name)) {
										$addressparts = array();
										if(!empty($address->first_name)) $addressparts[] = $address->first_name;
										if(!empty($address->middle_name)) $addressparts[] = $address->middle_name;
										if(!empty($address->last_name)) $addressparts[] = $address->last_name;
										$addressname = implode(', ', $addressparts);
									} else {
										//we do not want to use this address. It has no information!
										continue;
									}
								?>
								<div class="addressoption">
									<input type="radio" name="billto_id" id="billtobutton-<?=$address->donorcart_address_id?>" value="<?=$address->donorcart_address_id?>" <?php if($billto['donorcart_address_id']==$address->donorcart_address_id) echo('checked="checked"'); ?> /><label for="billtobutton-<?=$address->donorcart_address_id?>"> Use this address: <?=$addressname?></label>
									<?php
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
									?>
									<div class="optiondrawer">
										<ul>
											<?php if($addresstype): ?><li>Address Type: <?=$address->address_type?></li><?php endif; ?>
											<?php if(!empty($namearray)): ?><li>Name: <?=implode(' ',$namearray)?></li><?php endif; ?>
											<?php if($address->business_name): ?><li>Business Name: <?=$address->business_name?></li><?php endif; ?>
											<?php if($address->address1): ?><li>Address: <?=$address->address1?></li><?php endif; ?>
											<?php if($address->address2): ?><li>Address: <?=$address->address2?></li><?php endif; ?>
											<?php if($address->city): ?><li>City: <?=$address->city?></li><?php endif; ?>
											<?php if($address->state): ?><li>State: <?=$address->state?></li><?php endif; ?>
											<?php if($address->zip): ?><li>Zip: <?=$address->zip?></li><?php endif; ?>
											<?php if($address->country): ?><li>Country: <?=$address->country?></li><?php endif; ?>
										</ul>
									</div>
								</div>
							<?php endforeach;
						endif; ?>
						<div class="addressoption">
							<?php if(empty($this->prior_addresses)): ?>
								<input type="hidden" name="billto_id" value="<?=$billto['donorcart_address_id']?>" />
							<?php else: ?>
								<input type="radio" name="billto_id" id="billtobutton-new" value="<?=$billto['donorcart_address_id']?>" <?php if(!$billto['locked']) echo('checked="checked"'); ?> /><label for="billtobutton-new"> Create a new address</label>
								<div class="optiondrawer">
							<?php endif; ?>
							<div class="field">
								<label for="bill_address_type">Address Type</label>
								<select name="bill_address_type">
									<option value="house"<?php if($billto['address_type']=='house')echo(' selected="selected"');?>>House</option>
									<option value="apartment"<?php if($billto['address_type']=='apartment')echo(' selected="selected"');?>>Apartment</option>
									<option value="box"<?php if($billto['address_type']=='box')echo(' selected="selected"');?>>PO Box</option>
									<option value="business"<?php if($billto['address_type']=='business')echo(' selected="selected"');?>>Business</option>
									<option value="other"<?php if($billto['address_type']=='other')echo(' selected="selected"');?>>Other</option>
								</select>
							</div>
							<div class="field">
								<label for="bill_first_name">First Name</label>
								<input type="text" name="bill_first_name" value="<?=$billto['first_name']?>"/>
							</div>
							<div class="field">
								<label for="bill_middle_name">Middle Name</label>
								<input type="text" name="bill_middle_name" value="<?=$billto['middle_name']?>"/>
							</div>
							<div class="field">
								<label for="bill_last_name">Last Name</label>
								<input type="text" name="bill_last_name" value="<?=$billto['last_name']?>"/>
							</div>
							<div class="field">
								<label for="bill_business_name">Business Name</label>
								<input type="text" name="bill_business_name" value="<?=$billto['business_name']?>"/>
							</div>
							<div class="field">
								<label for="bill_address1">Address</label>
								<input type="text" name="bill_address1" value="<?=$billto['address1']?>"/>
							</div>
							<div class="field">
								<label for="bill_address2">Address 2</label>
								<input type="text" name="bill_address2" value="<?=$billto['address2']?>"/>
							</div>
							<div class="field">
								<label for="bill_city">City</label>
								<input type="text" name="bill_city" value="<?=$billto['city']?>"/>
							</div>
							<div class="field">
								<label for="bill_state">State</label>
								<input type="text" name="bill_state" value="<?=$billto['state']?>"/>
							</div>
							<div class="field">
								<label for="bill_zip">Zip</label>
								<input type="text" name="bill_zip" value="<?=$billto['zip']?>"/>
							</div>
							<div class="field">
								<label for="bill_country">Country</label>
								<input type="text" name="bill_country" value="<?=$billto['country']?>"/>
							</div>
						<?php if(!empty($this->prior_addresses)): ?>
							</div>
						<?php endif; ?>
						</div>
					</fieldset>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>