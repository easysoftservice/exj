<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpModel
 * Modelo para Help
 */
class AppHelpModel extends ExjModel {
	
    static function saveAyuda($id, $dataChanged, $is_module=null) {
    	global $exj;
    	
		// $exj->includeModelEditable('help');
		$help = new AppHelpEditableModel(false);
		if ($help->bind($dataChanged)) {
			$help->setValueId($id);
			if ($is_module !== null) {
				$help->is_module = $is_module;
			}
			
			$help->save();
		}
		
		return $help->validateResponse();
    }

    static function loadListAyudas(&$items, $paramsCriteria=null) {
    	global $exj;
    	// $exj->includeData();
    	
    	return AppHelpsData::loadListAyudas($items, $paramsCriteria);
    }
    
    static function loadHelpCmp(&$dataFormated, $nameCmp, $format){
    	global $exj;
    	// $exj->includeData();
    	
    	$dataHelpCmp = AppHelpsData::getDataHelpCmp($nameCmp);
    	if ($dataHelpCmp === false) {
    		return false;
    	}
    	
		$dataFormated = null;
    	if (count($dataHelpCmp->items) == 0) {
    		return true;
    	}
    	
    	// se retorna la data formateada
    	/*
    	foreach ($dataHelpCmp->items as $item) {
    		if (isset($item->arts) && count($item->arts) > 0) {
    			
    		}
    		
    	}
    	*/
    	$dataFormated = $dataHelpCmp;
    	
/*
    	$dataFormated = new stdClass();
    	$dataFormated->test1 = 'hola';
    	$dataFormated->test2 = 123;
  */  	
    	return true;
    }
    
}

?>