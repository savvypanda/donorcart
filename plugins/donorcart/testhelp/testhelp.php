<?php defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php');

class plgDonorcartTesthelp extends JPluginDonorcart {
	protected $_name = 'testhelp';

	public function onDisplayPaymentForm($order, $params) {
		return "<p><strong>THIS CHECKOUT APPLICATION IS CURRENTLY AUGMENTED BY A TEST SCRIPT. REMOVE THE TESTHELP PLUGIN BEFORE GOING LIVE.</strong></p>";
	}

	public function onDisplayPaymentInfo($order, $params, $payment_name) {
		return "<p><strong>THIS CHECKOUT APPLICATION IS CURRENTLY AUGMENTED BY A TEST SCRIPT. REMOVE THE TESTHELP PLUGIN BEFORE GOING LIVE.</strong></p>";
	}

	public function onBeforePostback($plugin_validated) {
		$code = $this->params->get('code','');
		ob_start();
		eval($code);
		$returnval = ob_get_contents();
		ob_end_clean();
		return $returnval;
	}
}
