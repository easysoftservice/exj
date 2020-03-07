<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Usuarios del Sistema
 * Autor: Byron Córdova
 */
class AppSysUserUIHelper {
	
	public static function newRadioGroupEnableDebug($name='enable_debug', $fieldLabel='Debug'){
		return ExjUI::NewRadioGroupSiNo($name, $fieldLabel, false);
	}
	
	public static function NewComboSimpleTemas($name='sys_type_theme', $fieldLabel='Tema'){
		global $exj;
		// $exj->includeDataCustom('sys_users', 'exj_sys_users');
		
		$items = AppSysUsersData::getLookupTemas();
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, null, 'Seleccione...', false);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		$combo->setWidth(120)->setEditable(false);
		
		return $combo;
	}
	
	public static function NewComboSimpleLangs($name='id_sys_lang', $fieldLabel='Lenguaje'){
		global $exj;
		// $exj->includeDataCustom('sys_users', 'exj_sys_users');
		
		$items = AppSysUsersData::GetLookupLenguajes();
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('acronym_lang');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text} <span>{acronym_lang}</span></h3>';
		
		$combo->setTplContentItemSelector($tplContent);
		$combo->setWidth(180)->setEditable(false);
		
		return $combo;
	}

	public static function NewComboSimpleUsuariosAll($name='id_user', $fieldLabel='Usuario'){
		return self::NewComboSimpleUsuarios($name, $fieldLabel, false, null);
	}
	
	public static function NewComboSimpleUsuarios($name='id_user', $fieldLabel='Usuario', $onlyUsersNoAssigned = true, $blocked = 0){
		global $exj;
		// $exj->includeDataCustom('sys_users', 'exj_sys_users');
		
		$items = AppSysUsersData::getLookupUsuarios($onlyUsersNoAssigned, $blocked);
		
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
	
	static function NewComboPagingUsersJoomla($fieldKey='id_user', $fieldLabel='Usuario del Sistema'){
		$fieldsExtras = array();
		
		$fieldsExtras[] = ExjUI::NewFieldString("username");
		$fieldsExtras[] = ExjUI::NewFieldString("usertype");
				
		$tplContent = array();
		$tplContent[] = '<h3>{text}<span>{username}</span></h3>';
		$tplContent[] = '{usertype}';
		
		$url = Exj::BuildURLModel('sys_users', 'viewLookupJUsers', 'exj_sys_users');
		
    	$cmb = ExjUI::NewComboPaging($fieldKey, $fieldLabel, $url, $fieldsExtras, $tplContent, '- Seleccione -', 360);
    	$cmb->forceSelection = true;
    	$cmb->setAnchor();
//    	$cmb->minChars = 2;
    	
    	$cmb->setAutoBindLoad();
    	
    	return $cmb;
	}
	
	public static function NewComboSimpleSysUsers($usertype, $name='id_sys_user', $fieldLabel='Usuario'){
		// Exj::IncludeClass('AppSysUsersData', 'exj_sys_users');
		
		$items = AppSysUsersData::GetLookupSysUsers($usertype);
		foreach ($items as &$item) {
			if ($item->registerDate) {
				$item->registerDate = date('d/m/Y H:i', strtotime($item->registerDate));
			}
		}
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('name');
		$fieldExtras[] = ExjUI::NewFieldInt('block');
		$fieldExtras[] = ExjUI::NewFieldDateTime('registerDate');
		$fieldExtras[] = ExjUI::NewFieldString('usertype');
		$fieldExtras[] = ExjUI::NewFieldInt('gid');
		$fieldExtras[] = ExjUI::NewFieldString('color');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras, 'Ninguno');
		$combo->anchor = '99%';
		$combo->forceSelection = true;
	//	$combo->setWidthListWidth(150, 210)->setEditable(false);;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text} <span style="color:{color}">{registerDate}</span></h3>';
		$tplContent[] = "{name}";
		
		$combo->setTplContentItemSelector($tplContent);

		
		return $combo;
	}

}

?>