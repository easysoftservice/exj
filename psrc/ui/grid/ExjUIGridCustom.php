<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIGridCustom extends ExjObject {

    private $_idLast = 0;
    private $_numTotalCols = 0;
    private $_prefixNameCol = '';
    private $_fieldsToSetterData = null;
    private $_indexsColsNoShows = null;
    private $_fieldsRenderDate = null;
    private $_fieldsRenderDateTime = null;
    private $_cols = null;
    private $_addColOrd = false;
    private $_addLinesToHeders = false;

    public function __construct($numTotalCols, $addLinesToHeders = false, $addColOrd = true, $prefixNameCol = 'col') {
        $this->_numTotalCols = $numTotalCols;
        $this->_prefixNameCol = $prefixNameCol;

        /*
          if ($addColOrd) {
          $this->_numTotalCols += 1;
          }
         */

        $this->_addLinesToHeders = $addLinesToHeders;

        $this->_addColOrd = $addColOrd;
        $this->_registerColOrd();
    }

    public function addLinesToHeders($add = true) {
        $this->_addLinesToHeders = $add;
    }

    private function _registerColOrd() {
        if (!$this->_addColOrd) {
            return;
        }

        $this->registerCol('ORD', '_ord');
    }

    public function getNameCol($index) {
        return ($this->_prefixNameCol . ($index + 1));
    }

    public function &newRow($id = null) {
        $rowUI = new stdClass();
        $this->_idLast += 1;

        if (!$id) {
            $id = $this->_idLast;
        }

        $rowUI->id = $id;
        $rowUI->isHeader = false;
        $rowUI->isData = false;
        $rowUI->titleRow = '';

        for ($i = 0; $i < $this->_numTotalCols; $i++) {
            $nameCol = $this->getNameCol($i);
            $rowUI->$nameCol = '';
        }

        return $rowUI;
    }

    public function NewRowTitle($title, $id = null) {
        $topic = $this->newRow($id);
        $topic->titleRow = $title;
        return $topic;
    }

    public function NewRowHeader($headers = '', $id = null) {
        if (!$headers) {
            $headers = $this->_getHeadersFromCols();
        }

        if (!is_array($headers)) {
            $headers = explode(',', $headers);
        }

        $numCols = count($headers);

        $topic = $this->newRow($id);
        $topic->isHeader = true;
        for ($i = 0; $i < $this->_numTotalCols; $i++) {
            if ($i >= $numCols) {
                break;
            }

            if ($this->isColNoShow($i)) {
                continue;
            }

            $nameCol = $this->getNameCol($i);
            $topic->$nameCol = $headers[$i];
        }

        return $topic;
    }

    public function addColNoShow($indexCol) {
        if (!$this->_indexsColsNoShows) {
            $this->_indexsColsNoShows = array();
        }

        $this->_indexsColsNoShows[] = $indexCol;
    }

    public function isColNoShow($indexCol) {
        if (!$this->_indexsColsNoShows) {
            return false;
        }

        if (in_array($indexCol, $this->_indexsColsNoShows)) {
            // echo '<br/>' . __METHOD__ . " indexCol: $indexCol";
            return true;
        }

        return false;
    }

    public function setFieldsToSetterData($fields) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        $this->_fieldsToSetterData = $fields;
    }

    private $_fieldsCustoms = null;

    private function _registerFieldCustom($nameField, $maxChars) {
        $nameField = trim($nameField);
        if (!$nameField) {
            return;
        }

        if (!$this->_fieldsCustoms) {
            $this->_fieldsCustoms = array();
        }

        $fieldCustom = null;
        if (count($this->_fieldsCustoms) > 0) {
            foreach ($this->_fieldsCustoms as &$itemFieldCustom) {
                if ($itemFieldCustom->nf == $nameField) {
                    $fieldCustom = $itemFieldCustom;
                    break;
                }
            }
        }

        if ($fieldCustom) {
            // redefine
            $fieldCustom->maxChars = $maxChars;
        } else {
            $fieldCustom = new stdClass();
            $fieldCustom->nf = $nameField;
            $fieldCustom->maxChars = $maxChars;

            $this->_fieldsCustoms[] = $fieldCustom;
        }
    }

    private function _getMaxCharsFromField($nameField) {
        $maxChars = null;
        if (!$this->_fieldsCustoms) {
            return $maxChars;
        }
        if (count($this->_fieldsCustoms) == 0) {
            return $maxChars;
        }

        foreach ($this->_fieldsCustoms as $fieldCustom) {
            if ($fieldCustom->nf == $nameField) {
                $maxChars = $fieldCustom->maxChars;
                break;
            }
        }

        return $maxChars;
    }

    // 
    public function setFieldsRenderBKBLMaxChars($field = 'bkbl', $maxChars = -6) {
        $this->setFieldsRenderMaxChars($field, $maxChars);
    }

    public function setFieldsRenderMaxChars($fields, $maxChars) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        foreach ($fields as $field) {
            $this->_registerFieldCustom($field, $maxChars);
        }
    }

    public function setFieldsRenderDate($fields) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $this->_fieldsRenderDate = $fields;
    }

    public function setFieldsRenderDateTime($fields) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }
        $this->_fieldsRenderDateTime = $fields;
    }

    public function isFieldRenderDate($nameField) {
        if (!$this->_fieldsRenderDate) {
            return false;
        }

        if (in_array($nameField, $this->_fieldsRenderDate)) {
            return true;
        }

        return false;
    }

    public function isFieldRenderDateTime($nameField) {
        if (!$this->_fieldsRenderDateTime) {
            return false;
        }

        if (in_array($nameField, $this->_fieldsRenderDateTime)) {
            return true;
        }

        return false;
    }

    public function newRowData($data, $namesFields = '', $id = null) {
        if (!$namesFields) {
            $namesFields = $this->_getFieldsFromCols();
        }

        $dataTopic = $this->newRow($id);
        $dataTopic->isData = true;

        if (!is_array($namesFields)) {
            $namesFields = explode(',', $namesFields);
        }

        $index = -1;
        foreach ($namesFields as $nameField) {
            $index += 1;
            $nameField = trim($nameField);
            if (!$nameField) {
                continue;
            }
            if ($this->isColNoShow($index)) {
                continue;
            }

            $nameCol = $this->getNameCol($index);

            if (!isset($data->$nameField)) {
                $data->$nameField = null;
            }

            if ($this->isFieldRenderDate($nameField)) {
                $dataTopic->$nameCol = ExjDate::ConvertToDateDisplay($data->$nameField);
            } elseif ($this->isFieldRenderDateTime($nameField)) {
                $dataTopic->$nameCol = ExjDate::ConvertToDateTimeDisplay($data->$nameField);
            } else {
                $dataTopic->$nameCol = $data->$nameField;
            }

            $limitChars = $this->_getMaxCharsFromField($nameField);
            if (($limitChars != null) && $dataTopic->$nameCol) {
                $valueField = $dataTopic->$nameCol;
                if (strlen($valueField) > abs($limitChars)) {
                    if ($limitChars > 0) {
                        $dataTopic->$nameCol = substr($valueField, 0, $limitChars);
                    } elseif ($limitChars < 0) {
                        $dataTopic->$nameCol = substr($valueField, $limitChars);
                    }
                }
            }
        }

        if ($this->_fieldsToSetterData) {
            foreach ($this->_fieldsToSetterData as $field) {
                if (isset($data->$field)) {
                    $dataTopic->$field = $data->$field;
                } else {
                    $dataTopic->$field = null;
                }
            }
        }

        return $dataTopic;
    }

    public function resetRegisterCols($addLinesToHeders = false) {
        $this->_cols = null;
        $this->_addLinesToHeders = $addLinesToHeders;
        $this->_registerColOrd();
    }

    public function registerCol($header, $nameField, $addLineToHeader = null) {
        if (!$this->_cols) {
            $this->_cols = array();
        }
        $nameField = trim($nameField);

        if ($addLineToHeader === null) {
            $addLineToHeader = $this->_addLinesToHeders;
        }

        if ($addLineToHeader) {
            if ($header && strpos(strtolower($header), "<br/>") === false) {
                $header .= "<br/>&nbsp;";
            }
        }

        $this->_cols[$nameField] = $header;
    }

    private function _getHeadersFromCols() {
        $headers = array();

        if (!$this->_cols) {
            return $headers;
        }

        foreach ($this->_cols as $nameField => $header) {
            $headers[] = $header;
        }

        return $headers;
    }

    private function _getFieldsFromCols() {
        $fields = array();

        if (!$this->_cols) {
            return $fields;
        }

        foreach ($this->_cols as $nameField => $header) {
            $fields[] = $nameField;
        }

        return $fields;
    }

}
?>