<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartControllerCheckout extends FOFController {
	private $params;

	public function __construct($config = array()) {
		$config['modelName']='DonorcartModelOrders';

		parent::__construct($config);
		$this->registerTask('login','_login');
		$this->registerTask('logout','_logout');
		$this->registerTask('register','_register');

		$this->registerTask('remove','_remove_item');
		$this->registerTask('emptyCart','_empty_cart');

		$this->registerTask('submit','_submit');
		$this->registerTask('confirm','_confirm');
		$this->registerTask('postback','_postback');

		$ordermodel = $this->getThisModel();
		//$ordermodel = FOFModel::getAnInstance('orders','DonorcartModel');
		//$order_id = $ordermodel->getId();
		if(!$ordermodel->getId()) {
			$ordermodel->createOrder();
		}
		//$ordermodel->getItem();

		$this->params = JComponentHelper::getParams('com_donorcart');

		//Now check the parameters for the SSL Mode, redirecting if necessary
		if($this->params->get('ssl_mode',0) == 2) {
			$juri = JUri::getInstance();
			if(!$juri->isSSL()) {
				$juri->setScheme('https');
				JFactory::getApplication()->redirect($juri->toString());
			}
		}
	}

	public function execute($task) {
		if(in_array($task,array('add','edit','read','save'))) {
			$this->task = $task = 'read';
		}

		if($task != 'read') {
			$taskresult = parent::execute($task);
			if(is_string($taskresult)) {
				echo $taskresult;
				return true;
			} elseif($taskresult !== true) {
				return false;
			}
		}
		$ordermodel = $this->getThisModel();
		$order_id = $ordermodel->getId();
		$order = $ordermodel->reset()->setId($order_id)->getItem();

		if(!$order->cart_id || !is_object($order->cart) || empty($order->cart->items)) {
			$this->layout = 'emptycart';
		} elseif($order->status == 'complete') {
			$this->_clearSession();
			$this->_sendConfirmationEmails();
			$this->layout = 'thankyou';
		} elseif($this->layout != 'review') {
			$this->layout = 'default';
		}

		$view = $this->getThisView();
		$view->assign('params',$this->params);
		$this->display();
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
			$this->getThisModel()->updateUserData(null, $user->id, $user->email, 'checkout');
			//$this->order->user_id = $user->id;
			//$this->order->email = $user->email;
		}

		return true;
	}

	public function _logout() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		//if(is_object($this->_modelObject->record) && !empty($this->_modelObject->record->donorcart_order_id)) {
			//if(FOFModel::getAnInstance('orders','DonorcartModel')->removeUserData(null, false, 'cart')) {
			if($this->getThisModel()->removeUserData(null, false, 'cart')) {
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
		return true;
	}

	public function _register() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$mainframe = JFactory::getApplication();

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
					$this->getThisModel()->updateUserData(null, $user->id, $user->email, 'checkout');
					//FOFModel::getTmpInstance('orders','DonorcartModel')->updateUserData($this->order->donorcart_order_id, $user->id, $user->email, 'checkout');
					//$this->order->user_id = $user->id;
					//$this->order->email = $user->email;
				}
			}
		} else {
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
					$this->getThisModel()->updateUserData(null, $user->id, $user->email, 'checkout');
					//FOFModel::getTmpInstance('orders','DonorcartModel')->updateUserData($this->order->donorcart_order_id, $user->id, $user->email, 'checkout');
					//$this->order->user_id = $user->id;
					//$this->order->email = $user->email;
				}
			} else {
				$errorMessage = $model->getError();
				JRequest::setVar('error', $errorMessage);
			}
		}
		return true;
	}

	public function _remove_item() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$id = JRequest::getInt('item',null);
		FOFModel::getAnInstance('carts','DonorcartModel')->removeItemFromCart($id);
		//FOFModel::getTmpInstance('orders','DonorcartModel')->calcOrderTotal($this->order);
		//$this->order = FOFModel::getTmpInstance('orders','DonorcartModel')->getItem($this->order->donorcart_order_id);
		return true;
	}

	public function _empty_cart() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		return $this->getThisModel()->delete();
	}

	public function _submit() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$is_valid = true;

		$ordermodel =& $this->getThisModel();
		$order = $ordermodel->getItem();
		$order_id = $order->donorcart_order_id;

		//track the order information (for changing address/payment details, etc...)
		$orderdata = array(
			'donorcart_order_id' => $order_id,
			'status' => 'checkout'
		);

		//Step 1: Save the email address (if applicable)
		if($email = JRequest::getString('email',null)) {
			$orderdata['email'] = $email;
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if(!$email) {
				$is_valid = false;
			}
		}
		if($this->params->get('require_email_for_guest_checkout') && !$email) {
			$is_valid = false;
		}

		// ************************************
		//Step 1: save the addresses (if present)
		//first we need to include some logic to determine:
		//A) If we are saving new addresses
		//B) If we are updating existing addresses
		//C) If we are referencing an existing address
		$addressmodel = FOFModel::getTmpInstance('addresses','DonorcartModel');
		$shipto_id = JRequest::getInt('shipto_id',null);
		$billto_id = JRequest::getInt('billto_id',null);
		$update_shipping = $shipto_id?true:false;
		$update_billing = $billto_id?true:false;
		$set_billto_to_ship_addr = (bool) JRequest::getInt('use_same_address_for_billto',0);
		if($set_billto_to_ship_addr) {
			$billto_id = $shipto_id;
			$update_billing = false;
		}
		if($update_shipping && $addressmodel->getItem($shipto_id)->locked) {
			$update_shipping = false;
		}
		if($update_billing && $addressmodel->getItem($billto_id)->locked) {
			$update_billing = false;
		}

		if(is_null($shipto_id) && $this->params->get('shipto_option',0) == 2) {
			//if the shipping address is required but not present, the form is invalid
			$is_valid = false;
		}
		if(is_null($billto_id) && $this->params->get('billto_option',0) == 2) {
			//if the billing address is required but not present, the form is invalid
			$is_valid = false;
		}

		//Now that we know what we are doing. Let's do it!
		//saving the shipping address
		if($shipto_id==0 || $update_shipping) { //new or updated address
			$shipdata = $this->_prepareAddress('shipping','ship_',$is_valid);
			if($shipto_id) $shipdata['donorcart_address_id'] = $shipto_id;

			$result = $addressmodel->save($shipdata);
			if(!$result) {
				$is_valid = false;
				JFactory::getApplication()->enqueueMessage($addressmodel->getError(), 'error');
			}
			$shipto_id = $addressmodel->getId();
		}
		$orderdata['shipping_address_id']=$shipto_id;

		//saving the billing address
		if($billto_id==0 || $update_billing) { //new or updated address
			$billdata = $this->_prepareAddress('billing','bill_',$is_valid);
			if($billto_id) $billdata['donorcart_address_id'] = $billto_id;

			$result = $addressmodel->save($billdata);
			if(!$result) {
				$is_valid = false;
				JFactory::getApplication()->enqueueMessage($addressmodel->getError(), 'error');
			}
			$billto_id = $addressmodel->getId();
		}
		$orderdata['billing_address_id']=$billto_id;


		//Step 2: save the payment details
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$payment_name = JRequest::getVar('payment_method','');
		$payment_details = false;
		$results = $dispatcher->trigger('onSubmitOrder', array($order, $this->params, &$payment_name));
		foreach($results as $result) {
			if($result === false || is_array($result)) {
				$payment_details=$result;
			}
		}
		if($payment_details) {
			$payment_model = FOFModel::getTmpInstance('payments','DonorcartModel');

			if($order->payment_id) {
				$payment_details['donorcart_payment_id'] = $order->payment_id;
			}
			$result = $payment_model->save($payment_details);
			if(!$result) {
				$is_valid = false;
				JFactory::getApplication()->enqueueMessage($payment_model->getError(), 'error');
			}
			$orderdata['payment_id'] = $payment_model->getId();
		} else {
			$is_valid = false;
		}

		//Step 3: Save the order.
		$ordermodel->save($orderdata);

		if($is_valid) {
			if($this->params->get('review_option')==0) {
				//Step 4: Submit the order (ONLY IF VALID AND THE COMPONENT PARAMETERS SPECIFY NO REVIEW STEP)
				$htmloutput = '';
				$order = $ordermodel->reset()->setId($order_id)->getItem(); //refresh the order in case it was modified since the beginning of the submit function
				$orderdata = array(
					'donorcart_order_id' => $order_id,
					'status' => 'submitted'
				);
				JPluginHelper::importPlugin('donorcart');
				$dispatcher = JDispatcher::getInstance();
				$results = $dispatcher->trigger('onConfirmOrder', array($order, $this->params, $is_valid));
				foreach($results as $result) {
					if($result === true) {
						$orderdata['status'] = 'complete';
						$this->_lock_addresses();
					} elseif(is_string($result)) {
						$htmloutput .= $result;
					}
				}
				$ordermodel->save($orderdata);

				if(!empty($htmloutput)) {
					return $htmloutput;
				}
			} else {
				//Step 4 Alternate: set the layout to 'review' (ONLY IF THE ORDER IS VALID)
				$this->layout = 'review';
			}
		}

		return true;
	}

	public function _confirm() {
		$is_valid = true;
		$ordermodel =& $this->getThisModel();
		$order = $ordermodel->getItem();
		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();

		//Step 1: Validate the email address (if applicable)
		$email = $order->email;
		if($email) {
			$email = filter_var($email, FILTER_VALIDATE_EMAIL);
			if(!$email) {
				$is_valid = false;
			}
		}
		if($this->params->get('require_email_for_guest_checkout') && !$email) {
			$is_valid = false;
		}

		//Step 2: Validate the billing and shipping addresses (if applicable)
		if($order->shipping_address_id) {
			$results = $dispatcher->trigger('onValidateAddress', array(((array)$order->shipping_address), 'shipping'));
			foreach($results as $result) {
				if($result === false) {
					$is_valid = false;
				}
			}
		} elseif($this->params->get('shipto_option',0) == 2) {
			$is_valid = false;
		}
		if($order->billing_address_id) {
			$results = $dispatcher->trigger('onValidateAddress', array(((array)$order->billing_address), 'billing'));
			foreach($results as $result) {
				if($result === false) {
					$is_valid = false;
				}
			}
		} elseif ($this->params->get('billto_option',0) == 2) {
			$is_valid = false;
		}


		//Step 3: confirm the payment - ONLY IF VALID
		if($is_valid) {
			$htmloutput = '';
			$orderdata = array(
				'donorcart_order_id' => $order->donorcart_order_id,
				'status' => 'submitted'
			);
			$results = $dispatcher->trigger('onConfirmOrder', array($order, $this->params, $is_valid));
			foreach($results as $result) {
				if($result === true) {
					$orderdata['status'] = 'complete';
					$this->_lock_addresses();
				} elseif(is_string($result)) {
					$htmloutput .= $result;
				}
			}
			$ordermodel->save($orderdata);

			if(!empty($htmloutput)) {
				return $htmloutput;
			}
		}

		return true;
	}

	protected function _prepareAddress($type, $prefix, &$is_valid) {
		if(!$is_valid) return false;
		$ordermodel = $this->getThisModel();
		$order = $ordermodel->getItem();

		$data = array(
			'address_type' => JRequest::getString($prefix.'address_type',null),
			'first_name' => JRequest::getString($prefix.'first_name',null),
			'middle_name' => JRequest::getString($prefix.'middle_name',null),
			'last_name' => JRequest::getString($prefix.'last_name',null),
			'business_name' => JRequest::getString($prefix.'business_name',null),
			'address1' => JRequest::getString($prefix.'address1',null),
			'address2' => JRequest::getString($prefix.'address2',null),
			'city' => JRequest::getString($prefix.'city',null),
			'state' => JRequest::getString($prefix.'state',null),
			'zip' => JRequest::getString($prefix.'zip',null),
			'country' => JRequest::getString($prefix.'country',null)
		);
		if($order->user_id) {
			$data['user_id']=$order->user_id;
		}

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onValidateAddress', array(&$data, $type));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			}
		}
		return $data;
	}

	protected function _lock_addresses() {
		$order = $this->getThisModel()->getItem();

		if($order->shipping_address_id && !$order->shipping_address->locked) {
			$shipdata = array(
				'donorcart_address_id' => $order->shipping_address_id,
				'locked' => 1
			);
			FOFModel::getTmpInstance('addresses','DonorcartModel')->save($shipdata);
		}
		if($order->billing_address_id && $order->billing_address_id != $order->shipping_address_id && !$order->billing_address->locked) {
			$billdata = array(
				'donorcart_address_id' => $order->billing_address_id,
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
		$results = $dispatcher->trigger('onBeforePostback', array(&$plugin_validated));
		foreach($results as $result) {
			if($result === true) {
				$is_valid = true;
			} elseif(is_string($result)) {
				$returnval .= $result;
			}
		}
		//none of the payment plugins were able to validate the request. Do not continue
		if(!$is_valid) return false;

		$results = $dispatcher->trigger('onPostback', array(&$is_valid, &$plugin_validated));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			} elseif(is_string($result)) {
				$returnval .= $result;
			}
		}
		$results = $dispatcher->trigger('onAfterPostback', array($is_valid, &$plugin_validated));
		foreach($results as $result) {
			if(is_string($result)) {
				$returnval .= $result;
			}
		}

		if($returnval) return $returnval;
		return true;
	}

	public function _clearSession() {
		$session = JFactory::getSession();
		$session->set('cart_id',null);
		$session->set('order_id',null);
	}

	public function _sendConfirmationEmails() {
		if($this->params->get('send_confirmation_email_to_admin')) {
			$data = $this->params->get('admin_email_template');
			//TODO: Implement the rest of this functionality
		}
		if($this->params->get('send_confirmation_email_to_user')) {
			$data = $this->params->get('user_email_template');
			//TODO: Implement the rest of this functionality
		}
	}
}
