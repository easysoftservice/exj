<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUsersReportModel
 * Modelo del reporte para: Usuarios
 * Autor: Byron Córdova
 */
class AppRolUsersReportModel extends ExjReportModel {

	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Roles de usuarios - " .ExjUser::GetNombreEmpresa());
	//	$this->registerCriteria('gid', 'Rol');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('user_name', 'USUARIO', 30);
		$this->registerCol('user_login', 'LOGIN', 30);
		$this->registerCol('user_email', 'EMAIL', 45);
		$this->registerCol('nom_empresa', 'EMPRESA', 21);
	}
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Usuarios';
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
		$isLoad = AppRolUserModel::LoadListRolUsersActives($this->getResponse(), $items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>