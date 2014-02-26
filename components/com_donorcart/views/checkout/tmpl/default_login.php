<?php defined('_JEXEC') or die("Restricted Access"); ?>
<form action="<?php echo JRoute::_('index.php?option=com_donorcart&task=login'); ?>" method="post">
	<fieldset>
		<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_LOGIN_TO_CONTINUE')?></legend>
		<div class="field text">
			<label for="dcart-checkout-login-username"><?php echo JText::_('COM_DONORCART_CHECKOUT_USERNAME') ?></label>
			<input name="username" id="dcart-checkout-login-username" type="text" />
		</div>
		<div class="field text">
			<label for="dcart-checkout-login-passwd"><?php echo JText::_('COM_DONORCART_CHECKOUT_PASSWORD') ?></label>
			<input type="password" id="dcart-checkout-login-passwd" name="passwd" />
		</div>
		<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div class="field checkbox">
				<input type="checkbox" id="dcart-checkout-login-remember" name="remember" value="yes" />
				<label for="dcart-checkout-login-remember"><?php echo JText::_('COM_DONORCART_CHECKOUT_REMEMBERME') ?></label>
			</div>
		<?php endif; ?>
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('COM_DONORCART_CHECKOUT_LOGIN') ?>" />
		<ul>
			<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('COM_DONORCART_CHECKOUT_FORGOT_PASSWORD'); ?></a></li>
			<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"><?php echo JText::_('COM_DONORCART_CHECKOUT_FORGOT_USERNAME'); ?></a></li>
		</ul>
	</fieldset>
	<?php echo JHTML::_('form.token'); ?>
</form>