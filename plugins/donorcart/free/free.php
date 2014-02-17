<?php defined('_JEXEC') or die('Restricted Access');
require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_donorcart'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'plugin.php');

class plgDonorcartFree extends JPluginDonorcart {
	protected $_name = 'free';

	public function onSubmitOrder($order, $params, $payment_name) {
		if(!empty($payment_name) && $payment_name != $this->getName()) return;
		return array(
			'payment_type' => 'free',
			'infohash' => ''
		);
	}

	public function onConfirmOrder($order, $params, $is_valid) {
		if($order->payment_name != $this->getName()) return;
		return true;
	}

	public function onDisplayPaymentForm($order, $params) {
		return '<div><p><strong>Payment</strong>: This is a free payment. You do not have to enter any information for this.</p>';
	}

	public function onDisplayPaymentInfo($order, $params, $payment_name) {
		if($payment_name==$this->getName() && $order->payment_id) {
			return '<div><p>Payment: <strong>FREE!</strong></p></div>';
		}
	}
}