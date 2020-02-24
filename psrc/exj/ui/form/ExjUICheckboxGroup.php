<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUICheckboxGroup extends ExjUIField {

	public function __construct($name) {
        parent::__construct(self::XTYPE_CheckboxGroup);
    }

    public function setColumns($value){
    	$this->columns = $value;
    	return $this;
    }

    public function setItems($value){
    	$this->items = $value;
    	return $this;
    }

    public function setItem($value){
    	if (!isset($this->items)) {
    		$this->items = array();
    	}

    	$this->items[] = $value;
    	return $this;
    }

    public function setVertical($value=true){
    	$this->vertical = $value;
    	return $this;
    }

}

?>