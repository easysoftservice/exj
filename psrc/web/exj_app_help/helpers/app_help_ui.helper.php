<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Help
 * Autor: Byron Córdova
 */
class AppHelpUIHelper {
	
	static function NewComboSimpleHelps($name='id_help', $fieldLabel='Ayuda'){
		global $exj;
		// $exj->includeDataCustom('helps', 'com_ehelps');
		
		$items = AppHelpsData::getLookupHelps();
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldInt('is_module');
		$fieldExtras[] = ExjUI::NewFieldString('url_help');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->width = 120;
		$combo->forceSelection = true;
		
		return $combo;
	}
}
?>