<?php defined('_JEXEC') or die('Restricted Access');

if($this->params->get('allow_return_to_login',false)):
	$login_option = $this->params->get('login_option',1);
	$usersConfig = JComponentHelper::getParams('com_users');
	$allow_registration = $usersConfig->get('allowUserRegistration');
	$require_email = $this->params->get('require_email_for_guest_checkout',false);

	$user = JFactory::getUser();
	if($user->id) {
		$myloginoptions[] = '<a href="'.JRoute::_('index.php?option=com_donorcart&task=logout&step=login&'.JSession::getFormToken().'=1#login').'">Log in as a different user</a>';
		if($allow_registration) $myloginoptions[] = '<a href="'.JRoute::_('index.php?option=com_donorcart&task=logout&step=login&'.JSession::getFormToken().'=1#createacct').'">Create a new account</a>';
		if($login_option != 2) $myloginoptions[] = '<a href="'.JRoute::_('index.php?option=com_donorcart&task=logout&step=login&'.JSession::getFormToken().'=1#noacct').'">Check out as a guest</a>';
		?>
		<div class="returntologin"><p>You are currently logged in as <?=$user->username?>. If you do not wish to check out as this user, <?=implode(', or ',$myloginoptions)?>.</p></div>
	<?php } else {
		$myloginoptions[] = '<a href="'.JRoute::_('index.php?option=com_donorcart&step=login#login').'">Log In</a>';
		if($allow_registration) $myloginoptions[] = '<a href="'.JRoute::_('index.php?option=com_donorcart&step=login#createacct').'">Create a new account</a>';
		if($login_option != 2 && $require_email) $myloginoptions[] = '<a href="'.JRoute::_('index.php?option=com_donorcart&step=login#noacct').'">Use a different email address.</a>';
		if(!empty($myloginoptions)) {
			if(!empty($this->order->email)) { ?>
				<div class="returntologin"><p>You are currently checking out as a guest using the email address <?=$this->order->email?>. If you wish to check out using a different method, you may <?=implode(', or ',$myloginoptions)?>.</p></div>
			<?php } else { ?>
				<div class="returntologin"><p>You are currently checking out as a guest. If you wish to check out using a different method, you may <?=implode(' or ',$myloginoptions)?>.</p></div>
			<?php }
		}
	}
endif;