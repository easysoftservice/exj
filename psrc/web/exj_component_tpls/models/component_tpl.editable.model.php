<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo Editable para {labelComponent}
 *
 */
class AppComponentTplEditableModel extends ExjEditableModel {
	/*editable.protected.fields*/
	/*editable.const.fields*/
	/*editable.public.fields*/
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = '{name_table}';
		$fieldKey = 'id_field_key';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		/*editable.registerFields*/
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		/*editable.registerControlUIs*/
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		/*editable.applyValidations*/
	}	

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}
    	
    	/*editable.canDestroyRelationTable*/
		
    	return true;
    }
    
    
    /**
     * overwrited. Antes Guardar
     *
     * @return bool
     */
    protected function beforeSave(){

    	/*editable.canSaveCodeUnique*/
    	
    	return true;
    }
	
}

?>