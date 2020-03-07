<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailTplsCriteriaModel
 */
class AppMailTplsCriteriaModel extends ExjCriteriaModel {
	public $is_published;
	public $is_default_tpl;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldInt('is_published', 'Published');
		$this->registerFieldInt('is_default_tpl', 'Is Default');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewRadioGroupSiNo('is_published', 'Publicado'));
    	$this->registerControlUI(ExjUI::NewRadioGroupSiNo('is_default_tpl', 'Por defecto'));
	}
}
?>