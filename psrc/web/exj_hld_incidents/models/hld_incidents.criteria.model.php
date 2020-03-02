<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncidentsCriteriaModel
 */
class AppHldIncidentsCriteriaModel extends ExjCriteriaModel {
	public $title_incident;
	public $id_hld_catalog_state;
	public $id_helpdesk;
	public $id_hld_catalog_priority;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('title_incident', 'Título del Incidente');
		$this->registerFieldInt('id_hld_catalog_priority', 'Prioridad');
		$this->registerFieldInt('id_hld_catalog_state', 'Estado');
		$this->registerFieldInt('id_helpdesk', 'HelpDesk');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('title_incident', 'Título del Incidente', '96%'));
    	
    	$this->registerControlUI($this->_comboPrioridades());
    	$this->registerControlUI($this->_comboEstados());
    	$this->registerControlUI($this->_comboHelpDesks());
	}
	
	private function _comboEstados(){
		global $exj;
		// $exj->includeHelperCustom('helpdesk_ui', 'exj_helpdesks');
    	
    	return AppHelpdeskUIHelper::NewComboSimpleEstados();
	}
	
	
	private function _comboPrioridades(){
		global $exj;
		// $exj->includeHelperCustom('helpdesk_ui', 'exj_helpdesks');
    	
    	return AppHelpdeskUIHelper::NewComboSimplePrioridades();
	}

	private function _comboHelpDesks(){
		global $exj;
		// $exj->includeHelperCustom('helpdesk_ui', 'exj_helpdesks');
    	
    	return AppHelpdeskUIHelper::NewComboSimpleHelpdesks();
	}
	
}
?>