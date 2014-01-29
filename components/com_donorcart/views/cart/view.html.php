<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartViewCart extends FOFViewHtml {
	function display($tpl = null) {
		$model = $this->getModel();
		$model->setState('task','read'); //force the read state for the display
		//$this->assign('total',$model->getTotal());
		$this->assign('subtotal',$model->getSubTotal());

		return parent::display($tpl);
	}
}
