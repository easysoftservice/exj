<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolsCriteriaModel
 */
class AppRolsCriteriaModel extends ExjCriteriaModel {
	public $code_rol;
	public $name_rol;
	public $detail_rol;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		/*
		$this->registerFieldString('code_rol', 'Código');
		$this->registerFieldString('name_rol', 'Nombre');
		*/
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		/*
    	$this->registerControlUI(ExjUI::NewTextField('code_rol', 'Código', '96%'));
    	$this->registerControlUI(ExjUI::NewTextField('name_rol', 'Nombre', '96%'));
    	*/
	}
}
?>