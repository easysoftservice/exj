<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolsListModel
 * Modelo de lista para: Roles
 */
class AppRolsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('rols');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Roles', 'id_rol');
		$this->nameTopics = 'Roles';
		$this->nameTopic = 'Rol';
		$this->defaultSort = 'name_rol';
		$this->fixColAutoActionsEditView('code_rol');
	//	$this->fixColAutoActionsEditView('name_rol');
		
		$this->autoAddColsNameUserDateRegister(true);
		$this->getView()->setForceFit(false);
		$this->forceEnableViewLogPers(false);
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldInt('id_group_acl_aro');
		$this->registerFieldString('code_rol', 'Código');
		$this->registerFieldString('name_rol', 'Nombre');
		$this->registerFieldInt('is_internal_rol', 'Is Internal');
		$this->registerFieldInt('is_required_rol', 'Is Required');
		$this->registerFieldString('detail_rol', 'Descripción');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		/*
		global $exj;
		// $exj->includeModelList('ciudades', 'com_app_loc_cities');
		
		AppCiudadesListModel::RegisterFieldsCommon($this);
		*/
		
		$this->registerCol('code_rol', self::COL_ANCHO_CODIGO+45);
		$this->registerCol('name_rol', self::COL_ANCHO_NOMBRE+15);
		$this->registerCol('detail_rol', self::COL_ANCHO_DETALLE);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppRolsModel::LoadListRoles($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>