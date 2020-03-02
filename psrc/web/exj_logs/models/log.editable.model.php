<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppLogEditableModel
 */
class AppLogEditableModel extends ExjEditableModel {
	public $idLog;

	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_logs';
		$fieldKey = 'id_log';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('idLog', 'Id Log');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    // 	$this->registerControlUI($this->_comboLogs());
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		
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
    	
    	$this->addBrokenRuler("No soportado eliminación de logs");
		
    	return false;
    }
    

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	return $this->_canSave();
    }
    private function _canSave(){
		$this->addBrokenRuler("No soportado guardado de logs");
		return false;
    }
	
}

?>