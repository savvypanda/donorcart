<?php defined('_JEXEC') or die('Restricted Access');
$unique_form_id = uniqid();
?>

<form method="post" action="<?=JRoute::_('index.php')?>" class="dcartadd<?=($options['skipprompt']?' dnoprompt':'').(empty($options['classname'])?'':' '.$options['classname'])?>">
	<?=JHtml::_('form.token')?>
	<input type="hidden" name="option" value="com_donorcart">
	<input type="hidden" name="view" value="cart">
	<input type="hidden" name="task" value="addItem">
	<input type="hidden" name="format" value="raw">
	<input type="hidden" name="template" value="system">
	<input type="hidden" name="my-item-id" value="<?=htmlentities($options['sku'], ENT_COMPAT)?>">

	<?php if($options['title']): ?><h3><?=JText::_($options['title'])?></h3><?php endif;

	if($options['img']) { ?>
		<div class="dcart-item-image">
			<?php if($options['url']): ?><a href="<?=JRoute::_($options['url'])?>"><?php endif; ?>
			<input type="hidden" name="my-item-img" value="<?=htmlentities($options['img'])?>">
			<img src="<?=htmlentities($options['img'], ENT_COMPAT)?>" alt="<?=htmlentities($options['name'], ENT_COMPAT)?>" />
			<?php if($options['url']): ?></a><?php endif; ?>
		</div>
	<?php } ?>

	<div class="cart-item-form">
		<?php if($options['editname']) { ?>
			<div class="dcart-item-name dcart-editable">
				<?php if(!empty($options['namelabel'])): ?><label for="<?=$unique_form_id?>-name"><?=JText::_($options['namelabel'])?></label><?php endif; ?>
				<input type="text" name="my-item-name" id="<?=$unique_form_id?>-name" <?=(($options['nameplaceholder'])?'placeholder':'value')?>="<?=htmlentities($options['name'], ENT_COMPAT)?>">
			</div>
		<?php } else {
			if(!$options['hidename']): ?>
				<div class="dcart-item-name dcart-static">
					<span class="cart-item-name"><?=$options['name']?></span>
				</div>
			<?php endif; ?>
			<input type="hidden" name="my-item-name" value="<?=htmlentities($options['name'], ENT_COMPAT)?>">
		<?php }

		if(!empty($options['priceoptions'])) { ?>
			<div class="dcart-item-price dcart-selectlist">
				<?php $priceoptions = explode(',',$options['priceoptions']); ?>
				<select class="item-price-selector" onchange="this.form['my-item-price'].value=this.value;"><option value="0"><?=JText::_($options['pricelabel'])?></option>
					<?php foreach($priceoptions as $opt): ?><option value="<?=htmlentities($opt, ENT_COMPAT)?>">$<?=$opt?></option><?php endforeach; ?>
				</select>

				<?php if($options['editprice']): ?>
					<div class="dcart-item-price dcart-editable">
						<input type="text" name="my-item-price" <?=(($options['priceplaceholder'])?'placeholder':'value')?>="<?=htmlentities($options['price'], ENT_COMPAT)?>">
					</div>
				<?php else: ?>
					<input type="hidden" name="my-item-price" value="<?=htmlentities($options['price'], ENT_COMPAT)?>">
				<?php endif; ?>
			</div>
		<?php } else {
			if($options['editprice']): ?>
				<div class="dcart-item-price dcart-editable">
					<?php if(!empty($options['pricelabel'])): ?><label for=<?=$unique_form_id?>-price"><?=JText::_($options['pricelabel'])?></label><?php endif; ?>
					<input type="text" name="my-item-price" id="<?=$unique_form_id?>-price" <?=(($options['priceplaceholder'])?'placeholder':'value')?>="<?=htmlentities($options['price'], ENT_COMPAT)?>">
				</div>
			<?php else:
				if(!$options['hideprice']): ?>
					<div class="dcart-item-price dcart-static">
						<span class="cart-item-amount">$<?=$options['price']?></span>
					</div>
				<?php endif; ?>
				<input type="hidden" name="my-item-price" value="<?=htmlentities($options['price'], ENT_COMPAT)?>">
			<?php endif;
		}

		if(!empty($options['qtyoptions'])) { ?>
			<div class="dcart-item-qty dcart-selectlist">
				<?php $qtyoptions = explode(',',$options['qtyoptions']); ?>
				<select name="item-qty-selector" onchange="this.form['my-item-qty'].value=this.value;"><option value="0"><?=JText::_($options['qtylabel'])?></option>
					<?php foreach($qtyoptions as $opt): ?><option value="<?=htmlentities($opt, ENT_COMPAT)?>">$<?=$opt?></option><?php endforeach; ?>
				</select>

				<?php if($options['editqty']): ?>
					<div class="dcart-item-qty dcart-editable">
						<input type="text" name="my-item-qty" <?=(($options['qtyplaceholder'])?'placeholder':'value')?>="<?=htmlentities($options['qty'], ENT_COMPAT)?>">
					</div>
				<?php else: ?>
					<input type="hidden" name="my-item-qty" value="<?=htmlentities($options['qty'], ENT_COMPAT)?>">
				<?php endif; ?>
			</div>
		<?php } else {
			if($options['editqty']): ?>
				<div class="dcart-item-qty dcart-editable">
					<?php if(!empty($options['qtylabel'])): ?><label for="<?=$unique_form_id?>-qty"><?=JText::_($options['qtylabel'])?></label><?php endif; ?>
					<input type="text" name="my-item-qty" id="<?=$unique_form_id?>-qty" <?=(($options['qtyplaceholder'])?'placeholder':'value')?>="<?=htmlentities($options['qty'], ENT_COMPAT)?>">
				</div>
			<?php else: ?>
				<input type="hidden" name="my-item-qty" value="<?=htmlentities($options['qty'], ENT_COMPAT)?>">
			<?php endif;
		}

		if($options['url']): ?><input type="hidden" name="my-item-url" value="<?=htmlentities($options['url'], ENT_COMPAT)?>"><?php endif; ?>

		<div class="dcart-item-add-button">
			<input type="submit" name="my-add-button" class="dcart-add-button" value="<?=JText::_($options['submitlabel'])?>">
			<?php if($options['recurringlabel'] && $this->componentParams->get('allow_recurring_donations',0)==1): ?>
				<input type="button" name="recurring-add-button" class="dcart-add-button dcart-add-recurring" value="<?=JText::_($options['recurringlabel'])?>">
				<input type="hidden" name="recurring" value="0">
			<?php endif; ?>
		</div>
	</div>
	<div class="clear"></div>
</form>