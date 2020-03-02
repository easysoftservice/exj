<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpdesksCriteriaModel
 */
class AppHelpdesksCriteriaModel extends ExjCriteriaModel {
	/**
	 * Tipo del Helpdesk
	 *
	 * @var string
	 */
	public $id_hld_catalog_hld;

	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('id_hld_catalog_hld', 'Mesa de Ayuda');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('id_hld_catalog_hld', 'Mesa de Ayuda', '96%'));
	}
}
?>