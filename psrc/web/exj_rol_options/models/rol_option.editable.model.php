<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolOptionEditableModel
 */
class AppRolOptionEditableModel extends ExjEditableModel {
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
		$nameTable = 'jos_app_rol_options';
		$fieldKey = 'id_rol';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldStringNullable('code_rol', 'Código');
		$this->registerFieldStringNullable('name_rol', 'Nombre');
		$this->registerFieldStringNullable('detail_rol', 'Detail');
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
    	
    	
    	return true;
    }
	
}

?>