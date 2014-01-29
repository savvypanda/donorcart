<?php defined('_JEXEC') or die("Restricted Access");

$this->exclude_form = true;
$this->includeLayout('head','default');

$user = JFactory::getUser();
if($user->id): ?>
	<h3>Checkout</h3>
	<p>You are currently logged in as <?=$user->username?>.<br />
		<a href="<?=JRoute::_('index.php?option=com_users&task=logout&return='.base64_encode('index.php?option=com_donorcart&step=login'))?>">Logout</a> or <a href="<?=JRoute::_('index.php?option=com_donorcart')?>">Continue checking out as this user.</a></p>
<?php else:
	$login_option = $this->params->get('login_option',1);
	$returned_to_login = JRequest::getCmd('step',false)=='login';
	if($login_option == 0 && !$returned_to_login) {
		//hypothetically, this code should never be activated. I am including it just in case.
		?>
		<h3>Checkout:</h3>
		<form action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post">
			<?php if($this->params->get('require_email_for_guest_checkout',false)): ?>
				<fieldset>
					<legend>Guest Checkout</legend>
					<p>Please enter your email address to continue checkout:</p>
					<div class="field">
						<label for="email"><?php echo JText::_('Email'); ?></label>
						<input type="text" name="email" class="inputbox" alt="email" size="18" />
					</div>
				</fieldset>
			<?php endif; ?>
			<input type="submit" name="Submit" class="button" value="Continue Checkout" />
			<input type="hidden" name="task" value="guestcheckout" />
			<?php echo JHTML::_('form.token'); ?>
		</form>
	<?php } else {
		//only display the create account option if user registration is allowed on the site
		$usersConfig = JComponentHelper::getParams('com_users');
		$allow_registration = $usersConfig->get('allowUserRegistration');

		//if login is required but registration is not allowed, the only option available is to log in
		if(!$allow_registration && $login_option==2) { ?>
			<h3>Checkout:</h3>
			<form action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post" name="com-login" id="com-form-login">
				<fieldset>
					<label>Please log in to continue checkout:</label>
					<div class="field">
						<label for="username"><?php echo JText::_('Username') ?></label><br />
						<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
					</div>
					<div class="field">
						<label for="passwd"><?php echo JText::_('Password') ?></label><br />
						<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
					</div>
					<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
						<div class="field">
							<label for="remember"><?php echo JText::_('Remember me') ?></label>
							<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
						</div>
					<?php endif; ?>
					<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
					<input type="hidden" name="task" value="login" />
					<?php echo JHTML::_('form.token'); ?>
					<ul>
						<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('Forgot your Password?'); ?></a></li>
						<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"><?php echo JText::_('Forgot your Username?'); ?></a></li>
					</ul>
				</fieldset>
			</form>
		<?php } else { //The user has a choice of at least two of the three options ?>
			<h3>Checkout: Choose an Account Option</h3>
			<div>
				<?php if($allow_registration): ?>
					<input type="radio" name="plan_id" id="donorcart_create_acct_option" value="0" /><span>Create an account to track donations</span>
					<div id="donorcart_create_acct_div" class="option">
						<form id="createForm" action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post" name="createForm" class="form-validate user straightstyle">
							<fieldset>
								<legend>Account Information</legend>
								<p>(*) Marks Required Fields</p>

								<div class="field">
									<label id="namemsg" for="name"><?php echo JText::_('Name'); ?>: *</label>
									<input type="text" name="name" id="name" value="" class="inputbox required" maxlength="50" />
								</div>
								<div class="field">
									<label id="usernamemsg" for="username"><?php echo JText::_('Username'); ?>: *</label>
									<input type="text" id="username" name="username" value="" class="inputbox required" maxlength="25" />
								</div>
								<div class="field">
									<label id="emailmsg" for="email"><?php echo JText::_('Email'); ?>: *</label>
									<input type="text" id="email" name="email" value="" class="inputbox required" maxlength="100" />
								</div>
								<div class="field">
									<label id="pwmsg" for="password"><?php echo JText::_('Password'); ?>: *</label>
									<input type="password" id="password" name="password" value="" class="required" />
								</div>
								<div class="field">
									<label id="pw2msg" for="password2"><?php echo JText::_('Verify Password'); ?>: *</label>
									<input type="password" id="password2" name="password2" value="" class="" />
								</div>
							</fieldset>
							<input class="button validate" type="submit" value="Register" />
							<input type="hidden" name="task" value="register" />
							<input type="hidden" name="id" value="0" />
							<input type="hidden" name="gid" value="0" />
							<?php echo JHTML::_('form.token'); ?>
						</form>
					</div>
					<br />
				<?php endif; ?>

				<input type="radio" name="plan_id" id="donorcart_login_option" value="0" /><span>I <?=$allow_registration?'already ':''?>have an account</span>
				<div id="donorcart_login_div" class="option">
					<form action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post" name="com-login" id="com-form-login">
						<fieldset>
							<div class="field">
								<label for="username"><?php echo JText::_('Username') ?></label><br />
								<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
							</div>
							<div class="field">
								<label for="passwd"><?php echo JText::_('Password') ?></label><br />
								<input type="password" id="passwd" name="passwd" class="inputbox" size="18" alt="password" />
							</div>
							<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
								<div class="field">
									<label for="remember"><?php echo JText::_('Remember me') ?></label>
									<input type="checkbox" id="remember" name="remember" class="inputbox" value="yes" alt="Remember Me" />
								</div>
							<?php endif; ?>
							<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
						</fieldset>
						<ul>
							<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('Forgot your Password?'); ?></a></li>
							<li><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"><?php echo JText::_('Forgot your Username?'); ?></a></li>
						</ul>

						<input type="hidden" name="task" value="login" />
						<?php echo JHTML::_('form.token'); ?>
					</form>
				</div>
				<br />

				<?php if($login_option != 2): //if login is not required (ie: guest checkout is allowed ?>
					<input type="radio" name="plan_id" id="donorcart_no_login_option" value="0" />No thanks, I don't want an account
					<div id="donorcart_no_account_div" class="option">
						<?php
							JPluginHelper::importPlugin('donorcart');
							$dispatcher = JDispatcher::getInstance();
							$results = $dispatcher->trigger('onReplaceGuestCheckout', array(&$this->order, $this->params));
							$formreplacement = '';
							foreach($results as $result) {
								if(is_string($result)) {
									$formreplacement .= $result;
								}
							}
							if(!empty($formreplacement)):
								echo $formreplacement;
							else: ?>
								<form action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post">
									<?php if($this->params->get('require_email_for_guest_checkout',false)): ?>
										<fieldset>
											<legend>Guest Checkout</legend>
											<p>* Your email address is still required for guest checkout</p>
											<div class="field">
												<label for="email"><?php echo JText::_('Email'); ?></label>
												<input type="text" name="email" class="inputbox" alt="email" size="18" />
											</div>
										</fieldset>
									<?php endif; ?>
									<input type="submit" name="Submit" class="button" value="Checkout without creating an account" />
									<input type="hidden" name="task" value="guestcheckout" />
									<?php echo JHTML::_('form.token'); ?>
								</form>
							<?php endif;
						?>
					</div>
				<?php endif; ?>
			</div>
			<!--/div> <!-- end wrapper div that maintains height -->
		<?php }
	}
endif;

$this->includeLayout('tail','default');