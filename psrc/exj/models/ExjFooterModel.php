<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjFooterModel
 * Clase base para Modelo de footer
 */
class ExjFooterModel extends ExjModels {
	private $_addControlesUI = true;
	private $_titleFormPanel = '';
	private $_disabledDefault = true;
	
	public function __construct($addControlesUI = true){
		$this->_addControlesUI = $addControlesUI;
		
	//	parent::__construct($addControlesUI);
		
		$this->footerInit($this->_addControlesUI, $this->_titleFormPanel);
		
		$this->footerRegisterFields();
		
		if ($this->_addControlesUI) {
			$this->footerRegisterControlsUI();
		}
		
		$this->registerRules();
		
		$this->footerLoadFields($this->_addControlesUI);
		$this->_setterControlsUI();
	}
	
	private function _setterControlsUI(){
		if (!$this->_addControlesUI) {
			return ;
		}
		
		$fields = $this->getFields(true);
		if (count($fields) == 0) {
			return ;
		}
		
		foreach ($fields as $field) {
			$nameField = $field->getName();
			
			if (!isset($this->$nameField)) {
				return ;
			}
			
			$value = $this->$nameField;
			$this->setValueControlUI($nameField, $value);
		}
	}
	
	
	/**
	 * overwrite. Inicio del Modelo Footer
	 *
	 */
	protected function footerInit(&$addControlesUI, &$titleFormPanel){
		
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function footerRegisterFields(){
		
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function footerRegisterControlsUI(){
		
	}
	
	protected function footerLoadFields($addControlesUI){
		
	}
	
	protected function getFormPanelFooter($withButtonReset = true){
		$cfg = ExjUI::NewFormPanel($this->_titleFormPanel);
		
		/*
		if ($this->_titleFormPanel){
			$cfg->collapsible = true;
			$cfg->titleCollapse = true;
		}
		*/
		

		return $cfg;
	}
	
    /**
     * Registra control para la ui.
     *
     * @param object $controlUI
     * @param string $nameField. No Requerido sino se lo define, se lo calcula desde el $controlUI
     */
    public function registerControlUI($controlUI, $nameField=''){
    	if ($this->_disabledDefault) {
    		if (!isset($controlUI->disabled)) {
    			$controlUI->disabled = true;
    		}
    	}
    	
    	return parent::registerControlUI($controlUI, $nameField);
    }
	

	/**
	 * overrride. Devuelve la ui de la footer
	 *
	 * @return object Estructura: cfgFormPanel, items
	 */
	public function to_ui(){
		// $this->controlsUI_enableKeyEvents();
		
		$ui = parent::to_ui();
		
		$ui->cfgFormPanel = $this->getFormPanelFooter();
		
		return $ui;
	}
	

	/**
	 * override. Registra campo de tipo entero
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isRequired
	 * @param bool $isNullable
	 * @param bool $allowZero
	 * @return ExjField Instancia
	 */
	public function registerFieldId($name, $alias='', $isRequired=false, $isNullable=false, $allowZero=true){
		return parent::registerFieldId($name, $alias, $isNullable, $isRequired, $allowZero);
	}

	/**
	 * override. Registra campo de tipo string
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isRequired
	 * @param bool $isNullable
	 * @return ExjField Instancia
	 */
	public function registerFieldString($name, $alias='', $isRequired=false, $isNullable=false){
		return parent::registerFieldString($name, $alias, $isNullable, $isRequired);
	}
	
	/**
	 * override. Registra campo de tipo date año-mes-dia
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isRequired
	 * @param bool $isNullable
	 * @return ExjField Instancia
	 */
	public function registerFieldDate($name, $alias='', $isRequired=false, $isNullable=false){
		return parent::registerFieldDate($name, $alias, $isNullable, $isRequired);
	}
	
	public function registerFieldFloat($name, $alias='', $isNullable=true, $isRequired=false, $allowZero=true){
		return parent::registerFieldFloat($name, $alias, $isNullable, $isRequired, $allowZero);
	}
	
	
	/**
	 * overwrited. bind
	 *
	 * @param object $data
	 * @return int Nro bindeados
	 */
    public function bind($data='') {
    	$nBinds = parent::bind($data);
    	if ($this->haveBrokenRules()) {
    		return $nBinds;
    	}
    	
    	$fields =  $this->getFields();
    	foreach ($fields as $field) {
    		$name = $field->getName();
    		if (!isset($this->$name)) {
    			continue;
    		}
    		if (!$field->isRequired) {
    			continue;
    		}
    		
    		$valueRaw = $this->$name;
    		if (!self::IsSettedValue($valueRaw)) {
    			// $this->addBrokenRuler($field->alias . ' no se ha seteado y es requerido.');
    			// $this->addBrokenRuler('El campo <b>'.$field->alias . '</b> es requerido.');
    			continue;
    		}
    		
    		$value = $valueRaw;
    		if ($field->isInt() || $field->isFloat()) {
	    		if ($field->isInt()) {
	    			$value = intval($value);
	    		}
	    		elseif ($field->isFloat()){
	    			$value = floatval($value);
	    		}
	    		
	    		if (is_nan($value)) {
	    			$this->addBrokenRuler($field->alias . " tiene el valor: <b>$valueRaw</b> este no es un valor numérico.");
	    			continue;
	    		}
    		}
    		if ($field->isAllowZero && $value == 0) {
    			continue;
    		}
    		
    		if ($value) {
    			continue;
    		}
    		
    		/*
    		if (!$value) {
    			$this->addBrokenRuler($field->alias . ' es requerido.');
    			continue;
    		}
    		*/
    	}
    	
    	return $nBinds;
    }

	
	/**
	 * Adiciona condiciones al objeto para consultas
	 *
	 * @param ExjDBQuery $dbQuery
	 * @return int Número de condiciones adicionadas, false si hay error
	 */
	public function addConditionsQuery(&$dbQuery){
		if (!($dbQuery instanceof ExjDBQuery)) {
			Exj::SetErrorValidating("Se ha llamado a " . __FUNCTION__. ' y se ha enviado parámetro de tipo no soportado');
			return false;
		}
		
		$numAdd = 0;
		$fieldsAdded = array(); // campos adicinados
		$fields = $this->getFields(true);
		foreach ($fields as $field) {
			$nameField = $field->getName();
//				echo "<br/>Probando campo: $nameField. ";
			
			if (!isset($this->$nameField)) {
//				echo "No está definido";
				continue;
			}
			
			$valueField = $this->$nameField;
			if (!$valueField) {
	//			echo "No tiene valor";
				continue;
			}
			
			if (in_array($nameField, $fieldsAdded)) {
				continue;
			}
			
		//	echo "<br/>ADICIONANDO A WHERE DEL QUERY";
			$aliasTable = $dbQuery->getAliasTableFromField($nameField);
			$nameFieldRaw = $nameField;
			if ($aliasTable) {
				$nameField = "$aliasTable.$nameField";
			}
			
			if ($field->isDate() || $field->isDateTime()) {
			//	echo "<br/>Es tipo fecha. Campo: $nameFieldRaw";
				$nameFieldFrom = '';
				$nameFieldUntil = '';
				if (isset($field->endDateField) && $field->endDateField) {
					$nameFieldUntil = $field->endDateField;
					$nameFieldFrom = $nameFieldRaw;
					
					$fieldsAdded[] = $nameFieldUntil;
				}
				if (isset($field->startDateField) && $field->startDateField) {
					$nameFieldFrom = $field->startDateField;
					$nameFieldUntil = $nameFieldRaw;	
					
					$fieldsAdded[] = $nameFieldFrom;
				}

				/*
				echo "<br/>1. nameFieldFrom: $nameFieldFrom nameFieldUntil: $nameFieldUntil";
				echo "<br/>";
				print_r($field);
				*/
				
				$valueFrom = null;
				$valueUntil = null;
				if ($nameFieldFrom && $this->isSettedField($nameFieldFrom)) {
					$valueFrom = $this->$nameFieldFrom;
				}
				if ($nameFieldUntil && $this->isSettedField($nameFieldUntil)) {
					$valueUntil = $this->$nameFieldUntil;
				}
//				echo "<br/>2. valueFrom: $valueFrom valueUntil: $valueUntil";
				
				if ($valueFrom || $valueUntil) {
					if ($aliasTable) {
						$nameFieldFrom = "$aliasTable.$nameFieldFrom";
						$nameFieldUntil = "$aliasTable.$nameFieldUntil";
					}
				//	echo "nameFieldFrom: $nameFieldFrom nameFieldUntil: $nameFieldUntil";
					
					if ($valueFrom) {
						$dbQuery->addConditions("$nameFieldFrom >= '$valueFrom'");
					}
					if ($valueUntil) {
						$dbQuery->addConditions("$nameFieldUntil <= '$valueUntil'");
					}
					// $dbQuery->addConditions("$nameField BETWEEN '$valueFrom' AND '$valueUntil'");					
				}
				else {
					$dbQuery->addConditions("$nameField >= '$valueField'");
				}
			}
			elseif ($field->isNumeric()) {
				$dbQuery->addConditions("$nameField = $valueField");
			}
			else {
				$dbQuery->addConditions("$nameField LIKE '%$valueField%'");	
			}
			
			$fieldsAdded[] = $nameFieldRaw;
			
			++$numAdd;
		}
		
		return $numAdd;
	}
	
	public function addConditionsToBaseADODB(ExjADODB &$dbADODB){
		$numAdd = 0;
		$fieldsAdded = array(); // campos adicinados
		$fields = $this->getFields(true);
		foreach ($fields as $field) {
			$nameField = $field->getName();
//				echo "<br/>Probando campo: $nameField. ";
			
			if (!isset($this->$nameField)) {
//				echo "No está definido";
				continue;
			}
			
			$valueField = $this->$nameField;
			if (!$valueField) {
	//			echo "No tiene valor";
				continue;
			}
			
			if (in_array($nameField, $fieldsAdded)) {
				continue;
			}
			
		//	echo "<br/>ADICIONANDO A WHERE DEL QUERY";
			$nameField = $dbADODB->getFieldFromAlias($nameField);
		
			$aliasTable = $dbADODB->getAliasTableFromField($nameField, false);
			$nameFieldRaw = $nameField;
			if ($aliasTable) {
				$nameField = "$aliasTable.$nameField";
			}
			
			if ($field->isDate() || $field->isDateTime()) {
			//	echo "<br/>Es tipo fecha. Campo: $nameFieldRaw";
				$nameFieldFrom = '';
				$nameFieldUntil = '';
				if (isset($field->endDateField) && $field->endDateField) {
					$nameFieldUntil = $field->endDateField;
					$nameFieldFrom = $nameFieldRaw;
					
					$fieldsAdded[] = $nameFieldUntil;
				}
				if (isset($field->startDateField) && $field->startDateField) {
					$nameFieldFrom = $field->startDateField;
					$nameFieldUntil = $nameFieldRaw;	
					
					$fieldsAdded[] = $nameFieldFrom;
				}

				/*
				echo "<br/>1. nameFieldFrom: $nameFieldFrom nameFieldUntil: $nameFieldUntil";
				echo "<br/>";
				print_r($field);
				*/
				
				$valueFrom = null;
				$valueUntil = null;
				if ($nameFieldFrom && $this->isSettedField($nameFieldFrom)) {
					$valueFrom = $this->$nameFieldFrom;
				}
				if ($nameFieldUntil && $this->isSettedField($nameFieldUntil)) {
					$valueUntil = $this->$nameFieldUntil;
				}
//				echo "<br/>2. valueFrom: $valueFrom valueUntil: $valueUntil";
				
				if ($valueFrom || $valueUntil) {
					if ($aliasTable) {
						$nameFieldFrom = "$aliasTable.$nameFieldFrom";
						$nameFieldUntil = "$aliasTable.$nameFieldUntil";
					}
				//	echo "nameFieldFrom: $nameFieldFrom nameFieldUntil: $nameFieldUntil";
					
					if ($valueFrom) {
						$dbADODB->addConditionANDToQuery("$nameFieldFrom >= '$valueFrom'");
					}
					if ($valueUntil) {
						$dbADODB->addConditionANDToQuery("$nameFieldUntil <= '$valueUntil'");
					}
					// $dbADODB->addConditionANDToQuery("$nameField BETWEEN '$valueFrom' AND '$valueUntil'");					
				}
				else {
					$dbADODB->addConditionANDToQuery("$nameField >= '$valueField'");
				}
			}
			elseif ($field->isNumeric()) {
				$dbADODB->addConditionANDToQuery("$nameField = $valueField");
			}
			else {
				$valueField = strtolower($valueField);
				$dbADODB->addConditionANDToQuery("LOWER($nameField) LIKE '%$valueField%'");
			}
			
			$fieldsAdded[] = $nameFieldRaw;
			
			++$numAdd;
		}
		
		return $numAdd;
	}	
}

?>