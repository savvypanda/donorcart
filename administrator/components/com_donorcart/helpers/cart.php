<?php defined('_JEXEC') or die();

class DonorcartHelperCart {
	public static function getMyCart($create_if_not_exists = false) {
		//get the cart for the current user.
		$cart_id = JFactory::getSession()->get('cart_id',0);
		if(!$cart_id) {
			if(!$create_if_not_exists) {
				return false;
			}
			$cart_id = self::createEmptyCart();
		}
		return FOFModel::getTmpInstance('cart','DonorcartModel')->donorcart_cart_id($cart_id)->getItem();
	}

	public static function createEmptyCart() {
		$newcart = FOFModel::getTmpInstance('cart','DonorcartModel');
		if(!$newcart->save(array())) {
			//JError::raiseError(500,$newcart->getError());
			JFactory::getApplication()->enqueueMessage($newcart->getError(),'error');
			return false;
		}
		$cart_id = $newcart->donorcart_cart_id;
		JFactory::getSession()->set('cart_id',$cart_id);
		return $cart_id;
	}

	public static function addItemToCart($sku, $name, $price = '0', $qty = '1', $url = '') {
		$cart = self::getMyCart();
		if(!$cart) {

		}
		$this->getItemList();
		$price = doubleval($price);
		$qty = abs(intval($qty));
		foreach($this->list as $id => $item) {
			if($item['sku']==$sku && $item['name']==$name && $item['price']==$price) {
				$this->list[$id]['qty'] += $qty;
				return true;
			}
		}
		$this->list[] = array('sku'=>$sku,'name'=>$name,'price'=>$price,'qty'=>$qty,'url'=>$url);
		return true;
	}
}
