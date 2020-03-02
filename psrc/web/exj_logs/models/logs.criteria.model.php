<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppLogsCriteriaModel
 */
class AppLogsCriteriaModel extends ExjCriteriaModel {
	public $fileLog;
	public $col4UserName;
	public $col7TypeError;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('fileLog', 'Log', true);
		$this->registerFieldString('col4UserName', 'Nombre de Usuario');
		$this->registerFieldInt('col7TypeError', 'Tipo');
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	protected function registerRules(){
		$this->applyValidationClear('fileLog');
	}
	
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('col4UserName', 'Usuario'));
    	$this->registerControlUI($this->_comboLogs());
    	$this->registerControlUI($this->_comboTipos());
	}
	
	private function _comboLogs(){
    	return AppLogUIHelper::NewComboSimpleLogsAll();
	}
	
	private function _comboTipos(){
    	return AppLogUIHelper::NewComboSimpleTipos();
	}
	
}
?>