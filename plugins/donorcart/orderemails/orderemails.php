<?php defined('_JEXEC') or die('Restricted Access');

class plgDonorcartOrderemails extends JPlugin {

	public function onOrderCompletion($order) {
		$send_admin_email = $this->params->get('send_confirmation_email_to_admin',1);
		$admin_emails = $this->params->get('admin_emails','');
		$admin_email_subject =  $this->params->get('admin_email_subject','');
		$admin_email_ishtml = $this->params->get('admin_email_ishtml',false);
		if($send_admin_email && $admin_emails && $admin_email_template) {
			$admin_email_text = $this->_prepare_email_template('adminemail', $order, $admin_email_ishtml);
			$this->_send_mail($admin_emails, $admin_email_subject, $admin_email_text, $admin_email_ishtml);
		}

		$send_user_email = $this->params->get('send_confirmation_email_to_user','');
		$user_email_subject =  $this->params->get('user_email_subject','');
		$user_email_ishtml = $this->params->get('user_email_ishtml',false);
		if($send_user_email && $user_email_template && $order->email) {
			$user_email_text = $this->_prepare_email_template('useremail', $order, false);
			$this->_send_mail($order->email, $user_email_subject, $user_email_text, $user_email_ishtml);
		}
	}

	private function _prepare_email_template($template, $order, $is_html) {
		$contents = '';
		$path = JPluginHelper::getLayoutPath('donorcart', 'orderemails', $template);
		if(file_exists($path)) {
			ob_start();
			include $path;
			$contents = ob_get_clean();
		}
		return $contents;
	}

	private function _send_mail($addresses, $subject, $text, $is_html) {
		$mailer = JFactory::getMailer();

		//set the sender information
		$config = JFactory::getConfig();
		$sender = array($config->getValue('config.mailfrom'),$config->getValue('config.fromname'));
		$mailer->setSender($sender);

		//set the recipients
		$numaddresses = 0;
		if(strpos($addresses,',')!==false || strpos($addresses,';')!==false) {
			$addresses = explode(';',str_replace(',',';',$addresses));
			foreach($addresses as $address) {
				$address = trim($address);
				if(!empty($address)) {
					$mailer->addRecipient($address);
					$numaddresses++;
				}
			}
		} else {
			$address = trim($addresses);
			if(!empty($address)) {
				$mailer->addRecipient($address);
				$numaddresses++;
			}
		}
		if(!$numaddresses) return false;

		$mailer->setSubject($subject);
		if($is_html) {
			$mailer->isHTML(true);
			$mailer->Encoding = 'base64';
		}
		$mailer->setBody($text);

		return $mailer->Send();
	}
}
