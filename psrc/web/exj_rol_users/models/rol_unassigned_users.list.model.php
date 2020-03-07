<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUnassignedUsersListModel
 * Modelo de lista para: Usuarios del Sistema
 */
class AppRolUnassignedUsersListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('rol_unassigned_users', 'rol_users');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Usuarios no asgianados', 'id_sys_user');
		$this->nameTopics = 'Usuarios';
		$this->nameTopic = 'Usuario';
		$this->defaultSort = 'modificado_dt';
		$this->fixSortDesc();
				
		$this->autoAddColOrder();
		$this->forceEnableViewLogPers(false);
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldInt('id_user', 'Id User');
		$this->registerFieldInt('id_empresa', 'Id Empresa');
		$this->registerFieldInt('id_persona', 'Id Persona');
		$this->registerFieldInt('id_sys_lang', 'Id Lenguaje');
		
		$this->registerFieldString('user_name', 'Nombre');
		$this->registerFieldString('user_login', 'Login');
		$this->registerFieldString('user_email', 'Correo');
		$this->registerFieldString('nom_empresa', 'Empresa');
		$this->registerFieldString('user_rol', 'Rol');
		$this->registerFieldInt('is_user_active', 'Estado');
		$this->registerFieldInt('is_user_inactive', 'Adicionar');
		$this->registerFieldInt('is_user_delete', 'Eliminar');
		
		$this->registerFieldDateTime('date_lastvisit', 'Ultima visita');
		
		$this->registerFieldString('name_usrchg', 'Modificado por');
		$this->registerFieldDateTime('modificado_dt', 'Last Change');
		
	//	AppPersonasListModel::RegisterFieldsCommon($this);
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$colAddAction = $this->registerColAction(
			'is_user_inactive',
			'renderActionUserActive'
		);
		$this->fixColWidthFixed('is_user_inactive', true, 36);
		$colAddAction->getClass = 'getClassUserAdd';
		$colAddAction->handler = 'handlerUserAdd';
		
		$colAddDelete = $this->registerColAction('is_user_delete', 'renderActionUserActive');
		$this->fixColWidthFixed('is_user_delete', true, 54);
		$colAddDelete->getClass = 'getClassUserDel';
		$colAddDelete->handler = 'handlerUserDel';
		
		
		$this->registerCol('user_name', 18);
		$this->registerCol('user_login', 18);
		$this->registerCol('user_email', 24);
		if (ExjUser::IsRolSuperAdmin()) {
			$this->registerCol('nom_empresa', 18);
		}
		else{
			$this->registerColHidden('nom_empresa', 18);
		}
		
		$this->registerColCustom('is_user_active', 'Enabled', 'Exj.rendererTextSiNo', 12);
		$this->registerCol('user_rol', 18, false);
		
		$this->registerColDateTimeHidden('date_lastvisit', 15, '', true, 'Fecha y hora de la última visita');
	
		
		$this->registerColHidden('name_usrchg', 15);
		$this->registerColDateTimeHidden('modificado_dt');
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppRolUserModel::LoadListRolUnassignedUsers(
			$this->getResponse(), $items, $total, $this->getBaseParams()
		);
		
		ExjRequest::ClearParamsQuery();
		
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
		
		$items[] = '-';
		$btnSendMail = ExjUI::NewButton('xxxx...', 'Assigns users will not assigned...', 'app-btn-users_unassigneds', 'unassigned_users');
		$items[] = $btnSendMail;

		return $items;
	}
	*/
	
	/**
	 * overwrited. Aplica configuración del grid
	 *
	 * @param object $cfg Config del Grid pasado por referencia
	 */
	protected function applyUICfgGrid(&$cfg){
		$cfg->header = false;
		$cfg->title = '';
	}
	
}

?>