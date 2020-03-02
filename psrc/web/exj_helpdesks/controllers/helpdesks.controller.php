<?php
/**
 * @class AppHelpdesksController
 * Controlador para Helpdesks
 */
class AppHelpdesksController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppHelpdeskModel::loadListHelpdesks($topics, $total, $this->paramCriteria)) {
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
		
		
		return AppHelpdeskModel::saveHelpdesk(0, $this->paramDataChanged);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		
		return AppHelpdeskModel::saveHelpdesk($this->id, $this->paramDataChanged);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppHelpdeskModel::destroy($this->id, 'AppHelpdeskEditableModel', $response);
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppHelpdesksReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('helpdesks');
		
		$dataReport = new AppHelpdesksReportModel();
		
		return $dataReport;
	}

}

?>