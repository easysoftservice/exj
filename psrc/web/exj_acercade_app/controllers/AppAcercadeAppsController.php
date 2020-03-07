<?php
/**
 * @class AppAcercadeAppsController
 * Controlador para AcercadeApps
 */
class AppAcercadeAppsController extends ExjController {

	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		return $this->getResponse()->setMsgError("Acción no permitida!");
	}

	public function getDataInfo() {
		$response = $this->getResponse();

		ExjAcercadeAppModel::GetDataInfo($response);

		return $response;
	}
	
	/**
	 * override. Creación
	 */
	public function create() {
		return $this->getResponse()->setMsgError("Acción no permitida!");
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		return $this->getResponse()->setMsgError("Acción no permitida!");
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		return $this->getResponse()->setMsgError("Acción no permitida!");
	}
}

?>