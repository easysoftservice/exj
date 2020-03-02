<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncResponseEditableModel
 */
class AppHldIncResponseEditableModel extends ExjEditableModel {
	public $id_hld_inc_res;
	public $id_hld_incident;
	public $id_hld_catalog_state;
	public $id_hld_catalog_response;
	public $response_inc_res;

	
	/**
	 * overwrited. Inicio del modelo editable
	 *
	 */
	protected function initEditableModel(){
		$this->enableTransactionOnSave();
		$this->enableTransactionOnDestroy();
	}
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_helpdesk_incs_responses';
		$fieldKey = 'id_hld_inc_res';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_hld_incident', 'Id Incidente');
		$this->registerFieldInt('id_hld_catalog_state', 'Id Estado');
		$this->registerFieldInt('id_hld_catalog_response', 'Id Catalogo de Respuestas', true, false);
		$this->registerFieldString('response_inc_res', 'Respuesta', true, true);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		/*
		if (!$this->getParams()) {
			echo " no hay parametros " . __CLASS__;
		}
		else {
			print_r($this->getParams());
		}
		*/
		
//    	$this->registerControlUI($this->_comboRespuestas());
    	
    	$txaDesc = ExjUI::NewTextArea('response_inc_res', 'Descripción');
    	$txaDesc->height = 210;
    	$this->registerControlUI($txaDesc);
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextMemo('response_inc_res');
	}
	
	private function _comboRespuestas(){
		global $exj;
		// $exj->includeHelperCustom('helpdesk_ui', 'exj_helpdesks');
    	
    	return AppHelpdeskUIHelper::NewComboSimpleResponses();
	}

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
	/*
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}
		
    	return true;
    }
    */

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    }
	
	public function saveResponse(){
		if ($this->isSettedField('id_hld_incident') && $this->isSettedField('id_hld_catalog_state')) {
			global $exj;
			// $exj->includeModelEditable('hld_incident');
			$incidente = new AppHldIncidentEditableModel(false);
			$incidente->setValueId($this->id_hld_incident);
			
			$incidente->id_hld_catalog_state = $this->id_hld_catalog_state;
			
			
			$incidente->save();
			if ($incidente->haveBrokenRules()) {
				$this->addBrokenRuler($incidente->getBrokenRules());
				return false;
			}
		}
		

		$this->save();
		return !$this->haveBrokenRules();
	}
}

?>