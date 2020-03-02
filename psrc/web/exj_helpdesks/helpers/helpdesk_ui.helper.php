<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Helpdesk
 * Autor: Byron Córdova
 */
class AppHelpdeskUIHelper {
	
	static function NewComboSimpleHelpdesks($name='id_helpdesk', $fieldLabel='Mesa de Ayuda'){
		global $exj;
		// $exj->includeDataCustom('helpdesks', 'exj_helpdesks');
		
		$items = AppHelpdesksData::getLookupHelpdesks();
		
		$fieldsExtras = array();
		$fieldsExtras[] = ExjUI::NewField('description');
		$fieldsExtras[] = ExjUI::NewField('color_hld');
		$fieldsExtras[] = ExjUI::NewFieldInt('is_default_hld');
		$fieldsExtras[] = ExjUI::NewFieldInt('id_hld_catalog_hld');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, $fieldsExtras);
		$combo->anchor = '90%';
		$combo->forceSelection = true;

		$tplContent = array();
		$tplContent[] = '<h3 style="color:{color_hld};">{text}</h3>';
		$tplContent[] = '{description}';
		
		$combo->setTplContentItemSelector($tplContent);
		
		return $combo;
	}
	
	static function getFieldsExtrasForCatalog(){
		$fieldsExtras = array();
		$fieldsExtras[] = ExjUI::NewField('description');
		$fieldsExtras[] = ExjUI::NewField('sample');
		$fieldsExtras[] = ExjUI::NewField('css');
		$fieldsExtras[] = ExjUI::NewField('color');
		
		return $fieldsExtras;
	}
	
	static function applyComboForCatalog(&$combo){
		$combo->anchor = '99%';
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3 style="color:{color};">{text}</h3>';
		$tplContent[] = '{description}';
		// $tplContent[] = '<p>{sample}</p>';
		
		$combo->setTplContentItemSelector($tplContent);
	}

	static function NewComboSimpleEstados($name='id_hld_catalog_state', $fieldLabel='Estado'){
		global $exj;
		// $exj->includeDataCustom('helpdesks', 'exj_helpdesks');
		
		$items = AppHelpdesksData::getLookupEstados();
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, self::getFieldsExtrasForCatalog());
		self::applyComboForCatalog($combo);
		
		return $combo;
	}
	
	static function NewComboSimplePrioridades($name='id_hld_catalog_priority', $fieldLabel='Prioridad'){
		global $exj;
		// $exj->includeDataCustom('helpdesks', 'exj_helpdesks');
		
		$items = AppHelpdesksData::getLookupPrioridades();
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, self::getFieldsExtrasForCatalog());
		self::applyComboForCatalog($combo);
		
		return $combo;
	}

	static function NewComboSimpleResponses($name='id_hld_catalog_response', $fieldLabel='Respuestas'){
		global $exj;
		// $exj->includeDataCustom('helpdesks', 'exj_helpdesks');
		
		$items = AppHelpdesksData::getLookupRespuestas();
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, self::getFieldsExtrasForCatalog());
		self::applyComboForCatalog($combo);
		
		return $combo;
	}
	
	
}
?>