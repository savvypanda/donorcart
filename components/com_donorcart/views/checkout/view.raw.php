<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartViewCheckout extends FOFViewHtml {
	public function display() {
		$this->assign('user',JFactory::getUser());
		$this->assign('params',JComponentHelper::getParams('com_donorcart'));
		if($error = JRequest::getString('error')) {
			$this->assign('error',$error);
		}
		return parent::display();
	}
}
