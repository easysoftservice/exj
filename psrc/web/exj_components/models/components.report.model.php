<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComponentsReportModel
 * Modelo del reporte para: Components
 * Autor: Byron Córdova
 */
class AppComponentsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Componentes");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerColsFromListModel();
	}
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$category = 'Desarrollo';
		$subject = 'Componentes';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppComponentsModel::CargarListaPrincipal($this->getResponse(), $items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>