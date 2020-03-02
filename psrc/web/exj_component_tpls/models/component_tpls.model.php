<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo o Capa de negocios {labelComponent}
 *
 */
class AppComponentTplsModel extends ExjModel {
	
    public static function CargarListaPrincipal(ExjResponse $response, &$items, &$total, $paramsCriteria=null) {
    	return AppComponentTplsData::CargarListaPrincipal($response, $items, $total, $paramsCriteria);
    }

}

?>