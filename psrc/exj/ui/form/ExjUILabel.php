<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUILabel extends ExjUIBoxComponent {

	public function __construct() {
        $this->setXType(self::XTYPE_Label);
    }

    public function setForId($value){
    	$this->forId = $value;
    	return $this;
    }

    public function setHtml($value){
    	$this->html = $value;
    	return $this;
    }

    public function setText($value){
    	$this->text = $value;
    	return $this;
    }
}

?>