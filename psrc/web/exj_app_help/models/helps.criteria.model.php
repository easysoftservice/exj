<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpsCriteriaModel
 */
class AppHelpsCriteriaModel extends ExjCriteriaModel {
	public $name_help;

	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('name_help', 'Tema');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('name_help', 'Tema', '96%'));
	}
}
?>