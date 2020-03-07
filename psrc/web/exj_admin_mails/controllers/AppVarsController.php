<?php
/**
 * @class AppVarsController
 * Controlador para variables de plantilla
 */
class AppVarsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppAdminMailModel::loadListVariables($topics, $total)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	
	

	/**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras) {
		$nameListModel = 'vars';
		$addItemsTopbarExtras = false;
		
		// $params[] = 'xxx';
	}	
	
	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppVarsReportModel
	 */
	public function getDataReport(){
		$dataReport = new AppVarsReportModel();
		
		return $dataReport;
	}

}

?>