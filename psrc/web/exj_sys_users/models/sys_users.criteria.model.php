<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUsersCriteriaModel
 */
class AppSysUsersCriteriaModel extends ExjCriteriaModel {
	public $nombres_persona;
	public $apellidos_persona;
	public $nro_doc_persona;
	public $id_empresa;
	public $id_persona;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('nro_doc_persona', 'Núm Doc.');
		$this->registerFieldString('nombres_persona', 'Nombres');
		$this->registerFieldString('apellidos_persona', 'Apellidos');
		$this->registerFieldInt('id_user', 'Usuario');
		$this->registerFieldInt('id_persona', 'Persona');
		$this->registerFieldInt('id_empresa', 'Office');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		global $exj;
		
    	$this->registerControlUI(ExjUI::NewTextField('nro_doc_persona', 'Núm Doc.', '96%'));
    	$this->registerControlUI(ExjUI::NewTextField('nombres_persona', 'First name'));
    	$this->registerControlUI(ExjUI::NewTextField('apellidos_persona', 'Apellidos'));
    	
		// $exj->includeHelperCustom('loc_empresas_ui', 'com_app_loc_empresas');
    	$this->registerControlUI(AppLocEmpresasUIHelper::NewComboSimpleEmpresas());
    	
    	$this->registerControlUI(AppSysUserUIHelper::NewComboSimpleUsuariosAll());
	}
}
?>