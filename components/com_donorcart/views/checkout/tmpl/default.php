<?php defined('_JEXEC') or die("Restricted Access");

$display_cart = isset($this->display_cart)?$this->display_cart:($this->params->get('display_cart') == 1);

FOFTemplateUtils::addCSS('media://com_donorcart/donorcart.css');
FOFTemplateUtils::addJS('media://com_donorcart/donorcart.js');

?>
<div id="donorcart_checkout_container">
	<?php if($display_cart): //Display the cart if we are supposed to ?>
		<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_CART')?></h3>
		<?php FOFDispatcher::getTmpInstance('com_donorcart','cart',array('layout'=>'checkout','input'=>array('task'=>'read')))->dispatch(); ?>
	<?php endif;

	if($this->user->id) {
		?>
		<h3><?= JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT') ?></h3>
		<p><?= JText::sprintf('COM_DONORCART_CHECKOUT_LOGGED_IN_AS',$this->user->username) ?><span class="dcart-dash"> - </span><span class="dcart-logout-link">
			(<a href="<?=JRoute::_('index.php?option=com_donorcart&view=checkout&task=logout')?>"><?=JText::_('COM_DONORCART_CHECKOUT_LOGOUT')?></a>)</span></p>
		<?php //$this->includeLayout('form','default');
		$this->loadTemplate('form');
	} else {
		$login_option = $this->params->get('login_option',1);

		//only display the create account option if user registration is allowed on the site
		$usersConfig = JComponentHelper::getParams('com_users');
		$allow_registration = $usersConfig->get('allowUserRegistration');

		if($login_option==0) { //skip login - go straight to guest checkout by default ?>
			<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOT')?></h3>
			<?php //$this->includeLayout('form','default'); ?>
			<?php $this->loadTemplate('form'); ?>
		<?php } elseif($login_option==2 && !$allow_registration) { //if login is required but registration is not allowed, the only option available is to log in ?>
			<h3><?= JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT') ?></h3>
			<?php //$this->includeLayout('login','default'); ?>
			<?php $this->loadTemplate('login'); ?>
		<?php } else { //The user has a choice of at least two of the three options ?>
			<h3><?=JText::_('COM_DONORCART_CHECKOUT_HEADER_CHECKOUT_OPTIONS') ?></h3>
			<div>
				<?php if($allow_registration): ?>
					<input type="radio" name="plan_id" id="donorcart_create_acct_option" value="0" /><span><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_CREATE_ACCT_OPTION')?></span>
					<div id="donorcart_create_acct_div" class="option">
						<?php //$this->includeLayout('createacct','default'); ?>
						<?php $this->loadTemplate('createacct'); ?>
					</div>
					<br />
				<?php endif; ?>
				<input type="radio" name="plan_id" id="donorcart_login_option" value="0" /><span><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_LOGIN_OPTION_'.($allow_registration?'ALLOWREG':'NOREG'))?></span>
				<div id="donorcart_login_div" class="option">
					<?php //$this->includeLayout('login','default'); ?>
					<?php $this->loadTemplate('login'); ?>
				</div>
				<br />
				<?php if($login_option != 2): //if login is not required (ie: guest checkout is allowed ?>
					<input type="radio" name="plan_id" id="donorcart_no_login_option" value="0" /><span><?=JText::_('COM_DONORCART_CHECKOUT_HEADING_LOGIN_OPTION_GUEST')?></span>
					<div id="donorcart_no_account_div" class="option">
							<?php //$this->includeLayout('form','default'); ?>
							<?php $this->loadTemplate('form'); ?>
					</div>
				<?php endif; ?>
			</div>
		<?php }
	} ?>
</div>