<?php
defined('_JEXEC') or die('Restricted access');

include_once JPATH_LIBRARIES.DIRECTORY_SEPARATOR.'fof'.DIRECTORY_SEPARATOR.'include.php';
if(defined('FOF_INCLUDED')) {
	FOFDispatcher::getTmpInstance('com_donorcart','cart',array('layout'=>'loader','input'=>array('task'=>'read')))->dispatch();
} else {
	//JError::raiseError('500','FOF is not installed');
	JFactory::getApplication()->enqueueMessage('FOF is not installed','error');
}
