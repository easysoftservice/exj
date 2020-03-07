<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysParametersCriteriaModel
 */
class AppSysParametersCriteriaModel extends ExjCriteriaModel {
	public $type_param, $value_param, $name_param;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('code_param', 'Código');
		$this->registerFieldString('type_param', 'Tipo');
		$this->registerFieldString('name_param', 'Nombre');
		$this->registerFieldString('value_param', 'Valor');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('code_param', 'Código'));
    	$this->registerControlUI(ExjUI::NewTextField('name_param', 'Nombre'));
    	$this->registerControlUI(ExjUI::NewTextField('value_param', 'Valor'));
    	
    	// Exj::IncludeClass('AppSysParametersUIHelper', 'exj_sys_parameters');
    	$this->registerControlUI(AppSysParametersUIHelper::NewTypesComboSimple());
	}
}

?>