<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppFilesReportModel
 * Modelo del reporte para: Archivos
 * Autor: Byron Córdova
 */
class AppFilesReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Lista de Archivos", "Archivos");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('name_file', 'Nombre', 21);
		$this->registerCol('nameext_file', 'Archivo', 24);
		$this->registerCol('str_size_file', 'Tamaño', 9);
	}
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$category = 'Administración';
		$subject = 'Archivos';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppFileModel::loadListArchivos($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>