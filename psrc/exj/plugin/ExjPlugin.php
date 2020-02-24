<?php

// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjModels
 * Clase base para Plugines
 */
class ExjPlugin extends ExjObject {
	private $_scope=null;

	
	public function __construct(&$scope){
		$this->_scope = $scope;
	}
	
	public function getScope(){
		return $this->_scope;
	}
}
?>