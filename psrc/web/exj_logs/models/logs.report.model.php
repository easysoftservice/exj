<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppLogsReportModel
 * Modelo del reporte para: Logs
 * Autor: Byron Córdova
 */
class AppLogsReportModel extends ExjReportModel {

	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Logs del Sistema", "Logs");
		$this->fixPageHorizontal();
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('col2Time', 'Hora', 9);
		$this->registerCol('col4UserName', 'Usuario', 12);
		$this->registerCol('col7TypeErrorStr', 'Tipo', 12);
		$this->registerCol('col8Msg', 'Mensaje', 27);
		$this->registerCol('col9Traces', 'Trasas', 60);
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
		$isLoad = AppLogModel::loadListLogs($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>