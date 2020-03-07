<?php
/**
 * @class AppCouParamsController
 * A simple application controller extension
 */
class AppCouParamsController extends ExjController {
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'cou_param';
		
		$id_pais = ExjUser::GetIdPais();
		$this->setParam('id_pais', $id_pais);
		
		$params[] = 'id_pais';
	}
	
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = new ExjResponse();
		
		$response->setMsgError("view. No est� soportado!");
		
		return $response;
	}
	
	/**
	 * override. Creaci�n
	 */
	public function create() {
		$response = new ExjResponse();
		
		$response->setMsgError("Creaci�n. No est� soportado!");
		
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		 
		return AppSysParametersModel::saveParamsPais($this->id, $this->paramDataChanged);
	}

	/**
	 * override. Destru�r o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		$response->setMsgError("No est� soportado!");
		
		return $response;
	}

}

?>