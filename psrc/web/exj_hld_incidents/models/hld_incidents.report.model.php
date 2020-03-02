<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncidentsReportModel
 * Modelo del reporte para: HldIncident
 * Autor: Byron Córdova
 */
class AppHldIncidentsReportModel extends ExjReportModel {
	
	/**
	 * overwrite. Inicio
	 *
	 */
	public function reportInit(){
		$this->setConfig("Registro de Incidentes", "Incidentes Mesa de Ayuda");
		$this->showBorderDetail = false;
		$this->fixPageHorizontal();
	}
	
	/**
	 * overwrite. Registro de Criterias.
	 *
	 */
	public function reportRegisterCriteria(){
		// $this->registerCriteriaInt('id_helpdesk', 'HelpDesk');
	}
	
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function reportRegisterCols(){
		$this->registerCol('name_hld', 'MESA AYUDA', 27);
		$this->registerCol('title_incident', 'ASUNTO', 33);
		$this->registerCol('desc_incident', 'DESCRIPCION', 45);
		$this->registerCol('name_state', 'ESTADO', 21);
		$this->registerCol('name_pri', 'PRIORIDAD', 21);
		$this->registerCol('start_incident', 'INICIO', 15);
		$this->registerCol('end_incident', 'FINAL', 15);
	}
	
	
	/**
	 * overwrite. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = 'Incidentes';
		$category = 'Mesa de Ayuda';
	}
	
	/**
	 * override. Devuelve data a items o detalle del reporte
	 *
	 * @param array $items
	 */
	public function reportLoadItems(&$items){
		ExjRequest::SetParamsQuery(0, '');
		
		$total = 0;
		$isLoad = AppHldIncidentModel::LoadListMain($items, $total, $this->getParamsCriteria());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
	
	/**
	 * override. Carga la data para el reporte
	 *
	 * @param object $data
	 */
	/*
	public function reportLoadData(&$data){
		$id_helpdesk = $this->getParamId('id_helpdesk');
		if ($this->haveError()) {
			return false;
		}
		
		return AppHldIncidentModel::loadHelpDesk($id_helpdesk, $data);
	}
	*/
	
	/**
	 * overwrite. Antes del detalle del reporte
	 *
	 */
	
	/*
	public function reportDetailBefore(&$numFilaActual, $data){
		$this->setValueCellFromIndex("Mesa de Ayuda");
		$this->setValueCellTextExpandFromIndex($data->name_hld_catalog);
	}
	*/

	public function reportDetail(&$numFilaActual, $items, $data=null){
		if (ExjUser::IsRolSuperOAdmin()) {
			$this->showHeadersDetail('INCIDENTES');
		}
		else {
			$this->showHeadersDetail(
				'INCIDENTES EMITIDOS POR: '. strtoupper(ExjUser::GetNames())
			);
		}
	}	
}
?>