<?php

defined('_JEXEC') or die();

class DonorcartHelperFormat {
	public static function formatName($fname, $mname, $lname) {
		$name = trim(implode(' ', array($fname, $mname, $lname)));
		while(strpos($name, '  ') !== false) {
			$name = str_replace('  ', ' ', $name);
		}
		return $name;
	}

	public static function formatItems($items) {
		$itemstrings = array();
		if(is_array($items)) {
			foreach($items as $item) {
				$itemstrings[] = $item->qty.' '.$item->name.' ($'.($item->price*$item->qty).')';
			}
		}
		return implode('<br />',$itemstrings);
	}
}
