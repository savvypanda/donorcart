<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCartItems extends FOFModel {
	public function __construct($config = array()) {
		$config=array_merge($config, array('table'=>'cart_items'));
		parent::__construct($config);
	}

	protected function onBeforeDelete(&$id, &$table) {
		return parent::onBeforeDelete($id, $table);
	}
}
