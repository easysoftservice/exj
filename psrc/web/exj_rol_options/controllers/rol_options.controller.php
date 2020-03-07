<?php
/**
 * @class AppRolOptionsController
 * Controlador para Opciones del Rol
 */
class AppRolOptionsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = $this->getResponse();
		$topics=null;
		
		if (!ExjRolOptionsModel::LoadDataRolOptions($response, $topics, $this->paramCriteria)) {
			return $response;
		}

		// print_r($topics);
		
		$response->setDataTopics($topics, count($topics));
		return $response;
	}
	
	public function commitChanges(){
		$response = $this->getResponse();
		
		$news = $this->getParamFromDataChanged('news', null);
		$removes = $this->getParamFromDataChanged('removes', null);
		
		if (!$news && !$removes) {
			$response->setMsgError("No hay nada que guardar!");
			return $response;
		}
		
		$gid = $this->getParamId('gid', 0);
		if (!$gid) {
			$response->setMsgError("No se indicó groupID!");
			return $response;
		}
		
		ExjDBTrx::Start();
		ExjRolOptionsModel::UpdateOptionsRol($response, $gid, $news, $removes);
		
		if ($response->haveMsgError()) {
			ExjDBTrx::Rollback();
			return $response;
		}
		
		if (!$response->haveMsgText()) {
			$response->setMsgInfo("A sido guadardo satisfactoriamente.");
		}
		
		ExjDBTrx::Commit();
		
		return $response;
	}
	
	/**
	 * override. Creación
	 */
	public function create() {
		return $this->commitChanges();
		
		/*
		$response = new ExjResponse();
		$response->setMsgError("No se permite acción create.");
		return $response;
		*/
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		$response->setMsgError("No se permite acción update.");
		
		return $response;
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		ExjRolOptionsModel::destroy($this->id, 'AppRolOptionEditableModel', $response);
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppRolOptionsReportModel
	 */
	public function getDataReport(){
		$dataReport = new AppRolOptionsReportModel();
		
		return $dataReport;
	}

}

?>