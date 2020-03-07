<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysLogPerEditableModel
 */
class AppSysLogPerEditableModel extends ExjEditableModel {
	public $id_log_pers_item;
	public $id_empresa;
	public $id_primary_key_current;
	public $id_primary_key_root;
	public $id_log_pers_prop;
	public $value_old;

	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'exj_log_pers_items';
		$fieldKey = 'id_log_pers_item';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_empresa', 'Id Empresa');
		$this->registerFieldInt('id_primary_key_current');
		$this->registerFieldInt('id_primary_key_root', 'Id Key Root', true, false);
		$this->registerFieldInt('id_log_pers_prop', 'Id Property');
		$this->registerFieldStringNullable('value_old', 'Valor Anterior');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI($this->_comboEmpresas());
    	/*
    	$this->registerControlUI($this->_comboLogsPersistencias());
    	$this->registerControlUI($this->_comboLangs());
    	$this->registerControlUI($this->_comboTemas());
    	*/
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		
	}
	
	private function _comboLogsPersistencias(){

    	
    	return AppSysLogPersUIHelper::NewComboSimpleLogsPersistenciasAll();
    	// return AppSysLogPersUIHelper::NewComboSimpleLogsPersistencias();
    	// return AppSysLogPersUIHelper::NewComboPagingUsersJoomla('id_primary_key_root', 'User');
	}
	
	private function _comboTemas(){
    	return AppSysLogPersUIHelper::NewComboSimpleTemas();
	}
	
	private function _comboLangs(){    	
    	return AppSysLogPersUIHelper::NewComboSimpleLangs();
	}

	private function _comboEmpresas(){
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
		
    	return true;
    }
    

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	return true;
    }
}

?>