<?php
defined('_JEXEC') or die('Restricted Access');

class DonorcartDispatcher extends FOFDispatcher {
	public $defaultView = 'checkout';

	public function dispatch() {
		parent::dispatch();
	}
}
