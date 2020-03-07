<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Rol y Usuarios del Sistema
 * Autor: Byron Córdova
 */
class AppRolUserUIHelper {
	
	static function newRadioGroupEnableDebug($name='enable_debug', $fieldLabel='Debug'){
		return ExjUI::NewRadioGroupSiNo($name, $fieldLabel, false);
	}
	
	static function NewComboSimpleUsuariosAll($name='id_user', $fieldLabel='User'){
		return self::NewComboSimpleUsuarios($name, $fieldLabel, false, null);
	}
	
	static function NewComboSimpleUsuarios($name='id_user', $fieldLabel='User', $onlyUsersNoAssigned = true, $blocked = 0){
		
		$items = AppRolUsersData::getLookupUsuarios($onlyUsersNoAssigned, $blocked);
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('name_usr');
		$fieldExtras[] = ExjUI::NewFieldInt('block');
		$fieldExtras[] = ExjUI::NewFieldDateTime('lastvisit_date');
		$fieldExtras[] = ExjUI::NewFieldString('usertype');
		$fieldExtras[] = ExjUI::NewFieldInt('id_sys_user');
		$fieldExtras[] = ExjUI::NewFieldInt('is_user_free');
		$fieldExtras[] = ExjUI::NewFieldString('color');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text} <span style="color:{color}">{name_usr}</span></h3>';
		$tplContent[] = "{usertype}";
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
}

?>