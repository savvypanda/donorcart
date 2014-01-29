<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelPayments extends FOFModel {
	public function __construct($config = array()) {
		//$config=array_merge($config, array('table'=>'payments'));
		parent::__construct($config);
	}
}
