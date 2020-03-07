<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppUsrPerfilModel
 * Modelo para UsrPerfil
 */
class AppUsrPerfilModel extends ExjModel {
	
    public static function SaveUsrPerfil($id, $dataChanged, ExjResponse $response) {
		// Exj::IncludeClass('AppUsrPerfilEditableModel', 'exj_usr_perfil');
		
		$userPerfil = new AppUsrPerfilEditableModel(false, $response);
		if ($userPerfil->bind($dataChanged)) {
			$userPerfil->setValueId($id);
			$userPerfil->enableTransactionOnSave();
			
			$userPerfil->save();
		}
		
		if (!$userPerfil->haveBrokenRules()) {
			$response->setMsgInfo("Datos actualizados safistactoriamente.");
		}
		
		return $userPerfil->validateResponse();
    }

    public static function LoadListMain(&$items, &$total, $paramsCriteria=null) {    	
    	return AppUsrPerfilData::LoadListMain($items, $total, $paramsCriteria);
    }
}

?>