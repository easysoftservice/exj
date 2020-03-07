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
        if (is_array($value)) {
            $value = (object) $value;
        }

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

    public function addItem($value){
        if (is_array($value)) {
            $value = (object) $value;
        }

        if (!isset($this->items)) {
            $this->items = array();
        }

        $this->items[] = $value;
        return $this;
    }

    public function setLayoutConfig($value){
        if (is_array($value)) {
            $value = (object) $value;
        }

        $this->layoutConfig = $value;
        return $this;
    }

    

    
}

?>