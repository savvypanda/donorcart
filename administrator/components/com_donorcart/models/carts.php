<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCarts extends FOFModel {
	public function __construct($config = array()) {
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
}
