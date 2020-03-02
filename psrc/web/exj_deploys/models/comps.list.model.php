<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppCompsListModel
 * Modelo de lista para: Componentes Actuales de la App
 */
class AppCompsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('comps');
		
		$this->setReportDownload(false, false, false);
		
		$this->setConfig('Componentes Actuales de la App', 'id');
		$this->nameTopics = 'Componentes';
		$this->nameTopic = 'Componente';
		$this->defaultSort = 'grp.name';
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('name_comp', 'Componente');
		$this->registerFieldString('title_comp', 'Título');
		
		$this->registerFieldString('render_published', 'Publicado');
		$this->registerFieldString('render_trash', 'Eliminado');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('name_comp', 27);
		$this->registerCol('title_comp', 30);
		
		$this->registerCol('render_published', 24);
		$this->registerCol('render_trash', 24);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppDeployModel::loadListComps($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionarán al toolbar del grid 
	 * en la parte superior Izquierda
	 *
	 * @return array
	 */
	/*
	public function getItemsTopbarExtrasLeft(){
		$items = array();

		$items[] = ExjUI::NewButton('Publicar...', 'Publica el componente seleccionado', 'exj-btn-view', 'comp_publisher');
		$items[] = ExjUI::NewButton('DesPublicar...', 'DesPublica el componente seleccionado', 'exj-btn-view', 'comp_unpublisher');
		
		return $items;
	}
	*/
	
}

?>