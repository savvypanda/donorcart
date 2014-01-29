<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartControllerCheckout extends FOFController {
	//private $order;
	private $params;
	private $setdisplay = false;

	public function __construct($config = array()) {
		$config['modelName']='DonorcartModelOrders';

		parent::__construct($config);
		$this->registerTask('login','_login');
		$this->registerTask('logout','_logout');
		$this->registerTask('register','_register');
//		$this->registerTask('guestcheckout','_guest');

		$this->registerTask('remove','_remove_item');
		$this->registerTask('emptyCart','_empty_cart');

		//$this->registerTask('onestep','_onestep_checkout');
		//$this->registerTask('setaddresses','_set_addresses');
		//$this->registerTask('payment','_set_payment');

		$this->registerTask('submit','_submit');
		$this->registerTask('confirm','_confirm');
		$this->registerTask('revise','_revise');
		$this->registerTask('postback','_postback');
		$this->registerTask('thankyou','_thankyou');

		$this->getThisModel();
		//$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
		//$order_id = $ordermodel->getId();
		if(!$this->_modelObject->getId()) {
			$this->_modelObject->createOrder();
		}
		$this->_modelObject->getItem();
		//$this->order = $ordermodel->getItem($order_id);
		//$ordermodel->calcOrderTotal($this->order);

		//allow the plugins to override the component parameters
		$this->params = JComponentHelper::getParams('com_donorcart');
		//JPluginHelper::importPlugin('donorcart');
		//$dispatcher = JDispatcher::getInstance();
		//$results = $dispatcher->trigger('onGetDonorcartPreferenceOverrides', array($this->order, &$params));

		//Now check the parameters for the SSL Mode, redirecting if necessary
		if($this->params->get('ssl_mode',0) == 2) {
			$juri = JUri::getInstance();
			if(!$juri->isSSL()) {
				$juri->setScheme('https');
				JFactory::getApplication()->redirect($juri->toString());
			}
		}
	}

	public function display($cachable = false, $urlparams = false) {
		$nextstep = $this->getNextStep();
		//$onestep = $this->params->get('onestep_checkout',0);
		switch($nextstep) {
			case 'emptycart':
				$layout = 'emptycart';
				break;
			//case 'shipto':
			//case 'billto':
			//	$layout = $onestep?'onestep':'addresses';
			//	break;
			//case 'payment':
			//	$layout = $onestep?'onestep':'payment';
			//	break;
			case 'review':
			case 'submit':
			case 'confirm':
				$layout = 'review';
				break;
			case 'thankyou':
				return $this->_thankyou();
			//$layout = 'thankyou';
			//break;
			case 'login':
			default:
				$layout = 'default';
				break;
		}

		$this->layout = $layout;
		$view = $this->getThisView();
		$view->assign('params',$this->params);
		parent::display($cachable, $urlparams);
	}

	private function getNextStep() {
		if(!is_object($this->order) || !$this->order->cart_id || !is_object($this->order->cart)) return 'emptycart';

		$task = $this->getTask();

		if(!$this->order->payment_id || $task == 'revise') return 'default';

/*		if(!$session->get('guestcheckout',false) && !$this->order->user_id) {
			if($this->params->get('login_option') || $this->params->get('require_email_for_guest_checkout',false)) {
				return 'default';
			}
		}

		$shipoption = $this->params->get('shipto_option',0);
		if(($shipoption == 1 || $shipoption == 2) && !$this->order->shipping_address_id) {
			return 'shipto';
		}

		$billoption = $this->params->get('billto_option',0);
		if(($billoption == 1 || $billoption == 2) && !$this->order->billing_address_id) {
			return 'billto';
		}

		if(!$this->order->payment_id) {
			return 'payment';
		}
*/
		if($this->order->status == 'complete') return 'thankyou';

		if($this->getTask() == 'review' || ($this->getTask() == 'submit' && $this->params->get('review_option',0))) return 'review';

		//$reviewoption = $this->params->get('review_option',0);
		//if(($reviewoption == 1 || $reviewoption == 2) && $session->get('reviewed_order') != $this->order->donorcart_order_id && $session->get('submitted_order') != $this->order->donorcart_order_id) {
		//	return 'review';
		//}

		/* if($session->get('submitted_order') != $this->order->donorcart_order_id) {
			return 'submit';
		} */

		//return 'thankyou';
		return 'default';
	}

	public function execute($task) {
		if(in_array($task,array('add','edit','read','save'))) {
			$this->task = $task = 'read';
		}

		//JPluginHelper::importPlugin('donorcart');
		//$dispatcher = JDispatcher::getInstance();
		//$results = $dispatcher->trigger('onBeforeCheckoutLoad', array(&$this->order, $task));

		$taskresult = ($task=='read')?$this->display():parent::execute($task);

		//JPluginHelper::importPlugin('donorcart');
		//$dispatcher = JDispatcher::getInstance();
		//$results = $dispatcher->trigger('onAfterCheckoutLoad',array(&$this->order, $task));

		if($this->setdisplay && $taskresult !== false) {
			$this->task = 'read';
			return $this->display();
		} elseif(is_string($taskresult)) {
			echo $taskresult;
			return true;
		} else {
			return $taskresult;
		}
	}

	/* protected function onBeforeRead() {
		return $this->checkACL('cart.checkout');
	}

	protected function onBeforeRemove() {
		return $this->checkACL('cart.view');
	} */

	/* This function is based on the J2.5 Users component code. Alter at your risk */
	// TODO: Replace login, logout, and register functionality in component with user plugins to handle the user data on pending orders
	public function _login() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();
		$session->set('guestcheckout',null);

		if($return = JRequest::getString('return', '')) {
			$return = base64_decode($return);
			if(!JURI::isInternal($return)) {
				$return = '';
			}
		}

		$options = array();
		$options['remember'] = JRequest::getBool('remember', false);
		$options['return'] = $return;
		$credentials = array();
		$credentials['username'] =JRequest::getString('username');
		$credentials['password'] = JRequest::getString('passwd');

		//preform the login action
		if($mainframe->login($credentials, $options) === true) {
			$user = JFactory::getUser();
			$this->_modelObject->updateUserData(null, $user->id, $user->email, 'checkout');
			//$this->order->user_id = $user->id;
			//$this->order->email = $user->email;
		}

		$this->setdisplay = true;
	}

	public function _logout() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		//if(is_object($this->_modelObject->record) && !empty($this->_modelObject->record->donorcart_order_id)) {
			if(FOFModel::getAnInstance('orders','DonorcartModel')->removeUserData(null, false, 'cart')) {
				$session = JFactory::getSession();
				$cartid = $session->get('cart_id',null);
				$orderid = $session->set('order_id',null);
				JFactory::getApplication()->logout();
				$session->restart();
				$session->set('cart_id',$cartid);
				$session->set('order_id',$orderid);
			} else {
				return false;
			}
		//}
		$this->setdisplay = true;
		//return true;
	}

	public function _register() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$mainframe = JFactory::getApplication();

		$session = JFactory::getSession();
		//$session->set('guestcheckout',null);

		if(version_compare(JVERSION, '3.0.0', 'ge')) {
			//This code is adapted from the Joomla 3.0.x User component

			if(!class_exists('UsersModelRegistration')) include(JPATH_BASE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_users'.DIRECTORY_SEPARATOR.'models'.DIRECTORY_SEPARATOR.'registration.php');
			$registrationmodel = new UsersModelRegistration();
			$data = array(
				'name'      => JRequest::getString('name'),
				'username'  => JRequest::getString('username', ''),
				'password1' => JRequest::getString('password', ''),
				'password2' => JRequest::getString('password2', ''),
				'email1'    => JRequest::getString('email', ''),
				'email2'    => JRequest::getString('email', '')
			);
			$newuserid = $registrationmodel->register($data);
			if(!is_int($newuserid)) {
				switch($newuserid) {
					case 'useractivate':
						//JError::raiseNotice(309,'Account requires activation. Please check your email for an activation code and follow the instructions there, then return to continue the checkout process.');
						$mainframe->enqueueMessage('Account requires activation. Please check your email for an activation code and follow the instructions there, then return to continue the checkout process.', 'notice');
						break;
					case 'adminactivate':
						//JError::raiseNotice(309,'Account requires activation. Please wait for an administrator to activate your account before returning to continue the checkout process.');
						$mainframe->enqueueMessage('Account requires activation. Please wait for an administrator to activate your account before returning to continue the checkout process.', 'notice');
						break;
					case '':
						break;
					case false:
						if($error = $registrationmodel->getError()) {
							//JError::raiseError(499,$error);
							$mainframe->enqueueMessage($error, 'error');
							break;
						}
					default:
						//JError::raiseError(500,'Error: Failed to create new user. Please contact the webmaster for assistance.');
						$mainframe->enqueueMessage('Error: Failed to create new user. Please contact the webmaster for assistance.', 'error');
						break;
				}
			} else {
				$usersipass = array();
				$usersipass['username'] = $data['username'];
				$usersipass['password'] = $data['password1'];
				if($mainframe->login($usersipass)) {
					$user = JFactory::getUser();
					$this->_modelObject->updateUserData(null, $user->id, $user->email, 'checkout');
					//FOFModel::getTmpInstance('orders','DonorcartModel')->updateUserData($this->order->donorcart_order_id, $user->id, $user->email, 'checkout');
					//$this->order->user_id = $user->id;
					//$this->order->email = $user->email;
				}
			}
		} elseif(version_compare(JVERSION, '1.6.0', 'ge')) {
			/* This code is based on the J2.5 Users component code. Alter at your risk */
			require_once JPATH_ROOT.'/components/com_users/controller.php';
			require_once JPATH_ROOT.'/components/com_users/models/registration.php';
			$post = array(
				'name'      => JRequest::getString('name'),
				'username'  => JRequest::getString('username', ''),
				'password1' => JRequest::getString('password', ''),
				'password2' => JRequest::getString('password2', ''),
				'email1'    => JRequest::getString('email', ''),
				'email2'    => JRequest::getString('email', ''),
			);
			JFactory::getLanguage()->load('com_users');
			$controller = new UsersController();
			$model = $controller->getModel('Registration');
			if($model->register($post)) {
				$usersipass = array();
				$usersipass['username'] = $post['username'];
				$usersipass['password'] = $post['password1'];
				if($mainframe->login($usersipass) === true) {
					$user = JFactory::getUser();
					$this->_modelObject->updateUserData(null, $user->id, $user->email, 'checkout');
					//FOFModel::getTmpInstance('orders','DonorcartModel')->updateUserData($this->order->donorcart_order_id, $user->id, $user->email, 'checkout');
					//$this->order->user_id = $user->id;
					//$this->order->email = $user->email;
				}
			} else {
				$errorMessage = $model->getError();
				JRequest::setVar('error', $errorMessage);
			}
		} else {
			//adapted from com_user: register_save
			require JPATH_ROOT.'/components/com_user/controller.php';

			// Get required system objects
			$user = clone(JFactory::getUser());
			$authorize =& JFactory::getACL();

			// If user registration is not allowed, show 403 not authorized.
			$usersConfig = &JComponentHelper::getParams('com_users');
			if($usersConfig->get('allowUserRegistration') == '0') {
				JError::raiseError(403, JText::_('Access Forbidden'));

				$this->setdisplay = true;
				return;
			}

			// Initialize new usertype setting
			$newUsertype = $usersConfig->get('new_usertype');
			if(!$newUsertype) {
				$newUsertype = 'Registered';
			}

			// Bind the post array to the user object
			if(!$user->bind(JRequest::get('post'), 'usertype')) {
				JError::raiseError(500, $user->getError());
				$this->setdisplay = true;
				return;
			}

			// Set some initial user values
			$user->set('id', 0);
			$user->set('usertype', '');
			$user->set('gid', $authorize->get_group_id('', $newUsertype, 'ARO'));
			$date =& JFactory::getDate();
			$user->set('registerDate', $date->toMySQL());

			// If user activation is turned on, we need to set the activation information
			$useractivation = $usersConfig->get('useractivation');
			if($useractivation == '1') {
				jimport('joomla.user.helper');
				$user->set('activation', JUtility::getHash(JUserHelper::genRandomPassword()));
				$user->set('block', '1');
			}

			// If there was an error with registration, set the message and display form
			if(!$user->save()) {
				JError::raiseWarning('', JText::_($user->getError()));
				$this->setdisplay = true;
				return;
			}

			// Send registration confirmation mail
			//$password = JRequest::getString('password', '', 'post', JREQUEST_ALLOWRAW);
			$password = JRequest::getString('password');
			$password = preg_replace('/[\x00-\x1F\x7F]/', '', $password); //Disallow control chars in the email
			UserController::_sendMail($user, $password);

			// Everything went fine, set relevant message depending upon user activation state and display message
			if($useractivation == 1) {
				$message = JText::_('REG_COMPLETE_ACTIVATE');
			} else {
				$message = JText::_('REG_COMPLETE');
			}

			$usersipass = array();
			$usersipass['username'] = $user->get('username');
			$usersipass['password'] = $password;
			if($mainframe->login($usersipass) === true) {
				$this->_modelObject->updateUserData(null, $user->id, $user->email, 'checkout');
				//FOFModel::getTmpInstance('orders','DonorcartModel')->updateUserData($this->order->donorcart_order_id, $user->id, $user->email, 'checkout');
				//$this->order->user_id = $user->id;
				//$this->order->email = $user->email;
			}
		}
		$this->setdisplay = true;
	}

	/* public function _guest() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$email = JRequest::getString('email',null);
		if($email) {
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if(!$email) {
				return false;
			}
		}
		if($this->params->get('require_email_for_guest_checkout') && !$email) {
			return false;
		}

		$session = JFactory::getSession();
		$session->set('guestcheckout',1);

		FOFModel::getTmpInstance('orders','DonorcartModel')->updateUserData($this->order->donorcart_order_id, null, $email, 'checkout');
		if($email) $this->order->email = $email;

		$this->setdisplay = true;
	} */

	public function _remove_item() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$id = JRequest::getInt('item',null);
		FOFModel::getTmpInstance('cart_items','DonorcartModel')->setId($id)->delete();
		//FOFModel::getTmpInstance('orders','DonorcartModel')->calcOrderTotal($this->order);
		$this->order = FOFModel::getTmpInstance('orders','DonorcartModel')->getItem($this->order->donorcart_order_id);
		$this->setdisplay = true;
	}

	public function _empty_cart() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$order_id = $this->_modelObject->record->donorcart_order_id;
		$cart_id = $this->_modelObject->record->cart_id;
		$session = JFactory::getSession();
		if(!$order_id || $this->_modelObject->delete()) {
			$session->set('order_id',null);
			$this->_modelObject->record = null;
		}
		if(!$cart_id || FOFModel::getTmpInstance('carts','DonorcartModel')->setId($cart_id)->delete()) {
			$session->set('cart_id',null);
		}
		$this->setdisplay = true;
	}

	/* public function _onestep_checkout() {
		$this->_set_addresses();
		$this->_set_payment();
	} */

	/* public function _set_addresses() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		//first we need to include some logic to determine:
		//A) If we are updating an existing billing and/or shipping address
		//B) If we are saving a new billing and/or shipping address
		//C) If we are referencing an existing address
		$addressmodel = FOFModel::getTmpInstance('addresses','DonorcartModel');
		$shipto_id = JRequest::getInt('shipto_id',null);
		$billto_id = JRequest::getInt('billto_id',null);
		$update_shipping = !is_null($shipto_id);
		$update_billing = !is_null($billto_id);
		$set_billto_to_ship_addr = (bool) JRequest::getInt('use_same_address_for_billto',0);
		if($set_billto_to_ship_addr) {
			$billto_id = $shipto_id;
			$update_billing = false;
		}
		if($shipto_id) {
			$shipto_address = $addressmodel->getItem($shipto_id);
			if($shipto_address->locked) {
				$update_shipping = false;
			}
		}
		if($billto_id && $update_billing) {
			$billto_address = $addressmodel->getItem($billto_id);
			if($billto_address->locked) {
				$update_billing = false;
			}
		}
		$newship = !is_null($shipto_id) && (!$shipto_id || !$this->order->shipping_address_id || $shipto_id != $this->order->shipping_address_id);
		$newbill = !is_null($billto_id) && (!$billto_id || !$this->order->billing_address_id || $billto_id != $this->order->billing_address_id);

		//sanity check
		if($update_shipping && $shipto_id && $this->order->shipping_address_id && $shipto_id != $this->order->shipping_address_id) {
			return false;
		}
		if($update_billing && $billto_id && $this->order->billing_address_id && $billto_id != $this->order->billing_address_id) {
			return false;
		}

		$user_id = $this->order->user_id;

		if($update_shipping) {
			$shipdata = array(
				'user_id' => $user_id,
				'address_type' => JRequest::getString('ship_address_type',null),
				'first_name' => JRequest::getString('ship_first_name',null),
				'middle_name' => JRequest::getString('ship_middle_name',null),
				'last_name' => JRequest::getString('ship_last_name',null),
				'business_name' => JRequest::getString('ship_business_name',null),
				'address1' => JRequest::getString('ship_address1',null),
				'address2' => JRequest::getString('ship_address2',null),
				'city' => JRequest::getString('ship_city',null),
				'state' => JRequest::getString('ship_state',null),
				'zip' => JRequest::getString('ship_zip',null),
				'country' => JRequest::getString('ship_country',null)
			);
			if($shipto_id) $shipdata['donorcart_address_id'] = $shipto_id;

			$shipping_is_valid = true;
			//plugin event handler for shipto event
			//JPluginHelper::importPlugin('donorcart');
			//$dispatcher = JDispatcher::getInstance();
			//$results = $dispatcher->trigger('onBeforeShipto', array(&$shipdata, &$this->order));
			//foreach($results as $result) {
			//	if($result === false) {
			//		$shipping_is_valid = false;
			//	}
			//}

			if($shipping_is_valid) {
				$result = $addressmodel->save($shipdata);
				if(!$result) {
					//JError::raiseError(500,'Invalid address: '.$addressmodel->getError());
					JFactory::getApplication()->enqueueMessage($addressmodel->getError(), 'error');
					$shipping_is_valid = false;
				}
				$shipto_id = $addressmodel->getId();
				$this->order->shipping_address = $addressmodel->getSavedTable();
			}
		} elseif(!$newship) {
			$shipto_id = $this->order->shipping_address_id;
		}

		if($update_billing) {
			$billdata = array(
				'user_id' => $user_id,
				'address_type' => JRequest::getString('bill_address_type',null),
				'first_name' => JRequest::getString('bill_first_name',null),
				'middle_name' => JRequest::getString('bill_middle_name',null),
				'last_name' => JRequest::getString('bill_last_name',null),
				'business_name' => JRequest::getString('bill_business_name',null),
				'address1' => JRequest::getString('bill_address1',null),
				'address2' => JRequest::getString('bill_address2',null),
				'city' => JRequest::getString('bill_city',null),
				'state' => JRequest::getString('bill_state',null),
				'zip' => JRequest::getString('bill_zip',null),
				'country' => JRequest::getString('bill_country',null)
			);
			if($billto_id) $billdata['donorcart_address_id'] = $billto_id;

			$billing_is_valid = true;
			//plugin event handler for billto event
			//JPluginHelper::importPlugin('donorcart');
			//$dispatcher = JDispatcher::getInstance();
			//$results = $dispatcher->trigger('onBeforeBillto', array(&$billdata, &$this->order));
			//foreach($results as $result) {
			//	if($result === false) {
			//		$billing_is_valid = false;
			//	}
			//}

			//if($billing_is_valid) {
				$result = $addressmodel->save($billdata);
				if(!$result) {
					//JError::raiseError(500,'Invalid address: '.$addressmodel->getError());
					JFactory::getApplication()->enqueueMessage($addressmodel->getError(), 'error');
					$billing_is_valid = false;
				}
				$billto_id = $addressmodel->getId();
				$this->order->billing_address = $addressmodel->getSavedTable();
			//}
		} elseif(!$newbill) {
			$billto_id = $this->order->billing_address_id;
		}

		if($newship || $newbill) {
			//attach the addresses to the order
			$orderdata = array(
				'donorcart_order_id' => $this->order->donorcart_order_id,
				'shipping_address_id' => $shipto_id,
				'billing_address_id' => $billto_id,
			);
			FOFModel::getTmpInstance('orders','DonorcartModel')->save($orderdata);
			$this->order->shipping_address_id = $shipto_id;
			$this->order->billing_address_id = $billto_id;
		}

		/* if($update_shipping) {
			JPluginHelper::importPlugin('donorcart');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterShipto', array(&$this->order, $shipping_is_valid));
		}
		if($update_billing) {
			JPluginHelper::importPlugin('donorcart');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterBillto', array(&$this->order, $billing_is_valid));
		} */
/*
		$this->setdisplay = true;
	} */

	/* public function _set_payment() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		$is_valid = false;
		$plugin_validated = '';
		$payment_id = 0;

		//payment is handled entirely through plugin events.
		//including multiple plugin events for this purpose
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onBeforePayment', array(&$this->order, &$plugin_validated));
		foreach($results as $result) {
			if($result === true) {
				$is_valid = true;
			}
		}
		$results = $dispatcher->trigger('onPayment', array(&$this->order, &$is_valid, &$plugin_validated));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			} elseif(is_numeric($result)) {
				$payment_id = $result;
			}
		}
		$results = $dispatcher->trigger('onAfterPayment', array(&$this->order, &$is_valid, &$plugin_validated));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			}
		}

		if($payment_id) {
			$ordermodel = FOFModel::getTmpInstance('orders','DonorcartModel');
			$orderdata = array(
				'donorcart_order_id' => $this->order->donorcart_order_id,
				'payment_id' => $payment_id,
			);
			if($is_valid) {
				$orderdata['status'] = 'complete';
				if (is_callable(array('JApplication', 'getHash'))) {
					$this->order->viewtoken = $orderdata['viewtoken'] = JApplication::getHash($this->order->donorcart_order_id.JSession::getFormToken());
				} else {
					$this->order->viewtoken = $orderdata['viewtoken'] = md5(JFactory::getApplication()->get('secret').$this->order->donorcart_order_id.JSession::getFormToken());
				}
			}
			$ordermodel->save($orderdata);
			$this->order->payment_id = $payment_id;
			$paymentmodel = FOFModel::getTmpInstance('payments','DonorcartModel');
			$this->order->payment = $paymentmodel->getItem($payment_id);
			$this->_lock_addresses();
		}

		$this->setdisplay = true;
	} */

	public function _review() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		/* JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onBeforeReview', array(&$this->order));

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onAfterReview', array(&$this->order));
		*/

		JFactory::getSession()->set('reviewed_order',$this->order->donorcart_order_id);

		$this->setdisplay = true;
	}

	public function _submit() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		$is_valid = true;

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onBeforeSubmit', array(&$this->order));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			}
		}
		$results = $dispatcher->trigger('onSubmit', array(&$this->order, $is_valid));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			}
		}
		if($is_valid) {
			//mark the order as submitted
			$orderdata = array(
				'donorcart_order_id' => $this->order->donorcart_order_id,
				'submitted' => 1
			);
			FOFModel::getTmpInstance('orders','DonorcartModel')->save($orderdata);
			$this->order->submitted = 1;
			//and also mark the addresses as locked
			$this->_lock_addresses();
		}
		$results = $dispatcher->trigger('onAfterSubmit', array(&$this->order, $is_valid));

		if($is_valid) {
			JFactory::getSession()->set('submitted_order',$this->order->donorcart_order_id);
		}

		$this->setdisplay = true;
	}

	protected function _lock_addresses() {
		if($this->order->shipping_address_id && !$this->order->shipping_address->locked) {
			$shipdata = array(
				'donorcart_address_id' => $this->order->shipping_address_id,
				'locked' => 1
			);
			FOFModel::getTmpInstance('addresses','DonorcartModel')->save($shipdata);
		}
		if($this->order->billing_address_id && $this->order->billing_address_id != $this->order->shipping_address_id && !$this->order->billing_address->locked) {
			$billdata = array(
				'donorcart_address_id' => $this->order->billing_address_id,
				'locked' => 1
			);
			FOFModel::getTmpInstance('addresses','DonorcartModel')->save($billdata);
		}
	}

	public function _postback() {
		//this request would not originate from the Joomla website.
		//We cannot validate it with the regular token method.
		//It is up to the plugins to validate the request origin.

		//default to invalid. It is only valid if one of the plugins says it is valid.
		//the request validation should occur in the onBeforePostback event.
		$is_valid = false;
		$plugin_validated = '';
		$returnval = '';

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onBeforePostback', array(&$this->order, &$plugin_validated));
		foreach($results as $result) {
			if($result === true) {
				$is_valid = true;
			} elseif(is_string($result)) {
				$returnval .= $result;
			}
		}
		$results = $dispatcher->trigger('onPostback', array(&$this->order, &$is_valid, &$plugin_validated));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			} elseif(is_string($result)) {
				$returnval .= $result;
			}
		}
		$results = $dispatcher->trigger('onAfterPostback', array(&$this->order, $is_valid, &$plugin_validated));
		foreach($results as $result) {
			if(is_string($result)) {
				$returnval .= $result;
			}
		}

		return $returnval;
	}

	public function _thankyou() {
		//JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		/*
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onBeforeThankyou', array(&$this->order));
		$results = $dispatcher->trigger('onThankyou', array(&$this->order));
		$results = $dispatcher->trigger('onAfterThankyou', array(&$this->order));
		*/

		$this->layout = 'thankyou';
		$returnval = parent::display();

		$session = JFactory::getSession();
		$session->set('cart_id',null);
		$session->set('order_id',null);
		$session->set('guestcheckout',null);
		$session->set('reviewed_order',null);
		$session->set('submitted_order',null);

		return $returnval;
	}
}
