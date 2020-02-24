<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjTypesVar {

    const INT = 'int';
    const STRING = 'string';
    const DATE = 'date';
    const DATETIME = 'datetime';
    const BOOL = 'bool';
    const FLOAT = 'float';
    const CONJUNTO = 'conjunto';

    private $_typeVar;
    private $_isNullable = false;
    private $_formatDate;
    private $_precisionFloat = false; // valor false no aplica precisión

    public function __construct($typeVar = 'string', $isNullable = false, $formatDate = '', $precisionFloat = false) {
        if (!$typeVar) {
            $typeVar = self::STRING;
        }

        $this->_typeVar = strtolower($typeVar);
        $this->_isNullable = $isNullable;
        $this->_precisionFloat = $precisionFloat;

        if (!$formatDate) {
            if ($this->isDate()) {
                $formatDate = $this->getFormatDateDefault();
            }
            if ($this->isDateTime()) {
                $formatDate = $this->getFormatDateTimeDefault();
            }
        }

        $this->_formatDate = $formatDate;

        return $this;
    }

    protected function getFormatDateDefault() {
        return '%d-%m-%Y';
    }

    protected function getFormatDateTimeDefault() {
        return '%d-%m-%Y %H:%M:%S';
    }

    public function setFormatDate($formatDate) {
        $this->_formatDate = $formatDate;
    }

    static function Int($isNullable = false) {
        return (new ExjTypesVar(ExjTypesVar::INT, $isNullable));
    }

    static function String($isNullable = false) {
        return (new ExjTypesVar(ExjTypesVar::STRING, $isNullable));
    }

    static function Date($isNullable = false, $formatDate = '') {
        return (new ExjTypesVar(ExjTypesVar::DATE, $isNullable, $formatDate));
    }

    static function DateTime($isNullable = false, $formatDate = '') {
        return (new ExjTypesVar(ExjTypesVar::DATETIME, $isNullable, $formatDate));
    }

    static function Bool($isNullable = false) {
        return (new ExjTypesVar(ExjTypesVar::BOOL, $isNullable));
    }

    static function Float($isNullable = false, $precision = false) {
        return (new ExjTypesVar(ExjTypesVar::FLOAT, $isNullable, '', $precision));
    }

    static function Float2Decimals($isNullable = false) {
        return (self::Float($isNullable, 2));
    }

    public function getFormatDate() {
        return $this->_formatDate;
    }

    public function isNullable() {
        return $this->_isNullable;
    }

    public function isInt() {
        return ($this->_typeVar == self::INT);
    }

    public function isString() {
        return ($this->_typeVar == self::STRING);
    }

    public function isConjunto() {
        return ($this->_typeVar == self::CONJUNTO);
    }

    public function isDate() {
        return ($this->_typeVar == self::DATE);
    }

    public function isDateTime() {
        return ($this->_typeVar == self::DATETIME);
    }

    public function isBool() {
        return ($this->_typeVar == self::BOOL);
    }

    public function isFloat() {
        return ($this->_typeVar == self::FLOAT);
    }

    public function isNumber() {
        return ($this->isInt() || $this->isFloat());
    }

    public function renderValue($value) {
        if ($value === null && $this->isNullable()) {
            return null;
        }

        $valueRenderer = $value;
        switch ($this->_typeVar) {
            case self::STRING:
                $valueRenderer .= '';
                if (!$valueRenderer && $valueRenderer != '0') {
                    $valueRenderer = '';
                }
                break;

            case self::BOOL:
                $valueRenderer = ($valueRenderer ? true : false);
                break;

            case self::INT:
                if (!$valueRenderer) {
                    $valueRenderer = 0;
                }

                $valueRenderer = intval($valueRenderer);
                break;

            case self::FLOAT:
                if (!$valueRenderer) {
                    $valueRenderer = 0;
                }

                $valueRenderer = floatval($valueRenderer);
                if ($this->_precisionFloat !== false && is_numeric($this->_precisionFloat)) {
                    $valueRenderer = sprintf("%01." . $this->_precisionFloat . 'f', $valueRenderer);
                }
                break;

            case self::DATETIME:
            case self::DATE:
                if (!$valueRenderer) {
                    if ($this->isDate()) {
                        $valueRenderer = '1900-01-01';
                    }
                    if ($this->isDateTime()) {
                        $valueRenderer = '1900-01-01 00:00:00';
                    }
                }

                $valueRenderer = strftime($this->_formatDate, strtotime($valueRenderer));
                break;
        }

        if ($this->isNumber() && is_nan($valueRenderer)) {
            $valueRenderer = 0;
        }

        return $valueRenderer;
    }

}

?>