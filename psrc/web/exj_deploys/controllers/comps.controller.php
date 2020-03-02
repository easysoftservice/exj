<?php
/**
 * @class AppDeploysController
 * Controlador para Componentes - Deploys
 */
class AppCompsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = $this->getResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppDeployModel::loadListComps($topics, $total, $this->paramCriteria)) {
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

		return $response->setMsgError("No implementado " . __FUNCTION__);
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = $this->getResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		return $response->setMsgError("No implementado " . __FUNCTION__);
	}

	/**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
	protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras) {
		$nameListModel = 'comps';
		$addItemsTopbarExtras = true;
	}
}

?>