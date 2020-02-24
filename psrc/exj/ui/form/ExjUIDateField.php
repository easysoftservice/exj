<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIDateField extends ExjUITriggerField {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct($name, $fieldLabel, $anchor);
        $this->setXType(self::XTYPE_DateField);
    }

    public function setFormat($value){
    	$this->format = $value;
    	return $this;
    }

    public function setAltFormats($value){
        if (is_array($value)) {
            $value = implode('|', $value);
        }

    	$this->altFormats = $value;
    	return $this;
    }

    public function addAltFormat($value){
        if (empty($value)) {
            return $this;
        }

        if (isset($this->altFormats) && $this->altFormats) {
            $vals = explode('|', $this->altFormats);
            if (!in_array($value, $vals)) {
                $this->altFormats .= '|' . $value;
            }
        }
        else {
            $this->setAltFormats($value);
        }

        return $this;
    }

    public function setShowToday($value=true){
    	$this->showToday = $value;
    	return $this;
    }
    
}

?>