<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppTableColsListModel
 * Modelo de lista para: Columnas de una tabla
 */
class AppTableColsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('table_cols', 'table_cols');
		
		$this->setReportDownload(false, false, false);
		
		$this->setConfig('Columnas', 'id_campo', false);
		$this->nameTopics = 'Columnas';
		$this->nameTopic = 'Columna';
		$this->defaultSort = 'nameCol';
		
		// $this->autoAddColInfoUltimoCambio();
		$this->getView()->setForceFit(false);
		$this->fixGridEditorGridPanel();
		$this->forceEnableViewLogPers(false);
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldId('id_campo');
		$this->registerFieldString('nameCol', 'Columna');
		$this->registerFieldInt('isNullable', 'Nulable');
		$this->registerFieldString('labelCol', 'Etiqueta');

		$this->registerFieldInt('isPrimaryKey');
		$this->registerFieldString('dataType', 'Tipo');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('nameCol', self::COL_ANCHO_DETALLE, false);
		$this->registerCol('dataType', self::COL_ANCHO_CODIGO, false);
		$this->registerColTextSino('isNullable', self::COL_ANCHO_CODIGO-12);
		$this->registerColEditorTextFieldRequired('labelCol', self::COL_ANCHO_DETALLE);
	}	
}

?>