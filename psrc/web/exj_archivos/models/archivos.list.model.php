<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppArchivosListModel
 * Modelo de lista para: Archivos
 */
class AppArchivosListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('archivos', 'archivos');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Archivos', 'id_file');
		$this->nameTopics = 'Archivos';
		$this->nameTopic = 'Archivo';
		$this->defaultSort = 'name_file';
		
		$this->autoAddColsNameUserDateRegister();
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('name_file', 'Archivo');
		$this->registerFieldString('ext_file', 'Extensión');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('name_file', 33);
		$this->registerCol('ext_file', 15);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppArchivoModel::loadListArchivos($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>