<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUpgradesCriteriaModel
 */
class AppSysUpgradesCriteriaModel extends ExjCriteriaModel {
	public $file_zip_code;
	public $file_zip_sql;
	public $state_upg;
	public $version_upg;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('file_zip_code', 'Código');
		$this->registerFieldString('file_zip_sql', 'DB');
		$this->registerFieldString('version_upg', 'Versión');
		$this->registerFieldInt('state_upg', 'State');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('file_zip_code', 'Código'));
    	$this->registerControlUI(ExjUI::NewTextField('file_zip_sql', 'DB'));
    	
    	$this->registerControlUI($this->_comboEstados());
    	$this->registerControlUI($this->_comboVersiones());
	}
	
	private function _comboEstados(){
		// global $exj;
	//	// $exj->includeHelperCustom('sys_upgrade_ui', 'exj_sys_upgrades');
    	
    	return AppSysUpgradeUIHelper::NewComboSimpleEstados();
	}
	private function _comboVersiones(){
		// global $exj;
	//	// $exj->includeHelperCustom('sys_upgrade_ui', 'exj_sys_upgrades');
    	
    	return AppSysUpgradeUIHelper::NewComboSimpleVersiones();
	}
}
?>