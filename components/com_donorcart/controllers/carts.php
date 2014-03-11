<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartControllerCarts extends FOFController {
	public function __construct($config = array()) {
		//$config['table'] = 'orders';
		parent::__construct($config);
		$this->registerTask('remove','_remove_item');
		$this->registerTask('addItem','_add_item');
		$this->registerTask('empty','_empty_cart');
		//$this->registerTask('setRecurring','_enable_recurring');
		//$this->registerTask('setNoRecurring','_disable_recurring');
	}

	public function execute($task) {
		if(in_array($task,array('add','edit','read'))) {
			$task = 'read';
		}

		return parent::execute($task);
	}

	/* protected function onBeforeRead() {
		return $this->checkACL('cart.view');
	} */

	protected function onBeforeRemove() {
		return true;
		//return $this->checkACL('cart.view');
	}

/*	public function display($cachable = false, $urlparams = false) {
		parent::display($cachable, $urlparams);
	} */

	//public function getItem($id = null) {
	//	return null;
	//}

	public function _remove_item() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$id = JRequest::getInt('item',null);
		FOFModel::getAnInstance('carts','DonorcartModel')->removeItemFromCart($id);
		return $this->display();
		return true;
	}


	public function _add_item() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$sku = JRequest::getString('my-item-id',null);
		$name = JRequest::getString('my-item-name',null);
		$price = JRequest::getFloat('my-item-price',null);
		$qty = JRequest::getInt('my-item-qty',1);
		$url = JRequest::getString('my-item-url','');
		$img = JRequest::getString('my-item-img','');
		$recurring = JRequest::getString('recurring',false);
		$dedication = false;
		$dedication_name = JRequest::getString('dedication_name',false);
		if($dedication_name) {
			$dedication_email = JRequest::getString('dedication_email',false);
			$dedication_note = JRequest::getString('dedication_note',false);
			$dedication = json_encode(array('name'=>$dedication_name,'email'=>$dedication_email,'note'=>$dedication_note));
		}
		if(empty($sku) || empty($name) || empty($price)) {
			//JError::raiseError(500,'Invalid product.<br />Sku = '.$sku.'<br />Name='.$name.'<br />Price='.$price);
			JFactory::getApplication()->enqueueMessage('Invalid product.<br />Sku = '.$sku.'<br />Name='.$name.'<br />Price='.$price, 'error');
		} else {
			FOFModel::getAnInstance('carts','DonorcartModel')->addItemToCart($sku, $name, $price, $qty, $url, $img);
		}
		if($recurring || $dedication) {
			$ordermodel = $this->getModel('orders','DonorcartModel');
			$order_id = $ordermodel->getId();
			if($order_id) {
				$db = JFactory::getDbo();
				$setvals = array();
				if($recurring) $setvals[] = 'recurring_frequency='.$db->quote($recurring);
				if($dedication) $setvals[] = ', dedication='.$db->quote($dedication);
				$query = 'UPDATE #__donorcart_orders SET '.implode(', ',$setvals).' WHERE donorcart_order_id='.$db->quote($order_id);
				$db->setQuery($query);
				$db->query();
			} else {
				$ordermodel->createOrder($recurring, $dedication);
			}
		}
		return $this->display();
	}

	/* public function _enable_recurring() {
		FOFModel::getAnInstance('carts','DonorcartModel')->enableRecurring();
		return $this->display();
	}
	public function _disable_recurring() {
		FOFModel::getAnInstance('carts','DonorcartModel')->disableRecurring();
		return $this->display();
	} */

	public function _empty_cart() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		FOFModel::getAnInstance('carts','DonorcartModel')->emptyCart();
		return $this->display();
	}
}
