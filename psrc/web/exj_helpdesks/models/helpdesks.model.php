<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpdeskModel
 * Modelo para Mesa de Ayuda
 */
class AppHelpdeskModel extends ExjModel {
	
    public static function saveHelpdesk($id, $dataChanged) {
		// Exj::IncludeClass('AppHelpdeskEditableModel');
		
		$helpDesk = new AppHelpdeskEditableModel(false);
		if ($helpDesk->bind($dataChanged)) {
			$helpDesk->setValueId($id);
			
			$helpDesk->save();
		}
		
		return $helpDesk->validateResponse();
    }

    public static function loadListHelpdesks(&$items, &$total, $paramsCriteria=null) {
    	// Exj::IncludeClass('AppHelpdesksData');
    	
    	return AppHelpdesksData::loadListHelpdesks($items, $total, $paramsCriteria);
    }
}

?>