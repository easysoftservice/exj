<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppLogsListModel
 * Modelo de lista para: Logs del Sistema
 */
class AppLogsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('logs', 'logs');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Logs del Sistema', 'col1Id', false);
		$this->nameTopics = 'Logs';
		$this->nameTopic = 'Log';
		$this->defaultSort = 'col2Time';
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('col2Time', 'Hora');
		$this->registerFieldInt('col3IdCompany', 'Id Empresa');
		$this->registerFieldString('col4UserName', 'Usuario');
		$this->registerFieldFloat('col5Delayed', 'Demora');
		$this->registerFieldString('col6RequestMethod', 'Método');
		$this->registerFieldInt('col7TypeError', 'Id Tipo de Error');
		$this->registerFieldString('col7TypeErrorStr', 'Tipo');
		$this->registerFieldString('col8Msg', 'Mensaje');
		$this->registerFieldString('col9Traces', 'Trasas');
		$this->registerFieldString('col10PathInfo', 'Path');
		$this->registerFieldString('col11UserAgent', 'HTTP User');
		$this->registerFieldString('col12Query', 'Query');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('col2Time', 9);
		$this->registerCol('col4UserName', 12);
		$this->registerCol('col5Delayed', 12, false);
		$this->registerCol('col6RequestMethod', 12);
		$this->registerCol('col7TypeErrorStr', 12);
		$this->registerCol('col8Msg', 27, false);
		$this->registerColHidden('col9Traces', 60, false);
		$this->registerCol('col10PathInfo', 15, false);
		$this->registerCol('col11UserAgent', 18, false);
		$this->registerColHidden('col12Query', 30, false);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppLogModel::loadListLogs($items, $total);
		
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
        	 
		$items[] = ExjUI::NewButton(
			'Ver logs PHP...',
			'Ver logs de php',
			'exj-btn-detalles',
			'viewLogsPhp'
		);

		$items[] = ExjUI::NewButton(
			'Editar php.ini...',
			'Edita archivo php.ini',
			'exj-btn-detalles',
			'editPhpini'
		);

		$items[] = ExjUI::NewButton(
			'Ver Var Server...',
			'Muestra el contenido de variable global SERVER',
			'exj-btn-detalles',
			'viewVarServer'
		);
		
		return $items;
	}
	
}

?>