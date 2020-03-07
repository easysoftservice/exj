<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI Parámetros del Sistema RIDE
 * Autor: Byron Córdova
 */
class AppSysParametersUIHelper {
	const TYPE_STRING = 'string';
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_DATE = 'date';
	const TYPE_DATETIME = 'datetime';
	const TYPE_OBJECT = 'object';
	
	public static function NewTypesComboSimple($anchor='99%', $fixDefaultValue=false, $name='type_param', $fieldLabel = 'Tipo de Dato'){
		global $exj;
		
    	$items = array();
    	$items[] = ExjUI::NewItemLookup(self::TYPE_STRING);
    	$items[] = ExjUI::NewItemLookup(self::TYPE_INT);
    	$items[] = ExjUI::NewItemLookup(self::TYPE_FLOAT);
    	$items[] = ExjUI::NewItemLookup(self::TYPE_DATE);
    	$items[] = ExjUI::NewItemLookup(self::TYPE_DATETIME);
    	$items[] = ExjUI::NewItemLookup(self::TYPE_OBJECT);
		
    	$cfg = ExjUI::NewComboSimple($name, $fieldLabel, $items, null, '- Seleccione -', false);
    	if ($fixDefaultValue) {
    		$cfg->setDefaultValue(self::TYPE_STRING);
    	}
    	$cfg->setAnchor($anchor);
    	$cfg->forceSelection = true;
    	$cfg->setEditable();
    	$cfg->setAutoBindLoad(true, true);
		
    	return $cfg;
	}
}


?>