<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Logs del Sistema
 * Autor: Byron Córdova
 */
class AppLogUIHelper {
	
	public static function NewComboSimpleTipos($name='col7TypeError', $fieldLabel='Tipo'){		
		$items = AppLogsData::getLookupTipos();

		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('color');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3 style="color:{color}">{text}</h3>';
		
		$combo->setTplContentItemSelector($tplContent);
		return $combo;
	}
	
	
	static function NewComboSimpleLogsAll($name='fileLog', $fieldLabel='Log'){		
		$items = AppLogsData::getLookupLogs();
		$valueDefault = 0;
		foreach ($items as $item) {
			if ($item->isCurrent) {
				$valueDefault = $item->value;
			}
		}
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('sizeStr');
		$fieldExtras[] = ExjUI::NewFieldInt('isCurrent');
		$fieldExtras[] = ExjUI::NewFieldString('timeLastChange');
		$fieldExtras[] = ExjUI::NewFieldString('isCurrentStr');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldExtras, 'Seleccione...', false);
		$combo->anchor = '96%';
		$combo->forceSelection = true;
		if ($valueDefault) {
			$combo->value = $valueDefault;
		}
		
		$tplContent = array();
		$tplContent[] = '<h3>{text} <span>{sizeStr}</span></h3>';
		//$tplContent[] = "Modificación: {timeLastChange} Actual: {isCurrentStr}";
		$tplContent[] = "Cambio: {timeLastChange}";
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
	
}

?>