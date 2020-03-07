<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppFilesCriteriaModel
 */
class AppFilesCriteriaModel extends ExjCriteriaModel {
	public $size_file;
	public $nameext_file;
	public $id_file_type;
	public $module_allow;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('nameext_file', 'File');
		$this->registerFieldInt('size_file', 'Size');
		$this->registerFieldInt('id_file_type', 'Type');
		$this->registerFieldString('module_allow', 'Module');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		global $exj;
		// $exj->includeHelper('exj_files');
		
    	$this->registerControlUI(ExjUI::NewTextField('nameext_file', 'Archivo', '96%'));
    	$this->registerControlUI(ExjUI::NewNumberField('size_file', 'Tamaño', '99%', true));
    	
    	$this->registerControlUI(AppFilesUIHelper::NewComboSimpleModulos());
    	$this->registerControlUI(AppFilesUIHelper::NewComboSimpleTipos());
	}
	
	
}
?>