<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Controlador Contribuyentes SRI
 *
 */
class AppSriContribuyentesController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = $this->getResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppSriContribuyentesModel::CargarListaPrincipal($response, $topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		return $response->setDataTopics($topics, $total);
	}	
	
}

?>