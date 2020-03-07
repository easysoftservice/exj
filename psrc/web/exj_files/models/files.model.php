<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppFileModel
 * Modelo para Archivo
 */
class AppFileModel extends ExjModel {
	
    static function saveArchivo($id, $dataChanged) {
    	global $exj;
    	
		// $exj->includeModelEditable('file');
		$pais = new AppFileEditableModel(false);
		if ($pais->bind($dataChanged)) {
			$pais->setValueId($id);
			
			$pais->save();
		}
		
		return $pais->validateResponse();
    }

    static function loadListArchivos(&$items, &$total, $paramsCriteria=null) {
    	global $exj;
    	// $exj->includeData('exj_files');
    	
    	return AppFilesData::loadListArchivos($items, $total, $paramsCriteria);
    }
}

?>