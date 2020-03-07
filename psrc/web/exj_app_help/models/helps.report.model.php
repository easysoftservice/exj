<?php
defined( '_JEXEC' ) or die( 'Access not permitted' );

/**
 * @class AppHelpsReportModel
 * Modelo del reporte para: Helps
 * Autor: Byron Córdova
 */
class AppHelpsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Tópicos", "AyudaSFAC");
		
		// $this->showBorderDetail = false;
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('name_help', 'HELP', 15);
		$this->registerCol('content_help', 'DESCRIPTION', 45);
	}

	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Help Topics';
		$category = 'Ayuda';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppHelpModel::loadListAyudas($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
		
	}
}

?>