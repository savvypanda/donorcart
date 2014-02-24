<?php defined('_JEXEC') or die("Restricted Access"); ?>
<form action="<?php echo JRoute::_('index.php?option=com_donorcart&task=register'); ?>" method="post">
	<fieldset>
		<legend><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_ACCT_INFO')?></legend>
		<p><?=JText::_('COM_DONORCART_CHECKOUT_REQUIRED_INFO_TEXT')?></p>

		<div class="field">
			<label id="namemsg" for="name"><?php echo JText::_('COM_DONORCART_CHECKOUT_REGISTER_NAME'); ?>: *</label>
			<input type="text" name="name" id="name" value="" class="inputbox required" maxlength="50" />
		</div>
		<div class="field">
			<label id="usernamemsg" for="username"><?php echo JText::_('COM_DONORCART_CHECKOUT_REGISTER_USERNAME'); ?>: *</label>
			<input type="text" id="username" name="username" value="" class="inputbox required" maxlength="25" />
		</div>
		<div class="field">
			<label id="emailmsg" for="email"><?php echo JText::_('COM_DONORCART_CHECKOUT_REGISTER_EMAIL'); ?>: *</label>
			<input type="text" id="email" name="email" value="" class="inputbox required" maxlength="100" />
		</div>
		<div class="field">
			<label id="pwmsg" for="password"><?php echo JText::_('COM_DONORCART_CHECKOUT_REGISTER_PASSWORD'); ?>: *</label>
			<input type="password" id="password" name="password" value="" class="required" />
		</div>
		<div class="field">
			<label id="pw2msg" for="password2"><?php echo JText::_('COM_DONORCART_CHECKOUT_REGISTER_CONFIRM_PASSWORD'); ?>: *</label>
			<input type="password" id="password2" name="password2" value="" class="" />
		</div>
		<input type="submit" value="<?=JText::_('COM_DONORCART_CHECKOUT_REGISTER_ACCOUNT_ACTION')?>" />
	</fieldset>
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="gid" value="0" />
	<?php echo JHTML::_('form.token'); ?>
</form>