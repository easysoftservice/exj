<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpsListModel
 * Modelo de lista para: Helps
 */
class AppHelpsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('helps');
		
		$this->setReportDownload(false, false, false);
		
		$this->setConfig('Temas', 'idMnu');
		$this->nameTopics = 'Temas';
		$this->nameTopic = 'Tema';
		$this->defaultSort = 'nameMnu';
		$this->pageSize = 60;
		// $this->autoAddColsNameUserDateRegister();
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('nameMnu', 'Tema');
		$this->registerFieldString('iconCls', 'icon cls');
		$this->registerFieldString('moduleName', 'Nombre mod');
		$this->registerFieldInt('numCatHlp', 'Contenidos');
		$this->registerFieldComplex('dataCats', 'renderDataCats', 'Contenido y Artículos');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('nameMnu', 15);
		$this->registerCol('numCatHlp', 9);
		$this->registerCol('dataCats', 66);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppHelpModel::loadListAyudas($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}

	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Izquierda
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		$items = array();
		
		$items[] = ExjUI::NewButton("Ver...", ExjText::_("View the helps selected from the list"), 'app-btn-view', 'viewhlp');
		
		return $items;
	}
	
}

?>