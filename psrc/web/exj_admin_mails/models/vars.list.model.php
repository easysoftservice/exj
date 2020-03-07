<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppVarsListModel
 * Modelo de lista para: Variables
 */
class AppVarsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('vars');
		
		$this->setReportDownload(true, true, true);
		
		$this->forceShowReports = true;
		$this->requiereSelectionReport = false;
		
		$this->setConfig('Variables', 'id');
		$this->nameTopics = 'Variables';
		$this->nameTopic = 'Variable';
		$this->defaultSort = 'var';
		$this->fixSortDesc();
	}
	
	
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){	
		$this->registerFieldString('var', 'Variable');
		$this->registerFieldString('desc', 'Descripción');
		$this->registerFieldString('sample', 'Ejemplo');
		$this->registerFieldString('value', 'Valor');
		$this->registerFieldBool('isValid', 'Es Valido');
		$this->registerFieldBool('isSampleValue', 'Es en ejemplo el valor');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('var', 21);
		$this->registerCol('value', 30);
		$this->registerCol('desc', 30);
		$this->registerCol('sample', 30);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){		
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$items = AppMailVarHelper::GetVarsList(true);
		$total = count($items);
		
	//	print_r($items);
		
		ExjRequest::ClearParamsQuery();
		
		return (!Exj::GetError()->haveError());
	}
	
}

?>