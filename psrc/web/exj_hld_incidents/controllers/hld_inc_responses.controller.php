<?php
/**
 * @class AppHldIncResponsesController
 * Controlador para Respuestas del Incidente
 */
class AppHldIncResponsesController extends ExjController {
	
	/**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras) {
		$nameListModel = 'hld_inc_responses';
		
		$params[] = 'id_hld_incident';
	}	
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		// print_r($this->params);
		
		if (!AppHldIncidentModel::LoadListRespuestas($topics, $total, $this->params)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	/**
	 * override. Creación
	 */
	public function create() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		return AppHldIncidentModel::saveResponseInc(0, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return AppHldIncidentModel::saveResponseInc($this->id, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppHldIncidentModel::destroy($this->id, 'AppHldIncidentRespEditableModel', $response);
		
		return $response;
	}
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'hld_inc_response';
		
		$params[] = 'id_hld_incident';
	}
	
}

?>