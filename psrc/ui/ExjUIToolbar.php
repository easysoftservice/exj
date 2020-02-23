<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIToolbar extends ExjUIContainer {

	public function __construct() {
        $this->setXType(self::XTYPE_Toolbar);
    }

    public function setButtonAlign($value){
    	$this->buttonAlign = $value;
    	return $this;
    }

    public function setEnableOverflow($value=true){
    	$this->enableOverflow = $value;
    	return $this;
    }

    public function setLayout($value){
    	$this->layout = $value;
    	return $this;
    }    
}

?>