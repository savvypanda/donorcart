<?php defined("_JEXEC") or die("Restricted Access");

JText::script('COM_DONORCART_JS_ADD_TO_CART_LOADING',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_FAILURE_TITLE',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_FAILURE',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_SUCCESS_TITLE',true);
JText::script('COM_DONORCART_JS_ADD_TO_CART_SUCCESS',true);
JText::script('COM_DONORCART_JS_PROCEED_TO_CHECKOUT',true);
JText::script('COM_DONORCART_JS_CONTINUE_SHOPPING',true);

$params = JComponentHelper::getParams('com_donorcart');
if($params->get('load_jquery')) {
	JHtml::_('jquery.framework');
}
if($uiversion = $params->get('jqueryui_version')) {
	JFactory::getDocument()->addScript('http://ajax.googleapis.com/ajax/libs/jqueryui/'.$uiversion.'/jquery-ui.min.js');
	if($uitheme = $params->get('jqueryui_theme')) {
		JFactory::getDocument()->addStyleSheet('http://ajax.googleapis.com/ajax/libs/jqueryui/'.$uiversion.'/themes/'.$uitheme.'/jquery-ui.min.css');
	}
}

FOFTemplateUtils::addJS('media://com_donorcart/donorcart.js');
FOFTemplateUtils::addCSS('media://com_donorcart/donorcart.css');
?>

<div id="dcart_target">
	<!--img src="media/com_donorcart/images/ajax_loading.gif" alt="Loading.." height="16" width="16" align="left" border="0" />&nbsp;&nbsp;Cart Loading...-->
	<?php include(dirname(__FILE__).DIRECTORY_SEPARATOR.'default.php'); ?>
</div>
