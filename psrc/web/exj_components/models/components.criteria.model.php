<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComponentsCriteriaModel
 */
class AppComponentsCriteriaModel extends ExjCriteriaModel {
	public $name;
	public $name_cat;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('name', 'Componente');
		$this->registerFieldString('name_cat', 'Categoría');
		
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('name'));
    	$this->registerControlUI(ExjUI::NewTextField('name_cat'));
	}
}
?>