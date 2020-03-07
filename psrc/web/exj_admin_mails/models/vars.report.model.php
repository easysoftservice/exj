<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppVarsReportModel
 * Modelo del reporte para: Variables
 * Autor: Byron Córdova
 */
class AppVarsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Lista de Variables", "Variables", true);
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('var', 'Variable', 27);
		$this->registerColInt('value', 'Valor', 33);
		$this->registerCol('desc', 'Descripcion', 45);
		$this->registerCol('sample', 'Ejemplo', 33);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Variable';
		$category = 'Plantilla de Correos';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$items = AppMailVarHelper::GetVarsList(true);
		
		ExjRequest::ClearParamsQuery();
		
		return (!Exj::GetError()->haveError());
	}
	
}

?>