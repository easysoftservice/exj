<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para Correo
 * Autor: Byron Córdova
 */
class AppAdminMailUIHelper {
	const MAIL_TIPO_INVITACION = 'INVITACION';
	const MAIL_TIPO_REPORTE = 'REPORTE';
	const MAIL_TIPO_NOTIFICACION = 'NOTIFICACION';
	
	static function NewComboSimpleEstados($name='state_mail', $fieldLabel='Estados'){		
		$items = array();
		$items[] = ExjUI::NewItemLookup('PENDIENTE', 'PENDIENTE');
		$items[] = ExjUI::NewItemLookup('ENVIADO', 'ENVIADO');
		$items[] = ExjUI::NewItemLookup('FALLIDO', 'FALLIDO');
		
//		$fieldExtras = array();
//		$fieldExtras[] = ExjUI::NewFieldInt('is_html');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, null, 'Seleccione...', false);
//		$combo->width = 210;
		$combo->anchor = '99%';
		$combo->forceSelection = true;
		
		return $combo;
	}
	
	

	static function NewComboSimplePlantillasReporte($name='id_mail_tpl', $fieldLabel='Plantilla'){
		return self::NewComboSimplePlantillas($name, $fieldLabel, self::MAIL_TIPO_REPORTE);
	}
	
	static function NewComboSimplePlantillas($name='id_mail_tpl', $fieldLabel='Plantilla', $type_tpl=''){
		
		$items = AppMailTplsData::getLookupPlantillas($type_tpl);
		
		$fieldExtras = array();
		$fieldExtras[] = ExjUI::NewFieldString('subject_default');
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, null, 'Seleccione...', false);
//		$combo->width = 210;
		$combo->anchor = '99%';
		$combo->forceSelection = true;
		
		if ($items && count($items) == 1) {
			$combo->value = $items[0]->value;
		}
		
		return $combo;
	}

	/**
	 * Combobox pra tipo de plantilla
	 *
	 * @param string $name
	 * @param string $fieldLabel
	 * @return object
	 */
	static function NewComboSimpleTplTipos($name='type_tpl', $fieldLabel='Tipo'){
		$items = array();
		$items[] = ExjUI::NewItemLookup(self::MAIL_TIPO_INVITACION, 'INVITACION');
		$items[] = ExjUI::NewItemLookup(self::MAIL_TIPO_NOTIFICACION, 'NOTIFICACION');
		$items[] = ExjUI::NewItemLookup(self::MAIL_TIPO_REPORTE, 'REPORTE');
		
		
		$combo = ExjUI::NewComboSimple($name, $fieldLabel, $items, null, 'Seleccione...', false);
//		$combo->width = 210;
		$combo->anchor = '99%';
		$combo->forceSelection = true;
		
		return $combo;
	}
	
}

?>