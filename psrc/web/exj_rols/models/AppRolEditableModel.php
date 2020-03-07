<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolEditableModel
 */
class AppRolEditableModel extends ExjEditableModel {
	public $id_rol;
	public $code_rol;
	public $name_rol;
	public $detail_rol=null;
	public $is_internal_rol=0;
	public $is_required_rol=0;
	public $id_company;
	public $id_group_acl_aro;
	
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'exj_rols';
		$fieldKey = 'id_rol';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldStringNullable('code_rol', 'Código');
		$this->registerFieldStringNullable('name_rol', 'Nombre');
		$this->registerFieldStringNullable('detail_rol', 'Detalle');
		$this->registerFieldInt('is_internal_rol', 'Is Internal', false, false, true);
		$this->registerFieldInt('is_required_rol', 'Is Required', false, false, true);
		$this->registerFieldInt('id_company', 'Id Company', false, false, false);
		$this->registerFieldInt('id_group_acl_aro', 'Id Group ACL ARO', false, false);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('code_rol'));
    	$this->registerControlUI(ExjUI::NewTextField('name_rol'));
    	$this->registerControlUI(ExjUI::NewTextArea('detail_rol'));
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextNameExtendido('code_rol', false, 21, 2);
    	$this->applyValidationTextNameExtendido('name_rol', false, 45, 3);
    	$this->applyValidationTextMemo('detail_rol', 150);
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
    		case 'code_rol':
    		case 'name_rol':
    			$component->allowBlank = false;
    		break;
    	}
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
    	
    	global $exj;
    	// $exj->includeDataCustom('rols', 'exj_rols');
    	
    	$numUsrsRealted = AppRolsData::GetNumUserRelated($id);
    	if ($numUsrsRealted === null) {
    		$this->addBrokenRuler("Error al recuperar nro de usuario relacionados!");
    		return false;
    	}
    	
    	if ($numUsrsRealted > 0) {
    		$this->addBrokenRuler("No se puede eliminar, existen $numUsrsRealted usuarios relacionados.");
    		return false;
    	}
    	
    	/*
    	if (!$this->canDestroyRelationTable($id, 'jos_app_xxxx', 'xxxxx')) {
    		return false;
    	}
    	*/
    	
    	if ($this->isSettedField('is_internal_rol')) {
	    	if ($this->is_internal_rol == 1) {
	    		$this->addBrokenRuler(ExjText::_("No se puede eliminar el rol interno"));
	    		return false;
	    	}
    	}
    	
    	/*
    	if (!$this->canDestroyRelationTable($id, 'jos_exj_sys_users', 'Users')) {
    		return false;
    	}
    	*/
    	
    	
		
    	return true;
    }

    /**
     * overwrited. Inicio de Guardar
     *
     */
    protected function initSave(){
    	if ($this->isNew()) {
    		if ($this->isEmptyField('id_company')) {
    			$this->id_company = ExjUser::GetIdCompania();
    		}
    		
    		if ($this->isEmptyField('is_internal_rol')) {
    			$this->is_internal_rol = 0;
    		}
    		if ($this->isEmptyField('is_required_rol')) {
    			$this->is_required_rol = 0;
    		}
    	}
    	
    	return true;
    }
    
    /**
     * overwrited. Antes de Guardar
     *
     * @return bool
     */
    public function beforeSave(){
    	if ($this->isSettedField('detail_rol')) {
    		if (is_string($this->detail_rol)) {
    			$this->detail_rol = trim($this->detail_rol);
	    		if (strlen($this->detail_rol) == 0) {
	    			$this->detail_rol = null;
	    		}
    		}
    	}
    	
    	global $exj;
    	$whereExtra = '';
    	$whereExtra = "id_company=" . ExjUser::GetIdCompania();
    	
    	$codeRol = null;
    	$nameRol = null;
    	if ($this->isSettedField('code_rol')) {
    		$codeRol = $this->code_rol;
    		$this->resetField('code_rol');
    	}
    	if ($this->isSettedField('name_rol')) {
    		$nameRol = $this->name_rol;
    		$this->resetField('name_rol');
    	}
    	
    	// comprobación de duplicados
    	$msgInvalidate = '';
    	AppRolsModel::ValidateCodeName($this->id, $codeRol, $nameRol, $msgInvalidate);
    	if ($msgInvalidate) {
    		$this->addBrokenRuler($msgInvalidate);
    		return false;
    	}
    	
    	if ($this->isNew()) {
    		if (!$this->isSettedField('id_group_acl_aro') || !$this->id_group_acl_aro) {
    			$this->addBrokenRuler("No se ha determinado el ID Group ACP ARO");
	    		return false;
	    	}
    	}
    	
    	/*
    	if (!$this->isNew() && $codeRol) {
    		// AppRolsData::ChangeARO_Rules()
    		// $this->id
    		$rowSelf = null;
    		$this->loadToObject($rowSelf, $this->id);
    		if ($this->haveBrokenRules()) {
    			return false;
    		}
    		if (!$rowSelf) {
    			$this->addBrokenRuler("No se cargó información del rol, al parecer fué eliminado recientemente.");
    			return false;
    		}
    	}
    	*/
    	
    	
    	// insert o update del grupo acl aro
    	if (!$this->_saveGroupACL_ARO($codeRol, $nameRol)) {
    		return false;
    	}
    	
    	return true;
    }
    
    private function _saveGroupACL_ARO($codeRol, $nameRol){
    	$id_group_acl_aro = $this->id_group_acl_aro;
    	if (!self::IsSettedValue($id_group_acl_aro) || !$id_group_acl_aro) {
    		return true;
    	}
    	
    	$isNewGroup = ($this->id_group_acl_aro == -1);
    	
    	if ($isNewGroup && (!$codeRol || !$nameRol)) {
    		$this->addBrokenRuler("No se indicó Código o Nomnre del rol!");
    		return false;
    	}
    	elseif (!$isNewGroup && (!$codeRol && !$nameRol)){
    		// no hay que actualizar
    		return true;
    	}
    	
    	$msgInvalid = '';
    	
    	if ($isNewGroup) {
    		// ver si ya existe, y que no este relacionado
    		AppRolsModel::ValidateGroupToCreate($id_group_acl_aro, $codeRol, $nameRol, $msgInvalid);
    		if ($msgInvalid) {
    			$this->addBrokenRuler($msgInvalid);
    			return false;
    		}
    		if ($id_group_acl_aro > 0) {
    			$this->id_group_acl_aro = $id_group_acl_aro;
    			return true;
    		}
    		
    		// el Rol no existe, se lo crea
    		AppRolsModel::CreateGroupACL_ARO($codeRol, $nameRol, $id_group_acl_aro, $msgInvalid);
    		if ($msgInvalid) {
    			$this->addBrokenRuler($msgInvalid);
    			return false;
    		}
    		
    		if (!$id_group_acl_aro || $id_group_acl_aro <= 0) {
    			$this->addBrokenRuler("No se pudo crear grupo acl aro: $nameRol.<br/>Motivo: Desconocido!");
    			return false;
    		}
    		
    		$this->id_group_acl_aro = $id_group_acl_aro;
    		return true;
    	}
    	
    	// Actualizar code o name del grupo acl aro y rules acl
    	AppRolsModel::UpdateGroupACL_ARO($id_group_acl_aro, $codeRol, $nameRol, $msgInvalid);
		if ($msgInvalid) {
			$this->addBrokenRuler($msgInvalid);
			return false;
		}
    	
    	
    	return true;
    }
	
}

?>