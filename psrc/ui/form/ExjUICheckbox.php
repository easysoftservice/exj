<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUICheckbox extends ExjUIField {

	public function __construct($name) {
        parent::__construct('checkbox');

        $this->setName($name);
    }
	
	public function setBoxLabel($value){
		$this->boxLabel = $value;
		return $this;
	}

	public function setChecked($checked=true){
		if (is_bool($checked)) {
			$this->checked = $checked;
		}
		else{
			$this->checked = ($checked ? true:false);
		}
		
		return $this;
	}

	public function setFieldClass($value){
		$this->fieldClass = $value;
		return $this;
	}

	public function setInputValue($value){
		$this->inputValue = $value;
		return $this;
	}
	 
}

?>