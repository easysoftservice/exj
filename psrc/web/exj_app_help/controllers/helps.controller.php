<?php
/**
 * @class AppHelpsController
 * Controlador para Helps
 */
class AppHelpsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		
		if (!AppHelpModel::loadListAyudas($topics, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, count($topics));
		return $response;
	}
	
	public function viewCmp(){
		global $exj;
		$response = new ExjResponse();
		
		$nameCmp = $this->getParam('nameCmp');
		if (!$nameCmp) {
			$response->setMsgError("No se indicó el componente", 'ERROR PRESENTANDO AYUDA');
			return $response;
		}
		$format = $this->getParam('format', 'html');
		if (!$format) {
			$response->setMsgError("No se indicó el formato", 'ERROR PRESENTANDO AYUDA');
			return $response;
		}
		
		if (!AppHelpModel::loadHelpCmp($response->data, $nameCmp, $format)) {
			return $response;
		}
		
		
		
		if (!$response->data) {
			$response->setMsgInfo("Ayuda no está disponible");
			return $response;
		}
		
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
		
		$is_module = $this->getParamIntFromData('is_module', 1);
		
		return AppHelpModel::saveAyuda(0, $this->paramDataChanged, $is_module);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$is_module = $this->getParamIntFromData('is_module');
		
		return AppHelpModel::saveAyuda($this->id, $this->paramDataChanged, $is_module);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppHelpModel::destroy($this->id, 'AppHelpEditableModel', $response);
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppHelpsReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('helps');
		
		$dataReport = new AppHelpsReportModel();
		
		return $dataReport;
	}

}

?>