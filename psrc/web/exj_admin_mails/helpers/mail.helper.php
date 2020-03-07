<?php

/**
 * Helper de correos
 *
 */
class AppMailHelper {
    

	static function send($idMail, $returnData = false) {
    	$mailData = new AppMailData($idMail);
		if (!$mailData->isValid()) {
			return false;
		}
		
		if ($returnData) {
			return $mailData->toObject();
		}
		
	//	$mailData->getEmailsTo()

		// Send the email
		/*
		if (JUtility::sendMail($mailData->from, $mailData->sender, $mailData->email, $mailData->subject, $mailData->body, $mailData->isHTML, $mailData->cc_mail, $mailData->bcc_mail, $mailData->attachment) !== true) {
			Exj::SetErrorValidating(JText::_('EMAIL_NOT_SENT'));
			return false;
		}
		*/

		if (self::sendMail($mailData) !== true) {
			print_r($GLOBALS['_JERROR_STACK']);
			Exj::SetErrorValidating('Correo no se pudo enviar');
			return false;
		}
		
		$mailEditable = new AppMailEditableModel(false);
		$mailEditable->setValueId($idMail);
		$mailEditable->fixEstadoEnviado();
		$mailEditable->save();
    	
    	return true;
    }    
    
 	static function sendMail(AppMailData $mailData, $replyto=null, $replytoname=null) {
		$from = $mailData->from;
		$fromname = $mailData->sender;
		$recipient = $mailData->getEmailsTo();
		$cc = $mailData->getEmailsCC();
		$bcc = $mailData->getEmailsBCC();
		
		$subject = $mailData->subject;
		$body = $mailData->body;
		$mode = $mailData->isHTML;
		$attachment = $mailData->attachment;
		
	 	// Get a JMail instance
		$mail =& JFactory::getMailer();
		
		/*
		$x = new JMail();
		$x->addRecipient();
		$x->AddCC();
		$x->AddBCC();
		$x->AddAddress();
		$x->Send();
		*/
		
		// print_r($recipient);
		// return false;
		
		foreach ($recipient as $item) {
			$item->email = JMailHelper::cleanLine($item->email);
			$mail->AddAddress($item->email, $item->name);
		}

		$mail->setSender(array($from, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);

		// Are we sending the email as HTML?
		if ($mode) {
			$mail->IsHTML(true);
		}

	//	$mail->addRecipient($recipient);
		
		$mail->addCC($cc);
		$mail->addBCC($bcc);
		$mail->addAttachment($attachment);

		// Take care of reply email addresses
		if( is_array( $replyto ) ) {
			$numReplyTo = count($replyto);
			for ( $i=0; $i < $numReplyTo; $i++){
				$mail->addReplyTo( array($replyto[$i], $replytoname[$i]) );
			}
		} elseif( isset( $replyto ) ) {
			$mail->addReplyTo( array( $replyto, $replytoname ) );
		}

		/*
		print_r($mail);
		return false;
		*/

		return $mail->Send();
	}    
}

?>