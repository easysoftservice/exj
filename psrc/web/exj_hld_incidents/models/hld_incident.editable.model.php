<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncidentEditableModel
 */
class AppHldIncidentEditableModel extends ExjEditableModel {
	private $_isNewData = false;
	
	public $id_hld_incident;
	public $id_helpdesk;
	public $id_hld_catalog_state;
	public $id_hld_catalog_priority;
	public $title_incident;
	public $desc_incident;
	public $start_incident;
	public $end_incident;
	public $id_user_created;
	public $id_empresa;
	public $id_sys_user_asignado;
	
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
		$nameTable = 'jos_exj_helpdesk_incidents';
		$fieldKey = 'id_hld_incident';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_helpdesk', 'Id HelpDesk');
		$this->registerFieldInt('id_hld_catalog_state', 'Id Estado');
		$this->registerFieldInt('id_hld_catalog_priority', 'Id Prioridad');
		$this->registerFieldInt('id_user_created', 'Id Usuario que lo creó', false, false);
		$this->registerFieldInt('id_empresa', 'Id Empresa');
		$this->registerFieldIntNullable('id_sys_user_asignado', 'Id Usr Asignado');
		$this->registerFieldString('title_incident', 'Asunto');
		$this->registerFieldString('desc_incident', 'Descripción');
		$this->registerFieldDateTime('start_incident', 'Inicio', false, false);
		$this->registerFieldDateTime('end_incident', 'Fecha Final', true, false);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI($this->_comboEstados());
    	$this->registerControlUI($this->_comboPrioridades());
    	$this->registerControlUI($this->_comboMesasAyuda());
    	$this->registerControlUI(ExjUI::NewTextField('title_incident', 'Asunto'));
    	
    	$txaDesc = ExjUI::NewTextField('desc_incident', 'Descripción');
    	$txaDesc->height = 210;
    	$this->registerControlUI($txaDesc);
    	    	
    	$this->registerControlUI(AppHldIncidentsUIHelper::NewUsrAsignarComboSimple());
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextName('title_incident', true, 60, 3);
    	$this->applyValidationTextMemo('desc_incident');
	}
	
	private function _comboMesasAyuda(){
    	return AppHelpdeskUIHelper::NewComboSimpleHelpdesks();
	}

	private function _comboPrioridades(){
    	return AppHelpdeskUIHelper::NewComboSimplePrioridades();
	}

	private function _comboEstados(){
    	return AppHelpdeskUIHelper::NewComboSimpleEstados();
	}
	

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}
    	
    	$numRespuestas = $this->getNumRecordsRelationTable($id, 'jos_exj_helpdesk_incs_responses');

    	if ($numRespuestas > 1) {
			global $exj;
			if (!ExjUser::IsRolSuperAdmin()) {
				$this->addBrokenRuler("No se puede eliminar este incidente.<br/>Solo el Usuario tipo: <b>Super Administrador</b> lo puede eliminar");
				$this->addBrokenRuler("Existen <b>$numRespuestas</b> Respuestas del Incidente");
				return false;	
			}
    	}
		
    	// childs
    	$responseInc = new AppHldIncidentRespEditableModel(false);
    	$responseInc->destroy($id, 'id_hld_incident');
    	if ($responseInc->haveBrokenRules()) {
    		$this->addBrokenRuler($responseInc->getBrokenRules());
    		return false;
    	}
		
    	return true;
    }
    
     /**
     * overwrited. Inicio de Guardar
     *
     */
    protected function initSave(){
    	if ($this->isNew()) {
			$this->start_incident = Exj::GetDateTime();
			$this->id_user_created = ExjUser::GetId();
			
			if (!$this->isSettedField('id_empresa')) {
				$this->id_empresa = ExjUser::GetIdEmpresa();
			}
			
			if (!$this->isSettedField('id_helpdesk')) {
				$this->id_helpdesk = $this->getParamId('id_helpdesk');
			}
			
			if (!$this->id_helpdesk) {
				$this->addBrokenRuler("No se indicó ID del HelpDesk");
				return false;
			}
		}
		else {
			if ($this->isSettedField('id_helpdesk') && !$this->id_helpdesk) {
				$this->resetField('id_helpdesk');
			}
		}
		
		
    	
    	return true;
    }

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	$this->_isNewData = $this->isNew();
    	
    	if (!$this->_isNewData) {
    		
    		if ($this->isSettedField('id_hld_catalog_state')) {
				switch ($this->id_hld_catalog_state) {
					case AppHldIncidentsData::ESTADO_PENDIENTE:
					case AppHldIncidentsData::ESTADO_CERRADO:
					case AppHldIncidentsData::ESTADO_RESUELTO:
						$this->end_incident = Exj::GetDateTime();
					break;
					
					case AppHldIncidentsData::ESTADO_TRABAJO_PROG:
						$this->end_incident = null;
					break;
				}
    		}
    		
    		if (!$this->isSettedField('title_incident')) {
    			return true;
    		}
    	}
    	
    	return $this->_canSave();
    }
    private function _canSave(){
    	$paramCriteria = new stdClass();
    	$paramCriteria->id_hld_catalog_state = $this->getParamId('id_hld_catalog_state');
    	$paramCriteria->id_helpdesk = $this->getParamId('id_helpdesk');
    	$paramCriteria->title_incident = $this->getParam('title_incident');
    	if (!$paramCriteria->title_incident) {
    		$this->addBrokenRuler("Título del Incidente es requerido para comprobación de guardardado");
    	}
    	
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
    	$topics=null;
    	$total=0;
		if (!AppHldIncidentModel::LoadListMain($topics, $total, $paramCriteria)) {
			return false;
		}
		
		// $db = Exj::InstanceDatabase();
		// $db->writeLastQuery();
		
		if (!$total) {
			return true;
		}
		
		$item = $topics[0];
		if ($item->id_hld_incident == $this->id) {
			return true;
		}
		
		$this->addBrokenRuler("Ya se encuentra registrado.<br/>Incidente: $item->title_incident <br/>Estado: $item->name_state");
		return false;
    }
    
    /**
     * overwrited. Despues de Guardar
     *
     * @param object $responseData
     * @return bool. si se retorna false y se activa transaccion al guardar se cancelan los datos guardado
     */
    protected function afterSave(&$responseData){
    	
		if ($this->_isNewData) {
			$responseInc = new AppHldIncidentRespEditableModel(false);
			$responseInc->setValueId(0);
			$responseInc->id_hld_incident = $this->id;
			$responseInc->id_hld_catalog_state = $this->id_hld_catalog_state;
			$responseInc->response_inc_res = "Creación de Incidente";
			if (!$responseInc->save()) {
				$this->addBrokenRuler($responseInc->getBrokenRules());
				return false;
			}
		}
    	
    	return true;
    }
    
    /*
	public function registerChildsListModel(){
		$params = new stdClass();
		
		$hMenu = new ExjHelperMenu();
		$hMenu->fixAccessOnlyNewTrash();
		$hMenu->isReports = false;
		
		$this->registerChildListModel('responses', 'responses', 'id_hld_incident', $params, $hMenu, 'hld_inc_response', false);
	}
	*/
}


/**
 * @class AppHldIncidentRespEditableModel
 */
class AppHldIncidentRespEditableModel extends ExjEditableModel {
	public $id_hld_inc_res;
	public $id_hld_incident;
	public $id_hld_catalog_state;
	public $id_hld_catalog_response;
	public $response_inc_res;
	
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
		$this->registerFieldString('response_inc_res', 'Respuesta');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI($this->_comboEstados());
    	
    	$txaDesc = ExjUI::NewTextField('response_inc_res', 'Descripción');
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
	

	private function _comboEstados(){
    	return AppHelpdeskUIHelper::NewComboSimpleEstados();
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