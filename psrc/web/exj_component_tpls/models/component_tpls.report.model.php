<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo de Reporte {labelComponents}
 * @author: {name_author}
 */
class AppComponentTplsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("{labelComponents}");
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerColsFromListModel();
	}
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$category = 'Componente';
		$subject = '{labelComponents}';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppComponentTplsModel::CargarListaPrincipal($this->getResponse(), $items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}
?>