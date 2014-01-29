<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelOrders extends FOFModel {
	//public $cart, $billing_address, $shipping_address, $payment, $custom_fields;

	public function __construct($config = array()) {
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$input = JFactory::getApplication()->input;
		$request_id = $input->get('id',false,'INT');
		$my_id = $request_id?$request_id:$session->get('order_id',0);
		if(!$my_id) {
			$cart_id = $session->get('cart_id',0);
			if($cart_id) {
				$my_id = $this->getOrderIdFromCartId($cart_id);
			}
		}
		//$config=array_merge($config, array('table'=>'orders','id'=>$my_id));
		$config['id']=$my_id;

		parent::__construct($config);

		if($request_id) {
			$viewtoken = $input->get('viewtoken','');
			$order = $this->getItem();
			if(!$order->viewtoken || $viewtoken != $order->viewtoken) {
				if(!$user->id || $user->id != $order->user_id) {
					//Jerror::raiseError(403,'You are not allowed to view that order.');
					throw new Exception('You are not allowed to view that order', 403);
					JFactory::getApplication()->redirect('index.php');
				}
			}
		}
		$this->setState('user_id',$user->id);
		$this->setState('status','complete');
	}

	private function getOrderIdFromCartId($cart_id) {
		$query = 'SELECT donorcart_order_id FROM #__donorcart_orders WHERE cart_id='.$this->_db->quote($cart_id);
		$this->_db->setQuery($query);
		$this->_db->query();
		if($this->_db->getNumRows() == 1) {
			return $this->_db->loadResult();
		}
		return 0;
	}

	public function getSubTotal($record = null) {
		if(!$record) {
			$record = $this->getItem();
		}
		$subtotal = 0;
		if(is_object($record) && $record->cart_id) {
			if(!$record->cart || !is_object($record->cart)) $record->cart = FOFModel::getTmpInstance('carts','DonorcartModel')->getItem($record->cart_id);
			if(is_array($record->cart->items) && !empty($record->cart->items)) {
				foreach($record->cart->items as $item) {
					$subtotal += $item->qty * $item->price;
				}
			}
		}
		return $subtotal;
	}

	public function calcOrderTotal(&$record = null) {
		if(!$record) {
			$record = $this->getItem();
		} elseif(!is_object($record)) {
			$record = (object) $record;
			$this->onAfterGetItem($record);
		}
		$subtotal = $this->getSubTotal($record);
		return $subtotal;
		//Todo: If we have more calculations to do, do them here
		//$total = $subtotal;
		//return $total;
	}

	public function updateOrderTotal(&$record = null) {
		$total = $this->calcOrderTotal($record);

		if(!$record->donorcart_order_id) return false;

		if($total != $record->order_total) {
			$this->_db->setQuery('UPDATE #__donorcart_orders SET order_total='.$this->_db->quote($total).' WHERE donorcart_order_id='.$this->_db->quote($record->donorcart_order_id));
			$this->_db->query();
			$record->order_total = $total;
		}

		return true;
	}

	protected function onProcessList(&$resultArray) {
		foreach($resultArray as &$item) {
			$item = $this->getItem($item->donorcart_order_id);
		}
	}

	protected function onAfterGetItem(&$record) {
		parent::onAfterGetItem($record);
		if($record->cart_id) {
			$record->cart = FOFModel::getTmpInstance('carts','DonorcartModel')->getItem($record->cart_id);
		}
		if($record->shipping_address_id) {
			$record->shipping_address = FOFModel::getTmpInstance('addresses','DonorcartModel')->getItem($record->shipping_address_id);
		}
		if($record->billing_address_id) {
			$record->billing_address = FOFModel::getTmpInstance('addresses','DonorcartModel')->getItem($record->billing_address_id);
		}
		if($record->payment_id) {
			$record->payment = FOFModel::getTmpInstance('payments','DonorcartModel')->getItem($record->payment_id);
		}
		if($record->donorcart_order_id) {
			$record->custom_fields = FOFModel::getTmpInstance('customFields','DonorcartModel')->order_id($record->donorcart_order_id)->getItemList(true);
		}
	}

	protected function onBeforeSave(&$data, &$table) {
		if(!parent::onBeforeSave($data, $table)) return false;
		/*
		 * parent::onBeforeSave merges the (new) data and (old) table, so we do not have to do that here.

		if(is_object($table)) { $tabledata = get_object_vars($table);
		} elseif(is_array($table)) { $tabledata = $table;
		} else { return false; //we can't update the order total if we don't know what the data type is that we are saving
		}
		if(is_object($data)) { $datadata = get_object_vars($data);
		} elseif(is_array($data)) { $datadata = $data;
		} else { return false; //we can't update the order total if we don't know what the data type is that we are saving
		}
		$newdata = array_merge($tabledata, $datadata);
		$total = $this->calcOrderTotal($newdata);

		if($data instanceof FOFTable) {
			$data->bind(array('order_total'=>$total));
		} elseif(is_object($data)) {
			$data->order_total = $total;
		} elseif(is_array($data)) {
			$data['order_total'] = $total;
		} else {
			return false; //we can't update the order total if we don't know what the data type is that we are saving
		}
		return true;

		 * Now let's update the order total
		 */
		$table->order_total = $this->calcOrderTotal($table);

		return true;
	}

	protected function onBeforeDelete(&$id, &$table) {
		//first of all, do not allow an order to be deleted after it has been submitted.
		$record = $this->getItem($id);
		if(!is_object($record) || $record->submitted) {
			return false;
		}
		//also do not allow it to be deleted if the payment has been completed.
		if($record->payment_id && is_object($record->payment) && $record->payment->status == 'completed') {
			return false;
		}
		$result = parent::onBeforeDelete($id, $table);
		if($result) {
			//first remove all custom fields assiciated with this order
			$custom_fields = FOFModel::getTmpInstance('customFields','DonorcartModel')->order_id($id)->getItemList(true);
			if(!empty($custom_fields)) {
				foreach($custom_fields as $field) {
					FOFModel::getTmpInstance('customFields','DonorcartModel')->setId($field->donorcart_custom_field_id)->delete();
				}
			}
			//then remove any non-locked addresses associated with this order
			if($record->shipping_address_id && $record->shipping_address->locked != 1) {
				FOFModel::getTmpInstance('addresses','DonorcartModel')->setId($record->shipping_address_id)->delete();
			}
			if($record->billing_address_id && $record->shipping_address_id != $record->billing_address_id && $record->billing_address->locked != 1) {
				FOFModel::getTmpInstance('addresses','DonorcartModel')->setId($record->billing_address_id)->delete();
			}
			//then remove any payment associated with this order
			if($record->payment_id) {
				FOFModel::getTmpInstance('payments','DonorcartModel')->setId($record->payment_id)->delete();
			}
			//TODO: Decide if we should also remove the cart when an order is deleted from the frontend.
		}
		return $result;
	}

	public function createOrder() {
		$order = $this->getTable();
		$cart_id = JFactory::getSession()->get('cart_id',0);
		if(!$cart_id) return false;
		$cart = FOFModel::getTmpInstance('carts','DonorcartModel')->getItem($cart_id);
		$subtotal = 0;
		if(is_array($cart->items)) {
			foreach($cart->items as $item) {
				$subtotal += $item->qty * $item->price;
			}
		}

		$data = array('cart_id' => $cart_id, 'order_total' => $subtotal);
		$user = JFactory::getUser();
		if($user->id) {
			$data['user_id'] = $user->id;
			$data['email'] = $user->email;
		}

		if(!$order->bind($data) || !$order->store()) {
			return false;
		};
		JFactory::getSession()->set('order_id',$order->donorcart_order_id);
		return $this->setId($order->donorcart_order_id);
	}

	public function updateUserData($order_id, $user_id = null, $email = null, $status = null) {
		$this->getItem($order_id);
		if($user_id) {
			if(!($user = JFactory::getUser($user_id))) return false;
			$email = $user->email;
		}
		$order_update_array = array();
		if(!empty($user_id)) $order_update_array['user_id'] = $user_id;
		if(!empty($email)) $order_update_array['email'] = $email;
		if(!empty($status)) $order_update_array['status'] = $status;

		//run the update in a transaction, so that if part of it fails, the entire update fails
		$this->_db->transactionStart();

		if($this->cart_id && !empty($user_id) && is_object($this->cart) && $this->cart->user_id != $user_id) {
			if(!FOFModel::getTmpInstance('carts','DonorcartModel')->save(array('donorcart_cart_id'=>$this->cart_id, 'user_id'=>$user_id))) {
				$this->_db->transactionRollback();
				return false;
			}
		}
		if($this->shipping_address_id && !empty($user_id) && is_object($this->shipping_address) && $this->shipping_address->user_id != $user_id) {
			if($this->shipping_address->locked) {
				$this->shipping_address_id = null;
				$this->shipping_address = null;
				$order_update_array['shipping_address_id'] = null;
			} else {
				if(!FOFModel::getTmpInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$this->shipping_address_id, 'user_id'=>$user_id))) {
					$this->_db->transactionRollback();
					return false;
				}
			}
		}
		if($this->billing_address_id && !empty($user_id) && is_object($this->billing_address) && $this->billing_address->user_id != $user_id) {
			if($this->billing_address->locked) {
				$this->billing_address_id = null;
				$this->billing_address = null;
				$order_update_array['billing_address_id'] = null;
			} else {
				if(!FOFModel::getTmpInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$this->billing_address_id, 'user_id'=>$user_id))) {
					$this->_db->transactionRollback();
					return false;
				}
			}
		}
		if($this->payment_id && is_object($this->payment) && $this->payment->user_id != $user_id) {
			if(!FOFModel::getTmpInstance('payments','DonorcartModel')->save(array('donorcart_payment_id'=>$this->payment_id, 'user_id'=>$user_id))) {
				$this->_db->transactionRollback();
				return false;
			}
		}

		if(!empty($order_update_array)) {
			if(!$this->save($order_update_array)) {
				$this->_db->transactionRollback();
				return false;
			}
		}
		$this->_db->transactionCommit();
		return true;
	}

	public function removeUserData($order_id, $remove_email = false, $status = null) {
		$this->getItem($order_id);

		$order_update_array = array('user_id'=>null);
		if($remove_email) $order_update_array['email'] = '';
		if(!empty($status)) $order_update_array['status'] = $status;

		//run the update in a transaction, so that if part of it fails, the entire update fails
		$this->_db->transactionStart();

		if($this->cart_id && is_object($this->cart) && !empty($this->cart->user_id)) {
			if(!FOFModel::getTmpInstance('carts','DonorcartModel')->save(array('donorcart_cart_id'=>$this->cart_id, 'user_id'=>null))) {
				$this->_db->transactionRollback();
				return false;
			}
		}
		if($this->shipping_address_id && is_object($this->shipping_address) && !empty($this->shipping_address->user_id)) {
			if($this->shipping_address->locked) {
				$this->shipping_address_id = null;
				$this->shipping_address = null;
				$order_update_array['shipping_address_id'] = null;
			} else {
				if(!FOFModel::getTmpInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$this->shipping_address_id, 'user_id'=>null))) {
					$this->_db->transactionRollback();
					return false;
				}
			}
		}
		if($this->billing_address_id && is_object($this->billing_address) && !empty($this->billing_address->user_id)) {
			if($this->billing_address->locked) {
				$this->billing_address_id = null;
				$this->billing_address = null;
				$order_update_array['billing_address_id'] = null;
			} else {
				if(!FOFModel::getTmpInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$this->billing_address_id, 'user_id'=>null))) {
					$this->_db->transactionRollback();
					return false;
				}
			}
		}
		if($this->payment_id && is_object($this->payment)) {
			if($this->payment->status == 'complete') return false;
			if(!FOFModel::getTmpInstance('payments','DonorcartModel')->save(array('donorcart_payment_id'=>$this->payment_id, 'user_id'=>null))) {
				$this->_db->transactionRollback();
				return false;
			}
		}
		if(!$this->save($order_update_array)) {
			$this->_db->transactionRollback();
			return false;
		}
		$this->_db->transactionCommit();
		return true;
	}
}
