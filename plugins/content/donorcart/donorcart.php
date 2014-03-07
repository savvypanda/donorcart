<?php defined('_JEXEC') or die('Restricted Access');

class plgContentDonorcart extends JPlugin {
	private $componentParams;

	public function __construct(&$subject, $config=array()){
		parent::__construct($subject, $config);
		$this->componentParams = JComponentHelper::getParams('com_donorcart');
	}

	public function onContentPrepare($context, &$row, &$params, $page) {
		if (is_object($row)) {
			return $this->_prepareContent($row->text, $params);
		}
		return $this->_prepareContent($row, $params);
	}

	private function _prepareContent(&$row, &$params) {
		$startpos = 0;
		while(($startpos = strpos($row, '{donorcart-add-to-cart', $startpos)) !== false) {
			include_once(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'includes.php');
			$this->replace($row, $startpos++);
		}
		return true;
	}

	private function replace(&$row, $startpos) {
		$endpos = strpos($row, '}', $startpos+18);
		if(!$endpos) {
			return false;
		}

		//set defaults
		$options = array(
			'title' => '',
			'sku' => '',
			'name' => '',
			'price' => '',
			'qty' => '',
			'url' => '',
			'img' => '',
			'submitlabel' => 'Give',
			'editqty' => false,
			'editprice' => false,
			'editname' => false,
			'hideprice' => false,
			'hideqty' => false,
			'hidename' => false,
			'skipprompt' => false,
			'classname' => '',
			'pricelabel' => 'Gift Amount',
			'namelabel' => 'Name',
			'qtylabel' => 'Qty',
			'priceoptions' => '',
			'qtyoptions' => '',
			'nameplaceholder' => false,
			'qtyplaceholder' => false,
			'priceplaceholder' => false,
			'recurringlabel' => '',
			'submit' => 'Give',
		);

		//fetch options from the plugin tag
		$argc = preg_match_all('/([a-zA-Z]+)=(?|"([^"]*)"|([^ ]*))/', substr($row, $startpos+19, $endpos-$startpos-19), $matches);
		for($i = 0; $i<$argc; $i++) {
			switch($matches[1][$i]) {
				case 'title':
				case 'sku':
				case 'name':
				case 'price':
				case 'qty':
				case 'url':
				case 'img':
				case 'submitlabel':
				case 'recurringlabel':
				case 'classname':
				case 'pricelabel':
				case 'namelabel':
				case 'qtylabel':
				case 'priceoptions':
				case 'qtyoptions':
					$options[$matches[1][$i]] = $matches[2][$i];
					break;
				case 'editqty':
				case 'editprice':
				case 'editname':
				case 'hidename':
				case 'hideqty':
				case 'hideprice':
				case 'skipprompt':
				case 'qtyplaceholder':
				case 'nameplaceholder':
				case 'priceplaceholder':
					$options[$matches[1][$i]] = true;
					break;
			}
		}

		//basic validations and transformations
		if(!$options['sku']) {
			return false;
		}
		if(!$options['name']) {
			$options['editname'] = true;
		}
		if(!$options['price'] && empty($options['priceoptions'])) {
			$options['editprice'] = true;
			$options['editqty'] = false;
			$options['qty'] = '1';
		}
		if(!empty($options['priceoptions'])) {
			$options['editprice'] = false;
			$options['editqty'] = false;
			$options['qty'] = '1';
		}
		if(!$options['qty'] && empty($options['qtyoptions'])) {
			$options['editqty'] = true;
			$options['qty'] = '1';
		}
		if(!empty($options['qtyoptions'])) {
			$options['editqty'] = false;
		}




		$path = JPluginHelper::getLayoutPath('content', 'donorcart');
		$form = '';
		if(file_exists($path)) {
			$this->loadLanguage();
			ob_start();
			include $path;
			$form = ob_get_clean();
		}
		if(empty($form)) {
			return false;
		}

		$row = substr_replace($row, $form, $startpos, $endpos-$startpos+1);
		return true;
	}
}
