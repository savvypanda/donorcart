<?php defined('_JEXEC') or die("Restricted Access"); ?>

<h3>Checkout: Payment</h3>
<div>
	<?php
	JPluginHelper::importPlugin('donorcart');
	$dispatcher = JDispatcher::getInstance();
	$results = $dispatcher->trigger('onDisplayPaymentForm', array(&$this->order, $this->params));
	foreach($results as $result):
		if(is_string($result)) echo $result;
	endforeach;
	?>
</div>
