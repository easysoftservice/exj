<?php
/**
 * @class AppLogsController
 * Controlador para Logs
 */
class AppLogsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppLogModel::loadListLogs($topics, $total, $this->paramCriteria)) {
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
		
		$response->setMsgError("No soportado", __METHOD__);
		
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();

		$response->setMsgError("No soportado", __METHOD__);
		
		return $response;
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppLogModel::destroy($this->id, 'AppLogEditableModel', $response, '', 'log');
		
		return $response;
	}

	public function getLogsInternals(){
		$response = new ExjResponse();

		if (!ExjUser::IsRolSuperAdmin()) {
			return $response->setMsgError(
				"Permiso denegado, para usuario: " . Exj::GetUserUserName()
			);
		}

		AppLogModel::LoadLogsInternals($response);

		return $response;
	}

	public function getContentIniInternals(){
		$response = new ExjResponse();

		if (!ExjUser::IsRolSuperAdmin()) {
			return $response->setMsgError(
				"Permiso denegado, para usuario: " . Exj::GetUserUserName()
			);
		}

		AppLogModel::LoadContentIniInternals($response);

		return $response;
	}

	public function saveCntIniInt(){
		$response = new ExjResponse();

		if (!ExjUser::IsRolSuperAdmin()) {
			return $response->setMsgError(
				"Permiso denegado, para usuario: " . Exj::GetUserUserName()
			);
		}

		$txtInter = $this->getParam('txtInter', '');

		AppLogModel::SaveCntIniInt($response, $txtInter);

		return $response;
	}

	public function getVarServer(){
		$response = new ExjResponse();

		if (!ExjUser::IsRolSuperAdmin()) {
			return $response->setMsgError(
				"Permiso denegado, para usuario: " . Exj::GetUserUserName()
			);
		}

		AppLogModel::LoadDataVarServer($response);

		return $response;
	}
}

?>