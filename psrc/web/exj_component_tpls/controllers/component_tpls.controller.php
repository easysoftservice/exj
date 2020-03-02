<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Controlador {labelComponents}
 *
 */
class AppComponentTplsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = $this->getResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppComponentTplsModel::CargarListaPrincipal($response, $topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		return $response->setDataTopics($topics, $total);
	}

	/**
	* Lookup para {labelComponents}
	* @return array
	*/
	public function getLookup(){
		$response = $this->getResponse();

		return $response->setDataTopics(AppComponentTplsData::GetLookupComponentTpls());
	}	
}

?>