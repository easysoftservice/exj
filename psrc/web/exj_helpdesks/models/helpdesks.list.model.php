<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpdesksListModel
 * Modelo de lista para: Helpdesks
 */
class AppHelpdesksListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('helpdesks');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Lista Mesas de Ayuda', 'id_helpdesk');
		$this->nameTopics = 'Mesas de Ayuda';
		$this->nameTopic = 'Mesa de Ayuda';
		$this->defaultSort = 'is_default_hld';
		$this->fixSortDesc();
		
		$this->autoAddColsNameUserDateRegister();
		$this->getView()->setForceFit(false);
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('name_hld', 'Tipo');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('name_hld', self::COL_ANCHO_DETALLE);
	}	
	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppHelpdeskModel::loadListHelpdesks($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>