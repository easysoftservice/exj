<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );
	
/**
 * Helper UI para Contribuyentes SRI
 * @author: BYRON VINICIO CORDOVA MORA
 */
class AppSriContribuyentesUIHelper {
	
	/**
	 * ComboBox de Contribuyentes SRI
	 *
	 * @param string $name. No es requerido, por defecto: id_contribuyente
	 * @param string $fieldLabel. No es requerido, por defecto: Contribuyente SRI
	 * @return ExjUIComboBox
	 */
	public static function NewComboSimpleSriContribuyentes($name='id_contribuyente', $fieldLabel = 'Contribuyente SRI'){
		// Exj::IncludeClass('AppSriContribuyentesData', 'exj_sri_contribuyentes');
		
    	$data = null;
    	AppSriContribuyentesData::LoadLookupSriContribuyentes($data);
    
    	$fieldExtras = array();
		 $fieldExtras[] = ExjUI::NewFieldInt('id_act_eco');
		 $fieldExtras[] = ExjUI::NewFieldString('razon_social');
		 $fieldExtras[] = ExjUI::NewFieldString('nombre_comercial');
		 $fieldExtras[] = ExjUI::NewFieldString('estado_contribuyente');
		 $fieldExtras[] = ExjUI::NewFieldString('clase_contribuyente');
		 $fieldExtras[] = ExjUI::NewFieldDate('fecha_inicio_actividades');
		 $fieldExtras[] = ExjUI::NewFieldDate('fecha_actualizacion');
		 $fieldExtras[] = ExjUI::NewFieldDate('fecha_suspension_definitiva');
		 $fieldExtras[] = ExjUI::NewFieldDate('fecha_reinicio_actividades');
		 $fieldExtras[] = ExjUI::NewFieldInt('obligado');
		 $fieldExtras[] = ExjUI::NewFieldString('tipo_contribuyente');
		 $fieldExtras[] = ExjUI::NewFieldInt('numero_establecimiento');
		 $fieldExtras[] = ExjUI::NewFieldString('nombre_fantasia_comercial');
		 $fieldExtras[] = ExjUI::NewFieldString('calle');
		 $fieldExtras[] = ExjUI::NewFieldString('numero');
		 $fieldExtras[] = ExjUI::NewFieldString('interseccion');
		 $fieldExtras[] = ExjUI::NewFieldString('estado_establecimiento');
		 $fieldExtras[] = ExjUI::NewFieldString('descripcion_provincia');
		 $fieldExtras[] = ExjUI::NewFieldString('descripcion_canton');
		 $fieldExtras[] = ExjUI::NewFieldString('descripcion_parroquia');	
		
    	$combo = ExjUI::NewComboSimple($name, $fieldLabel, $data, $fieldExtras);
    	$combo->setAnchor('99%');
		$combo->forceSelection = true;
		
    	return $combo;
	}
}

?>