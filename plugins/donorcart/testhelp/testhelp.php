<?php defined('_JEXEC') or die('Restricted Access');

class plgDonorcartTesthelp extends JPlugin {
	public function onDisplayPaymentForm($order, $params) {
		return "<p><strong>THIS CHECKOUT APPLICATION IS CURRENTLY AUGMENTED BY A TEST SCRIPT. REMOVE THE TESTHELP PLUGIN BEFORE GOING LIVE.</strong></p>";
	}

	public function onDisplayPaymentInfo($order) {
		return "<p><strong>THIS CHECKOUT APPLICATION IS CURRENTLY AUGMENTED BY A TEST SCRIPT. REMOVE THE TESTHELP PLUGIN BEFORE GOING LIVE.</strong></p>";
	}

	public function onBeforePostback($order, $plugin_validated) {
		$code = $this->params->get('code','');
		ob_start();
		eval($code);
		$returnval = ob_get_contents();
		ob_end_clean();
		return $returnval;
	}
}
