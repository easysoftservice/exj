<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDeploysReportModel
 * Modelo del reporte para: Deploys
 * Autor: Byron Córdova
 */
class AppDeploysReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Deploys", "Deploys CACEL");
		$this->showBorderDetail = false;
		
		$this->fixPageHorizontal();
		// $this->fixPaperSizeA4();
		$this->fixPaperSizeFOLIO();
	}
	
	/**
	 * overwrite. Registro de Criterias.
	 *
	 */
	public function reportRegisterCriteria(){
	}
	
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('version_dpy', 'VERSION', 12);
		$this->registerCol('path_dpy', 'PATH', 30);
		$this->registerCol('file_bkdb', 'DB', 21);
		$this->registerCol('url_dpy', 'URL', 30);
		
		$this->registerColInt('num_filesphp', 'PHP', 9);
		$this->registerColInt('num_filesjs', 'JS', 9);
		$this->registerColInt('num_filescss', 'CSS', 9);
		$this->registerColInt('num_filesimg', 'IMG', 9);
		$this->registerColInt('num_filesotros', 'OTROS', 9);
		$this->registerColInt('num_totfiles', 'TOTAL', 12);
		
		$this->registerColInt('num_filesjs_encoded', 'js Pack', 9);
		$this->registerColInt('size_filesjs_encoded', 'Tamaño Packs', 9);
		
		$this->registerCol('obs_dpy', 'OBSERVACION', 24);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Deploys';
		$category = 'App';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, 'version_dpy');
		
		$total = 0;
		$isLoad = AppDeployModel::loadListDeploys($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		foreach ($items as &$item) {
			$item->num_totfiles = $item->num_filesjs + $item->num_filesphp + $item->num_filescss + $item->num_filesimg+ $item->num_filesotros;
			// $item->size_filesjs_encoded = ExjUtil::RenderSizeBytes($item->size_filesjs_encoded);
		}
		
		return $isLoad;
	}
	
	
	/**
	 * override. Carga la data para el reporte
	 *
	 * @param object $data
	 */
	public function reportLoadData(&$data){
		
	}
	
	/**
	 * overwrite. Antes del detalle del reporte
	 *
	 */
	public function reportDetailBefore(&$numFilaActual, $data){
		// $numFilaActual += 1;
	}

	public function reportDetail(&$numFilaActual, $items, $data=null){
		$this->showHeadersDetail('Deploys de CACEL');
	}	
}
?>