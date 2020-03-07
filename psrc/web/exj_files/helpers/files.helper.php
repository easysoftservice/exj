<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Archivo
 * Autor: Byron Córdova
 */
class AppFilesUIHelper {
	
	
	static function NewComboSimpleTipos($name='id_file_type', $fieldLabel='Type'){
		$items = AppFilesData::loadLookupTipos();
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('name_type_file');
		$fieldExtras[] = ExjUI::NewFieldInt('size_max_bytes');
		$fieldExtras[] = ExjUI::NewFieldString('module_allow');
		$fieldExtras[] = ExjUI::NewFieldString('cat_type_file');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text}<span>{cat_type_file}</span></h3>';
		$tplContent[] = '<p>Maximum: {size_max_bytes} bytes</p>';
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}

	static function NewComboSimpleModulos($name='module_allow', $fieldLabel='Module'){
		$items = AppFilesData::loadLookupModulos();
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldInt('num_tipos');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras, 'Select ...', false);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text}</h3>';
		$tplContent[] = '<div>Types: {num_tipos}</div>';
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
	
}

?>