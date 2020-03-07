<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUsersReportModel
 * Modelo del reporte para: Usuarios
 * Autor: Byron Córdova
 */
class AppSysUsersReportModel extends ExjReportModel {

	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Usuarios del Sistema", "Usuarios");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('name_usr', 'USER', 33);
		$this->registerCol('nro_doc_persona', 'DOC NUM', 15);
		$this->registerCol('nom_empresa', 'OFFICE', 30);
		$this->registerCol('str_block_usr', 'LOCKED', 21);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Users';
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
		$isLoad = AppSysUserModel::loadListSysUsers($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>