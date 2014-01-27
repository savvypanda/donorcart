<?php defined('_JEXEC') or die('Restricted Access');

class plgContentDonorcart extends JPlugin {
	public function onContentPrepare($context, &$row, &$params, $page) {
		if (is_object($row)) {
			return $this->_prepareContent($row->text, $params);
		}
		return $this->_prepareContent($row, $params);
	}

	private function _prepareContent(&$row, &$params) {
		$startpos = 0;
		while(($startpos = strpos($row, '{donorcart-add-to-cart', $startpos)) !== false) {
			$this->replace($row, $startpos++);
		}
		return true;
	}

	private function replace(&$row, $startpos) {
		$endpos = strpos($row, '}', $startpos+18);
		if(!$endpos) {
			return false;
		}

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
			'submit' => 'Give',
		);


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

		$form = $this->buildItemForm($options);
		if(!$form) {
			return false;
		}

		$row = substr_replace($row, $form, $startpos, $endpos-$startpos+1);
		return true;
	}

	private function buildItemForm($options) {
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

		$parts = array(
			'<form method="post" action="'.JRoute::_('index.php').'" class="dcartadd'.($options['skipprompt']?' dnoprompt':'').(empty($options['classname'])?'':' '.$options['classname']).'">',
			JHtml::_('form.token'),
			'<input type="hidden" name="option" value="com_donorcart">',
			'<input type="hidden" name="view" value="cart">',
			'<input type="hidden" name="task" value="addItem">',
			'<input type="hidden" name="format" value="raw">',
			'<input type="hidden" name="template" value="system">',
			'<input type="hidden" name="my-item-id" value="'.htmlentities($options['sku'], ENT_COMPAT).'">'
		);

		if($options['title']) {
			$parts[] = '<h3>'.JText::_($options['title']).'</h3>';
		}

		if($options['img']) {
			$parts[] = '<div class="dcart-item-image">';
			if($options['url']) {
				$parts[] = '<a href="'.JRoute::_($options['url']).'">';
			}
			$parts[] = '<img src="'.htmlentities($options['img'], ENT_COMPAT).'" alt="'.htmlentities($options['name'], ENT_COMPAT).'" />';
			if($options['url']) {
				$parts[] = '</a>';
			}
			$parts[] = '</div>';
		}

		$parts[] = '<div class="cart-item-form">';
		if($options['editname']) {
			$parts[] = '<div class="dcart-item-name dcart-editable">';
			if(!empty($options['namelabel'])) $parts[] = '<label for="my-item-name">'.JText::_($options['namelabel']).': </label>';
			$parts[] = '<input type="text" name="my-item-name" '.(($options['nameplaceholder'])?'placeholder':'value').'="'.htmlentities($options['name'], ENT_COMPAT).'" class="input-full">';
		} else {
			$parts[] = '<div class="dcart-item-name dcart-static">';
			if(!$options['hidename']) {
				$parts[] = '<span class="cart-item-name">'.$options['name'].'</span>';
			}
			$parts[] = '<input type="hidden" name="my-item-name" value="'.htmlentities($options['name'], ENT_COMPAT).'">';
		}
		$parts[] = '</div>';

		if(!empty($options['priceoptions'])) {
			$parts[] = '<div class="dcart-item-price dcart-selectlist">';

			$priceoptions = explode(',',$options['priceoptions']);
			$parts[] = '<select class="item-price-selector" onchange="this.form[\'my-item-price\'].value=this.value;"><option value="0">'.JText::_($options['pricelabel']).'</option>';
			foreach($priceoptions as $opt) {
				$parts[] = '<option value="'.htmlentities($opt, ENT_COMPAT).'">$'.$opt.'</option>';
			}
			$parts[] = '</select>';

			if($options['editprice']) {
				$parts[] = '<div class="dcart-item-price dcart-editable">';
				$parts[] = '<input type="text" name="my-item-price" '.(($options['priceplaceholder'])?'placeholder':'value').'="'.htmlentities($options['price'], ENT_COMPAT).'" class="input-mini">';
				$parts[] = '</div>';
			} else {
				$parts[] = '<input type="hidden" name="my-item-price" value="'.htmlentities($options['price'], ENT_COMPAT).'">';
			}
			$parts[] = '</div>';
		} else {
			if($options['editprice']) {
				$parts[] = '<div class="dcart-item-price dcart-editable">';
				if(!empty($options['pricelabel'])) $parts[] = '<label for="my-item-price" class="">'.JText::_($options['pricelabel']).': </label>';
				$parts[] = '<input type="text" name="my-item-price" '.(($options['priceplaceholder'])?'placeholder':'value').'="'.htmlentities($options['price'], ENT_COMPAT).'" class="input-mini">';
				$parts[] = '</div>';
			} else {
				if(!$options['hideprice']) {
					$parts[] = '<div class="dcart-item-price dcart-static">';
					$parts[] = '<span class="cart-item-amount">$'.$options['price'].'</span>';
					$parts[] ='</div>';
				}
				$parts[] = '<input type="hidden" name="my-item-price" value="'.htmlentities($options['price'], ENT_COMPAT).'">';
			}
		}

		if(!empty($options['qtyoptions'])) {
			$parts[] = '<div class="dcart-item-qty dcart-selectlist">';

			$qtyoptions = explode(',',$options['qtyoptions']);
			$parts[] = '<select name="item-qty-selector" onchange="this.form[\'my-item-qty\'].value=this.value;"><option value="0">'.JText::_($options['qtylabel']).'</option>';
			foreach($qtyoptions as $opt) {
				$parts[] = '<option value="'.htmlentities($opt, ENT_COMPAT).'">$'.$opt.'</option>';
			}
			$parts[] = '</select>';

			if($options['editqty']) {
				$parts[] = '<div class="dcart-item-qty dcart-editable">';
				$parts[] = '<input type="text" name="my-item-qty" '.(($options['qtyplaceholder'])?'placeholder':'value').'="'.htmlentities($options['qty'], ENT_COMPAT).'" class="input-mini">';
				$parts[] = '</div>';
			} else {
				$parts[] = '<input type="hidden" name="my-item-qty" value="'.htmlentities($options['qty'], ENT_COMPAT).'">';
			}
			$parts[] = '</div>';
		} else {
			if($options['editqty']) {
				$parts[] = '<div class="dcart-item-qty dcart-editable">';
				if(!empty($options['qtylabel'])) $parts[] = '<label for="my-item-qty">'.JText::_($options['qtylabel']).': </label>';
				$parts[] = '<input type="text" name="my-item-qty" '.(($options['qtyplaceholder'])?'placeholder':'value').'="'.htmlentities($options['qty'], ENT_COMPAT).'" class="input-mini">';
				$parts[] = '</div>';
			} else {
				$parts[] = '<input type="hidden" name="my-item-qty" value="'.htmlentities($options['qty'], ENT_COMPAT).'">';
			}
		}

		if($options['url']) {
			$parts[] = '<input type="hidden" name="my-item-url" value="'.htmlentities($options['url'], ENT_COMPAT).'">';
		}

		$parts[] = '<div class="dcart-item-add-button"><input type="submit" name="my-add-button" class="dcart-add-button" value="'.JText::_($options['submitlabel']).'"></div>';
		$parts[] = '</div>';
		$parts[] = '<div class="clear"></div>';
		$parts[] = '</form>';

		return implode('',$parts);
	}
}
