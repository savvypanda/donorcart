<?php defined('_JEXEC') or die('Restricted Access');

class DonorcartModelOrders extends FOFModel {

	public function __construct($config = array()) {
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$input = JFactory::getApplication()->input;
		$request_id = ($input->get('option')=='com_donorcart' && $input->get('view')=='order')?$input->get('id',false,'INT'):false;
		$my_id = $request_id?$request_id:$session->get('order_id',0);
		$config['id']=$my_id;

		parent::__construct($config);

		if($request_id) {
			if($viewtoken = $input->get('viewtoken','')) {
				$order = $this->getItem();
				if(!$order->viewtoken || $viewtoken != $order->viewtoken) {
					if(!$user->id || $user->id != $order->user_id) {
						//Jerror::raiseError(403,'You are not allowed to view that order.');
						throw new Exception('You are not allowed to view that order', 403);
						JFactory::getApplication()->redirect('index.php');
					}
				}
			}
		}
		//this code is here to limit the orders a user can view to their own completed orders
		$this->setState('user_id',$user->id);
		$this->setState('status','complete');
	}

	public function setIDsFromRequest(){
		return $this;
	}

	public function updateOrderTotal($subtotal = null, $savetotal = true, $status = NULL) {
		if(!$this->getId()) return false;
		if(!$subtotal) {
			$subtotal = $this->record->cart->subtotal;
		}
		$total = $subtotal;
		//Todo: If we have more calculations to do, do them here

		if($savetotal && $total != $this->record->order_total) {
			$this->_db->setQuery('UPDATE #__donorcart_orders SET order_total='.$this->_db->quote($total).(is_null($status)?'':', status='.$this->_db->quote($status)).' WHERE donorcart_order_id='.$this->_db->quote($this->record->donorcart_order_id));
			$this->_db->query();
			$this->record->order_total = $total;
		}

		return $total;
	}

	protected function onProcessList(&$resultArray) {
		foreach($resultArray as &$item) {
			$item = $this->getItem($item->donorcart_order_id);
		}
	}

	protected function onAfterGetItem(&$record) {
		parent::onAfterGetItem($record);
		//Now let's set the rest of the references
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
	}

	protected function onBeforeSave(&$data, &$table) {
		if(!parent::onBeforeSave($data, $table)) return false;

		//$table->order_total = $this->updateOrderTotal(null, false);
		return true;
	}

	protected function onBeforeDelete(&$id, &$table) {
		//first of all, do not allow an order to be deleted after it has been submitted.
		$record = $this->getItem($id);
		if(!is_object($record) || $record->status=='submitted' || $record->status=='complete') {
			return false;
		}
		$result = parent::onBeforeDelete($id, $table);
		return $result;
	}

	protected function onAfterDelete($id) {
		JFactory::getSession()->set('order_id',null);

		//first let's clear the cart
		FOFModel::getAnInstance('carts','DonorcartModel')->delete();

		//then remove any payment associated with this order
		if($this->_recordForDeletion->payment_id) {
			FOFModel::getTmpInstance('payments','DonorcartModel')->setId($this->_recordForDeletion->payment_id)->delete();
		}

		//then remove any non-locked addresses associated with this order
		$addressModel = FOFModel::getAnInstance('addresses','DonorcartModel');
		if($this->_recordForDeletion->shipping_address_id) {
			$shipping_address = $addressModel->getItem($this->_recordForDeletion->shipping_address_id);
			if(is_object($shipping_address) && !$shipping_address->locked) {
				$addressModel->delete();
			}
		}
		if($this->_recordForDeletion->billing_address_id && $this->_recordForDeletion->billing_address_id != $this->_recordForDeletion->shipping_address_id) {
			$billing_address = $addressModel->getItem($this->_recordForDeletion->billing_address_id);
			if(is_object($billing_address) && !$billing_address->locked) {
				$addressModel->delete();
			}
		}
		return parent::onAfterDelete($id);
	}

	public function createOrder() {
		$order = $this->getTable();
		$cartmodel = FOFModel::getAnInstance('carts','DonorcartModel');
		if(!$cart_id = $cartmodel->getId()) return false;
		$cart = $cartmodel->getItem();

		$data = array('cart_id' => $cart_id, 'order_total' => $cart->subtotal);
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

	public function resetOrder($order_id = null) {
		if(!$order_id || !is_numeric($order_id)) {
			$order_id = $this->getId();
			if(!$order_id) return false;
		}
		//$order = $this->getItem();
		//if(!is_object($order) || !$order->donorcart_order_id) return false;

		$query = 'UPDATE #__donorcart_orders SET status="cart" WHERE donorcart_order_id='.$order_id;
		$this->_db->setQuery($query);
		$this->_db->query();

		return true;
	}

	public function updateUserData($order_id, $user_id = null, $email = null, $status = null) {
		$order = $this->getItem($order_id);
		if(!is_object($order) || !$order->donorcart_order_id || !$order->cart_id || !is_object($order->cart)) return false;
		if($user_id) {
			if(!($user = JFactory::getUser($user_id))) return false;
			$email = $user->email;
		}
		$order_update_array = array('donorcart_order_id'=>$order->donorcart_order_id);
		if(!empty($user_id)) $order_update_array['user_id'] = $user_id;
		if(!empty($email)) $order_update_array['email'] = $email;
		if(!empty($status)) $order_update_array['status'] = $status;

		//run the update in a transaction, so that if part of it fails, the entire update fails
		$this->_db->transactionStart();

		if(!empty($user_id) && $order->cart->user_id != $user_id) {
			if(!FOFModel::getAnInstance('carts','DonorcartModel')->save(array('donorcart_cart_id'=>$order->cart_id, 'session_id'=>'', 'user_id'=>$user_id))) {
				$this->_db->transactionRollback();
				return false;
			}
		}
		if($order->shipping_address_id && !empty($user_id) && is_object($order->shipping_address) && $order->shipping_address->user_id != $user_id) {
			if($order->shipping_address->locked) {
				$order_update_array['shipping_address_id'] = null;
				if($order->billing_address_id && $order->billing_address_id == $order->shipping_address_id) {
					$order_update_array['billing_address_id'] = null;
				}
			} else {
				if(!FOFModel::getAnInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$order->shipping_address_id, 'user_id'=>$user_id))) {
					$this->_db->transactionRollback();
					return false;
				}
			}
		}
		if($order->billing_address_id && $order->billing_address_id != $order->shipping_address_id && !empty($user_id) && is_object($order->billing_address) && $order->billing_address->user_id != $user_id) {
			if($order->billing_address->locked) {
				$order_update_array['billing_address_id'] = null;
			} else {
				if(!FOFModel::getAnInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$order->billing_address_id, 'user_id'=>$user_id))) {
					$this->_db->transactionRollback();
					return false;
				}
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

	public function removeUserData($order_id, $remove_email = false, $status = null, $session_id = null) {
		$order = $this->getItem($order_id);

		//do not remove the userdata from a submitted order
		if(!is_object($order) || !$order->donorcart_order_id || $order->status == 'submitted' || $order->status == 'complete' || ($order->payment_id && is_object($order->payment) && $order->payment->status == 'complete')) {
			return false;
		}


		$order_update_array = array('donorcart_order_id'=>$order->donorcart_order_id,'user_id'=>null);
		if($remove_email) $order_update_array['email'] = '';
		if(!empty($status)) $order_update_array['status'] = $status;

		//run the update in a transaction, so that if part of it fails, the entire update fails
		$this->_db->transactionStart();

		if($order->cart_id && is_object($order->cart) && !empty($order->cart->user_id)) {
			if(!FOFModel::getAnInstance('carts','DonorcartModel')->save(array('donorcart_cart_id'=>$order->cart_id, 'session_id'=>$session_id, 'user_id'=>null))) {
				$this->_db->transactionRollback();
				return false;
			}
		}
		if($order->shipping_address_id && is_object($order->shipping_address) && !empty($order->shipping_address->user_id)) {
			if($order->shipping_address->locked) {
				$order_update_array['shipping_address_id'] = null;
				if($order->billing_address_id == $order->shipping_address_id) {
					$order_update_array['billing_address_id'] = null;
				}
			} else {
				if(!FOFModel::getAnInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$order->shipping_address_id, 'user_id'=>null))) {
					$this->_db->transactionRollback();
					return false;
				}
			}
		}
		if($order->billing_address_id && $order->billing_address_id != $order->shipping_address_id && is_object($order->billing_address) && !empty($order->billing_address->user_id)) {
			if($order->billing_address->locked) {
				$order_update_array['billing_address_id'] = null;
			} else {
				if(!FOFModel::getAnInstance('addresses','DonorcartModel')->save(array('donorcart_address_id'=>$order->billing_address_id, 'user_id'=>null))) {
					$this->_db->transactionRollback();
					return false;
				}
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
