<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysLogPersReportModel
 * Modelo del reporte para: Logs Persistencias
 * Autor: Byron Córdova
 */
class AppSysLogPersReportModel extends ExjReportModel {

	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Historial de Cambios", "Logs Historial de Cambios");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('cod_empresa', 'OFFICE', 24);
		$this->registerCol('alias_model', 'PROCESS', 21);
		$this->registerCol('last_change', 'UPDATED BY', 30);
		$this->registerCol('alias_prop', 'PROPERTY', 21);
		$this->registerCol('value_old', 'OLD VALUE', 18);
		$this->registerCol('value_new', 'NEW VALUE', 18);
		$this->registerCol('ref_change', 'REF', 15);
	}
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Logs';
		$category = 'System';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppSysLogPersModel::LoadListSysLogsItems($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>