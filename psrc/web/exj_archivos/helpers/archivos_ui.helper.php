<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Archivo
 * Autor: Byron Córdova
 */
class AppArchivoUIHelper {
	
	static function NewComboSimpleArchivos($name='id_file', $fieldLabel='Archivo'){
		global $exj;
		// $exj->includeDataCustom('archivos', 'exj_archivos');
		
		$items = AppArchivosData::getLookupArchivos();
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('ext_file');
		$fieldExtras[] = ExjUI::NewFieldString('path_file');
		$fieldExtras[] = ExjUI::NewFieldString('name_type_file');
		$fieldExtras[] = ExjUI::NewFieldString('cat_type_file');
		$fieldExtras[] = ExjUI::NewFieldString('size_max_bytes');
		$fieldExtras[] = ExjUI::NewFieldDate('modificado_dt');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->width = 120;
		$combo->forceSelection = true;
		
		return $combo;
	}
}
?>