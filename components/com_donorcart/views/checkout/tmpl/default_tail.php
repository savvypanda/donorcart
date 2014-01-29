<?php defined('_JEXEC') or die('Restricted Access');

if(isset($this->include_form) && $this->include_form):
	if(isset($this->include_submit_button) && $this->include_submit_button): ?>
	<input type="submit" value="Submit" />
	<?php endif; ?>
	<?=JHtml::_('form.token')?>
	</form>
<?php endif; ?>
</div>