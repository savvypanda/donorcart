<?php defined('_JEXEC') or die('Restricted Access');

class plgDonorcartFree extends JPlugin {

	public function onBeforePayment($order, $plugin_validated) {
		if($plugin_validated) return false;
		$plugin_validated = 'free';
		return true;
	}
	public function onPayment($order, $is_valid, $plugin_validated) {
		if($is_valid && $plugin_validated=='free') {
			$paymentmodel = FOFModel::getTmpInstance('payment','DonorcartModel');
			$data = array(
				'payment_type' => 'free',
				'infohash' => ''
			);
			$user = JFactory::getUser();
			if($user->id) {
				$data['user_id'] = $user->id;
			}
			$paymentmodel->save($data);
			return $paymentmodel->getId();
		}
	}

	public function onDisplayPaymentForm($order) {
		return '<div><p><strong>Payment</strong>: This is a free payment. You do not have to enter any information for this.</p>';
	}

	public function onDisplayPaymentInfo($order) {
		if($order->payment_id && is_object($order->payment) && $order->payment->payment_type=='free') {
			return '<div><p>Payment: <strong>FREE!</strong></p></div>';
		}
	}
}