<?php
/**
 * @class AppHldIncDocsController
 * Controlador para Documentación del Incidente
 */
class AppHldIncDocsController extends ExjController {
	
	/**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras) {
		$nameListModel = 'hld_inc_docs';
		
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
		
		$id_hld_incident = $this->getParamId('id_hld_incident');
		if (!$id_hld_incident) {
			return $response->setMsgError("Id del incidente es requerido para docs");
		}
		
		if (!AppHldIncidentModel::LoadListDocs($topics, $total, $id_hld_incident)) {
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
		
		return AppHldIncidentModel::SaveDocInc(0, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return AppHldIncidentModel::SaveDocInc($this->id, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppHldIncidentModel::destroy($this->id, 'AppHldIncDocEditableModel', $response);
		
		return $response;
	}
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'hld_inc_doc';
		
		$params[] = 'id_hld_incident';
	}
	
}

?>