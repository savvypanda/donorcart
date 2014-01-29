<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartControllerPostback extends FOFController {
	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function display($cachable = false, $urlparams = false) {
		return false;
		//return parent::display($cachable, $urlparams);
	}

	public function execute($task) {
		//this request would not originate from the Joomla website.
		//We cannot validate it with the regular token method.
		//It is up to the plugins to validate the request origin.

		//default to invalid. It is only valid if one of the plugins says it is valid.
		//the request validation should occur in the onBeforePostback event.
		$is_valid = false;
		$plugin_validated = '';
		$returnval = '';
		$post = JRequest::get('POST');

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onBeforePostback', array(&$post, &$plugin_validated));
		foreach($results as $result) {
			if($result === true) {
				$is_valid = true;
			} elseif(is_string($result)) {
				$returnval .= $result;
			}
		}
		$results = $dispatcher->trigger('onPostback', array(&$post, &$is_valid, &$plugin_validated));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			} elseif(is_string($result)) {
				$returnval .= $result;
			}
		}
		$results = $dispatcher->trigger('onAfterPostback', array(&$post, $is_valid, &$plugin_validated));
		foreach($results as $result) {
			if(is_string($result)) {
				$returnval .= $result;
			}
		}

		return $returnval;
	}
}