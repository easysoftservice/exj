<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSendMailEditableModel
 */
class AppSendMailEditableModel extends ExjEditableModel {
	public $id_mdb_sol;
	public $ApplicationName;
	public $type;
	public $source;
	public $id_mdb_expmsg;
	public $desc_sol_comun;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_tmongodb_solutions';
		$fieldKey = 'id_mdb_sol';
		
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('ApplicationName', 'App');
		$this->registerFieldString('type', 'Tipo');
		$this->registerFieldString('source', 'Fuente', false, false);
		$this->registerFieldInt('id_mdb_expmsg', 'ID de mensajes expresiones');
		$this->registerFieldString('desc_sol_comun', 'Descripción de la Solución Común', false, false);
	}

	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
	//	echo "<br/>".__METHOD__;
    	$this->registerControlUI(ExjUI::NewTextField('ApplicationName', 'App'));
    	$this->registerControlUI(ExjUI::NewTextField('type', 'Tipo'));
    	
    	$id_persona = ExjRequest::GetParamInt('id_persona', 0);
    	if ($id_persona === 0) {
    		$this->addBrokenRuler("No se ha enviado el parámetro id!");
    		return false;
    	}
    	
//    	ExjTransferCharacters::decodeUTF8ToISO($id_persona);
    	    	
    	
    	global $exj;
    	
    	// $exj->includeDataCustom('sys_users');
    	
    	$infoPerson = AppSysUsersData::getInfoPerson($id_persona);

    	if (!$this->_sendMail($infoPerson)) {
    		return false;
    	}

    	
    	$this->registerControlUI(ExjUI::NewTextField('source', 'Fuente'));
    	
    	
    	$this->registerControlUI(ExjUI::NewTextArea('desc_sol_comun', 'Descripción', '99%', 210));
	}
	
	private function _sendMail($infoPerson){
    	if (!$infoPerson) {
    		$this->addBrokenRuler("No se ha encontró ID");
    		return false;
    	}
    	if ($infoPerson->block) {
    		$this->addBrokenRuler("El usuario está bloqueado");
    		return false;
    	}
    	
    	jimport('joomla.mail.helper');
    	
    //	echo "infoPerson->email: $infoPerson->email";
    	
    	// $infoPerson->email
    	// $infoPerson->nombres_persona
    	// $infoPerson->apellidos_persona
    	// $infoPerson->nom_empresa
    	
//		print_r(JError::raiseWarning(0, "test de error"));
		
		

		$sender = "$infoPerson->nombres_persona $infoPerson->apellidos_persona";
		$subject = "PRUEBAS PHP ADJUNTANDO 2 ARCHIVOS";
		$from = 'admin@x.com';
		$email = $infoPerson->email;
		
		
		// Build the message to send
		// $msg	= JText :: _('EMAIL_MSG');
		// $body	= sprintf( $msg, $SiteName, $sender, $from, $link);
		$msg = "<h3>Pruebas de envío de correo</h3> desde code <b>php</b> con archivo adjunto";
		$body = $msg;
		

		// Clean the email data
		$subject = JMailHelper::cleanSubject($subject);
		$body	 = JMailHelper::cleanBody($body);
		$sender	 = JMailHelper::cleanAddress($sender);
		$isHTML = 1;
		
		$attachment = array();
		$attachment[] = "D:\Users\Byron Córdova\Documents\IMG_23012013_161133.png";
	//	$attachment[] = "D:\Users\Byron Córdova\Documents\Ciudades.pdf";
		
		foreach ($attachment as &$attach) {
			$attach = str_replace('\\', "/", $attach);
		}
		// $attachment = array();
		
//		return true;

		// Send the email
		if (JUtility::sendMail($from, $sender, $email, $subject, $body, $isHTML, null, null, $attachment) !== true) {
			// JError::raiseNotice( 500, JText:: _ ('EMAIL_NOT_SENT' ));
			// return $this->mailto();
			$this->addBrokenRuler(JText::_('EMAIL_NOT_SENT'));
			return false;
		}

		return true;
	}
	
	/**
	 * overwrited. Después que se inicia el modelo editable
	 *
	 */
	protected function afterInitEditableModel(){
	//	echo "<br/>".__METHOD__;
		if (!$this->isAddControlesUI()) {
			return ;
		}
		
		
		if ($this->haveBrokenRules()) {
			return ;
		}
		
		/*
    	global $exj;
    	// $exj->includeDataCustom('sys_users');
    	*/
    	
    	$this->setValueId(0);
	}
	
	
	/**
	 * overwrited. Se llama cuando ya se hayan cargado el modelo.
	 * Registro de controles UI
	 *
	 */
	public function afterLoadRegisterControlsUI(){
		
	}

	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	// $this->applyValidationDateRange('start_event', 'end_event', 'editable_cierre');
    	// $this->applyValidationTextMemo('desc_sol_comun', 3000, 0);
    	$this->applyValidationClear('desc_sol_comun', 3000, 0);
	}
	
	/**
	 * overwrited. save
	 *
	 */
	public function save(){
		
		if ($this->isNew()) {
			if (!$this->desc_sol_comun) {
				$this->addBrokenRuler("Debe ingresar la descripción de la solución");
				return false;
			}
		}
		else {
			if (!$this->desc_sol_comun) {
				$this->destroy($this->id);
				return true;
			}
			
		}

		try	{
			ExjDBTrx::Start();
			
			if (!parent::save()) {
				return false;
			}
			
			ExjDBTrx::Commit();
		}
		catch (Exception $e){
			$this->addBrokenRuler($e->getMessage());
			ExjDBTrx::Rollback();
			return false;
		}
		
		
		return true;
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
		
    	return true;
    }
    
}

?>