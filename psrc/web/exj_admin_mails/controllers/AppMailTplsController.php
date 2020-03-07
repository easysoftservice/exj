<?php
/**
 * @class AppMailTplsController
 * Controlador para Correos
 */
class AppMailTplsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppAdminMailModel::loadListPlantillas($topics, $total, $this->paramCriteria)) {
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
		
		$response = AppAdminMailModel::savePlantila(0, $this->paramDataChanged);
		$response->setDataObject($this->paramDataChanged->id_mail_tpl);
		
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$response = AppAdminMailModel::savePlantila($this->id, $this->paramDataChanged);
		
		$response->setDataObject($this->paramDataChanged->id_mail_tpl);
		
		return $response;
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppAdminMailModel::destroy($this->id, 'AppMailTplEditableModel', $response);
		
		return $response;
	}
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'tpl';
		
		// $params[] = 'id_doc';
		// $params[] = 'message';
	}
	

	/**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras) {
		$nameListModel = 'tpls';
		$addItemsTopbarExtras = true;
		
		// $params[] = 'xxx';
	}	
	
	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppCorreosReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('tpls');
		
		$dataReport = new AppMailTplsReportModel();
		
		return $dataReport;
	}

}

?>