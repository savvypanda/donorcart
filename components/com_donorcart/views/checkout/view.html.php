<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartViewCheckout extends FOFViewHtml {
	public function display($tpl = null) {
		$user = JFactory::getUser();
		$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
		$order = $ordermodel->getItem();
		//$ordermodel->calcOrderTotal($order);
		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_DONORCART_CHECKOUT_PAGE_TITLE'));
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

		$this->assign('order',$order);
		$this->assign('user',$user);

		/* if($error = JRequest::getString('error')) {
			$this->assign('error',$error);
		} */

		return parent::display();
	}

	/* public function includeLayout($tpl, $layout = false) {
		if (version_compare(JVERSION, '3.0', 'lt')) JError::setErrorHandling(E_ALL,'ignore');
		if($layout && $layout != $this->_layout) {
			$previouslayout = $this->_layout;
			$this->_layout = $layout;
			$result = $this->loadTemplate($tpl,true);
			$this->_layout = $previouslayout;
		} else {
			$result = $this->loadTemplate($tpl,true);
		}
		if (version_compare(JVERSION, '3.0', 'lt')) JError::setErrorHandling(E_WARNING,'callback');

		if ($result instanceof Exception) {
			//JError::raiseError($result->getCode(), $result->getMessage());
			JFactory::getApplication()->enqueueMessage($result->getMessage(),'error');
		} else {
			echo $result;
		}
	} */
}
