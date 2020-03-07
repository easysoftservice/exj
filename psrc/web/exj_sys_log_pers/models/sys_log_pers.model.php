<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysLogPersModel
 * Modelo para Usuario
 */
class AppSysLogPersModel extends ExjModel {
	
    static function saveSysUser($id, $dataChanged, $id_persona, $paramData) {
    	global $exj;
    	
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
	    	
			
			$sysLog = new AppSysLogPerEditableModel(false);
			if ($sysLog->bind($dataChanged)) {
				$sysLog->setValueId($id);
				if ($id_persona) {
					$sysLog->id_persona = $id_persona;
				}
				
				if ($paramData && $id_persona) {
					$paramData->id_persona = $sysLog->id_persona;
				}
				$sysLog->setParams($paramData);
				
				if (!$sysLog->save()) {
					// NOTE: La entidad ya hace roolback
					return $sysLog->validateResponse();
				}
			}
    		
    		ExjDBTrx::Commit();
    		return $sysLog->validateResponse();
    	}
    	catch (Exception $ex){
    		Exj::SetErrorException($ex);
    		ExjDBTrx::Rollback();
    	}
		
		return Exj::GetResponseError();
    }

    public static function LoadListSysLogsItems(ExjResponse &$response, $paramsCriteria) {
    	
    	return AppSysLogPersData::LoadListSysLogsItems($response, $paramsCriteria);
    }
    
    static function loadLookupLogTables(&$items, &$total, $onlyActives=true, $exceptSuperAdmin=true) {
    	
    	return AppSysLogPersData::loadLookupLogTables($items, $total, $onlyActives, $exceptSuperAdmin);
    }
}

?>