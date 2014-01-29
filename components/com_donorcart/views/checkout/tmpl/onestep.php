<?php defined('_JEXEC') or die('Restricted Access');

$this->include_form = true;
$this->include_submit_button = true;

if($this->params->get('ignore_form_for_payment')) $this->include_form = false;

$this->includeLayout('head','default');
$this->includeLayout('returntologin','default');
$this->includeLayout('addresses','default');
$this->includeLayout('payment','default');

?><input type="hidden" name="task" value="onestep" /><?
$this->includeLayout('tail','default');
