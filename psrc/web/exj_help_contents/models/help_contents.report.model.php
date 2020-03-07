<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpContentsReportModel
 * Modelo del reporte para: Help Contents
 * Autor: Byron Córdova
 */
class AppHelpContentsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		parent::reportInit();
		
		$this->setConfig("Ayuda - Contenido");
	}
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Ayuda';
		$category = 'Contenido';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::setParamsQuery(0, '');
		
		$isLoad = AppHelpContentsModel::LoadDataHelpContents($items, $this->getParamsCriteria());
		
		ExjRequest::clearParamsQuery();
		
		return $isLoad;
	}
	
}

?>