<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCarts extends FOFModel {
	//public $items = array();

	public function __construct($config = array()) {
		//$my_id = JFactory::getSession()->get('cart_id',0);
		//$config=array_merge($config, array('table'=>'carts','id'=>$my_id));
		$config['id'] = JFactory::getSession()->get('cart_id',0);
		parent::__construct($config);
	}

	/* public function getSubTotal() {
		$record = $this->getItem();
		$subtotal = 0;
		foreach($record->items as $item) {
			$subtotal += $item->qty * $item->price;
		}
		return $subtotal;
	} */

	protected function onAfterGetItem(&$record) {
		parent::onAfterGetItem($record);
		if($record->donorcart_cart_id) {
			$record->items = FOFModel::getTmpInstance('cartItems','DonorcartModel')->cart_id($record->donorcart_cart_id)->getItemList(true,'donorcart_cart_item_id');
		}
	}

	protected function onBeforeDelete(&$id, &$table) {
		//first lets make sure that this cart is not part of an order
		$query = 'SELECT * FROM #__donorcart_orders WHERE cart_id='.$this->_db->quote($id);
		$this->_db->setQuery($query);
		$this->_db->query();
		$numrows = $this->_db->getNumRows();
		if($numrows != 0) return false;

		//it's not part of an order. We can delete it.
		$result = parent::onBeforeDelete($id, $table);
		if($result) {
			//$table->load($id);
			$cart_items = FOFModel::getTmpInstance('cartItems','DonorcartModel')->cart_id($id)->getItemList(true);
			if(!empty($cart_items)) {
				foreach($cart_items as $item) {
					if(!FOFModel::getTmpInstance('cartItems','DonorcartModel')->setId($item->donorcart_cart_item_id)->delete()) {
						$result = false;
					}
				}
			}
		}
		return $result;
	}

	protected function onAfterDelete($id) {
		JFactory::getSession()->set('cart_id',null);
	}

	/*public function fetchCartFromSession($create_if_not_exists = true) {
		$cart_id = JFactory::getSession()->get('cart_id');
		if($cart_id) {
			return $this->setId($cart_id);
		}
		if($create_if_not_exists) {
			return $this->createEmptyCart();
		}
		//return false if the cart is not set in the session and create_if_not_exists != true
		return false;
	}*/

	public function createEmptyCart() {
		$cart = $this->getTable();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$data = array();
		if($user->id) {
			$data['user_id'] = $user->id;
		} else {
			$data['session_id'] = $session->getId();
		}
		if(!$cart->store($data)) {
			return false;
		};
		$session->set('cart_id',$cart->donorcart_cart_id);
		return $this->setId($cart->donorcart_cart_id);
	}

	public function addItemToCart($sku, $name, $price = '0', $qty = '1', $url = '') {
		if(!$this->id) {
			//if we do not already have a cart, create one and then proceed
			$this->createEmptyCart();
		}
		$this->getItem();
		$price = doubleval($price);
		$qty = abs(intval($qty));

		//first let's make sure that we do not already have the same item in our cart
		foreach($this->record->items as $id => &$item) {
			if($item->sku==$sku && $item->name==$name && $item->price==$price) {
				//if we already have this item in our cart, we just need to update the quantity!
				$item->qty += $qty;
				$model = FOFModel::getTmpInstance('cartItems','DonorcartModel');
				$data = $model->getItem($id);
				$data->qty = $item->qty;
				return $model->save($data);
			}
		}

		//We are adding a new item to the cart
		$data = array('cart_id'=>$this->id,'sku'=>$sku,'name'=>$name,'price'=>$price,'qty'=>$qty,'url'=>$url);
		$item = FOFModel::getTmpInstance('cartItems','DonorcartModel');
		if(!$item->save($data)) {
			//JError::raiseError(500,$item->getError());
			JFactory::getApplication()->enqueueMessage($item->getError(),'error');
			return false;
		}
		$this->record->items[] = $item;
		return true;
	}
}
