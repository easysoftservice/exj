<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolOptionsCriteriaModel
 */
class AppRolOptionsCriteriaModel extends ExjCriteriaModel {
	public $gid;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldInt('gid', 'Rol', true, false, true, 'j_user.gid');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		global $exj;
		// $exj->includeHelperCustom('rols_ui', 'exj_rols');
    	
    	$this->registerControlUI(AppRolsUIHelper::NewComboSimpleRolsCriteria());
	}
}
?>