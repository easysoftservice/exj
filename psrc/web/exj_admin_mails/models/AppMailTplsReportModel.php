<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailTplsReportModel
 * Modelo del reporte para: Plantillas
 * Autor: Byron Córdova
 */
class AppMailTplsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Lista de Plantillas", "Plantillas", true);
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('title_tpl', 'TITULO', 33);
		$this->registerCol('subject_default', 'MOTIVO', 30);
		$this->registerCol('type_tpl', 'TIPO', 30);
		$this->registerColInt('is_published', 'PUBLICADO', 18);
		$this->registerColInt('is_default_tpl', 'POR DEFECTO', 18);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Plantilla';
		$category = 'Correos';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppAdminMailModel::loadListPlantillas($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>