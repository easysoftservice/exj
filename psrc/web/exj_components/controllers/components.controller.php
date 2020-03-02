<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComponentsController
 * Controlador para Components
 */
class AppComponentsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppComponentsModel::CargarListaPrincipal($response, $topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		return $response->setDataTopics($topics, $total);
	}
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'component';
	}

	public function deleteAllFromGrupo(){
		$response = $this->getResponse();
		$id_group_joomla = $this->getParam('id_gj');
		if (!$id_group_joomla) {
			return $response->setMsgError("Id de grupo es requerido");
		}

		AppComponentsModel::DeleteAllFromGrupo($id_group_joomla, $response);

		if (!$response->haveMsgText()) {
			$response->setMsgInfo("Se eliminó satisfactoriamente.");
		}

		return $response;
	}
	
	
}

?>