<?php

defined('_JEXEC') or die('Acceso Restringido');

class ExjUIContainer extends ExjUIBoxComponent {

	public function __construct() {
        $this->setXType(self::XTYPE_Container);
    }

    public function setDefaultType($value){
    	$this->defaultType = $value;
    	return $this;
    }

    public function setAutoDestroy($value=true){
    	$this->autoDestroy = $value;
    	return $this;
    }

    public function setHideBorders($value=true){
        $this->hideBorders = $value;
        return $this;
    }

    

    public function setLayout($value){
    	$this->layout = $value;
    	return $this;
    }

    public function setDefaults($value){
        $this->defaults = $value;
        return $this;
    }

    public function setDefaultsKeyValue($key, $value){
        $key = trim($key);
        if (!$key) {
            return $this;
        }

        if (!isset($this->defaults)) {
            $this->defaults = new stdClass();
        }

        $this->defaults->$key = $value;
        return $this;
    }

    public function setItems($value){
        $this->items = $value;
        return $this;
    }

    
}

?>