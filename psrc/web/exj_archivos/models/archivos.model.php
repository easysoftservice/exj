<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppArchivoModel
 * Modelo para Archivo
 */
class AppArchivoModel extends ExjModel {
	
    static function saveArchivo(&$id, $dataChanged) {
    	global $exj;
    	
		// $exj->includeModelEditable('archivo', 'exj_archivos');
		$archivo = new AppArchivoEditableModel(false);
		if ($archivo->bind($dataChanged)) {
			$archivo->setValueId($id);
			
			if (!$archivo->save()) {
				return $archivo->validateResponse();
			}
			
			$id = $archivo->id;
		}
		
		return $archivo->validateResponse();
    }

    static function loadListArchivos(&$items, &$total, $paramsCriteria=null) {
    	global $exj;
    	// $exj->includeData();
    	
    	return AppArchivosData::loadListArchivos($items, $total, $paramsCriteria);
    }
    
    
}

?>