<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppCouParamEditableModel
 * Parametros del pais actual
 */
class AppCouParamEditableModel extends ExjEditableModel {
	public $id_pais;
	public $offset_time;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'app_loc_paises';
		$fieldKey = 'id_pais';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('offset_time', 'Compensación de la fecha y hora del País');
	}

	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		global $exj;
		
		$compesacion = ExjUser::GetOffsetTime();
		$tiempoActual = $exj->getTime();
		$minValue = $exj->getTime($compesacion-3000);
		$maxValue = $exj->getTime($compesacion+3000);
		
		$tifHora = ExjUI::NewTimeField('offset_time', 'Nueva Hora', $minValue, $maxValue, 3);
		$tifHora->tiempoActual = $tiempoActual;
		$tifHora->tiempoSrv = $exj->getTime(0);
		$tifHora->value = $tiempoActual;
		
	//	echo " tiempoActual: $tiempoActual minValue: $minValue maxValue: $maxValue";
		
    	$this->registerControlUI($tifHora);
	}

	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
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
    	if (!$this->isSettedField('offset_time')) {
    		return true;
    	}
    	
		if (!is_numeric($this->offset_time)) {
			$this->addBrokenRuler("Compesación de tiempo no es un valor numérico");
			return false;
		}
    	
		return true;
    }

    /**
     * overwrited. Después de Guardar
     *
     * @param obj $responseData
     */
    public function afterSave(&$responseData){
    	if ($this->isSettedField('offset_time')) {
			ExjUser::SetOffsetTime($this->offset_time);
    	}
    }
}

?>