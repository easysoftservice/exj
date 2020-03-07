<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysLogPersCriteriaModel
 */
class AppSysLogPersCriteriaModel extends ExjCriteriaModel {
	public $id_log_pers_table;
	public $id_primary_key_current;
	public $id_primary_key_root;
	public $id_empresa;
	public $alias_prop;
	
	private $_nameEditableModel='';
	private $_nameComponentLog='';
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldInt('id_log_pers_table', 'Id Log Pers Table', true, false, false);
		$this->registerFieldInt('id_primary_key_current', 'Id Primary Key Current');
		$this->registerFieldInt('id_primary_key_root', 'Id Primary Key Root');
		$this->registerFieldInt('id_empresa', 'Office');
		$this->registerFieldString('alias_prop', 'Property');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		
    	$this->registerControlUI(ExjUI::NewTextField('alias_prop', 'Property'));
    	
    	$this->registerControlUI(AppLocEmpresasUIHelper::NewComboSimpleEmpresas());
    	
    	// $this->registerControlUI(AppSysLogPersUIHelper::NewComboSimpleTables());
	}
	
	/**
	 * overwrited. bind
	 *
	 * @param object $data
	 * @return int Nro bindeados
	 */
    public function bind($data='') {
    	if (!$data) {
    		$this->addBrokenRuler("No se indicó data para mapeo de criteria!");
    		return false;
    	}

    	if (is_array($data)) {
    		$data = self::ConvertArrayToObject($data);
    	}
    	
    	/*
    	echo '<br/>' . __METHOD__.'<br/>';
    	print_r($data);
    	*/
    	
    	$this->_loadIdLogPersTable($data);
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
    	return parent::bind($data);
    }
    
    
    private function _loadIdLogPersTable($data=''){
    	if (isset($data->id_log_pers_table)) {
    		return true;
    	}

    	if (!isset($data->nameEditableModel) || !$data->nameEditableModel) {
    		$this->addBrokenRuler("No se indicó modelo editable para criteria de logs!");
    		return false;
    	}
    	
    	$this->_nameComponentLog = '';
    	if (isset($data->name_comp_log)) {
    		$this->_nameComponentLog = $data->name_comp_log;
    	}
    	
    	
    	$this->_nameEditableModel = $data->nameEditableModel;
    	
    	$ClassEditable = Exj::GetNameClassEditable($this->_nameEditableModel);
    	if (!class_exists($ClassEditable)) {    		
    		if (!class_exists($ClassEditable)) {
    			$this->addBrokenRuler("No existe la clase editable: $ClassEditable según modelo: $this->_nameEditableModel");
    			return false;
    		}
    	}
    	
    	$objEditable = new $ClassEditable(false);
    	$nameTable = $this->_getNameTableFromEditableModel($objEditable);
    	
    	$dataTable = null;
    	if (AppSysLogPersData::LoadLogTable($dataTable, $nameTable)) {
    		$this->id_log_pers_table = $dataTable->id_log_pers_table;
    	}
    	else {
    		$this->addBrokenRuler(AppSysLogPersData::MSG_NOT_HAVE_HISTORY);
    		return false;
    	}
    	
    	$data->id_log_pers_table = $this->id_log_pers_table;
    	
    	return true;
    }
    
    private function _getNameTableFromEditableModel(ExjEditableModel $editableModel){
    	return $editableModel->getNameTable();
    }
	
}
?>