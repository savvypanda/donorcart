<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartViewOrders extends FOFViewHtml {
	function display($tpl = null) {
		include_once(JPATH_COMPONENT_ADMINISTRATOR.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'format.php');

		$model = $this->getModel();
		$statuses = $model->getStatusList();
		$this->statuslist = array('' =>  JText::_('COM_DONORCART_ORDERS_FILTER_STATUS'));
		foreach($statuses as $status) $this->statuslist[htmlspecialchars($status)] = $status;

		$this->statusfilter = $model->getState('statusfilter','');
		$this->itemfilter = $model->getState('itemfilter','');
		$this->emailfilter = $model->getState('emailfilter','');
		$this->startdate = $model->getState('startdate', '');
		$this->enddate = $model->getState('enddate', '');

//		JToolbarHelper::custom('browse','filter','filter','Filter',false);
//		JToolbarHelper::custom('clearfilter','filter','filter','Clear Filters',false);

		return parent::display($tpl);
	}
}
