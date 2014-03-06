<?php defined('_JEXEC') or die('Restricted Access');
//get the basic params
$module_suffix = htmlspecialchars($params->get('moduleclass_sfx',''), ENT_COMPAT);
$unique_form_id = uniqid();
$allowrecurring = $params->get('allowrecurring',false);
$skipprompt = $params->get('skipprompt',false);

//get the form params
$sku = htmlspecialchars($params->get('sku',''), ENT_COMPAT);
$name = $params->get('name',''); if($name) $name = htmlspecialchars(JText::_($name), ENT_COMPAT);
$editname = ($name)?$params->get('editname',false):true;
$img = htmlspecialchars($params->get('img',''), ENT_COMPAT);
$url = htmlspecialchars($params->get('url',''), ENT_COMPAT);
$qty = htmlspecialchars($params->get('qty',''), ENT_COMPAT);
$editqty = ($qty)?$params->get('editqty',false):true;
$price = htmlspecialchars($params->get('price',''), ENT_COMPAT);
$editprice = ($price)?$params->get('editprice',false):true;

//get the label params
$namelabel = $params->get('namelabel'); if($namelabel) $namelabel = htmlspecialchars(JText::_($namelabel), ENT_COMPAT);
$nameplaceholder = $params->get('nameplaceholder'); if($nameplaceholder) $nameplaceholder = htmlspecialchars(JText::_($nameplaceholder), ENT_COMPAT);
$pricelabel = $params->get('pricelabel'); if($pricelabel) $pricelabel = htmlspecialchars(JText::_($pricelabel), ENT_COMPAT);
$priceplaceholder = $params->get('priceplaceholder'); if($priceplaceholder) $priceplaceholder = htmlspecialchars(JText::_($priceplaceholder), ENT_COMPAT);
$qtylabel = $params->get('qtylabel'); if($qtylabel) $qtylabel = htmlspecialchars(JText::_($qtylabel), ENT_COMPAT);
$qtyplaceholder = $params->get('qtyplaceholder'); if($qtyplaceholder) $qtyplaceholder = htmlspecialchars(JText::_($qtyplaceholder), ENT_COMPAT);
if($allowrecurring) $recurringlabel = htmlspecialchars(JText::_($params->get('recurringlabel','Recurring Donation')), ENT_COMPAT);
$submitlabel = htmlspecialchars(JText::_($params->get('submitlabel','Donate')), ENT_COMPAT);

//get advanced params
$priceoptions = $params->get('priceoptions',false);
if($priceoptions):
	$optionstrings = preg_split("/\r\n|\n|\r/", $priceoptions);
	$priceoptions = array();
	foreach($optionstrings as $string) {
		list($value,$text) = explode('=',$string,2);
		$value = trim($value);
		$text = trim($text);
		if(!empty($value) && !empty($text)) $priceoptions[$value] = $text;
	}
	if(empty($priceoptions)) $priceoptions = false;
endif;
$qtyoptions = $params->get('qtyoptions',false);
if($qtyoptions):
	$optionstrings = preg_split("/\r\n|\n|\r/", $qtyoptions);
	$qtyoptions = array();
	foreach($optionstrings as $string) {
		list($value,$text) = explode('=',$string,2);
		$value = trim($value);
		$text = trim($text);
		if(!empty($value) && !empty($text)) $qtyoptions[$value] = $text;
	}
	if(empty($qtyoptions)) $qtyoptions = false;
endif;
?>

<div class="dcartform <?=$module_suffix?>">
	<form method="post" action="<?=JRoute::_('index.php')?>" name="dform<?=$unique_form_id?>" class="dcartadd<?=($skipprompt?' dnoprompt':'')?>">
		<?=JHtml::_('form.token')?>
		<input type="hidden" name="option" value="com_donorcart">
		<input type="hidden" name="view" value="cart">
		<input type="hidden" name="task" value="addItem">
		<input type="hidden" name="format" value="raw">
		<input type="hidden" name="template" value="system">
		<input type="hidden" name="my-item-id" value="<?=$sku?>">
		<?php if($url): ?><input type="hidden" name="my-item-url" value="<?=$url?>"><?php endif; ?>

		<?php if($img): ?>
		<div class="dcart-item-image">
			<input type="hidden" name="my-item-img" value="<?=$img?>">
			<?php if($url): ?><a href="<?=$url?>"><?php endif; ?>
			<img src="<?=$img?>" alt="<?=$name?>" />
			<?php if($url): ?></a><?php endif; ?>
		</div>
		<?php endif; ?>

		<?php if($allowrecurring): ?>
			<input type="hidden" name="recurring" value="0">
			<input type="button" class="btn blue fullwidth recurring-option" value="<?=$recurringlabel?>" onclick="set<?=$unique_form_id?>Value('recurring',1,'recurring-option', this)">
			<input type="button" class="btn blue fullwidth recurring-option" value="Make a single donation" onclick="set<?=$unique_form_id?>Value('recurring',0,'recurring-option', this)">
		<?php endif; ?>

		<div class="cart-item-form">
			<?php if($editname): ?>
				<div class="dcart-item-name dcart-editable">
					<?php if($namelabel): ?><label for="<?=$unique_form_id?>-name"><?=$namelabel?></label><?php endif; ?>
					<input type="text" name="my-item-name" id="<?=$unique_form_id?>-name"<?=($nameplaceholder?' placeholder="'.$nameplaceholder.'"':'')?> value="<?=$name?>">
				</div>
			<?php else: ?>
				<input type="hidden" name="my-item-name" value="<?=$name?>">
				<?php if($namelabel): ?><h4><?=$namelabel?></h4><?php endif; ?>
			<?php endif; ?>
			<?php if($editprice): ?>
				<div class="dcart-item-price dcart-editable">
					<?php if($pricelabel): ?><label for="<?=$unique_form_id?>-price"><?=$pricelabel?></label><?php endif; ?>
					<?php foreach($priceoptions as $value => $text): ?>
						<?php if($value!='00'): ?><input type="button" class="btn blue price-option" value="<?=$text?>" onclick="set<?=$unique_form_id?>Value('my-item-price','<?=$value?>','price-option',this)"><?php endif; ?>
					<?php endforeach; ?>
					<?php if(isset($priceoptions['00'])): ?>
						<div class="btn blue price-option fullwidth" onclick="jQuery('.price-option',document.dform<?=$unique_form_id?>).removeClass('selected');jQuery(this).addClass('selected');document.dform<?=$unique_form_id?>['my-item-price'].focus();">
							<label for="<?=$unique_form_id?>-price"><?=$priceoptions['00']?></label><input type="text" name="my-item-price" id="<?=$unique_form_id?>-price"<?=($priceplaceholder?' placeholder="'.$priceplaceholder.'"':'')?> value="<?=$price?>">
						</div>
					<?php else: ?>
						<input type="hidden" name="my-item-price" value="<?=$price?>">
					<?php endif; ?>
				</div>
			<?php else: ?>
				<input type="hidden" name="my-item-price" value="<?=$price?>">
			<?php endif; ?>

			<?php if($editqty): ?>
				<div class="dcart-item-qty dcart-editable">
					<?php if($qtylabel): ?><label for="<?=$unique_form_id?>-qty"><?=$qtylabel?></label><?php endif; ?>
					<input type="text" name="my-item-qty" id="<?=$unique_form_id?>-qty"<?=($qtyplaceholder?' placeholder="'.$qtyplaceholder.'"':'')?> value="<?=$qty?>">
				</div>
			<?php else: ?>
				<input type="hidden" name="my-item-qty" value="<?=$qty?>">
			<?php endif; ?>

			<div class="dcart-item-add-button">
				<input type="submit" name="my-add-button" class="dcart-add-button" value="<?=$submitlabel?>">
			</div>
		</div>
		<div class="clear"></div>
	</form>
</div>
<script type="text/javascript">
	function set<?=$unique_form_id?>Value(field, value, buttonclass, selected_element) {
		jQuery(document.dform<?=$unique_form_id?>[field]).val(value).change();
		jQuery('.'+buttonclass, document.dform<?=$unique_form_id?>).removeClass('selected').filter(selected_element).addClass('selected');
		return false;
	}
</script>