<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelCustomFields extends FOFModel {
	public function __construct($config = array()) {
		$config=array_merge($config, array('table'=>'custom_fields'));
		parent::__construct($config);
	}
}
