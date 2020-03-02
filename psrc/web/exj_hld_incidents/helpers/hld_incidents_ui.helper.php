<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper UI para HldIncident
 * Autor: Byron Córdova
 */
class AppHldIncidentsUIHelper {
	
	/**
	 * Crea un nuevo objeto para combo simple de Usuarios A asignar
	 *
	 * @param string $anchor. No es requerido, por defecto: 98%
	 * @param string $name. No es requerido, por defecto: id_sys_user_asignado
	 * @param string $fieldLabel. No es requerido, por defecto: Asignar a
	 * @return object. Instancia de: ExjUI::NewComboSimple
	 */
	static function NewUsrAsignarComboSimple($anchor='98%', $name='id_sys_user_asignado', $fieldLabel = 'Asignar a'){
		// Exj::IncludeClass('AppHldIncidentsData', 'exj_hld_incidents');
		
    	$data = null;
    	$total = 0;
    	AppHldIncidentsData::LoadLookupUsrAsignar($data, $total);
    	
    	$fieldExtras = array();
    	$fieldExtras[] = ExjUI::NewFieldString('username');
    	$fieldExtras[] = ExjUI::NewFieldString('email');
    	$fieldExtras[] = ExjUI::NewFieldString('usertype');
    	$fieldExtras[] = ExjUI::NewFieldString('cod_empresa');
		
    	$cfg = ExjUI::NewComboSimple($name, $fieldLabel, $data, $fieldExtras);
    	$cfg->setAnchor($anchor);
    	
    	$tplContent = array();
        $tplContent[] = '<h3>{text}<span>{usertype}</span></h3>';
    	
    	ExjUI::applyComboTemplateToItemSelector($cfg, $tplContent);
    	
    	return $cfg;
	}
	
	
}

?>