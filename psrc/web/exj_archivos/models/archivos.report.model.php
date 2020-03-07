<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppArchivosReportModel
 * Modelo del reporte para: Archivos
 * Autor: Byron Córdova
 */
class AppArchivosReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Lista de Archivos", "Archivos");
		$this->showBorderDetail = false;
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('name_file', 'ARCHIVO', 30);
		$this->registerCol('ext_file', 'EXTENSION', 15);
		$this->registerCol('cat_type_file', 'CATEGORIA', 15);
		$this->registerColDateTime('modificado_dt', 'REGISTRO', 15);
	}

	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Archivos subidos';
		$category = 'Archivos';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppArchivoModel::loadListArchivos($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
		
	}
}

?>