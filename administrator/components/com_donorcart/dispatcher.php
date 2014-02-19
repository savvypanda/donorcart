<?php
defined('_JEXEC') or die('Restricted Access');

class DonorcartDispatcher extends FOFDispatcher {
	public $defaultView = 'orders';

	public function dispatch() {
		return parent::dispatch();
	}
}
