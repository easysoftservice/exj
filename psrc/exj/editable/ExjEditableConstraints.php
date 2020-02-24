<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjEditableConstraints {

    private $_items = null;

    public function addConstraintForeignKey($nameField, $isUnique = false, $isActionUpdate = false, $autoSeteerPrimaryKey = true) {
        if (!$nameField) {
            return false;
        }

        if (!$this->_items) {
            $this->_items = array();
        }

        $newItem = new ExjEditableConstraint($nameField, $isUnique, $isActionUpdate, true, $autoSeteerPrimaryKey);

        $this->_items[$nameField] = $newItem;
    }

    public function getConstraints() {
        $constraints = array();
        if (!$this->_items) {
            return $constraints;
        }

        foreach ($this->_items as $nf => $item) {
            $constraints[] = $item;
        }

        return $constraints;
    }

    public function getConstraintForeignKey($nameField) {
        if (!$this->_items) {
            return null;
        }

        if (!isset($this->_items[$nameField])) {
            return null;
        }

        return $this->_items[$nameField];
    }
}

?>