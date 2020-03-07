<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppUsrPerfilEditableModel
 */
/* class AppUsrPerfilEditableModel extends AppSysUserEditableModel { */
class AppUsrPerfilEditableModel extends ExjEditableModel {
	public $id_persona;
	public $nro_doc_persona;
	public $id_doc_tipo;
	public $noms_apes;
	public $dir_person;
	public $tlf_persona;
	public $id_sit;
	
	public $user_email;
	public $user_pwd_current;
	public $user_pwd1;
	public $user_pwd2;
	
	public $apellidos_persona;
	public $nombres_persona;
	public $email_person;
	
	// 
	
	/**
	 * overwrited. Inicio del Modelo base
	 *
	 */
	protected function initModel(){
		$this->registerTable('jos_app_personas', 'id_persona');
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	
	public function registerFields(){
		$this->registerFieldInt('id_persona', 'Id Persona');
		$this->registerFieldInt('id_doc_tipo', 'Id Tipo Documento');
		$this->registerFieldString('nro_doc_persona', 'Núm Doc.');
		$this->registerFieldInt('id_sit', 'Id Ciudad', true, true);
		$this->registerFieldString('noms_apes', 'Nombres y Apellidos / Razón Social');
		$this->registerFieldStringNullable('dir_person', 'Dirección');
		$this->registerFieldStringNullable('tlf_persona', 'Teléfono');
		
		$this->registerFieldString('user_email', 'Correo Electrónico');
		$this->registerFieldStringNullable('user_pwd_current', 'Contraseña Actual');
		$this->registerFieldStringNullable('user_pwd1', 'Contraseña Nueva');
		$this->registerFieldStringNullable('user_pwd2', 'Repita su Nueva Contraseña');
		
		$this->registerFieldStringNullable('nombres_persona', 'Nombres');
		$this->registerFieldString('apellidos_persona', 'Nombres y Apellidos');
		$this->registerFieldString('email_person', 'Correo Electrónico');
		
		parent::registerFields();
		
		// $this->registerFieldInt('id_empresa', 'Id Empresa');		
	}
	
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	
	public function registerControlsUI(){
    	// $this->registerControlUI(...);
    	
    	$infoUser = ExjUser::GetUserSys();
    	$this->_loadControlsUI($infoUser);
    	
    	// parent::registerControlsUI();
	}
	
	private function _loadControlsUI(ExjHelperInfoUser $infoUser){
	//	print_r($infoUser);
		
	//	$this->setValueId($infoUser->id_persona);
		// $this->load($infoUser->id_persona);
		
		$rowUserPerfil = null;
		if (AppUsrPerfilData::LoadUserPerfil($rowUserPerfil, $infoUser->id_sys_user)) {
			if (!$rowUserPerfil) {
				$this->addBrokenRuler("No se encontraron datos del perfil!");
				return false;
			}
		}
		else {
			$this->addBrokenRuler("Ocurrio un error interno, cargando datos del perfil");
			return false;
		}
		
		$this->registerControlUI(ExjUI::NewHidden('id_persona'));
		$this->registerControlUI(ExjUI::NewTextField('nro_doc_persona', $rowUserPerfil->name_doc, '111px'));
		$this->registerControlUI(ExjUI::NewTextField('noms_apes'));
    	$this->registerControlUI(ExjUI::NewTextArea('dir_person'));
    	$this->registerControlUI(ExjUI::NewTextField('user_email'));
    	$this->registerControlUI(ExjUI::NewTextField('tlf_persona'));
    	
    	// Exj::IncludeClass('AppLocUIHelper', 'com_app_locs');
    	$this->registerControlUI(
            AppLocUIHelper::NewComboPagingCiudades('id_sit', 'Ciudad', 300)
        );
    	
    	$this->registerControlUI(
            ExjUI::NewPasswordField('user_pwd_current', '', '120px')
        );
    	$this->registerControlUI(ExjUI::NewPasswordField('user_pwd1', '', '120px'));
    	$this->registerControlUI(ExjUI::NewPasswordField('user_pwd2', '', '120px'));
    	
    //	print_r($rowUserPerfil);
		$this->loadData($rowUserPerfil);
	}
	
	public function load($id=null, $noFoundAddError = true) {
		
		return true;
	}
	
	public function registerRules(){
		parent::registerRules();
		
    //	$this->applyValidationNumDoc('nro_doc_persona', 'id_doc_tipo', 'edit_persona');
    	$this->applyValidationTextNameExtendido('noms_apes', false, 66, 3);
    	$this->applyValidationTextMemo('dir_person');
    	$this->applyValidationTextFono('tlf_persona', 48);
    	$this->applyValidationTextCorreo('user_email', 80);
    	$this->applyValidationClear('user_pwd1', 16, 4);
    	$this->applyValidationClear('user_pwd2', 16, 4);
	}
	
	/**
     * Valida control UI cuando sea el acceso edit, o no sea de solo lectura
     *
     * @param string $name
     * @param object $component Pasado por referencia
     * @param bool $isReadOnly
     * @param bool $isHidden
     */
    protected function validateControlUIInEdit($name, &$component, $isReadOnly, $isHidden){
    	switch ($name) {
    		case 'nro_doc_persona':
    			$component->readOnly = true;
    			$component->disabled = true;
    		break;
    	}
    	
    }

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	$this->addBrokenRuler("No se permite eliminar desde edición del perfil");
    	return false;
    }
    
    private $_nombresApellidos = null;
    private $_user_email = null;
    private $_user_pwd_current = null;
    private $_user_pwd1 = null;
    private $_user_pwd2 = null;
    
    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	if ($this->isNew()) {
    		$this->addBrokenRuler("No se permite crear desde edición del perfil");
    		return false;
    	}
    	
    	$idPersonaActual = ExjUser::GetIdPersona();
    	
    	if ($this->id != $idPersonaActual) {
    		$this->addBrokenRuler("Error persona no reconocida!");
    		return false;
    	}
    	
    	if ($this->_readValueReset('noms_apes', $this->_nombresApellidos)) {
    		$this->apellidos_persona = $this->_nombresApellidos;
    		$this->nombres_persona = null;
    	}
    	
    	if ($this->_readValueReset('user_email', $this->_user_email)) {
    		$this->_user_email = trim($this->_user_email);
    		if (!$this->_user_email) {
	    		$this->addBrokenRuler("Correo electrónico inválido");
	    		return false;
    		}
    		
    		$this->email_person = $this->_user_email;
    		if (!$this->_validateEmailUser()) {
    			return false;
    		}
    	}
    	
    	$this->_readValueReset('user_pwd_current', $this->_user_pwd_current);
    	$this->_readValueReset('user_pwd1', $this->_user_pwd1);
    	$this->_readValueReset('user_pwd2', $this->_user_pwd2);
    	
    	if ($this->_user_pwd_current || $this->_user_pwd1 || $this->_user_pwd2) {
    		if (!$this->_validatePwdCurrent()) {
    			return false;
    		}
    		
    		if (!$this->_user_pwd1) {
	    		$this->addBrokenRuler("Ingrese la Contraseña Nueva");
	    		return false;
    		}
    		
    		if (strlen($this->_user_pwd1) < 4) {
				$this->addBrokenRuler("La Contraseña Nueva debe tener 4 o más caracteres.<br/>La Contraseña puede contener combinación de letras, números o símbolos");
	    		return false;
    		}
    		
    		if (!$this->_user_pwd2) {
	    		$this->addBrokenRuler("Repita su Nueva Contraseña");
	    		return false;
    		}
    		
    		if ($this->_user_pwd1 == $this->_user_pwd_current) {
	    		$this->addBrokenRuler(
                    "La Nueva Contraseña es la misma que la Contraseña Actual.<br/>Se desea cambiar su contraseña ingrese una nueva contraseña diferente a la actual, sino elimine las contraseñas ingresadas.".
                    $this->_user_pwd_current
                );
	    		return false;
    		}
    		
    		if ($this->_user_pwd1 != $this->_user_pwd2) {
	    		$this->addBrokenRuler("La Contraseña Nueva no coincide, los últimos casilleros debe tener la misma contraseña.<br/>Revize por favor...");
	    		return false;
    		}
    		
    		//$this->addBrokenRuler("En construcción...");
    		//return false;
    	}
    	
    	return true;
    }
    
    private function _validateEmailUser(){
    	if (!$this->_user_email) {
    		return true; // nada que validar
    	}
    	
    	// Exj::IncludeClass('AppUsrPerfilData', 'exj_usr_perfil');
    	
    	$infoUserReg = null;
    	if (!AppUsrPerfilData::LoadRowUserEMailRegistered($infoUserReg, $this->_user_email)) {
    		$this->addBrokenRuler("Ocurrio un error al verificar correo disponible");
    		return false;
    	}
    	
    	if ($infoUserReg && is_object($infoUserReg)) {
    		$this->addBrokenRuler("Cuenta de correo electrónico: <b>$this->_user_email</b> ya está registrada por: $infoUserReg->name ($infoUserReg->username)");
    		return false;
    	}
    	
    	return true;
    }
    
    private function _validatePwdCurrent(){
   		if (!$this->_user_pwd_current) {
    		$this->addBrokenRuler("Ingrese la Contraseña Actual");
    		return false;
   		}
    	
		$jUser = JFactory::getUser();
		// print_r($jUser);
		
		$parts	= explode(':', $jUser->password);
		$crypt	= $parts[0];
		$salt	= @$parts[1];
		
		
		$testcrypt = JUserHelper::getCryptedPassword($this->_user_pwd_current, $salt);
		
		if ($crypt != $testcrypt) {
    		$this->addBrokenRuler("La <b>Contraseña Actual</b> ingresada no es correcta");
    		return false;
		}

		return true;
    }
    
    private function _readValueReset($nameField, &$value){
    	if ($this->isSettedField($nameField)) {
    		$value = $this->getValueField($nameField, null);
    		$this->resetField($nameField);
    		return true;
    	}
    	else {
    		$value = null;
    	}
    	
    	return false;
    }
    
    /**
     * overwrited. Despues de Guardar
     *
     * @param object $responseData
     * @return bool. si se retorna false y se activa transaccion al guardar se cancelan los datos guardado
     */
    protected function afterSave(&$responseData){
    	if ($this->_user_email || $this->_user_pwd1 || $this->_nombresApellidos) {
    		$jUser =& JFactory::getUser();
    		
    		if (!$this->_saveUser($jUser)) {
    			return false;
    		}
    	}
    	
    	return true;
    }
    
    private function _saveUser(JUser &$jUser){
    	$dataUser = array();
    	
    	if ($this->_user_email) {
    		$dataUser['email'] = $this->_user_email;
    	}
    	if ($this->_user_pwd1) {
    		$dataUser['password'] = $this->_user_pwd1;
    		$dataUser['password2'] = $this->_user_pwd1;
    	}
    	
    	if (!ExjUser::IsRolSuperAdmin()) {
    		if ($this->_nombresApellidos) {
    			$dataUser['name'] = $this->_nombresApellidos;
    		}
    	}
    	
    	if (empty($dataUser)) {
    		return true; // no hay que guardar
    	}
    	
    //	print_r($dataUser);
    	//$this->addBrokenRuler("test save user joomla");
    	//return false;
        
        ExjTransferCharacters::encodeISOToUTF8($dataUser);
    	
    	$jUser->bind($dataUser);
    	
    	if ($jUser->save(true) === false) {
    		$msgError = $jUser->getError();
    		if (!$msgError) {
    			$msgError = "Error inesperado al guardar datos del usuario!";
    		}
    		
    		$this->addBrokenRuler($msgError);
    		
    		$idUserCurrent = $jUser->id;
	    	// se recuperan datos del usuario
	    	$jUser->load($idUserCurrent);
		    	
    		return false;
    	}
    	
    	return true;
    }
	
}

?>