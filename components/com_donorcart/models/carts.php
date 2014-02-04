<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCarts extends FOFModel {

	public function __construct($config = array()) {
		//$my_id = JFactory::getSession()->get('cart_id',0);
		//$config=array_merge($config, array('table'=>'carts','id'=>$my_id));
		$config['id'] = JFactory::getSession()->get('cart_id',0);
		parent::__construct($config);
	}

	protected function onAfterGetItem(&$record) {
		parent::onAfterGetItem($record);
		if($record->donorcart_cart_id) {
			$record->items = FOFModel::getTmpInstance('cartItems','DonorcartModel')->cart_id($record->donorcart_cart_id)->getItemList(true,'donorcart_cart_item_id');
		}
	}

	protected function onBeforeDelete(&$id, &$table) {
		$result = parent::onBeforeDelete($id, $table);
		if($result) {
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
			$ordermodel = false;
		} else {
			//We need to make sure it's not part of a submitted order
			$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
			if($ordermodel->getid() && $ordermodel->getItem()->submitted) return false;
		}
		$this->getItem();
		$price = doubleval($price);
		$qty = abs(intval($qty));

		$subtotal = 0;
		$itemadded = false;
		//first let's make sure that we do not already have the same item in our cart
		//we can also track the new subtotal at the same time
		foreach($this->record->items as $id => &$item) {
			if(!$itemadded && $item->sku==$sku && $item->name==$name && $item->price==$price) {
				//if we already have this item in our cart, we just need to update the quantity!
				$item->qty += $qty;
				$query = 'UPDATE #__donorcart_cart_items SET qty='.$this->_db->quote($item->qty).' WHERE donorcart_cart_item_id='.$id;
				$this->_db->setQuery($query);
				$this->_db->query();
				$itemadded = true;
			}
			$subtotal += $item->qty * $item->price;
		}

		if(!$itemadded) {
			//We are adding a new item to the cart
			$query = sprintf('INSERT INTO #__donorcart_cart_items(`cart_id`, `sku`, `name`, `price`, `qty`, `url`) VALUES (%s, %s, %s, %s, %s, %s)',
				$this->_db->quote($this->id),
				$this->_db->quote($sku),
				$this->_db->quote($name),
				$this->_db->quote($price),
				$this->_db->quote($qty),
				$this->_db->quote($url)
			);
			$this->_db->setQuery($query);
			$this->_db->query();
			//$this->record->items[] = $item;
			$subtotal += $qty * $price;
		}
		$query = 'UPDATE #__donorcart_carts SET subtotal='.$this->_db->quote($subtotal).' WHERE donorcart_cart_id='.$this->id;
		$this->_db->setQuery($query);
		$this->_db->query();
		//$this->record->subtotal = $subtotal;
		if($ordermodel && $ordermodel->getId()) {
			$ordermodel->updateOrderTotal($subtotal);
		}
		return true;
	}

	public function removeItemFromCart($cart_item_id) {
		if(!$this->id) {
			//if we do not already have a cart, we cannot remove any item from it!
			return false;
		}
		//Before modifying the cart, we need to make sure it's not part of a submitted order
		$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
		if($ordermodel->getid() && $ordermodel->getItem()->submitted) return false;

		$this->getItem();
		$subtotal = 0;
		$itemremoved = false;
		foreach($this->record->items as $id => &$item) {
			if($id == $cart_item_id) {
				if(FOFModel::getTmpInstance('cartItems','DonorcartModel')->setId($id)->delete()) {
					$itemremoved = true;
				} else {
					return false;
				}
			} else {
				$subtotal += $item->qty * $item->price;
			}
		}
		if(!$itemremoved) return false;

		if($subtotal == 0) {
			//if the cart is empty now, we should delete it (and any related order)
			if($ordermodel->getId()) {
				return $ordermodel->delete();
			} else {
				return $this->delete();
			}
		} else {
			//we need to update the cart subtotal and the order total (if applicable)
			$query = 'UPDATE #__donorcart_carts SET subtotal='.$this->_db->quote($subtotal).' WHERE donorcart_cart_id='.$this->_db->quote($cart_id);
			$this->_db->setQuery($query);
			$this->_db->query();

			$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
			if($ordermodel->getId()) {
				$ordermodel->updateOrderTotal($subtotal);
			}
		}

		return true;
	}

	public function emptyCart() {
		if(!$this->id) return true;
		$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
		if($ordermodel->getid()) {
			if($ordermodel->getItem()->submitted) {
				return false;
			} else {
				return $ordermodel->delete();
			}
		} else {
			return $this->delete();
		}
	}
}
