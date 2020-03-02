<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppTableColsController
 * Controlador para Columna de una tabla
 */
class AppTableColsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = $this->getResponse();
		
		$topics=null;
		$table_name = $this->getParam('table_name');
		if (!$table_name) {
			return $response->setMsgError("Se requiere nombre de tabla");
		}
		
		if (!AppComponentsModel::LoadListTableCols($topics, $table_name)) {
			return $response;
		}
		
		return $response->setDataTopics($topics, count($topics));
	}

}

?>