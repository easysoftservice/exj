<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppFilesListModel
 * Modelo de lista para: Archivos
 */
class AppFilesListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('files');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Archivos', 'id_file');
		$this->nameTopics = 'Archivos';
		$this->nameTopic = 'Archivo';
		$this->defaultSort = 'nameext_file';
		
		$this->autoAddColsNameUserDateRegister();
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('nameext_file', 'Archivo');
		$this->registerFieldString('name_file', 'Nombre');
		$this->registerFieldString('path_file', 'Path');
		$this->registerFieldString('sub_folder', 'Sub Dir');
		$this->registerFieldInt('uri_file', 'URI');
		$this->registerFieldInt('size_file', 'Tamaño');
		$this->registerFieldString('str_size_file', 'Tamaño');
		$this->registerFieldString('name_type_file', 'Tipo');
		$this->registerFieldString('module_allow', 'Módulo');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('nameext_file', 21);
		$this->registerCol('name_file', 21);
		$this->registerCol('module_allow', 15);
		$this->registerCol('name_type_file', 15);
		$this->registerCol('path_file', 24);
		$this->registerCol('str_size_file', 12);
		$this->registerCol('sub_folder', 12, true, true, 'Sub Directorio');
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppFileModel::loadListArchivos($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>