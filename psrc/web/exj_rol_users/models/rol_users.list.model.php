<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUsersListModel
 * Modelo de lista para: Usuarios del Sistema
 */
class AppRolUsersListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('rol_users', 'rol_users');
		
		$this->setReportDownload(false, false, false);
		
		$this->setConfig('Usuarios Asignados', 'id_sys_user');
		$this->nameTopics = 'Usuarios Asignados';
		$this->nameTopic = 'Usuario Asginado';
		$this->defaultSort = 'modificado_dt';
		$this->getView()->setForceFit(false);
		
		global $exj;
		// $exj->includeModelList('personas', 'com_app_personas');
		
		$this->autoAddColOrder();
		$this->setTextButtonDelete("Eliminar");
		$this->fixGridEditorGridPanel();
		
		// $this->addListModelSecundary('rol_unassigned_users', 'rol_unassigned_users');
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
		$this->registerFieldBool('is_user_active', 'Enabled');
		
		$this->registerFieldString('user_name', 'Nombre');
		$this->registerFieldString('user_login', 'Login');
		$this->registerFieldString('user_email', 'Email');
		$this->registerFieldString('nom_empresa', 'Empresa');
		
		$this->registerFieldDateTime('date_lastvisit', 'Ultima visita');
		
		$this->registerFieldString('name_usrchg', 'Modificado por');
		$this->registerFieldDateTime('modificado_dt', 'Last Change');
		
		$this->registerFieldRaw('itemsOfcsRel', 'Empresas relacionados');
		$this->registerFieldInt('valueFirstOfcsRel', 'Ofc Rel');
		
		AppPersonasListModel::RegisterFieldsCommon($this);
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('user_name', self::COL_ANCHO_NOMBRE2+15);
		$this->registerCol('user_login', self::COL_ANCHO_NOMBRE);
		$this->registerCol('user_email', self::COL_ANCHO_NOMBRE+90);
		
		if (ExjUser::IsRolSuperAdmin()) {
			$this->registerCol('nom_empresa', self::COL_ANCHO_NOMBRE);
		}
		else {
			$this->registerColHidden('nom_empresa', self::COL_ANCHO_NOMBRE);
		}
		
		
		$this->registerColDateTimeHidden('date_lastvisit', self::COL_ANCHO_FECHAHORA, '', true, 'Fecha y hora de la última visita');
	
		$this->registerColHidden('name_usrchg', self::COL_ANCHO_NOMBRE);
		$this->registerColDateTimeHidden('modificado_dt', self::COL_ANCHO_FECHAHORA);
		
		if (ExjUser::IsRolSuperAdmin()) {
			$cmbOfcsRel = ExjUI::NewComboSimple('', '', array());
		
			$cmbOfcsRel->forceSelection = false;
			$cmbOfcsRel->setEditable();
			
			$cmbOfcsRel->lazyRender = true;
			$cmbOfcsRel->typeAhead = true;
			$cmbOfcsRel->triggerAction = 'all';
		
			$this->registerColCustom('valueFirstOfcsRel', ' ', 'renderComboOfcsRel', self::COL_ANCHO_DEFECTO+15, false, 'Empresas relacionados', false, true);
			$this->registerColEditor('valueFirstOfcsRel', $cmbOfcsRel);
		}
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	/*
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppRolUserModel::LoadListRolUsers($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	*/
	
	
	/**
	 * overwrited. Devuelve los items q se adicionarán al toolbar del grid 
	 * en la parte superior Izquierda
	 *
	 * @return array
	 */
	
	public function getItemsTopbarExtrasLeft(){
		$items = array();
		
		/*
		if (!ExjUser::IsRolSuperAdmin()) {
			return $items;
		}
		*/
		
		$items[] = '-';
		$btnSendMail = ExjUI::NewButton('Usuarios No Asignados...', 'Asigna usuario como asignado...', 'app-btn-users_unassigneds', 'unassigned_users');
		$items[] = $btnSendMail;

		return $items;
	}
	
	
}

?>