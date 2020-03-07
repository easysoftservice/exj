<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo o Capa de negocios Contribuyente SRI
 *
 */
class AppSriContribuyentesModel extends ExjModel {
	
    public static function CargarListaPrincipal(ExjResponse $response, &$items, &$total, $paramsCriteria=null) {
    	// Exj::IncludeClass('AppSriContribuyentesData');
    	
    	return AppSriContribuyentesData::CargarListaPrincipal($response, $items, $total, $paramsCriteria);
    }

}

?>