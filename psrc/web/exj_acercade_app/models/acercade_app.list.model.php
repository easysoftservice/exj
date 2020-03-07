<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjAcercadeAppListModel
 * Modelo de lista para: AcercadeApp
 */
class ExjAcercadeAppListModel extends ExjListModel {

	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('titulos');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('AcercadeApp', 'id_diploma');
		$this->nameTopics = 'T�tulos';
		$this->nameTopic = 'T�tulo';
		$this->defaultSort = 'name_tit';
		
		$this->autoAddColsNameUserDateRegister();
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('name_tit', 'T�tulo');
		$this->registerFieldInt('is_national', 'Es Nacional');
		$this->registerFieldString('siglas_tit', 'Descripci�n');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('name_tit', 33);
		$this->registerCol('siglas_tit', 15);
	}
}

?>