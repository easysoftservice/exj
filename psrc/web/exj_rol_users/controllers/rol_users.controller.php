<?php
/**
 * @class AppRolUsersController
 * Controlador para Rol y Usuarios
 */
class AppRolUsersController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
	//	echo __METHOD__;
		
		if (!AppRolUserModel::LoadListRolUsersActives($response, $topics, $total, $this->paramCriteria)) {
			return $response;
		}
	//	$response->setMsgInfo(__METHOD__);
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	public function viewLookupJUsers(){
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
		
		if (!AppRolUserModel::loadLookupJUsers($topics, $total, $onlyActives, $exceptSuperAdmin)) {
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
		$gid = $this->getParamIntFromData('gid');	
		
		return AppRolUserModel::SaveSysUser(0, $this->paramDataChanged, $id_persona, 0, $this->paramData, $response, $gid);
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
		$id_user = $this->getParamIntFromData('id_user');
		$gid = $this->getParamIntFromData('gid');
		if (!$id_persona) {
			$response->setMsgError("No se indicó Id de persona");
			return $response;
		}
		if (!$id_user) {
			$response->setMsgError("No se indicó Id de Usuario");
			return $response;
		}
		
		return AppRolUserModel::SaveSysUser($this->id, $this->paramDataChanged, $id_persona, $id_user, $this->paramData, $response, $gid);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		// NOTE: No lo elimina sino cambia de estado al usuario
		AppRolUserModel::UnassignerUser($response, $this->id);
		
	//	$response->setMsgInfo(__METHOD__);
		
	//	AppRolUserModel::destroy($this->id, 'AppRolUserEditableModel', $response, '', 'rol_user');
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppRolUsersReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('rol_users');
		
		/*
		$gid = $this->getParamIntFromData('gid');
		echo "getDataReport. gid: $gid";
		*/
		
		$dataReport = new AppRolUsersReportModel();
		
		return $dataReport;
	}

}

?>