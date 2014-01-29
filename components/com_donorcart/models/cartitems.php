<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCartItems extends FOFModel {
	public function __construct($config = array()) {
		$config=array_merge($config, array('table'=>'cart_items'));
		parent::__construct($config);
	}

	protected function onBeforeDelete(&$id, &$table) {
		//first lets make sure that this item belongs to the current user
		$cart_id = JFactory::getSession()->get('cart_id',0);
		$query = 'SELECT * FROM #__donorcart_cart_items WHERE cart_id='.$this->_db->quote($cart_id).' AND donorcart_cart_item_id='.$this->_db->quote($id);
		$this->_db->setQuery($query);
		$this->_db->query();
		if($this->_db->getNumRows() != 1) {
			return false;
		}
		//now make sure that the order has not already been completed.
		$query = 'SELECT * FROM #__donorcart_orders WHERE cart_id='.$this->_db->quote($cart_id).' AND submitted=1';
		$this->_db->setQuery($query);
		$this->_db->query();
		if($this->_db->getNumRows() != 0) {
			return false;
		}
		return parent::onBeforeDelete($id, $table);
	}

	protected function onAfterDelete($id) {
		$query = 'SELECT donorcart_order_id FROM #__donorcart_orders WHERE cart_id='.$this->_db->quote($this->_recordForDeletion->cart_id);
		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
		if($result) {
			FOFModel::getTmpInstance('orders','DonorcartModel')->setId($result)->updateOrderTotal();
		}
		return parent::onAfterDelete($id);
	}
}
