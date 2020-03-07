<?php
/**
 * @class UserSendMailsController
 * Controlador para Usuarios
 */
class UserSendMailsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppSysUserModel::loadListSysUsers($topics, $total, $this->paramCriteria)) {
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
		
		return AppSysUserModel::saveSysUser(0, $this->paramDataChanged, $id_persona, $this->paramData);
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
			$response->setMsgError("No se indicó id de la persona");
			return $response;
		}
		
		return AppSysUserModel::saveSysUser($this->id, $this->paramDataChanged, $id_persona, $this->paramData);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		$response->setMsgError("No soportado");
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppSysUsersReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('sys_users');
		
		$dataReport = new AppSysUsersReportModel();
		
		return $dataReport;
	}
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'send_mail';
		
		$params[] = 'id_persona';
	}

}

?>