<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIHtmlEditor extends ExjUIField {

	public function __construct($name='', $fieldLabel='', $anchor=0) {
        parent::__construct(self::XTYPE_HtmlEditor);

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

    public function setCreateLinkText($value){
    	$this->createLinkText = $value;
    	return $this;
    }

    public function setDefaultValue($value){
    	$this->defaultValue = $value;
    	return $this;
    }

    public function setFontFamilies($value){
    	if (is_string($value)) {
    		$value = array($value);
    	}

    	$this->fontFamilies = $value;
    	return $this;
    }
    
}

?>