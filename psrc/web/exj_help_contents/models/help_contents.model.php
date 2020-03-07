<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpContentsModel
 * Modelo para Help Contents
 */
class AppHelpContentsModel extends ExjModel {

	/**
	 * Carga contenido de la ayuda - No Implementado
	 *
	 * @param ExjResponse $response
	 * @param array $items
	 * @param object $paramsCriteria
	 * @return bool
	 */
    static function LoadDataHelpContents(ExjResponse &$response, &$items, $paramsCriteria) {
    	return AppHelpContentsData::LoadDataHelpContents($response, $items, $paramsCriteria);
    }
       
}

?>