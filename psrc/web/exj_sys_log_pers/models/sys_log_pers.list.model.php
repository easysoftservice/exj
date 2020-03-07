<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysLogPersListModel
 * Modelo de lista para: Logs de Persistencia
 */
class AppSysLogPersListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('sys_log_pers', 'sys_log_pers');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('', 'id_log_pers_item');
		$this->nameTopics = 'Logs';
		$this->nameTopic = 'Log';
		$this->defaultSort = 'modificado_dt';
		$this->fixSortDesc();
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('cod_empresa', 'Empresa');
		$this->registerFieldString('alias_model', 'Proceso');
		$this->registerFieldString('value_old', 'Valor Anterior');
		$this->registerFieldString('alias_prop', 'Propiedad');
		$this->registerFieldString('value_new', 'Valor Nuevo');
		$this->registerFieldString('ref_change', 'Ref');
		$this->registerFieldDateTime('modificado_dt', 'Fecha Cambio');
		$this->registerFieldString('usr_change', 'Cambiado por');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$widthDefault = self::COL_ANCHO_DEFECTO;
		
		$this->registerColHidden('cod_empresa', $widthDefault);
		$this->registerColHidden('alias_model', $widthDefault);
		$this->registerColDateTime('modificado_dt', self::COL_ANCHO_FECHAHORA);
		$this->registerCol('usr_change', $widthDefault);
		$this->registerCol('alias_prop', $widthDefault+15);
		$this->registerCol('value_old', $widthDefault+66);
		$this->registerCol('value_new', $widthDefault+66);
		$this->registerCol('ref_change', self::COL_ANCHO_DEFECTO+45);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		$params = $this->getBaseParams();
		/*
		if (!$params) {
			return parent::onGetData($items, $total);
		}
		*/
		
		$response = new ExjResponse();
		
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppSysLogPersModel::LoadListSysLogsItems($response, $params);
		
		ExjRequest::ClearParamsQuery();
		
		if ($response->haveMsgError()) {
			global $exj;
			Exj::SetErrorValidating($response->getErrorMsg());
			return false;
		}

		if (!$isLoad) {
			return false;
		}
		
		// $items = $response->getItemsDataTopics();
		$response->loadTopics($items);
		$total = $response->getTotalDataTopics();
		
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
		
		if (!ExjUser::IsRolSuperAdmin()) {
			return $items;
		}
		
		
		$items[] = '-';
		$btnSendMail = ExjUI::NewButton('Enviar Correo...', 'Permite enviar un correo al usuario seleccionado...', 'app-btn-mail', 'send_mail');
		$items[] = $btnSendMail;

		return $items;
	}
	*/
	
}

?>