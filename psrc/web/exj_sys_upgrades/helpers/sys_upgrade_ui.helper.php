<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Actualizaciones del Sistema
 * Autor: Byron Córdova
 */
class AppSysUpgradeUIHelper {
	
	static function NewComboSimpleEstados($name='state_upg', $fieldLabel='Estado'){
		global $exj;
		// $exj->includeDataCustom('sys_upgrades', 'exj_sys_upgrades');
		
		$items = AppSysUpgradesData::getLookupEstados();

		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('color');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;

		$tplContent = array();
		$tplContent[] = '<h3 style="color:{color}">{text}</h3>';
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
	
	static function NewComboSimpleVersiones($name='version_upg', $fieldLabel='Versión'){
		global $exj;
		// $exj->includeDataCustom('sys_upgrades', 'exj_sys_upgrades');
		
		$items = AppSysUpgradesData::getLookupVersiones();
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('file_zip_code');
		$fieldExtras[] = ExjUI::NewFieldString('file_zip_sql');
		$fieldExtras[] = ExjUI::NewFieldInt('state_upg');
		
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text} <span>{state_upg}</span></h3>';
		$tplContent[] = "{file_zip_code} {file_zip_sql}";
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
	
}

?>