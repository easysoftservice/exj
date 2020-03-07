<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailEditableModel
 */
class AppMailEditableModel extends ExjEditableModel {
	const ESTADO_ENVIADO = 'ENVIADO';
	const ESTADO_FALLIDO = 'FALLIDO';
	const ESTADO_PENDIENTE = 'PENDIENTE';

	
	/**
	 * Campo principal del correo
	 *
	 * @var int
	 */
	public $id_mail;
	protected $to_email;
	public $body_mail;
	public $is_html=1;
	public $id_mail_tpl;
	
	/**
	 * No es requerido
	 *
	 * @var string
	 */
	public $from_email;
	public $sender_mail;
	public $subject_mail;
	protected $cc_mail=null;
	protected $bcc_mail=null;
	public $state_mail;
	
	private $_attachFiles = null;
	
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_app_mails';
		$fieldKey = 'id_mail';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('to_email', 'Para');
		$this->registerFieldString('body_mail', 'Mensaje');
		$this->registerFieldInt('is_html', 'es html', false, false, true);
		$this->registerFieldInt('id_mail_tpl', 'Id Plantilla de correo');
		
		
		$this->registerFieldString('from_email', 'Correo origen');
		$this->registerFieldString('sender_mail', 'Origen');
		$this->registerFieldString('subject_mail', 'Asunto');
		$this->registerFieldString('cc_mail', 'Copia', true, false);
		$this->registerFieldString('bcc_mail', 'Copia oculta', true, false);
		$this->registerFieldString('state_mail', 'Estado');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('to_email', 'Para'));
    	$this->registerControlUI(ExjUI::NewTextField('cc_mail', 'Copia'));
    	$this->registerControlUI(ExjUI::NewTextField('bcc_mail', 'Copia oculta'));
    	$this->registerControlUI(ExjUI::NewTextField('subject_mail', 'Asunto'));
    	$this->registerControlUI(ExjUI::NewTextArea('body_mail', 'Mensaje'));
    	
    	$this->registerControlUI(AppAdminMailUIHelper::NewComboSimplePlantillas());
	}
	
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextCorreo('to_email', 90);
    	$this->applyValidationTextCorreo('cc_mail', 90);
    	$this->applyValidationTextCorreo('bcc_mail', 90);
    	
    	$this->applyValidationClear('body_mail', 600);
	}	

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}
    	
    	/*
    	if (!$this->canDestroyRelationTable($id, 'jos_txxx', 'Xxxx')) {
    		return false;
    	}
    	*/
    	
    	return true;
    }

    /**
     * overwrited. Inicio del Guardardo
     *
     * @return bool
     */
    protected function initSave(){
    	global $mainframe;
    	
    	if ($this->isNew()) {
    		
	    	if (!$this->isSettedField('state_mail')) {
	    		$this->state_mail = self::ESTADO_PENDIENTE;
	    	}
	    	if (!$this->isSettedField('is_html')) {
	    		$this->is_html = 1;
	    	}

			$siteName 	= $mainframe->getCfg('sitename');
			$mailFrom 	= $mainframe->getCfg('mailfrom');
		//	$fromName 	= $mainframe->getCfg('fromname');
	    	
			// el que está configurado
			$this->from_email = $mailFrom;
			
			// correo del usuario actual
			// $this->sender_mail = ExjUser::GetEmailSys();
			$this->sender_mail = $siteName;
    	}
    	
    	// comprobación de duplicados
    	/*
    	if (!$this->canSaveCodeUnique('to_email', 'Nombre de Correo', $whereExtra)) {
    		return false;
    	}
    	*/
    	
    	return true;
    }
    
    
    
	public function fixEstadoEnviado(){
		$this->state_mail = self::ESTADO_ENVIADO;
	}
	public function fixEstadoFallido(){
		$this->state_mail = self::ESTADO_FALLIDO;
	}
    
	public function addEmailTo($email, $name=''){
		return $this->_addEmail('to_email', $email, $name);
	}
	public function addEmailCC($email, $name=''){
		return $this->_addEmail('cc_mail', $email, $name);
	}
	public function addEmailBCC($email, $name=''){
		return $this->_addEmail('bcc_mail', $email, $name);
	}
	
	public function _addEmail($nameField, $email, $name=''){
		$email = trim($email);
		$name = trim($name);
		if (!$email) {
			return false;
		}
		
		if (!$this->$nameField || !self::IsSettedValue($this->$nameField)) {
			$this->$nameField = '';
		}
		if ($this->$nameField) {
			$this->$nameField .= ',';
		}
		
		if ($name) {
			$this->$nameField .= "$name($email)";
		}
		else {
			$this->$nameField .= $email;
		}
		
		return true;
	}
	
	
	public function addAttachFile($filePath, $fileNameCustom=null){
		if (!$filePath) {
			$this->addBrokenRuler("No se ha indicado el path del archivo a adjuntar");
			return false;
		}
		$filePath = trim($filePath);
		
		if (!file_exists($filePath)) {
			$this->addBrokenRuler("No existe el archivo:<br/>$filePath");
			return false;
		}
		
		if (!$fileNameCustom) {
			$path_parts = pathinfo($filePath);
			$extensionFile = $path_parts['extension'];
			$fileNameCustom = basename($filePath, '.'.$extensionFile);
		}
		
		$attachFile = new stdClass();
		$attachFile->filePath = $filePath;
		$attachFile->fileNameCustom = $fileNameCustom;
		
		if (!$this->_attachFiles) {
			$this->_attachFiles = array();
		}
		
		$this->_attachFiles[] = $attachFile;
		
		return true;
	}
	
    /**
     * overwrited. Despues de Guardar
     *
     * @param object $responseData
     * @return bool. si se retorna false y se activa transaccion al guardar se cancelan los datos guardado
     */
    protected function afterSave(&$responseData){
    	if (!$this->_attachFiles) {
    		return true;
    	}
    	
    	foreach ($this->_attachFiles as $attachFile) {
    		$mailAttach = new AppMailAttachEditableModel(false, $this->getResponse());
    		$mailAttach->setValueId(0);
    		
    		$mailAttach->id_mail = $this->id_mail;
    		$mailAttach->file_path = $attachFile->filePath;
    		$mailAttach->file_name_custom = $attachFile->fileNameCustom;
    		
    		$mailAttach->save();
    		if ($mailAttach->haveBrokenRules()) {
    			$this->addBrokenRuler($mailAttach->getBrokenRules());
    			break;
    		}
    	}
    	
    	return (!$this->haveBrokenRules());
    }
	
	
}

/**
 * @class AppMailAttachEditableModel
 */
class AppMailAttachEditableModel extends ExjEditableModel {
	public $id_mail_attach;
	public $id_mail;
	public $file_path;
	public $file_name_custom;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_app_mails_attachs';
		$fieldKey = 'id_mail_attach';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_mail', 'Id de correo');
		
		$this->registerFieldString('file_path', 'Path del archivo');
		$this->registerFieldString('file_name_custom', 'Nombre del archivo personalizado', true, false);
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationClear('file_path', 600);
	}	
	
}

?>