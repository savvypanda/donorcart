<?php
defined('_JEXEC') or die();

class DonorcartToolbar extends FOFToolbar {
	public function onBrowse() {
		JToolBarHelper::title(JText::_('COM_DONORCART').' &ndash; <small>'.JText::_('COM_DONORCART_TITLE_'.strtoupper(FOFInput::getCmd('view','cpanel',$this->input))).'</small>', 'donorcart');

		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_donorcart');
	}
}
