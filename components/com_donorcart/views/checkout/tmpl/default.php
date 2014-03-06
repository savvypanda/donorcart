<?php defined('_JEXEC') or die("Restricted Access");
include_once(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'includes.php');

$display_cart = isset($this->display_cart)?$this->display_cart:($this->params->get('display_cart') == 1);
$login_option = $this->params->get('login_option',1);
?>
<div id="donorcart_checkout_container">
	<?php if($display_cart): //Display the cart if we are supposed to ?>
		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_CART')?></h3>
		<?php echo $this->loadTemplate('cart');
	endif;

	if($this->user->id) {
		?>
		<h3><?= JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT') ?></h3>
		<p><?= JText::sprintf('COM_DONORCART_CHECKOUT_LOGGED_IN_AS',$this->user->name) ?><span class="dcart-dash"> - </span><span class="dcart-logout-link">
			(<a href="<?=JRoute::_('index.php?option=com_donorcart&view=checkout&task=logout')?>"><?=JText::_(($login_option==0)?'COM_DONORCART_CHECKOUT_LOGOUT_AND_CONTINUE_AS_GUEST':(($login_option==2)?'COM_DONORCART_CHECKOUT_LOGOUT_AND_CONTINUE_AS_USER':'COM_DONORCART_CHECKOUT_LOGOUT_AND_CONTINUE'))?></a>)</span></p>
		<?php echo $this->loadTemplate('form');
	} else {

		//only display the create account option if user registration is allowed on the site
		$usersConfig = JComponentHelper::getParams('com_users');
		$allow_registration = $usersConfig->get('allowUserRegistration');

		if($login_option==0) { //skip login - go straight to guest checkout by default ?>
			<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT')?></h3>
			<?php echo $this->loadTemplate('form'); ?>
		<?php } elseif($login_option==2 && !$allow_registration) { //if login is required but registration is not allowed, the only option available is to log in ?>
			<h3><?= JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT') ?></h3>
			<?php echo $this->loadTemplate('login'); ?>
		<?php } else { //The user has a choice of at least two of the three options ?>
			<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT_OPTIONS') ?></h3>
			<div>
				<?php if($allow_registration): ?>
					<input type="radio" name="account_option" id="donorcart_create_acct_option" data-target="donorcart_create_acct_div" value="createacct" /><label for="donorcart_create_acct_option"><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_CREATE_ACCT_OPTION')?></label>
					<div id="donorcart_create_acct_div" class="option">
						<?php echo $this->loadTemplate('createacct'); ?>
					</div>
				<?php endif; ?>
				<input type="radio" name="account_option" id="donorcart_login_option" data-target="donorcart_login_div" value="login" /><label for="donorcart_login_option"><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_LOGIN_OPTION_'.($allow_registration?'ALLOWREG':'NOREG'))?></label>
				<div id="donorcart_login_div" class="option">
					<?php echo $this->loadTemplate('login'); ?>
				</div>
				<?php if($login_option != 2): //if login is not required (ie: guest checkout is allowed ?>
					<input type="radio" name="account_option" id="donorcart_no_login_option" data-target="donorcart_no_account_div" value="noacct" /><label for="donorcart_no_login_option"><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_LOGIN_OPTION_GUEST')?></label>
					<div id="donorcart_no_account_div" class="option">
							<?php echo $this->loadTemplate('form'); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php }
	} ?>
</div>
