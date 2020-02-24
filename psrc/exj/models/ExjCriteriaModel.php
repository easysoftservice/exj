<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para el modelo Criteria. Las criterias deben heredar de esta clase.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[componente]/models/[componente].criteria.model.php
 * Nombrado de la clase: Debe tener el formato: class App[Componente]CriteriaModel extends ExjCriteriaModel
 */
class ExjCriteriaModel extends ExjModels {
	private $_addControlesUI = true;
	
	public function __construct($addControlesUI = true){
		$this->_addControlesUI = $addControlesUI;
		
	//	parent::__construct($addControlesUI);
		
		$this->criteriaInit($this->_addControlesUI);
		
		$this->criteriaRegisterFields();
		
		if ($this->_addControlesUI) {
			$this->criteriaRegisterControlsUI();
		}
		
		$this->registerRules();
		
		$this->afterConstructCriteria($this->_addControlesUI);
	}

	public function isAddControlsUI(){
		return $this->_addControlesUI;
	}
	
	/**
	 * Adiciona filtro a obj criteria como binario o para hacer comparación extricta
	 *
	 * @param object $objCriteria
	 * @param string $nameField
	 * @param mixed $value Por lo general string
	 */
	public static function AddFieldsBinary(&$objCriteria, $nameField, $value){
		if(!isset($objCriteria->fieldsBinary)){
			$objCriteria->fieldsBinary = array();
		}
		
		$objCriteria->fieldsBinary[] = $nameField;
		$objCriteria->$nameField = $value;
	}
	
	/**
	 * overwrite. Inicio del Modelo de Criteria
	 *
	 */
	protected function criteriaInit(&$addControlesUI){
		
	}
	
	/**
	 * overwrite. Después de ejecutar el constructor del modelo criteria
	 *
	 * @param bool $addControlesUI Indica si se adicionaron controles para la UI
	 */
	protected function afterConstructCriteria($addControlesUI){
		
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		
	}
	
	public function getFormPanelCriteria($withButtonReset = true){
		$cfg = ExjUI::NewFormPanel('Filtros');
		
		$cfg->collapsible = true;
		$cfg->titleCollapse = true;
		
		$btnSearch = ExjUI::NewButton('Buscar...', 'Permite buscar en base a los filtros indicados', 'exj-btn-search', 'search');
		
		$cfg->bbar = array();
		$cfg->bbar[] = '->';
		if ($withButtonReset) {
			$btnReset = ExjUI::NewButton('Reiniciar', 'Permite reiniciar los filtros a sus valores originales', 'exj-btn-reset', 'reset');
			$cfg->bbar[] = $btnReset;
			$cfg->bbar[] = '-';
		}
		$cfg->bbar[] = $btnSearch;
		
		return $cfg;
	}

	/**
	 * overrride. Devuelve la ui de la criteria
	 *
	 * @return object Estructura: cfgFormPanel, items
	 */
	public function to_ui(){
		$this->controlsUI_enableKeyEvents();
		
		$ui = parent::to_ui();
		
		$ui->cfgFormPanel = $this->getFormPanelCriteria();
		
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
	public function registerFieldId($name, $alias='', $isRequired=false, $isNullable=false, $allowZero=true, $nameFieldDB=''){
		return parent::registerFieldId($name, $alias, $isNullable, $isRequired, $allowZero, $nameFieldDB);
	}
	
	public function registerFieldInt($name, $alias='', $isRequired=false, $allowZero=true, $nameFieldDB=''){
		return parent::registerFieldInt($name, $alias, $isRequired, $allowZero, $nameFieldDB);
	}

	/**
	 * Registra campo tipo entero como requerido
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isRequired
	 * @param bool $isNullable
	 * @param bool $allowZero
	 * @param string $nameFieldDB
	 * @return ExjField Instancia
	 */
	public function registerFieldIdRequired($name, $alias='', $isRequired=true, $isNullable=false, $allowZero=false, $nameFieldDB=''){
		return $this->registerFieldId($name, $alias, $isRequired, $isNullable, $allowZero, $nameFieldDB);
	}

	/**
	 * override. Registra campo de tipo string
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isRequired
	 * @param bool $isNullable
	 * @param bool $isComparationEqual
	 * @return ExjField Instancia
	 */
	public function registerFieldString($name, $alias='', $isRequired=false, $isNullable=false, $isComparationEqual=false){
		return parent::registerFieldString($name, $alias, $isNullable, $isRequired, $isComparationEqual);
	}
	
	/**
	 * override. Registra campo de tipo date año-mes-dia
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isRequired
	 * @param bool $isNullable
	 * @param string $nameFieldDB No requerido, nombre del campo de la DB
	 * @return ExjField Instancia
	 */
	public function registerFieldDate($name, $alias='', $isRequired=false, $isNullable=false, $nameFieldDB=''){
		
		return parent::registerFieldDate(
			$name, $alias, $isNullable, $isRequired, $nameFieldDB
		);
	}
	
	public function registerFieldDateRange($nameFromUI, $nameUntilUI, $nameFieldDB, $aliasFrom='Inicial', $aliasUntil='Final'){
		$this->registerFieldDate($nameFromUI, $aliasFrom, false, false, $nameFieldDB);
		$this->registerFieldDate($nameUntilUI, $aliasUntil, false, false, $nameFieldDB);
		
		$this->_applyRangeToDates($nameFromUI, $nameUntilUI);
	}

	public function registerFieldDateRangeRequired($nameFromUI, $nameUntilUI, $nameFieldDB, $aliasFrom='Inicial', $aliasUntil='Final'){
		$this->registerFieldDate($nameFromUI, $aliasFrom, true, false, $nameFieldDB);
		$this->registerFieldDate($nameUntilUI, $aliasUntil, true, false, $nameFieldDB);
		
		$this->_applyRangeToDates($nameFromUI, $nameUntilUI);
	}

	public function addValidateDatesFromUntil($nameFieldUI, $aliasField='', $aliasDB=''){
		$fieldUI = new ExjField($nameFieldUI, ExjField::TYPE_DATE, $aliasField);
		$fieldUI->required(false)->nullable();
		

		$this->addFieldRegister($fieldUI);

		if ($this->isAddControlsUI()) {
			$this->registerControlUI(ExjUI::NewDateField($nameFieldUI));
		}
		else{
			$fFrom = new ExjField('valid_from_date', ExjField::TYPE_DATE, 'Vigente desde');
			$fUntil = new ExjField('valid_until_date', ExjField::TYPE_DATE, 'Vigente hasta');
			$fUntil->nullable();

			$fFrom->setterNameFieldDBFromName($aliasDB);
			$fUntil->setterNameFieldDBFromName($aliasDB);

			$fieldUI->fieldFromDB = $fFrom;
			$fieldUI->fieldUntilDB = $fUntil;
		}

		return $this;
	}

	
	private function _applyRangeToDates($nameFrom, $nameUntil){
		// aplicar a los campos en la parte del servidor
		$fields = &$this->getFields();
		foreach ($fields as &$field) {
			if ($field->getName() == $nameFrom) {
				$field->endDateField = $nameUntil;
			}
			if ($field->getName() == $nameUntil) {
				$field->startDateField = $nameFrom;
			}
		}
	}

	public function registerFieldDateRangeRequiredOnlyInitial($nameFromUI, $nameUntilUI, $nameFieldDB, $aliasFrom='Inicial', $aliasUntil='Final'){
		$this->registerFieldDate($nameFromUI, $aliasFrom, true, false, $nameFieldDB);
		$this->registerFieldDate($nameUntilUI, $aliasUntil, false, false, $nameFieldDB);
		
		$this->_applyRangeToDates($nameFromUI, $nameUntilUI);
	}
	
	/**
	 * overwrited. bind
	 *
	 * @param object $data
	 * @return int Nro bindeados
	 */
    public function bind($data='') {
    	$nBinds = parent::bind($data);
    	
    	// echo '<br/>'.__METHOD__ . " nBinds: $nBinds";
    	
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
    			$this->addBrokenRuler("El campo <b>$field->alias</b> es requerido.");
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
    		
    		if (!$value) {
    			$this->addBrokenRuler($field->alias . ' es requerido.');
    			continue;
    		}
    	}
    	
    	return $nBinds;
    }

	
	/**
	 * Adiciona condiciones al objeto para consultas
	 *
	 * @param ExjDBQuery $dbQuery
	 * @param array $exceptNameFields Nombres de campos que no se tomarán en cuenta
	 * @return int Número de condiciones adicionadas, false si hay error
	 */
	public function addConditionsQuery(ExjDBQuery &$dbQuery, $exceptNameFields = null){
		if (!($dbQuery instanceof ExjDBQuery)) {
			global $exj;
			$exj->setErrorValidating("Se ha llamado a " . __FUNCTION__. ' y se ha enviado parámetro de tipo no soportado');
			return false;
		}
		
		if ($exceptNameFields && !is_array($exceptNameFields)) {
			$exceptNameFields = explode(',', $exceptNameFields);
		}
		
		$numAdd = 0;
		$fieldsAdded = array(); // campos adicinados
		$fields = $this->getFields(true);
		
	//	print_r($fields);
		
		foreach ($fields as $field) {
			$nameField = $field->getName();
//				echo "<br/>Probando campo: $nameField. ";
			
			if (!isset($this->$nameField)) {
//				echo "No está definido";
				continue;
			}
			
			if ($exceptNameFields && count($exceptNameFields) > 0) {
				if (in_array($nameField, $exceptNameFields)) {
					continue;
				}
			}
			
			$valueField = $this->$nameField;
			if (!$valueField) {
	//			echo "No tiene valor";
				continue;
			}
			
			/*
			if (!self::IsSettedValue($valueField)) {
				continue;
			}
			*/
			
			if (in_array($nameField, $fieldsAdded)) {
				continue;
			}
			
			$nameFieldDB = $field->nameFieldDB;
			if (is_numeric($nameFieldDB) || is_bool($nameFieldDB)) {
				$this->addBrokenRuler("ERROR EN CRITERIA.<br>El valor del campo: $nameField no es string.<br>Referencia: " . get_class($this));
				continue;
			}
			
			
		//	echo "<br/>ADICIONANDO A WHERE DEL QUERY. nameFieldDB: $nameFieldDB";
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
					$valueUntil = self::GetDateTimeMaxForDB($valueUntil);
					// echo "valueUntil: $valueUntil";
				}
				
//				echo "<br/>2. valueFrom: $valueFrom valueUntil: $valueUntil";
				
				if ($valueFrom || $valueUntil) {
					$nameFieldDBFrom = $this->getNameFieldDB($nameFieldFrom);
					$nameFieldDBUntil = $this->getNameFieldDB($nameFieldUntil);
					
					if ($aliasTable) {
						$nameFieldFrom = "$aliasTable.$nameFieldFrom";
						$nameFieldUntil = "$aliasTable.$nameFieldUntil";
					}
				//	echo "nameFieldFrom: $nameFieldFrom nameFieldUntil: $nameFieldUntil";
				
					if ($nameFieldDBFrom) {
						$nameFieldFrom = $nameFieldDBFrom;
					}
					if ($nameFieldDBUntil) {
						$nameFieldUntil = $nameFieldDBUntil;
					}
					
					if ($valueFrom) {
						$dbQuery->addConditions("$nameFieldFrom >= '$valueFrom'");
					}
					if ($valueUntil) {
						$dbQuery->addConditions("$nameFieldUntil <= '$valueUntil'");
					}
					
					// $dbQuery->addConditions("$nameField BETWEEN '$valueFrom' AND '$valueUntil'");					
				}
				elseif(!$this->resolveAddConditionsFieldsFromUntil($dbQuery, $field, $valueField)) {

					$this->_addConditionSplit($dbQuery, $nameField, $nameFieldDB, ">= '$valueField'");
				}
			}
			elseif ($field->isNumeric() || $field->isComparationEqual) {
				if ($field->isNumeric()) {
					$this->_addConditionSplit($dbQuery, $nameField, $nameFieldDB, "= $valueField");
				}
				else {
					$this->_addConditionSplit(
						$dbQuery, $nameField, $nameFieldDB, "= '$valueField'"
					);
				}
			}
			else {
				$this->_addConditionSplit($dbQuery, $nameField, $nameFieldDB, "LIKE '%$valueField%'");
			}
			
			$fieldsAdded[] = $nameFieldRaw;
			
			++$numAdd;
		}
		
		return $numAdd;
	}

	protected function resolveAddConditionsFieldsFromUntil($dbQuery,ExjField $field, $valueField){

		$fieldFromDB = $fieldUntilDB = null;
		if (isset($field->fieldFromDB)) {
			$fieldFromDB = $field->fieldFromDB;
		}

		if (isset($field->fieldUntilDB)) {
			$fieldUntilDB = $field->fieldUntilDB;
		}

		if (!$fieldFromDB || !$fieldUntilDB) {
			return false;
		}

		if ($fieldFromDB) {
			$dbQuery->addConditions($fieldFromDB->getConditionSQL($valueField, '<='));
		}

		if ($fieldUntilDB) {
			$dbQuery->addConditions($fieldUntilDB->getConditionSQL($valueField, '>='));
		}

		// echo "valueField: $valueField getStrConditions: " . $dbQuery->getStrConditions();

		return true;
	}
	
	public static function GetDateTimeMaxForDB($dateRaw){
		if (!$dateRaw) {
			return $dateRaw;
		}
		
		$dateSQL = ExjDate::ConvertToDateDB($dateRaw);
		$dateSQL .= ' 23:59:59';
		
		return $dateSQL;
	}
	
	private function _addConditionSplit(ExjDBQuery &$dbQuery, $nameField, $nameFieldDB, $conditionComplement){
		if (!$nameFieldDB) {
			$nameFieldDB = $nameField;
		}
		
		if (!$nameFieldDB) {
			$this->addBrokenRuler("No se definió un campo, al adicionar condición!");
			return false;
		}
		
		$dbQuery->addConditions("$nameFieldDB $conditionComplement");
		
		return true;
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

	public static function AddWhereCriteria(&$where, $params, $fieldDB, $op='=', $unsetVal=true) {
		if (empty($params)) {
			return;
		}

		$fieldObj = trim($fieldDB);
		if (($posPoint=strrpos($fieldObj, '.')) !== false) {
			$fieldObj = substr($fieldObj, $posPoint+1);
		}

		if (!$fieldObj || !isset($params->$fieldObj)) {
			return;
		}

		$value = $params->$fieldObj;
		if ($value) {
			if (!$where) {
				$where = array();
			}

			if ($op == 'IN') {
				if (is_array($value)) {
					$value = implode(',', $value);
				}

				$value = "($value)";
			}
			elseif ($op == 'LIKE') {
				$value = "'$value%'";
			}
			else{
				if (!is_numeric($value) || substr($fieldObj, 0, 2)!='id') {
					$value = trim($value, "'");
					$value = "'$value'";
				}
			}

			$where[] = $fieldDB . " $op " . $value;
		}

		if ($unsetVal) {
			unset($params->$fieldObj);
		}
	}	
}

?>