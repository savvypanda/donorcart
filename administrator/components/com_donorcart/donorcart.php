<?php
if(!defined('_JEXEC')) die('Restricted Access');
include_once JPATH_LIBRARIES.DIRECTORY_SEPARATOR.'fof'.DIRECTORY_SEPARATOR.'include.php';
if(defined('FOF_INCLUDED')) {
	FOFDispatcher::getTmpInstance('com_donorcart')->dispatch();
} else {
	JFactory::getApplication()->enqueueMessage('FOF is not installed','error');
	//JError::raiseError('500','FOF is not installed');
}
