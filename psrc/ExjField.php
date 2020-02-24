<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjField extends ExjObject {

	private $_name;

    public $type='';
    public $alias='';
    public $nameFieldDB = '';

    public $isAllowZero = true;
    public $isRequired = true;

    public $validationType = '';
    public $maxLength = 0;
    public $minLength = 0;
    public $isApplyTrim = false;
    public $isToUpper = false;

    public $isComparationEqual=false;
    public $isComplex=false;
    public $dateFormat='';
    public $defaultValue=null;
    public $isUseNull=null;


    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_BOOL = 'bool';
    const TYPE_ListModel = 'exjListModel';

    const VALIDATION_textnameext = 'textnameext';
    const VALIDATION_textmemo = 'textmemo';
    const VALIDATION_email = 'email';

    public function __construct($name, $type = null, $alias = '', $isNullable = false, $isRequired = true, $allowZero = true, $nameFieldDB = '') {
        
        if ($type === null) {
            $type = self::TYPE_STRING;
        }

        $this->_name = trim($name);
        $this->resetValidation();

        $this->setType($type)
        	->setAlias($alias)
        	->nullable($isNullable)
        	->required($isRequired)
        	->allowZero($allowZero)
        	->setNameFieldDB($nameFieldDB);
 	
    }

    public function toUI(){
        /*
        foreach ($this as $key => $value) {
            if (strlen($key) <= 1) {
                continue;
            }

            if ($value === null || substr($key, 0, 1) == '_') {
                continue;
            }

            echo "<br> $key = $value";
        }
        */

    	$dataField = new ExjDataField($this->getName(), $this->type);

    	if ($this->isBool()) {
    		$dataField->type = ExjDataField::TYPE_BOOL;
    	}

    	if (isset($this->isUseNull)) {
    		$dataField->useNull = $this->isUseNull;
    	}

		if (isset($this->defaultValue)) {
    		$dataField->defaultValue = $this->defaultValue;
    	}

    	if ($this->dateFormat) {
    		$dataField->dateFormat = $this->dateFormat;
    	}

    	if ($this->isRequired) {
    		$dataField->allowBlank = false;
    	}

    	return $dataField;
    }

    public function rendererType(){
    	$value = $this->type;

    	if ($value === '') {
    		$value = 'auto';
    	}
    	else{
    		switch ($value) {
	    		case self::TYPE_INT:
	    			$value = 'Entero';
	    		break;

	    		case self::TYPE_FLOAT:
	    			$value = 'Decimal';
	    		break;

	    		case self::TYPE_DATE:
	    			$value = 'Fecha';
	    		break;

	    		case self::TYPE_DATETIME:
	    			$value = 'Fecha y hora';
	    		break;
	    	}
    	}

    	return $value;
    }

    public function complex($value=true){
    	$this->isComplex = $value;
    	return $this;
    }

    public function useNull($value=true){
    	$this->isUseNull = ($value === null ? null: ($value ? true : false));
    	return $this;
    }

    

    public function setDefaultValue($value){
    	$this->defaultValue = $value;
    	return $this;
    }

    public function setDateFormat($value){
    	$this->dateFormat = $value;
    	return $this;
    }

    public function setType($value){
    	$value = trim($value);

    	if ($this->type != $value) {
    		$this->type = $value;

	    	if ($this->isString()) {
	            $this->setValidationTypeStringDefault();
	        }
	        else{
	        	$this->resetValidation();
	        }
    	}

    	return $this;
    }

    public function resetValidation(){
    	return $this->clearValidationType()
    		->toUpper(false)
    		->setMaxLength(0)
    		->setMinLength(0)
    		->applyTrim();
    }

    public function setValidationType($value){
    	$this->validationType = $value;
    	return $this;
    }

    public function clearValidationType(){
    	return $this->setValidationType('');
    }

    public function setMaxLength($value){
    	$this->maxLength = $value;
    	return $this;
    }

    public function setMinLength($value){
    	$this->minLength = $value;
    	return $this;
    }

    public function toUpper($value=true){
    	$this->isToUpper = $value;
    	return $this;
    }

    public function applyTrim($value=true){
    	$this->isApplyTrim = $value;
    	return $this;
    }


    public function typeString(){
    	return $this->setType(self::TYPE_STRING);
    }

    public function typeInt(){
    	return $this->setType(self::TYPE_INT);
    }

    public function typeIntNullable(){
    	return $this->typeInt()->nullable();
    }

    public function typeId(){
    	return $this->typeInt()->nullable(false)->allowZero(false);
    }

    public function typeIdNullable(){
    	return $this->typeInt()->nullable(true)->allowZero(false);
    }

    public function typeFloat(){
    	return $this->setType(self::TYPE_FLOAT);
    }

    public function typeDate(){
    	return $this->setType(self::TYPE_DATE);
    }

    public function typeDateTime(){
    	return $this->setType(self::TYPE_DATETIME);
    }

    public function typeBool(){
    	return $this->setType(self::TYPE_BOOL);
    }

    public function typeListModel(){
    	return $this->setType(self::TYPE_ListModel);
    }


    

    public function isString(){
    	return ($this->type == self::TYPE_STRING);
    }

    public function isInt(){
    	return ($this->type == self::TYPE_INT);
    }

    public function isFloat(){
    	return ($this->type == self::TYPE_FLOAT);
    }

    public function isDate(){
    	return ($this->type == self::TYPE_DATE);
    }

    public function isDateTime(){
    	return ($this->type == self::TYPE_DATETIME);
    }

    public function isBool(){
    	return ($this->type == self::TYPE_BOOL);
    }

    public function isListModel(){
    	return ($this->type == self::TYPE_ListModel);
    }
    
    public function isNumeric(){
    	return ($this->isInt() || $this->isFloat());
    }

	public function setRawAlias($value){
		$this->alias = $value;
    	return $this;
	}    

    public function setAlias($value){
    	$value = trim($value);
        if (!$value) {
            $value = ucfirst($this->getName());
            if (strpos($value, "_") !== false) {
                $value = str_replace("_", ' ', $value);
                $value = ucwords($value);
            }
        }

    	$this->alias = $value;
    	return $this;
    }

    public function nullable($value=true){
    	$this->isNullable = $value;
    	return $this;
    }


    public function required($value=true){
    	$this->isRequired = $value;
    	return $this;
    }

    public function allowZero($value=true){
    	$this->isAllowZero = $value;
    	return $this;
    }

    public function setNameFieldDB($value){
    	$this->nameFieldDB = $value;
    	return $this;
    }

    public function setValidationTypeTextMemo(){
    	return $this->setValidationType(self::VALIDATION_textmemo);
    }

    public function setValidationTypeTextNameExt(){
    	return $this->setValidationType(self::VALIDATION_textnameext);
    }

    public function setValidationTypeEmail(){
    	return $this->setValidationType(self::VALIDATION_email);
    }


    

    public function setValidationTypeStringDefault($maxLength=300){
    	return $this->setValidationTypeTextMemo()
            	->setMaxLength($maxLength)
            	->applyTrim();
    }

    public function getName(){
    	return $this->_name;
    }

    public function setterNameFieldDBFromName($aliasTable=''){
    	$nf = $this->getName();
    	$aliasTable = trim($aliasTable);
    	if ($aliasTable) {
    		$nf = $aliasTable. '.'. $nf;
    	}

    	return $this->setNameFieldDB($nf);
    }

    public function getConditionSQL($valueField, $conditon = ''){
    	if (!$conditon) {
    		$conditon = '=';
    	}

    	$nfDB = trim($this->nameFieldDB);
    	if (!$nfDB) {
    		$nfDB = $this->getName();
    	}

    	$expresion = '';
    	if ($valueField === null || $valueField === 'null') {
    		$expresion .= "$nfDB IS NULL";
    	}
    	else{
    		$expresion .= "$nfDB $conditon '$valueField'";
			if ($this->isNullable) {
				$expresion .= " OR $nfDB IS NULL";
				$expresion = "($expresion)";
			}
    	}

    	return $expresion;
    }

    

    public function rendererValue($value){
        if ($this->isNullable) {
            if ($value === 'null') {
                $value = null;
            }
        }

        if ($value === null) {
    		return $value;
    	}


		if ($this->isNumeric() && $this->isNullable) {
            if (trim($value . '') === '') {
                $value = null;
                return $value;
            }
        }

    	if ($this->isInt() || $this->isFloat()) {
    		if ($this->isInt()) {
	            $valueNum = intval($value);
	        }
	        elseif ($this->isFloat()) {
	            $valueNum = floatval($value);
	        }

	        if (is_nan($valueNum)) {
	        	$this->setErrorMsg($this->alias . " tiene<br/>el valor: $value, el cual no es " . $this->rendererType());
	        }
	        else{
	        	$value = $valueNum;
	        }
    	}
    	elseif ($value){
    		if ($this->isToUpper && is_string($value)) {
	            $value = strtoupper($value);
	        }

	    	if ($this->isString()) {
	    		if ($this->isApplyTrim) {
	    			if (!is_object($value)) {
		                $value = trim($value);
		            }
	    		}
	        }
	        elseif ($this->isDate() || $this->isDateTime()) {
	        	$value = trim($value);
	        	// $this->dateFormat
	        	$testFormatConvert = '';
	        	if ($this->isDate()) {
	        		if (strlen($value) > 10) {
	        			$value = trim(substr($value, 0, 10));
	        		}

	        		if (!ExjString::IsDateDB($value)) {
	        			$testFormatConvert = 'Y-m-d';
	        		}
	        	}
	        	elseif ($this->isDateTime()) {
	        		if (strpos($value, 'T')!==false) {
	        			$value = str_replace('T', '', $value);
	        		}

	        		if (!ExjString::IsDateTimeDB($value)) {
	        			$testFormatConvert = 'Y-m-d H:i:s';
	        		}
	        	}

	        	if ($testFormatConvert) {
	        		$vTime = strtotime($value);
	        		if ($vTime === false) {
	        			$this->setErrorMsg($this->alias . " tiene<br/>el valor: $value, el cual no es " . $this->rendererType());
	        		}
	        		else{
	        			$value = date($testFormatConvert, $vTime);
	        		}
	        	}
	        }
    	}

    	return $value;
    }

    public function setClassEditable($classEditable){
        $this->classEditable = $classEditable;
        return $this;
    }

    public function getClassEditable(){
        return (isset($this->classEditable) ? $this->classEditable : '');
    }
}
?>