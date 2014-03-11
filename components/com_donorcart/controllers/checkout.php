<?php defined('_JEXEC') or die("Restricted Access");

class DonorcartControllerCheckout extends FOFController {
	private $params;

	public function __construct($config = array()) {
		$config['modelName']='DonorcartModelOrders';

		parent::__construct($config);
		$this->registerTask('login','_login');
		$this->registerTask('logout','_logout');
		$this->registerTask('register','_register');

		$this->registerTask('removeItem','_remove_item');
		$this->registerTask('emptyCart','_empty_cart');
		$this->registerTask('resetOrder','_reset_order');
		$this->registerTask('setRecurring','_enable_recurring');
		$this->registerTask('setNoRecurring','_disable_recurring');

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

		$ordermodel =& $this->getThisModel();
		$order = $ordermodel->getItem();

		//If the user is not correctly saved to the order and cart, correct that here
		if($order->donorcart_order_id && !in_array($task,array('login','logout','register','emptyCart','resetOrder','postback'))) {
			$user = JFactory::getUser();
			if($user->id) {
				if($order->user_id != $user->id) {
					if($order->user_id) {
						//the user is logged in but the order belongs to a different user.
						//We should forget the cart and the order.
						$session = JFactory::getSession();
						$session->set('cart_id',null);
						$session->set('order_id',null);
						$ordermodel->setId(0);
						$task='read';
					} else {
						//the user is logged in but the order is not. Fix it
						$ordermodel->updateUserData(null, $user->id, $user->email, 'checkout');
					}
				}
			} elseif(!empty($order->user_id)) {
				//The user is not logged in but the order is. We should forget the cart and the order
				$session = JFactory::getSession();
				$session->set('cart_id',null);
				$session->set('order_id',null);
				$ordermodel->setId(0);
				$task='read';
			}
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

		$order_id = $ordermodel->getId();
		$order = $ordermodel->setId($order_id)->getItem();

		if(!$order->cart_id || !is_object($order->cart) || empty($order->cart->items)) {
			$this->layout = 'emptycart';
		} elseif($order->status == 'complete') {
			$this->_completeOrder();
			$this->layout = 'thankyou';
		} elseif($this->layout != 'review') {
			$this->layout = 'default';
		}

		$view = $this->getThisView();
		$view->assign('params',$this->params);
		$this->display();
	}

	/* This function is based on the J2.5 Users component code. Alter at your risk */
	// TODO: Replace login, logout, and register functionality in component with user plugins to handle the user data on pending orders
	public function _login() {
		JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');

		$mainframe = JFactory::getApplication();
		$session = JFactory::getSession();

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
		//JRequest::checkToken() or JRequest::checkToken('get') or die('Invalid Token');
		$session = JFactory::getSession();
		$cartid = $session->get('cart_id',null);
		$orderid = $session->set('order_id',null);
		JFactory::getApplication()->logout();
		$session->restart();
		$session->set('cart_id',$cartid);
		$session->set('order_id',$orderid);
		return $this->getThisModel()->removeUserData(null, false, 'cart', $session->getId());
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
		FOFModel::getAnInstance('carts','DonorcartModel')->removeItemFromCart($id, true);
		//FOFModel::getTmpInstance('orders','DonorcartModel')->calcOrderTotal($this->order);
		//$this->order = FOFModel::getTmpInstance('orders','DonorcartModel')->getItem($this->order->donorcart_order_id);
		return true;
	}

	public function _enable_recurring() {
		return FOFModel::getAnInstance('carts','DonorcartModel')->enableRecurring();
		return true;
	}
	public function _disable_recurring() {
		FOFModel::getAnInstance('carts','DonorcartModel')->disableRecurring();
		return true;
	}
	public function _reset_order() {
		$returnval = $this->getThisModel()->resetOrder();
		if($returnval === true && JRequest::getCmd('format')=='raw') return 'success';
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

		//Step 2: save the addresses (if present)
		//first we need to include some logic to determine:
		//A) If there is an address at all on the order
		//B) If we are saving new addresses
		//C) If we are updating existing addresses
		//D) If we are referencing a locked address
		$addressmodel = FOFModel::getTmpInstance('addresses','DonorcartModel');

		//processing shipping address first
		$shipto_id = JRequest::getInt('shipto_id',null);
		if(is_null($shipto_id)) {
			//no shipping address on the order
			if($this->params->get('shipto_option',0) == 2) {
				//if the shipping address is required but not present, the form is invalid
				$is_valid = false;
			}
		} else {
			$shipping_address = $addressmodel->getItem($shipto_id);
			//if the shipping address is locked, validate it without saving
			if($shipping_address->locked) {
				if(!$this->_validateAddress($shipping_address, 'shipping')) $is_valid = false;
			} else {
				//we are create a new address or updating an existing one
				$shipdata = $this->_prepareAddress('shipping','ship_',$is_valid);
				if($shipto_id) $shipdata['donorcart_address_id'] = $shipto_id;

				$result = $addressmodel->save($shipdata);
				if(!$result) {
					$is_valid = false;
					JFactory::getApplication()->enqueueMessage($addressmodel->getError(), 'error');
				}
				$shipto_id = $addressmodel->getId();
			}
		}
		$orderdata['shipping_address_id']=$shipto_id;

		//now processing the billing address using essentially the same logic
		$set_billto_to_ship_addr = (bool) JRequest::getInt('use_same_address_for_billto',0);
		if($set_billto_to_ship_addr) {
			$billto_id = $shipto_id;
		} else {
			$billto_id = JRequest::getInt('billto_id',null);
		}
		if(is_null($billto_id)) {
			if($this->params->get('billto_option',0) == 2) {
				$is_valid = false;
			}
		} else {
			$billing_address = $addressmodel->getItem($billto_id);
			if($billing_address->locked) {
				if(!$this->_validateAddress($billing_address, 'billing')) $is_valid = false;
			} else {
				$billdata = $this->_prepareAddress('billing','bill_',$is_valid);
				if($billto_id) $billdata['donorcart_address_id'] = $billto_id;

				$result = $addressmodel->save($billdata);
				if(!$result) {
					$is_valid = false;
					JFactory::getApplication()->enqueueMessage($addressmodel->getError(), 'error');
				}
				$billto_id = $addressmodel->getId();
			}
		}
		$orderdata['billing_address_id']=$billto_id;


		// ************************************
		//Step 3: Record the recurring options, dedication, and special instructions
		$payment_name = JRequest::getVar('payment_method','');
		if($this->params->get('allow_recurring_donations',0)==0 || !JRequest::getBool('recurring',false)) {
			$orderdata['recurring_frequency'] = 'One Time';
		} else {
			$recurring_field = $payment_name.'_payment_frequency';
			$orderdata['recurring_frequency'] = JRequest::getString($recurring_field,'One Time');
		}
		/* switch($this->params->get('allow_recurring_donations',0)){
			case 1: //the user can decide if they want their donation to be recurring
				$recurring = (int) JRequest::getBool('recurring',false);
				break;
			case 2: //all donations are recurring
				$recurring = 1;
				break;
			default: //all donations are one-time
				$recurring = 'One Time';
				break;
		} */
		/* if($recurring != $order->cart->recurring) {
			$db = JFactory::getDbo();
			$db->setQuery('UPDATE #__donorcart_carts SET recurring='.$recurring.' WHERE donorcart_cart_id='.$order->cart->donorcart_cart_id);
			$db->query();
			$order->cart->recurring = $recurring;
		} */
		if($this->params->get('allow_dedication_option',1) && ($dedicated = JRequest::getBool('dedicate',false))) {
			$orderdata['dedication'] = json_encode(array(
				'name' => JRequest::getString('dedication_name',''),
				'email' => JRequest::getString('dedication_email',''),
				'text' => JRequest::getString('dedication_text','')
			));
		} else {
			$orderdata['dedication']='';
		}
		$orderdata['special_instr'] = JRequest::getString('special_instr','');


		//Step 4: save the payment details (save and refresh the order first, so the payment plugins have the most recent data to work with)
		$ordermodel->save($orderdata);
		$order = $ordermodel->setId($order_id)->getItem();
		$orderdata = array('donorcart_order_id'=>$order_id);

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
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
			$orderdata['payment_name'] = $payment_name;
			$orderdata['payment_id'] = $payment_model->getId();
		} else {
			$is_valid = false;
		}

		//Step 5: Save the order.
		if(count($orderdata > 1)) $ordermodel->save($orderdata);


		if($is_valid) {
			if($this->params->get('review_option')==0) {
				//Step 6: Submit the order (ONLY IF VALID AND THE COMPONENT PARAMETERS SPECIFY NO REVIEW STEP)
				$htmloutput = '';
				$order = $ordermodel->setId($order_id)->getItem(); //refresh the order in case it was modified since the beginning of the submit function
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
				//Step 6 Alternate: set the layout to 'review' (ONLY IF THE ORDER IS VALID)
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
			if(!$this->_validateAddress($order->shipping_address,'shipping')) $is_valid = false;
		} elseif($this->params->get('shipto_option',0) == 2) {
			$is_valid = false;
		}
		if($order->billing_address_id) {
			if(!$this->_validateAddress($order->billing_address,'billing')) $is_valid = false;
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

	private function _prepareAddress($type, $prefix, &$is_valid) {
		if(!$is_valid) return false;
		$order = $this->getThisModel()->getItem();

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

		if(!$this->_validateAddress($data, $type)) {
			$is_valid = false;
		}

		return $data;
	}

	private function _validateAddress(&$data, $type) {
		if(is_object($data)) {
			$editdata = get_object_vars($data);
		} elseif(is_array($data)) {
			$editdata = &$data;
		} else {
			return false;
		}
		$is_valid = true;

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$results = $dispatcher->trigger('onValidateAddress', array(&$editdata, $type));
		foreach($results as $result) {
			if($result === false) {
				$is_valid = false;
			}
		}

		return $is_valid;
	}

	private function _lock_addresses() {
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

	private function _completeOrder() {
		$ordermodel =& $this->getThisModel();
		$order = $ordermodel->getItem();
		if(!$order || !is_object($order) || !$order->status=='complete') return false;
		$updatefields = array();
		if(!$order->viewtoken) $updatefields['viewtoken'] = $order->viewtoken = is_callable(array('JApplication', 'getHash'))?JApplication::getHash($order->donorcart_order_id.JSession::getFormToken()):md5(JFactory::getApplication()->get('secret').$order->donorcart_order_id.JSession::getFormToken());
		if(!$order->completed_on) $updatefields['completed_on'] = $order->completed_on = date('Y-m-d H:i:s');
		if(!empty($updatefields)) {
			$db = JFactory::getDbo();
			$update_statements = array();
			foreach($updatefields as $field => $value) $update_statements[] = $field.'='.$db->quote($value);
			$query = 'UPDATE #__donorcart_orders SET '.implode(', ',$update_statements).' WHERE donorcart_order_id='.$db->quote($order->donorcart_order_id);
			$db->setQuery($query);
			$db->query();
		}

		JPluginHelper::importPlugin('donorcart');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onOrderCompletion', array($order));

		$session = JFactory::getSession();
		$session->set('cart_id',null);
		$session->set('order_id',null);
	}
}
