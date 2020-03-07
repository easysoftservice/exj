<?php
/**
 * @class AppUsrPerfilController
 * Controlador para Perfil del Usuario
 */
class AppUsrPerfilController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppUsrPerfilModel::LoadListMain($topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	/**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'usr_perfil';
		
	}
	
	/**
	 * override. Creación
	 */
	public function create() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		return AppUsrPerfilModel::SaveUsrPerfil(0, $this->paramDataChanged, $response);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return AppUsrPerfilModel::SaveUsrPerfil($this->id, $this->paramDataChanged, $response);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppUsrPerfilModel::destroy($this->id, 'AppUsrPerfilEditableModel', $response);
		
		return $response;
	}
}

?>