<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo de lista {labelComponents}
 *
 */
class AppComponentTplsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('component_tpls');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('{labelComponents}', 'id_field_key');
		$this->nameTopics = '{labelComponents}';
		$this->nameTopic = '{labelComponent}';
		$this->defaultSort = '{alias_table}.{field_sort_sql}';
		
		$this->autoAddColInfoUltimoCambio();
		$this->getView()->setForceFit(false);
		$this->fixColAutoActionEdit('{list.field_action_edit_sql}');
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		/*list.registerFields*/
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		/*list.registerCol*/
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppComponentTplsModel::CargarListaPrincipal($this->getResponse(), $items, $total, $this->getBaseParamsCriteriaClone());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>