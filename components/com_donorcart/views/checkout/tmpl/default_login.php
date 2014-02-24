<?php defined('_JEXEC') or die("Restricted Access"); ?>
<form action="<?php echo JRoute::_('index.php?option=com_donorcart&task=login'); ?>" method="post">
	<fieldset>
		<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_LOGIN_TO_CONTINUE')?></legend>
		<div class="field">
			<label for="username"><?php echo JText::_('COM_DONORCART_CHECKOUT_USERNAME') ?></label>
			<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
		</div>
		<div class="field">
			<label for="passwd"><?php echo JText::_('COM_DONORCART_CHECKOUT_PASSWORD') ?></label>
			<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
		</div>
		<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
			<div class="field">
				<label for="remember"><?php echo JText::_('COM_DONORCART_CHECKOUT_REMEMBERME') ?></label>
				<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="<?=JText::_('COM_DONORCART_CHECKOUT_REMEMBERME')?>" />
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