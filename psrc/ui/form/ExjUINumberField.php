<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUINumberField extends ExjUITextField {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct($name, $fieldLabel, $anchor);
        $this->setXType(self::XTYPE_NumberField);
    }

    public function setAllowDecimals($value=true){
    	$this->allowDecimals = $value;
    	return $this;
    }

    public function setMinValue($value){
    	$this->minValue = $value;
    	return $this;
    }

    public function setMinText($value){
    	$this->minText = $value;
    	return $this;
    }

    public function setMaxValue($value){
        $this->maxValue = $value;
        return $this;
    }
    

    public function setAllowNegative($value=true){
    	$this->allowNegative = $value;
    	return $this;
    }

    public function setAutoStripChars($value=true){
    	$this->autoStripChars = $value;
    	return $this;
    }

    public function setBaseChars($value){
    	$this->baseChars = $value;
    	return $this;
    }

    public function setDecimalPrecision($value){
    	$this->decimalPrecision = $value;
    	return $this;
    }

    public function setDecimalSeparator($value){
    	$this->decimalSeparator = $value;
    	return $this;
    }

    public function setNanText($value){
    	$this->nanText = $value;
    	return $this;
    }

    
}

?>