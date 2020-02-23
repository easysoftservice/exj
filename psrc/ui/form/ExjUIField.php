<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIField extends ExjUIBoxComponent {

    public $disabled = false;
    public $invalidText = 'El valor de este campo es inválido';
    public $name = '';
    public $readOnly = false;

    public function __construct($xtype = '') {
        if (!$xtype) {
            $xtype = self::XTYPE_Field;
        }

        $this->setXType($xtype);
    }

    public function setName($value) {
        $this->name = $value;
        return $this;
    }

    public function getName() {
        return $this->name;
    }

    /**
     * Envia o setea la propiedad value
     *
     * @param mixed $value
     * @return ExjUIField
     */
    public function setValue($value) {
        $this->value = $value;

        return $this;
    }

    public function getValue() {
        return (isset($this->value) ? $this->value : null);
    }

    
    public function isEmptyValue() {
    	if (isset($this->value)) {
        	return ($this->value ? false:true);
        }
        
        return true;
    }
    
    public function clearValue() {
        if (isset($this->value)) {
        	unset($this->value);
        }

        return $this;
    }

    /**
     * Envia flex al ojecto UI
     *
     * @param int $flex
     * @return ExjUIField
     */
    public function setFlex($flex = 1) {
        $this->flex = $flex;
        return $this;
    }

    public function setReadOnly($value = true) {
        $this->readOnly = $value;
        return $this;
    }

    public function setCls($value) {
        $this->cls = $value;
        return $this;
    }

    /**
     * Envia cls para que el texto se encriba a mayúsculas
     *
     * @return ExjUIComponent
     */
    public function setClsTextUpper() {
        return $this->setCls('exj-text-upper');
    }

    public function addCls($cls) {
        if (!$cls) {
            return $this;
        }

        if (isset($this->cls) && $this->cls) {
            $this->cls .= ' ' . $cls;
        } else {
            $this->cls = $cls;
        }

        return $this;
    }

    public function setInputType($value) {
        $this->inputType = $value;
        return $this;
    }
}
?>