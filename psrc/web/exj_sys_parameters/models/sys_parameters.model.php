<?php
/**
 * @class AppSysParametersModel
 */
class AppSysParametersModel extends ExjModel {
	
    static function saveSysParam($id, $dataChanged) {
    	global $exj;
    	
		// $exj->includeModelEditable('sys_parameter');
		$sysParam = new AppSysParameterEditableModel(false);
		if ($sysParam->bind($dataChanged)) {
			$sysParam->setValueId($id);
			
			$sysParam->save();
		}
		
		return $sysParam->validateResponse();
    }
    
    static function saveParamsPais($id, $dataChanged) {
    	global $exj;
    	
    	if (!$id) {
    		Exj::SetErrorValidating("No se indicó el ID del País!");
    		return Exj::GetResponseError();
    	}
    	
    	
		// $exj->includeModelEditable('cou_param');
		$couParam = new AppCouParamEditableModel(false);
		$couParam->setValueId($id);
		
    	if (isset($dataChanged->offset_time)) {
    		$newTime = $dataChanged->offset_time;
    		$timeSrv = $exj->getTime(0);
    		
    		// echo " timeSrv: $timeSrv newTime: $newTime";
    		
    		$numTimeNew = strtotime($newTime);
    		$numTimeSrv = strtotime($timeSrv);
    		
    		$offset_time = ($numTimeNew-$numTimeSrv);
    		
    		$dataChanged->offset_time = $offset_time;
    		
    	//	echo "<br/>numTimeNew: $numTimeNew numTimeSrv $numTimeSrv offset_time: $offset_time";
    	}
		
		if ($couParam->bind($dataChanged)) {
			$couParam->save();
		}
		
		return $couParam->validateResponse();
    }

    

    static function loadListSysParams(&$items, &$total, $paramsCriteria=null) {
    	global $exj;
    	// $exj->includeData();
    	
    	return AppSysParametersData::loadListSysParams($items, $total, $paramsCriteria);
    }
    
}

?>