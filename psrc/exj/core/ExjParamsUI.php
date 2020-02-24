<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Clase para manejo de parametros de UI
 *
 */
class ExjParamsUI extends ExjObject {

    private $_data = null;
    private $_nroFieldsBindeds = 0;

    public function __construct($data = null) {

        if ($data) {
            $this->_data = $data;

            $this->_loadData();
        }
    }

    private function _loadData() {
        if (!$this->_data) {
            return;
        }

        if (is_object($this->_data)) {
            /*
              $vars = get_object_vars($this->_data);
              foreach ($vars as $name => $value) {
              $this->$name = $value;
              }
             */

            $this->_nroFieldsBindeds = $this->bindData($this->_data);
        } elseif (is_string($this->_data)) {
            $this->loadValuesFromParams($this->_data);
        }
    }

    public function getNumFieldsBindeds() {
        return $this->_nroFieldsBindeds;
    }

    protected function getFieldLabelFromName($name) {
        return $this->_convertNameToFieldLabel($name);
    }

    private function _convertNameToFieldLabel($name) {
        if (!$name) {
            return $name;
        }

        $fieldLabel = trim($name);

        $fieldLabel = str_replace("_", ' ', $fieldLabel);
        // $fieldLabel = ucwords($fieldLabel);
        $fieldLabel = strtoupper($fieldLabel);

        return $fieldLabel;
    }

    public function getItemsUI() {
        $itemsUI = array();

        $fields = $this->getFieldsOfThisObj();
        foreach ($fields as $field) {
            $value = $this->$field;
            $fieldLabel = $this->getFieldLabelFromName($field);

            $cmpUI = $this->createCmpUI($field, $fieldLabel, $value);
            if (!$cmpUI) {
                continue;
            }

            $itemsUI[] = $cmpUI;
        }

        return $itemsUI;
    }

    protected function createCmpUI($field, $fieldLabel, $value) {
        $cmp = ExjUI::NewTextField($field, $fieldLabel);
        $cmp->value = $value;

        return $cmp;
    }

}
?>