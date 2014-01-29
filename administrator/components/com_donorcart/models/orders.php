<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelOrders extends FOFModel {
	private static $statuslist = false;

	public static function getStatusList() {
		if(!self::$statuslist) {
			$db = JFactory::getDbo();
			$query = 'SELECT DISTINCT status FROM #__donorcart_orders';
			$db->setQuery($query);
			self::$statuslist = $db->loadColumn();
		}
		return self::$statuslist;
	}

	public function __construct($config = array()) {
		parent::__construct($config);

		$statusfilter = JRequest::getString('statusfilter');
		if(!is_null($statusfilter) && $statusfilter != $this->getState('statusfilter',false)) $this->setState('statusfilter',$statusfilter);

		$itemfilter = JRequest::getString('itemfilter');
		if(!is_null($itemfilter) && $itemfilter != $this->getState('itemfilter',false)) $this->setState('itemfilter', $itemfilter);

		$emailfilter = JRequest::getString('emailfilter');
		if(!is_null($emailfilter) && $emailfilter != $this->getState('emailfilter',false)) $this->setState('emailfilter',$emailfilter);

		$startdate = JRequest::getString('startdate');
		if(!is_null($startdate) && $startdate != $this->getState('startdate',false)) $this->setState('startdate',$startdate);

		$enddate = JRequest::getString('enddate');
		if(!is_null($enddate) && $enddate != $this->getState('enddate',false)) $this->setState('enddate',$enddate);
	}

	protected function onProcessList(&$resultArray) {
		foreach($resultArray as &$item) {
			//$item = $this->getItem($item->donorcart_order_id);
			$this->onAfterGetItem($item);
		}
	}

	public function buildQuery($overrideLimits = false) {
		$query = $this->_db->getQuery(true)->select('DISTINCT o.*')->from('#__donorcart_orders o');

		if($statusfilter = $this->getState('statusfilter')) {
			$query->where('o.status='.$this->_db->quote($statusfilter));
		}
		if($itemfilter = $this->getState('itemfilter')) {
			$query->innerJoin('#__donorcart_cart_items i ON o.cart_id = i.cart_id');
			$query->where(' ( i.name LIKE '.$this->_db->quote('%'.$itemfilter.'%').' OR i.sku LIKE '.$this->_db->quote('%'.$itemfilter.'%').' ) ');
		}
		if($emailfilter = $this->getState('emailfilter')) {
			$query->where('o.email LIKE '.$this->_db->quote('%'.$emailfilter.'%'));
		}
		if($startdate = $this->getState('startdate')) {
			$query->where('o.created_on >= '.$this->_db->quote($startdate));
		}
		if($enddate = $this->getState('enddate')) {
			$query->where('o.created_on <= '.$this->_db->quote($enddate));
		}

		if (!$overrideLimits) {
			$order = $this->getState('filter_order', null, 'cmd');
			if (!in_array($order, array_keys($this->getTable()->getData()))) {
				$order = 'donorcart_order_id';
			}
			$dir = $this->getState('filter_order_Dir', 'ASC', 'cmd');
			$query->order($this->_db->qn($order).' '.$dir);
		}

		return $query;
	}

	protected function onAfterGetItem(&$record) {
		parent::onAfterGetItem($record);
		if($record->cart_id) {
			$record->cart = FOFModel::getTmpInstance('carts','DonorcartModel')->getItem($record->cart_id);
		}
		if($record->shipping_address_id) {
			$record->shipping_address = FOFModel::getTmpInstance('addresses','DonorcartModel')->getItem($record->shipping_address_id);
		}
		if($record->billing_address_id) {
			$record->billing_address = FOFModel::getTmpInstance('addresses','DonorcartModel')->getItem($record->billing_address_id);
		}
		if($record->payment_id) {
			$record->payment = FOFModel::getTmpInstance('payments','DonorcartModel')->getItem($record->payment_id);
		}
		if($record->donorcart_order_id) {
			$record->custom_fields = FOFModel::getTmpInstance('customFields','DonorcartModel')->order_id($record->donorcart_order_id)->getItemList(true);
		}
	}

	protected function onBeforeDelete(&$id, &$table) {
		$record = $this->getItem($id);
		$result = parent::onBeforeDelete($id, $table);
		if($result) {
			//first remove all custom fields assiciated with this order
			$custom_fields = FOFModel::getTmpInstance('customFields','DonorcartModel')->order_id($id)->getItemList(true);
			if(!empty($custom_fields)) {
				foreach($custom_fields as $field) {
					FOFModel::getTmpInstance('customFields','DonorcartModel')->setId($field->donorcart_custom_field_id)->delete();
				}
			}
			//then remove any non-locked addresses associated with this order
			if($record->shipping_address_id && $record->shipping_address->locked != 1) {
				FOFModel::getTmpInstance('addresses','DonorcartModel')->setId($record->shipping_address_id)->delete();
			}
			if($record->billing_address_id && $record->shipping_address_id != $record->billing_address_id && $record->billing_address->locked != 1) {
				FOFModel::getTmpInstance('addresses','DonorcartModel')->setId($record->billing_address_id)->delete();
			}
			//then remove any payment associated with this order
			if($record->payment_id) {
				FOFModel::getTmpInstance('payments','DonorcartModel')->setId($record->payment_id)->delete();
			}
			if($record->cart_id) {
				FOFModel::getTmpInstance('carts','DonorcartModel')->setId($record->cart_id)->delete();
			}
		}
		return $result;
	}
}
