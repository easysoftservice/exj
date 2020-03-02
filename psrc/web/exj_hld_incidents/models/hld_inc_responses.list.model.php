<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncResponsesListModel
 * Modelo de lista para: Respuestas del Incidente
 */
class AppHldIncResponsesListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('hld_inc_responses', 'hld_inc_responses');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Respuestas de Incidente', 'id_hld_inc_res');
		$this->nameTopics = 'Respuestas del Incidente';
		$this->nameTopic = 'Respuesta';
		$this->defaultSort = 'modificado_dt';
		
		$this->getView()->setForceFit(false);
		
		$this->autoAddColInfoUltimoCambio(false);
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldInt('id_hld_inc_res', 'Id Resp de Inc');
		$this->registerFieldInt('id_hld_catalog_state', 'Id Estado');
		$this->registerFieldString('color_state', 'Color');
		
		$this->registerFieldString('name_state', 'Estado');
		$this->registerFieldString('response_inc_res', 'Respuesta');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerColCustom('name_state', 'Estado', 'renderState', self::COL_ANCHO_NOMBRE, false);
		$this->registerCol('response_inc_res', self::COL_ANCHO_NOMBRE*5, false);
	}	
	
	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppHldIncidentModel::LoadListRespuestas($items, $total, $this->getBaseParamsCriteriaClone());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>