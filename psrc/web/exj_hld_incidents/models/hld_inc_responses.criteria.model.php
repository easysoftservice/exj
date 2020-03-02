<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncResponsesCriteriaModel
 */
class AppHldIncResponsesCriteriaModel extends ExjCriteriaModel {
	public $id_hld_incident;
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function criteriaRegisterFields(){
		$this->registerFieldInt('id_hld_incident', 'Incidente', true, false, false);
	}

	/**
	 * overwrited. Registro de Controles para la UI
	 *
	 */
	public function criteriaRegisterControlsUI(){
	}

}
?>