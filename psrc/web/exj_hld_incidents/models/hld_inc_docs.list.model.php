<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncDocsListModel
 * Modelo de lista para: Respuestas del Incidente
 */
class AppHldIncDocsListModel extends ExjListModel {
	
	public function __construct($hMenu, $nameListModel='', $nameController=''){
		if (!$hMenu) {
			$hMenu = new ExjHelperMenu();
		//	$hMenu->loadFromSession();
    		$hMenu->fixAccessOnlyTrash();
    		
    	//	print_r(ExjSession::IsNew());
		}
		
		
		parent::__construct($hMenu, $nameListModel, $nameController);
	}
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('hld_inc_docs', 'hld_inc_docs');
		
		$this->setReportDownload(false, false, false);
		
		$this->setConfig('Documentos del Incidente', 'id_hld_inc_doc');
		$this->nameTopics = 'Documentos del Incidente';
		$this->nameTopic = 'Documento';
		$this->defaultSort = 'doc.modificado_dt';
		
		$this->getView()->setForceFit(false);
		$this->forceEnableViewLogPers(false);
		
		$this->autoAddColInfoUltimoCambio(false);
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldInt('id_hld_inc_doc', 'Id Doc del Inc');
		$this->registerFieldString('valor_doc', 'Valor');
		$this->registerFieldString('tipo_doc', 'Tipo');
		
		$this->registerFieldString('desc_doc', 'Descripción');
		$this->registerFieldString('titulo_doc', 'Título');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('titulo_doc', self::COL_ANCHO_NOMBRE);
		$this->registerCol('desc_doc', self::COL_ANCHO_DETALLE);
	}	
	
	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$id_hld_incident = $this->getParamFromBaseParams('id_hld_incident', 0);
		if (!$id_hld_incident) {
			$this->getResponse()->setMsgError("No se indicó el ID del incidente para documentos");
			return false;
		}
		
		$isLoad =  AppHldIncidentModel::LoadListDocs($items, $total, $id_hld_incident);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>