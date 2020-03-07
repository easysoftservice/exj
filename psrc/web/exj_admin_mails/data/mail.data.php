<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppMailData
 *
 */
class AppMailData extends ExjObject {
	private $_id_mail;
	
	public $from;
	public $sender;
	protected $email;
	public $subject;
	public $body;
	public $isHTML;
	protected $cc_mail;
	protected $bcc_mail;
	public $attachment;
	private $_title;
	private $_mensaje;
	
	private $_infoCorreo;
	
	public function __construct($idMail){
		$this->loadMail($idMail);
	}
	
	public function loadMail($idMail){
		$this->_id_mail = $idMail;
    	
    	$this->_infoCorreo = AppAdminMailsData::getInfoCorreo($idMail);
    	if (!$this->_infoCorreo) {
    		return false;
    	}

		$this->from = $this->_infoCorreo->from_email;
		$this->sender = $this->_infoCorreo->sender_mail;
		$this->subject = $this->_infoCorreo->subject_mail;
		$this->email = $this->_infoCorreo->to_email;
		$this->cc_mail = $this->_infoCorreo->cc_mail;
		$this->bcc_mail = $this->_infoCorreo->bcc_mail;
    	$this->isHTML = 1;
		
		$this->_title = $this->_infoCorreo->title_mail;
		$this->_mensaje = $this->_infoCorreo->body_mail;
		
		
		$this->attachment = null;
		if ($this->_infoCorreo->attachment && count($this->_infoCorreo->attachment) > 0) {
			$this->attachment = array();
			
			foreach ($this->_infoCorreo->attachment as $itemAttach) {
				/*
				$itemAttach->file_path
				$itemAttach->file_name_custom
				*/
				
				$this->attachment[] = $itemAttach->file_path;
			}
		}
		
    	return $this->_buildMail();
	}
	
	public function getEmailsTo($onlyItemEmails = false){
		return self::TransformEmailsToItems($this->email, $onlyItemEmails);
	}
	public function getEmailsCC($onlyItemEmails = true){
		return self::TransformEmailsToItems($this->cc_mail, $onlyItemEmails);
	}
	public function getEmailsBCC($onlyItemEmails = true){
		return self::TransformEmailsToItems($this->bcc_mail, $onlyItemEmails);
	}

	
	static function TransformEmailsToItems($strEmails, $onlyItemEmails = false, $dataEmptyDefault=null){
		$emailsData = array();
		
		if ($strEmails) {
			$strEmails = trim($strEmails);
		}
		
		if (!$strEmails) {
			return $dataEmptyDefault;
		}
		
		$emails = explode(",", $strEmails);
		
		foreach ($emails as $strEmail) {
			$strEmail = trim($strEmail);
			
			$item = new stdClass();
			$item->email = $strEmail;
			$item->name = '';
			
			// ver si tiene la esctructura: "Byron Córdova(bvcordova@hotmail.com)"
			$posEmail = strpos($strEmail, '(');
			if ($posEmail !== false) {
				$item->name = trim(substr($strEmail, 0, $posEmail));
				$item->email = trim(substr($strEmail, $posEmail+1));
				$item->email = trim($item->email, ')');
			}
			
			if ($onlyItemEmails) {
				$emailsData[] = $item->email;
				continue;
			}
			
			$emailsData[] = $item;
		}
		
		if (count($emailsData) == 0) {
			return $dataEmptyDefault;
		}
		
		return $emailsData;
	}
	
	public function getTplMail(){
		if (!$this->_infoCorreo) {
			return '';
		}
		
		return $this->_infoCorreo->tpl_mail;
	}
	
	public function getInfoCorreo(){
		return $this->_infoCorreo;
	}
	
	public function isValid(){
    	if (!$this->_infoCorreo) {
    		return false;
    	}
		
    	return (!Exj::GetError()->haveError());
	}
	
	public function getTitulo(){
		return $this->_title;
	}
	
	private function _buildMail(){
    	if (!$this->_infoCorreo) {
    		return false;
    	}
    	
    	global $exj;
    	jimport('joomla.mail.helper');
		
		$msgError = null;
		$this->body = AppMailVarHelper::Render($this->getTplMail(), $msgError, $this->_mensaje, $this->_title, $this->email);
		if ($msgError) {
			Exj::SetErrorValidating($msgError);
			return false;
		}

		// Clean the email data
		$this->subject = JMailHelper::cleanSubject($this->subject);
		$this->body	 = JMailHelper::cleanBody($this->body);
		$this->sender	 = JMailHelper::cleanAddress($this->sender);
		
		if (count($this->attachment) > 0) {
			foreach ($this->attachment as &$attach) {
				$attach = str_replace('\\', "/", $attach);
			}
		}

		return true;
	}
	
}

?>