<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolOptionsReportModel
 * Modelo del reporte para: Opciones del Rol
 * Autor: Byron Córdova
 */
class AppRolOptionsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Roles", "Roles");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('code_rol', 'CODE', 21);
		$this->registerCol('name_rol', 'NANE', 24);
		$this->registerCol('detail_rol', 'DESCRIPTION', 30);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Roles';
		$category = 'Security';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$isLoad = ExjRolOptionsModel::LoadDataRolOptions($items, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>