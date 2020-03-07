<?php
/**
 * @class AppHelpContentsController
 * Controlador para Help Contents
 */
class AppHelpContentsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = new ExjResponse();
		
		$topics=null;
		
		if (!AppHelpContentsModel::LoadDataHelpContents($response, $topics, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, count($topics));
		return $response;
	}
	
	/**
	 * override. Creaci�n
	 */
	public function create() {
		$response = new ExjResponse();
		$response->setMsgError("No se permite acci�n create.");
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		$response->setMsgError("No se permite acci�n update.");
		
		return $response;
	}

	/**
	 * override. Destru�r o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		$response->setMsgError("No se permite acci�n delete.");
		
		return $response;
	}
}

?>