<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUserEditableModel
 */
class AppSysUserEditableModel extends ExjEditableModel {
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
		$this->registerFieldInt('id_empresa', 'Id Empresa');
		$this->registerFieldInt('id_persona', 'Id Persona');
		$this->registerFieldInt('id_user', 'Id User');
		$this->registerFieldInt('id_sys_lang', 'Id Languaje');
		$this->registerFieldInt('enable_debug', 'Enable Debug', false, false, true);
		$this->registerFieldString('sys_type_theme', 'Theme');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI($this->_comboEmpresas());
    	$this->registerControlUI($this->_comboUsuarios());
    	$this->registerControlUI($this->_comboLangs());
    	$this->registerControlUI($this->_comboTemas());
    	$this->registerControlUI($this->_radioGroupEnableDebug());
    	$this->registerControlUI($this->_uiPersona());
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		
	}
	
	private function _uiPersona(){
		global $exj;
    	// $exj->includeModelEditable('persona', 'com_app_personas');
		
		$personaEditableModel = new AppPersonaEditableModel();
		
		$cmpCustom = ExjUI::NewCmpUI('id_persona', $personaEditableModel->to_ui());
		return $cmpCustom;
	}
	
	/*
	private function _comboPersonas(){
		global $exj;
		// $exj->includeHelperCustom('people_ui', 'com_app_personas');
    	
    	return AppPersonaUIHelper::newPersonasComboSimple();
	}
	*/
	
	private function _comboUsuarios(){
		global $exj;
		// $exj->includeHelperCustom('sys_user_ui', 'exj_sys_users');
    	
    	return AppSysUserUIHelper::NewComboSimpleUsuariosAll();
    	// return AppSysUserUIHelper::NewComboSimpleUsuarios();
    	// return AppSysUserUIHelper::NewComboPagingUsersJoomla('id_user', 'User');
	}
	
	
	private function _radioGroupEnableDebug(){
		global $exj;
		// $exj->includeHelperCustom('sys_user_ui', 'exj_sys_users');
    	
    	return AppSysUserUIHelper::newRadioGroupEnableDebug();
	}
	
	private function _comboTemas(){
		global $exj;
		// $exj->includeHelperCustom('sys_user_ui', 'exj_sys_users');
    	
    	return AppSysUserUIHelper::NewComboSimpleTemas();
	}
	
	private function _comboLangs(){
		global $exj;
		// $exj->includeHelperCustom('sys_user_ui', 'exj_sys_users');
    	
    	return AppSysUserUIHelper::NewComboSimpleLangs();
	}

	private function _comboEmpresas(){
		global $exj;
		// $exj->includeHelperCustom('loc_empresas_ui', 'com_app_loc_empresas');
    	
    	return AppLocEmpresasUIHelper::NewComboSimpleEmpresas();
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
    	
    	$this->load($id);
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
    	/*
    	$this->addBrokenRuler("test xxxssss");
    	return false;
    	*/

    	
    	if (!$this->_canDestroyTableUserChange('jos_app_personas', 'Persona')) {
    		return false;
    	}
    	if (!$this->_canDestroyTableUserChange('app_loc_empresas', 'Empresas')) {
    		return false;
    	}
    	if (!$this->_canDestroyTableUserChange('app_loc_paises', 'Paises')) {
    		return false;
    	}
    	if (!$this->_canDestroyTableUserChange('jos_app_files', 'Archivos')) {
    		return false;
    	}
    	if (!$this->_canDestroyTableUserChange('jos_app_loc_sites', 'Ciuddad o Provincia')) {
    		return false;
    	}
    	if (!$this->_canDestroyTableUserChange('jos_app_entities', 'Entidades')) {
    		return false;
    	}
    	if (!$this->_canDestroyTableUserChange('jos_exj_helpdesk_incidents', 'Incidente Help Desk')) {
    		return false;
    	}

    	/*
    	$this->addBrokenRuler("test de delete usr sys");
    	return false;
    	*/
		
    	return true;
    }
    
    private function _canDestroyTableUserChange($nameTable, $nameEntity){
    	return $this->canDestroyRelationTable($this->id_user, $nameTable, $nameEntity, 'id_usuario_modifico', "No se puede eliminar.<br/>El Usuario a creado o editado registros.");
    }
    

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	return $this->_canSave();
    }
    private function _canSave(){
    	if (!$this->isSettedField('id_user')) {
    		return true;
    	}
    	
    	$paramCriteria = new stdClass();
    	// $paramCriteria->id_user = $this->getParamId('id_user');
    	$paramCriteria->id_user = $this->id_user;

    	/*
    	if ($this->haveBrokenRules()) {
    		echo " aqui xxxxx";
    		return false;
    	}
    	*/
    	
    	global $exj;
    	// $exj->includeModel('exj_sys_users');
    	
    	$topics=null;
    	$total=0;
		if (!AppSysUserModel::loadListSysUsers($topics, $total, $paramCriteria)) {
			return false;
		}
		
		// $db = Exj::InstanceDatabase();
		// $db->writeLastQuery();
		
		if (!$total) {
			return true;
		}
		
		$item = $topics[0];
		if ($item->id_sys_user == $this->id) {
			return true;
		}
		
		$this->addBrokenRuler("Ya está registrado.<br/>Usuario: $item->name_usr <br/>Empresa: $item->nom_empresa<br/>Persona: $item->nombres_persona, $item->apellidos_persona");
		return false;
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
	
}

?>