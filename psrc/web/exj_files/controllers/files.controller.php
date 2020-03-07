<?php
/**
 * @class AppFilesController
 * Controlador para Archivos
 */
class AppFilesController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppFileModel::loadListArchivos($topics, $total, $this->paramCriteria)) {
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
		
		return AppFileModel::saveArchivo(0, $this->paramDataChanged);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return AppFileModel::saveArchivo($this->id, $this->paramDataChanged);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppFileModel::destroy($this->id, 'AppFileEditableModel', $response);
		
		return $response;
	}

}

?>