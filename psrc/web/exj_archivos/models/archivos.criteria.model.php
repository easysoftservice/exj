<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppArchivosCriteriaModel
 */
class AppArchivosCriteriaModel extends ExjCriteriaModel {
	public $name_file;
	public $ext_file;

	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('name_file', 'File');
		$this->registerFieldString('ext_file', 'Extensión');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('name_file', 'File', '96%'));
    	$this->registerControlUI(ExjUI::NewTextField('ext_file', 'Extensión', '90%'));
	}
}
?>