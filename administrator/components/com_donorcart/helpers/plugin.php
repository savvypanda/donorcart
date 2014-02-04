<?php defined('_JEXEC') or die('Restricted Access');

class JPluginDonorcart extends JPlugin {

	/**
	 * A string containing the name of the payment plugin. Must be overwritten in child classes.
	 *
	 * @var    string
	 */
	protected $_name;

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
	 * @return boolean|string False if this payment plugin can't or shouldn't process this order. True for a generic selector.
	 * 						  The HTML for the selection list if the plugin can process this order and you don't just want the name.
	 */
	public function onDisplayPaymentSelector($order, $params) {
		return $this->isActive();
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
		die('You must define the onDisplayPaymentForm function in your plugin.');
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
		if($order->payment_name != $this->getName()) return;
		die('You must define the onConfirmOrder function in your plugin.');

		//example code for this function
		$payment = $order->payment;
		$data = json_decode($payment->infohash);

		$paymentdata_is_saved_correctly = true;
		if(!$paymentdata_is_saved_correctly) return false;

		$html = <<<HEREDOC
<form method="post" action="http://someotherserver.com/postdata/endpoint" id="someotherserverform">
<input type="hidden" name="order_total" value="{$order->order_total}" />
<input type="hidden" name="val1" value="{$data['val1']}" />
<p>You will be redirected to our secure processing server. If your browser does not automatically redirect you, <input type="submit" value="Click Here" /></p>
</form>
<script type="text/javascript">window.setTimeout(function(){document.getElementById("someotherserverform").submit()},2000)</script>
HEREDOC;

		$althtml = <<<HEREDOC
<iframe src="https://secure.someotherserver"></iframe>
HEREDOC;

		return $html or $althtml;
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
		die('You must define the onDisplayPaymentInfo function in your plugin.');
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
}
