<?php
/**
 * @class AppSysParametersController
 * Controlador para la gesti�n de par�metros del sistema
 */
class AppSysParametersController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppSysParametersModel::loadListSysParams($topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	/**
	 * override. Creaci�n
	 */
	public function create() {
		$response = new ExjResponse();
		
		$response->setMsgError("No se permite creaci�n de par�metros del sistema!");
		
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
		
		return AppSysParametersModel::saveSysParam($this->id, $this->paramDataChanged);
	}

	/**
	 * override. Destru�r o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppSysParametersModel::destroy($this->id, 'AppSysParameterEditableModel', $response);
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppTiposReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('sys_parameters');
		
		$dataReport = new AppSysParametersReportModel();
		
		return $dataReport;
	}
}

?>