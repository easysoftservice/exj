<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpdesksReportModel
 * Modelo del reporte para: Helpdesks
 * Autor: Byron Córdova
 */
class AppHelpdesksReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Lista de Mesas de Ayuda", "Mesas de Ayuda");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('name_hld', 'Tipo', 30);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Helpdesks';
		$category = 'Administración';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppHelpdeskModel::loadListHelpdesks($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>