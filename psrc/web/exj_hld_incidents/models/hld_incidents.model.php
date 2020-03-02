<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncidentModel
 * Modelo para HldIncident
 */
class AppHldIncidentModel extends ExjModel {
	
    static function saveIncident($id, $dataChanged, $paramData) {
		// Exj::IncludeClass('AppHldIncidentEditableModel', 'exj_hld_incidents');

		$hldIncidente = new AppHldIncidentEditableModel(false);
		if ($hldIncidente->bind($dataChanged)) {
			$hldIncidente->setParams($paramData);
			$hldIncidente->setValueId($id);
			
			
			
			$hldIncidente->save();
		}
		
		return $hldIncidente->validateResponse();
    }
    
    static function saveResponseInc($id, $dataChanged, $paramData) {
    	// Exj::IncludeClass('AppHldIncResponseEditableModel', 'exj_hld_incidents');
    	
		$responseInc = new AppHldIncResponseEditableModel(false);
		if ($responseInc->bind($dataChanged)) {
			$responseInc->setValueId($id);
			$responseInc->setParams($paramData);

			if (!$responseInc->isSettedField('id_hld_catalog_state')) {
				$responseInc->id_hld_catalog_state = $responseInc->getParamId('id_hld_catalog_state');
			}
			
			if (!$responseInc->isSettedField('id_hld_incident')) {
				$responseInc->id_hld_incident = $responseInc->getParamId('id_hld_incident');
			}
			
			$responseInc->saveResponse();
		}
		
		return $responseInc->validateResponse();
    }
    
    public static function SaveDocInc($id, $dataChanged, $paramData) {
    	// Exj::IncludeClass('AppHldIncDocEditableModel', 'exj_hld_incidents');
    	
    	$response = new ExjResponse();
    	
		$docInc = new AppHldIncDocEditableModel(false, $response);
		if ($docInc->bind($dataChanged)) {
			$docInc->setParams($paramData);
			$docInc->setValueId($id);

			if (!$docInc->isSettedField('id_hld_incident')) {
				$docInc->id_hld_incident = $docInc->getParamId('id_hld_incident');
			}
			
			$docInc->save();
		}
		
		return $docInc->validateResponse();
    }
    

    static function LoadListMain(&$items, &$total, $paramsCriteria) {
    	// Exj::IncludeClass('AppHldIncidentsData', 'exj_hld_incidents');
    	
    	return AppHldIncidentsData::LoadListMain($items, $total, $paramsCriteria);
    }

    static function LoadListRespuestas(&$items, &$total, $paramsCriteria) {
    	// Exj::IncludeClass('AppHldIncidentsData', 'exj_hld_incidents');
    	
    	return AppHldIncidentsData::LoadListRespuestas($items, $total, $paramsCriteria);
    }
    
    static function loadHelpDesk($id_helpdesk, &$data) {
    	// Exj::IncludeClass('AppHelpdesksData', 'exj_helpdesks');
    	
    	return AppHelpdesksData::loadHelpDesk($id_helpdesk, $data);
    }
    
    public static function LoadListDocs(&$items, &$total, $id_hld_incident) {
    	// Exj::IncludeClass('AppHldIncidentsData', 'exj_hld_incidents');
    	
    	return AppHldIncidentsData::LoadListDocs($items, $total, $id_hld_incident);
    }
}

?>