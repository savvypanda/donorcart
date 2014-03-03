<?php defined('_JEXEC') or die('Restricted Access');

class JPluginDonorcart extends JPlugin {

	/**
	 * A string containing the name of the payment plugin. Must be overwritten in child classes.
	 *
	 * @var    string
	 */
	protected $_name;

	/**
	 * A string containing the sku for the processing fee.
	 * It is not recommended to changes this for the best compatibility between payment plugins.
	 *
	 * @var    string
	 */
	protected $_cc_fees_sku = 'PROCFEE';

	/**
	 * A string containing the name for the processing fee
	 *
	 * @var    string
	 */
	protected $_cc_fees_name = 'Processing Fee';


	public function __construct(&$subject, $config) {
		if(!$this->_name) die('Donorcart plugin must include a name.');
		parent::__construct($subject, $config);
	}

	/*
	 * Returns the name of the payment plugin
	 *
	 * @return string The name of the payment plugin
	 */
	public function getName() {
		return $this->_name;
	}
	/*
	 * Returns whether or not the payment plugin is currently active (can be used on new orders)
	 *
	 * @return boolean True if the plugin is active. False if it is inactive.
	 */
	public function isActive() {
		return ($this->params->get('active')==1);
	}

	/*
	 * Displays the type of payment for the user to select (if more than on payment plugin is currently active)
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 *
	 * @return boolean|string False if this payment plugin can't or shouldn't process this order. The name of the payment plugin if it can.
	 */
	public function onDisplayPaymentSelector($order, $params) {
		return $this->isActive()?$this->getName():false;
	}

	/*
	 * Displays the payment form (assuming the user selects this payment method)
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 *
	 * @return string The HTML for the payment form
	 */
	public function onDisplayPaymentForm($order, $params) {
		if(!$this->isActive()) return;
		$path = JPluginHelper::getLayoutPath('donorcart', $this->getName(), 'paymentform');
		$contents = '';
		if(file_exists($path)) {
			ob_start();
			include $path;
			$contents = ob_get_clean();
		}
		return $contents;
	}

	/*
	 * Use to add custom validation rules for billing and shipping addresses
	 *
	 * @param array &$address The address to validate
	 * @param string $type The type of address (billing or shipping)
	 *
	 * @return boolean False if the address is invalid
	 */
	public function onValidateAddress($address, $type) {
		return;
	}

	/*
	 * Processes the submitted order and saves the submitted payment info.
	 * This function is fired PRIOR to confirming the order - DO NOT submit the payment here
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 * @param string &$payment_name The name of the payment plugin that was selected for this order
	 *
	 * @return null|boolean|array Null if this payment plugin was not selected
	 * 							  False if the payment form was filled out incorrectly.
	 * 						 	  An array containing the payment details if this payment plugin was selected and the form was filled out correctly.
	 */
	public function onSubmitOrder($order, $params, $payment_name) {
		if(!empty($payment_name) && $payment_name != $this->getName()) return;
		die('You must define the onSubmitOrder function in your plugin.');

		//example code for this function
		$form_was_filled_out_correctly = true;
		if(!$form_was_filled_out_correctly) return false;

		$payment_name = $this->getName();

		$data = array(
			'status' => 'pending',
			'val1' => 'myval',
			'val2' => 'myval2',
			'etc...'
		);
		$payment = array(
			'infohash' => json_encode($data)
		);

		return $payment;
	}

	/*
	 * Processes the submitted order - performing any necessary actions to submit the order to the payment gateway
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 * @param boolean &$is_valid Whether or not the order has passed all validation
	 *
	 * @return null|boolean|string NULL if this payment plugin was not selected
	 * 							   True if the payment was completed successfully
	 * 						  	   The HTML to redirect the user to the payment gateway if more details must be collected (eg: credit card info, etc...)
	 */
	public function onConfirmOrder($order, $params, $is_valid) {
		if(!is_valid || $order->payment_name != $this->getName()) return;

		$path = JPluginHelper::getLayoutPath('donorcart', $this->getName(), 'submitform');
		$contents = '';
		if(file_exists($path)) {
			ob_start();
			include $path;
			$contents = ob_get_clean();
		}
		return $contents;
	}

	/*
	 * Displays the payment information after it has been entered
	 *
	 * @param Object $order The donorcartModelOrders object containing the current order
	 * @param Object $params The com_donorcart JParams object
	 * @param string $payment_name The name of the payment plugin that was selected for this order
	 *
	 * @return string The HTML containing the details of the payment to be displayed on the user's screen.
	 */
	public function onDisplayPaymentInfo($order, $params, $payment_name) {
		if($payment_name != $this->getName()) return;
		$path = JPluginHelper::getLayoutPath('donorcart', $this->getName(), 'paymentinfo');
		$contents = '';
		if(file_exists($path)) {
			ob_start();
			include $path;
			$contents = ob_get_clean();
		}
		return $contents;
	}

	/*
	 * Code to validate a request when returning from the payment gateway
	 *
	 * @param string &$plugin_validated The payment plugin that has already validated this request.
	 * 								   Empty if it has not been validated by any other payment plugins yet.
	 *
	 * @return boolean True if the postback response can be validated by the current plugin.
	 */
	public function onBeforePostback($plugin_validated) {
		return;

		//example code
		if(JRequest::getString('someotheservervar')=='someotherserverval') {
			$plugin_validated = $this->getName();
			return true;
		}
	}

	/*
	 * Code to process a request when returning from the payment gateway
	 *
	 * @param boolean &$is_valid Whether or not the payment information in the current request is valid
	 * @param string &$plugin_validated The payment plugin that has already validated this request.
	 */
	public function onPostback($is_valid, $plugin_validated) {
		return;

		//example code
		if($plugin_validated == $this->getName() && $is_valid) {
			$payment = $order->payment;
			$data = json_decode($payment->infohash);

			$paymentDetails = array(
				'var1' => 'someval',
				'var2' => 'someotherval',
				'etc...'
			);
			$paymentDetails = array_merge($data, $paymentDetails);
			$payment->infohash = json_encode($paymentDetails);
			$payment->status = 'complete';
			FOFModel::getTmpInstance('payments','DonorcartModel')->save($payment);
		}
	}

	/*
	 * Generates the HTML to display to a user after returning from the payment gateway.
	 * Will be called whether or not the payment is valid or from this plugin.
	 *
	 * @param boolean &$is_valid Whether or not the payment information in the current request is valid
	 * @param string &$plugin_validated The payment plugin that has already validated this request.
	 *
	 * @return string The HTML to display to the user. If nothing is returned, the regular checkout screen (or thankyou page) will be displayed instead
	 */
	public function onAfterPostback($is_valid, $plugin_validated) {
		return;

		//example code
		if($plugin_validated == $this->getName) {
			$html = '<h3>Payment: '.$this->getName().'</h3>';
			if($is_valid) {
				$html .= '<p>Payment Success</p>';
			} else {
				$html .= '<p>Payment Failed</p>';
			}
			return $html;
		}
	}

	/*
	 * This function is called when an order is completed
	 *
	 * @param DonorcartModelOrders order The order that was completed
	 */
	public function onOrderCompletion($order) {
		return;
	}

	/*
	 * Handles the payment processing fee, if applicable, based on the configuration of the payment plugin
	 * This function should be called from the onSubmitOrder function.
	 *
	 * @param DonorcartModelOrders $order The order we are processing
	 * @param string $payment_name The name of the payment method selected for this order
	 * @param boolean &$order_modified Whether or not the order was modified by this function to include, exclude, or update the processing fee.
	 *
	 * @return boolean True if the order includes a processing fee. False if it does not. Null if this payment method was not selected.
	 */
	protected function _handle_processing_fee($order, $payment_name, &$order_modified = false) {
		if($payment_name != $this->getName()) return;
		$cc_fees_option = $this->params->get('pay_cc_fee',0);
		if($cc_fees_option==0) {
			$pay_cc_fees=0;
		} elseif($cc_fees_option==2) {
			$pay_cc_fees=1;
		} else {
			$pay_cc_fees = JRequest::getInt($payment_name.'_pay_cc_fee',0);
		}
		$current_fee_item = false;
		foreach($order->cart->items as $item_id => $item) {
			if($item->sku==$this->_cc_fees_sku) {
				$current_fee_item = $item_id;
			}
		}
		if($pay_cc_fees) {
			$cc_fees_total = $this->_calc_cc_processing_fee($order);
			if(!$current_fee_item) {
				FOFModel::getAnInstance('carts','DonorcartModel')->addItemToCart($this->_cc_fees_sku, $this->_cc_fees_name, $cc_fees_total, '1', '', '', false, true);
				$order_modified = true;
			} elseif($order->cart->items[$current_fee_item]->price != $cc_fees_total) {
				FOFModel::getAnInstance('carts','DonorcartModel')->updateItemInCart($current_fee_item, null, null, $cc_fees_total, '1', null, null, true);
				$order_modified = true;
			}
		} else {
			if($current_fee_item) {
				FOFModel::getAnInstance('carts','DonorcartModel')->removeItemFromCart($current_fee_item, true);
				$order_modified = true;
			}
		}
		return $pay_cc_fees;
	}


	/*
	 * Calculates the credit card processing fee for this payment plugin.
	 * Only calculates the fee if the payment plugin specifies a
	 *
	 * @param DonorcartModelOrder $order The order to calculate the fees for
	 */
	protected function _calc_cc_processing_fee($order) {
		if(!is_object($order->cart) || empty($order->cart->items)) return 0;
		$cc_fees_amount = $this->params->get('cc_fee_amount',0);
		if(!$cc_fees_amount || !is_numeric($cc_fees_amount)) return 0;
		$cc_fees_type = $this->params->get('cc_fee_type','percent');
		if($cc_fees_type=='fixed') {
			return round($cc_fees_amount,2);
		}
		//We currently only support 'fixed' and 'percent' fees. If it's not fixed, it's a percent.
		$cc_fees_amount = round($cc_fees_amount,2)/100;
		$cart_subtotal = 0;
		foreach($order->cart->items as $item) {
			if($item->sku!=$this->_cc_fees_sku) {
				$cart_subtotal += ($item->qty * $item->price);
			}
		}
		return round($cart_subtotal*$cc_fees_amount,2);
	}

	/*
	 * Returns an array containing the valid recurring options for this payment plugin
	 *
	 * @return array The valid options configured in this payment plugin
	 */
	private function _get_recurring_options() {
		$recurring_options = array();
		if($this->params->get('recur_twoweeks',false)) $recurring_options['2 Weeks'] = '2 Weeks';
		if($this->params->get('recur_weekly',false)) $recurring_options['Weekly'] = 'Weekly';
		if($this->params->get('recur_fourweeks',false)) $recurring_options['4 Weeks'] = '4 Weeks';
		if($this->params->get('recur_monthly',false)) $recurring_options['Monthly'] = 'Monthly';
		if($this->params->get('recur_querterly',false)) $recurring_options['Querterly'] = 'Querterly';
		if($this->params->get('recur_semiannual',false)) $recurring_options['Semi-Annual'] = 'Semi-Annual';
		if($this->params->get('recur_yearly',false)) $recurring_options['Yearly'] = 'Yearly';
		return $recurring_options;
	}

}
