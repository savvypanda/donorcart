<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartControllerCarts extends FOFController {
	public function __construct($config = array()) {
		//$config['table'] = 'orders';
		parent::__construct($config);
		$this->registerTask('remove','_remove_item');
		$this->registerTask('addItem','_add_item');
		$this->registerTask('empty','_empty_cart');
	}

	public function execute($task) {
		if(in_array($task,array('add','edit','read'))) {
			$task = 'read';
		}

		return parent::execute($task);
	}

	/* protected function onBeforeRead() {
		return $this->checkACL('cart.view');
	}

	protected function onBeforeRemove() {
		return $this->checkACL('cart.view');
	} */

/*	public function display($cachable = false, $urlparams = false) {
		parent::display($cachable, $urlparams);
	} */

	public function getItem($id = null) {
		return null;
	}

	public function _remove_item() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$id = JRequest::getInt('item',null);
		FOFModel::getAnInstance('cart_items','DonorcartModel')->setId($id)->delete();
		return $this->display();
	}


	public function _add_item() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$sku = JRequest::getString('my-item-id',null);
		$name = JRequest::getString('my-item-name',null);
		$price = JRequest::getFloat('my-item-price',null);
		$qty = JRequest::getInt('my-item-qty',1);
		$url = JRequest::getString('my-item-url','');
		if(empty($sku) || empty($name) || empty($price)) {
			//JError::raiseError(500,'Invalid product.<br />Sku = '.$sku.'<br />Name='.$name.'<br />Price='.$price);
			JFactory::getApplication()->enqueueMessage('Invalid product.<br />Sku = '.$sku.'<br />Name='.$name.'<br />Price='.$price, 'error');
		} else {
			FOFModel::getAnInstance('carts','DonorcartModel')->addItemToCart($sku, $name, $price, $qty, $url);
		}
		return $this->display();
	}

	public function _empty_cart() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$cart_id = FOFModel::getAnInstance('carts','DonorcartModel')->getId();
		if($cart_id) {
			$cart_items = FOFModel::getTmpInstance('cartItems','DonorcartModel')->cart_id($cart_id)->getItemList(true);
			if(!empty($cart_items)) {
				foreach($cart_items as $item) {
					FOFModel::getTmpInstance('cartItems','DonorcartModel')->setId($item->donorcart_cart_item_id)->delete();
				}
			}
		}
		return $this->display();
	}

}
