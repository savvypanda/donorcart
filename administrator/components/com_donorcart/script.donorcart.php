<?php
defined('_JEXEC') or die('Restricted access');
 
// include required libraries
jimport('joomla.application.component.helper');

 
/**
 * Script file of Donorcart component
 */
class com_donorcartInstallerScript {

	/*
     * method to install the component
	 *
	 * @return void
	*/
	function install($parent) {
		JFactory::getApplication()->enqueueMessage(JText::_('COM_DONORCART_INSTALL_TEXT'));
	}

	/*
	 * method to uninstall the component
	 *
	 * @return void
	*/
	function uninstall($parent) {
		echo '<p>'.JText::_('COM_DONORCART_UNINSTALL_TEXT').'</p>';
	}

	/*
	 * method to update the component
	 *
	 * @return void
	*/
	function update($parent) {
		JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_DONORCART_UPDATE_TEXT', $parent->get('manifest')->version));
	}

	/*
	 * method to run before an install/update/discover_install method
	 *
	 * @return void
	*/
	function preflight($type, $parent) {
		//There is currently nothing we want to do here
	}

	/*
	 * method to run after an install/update/discover_install method
	 *
	 * @return void
	*/
	function postflight($type, $parent) {
		//$parent->getParent()->setRedirectURL('index.php?option=com_donorcart');
	}
}
