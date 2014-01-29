<?php defined('_JEXEC') or die("Restricted Access");
$this->includeLayout('head','default');
?>

<script type="text/javascript">
	function sage_form_submit() {
		products = jQuery('input[name^=P_part]', '#frmDonorcart');
		pattern = /-[R1]X$/;
		replace = jQuery('input[name="is_recurring"]', '#frmDonorcart').is(':checked')?'-RX':'-1X';
		products.each(function() {
			var sku = jQuery(this).val();
			if(pattern.test(sku)) {
				jQuery(this).val(sku.replace(pattern,replace));
			} else {
				jQuery(this).val(sku+replace);
			}
		});

		return true;
	}
</script>

<h3>Checkout: </h3>
<form id="frmDonorcart" name="frmDonorcart" method="post" action="https://www.sagepayments.net/eftcart/forms/order.asp" onsubmit="return sage_form_submit();">
	<fieldset class="em">
		<input type='checkbox' name='is_recurring' id="is_recurring" /><strong><?=JText::_(' Make this an automatic monthly gift')?></strong>
	</fieldset>
	<fieldset class="em">
		<input type="submit" name="submit1" id="submit1" class="button" value="<?php echo JText::_('Click Here to Complete Donation'); ?> &raquo;" /><br />
		<span id="processing"><em>You will be redirected to our secure processing server</em></span>
	</fieldset>

	<input type="hidden" name="M_id" value="<?=$this->params->get('sage_payments_virtual_terminal_id');?>" />
	<input type="hidden" name="P_count" value="<?php echo count($this->dcart); ?>" />
	<?php $i = 1; foreach($this->dcart as $item): ?>
	<input type="hidden" name="P_part<?=$i?>" value="<?=$item['sku']?>" />
	<input type="hidden" name="P_desc<?=$i?>" value="<?=$item['name']?>" />
	<input type="hidden" name="P_qty<?=$i?>" value="<?=$item['qty']?>" />
	<input type="hidden" name="P_price<?=$i?>" value="<?=$item['price']?>" />
	<?php $i++; endforeach; ?>
	<?php foreach(array('M_image','B_color','BF_color','M_color','F_color','F_font') as $p):
		$pval = $this->params->get($p);
		if(!empty($pval)): ?>
			<input type="hidden" name="<?=$p?>" value="<?=$pval?>" />
		<?php endif;
	endforeach; ?>
</form>

<?php $this->includeLayout('tail','default'); ?>
