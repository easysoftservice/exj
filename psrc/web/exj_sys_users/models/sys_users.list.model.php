<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUsersListModel
 * Modelo de lista para: Usuarios del Sistema
 */
class AppSysUsersListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('sys_users', 'sys_users');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Usuarios del Sistema', 'id_sys_user');
		$this->nameTopics = 'Usuarios';
		$this->nameTopic = 'Usuario';
		$this->defaultSort = 'str_block_usr';
		$this->getView()->setForceFit(false);
		
		global $exj;
		// $exj->includeModelList('personas', 'com_app_personas');		
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldInt('id_empresa', 'Id Empresa');
		$this->registerFieldInt('id_persona', 'Id Persona');
		$this->registerFieldInt('id_user', 'Id Usuario');
		$this->registerFieldInt('id_sys_lang', 'Id Lenguaje');
		$this->registerFieldInt('enable_debug', 'Debug');
		$this->registerFieldString('sys_type_theme', 'Tema');
		$this->registerFieldString('str_sys_type_theme', 'Tema');
		
		$this->registerFieldString('name_usr', 'User Name');
		$this->registerFieldString('username_usr', 'User Login');
		$this->registerFieldString('usertype', 'Rol');
		$this->registerFieldString('str_block_usr', 'Locked');
		$this->registerFieldString('nom_empresa', 'Empresa');
		$this->registerFieldDateTime('lastvisit_date', 'Ultima visita');
		
		$this->registerFieldInt('time_session', 'Session Time');
		$this->registerFieldDateTime('tiempoSesion', 'Session Time');
		$this->registerFieldInt('client_session', 'Is Backend');
		$this->registerFieldString('str_is_loggin', 'Logged in');
		$this->registerFieldString('str_client_ses', 'Login Client');
		
		
		$this->registerFieldString('name_doc', 'Documento');
		
		$this->registerFieldString('name_usrchg', 'Modificado por');
		$this->registerFieldDateTime('modificado_dt', 'Last Change');
		
		AppPersonasListModel::RegisterFieldsCommon($this);
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('name_usr', self::COL_ANCHO_NOMBRE);
		$this->registerCol('username_usr', self::COL_ANCHO_NOMBRE);
		$this->registerCol('usertype', self::COL_ANCHO_NOMBRE);
		$this->registerCol('nom_empresa', self::COL_ANCHO_NOMBRE);
		$this->registerColHidden('str_sys_type_theme', self::COL_ANCHO_NOMBRE);
		$this->registerCol('str_block_usr', self::COL_ANCHO_NOMBRE-45);
		$this->registerColDateTimeHidden('lastvisit_date', self::COL_ANCHO_FECHAHORA, '', true, 'Date and time of the last visit Office');
		$this->registerColCustom('enable_debug', 'Debug', 'Exj.rendererTextSiNo', self::COL_ANCHO_DEFECTO, true, 'Mode Debug for App', true);
		
		$this->registerColCustom('nombres_persona', 'Persona', 'renderDataPersona', self::COL_ANCHO_NOMBRE+45);
		$this->registerCol('nro_doc_persona', self::COL_ANCHO_NOMBRE);
		
		// $this->registerCol('str_is_loggin', 12);
		$this->registerColCustom('time_session', 'Login', 'Exj.rendererTextSiNo', self::COL_ANCHO_CODIGO-30);
		$this->registerColHidden('str_client_ses', self::COL_ANCHO_NOMBRE);
		$this->registerColDateTimeHidden('tiempoSesion', self::COL_ANCHO_NOMBRE, 'Session', false);
		
		$this->registerColHidden('name_usrchg', self::COL_ANCHO_NOMBRE);
		$this->registerColDateTimeHidden('modificado_dt', self::COL_ANCHO_FECHAHORA);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppSysUserModel::loadListSysUsers($items, $total);
		
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
		
		global $exj;
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