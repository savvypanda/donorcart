<?php defined('_JEXEC') or die;

FOFTemplateUtils::addCSS('media://com_donorcart/donorcart.css');
FOFTemplateUtils::addJS('media://com_donorcart/donorcart.js');

$this->includeLayout('messages','default');
?>

	<div id="donorcart">
	<?php
		//Display the cart and the progress bar, in the correct order
		//$display_progress = false; //(isset($display_progress) && $display_progress === true);
		$display_cart = isset($this->display_cart)?$this->display_cart:($this->params->get('display_cart') == 1);
		//$display_cart_first = (isset($display_cart_first) && $display_cart_first === true);

		//$progresstext = '';
		//$carttext = '';
		//if($display_progress):
		//	ob_start();
		//	$this->includeLayout('progressbar','default');
		//	$progresstext = ob_get_contents();
		//	ob_end_clean();
		//endif;
		if($display_cart):
		//	$carttext = '<h3>'.JText::_('Donation Amounts').'</h3>';
			echo '<h3>'.JText::_('Donation Amounts').'</h3>';
		//	ob_start();
			FOFDispatcher::getTmpInstance('com_donorcart','cart',array('layout'=>'checkout','input'=>array('task'=>'read')))->dispatch();
		//	$carttext .= ob_get_contents();
		//	ob_end_clean();
		endif;
		//if($display_cart_first) {
		//	echo $carttext.$progresstext;
		//} else {
		//	echo $progresstext.$carttext;
		//}

	if(isset($this->include_form) && $this->include_form): ?>
		<form action="<?php echo JRoute::_('index.php?option=com_donorcart'); ?>" method="post">
	<?php endif; ?>
