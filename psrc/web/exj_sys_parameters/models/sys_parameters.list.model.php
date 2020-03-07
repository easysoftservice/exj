<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysParametersListModel
 * Modelo de lista para: Parámetros del Sistema
 */
class AppSysParametersListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('sys_parameters', 'sys_parameters');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Parámetros del Sistema', 'id_sys_param');
		$this->nameTopics = 'Parámetros';
		$this->nameTopic = 'Parámetro';
		$this->defaultSort = 'code_param';
		
		$this->autoAddColsNameUserDateRegister();
		$this->getView()->setForceFit(false);
		$this->fixColAutoActionEdit('code_param');
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('code_param', 'Código');
		$this->registerFieldString('name_param', 'Nombre');
		$this->registerFieldString('type_param', 'Tipo Dato');
		$this->registerFieldString('value_param', 'Valor');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('code_param', self::COL_ANCHO_DEFECTO+99);
		$this->registerCol('name_param', self::COL_ANCHO_NOMBRE+99);
		$this->registerCol('type_param', self::COL_ANCHO_CODIGO-15);
		$this->registerCol('value_param', self::COL_ANCHO_CODIGO-18);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppSysParametersModel::loadListSysParams($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionarán al toolbar del grid 
	 * en la parte superior Izquierda
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		$items = array();

		$items[] = ExjUI::NewButton('Fijar tiempo...', 'Establece la hora del servidor', 'app-btn-uploadfile', 'fix_time_srv');
		
		return $items;
	}
}

?>