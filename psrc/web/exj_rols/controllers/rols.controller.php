<?php
/**
 * @class AppRolsController
 * Controlador para Roles
 */
class AppRolsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppRolsModel::LoadListRoles($topics, $total, $this->paramCriteria)) {
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
		
		$is_internal_rol = $this->getParamIntFromData('is_internal_rol');
		$id_group_acl_aro = $this->getParamIntFromData('id_group_acl_aro', -1);
		
		return AppRolsModel::SaveRol(0, $this->paramDataChanged, $is_internal_rol, $id_group_acl_aro);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$is_internal_rol = $this->getParamIntFromData('is_internal_rol');
		$id_group_acl_aro = $this->getParamIntFromData('id_group_acl_aro', 0);
		
		return AppRolsModel::SaveRol($this->id, $this->paramDataChanged, $is_internal_rol, $id_group_acl_aro);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppRolsModel::destroy($this->id, 'AppRolEditableModel', $response);
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppRolsReportModel
	 */
	public function getDataReport(){
		global $exj;
		// $exj->includeModelReport('rols');
		
		$dataReport = new AppRolsReportModel();
		
		return $dataReport;
	}

}

?>