<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIRadioGroup extends ExjUICheckboxGroup {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct($name, $fieldLabel, $anchor);
        $this->setXType(self::XTYPE_RadioGroup);
    }

    public function setAllowBlank($value=true){
    	$this->allowBlank = $value;
    	return $this;
    }

   
    
}

?>