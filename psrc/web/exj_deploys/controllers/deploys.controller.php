<?php

/**
 * @class AppDeploysController
 * Controlador para Deploys
 */
class AppDeploysController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = $this->getResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppDeployModel::loadListDeploys($topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	/**
	 * override. Creación
	 */
	public function create() {
		$response = $this->getResponse();
		
		if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		
		// PRUEBAS
		/*
    	$deployFiles = new AppDeployFilesModel('xxxx');
    	$deployFiles->writeLogFile(__METHOD__. " INICIANDO");
    	*/
		
		return AppDeployModel::SaveDeploy(0, $this->paramDataChanged);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = $this->getResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return AppDeployModel::SaveDeploy($this->id, $this->paramDataChanged);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = $this->getResponse();
		
		AppDeployModel::destroy($this->id, 'AppDeployEditableModel', $response);
		
		return $response;
	}
	
	public function copyToPreProduction(){
		$response = $this->getResponse();
		
		$id_deploy = $this->getParam('id_deploy');
		if (!$id_deploy) {
			$response->setMsgError("ID del deploy es requerido");
			return $response;
		}
		
		$url_release = '';
		if (!AppDeployModel::CopyToPreProduction($id_deploy, $url_release)) {
			return $response;
		}
		
		$response->data = $url_release;
		
		$response->setMsgInfo(
			"Se ha copiado con éxito.<br/>En la siguiente url se puede ver la aplicación:<br/>$url_release"
		);
		
		return $response;
	}
	
	public function bkDB(){
		$response = $this->getResponse();
		
		$id_deploy = $this->getParam('id_deploy');
		if (!$id_deploy) {
			$response->setMsgError("ID del deploy es requerido");
			return $response;
		}
		
		$nameFileBKDB = '';
		if (!AppDeployModel::bkDB($id_deploy, $nameFileBKDB)) {
			return $response;
		}
		
		$response->data = $nameFileBKDB;
		
		$response->setMsgInfo("Backup de la base de datos satisfactorio.");
		
		return $response;
	}
	
	
	public function ofuscarPHP(){
		$response = $this->getResponse();
		
		$id_deploy = $this->getParam('id_deploy');
		if (!$id_deploy) {
			$response->setMsgError("ID del deploy es requerido");
			return $response;
		}
		
		if (!AppDeployModel::OfuscarPHP($id_deploy)) {
			return $response;
		}
		
		$response->setMsgInfo("Se ha ofuscado con éxito todo el código PHP.");
		
		return $response;
	}
	
}

?>