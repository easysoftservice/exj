<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailsReportModel
 * Modelo del reporte para: Correos
 * Autor: Byron Córdova
 */
class AppMailsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Lista de Correos", "Correos", true);
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('to_email', 'CORREO', 21);
		$this->registerCol('body_mail', 'MENSAJE', 45);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Correos';
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
		$isLoad = AppAdminMailModel::loadListCorreos($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>