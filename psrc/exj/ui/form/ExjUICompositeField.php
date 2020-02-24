<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUICompositeField extends ExjUIField {

    public function __construct($name = '') {
        parent::__construct(self::XTYPE_CompositeField);
        
        $this->setName($name);
        $this->anchor = '99%';
    }

    /**
     * Envia un objeto por defecto
     *
     * @param object $defaults
     * @return ExjUICompositeField
     */
    public function setDefaults($defaults) {
        $this->defaults = $defaults;
        return $this;
    }

    /**
     * Adiciona itemUI al composite
     *
     * @param ExjUIField $itemUI
     * @param bool $setterFlex
     * @return ExjUICompositeField
     */
    public function addItem(ExjUIField $itemUI, $setterFlex = false) {
        if (!isset($this->items)) {
            $this->items = array();
        }

        if ($setterFlex) {
            $itemUI->setFlex(1);
        }

        $this->items[] = $itemUI;

        return $this;
    }

}
?>