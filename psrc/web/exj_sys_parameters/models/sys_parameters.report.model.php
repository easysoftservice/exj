<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysParametersReportModel
 * Modelo del reporte para: Parámetros del Sistema
 * Autor: Byron Córdova
 */
class AppSysParametersReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Parámetros del Sistema - " . ExjUser::GetNombreEmpresa());
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('code_param', 'CODIGO', 18);
		$this->registerCol('name_param', 'NOMBRE', 21);
		$this->registerCol('type_param', 'TIPO DATO', 15);
		$this->registerCol('value_param', 'VALOR', 39);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Sistema';
		$category = 'Parámetros Sistema';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad =  AppSysParametersModel::loadListSysParams($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>