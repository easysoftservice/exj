<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUpgradesReportModel
 * Modelo del reporte para: Actualizaciones
 * Autor: Byron Córdova
 */
class AppSysUpgradesReportModel extends ExjReportModel {

	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Actualizaciones del Sistema", "Actualizaciones");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('file_zip_code', 'CODE', 24);
		$this->registerCol('file_zip_sql', 'DB', 24);
		$this->registerCol('version_upg', 'VERSION', 15);
		$this->registerCol('state_text', 'ESTADO', 15);
		$this->registerCol('desc_upg', 'DESCRIPCION', 45);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Actualizaciones';
		$category = 'Sistema';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppSysUpgradeModel::loadListSysUpgrades($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>