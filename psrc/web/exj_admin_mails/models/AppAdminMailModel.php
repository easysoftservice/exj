<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppAdminMailModel
 * Modelo para Correo
 */
class AppAdminMailModel extends ExjModel {
	
    static function saveCorreo($id, &$dataChanged) {
		$correo = new AppMailEditableModel(false);
		if ($correo->bind($dataChanged)) {
			$correo->setValueId($id);
			
			$correo->save();
			if (!$correo->haveBrokenRules()) {
				$dataChanged->id_mail = $correo->getId();
			}
		}
		
		return $correo->validateResponse();
    }
    
    static function savePlantila($id, &$dataChanged) {
		$plantilla = new AppMailTplEditableModel(false);
		if ($plantilla->bind($dataChanged)) {
			$plantilla->setValueId($id);
			
			if ($plantilla->save()) {
				$dataChanged->id_mail_tpl = $plantilla->getId();
			}
		}
		
		return $plantilla->validateResponse();
    }
    

    static function loadListCorreos(&$items, &$total, $paramsCriteria=null) {
    	return AppAdminMailsData::loadListCorreos($items, $total, $paramsCriteria);
    }

    static function loadListPlantillas(&$items, &$total, $paramsCriteria=null) {
    	return AppMailTplsData::loadListPlantillas($items, $total, $paramsCriteria);
    }

    static function loadListVariables(&$items, &$total) {
    	$items = AppMailVarHelper::GetVarsList(true);
    	$total = count($items);
    	
    	return (!Exj::GetError()->haveError());
    }
    
}

?>