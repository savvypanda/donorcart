<?php defined("_JEXEC") or die("Restricted Access");

JText::script('COM_DONORCART_JS_ADD_TO_CART_LOADING',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_FAILURE_TITLE',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_FAILURE',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_SUCCESS_TITLE',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_SUCCESS',true);
JText::script('COM_DONORCART_JS_PROCEED_TO_CHECKOUT',true);
JText::script('COM_DONORCART_JS_CONTINUE_SHOPPING',true);

$donorcart_params = JComponentHelper::getParams('com_donorcart');
if($donorcart_params->get('load_jquery')) {
	JHtml::_('jquery.framework');
}
if($uiversion = $donorcart_params->get('jqueryui_version')) {
	JFactory::getDocument()->addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/'.$uiversion.'/jquery-ui.min.js');
	if($uitheme = $donorcart_params->get('jqueryui_theme')) {
		JFactory::getDocument()->addStyleSheet('http://ajax.googleapis.com/ajax/libs/jqueryui/'.$uiversion.'/themes/'.$uitheme.'/jquery-ui.min.css');
	}
}
$juri = JUri::getInstance();
$custom_js = 'var sp_website_root="'.JUri::root().'",sp_checkout_page="'.JRoute::_('index.php?option=com_donorcart',false,$donorcart_params->get('ssl_mode')==2?1:null).'";';

$document = JFactory::getDocument();
$document->addScriptDeclaration($custom_js);
$document->addScript(JUri::root(true).'/media/com_donorcart/donorcart.js');
$document->addStyleSheet(JUri::root(true).'/media/com_donorcart/donorcart.css');