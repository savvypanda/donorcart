<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartViewCheckout extends FOFViewHtml {
	public function display($tpl = null) {
		$user = JFactory::getUser();
		$layout = $this->getLayout();
		if(empty($this->params)) {
			$this->assign('params',JComponentHelper::getParams('com_donorcart'));
		}

		if($layout == 'default') {
			$prior_addresses = array();
			if($user->id) {
				$prior_addresses = FOFModel::getTmpInstance('addresses','DonorcartModel')->user_id($user->id)->locked(1)->getItemList(true);
			}
			$this->assign('prior_addresses',$prior_addresses);
		}

		$this->assign('user',$user);

		return parent::display();
	}

	protected function onDisplay($tpl = null) {
		return $this->onAdd($tpl);
	}
}
