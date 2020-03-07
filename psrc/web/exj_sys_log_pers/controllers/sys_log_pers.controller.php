<?php
/**
 * @class AppSysLogPersController
 * Controlador para Logs Persistencia
 */
class AppSysLogPersController extends ExjController {
	
    /**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras = false) {
		$nameListModel = 'sys_log_pers';
		
		$this->setParam('loadFromParams', 1);
		
		$params[] = 'id_primary_key_current';
		$params[] = 'name_comp_log';
		$params[] = 'nameEditableModel';
		
		$params[] = 'loadFromParams';
	}
	
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
	//	echo "<br/>" . __METHOD__;
		
		$loadFromParams = $this->getParam('loadFromParams', 0);
		if ($loadFromParams) {
		//	echo " Cargando desde params";
			
			AppSysLogPersModel::LoadListSysLogsItems($response, $this->params);
		}
		else {
	//		echo " Cargando desde paramCriteria";
			AppSysLogPersModel::LoadListSysLogsItems($response, $this->paramCriteria);
		}

		return $response;
	}
	
	public function viewLookupLogTables(){
		global $exj;
		
		$response = new ExjResponse();
		
		$onlyActives = $this->getParamId('onlyActives', -1);
		$exceptSuperAdmin = $this->getParamId('exceptSuperAdmin', -1);
		
		if ($onlyActives == -1) {
			$onlyActives = true;
		}
		if ($exceptSuperAdmin == -1) {
			$exceptSuperAdmin = true;
		}
		
		$topics=null;
		$total=0;
		
		if (!AppSysLogPersModel::loadLookupLogTables($topics, $total, $onlyActives, $exceptSuperAdmin)) {
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
		
		$id_persona = $this->getParamIntFromData('id_persona');		
		
		return AppSysLogPersModel::saveSysUser(0, $this->paramDataChanged, $id_persona, $this->paramData);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$id_persona = $this->getParamIntFromData('id_persona');
		if (!$id_persona) {
			$response->setMsgError("No se indicó Id de persona");
			return $response;
		}
		
		return AppSysLogPersModel::saveSysUser($this->id, $this->paramDataChanged, $id_persona, $this->paramData);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppSysLogPersModel::destroy($this->id, 'AppSysLogPerEditableModel', $response, '', 'sys_log_per');
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppSysLogPersReportModel
	 */
	public function getDataReport(){
		
		$dataReport = new AppSysLogPersReportModel();
		
		return $dataReport;
	}

}

?>