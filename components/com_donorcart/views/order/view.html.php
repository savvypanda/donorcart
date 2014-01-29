<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartViewOrder extends FOFViewHtml {
	public function display($tpl = null){
		$params = JFactory::getApplication()->getParams();
		$this->assign('params',$params);

		return parent::display($tpl);
	}
}