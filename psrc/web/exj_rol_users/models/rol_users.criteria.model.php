<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUsersCriteriaModel
 */
class AppRolUsersCriteriaModel extends ExjCriteriaModel {
	public $gid;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldIdRequired('gid', 'Rol', true, false, false, 'j_user.gid');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		global $exj;
		// $exj->includeHelperCustom('rols_ui', 'exj_rols');
		
    	/*
		// $exj->includeHelperCustom('loc_empresas_ui', 'com_app_loc_empresas');
    	$this->registerControlUI(AppLocEmpresasUIHelper::NewComboSimpleEmpresas());
    	*/
    	
    	$this->registerControlUI(AppRolsUIHelper::NewComboSimpleRolsCriteria());
	}
}
?>