<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUserEditableModel
 */
class AppRolUserEditableModel extends ExjEditableModel {
	public $id_sys_user;
	public $id_empresa;
	public $id_persona;
	public $id_user;
	public $id_sys_lang;
	public $sys_type_theme;
	public $enable_debug;

	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_sys_users';
		$fieldKey = 'id_sys_user';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		if (ExjUser::IsRolSuperAdmin()) {
			$this->registerFieldInt('id_empresa', 'Id Empresa');
		}
		else{
			$this->registerFieldIntNullable('id_empresa', 'Id Empresa');
		}
		
		$this->registerFieldInt('id_persona', 'Id Persona');
		$this->registerFieldInt('id_user', 'Id User Joomla');
		$this->registerFieldInt('id_sys_lang', 'Id Languaje', false, false);
		$this->registerFieldInt('enable_debug', 'Enable Debug', false, false, true);
		$this->registerFieldString('sys_type_theme', 'Theme', false, false);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		/* id_empresa */
		$cmbEmpresas = $this->_comboEmpresas();
		if (!ExjUser::IsRolSuperAdmin()) {
			$cmbEmpresas->hidden = true;
			$cmbEmpresas->value = ExjUser::GetIdEmpresa();
		}
		
    	$this->registerControlUI($cmbEmpresas);
    //	$this->registerControlUI($this->_comboUsuarios());
    
    //	$this->registerControlUI($this->_radioGroupEnableDebug());
    	$this->registerControlUI($this->_uiPersona());
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	/*
	public function registerRules(){
		
	}
	*/
	
	private function _uiPersona(){
		$personaEditableModel = new AppPersonaEditableModel();
		
		$cmpCustom = ExjUI::NewCmpUI('id_persona', $personaEditableModel->to_ui());
		return $cmpCustom;
	}
	
	/*
	private function _comboPersonas(){
    	return AppPersonaUIHelper::newPersonasComboSimple();
	}
	*/
	
		
	private function _comboEmpresas(){
    	return AppLocEmpresasUIHelper::NewComboSimpleEmpresas();
	}
	
	public function removeOfficeAssignation($id_empresa=null){
		if (!$id_empresa) {
			$id_empresa = ExjUser::GetIdEmpresa();
		}
		
		if ($this->isEmptyField('id_sys_user')) {
			$this->addBrokenRuler("No se fijó el id del usuario del sistema!");
			return false;
		}
		
		$dataRolUser = null;
		$this->loadToObject($dataRolUser);
		if (!$dataRolUser) {
			$this->addBrokenRuler("No se cargaron datos del usuario del sistema!");
		}
		if ($this->haveBrokenRules()) {
			return false;
		}
		
	///	print_r($this->toObject());
	//	print_r($dataRolUser);
	
		global $exj;
		// $exj->includeModelEditable('rol_user_ofc', 'exj_rol_users');
		$id_sys_user = $this->id_sys_user;
		
		$userOfc = new AppRolUserOfcEditableModel(false, $this->getResponse());
		$criteriaUsrOfc = new stdClass();
		$criteriaUsrOfc->id_sys_user = $id_sys_user;
		$criteriaUsrOfc->id_empresa = $id_empresa;
		$regLoaded = 0;
		$userOfc->loadDBFromCriteria($criteriaUsrOfc, $regLoaded);
		if ($userOfc->haveBrokenRules()) {
			$this->addBrokenRuler($userOfc->getBrokenRules());
			return false;
		}
		
		ExjDBTrx::Start();
		$userOfc->enableTransactionOnDestroy(false);
		
		$userOfc->destroy($userOfc->id);
	//	$userOfc->addBrokenRuler("Pruebas de rollback al eliminar");
		
		if ($userOfc->haveBrokenRules()) {
			$this->addBrokenRuler($userOfc->getBrokenRules());
			ExjDBTrx::Rollback();
			return false;
		}
		
		// Ajustar el id de la empresa
		if ($dataRolUser->id_empresa == $id_empresa) {
			// hay que cambiar ya que la empresa ya es eliminada
			$query = "SELECT 
				  su_ofc.id_empresa 
				FROM 
				  jos_exj_sys_user_empresas su_ofc 
				WHERE 
				  su_ofc.id_sys_user = $id_sys_user
				ORDER BY 
				  su_ofc.modificado_dt DESC 
				LIMIT 1";
			$db = Exj::InstanceDatabase();
			$idOfficeToChange = $db->loadResult($query);
			if (!$db->isValid()) {
				$this->addBrokenRuler($db->getErrorMsg());
				ExjDBTrx::Rollback();
				return false;
			}
			
			if ($idOfficeToChange) {
				$this->enableTransactionOnSave(false);
				$this->id_empresa = $idOfficeToChange;
				$this->save();
				if ($this->haveBrokenRules()) {
					ExjDBTrx::Rollback();
					return false;
				}
			}
		}
		
		ExjDBTrx::Commit();
		
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
    	
    	if (!$this->canDestroyRelationTable($id, 'jos_app_cli_usrs', 'Clientes - Usuarios')) {
    		return false;
    	}
    	
    	global $exj;
    	
    	$this->load($id);
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
     	// ver si el usuario es el mismo logueado
    	$id_user = $this->id_user;
    	if ($id_user == ExjUser::GetId()) {
    		$this->addBrokenRuler("No se puede eliminar, el mismo usuario logueado.");
    		return false;
    	}
    	$id_sys_user = $this->id_sys_user;
    	
    	// ver si el usuario está relacionado en otras empresas como asignado
    	$itemsOfficesRealted = null;
    	// $exj->includeDataCustom('rol_users', 'exj_rol_users');
    	if (!AppRolUsersData::LoadOfficesRelatedUser($itemsOfficesRealted, $id_sys_user)) {
    		$this->addBrokenRuler("No se cargaron empresas relacionadas del usuario.");
    		return false;
    	}
    	
    	if ($itemsOfficesRealted && count($itemsOfficesRealted) > 0) {
    		$namesOffices = array();
    		foreach ($itemsOfficesRealted as $itemOfficeRealted) {
    			$namesOffices[] = $itemOfficeRealted->nom_empresa;
    		}
    		
    		$namesOffices = implode(', ', $namesOffices);
    		
    		$this->addBrokenRuler("No se puede eliminar el usuario.<br/>Está relacionado con otras Empresas:<br/>$namesOffices");
    		
    		return false;
    	}
    	
    	$tablesSVU = array();
    	$tablesSVU[] = "jos_app_personas";
    	$tablesSVU[] = "app_loc_empresas";
    	
    	$db = Exj::InstanceDatabase();
    	$db->setQuery("SHOW TABLES");
    	$tablesSystem = $db->loadRowList();
    	if (!$db->isValid()) {
    		$this->addBrokenRuler("Error al leer tablas del sistema.<br/>Ref:".$db->getErrorMsg());
    		return false;
    	}
    	
    //	print_r($tablesSystem);
    	
    	foreach ($tablesSystem as $rowtableSystem) {
    		foreach ($rowtableSystem as $tableSystem) {
    		//	echo "<br/>tableSystem: $tableSystem";
    			if (strlen($tableSystem) <= 9) {
    				continue;
    			}
    			
    			if (strpos($tableSystem, 'jos_app_') === 0 || (strpos($tableSystem, 'jos_exj_') === 0)) {
    				if (!in_array($tableSystem, $tablesSVU)) {
    					$tablesSVU[] = $tableSystem;
    				}
    			}
    		}
    	}
    	
    //	print_r($tablesSVU);
    	$tablesExepts = array('jos_exj_helpdesk_catalogs', 'exj_log_pers_props', 'exj_log_pers_tables',
    					'jos_exj_sys_lang', 'jos_app_doc_tipos', 'jos_app_files_type');
    					
    	
    	foreach ($tablesSVU as $tableMrCargo) {
    		if (in_array($tableMrCargo, $tablesExepts)) {
    			continue;
    		}
    		
    		$nameEntity = str_replace(array('jos_app_loc_', 'jos_app_', 'jos_exj_'), '', $tableMrCargo);
    		$nameEntity = str_replace("_", ' ', $nameEntity);
    		$nameEntity = ucwords($nameEntity);
    		$nameEntity = str_replace("Cnt ", 'Container ', $nameEntity);
    		$nameEntity = str_replace("Cit ", 'Cities ', $nameEntity);
    		
    	//	echo "<br/>Verificando tabla: $tableMrCargo Name: $nameEntity";
    		
    		if (!$this->_canDestroyTableUserChange($tableMrCargo, $nameEntity)) {
	    		break;
	    	}
    	}
    	
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
		
    	return true;
    }
    
    private function _canDestroyTableUserChange($nameTable, $nameEntity){
    	return $this->canDestroyRelationTable($this->id_user, $nameTable, $nameEntity, 'id_usuario_modifico', "No se puede eliminar.<br/>El Usuario a creado o editado registros en el Sistema.");
    }
    
     /**
     * overwrited. Después de eliminar
     *
     * @param int $id
     * @param int $affectedRows
     * @return bool. Retornar false para cancelar el eliminado y adicionar la regla rota
     */
    protected function afterDestroy($id, $affectedRows){
    	// Eliminar usuario de joomla, si puede eliminar ya que se comprobó que no estan relaciondados con
    	// cambios en registros
    	$id_user = $this->id_user;
    	
    	$userx = JUser::getInstance($id_user);
    	if (!$userx->id) {
    		// $this->addBrokenRuler("No se pudo cargar información de usuario joomla!");
    		// return false;
    		// puede ser el caso que fue eliminado.
    		return true;
    	}
    	
    	if (!$userx->delete()) {
    		$errorDel = $userx->getError();
    		if (!$errorDel) {
    			$errorDel = "No se puede eliminar eliminar usuario joomla, motivo desconocido.";
    		}
    		
    		$this->addBrokenRuler($errorDel);
    		return false;
    	}
    	
    //	$this->addBrokenRuler("despues de eliminar id_user: $id_user");
    //	return false;
    	
    	return true;
    }
    
    /**
     * overwrited. Inicio de Guardar
     *
     * @return bool
     */
    protected function initSave() {
    	if ($this->isNew()) {
    		if (!$this->isSettedField('id_empresa') || !$this->id_empresa) {
    			$this->id_empresa = ExjUser::GetIdEmpresa();
    		}
    	}
    	else {
    		if (!ExjUser::IsRolSuperAdmin()) {
    			if ($this->isSettedField('id_empresa')) {
    				$this->resetField('id_empresa');
    			}
    		}
    	}
    	
        return true;
    }

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	if (!$this->_canSave()) {
    		return false;
    	}
    	
    	if ($this->isNew()) {
    		$this->setValueToField('id_sys_lang', 1); // ESPAÑOL
    		$this->setValueToField('enable_debug', 0); // NO ACTIVA DEBUG
    	//	$this->setValueToField('sys_type_theme', 'RIDE');
    	}
    	
    	return true;
    }
    private function _canSave(){
    	if (!$this->isSettedField('id_user') || !$this->id_user) {
    		return true;
    	}
    	
    	global $exj;
    	if (!class_exists('AppRolUsersData')) {
    		// $exj->includeDataCustom('rol_users', 'exj_rol_users');
    	}
    	
    	$infoUser = AppRolUsersData::GetInfoUsuarioSystemFromUserJoomla($this->id_user);
    	if ($infoUser === false) {
    		$this->addBrokenRuler("No se recuperó información de usuario del sistema!");
    		return false;
    	}
    	if (!$infoUser) {
    		// no existe el usuario registro como usuario del sistema
    		return true;
    	}
    	
		if ($infoUser->id_sys_user == $this->id) {
			// es el mismo usuario
			return true;
		}
		
		$msgInfo = array();
		if ($infoUser->user_change == $infoUser->user_related) {
			$msgInfo[] = "Usuario ya está registrado, último cambio el " . ExjDate::ConvertToDateTimeDisplay($infoUser->modificado_dt);
		}
		else {
			$msgInfo[] = "Usuario ya está registrado by: $infoUser->user_change el " . ExjDate::ConvertToDateTimeDisplay($infoUser->modificado_dt);
		}
		
		$msgInfo[] = "<br/><b>INFORMACION DEL USUARIO</b>";
		$msgInfo[] = "Usuario: $infoUser->user_related, Correo: $infoUser->email";
		$msgInfo[] = "Empresa: $infoUser->nom_empresa";
		
		$msgInfo = implode('<br/>', $msgInfo);
		
		$this->addBrokenRuler($msgInfo);
		return false;
    }
    
    /**
     * overwrited. Despues de Guardar
     *
     * @param object $responseData
     * @return bool. si se retorna false y se activa transaccion al guardar se cancelan los datos guardado
     */
    protected function afterSave(&$responseData){
    	if (!$this->_saveOfficesAssigners()) {
    		return false;
    	}
    	
    	return true;
    }
    
    public function changeEmpresaToPerson($id_persona, $id_empresa){
    	if (!$id_persona){
    		$this->addBrokenRuler("No se ha enviado <b>ID Persona</b> para cambio de empresa del usuario");
    		return false;
    	}
    	if (!$id_empresa){
    		$this->addBrokenRuler("No se ha enviado <b>ID Empresa</b> para cambio de empresa del usuario");
    		return false;
    	}
    	
    	global $exj;
        $db = Exj::InstanceDatabase();
        
    	$sql = "UPDATE " . $this->getNameTable();
    	$sql .= " SET id_empresa=$id_empresa";
    	$sql .= " WHERE id_persona=$id_persona";
    	
    	$db->query($sql);
    	if ($db->getErrorMsg()) {
    		$this->addBrokenRuler($db->getErrorMsg());
    		return false;
    	}
    	
    	return true;
    }
    
    private $_idsOfficesToAssigners=null;
    
    private function _saveOfficesAssigners(){
    	/*
    	echo "<br>"  . __FUNCTION__;
    	$this->addBrokenRuler("test xxx");
    	return false;
    	*/
    	
    	if (!$this->_idsOfficesToAssigners) {
    		return true; // no hay errores
    	}
    	
    	if (!self::IsSettedValue($this->id_sys_user)) {
    		$this->addBrokenRuler("ERROR ADICIONANDO EMPRESA AL USUARIO.<br/>No se seteó ID del usuario del sistema.");
    		return false;	
    	}
    	
    	if (!$this->id_sys_user) {
    		$this->addBrokenRuler("ERROR ADICIONANDO EMPRESA AL USUARIO.<br/>No se indicó ID del usuario del sistema.");
    		return false;
    	}
    	
    	global $exj;
    	if (!class_exists('AppRolUserOfcEditableModel')) {
    		// $exj->includeModelEditable('rol_user_ofc', 'exj_rol_users');
    	}
    	
    	$userOfc = new AppRolUserOfcEditableModel(false, $this->getResponse());
    	
    	foreach ($this->_idsOfficesToAssigners as $idOfficeToAssigner) {
    		$userOfc->reset();
    		$userOfc->enableTransactionOnSave(false);
    	
	    	$userOfc->setValueId(0);
	    	$userOfc->id_sys_user = $this->id_sys_user;
	    	$userOfc->id_empresa = $idOfficeToAssigner;
	    	
	    	$userOfc->save();
	    	if ($userOfc->haveBrokenRules()) {
	    		$this->addBrokenRuler($userOfc->getBrokenRules());
	    		break;
	    	}
    	}
    	
    	return (!$this->haveBrokenRules());
    }
    
    /**
     * Adiciona una empresa al usuario pasado por parámetro
     *
     * @param int $id_sys_user
     * @param int $id_empresaNew Si no se indica toma la empresa actual del usuario logueado
     * @return bool false si ocurrio un error sino true
     */
    public function addOfficeToUser($id_empresaNew=null){
    	if (!$this->_idsOfficesToAssigners) {
    		$this->_idsOfficesToAssigners = array();
    	}
    	
    	if (!$id_empresaNew) {
    		$id_empresaNew = ExjUser::GetIdEmpresa();
    	}
    	
    	if (!in_array($id_empresaNew, $this->_idsOfficesToAssigners)) {
    		$this->_idsOfficesToAssigners[] = $id_empresaNew;
    	}
    	
    	return true;
    }
    
    /**
     * Importa datos setados a Usuarios del Sistema y Adiciona la empresa al Usurio del Sistema
     *
     * @return bool
     */
    public function importData(){
    	if (!$this->isSettedField('id_user') || !$this->id_user) {
    		$this->addBrokenRuler("No se a seteado ID USR JOOMLA para importar");
    		return false;
    	}
    	
    	$id_empresa = $this->getParam('id_empresa', 0, false);
    	if (!$id_empresa) {
    		$id_empresa = ExjUser::GetIdEmpresa();
    	}
    	
    	// consultar si ya esta registrado
    	$criteria = new stdClass();
    	$criteria->id_user = $this->id_user;
    	
    	$objSelf = null;
    	if (!$this->loadDBFromCriteriaToObject($objSelf, $criteria, 'id_sys_user,id_empresa')) {
    		$this->addBrokenRuler("ERROR. importData. No se pudo cargar desde criteria en: " . get_class($this));
    		return false;
    	}
    	
    	if ($objSelf) {
    		$this->setValueId($objSelf->id_sys_user);
    		if ($objSelf->id_empresa == $id_empresa) {
    			return true;
    		}
    		
    		// Exj::IncludeClass('AppRolUserOfcEditableModel', 'exj_rol_users');
    		$ruo = new AppRolUserOfcEditableModel(false, $this->getResponse());
    		if ($ruo->existeUserOfc($objSelf->id_sys_user, $id_empresa)) {
    			return true;
    		}
    		
    		// edit y adicionar empresa
    		$this->addOfficeToUser($id_empresa);
    		$this->save();
    		if ($this->haveBrokenRules()) {
	    		return false;
	    	}
    		
    		return true;
    	}
    	
    	// no existe hay que crearlo
    	$this->setValueId(0);
    	
    	if (!$this->isSettedField('id_empresa') || !$this->id_empresa) {
    		$this->id_empresa = $id_empresa;
    	}
    	
    	$this->addOfficeToUser($this->id_empresa);
    	
    	$this->save();
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
    	return true;	
    }
	
}

?>