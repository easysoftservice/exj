<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUserModel
 * Modelo para Rol y Usuario
 */
class AppRolUserModel extends ExjModel {
	
    static function SaveSysUser($id, $dataChanged, $id_persona, $idUserJoomla, $paramData, ExjResponse &$response, $gid=0)
    {
    	
    	try {
    		ExjDBTrx::Start();
    		
			$persona = new AppPersonaEditableModel(false);
			$persona->enableTransactionOnSave(false);
			$persona->disableAllTransactionsDB(true);
			
			if ($persona->bind($dataChanged)) {
				$persona->setValueId($id_persona);
				
				if (!$persona->save()) {
					ExjDBTrx::Rollback();
					return $persona->validateResponse();
				}
				
				$id_persona = $persona->id;
			}
			
			// Create/Update User. Cuando es nuevo siempre idUserJoomla es 0
			self::_SaveUserJoomla($idUserJoomla, $dataChanged, $id, $response, $gid);
			if ($response->haveMsgError()) {
				ExjDBTrx::Rollback();
				return $response;
			}
			
			$sysUser = new AppRolUserEditableModel(false, $response);
			$sysUser->enableTransactionOnSave(false);
			
			if ($sysUser->bind($dataChanged)) {
				$sysUser->setValueId($id);
				if ($id_persona) {
					$sysUser->id_persona = $id_persona;
				}
				
				if ($paramData && $id_persona) {
					$paramData->id_persona = $sysUser->id_persona;
				}
				$sysUser->setParams($paramData);
				if ($sysUser->haveBrokenRules()){
					return $sysUser->validateResponse();
				}
				
				if (!$sysUser->save()) {
					// echo "<br/>ERROR Line: " . __LINE__;
					// No se requiere rollback, ya lo hace la entidad
					// ExjDBTrx::Rollback();
					return $sysUser->validateResponse();
				}
			}
			
			if ($sysUser->haveBrokenRules()) {
				return $sysUser->validateResponse();
			}
			
    		ExjDBTrx::Commit();
    		return $response;
    	}
    	catch (Exception $ex){
    		Exj::SetErrorException($ex);
    		ExjDBTrx::Rollback();
    	}
		
		return Exj::GetResponseError();
    }
    
    private static function _SaveUserJoomla($idUserJoomla, &$dataChanged, $id_sys_user, ExjResponse &$response, &$gid){
    	$isNew = ($id_sys_user <= 0 ? true:false);
    	if ($isNew && (!$dataChanged || !is_object($dataChanged))) {
    		$response->setMsgError("No se han modificado datos!");
    		return false;
    	}
    	
    	if ((!$dataChanged || !is_object($dataChanged))) {
    		return true;
    	}
    	
    //	print_r($dataChanged);
    	
    	// LECTURA DE PARAMETROS
    	$is_user_active = self::_GetValue($dataChanged, 'is_user_active');
    //	echo "<br/>get value: is_user_active: $is_user_active";
    	if ($is_user_active !== null) {
    		if ($is_user_active === '0' || ($is_user_active === 'false')){
    			// echo " es cero o false ";
    			$is_user_active = 0;
    		}
    		else {
    			$is_user_active = ($is_user_active ? 1:0);
    		}
    	}
    //	echo "<br/>is_user_active: $is_user_active es cero: " . ($is_user_active === 0 ? 'si':'no');
    	
    	$user_email = self::_GetValue($dataChanged, 'user_email');
    	$user_login = self::_GetValue($dataChanged, 'user_login');
    	$user_name = self::_GetValue($dataChanged, 'user_name');
    	$pwd2_usr = self::_GetValue($dataChanged, 'pwd2_usr');
    	$pwd_usr = self::_GetValue($dataChanged, 'pwd_usr');
    	
    	if (!$isNew && $user_name === null && $user_login === null && $pwd_usr === null && $user_email === null && $is_user_active === null) {
    		return true;
    	}
    	
		$dataUser = array();
		// echo "is_user_active: $is_user_active";
    	if ($is_user_active !== null) {
    		$dataUser['block'] = ($is_user_active ? 0:1);
    	}
    	if ($user_email !== null) {
    		$dataUser['email'] = $user_email;
    	}
    	if ($user_login !== null) {
    		$dataUser['username'] = $user_login;
    	}
    	if ($user_name !== null) {
    		$dataUser['name'] = $user_name;
    	}
    	if ($pwd_usr !== null) {
    		$dataUser['password'] = $pwd_usr;
    	}
    	if ($pwd2_usr !== null) {
    		$dataUser['password2'] = $pwd2_usr;
    	}
    	
    	if ($gid > 0) {
    		$dataUser['gid'] = $gid;
    	}   	
    	
    	if ($isNew) {
    		if (!$user_name){
    			$response->setMsgError("You must specify a fullName.");
    			return false;
    		}
    		
    		if (!$user_login){
    			$response->setMsgError("You must specify a username.");
    			return false;
    		}
    		
    		if (!$pwd_usr){
    			$response->setMsgError("Password Error.");
    			return false;
    		}
    		
    	//	$response->setMsgError("No está implentado New");
    	//	return false;
    	}
    	
    	// Actualizar usuario
    	$userJoomla = JUser::getInstance($idUserJoomla);
    	if (!$isNew && !$userJoomla->id) {
    		$response->setMsgError("Usuario ha sido eliminado!");
    		return false;
    	}
    	
    	if (!$userJoomla->id) {
    		// se pretende crear el usuario, validar si el correo ya existe
    		
    		
    		$infoUserJoomla = AppRolUsersData::GetInfoUsuarioJoomlaFromEmail($user_email, $response);
    		if ($response->haveMsgError()) {
    			return false;
    		}
    		if ($infoUserJoomla && is_object($infoUserJoomla)) {
    			if ($user_login == $infoUserJoomla->username) {
    				$dataChanged->id_user = $infoUserJoomla->id;
    				return true;
    			}
    			$response->setMsgError("Correo: <b>$user_email</b> ya está registrado con el usuario: $infoUserJoomla->username<br/>Rol: $infoUserJoomla->usertype");
    			return false;
    		}
    		
    		// ver si esta seteado el rol
    		if (!isset($dataUser['gid'])) {
    			$dataUser['gid'] = 0;
    		}
    		
    		if (!$dataUser['gid']) {
    			// seteamos un rol temporal
    			$gid = Exj::GetValueCfg('ugidTempUndefined');
    			$dataUser['gid'] = $gid;
    			$dataChanged->gid = $gid;
    		}
    	}
    	
    	
    	$userJoomla->bind($dataUser);
    	$errorMsg = $userJoomla->getError();
    	if ($errorMsg) {
    		$response->setMsgError($errorMsg."<br/>Ref: Usuario Joomla.");
    		return false;
    	}
    	
    	$userJoomla->save(($idUserJoomla > 0 ? true:false));
    	$errorMsg = $userJoomla->getError();
    	if ($errorMsg) {
    		$response->setMsgError($errorMsg."<br/>Ref: Usuario Joomla.");
    		return false;
    	}
    	
    	if ($isNew) {
    		$dataChanged->id_user = $userJoomla->id;
    		if (!$dataChanged->id_user) {
    			$response->setMsgError("No se pudo crear usuario, razón desconocida!");
    			return false;
    		}
    	}
    	
    //	print_r($userJoomla);
    	
    //	$response->setMsgError("No está implementado Update!");
   // 	return false;
    		
    	return true;
    }
    
    private static function _GetValue($obj, $name, $defaultValue = null){
    	if (!isset($obj->$name)) {
    		return $defaultValue;
    	}
    	
    	return $obj->$name;
    }
    
    

    static function LoadListRolUsers(ExjResponse &$response, &$items, &$total, $paramsCriteria=null, $onlyUsersActives=false)
    {    	    	
    	return AppRolUsersData::LoadListRolUsers($response, $items, $total, $paramsCriteria, $onlyUsersActives);
    }
    
  	static function LoadListRolUsersActives(ExjResponse &$response, &$items, &$total, $paramsCriteria=null)
    {    	    	
    	return AppRolUsersData::LoadListRolUsersActives($response, $items, $total, $paramsCriteria);
    }    
    
    static function loadLookupJUsers(&$items, &$total, $onlyActives=true, $exceptSuperAdmin=true) {
    	
    	return AppRolUsersData::loadLookupJUsers($items, $total, $onlyActives, $exceptSuperAdmin);
    }
    
    static function LoadListRolUnassignedUsers(ExjResponse &$response, &$items, &$total, $paramsCriteria){
    	    	
    	return AppRolUsersData::LoadListRolUnassignedUsers($response, $items, $total, $paramsCriteria);
    }
    
    static function DeleteUserSys(ExjResponse &$response, $id_sys_user){
    	
    	
    	$userSystem = new AppRolUserEditableModel(false, $response);
    	$userSystem->enableTransactionOnDestroy(true);
    	$userSystem->setValueId($id_sys_user);
    	$userSystem->destroy($id_sys_user);
    	
    	if ($userSystem->haveBrokenRules()) {
    		$response = $userSystem->validateResponse();
    		return false;
    	}
    	
    	$response->setMsgInfo("Usuario eliminado satisfactoriamente.");
    	
    	return true;
    }
    
    
    static function AddUserUnassigned(ExjResponse &$response, $id_user, $jusr_gid){
    	
    	$userx = JUser::getInstance($id_user);
    	if (!$userx->id) {
    		$response->setMsgInfo("Not found user!");
    		return false;
    	}
    	
    	if ($userx->gid != $jusr_gid) {
			$db =& JFactory::getDBO();
			$gid = $jusr_gid;
	
			$query = 'SELECT name'
			. ' FROM #__core_acl_aro_groups'
			. ' WHERE id = ' . (int) $gid
			;
			$db->setQuery( $query );
			$userx->set('usertype', $db->loadResult());
			$userx->set('gid', $jusr_gid);
    		
    //		$response->setMsgInfo("User was assigned and changed the user role");
    	}
    	
    	$userx->set('block', 0);
    	
    	if (!$userx->save(true)) {
    		$response->setMsgError($userx->getError());
    		return false;
    	}
    	
    	// 	print_r($userx);
    	
    	
    	// adicionar la empresa actual al usuario
    	// consultar si ya esta agregado, aunque esto no seria necesario ya se supone que ese usuario no esta asginado
    	// pero se hace el control porque otro usuario puede hacer el mismo proceso
    	$idOfficeCurrent = ExjUser::GetIdEmpresa();
    	$infoUserOfc = null;
    	if (!AppRolUsersData::LoadInfoUserOffice($infoUserOfc, $id_user, $idOfficeCurrent)) {
    		$response->setMsgError("No se pudo cargar informacion de usuario-empresa.");
    		return false;
    	}
    	
    	if (!$infoUserOfc) {
    		$response->setMsgError("No se encontró información del usuario.<br/>Verifique si ha sido eliminado!");
    		return false;
    	}
    	
		if ($infoUserOfc->id_ofc_assigned == $idOfficeCurrent) {
			// esta asignado
			$response->setMsgNotify(
                "Usuario asignado satisfactoriamente.<br/>Empresa ya estubo asignada!"
            );
			return true;
		}
		
		$id_sys_user = $infoUserOfc->id_sys_user;
		
		// $infoUserOfc->id_ofc_usr_current
    	// adicionar relacion al usuario la empresa actual
    	
    	$userSystem = new AppRolUserEditableModel(false, $response);
    	$userSystem->setValueId($id_sys_user);
    	$userSystem->id_empresa = $idOfficeCurrent;
    	$userSystem->addOfficeToUser($idOfficeCurrent);
    	$userSystem->save();
    	
    	$response = $userSystem->validateResponse();
    	
    	if ($response->haveMsgError()) {
    		return false;
    	}
    	
    	$response->setMsgNotify(
            "Usuario asignado satisfactoriamente."
        );
    
    	return true;
    }
    
    static function UnassignerUser(ExjResponse &$response, $id_sys_user){
    	if (!$id_sys_user) {
    		$response->setMsgError("No se indicó ID user!");
    		return false;
    	}
    	    	
    //	AppRolUsersData::LoadListRolUsers()    	
    //	echo "id_sys_user: $id_sys_user";
    	
    	$rolUser = new AppRolUserEditableModel(false, $response);
    	$rolUser->setValueId($id_sys_user);
    	$rolUser->removeOfficeAssignation();
    	
    	$response = $rolUser->validateResponse();
    	if ($response->haveMsgError()) {
    		return false;
    	}
    	
    	$response->setMsgInfo("Al Usuario se le quitó la asignación del rol.");
    }
}

?>