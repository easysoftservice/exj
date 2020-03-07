<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Roles del Usuario
 * Autor: Byron Córdova
 */
class AppRolsUIHelper {
	
	static function NewComboSimpleRolsCriteria($name='gid', $fieldLabel='Rol'){
		global $exj;
		// $exj->includeDataCustom('rols', 'exj_rols');
		
		$items = AppRolsData::GetLookupRols();
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('code_rol');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text} <span>{code_rol}</span></h3>';
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
}

?>