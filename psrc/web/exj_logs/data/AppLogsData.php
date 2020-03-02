<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppLogsData
 *
 */
class AppLogsData extends ExjData {
	
	/**
	 * Lista de Logs del Sistema
	 *
	 * @return array de object
	 */
	static function loadListLogs(&$items, &$total, $paramsCriteria=null){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        
        $hLogData = new ExjHandlerLogData();
        
     //   $hLogData->writeLogLn("hola2 jeje " . __METHOD__);
        
        
        $fileLogCriteria = '';
        $criteriaLogs = null;
        if ($paramsCriteria) {
			$criteriaLogs = new AppLogsCriteriaModel(false);
			if ($criteriaLogs->bind($paramsCriteria)) {
				// print_r($criteriaLogs->toObject());
				$fileLogCriteria = $criteriaLogs->fileLog;
				// echo "fileLogCriteria: $fileLogCriteria";
			}
        }

        $items = $hLogData->getItemsLog($fileLogCriteria, $criteriaLogs);
        $total = count($items);
        
        
		/*
		foreach ($items as &$item) {
			$item->tiempoSesion = '';
			if (!$item->time_session) {
				continue;
			}
			$item->tiempoSesion = date("Y-m-d H:i:s", $item->time_session);
		}
		*/

	//	print_r($_SERVER);
    
        return true;
	}
	
	
	
	static function getLookupTipos(){
        $items = ExjError::GetLookupTypeError();
        return $items;
	}

	/**
	 * Lista de Logs
	 *
	 * @param bool $onlyUsersNoAssigned true solo los asginados como usuarios del sistema
	 * @param int $blocked 0 incidica que se mostrarán solo los No bloqueados, null todos
	 * @return array
	 */
	static function getLookupLogs(){
//        $id_company = ExjUser::GetIdCompania();
        
        $hLogData = new ExjHandlerLogData();
        
        $filesLogs = $hLogData->getFilesLogs();
        
        $items = array();
        if (count($filesLogs) == 0) {
        	return $items;
        }
        
        foreach ($filesLogs as $fileLog) {
        	$item = self::newItemLookup($fileLog->name, $fileLog->name);
        	
        	$item->sizeStr = $fileLog->sizeStr;
        	$item->isCurrent = $fileLog->isCurrent;
        	$item->isCurrentStr = $fileLog->isCurrentStr;
        	$item->timeLastChange = $fileLog->timeLastChange;
        	
        	$items[] = $item;
        }
        
        return $items;
	}
	
}

?>