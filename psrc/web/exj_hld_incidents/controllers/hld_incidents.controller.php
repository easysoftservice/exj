<?php
/**
 * @class AppHldIncidentsController
 * Controlador para HldIncident
 */
class AppHldIncidentsController extends ExjController {

    /**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
    
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras = false) {
		$nameListModel = 'hld_incidents';
		
		$this->setParam('loadFromParams', 1);
		
		$params[] = 'id_helpdesk';
		$params[] = 'loadFromParams';
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
		
		
		$loadFromParams = $this->getParam('loadFromParams', 0);
		if ($loadFromParams) {
			if (!AppHldIncidentModel::LoadListMain($topics, $total, $this->params)) {
				return $response;
			}
		}
		else {
			/*
			if (!$this->getParamFromCriteria('id_helpdesk', 0)) {
				$exj->setErrorValidating("No se ha indicado la Mesa de Ayuda.<br/>Debe indicar la Mesa de Ayuda en los filtros");
				return false;
			}
			*/
			
			if (!AppHldIncidentModel::LoadListMain($topics, $total, $this->paramCriteria)) {
				return $response;
			}
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
		
		return AppHldIncidentModel::saveIncident(0, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return AppHldIncidentModel::saveIncident($this->id, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppHldIncidentModel::destroy($this->id, 'AppHldIncidentEditableModel', $response, '', 'hld_incident');
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppHldIncidentReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('hld_incidents');
		
		$dataReport = new AppHldIncidentReportModel();
		
		
		return $dataReport;
	}

}

?>