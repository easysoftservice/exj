<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUITimeField extends ExjUIComboBox {

	public function __construct($name='') {
        parent::__construct($name, self::XTYPE_TimeField);
    }

    public function setIncrement($value){
    	$this->increment = $value;
    	return $this;
    }

    public function setMinValue($value){
    	$this->minValue = $value;
    	return $this;
    }

    public function setMaxValue($value){
    	$this->maxValue = $value;
    	return $this;
    }

    

    
}

?>