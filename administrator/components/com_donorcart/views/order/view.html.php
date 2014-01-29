<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartViewOrder extends FOFViewHtml {
	function display($tpl = null) {
		include_once(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'format.php');
		return parent::display($tpl);
	}
}
