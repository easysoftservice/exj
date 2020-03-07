<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUserModel
 * Modelo para Usuario
 */
class AppSysUserModel extends ExjModel {
	
    static function saveSysUser($id, $dataChanged, $id_persona, $paramData) {
    	
    	try {
    		ExjDBTrx::Start();
    		
			$persona = new AppPersonaEditableModel(false);
			if ($persona->bind($dataChanged)) {
				$persona->setValueId($id_persona);
				
				if (!$persona->save()) {
					return $persona->validateResponse();
				}
				
				$id_persona = $persona->id;
			}
	    	
			$sysUser = new AppSysUserEditableModel(false);
			if ($sysUser->bind($dataChanged)) {
				$sysUser->setValueId($id);
				if ($id_persona) {
					$sysUser->id_persona = $id_persona;
				}
				
				if ($paramData && $id_persona) {
					$paramData->id_persona = $sysUser->id_persona;
				}
				$sysUser->setParams($paramData);
				
				if (!$sysUser->save()) {
					// NOTE: La entidad ya hace roolback
					return $sysUser->validateResponse();
				}
			}
    		
    		ExjDBTrx::Commit();
    		return $sysUser->validateResponse();
    	}
    	catch (Exception $ex){
    		Exj::SetErrorException($ex);
    		ExjDBTrx::Rollback();
    	}
		
		return Exj::GetResponseError();
    }

    static function loadListSysUsers(&$items, &$total, $paramsCriteria=null) {
    	
    	return AppSysUsersData::loadListSysUsers($items, $total, $paramsCriteria);
    }
    
    static function loadLookupJUsers(&$items, &$total, $onlyActives=true, $exceptSuperAdmin=true)
    {
    	
    	return AppSysUsersData::loadLookupJUsers($items, $total, $onlyActives, $exceptSuperAdmin);
    }
}

?>