<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysParameterEditableModel
 */
class AppSysParameterEditableModel extends ExjEditableModel {
	public $id_sys_param;
	public $code_param;
	public $name_param;
	public $type_param;
	public $value_param;
	public $id_empresa;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_app_sys_parameters';
		$fieldKey = 'id_sys_param';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('code_param', 'Código');
		$this->registerFieldString('name_param', 'Nombre');
		$this->registerFieldString('type_param', 'Tipo Dato');
		$this->registerFieldStringNullable('value_param', 'Valor');
		$this->registerFieldInt('id_empresa', 'Id Empresa');
	}

	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		global $exj;
		
    	$this->registerControlUI(ExjUI::NewTextFieldReadOnly('code_param'));
    	$this->registerControlUI(ExjUI::NewTextFieldReadOnly('name_param'));
    	
    	// $taValueParam = 
    	
    	$this->registerControlUI(ExjUI::NewTextArea('value_param', '', '99%', 120));
    	
    	// $exj->includeHelperCustom('sys_parameters_ui', 'exj_sys_parameters');
    	$this->registerControlUI(AppSysParametersUIHelper::NewTypesComboSimple());
	}

	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextNameExtendido('code_param', true, 30, 3);
    	$this->applyValidationTextNameExtendido('name_param', false, 51, 3);
    	// $this->applyValidationTextMemo('value_param', 1200, 0);
    	$this->applyValidationClear('value_param', 1200, 0);
	}

    /**
     * overwrited. Inicio de Guardar
     *
     */
    protected function initSave(){
    	if ($this->isNew()) {
    		if (!$this->isSettedField('id_empresa')) {
    			$this->id_empresa = ExjUser::GetIdEmpresa();
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
    	// comprobación de códigos si ya existen
    	/*
    	$whereExtra = array();
    	$whereExtra[] = "id_empresa = " . ExjUser::GetIdEmpresa();
    	if (!$this->canSaveCodeUnique('code_param', 'Código', $whereExtra, 'name_param', 'Parameter')) {
    		return false;
    	}
    	*/
    	return true;
    }
    
    /**
     * overwrited. Antes de eliminar un registro
     *
     * @param int $id
     * @return bool Retirnar false para cancelar la eliminación
     */
    protected function beforeDestroy($id) {
    	$this->addBrokenRuler("No se permite eliminación de parámetros del sistema!");
    	return false;
    }
    

}

?>