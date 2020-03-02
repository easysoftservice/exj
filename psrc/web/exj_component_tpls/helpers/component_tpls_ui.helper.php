<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );
	
/**
 * Helper UI para {labelComponents}
 * @author: {name_author}
 */
class AppComponentTplsUIHelper {
	
	/**
	 * ComboBox de {labelComponents}
	 *
	 * @param string $name. No requerido, por defecto: id_field_key
	 * @param string $fieldLabel. No requerido, por defecto: {labelComponent}
	 * @return ExjUIComboBox
	 */
	public static function NewComboSimpleComponentTpls($name='id_field_key', $fieldLabel = '{labelComponent}'){		

    	/*combobox.simple.fieldExtras*/	

    	$combo = ExjUI::NewComboSimpleProxy(
    		$name, 
    		$fieldLabel,
    		Exj::BuildURLProxy('ComponentTpls', 'getLookup'),
    		$fieldExtras
    	);

    	// $combo->setWidth(120);

    	$combo->getStore()->load();
		
    	return $combo;
	}
}

?>