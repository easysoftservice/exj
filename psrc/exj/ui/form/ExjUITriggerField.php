<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUITriggerField extends ExjUITextField {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct($name, $fieldLabel, $anchor);
        $this->setXType(self::XTYPE_TriggerField);
    }

    public function setEditable($value=true){
    	$this->editable = $value;
    	return $this;
    }

    public function setHideTrigger($value=true){
    	$this->hideTrigger = $value;
    	return $this;
    }

    public function setReadOnly($value=true){
    	$this->readOnly = $value;
    	return $this;
    }

    public function setWrapFocusClass($value){
    	$this->wrapFocusClass = $value;
    	return $this;
    }
     
}

?>