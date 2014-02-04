<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCartItems extends FOFModel {
	public function __construct($config = array()) {
		$config=array_merge($config, array('table'=>'cart_items'));
		parent::__construct($config);
	}

/* All of this item deletion stuff should be happening in the cart model, not in the cartitems model
	protected function onBeforeDelete(&$id, &$table) {
		//first lets make sure that this item belongs to the current user's shopping cart
		$cart_id = JFactory::getSession()->get('cart_id',0);
		if(!$cart_id) return false;
		$query = 'SELECT * FROM #__donorcart_cart_items WHERE cart_id='.$this->_db->quote($cart_id).' AND donorcart_cart_item_id='.$this->_db->quote($id);
		$this->_db->setQuery($query);
		$this->_db->query();
		if($this->_db->getNumRows() != 1) {
			return false;
		}
		//now make sure that the order has not already been submitted.
		$query = 'SELECT * FROM #__donorcart_orders WHERE cart_id='.$this->_db->quote($cart_id).' AND submitted=1';
		$this->_db->setQuery($query);
		$this->_db->query();
		if($this->_db->getNumRows() != 0) {
			return false;
		}
		return parent::onBeforeDelete($id, $table);
	}

	protected function onAfterDelete($id) {
		//after we delete a cart item, we need to update the cart total (or delete the cart if it is now empty)
		$cart_id = JFactory::getSession()->get('cart_id',0);
		$query = 'SELECT * FROM #__donorcart_cart_items WHERE cart_id='.$this->_db->quote($cart_id);
		$this->_db->setQuery($query);
		$cartitems = $this->_db->loadObjectList();
		$subtotal = 0;
		foreach($cartitems as $item) {
			$subtotal += $item->qty * $item->price;
		}

		if($subtotal == 0) {
			//if the cart is empty now, we should delete it (and any related order)
			$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
			if($ordermodel->getId()) {
				$ordermodel->delete();
			} else {
				FOFModel::getAnInstance('carts','DonorcartModel')->delete();
			}
		} else {
			//we need to update the cart subtotal and the order total (if applicable)
			$query = 'UPDATE #__donorcart_carts SET subtotal='.$this->_db->quote($subtotal).' WHERE donorcart_cart_id='.$this->_db->quote($cart_id);
			$this->_db->setQuery($query);
			$this->_db->query();

			$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
			if($ordermodel->getId()) {
				$ordermodel->updateOrderTotal();
			}
		}

		$query = 'SELECT donorcart_order_id FROM #__donorcart_orders WHERE cart_id='.$this->_db->quote($this->_recordForDeletion->cart_id);
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if($result) {
			FOFModel::getTmpInstance('orders','DonorcartModel')->setId($result)->updateOrderTotal();
		}
		return parent::onAfterDelete($id);
	}
*/
}
