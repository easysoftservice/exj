<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo de Reporte Contribuyentes SRI
 * Autor: BYRON VINICIO CORDOVA MORA
 */
class AppSriContribuyentesReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Contribuyentes SRI");
        $this->fixPageHorizontal();
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
		$category = 'Componente';
		$subject = 'Contribuyentes SRI';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppSriContribuyentesModel::CargarListaPrincipal($this->getResponse(), $items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}
?>