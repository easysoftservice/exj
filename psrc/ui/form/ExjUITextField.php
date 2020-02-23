<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUITextField extends ExjUIField {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct(self::XTYPE_TextField);

        if ($name) {
            $this->setName($name);
        }

        if ($fieldLabel) {
            $this->setFieldLabel($fieldLabel);
        }
        
        if ($anchor) {
            $this->setAnchor($anchor);
        }
    }

    public function setBlankText($value = true){
    	$this->blankText = $value;
    	return $this;
    }

    public function setDisableKeyFilter($value = true){
    	$this->disableKeyFilter = $value;
    	return $this;
    }

    public function setEmptyText($value){
    	$this->emptyText = $value;
    	return $this;
    }

    public function setEnableKeyEvents($value = true){
    	$this->enableKeyEvents = $value;
    	return $this;
    }

    public function setMaxLength($value){
    	$this->maxLength = $value;
    	return $this;
    }

    public function setMaxLengthText($value){
    	$this->maxLengthText = $value;
    	return $this;
    }

    public function setMinLength($value){
    	$this->minLength = $value;
    	return $this;
    }

    public function setMinLengthText($value){
    	$this->minLengthText = $value;
    	return $this;
    }

    public function setVtype($value){
    	$this->vtype = $value;
    	return $this;
    }

    public function setVtypeText($value){
    	$this->vtypeText = $value;
    	return $this;
    }

    public function setAllowBlank($value=true){
        $this->allowBlank = $value;
        return $this;
    }

    public function setSelectOnFocus($value=true){
        $this->selectOnFocus = $value;
        return $this;
    }

    
}

?>