<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUITextArea extends ExjUITextField {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct($name, $fieldLabel, $anchor);
        $this->setXType(self::XTYPE_TextArea);
    }

    public function setPreventScrollbars($value=true){
    	$this->preventScrollbars = $value;
    	return $this;
    }
}

?>