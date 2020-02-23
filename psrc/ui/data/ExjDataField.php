<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjDataField {

    const TYPE_AUTO = 'auto';
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_FLOAT = 'float';
    const TYPE_DATE = 'date';
    const TYPE_BOOL = 'boolean';

    public $name;
    public $type = '';
    public $useNull = false;

    public function __construct($name, $type = '') {
        $this->name = $name;
        if (!$type) {
            // tipo auto no hace conversión
            $type = self::TYPE_AUTO;
        }

        $this->type = $type;
    }

    public static function Create($name, $type=''){
        $df = new ExjDataField($name, $type);
        return $df;
    }

    public static function CreateBool($name){
        return self::Create($name, self::TYPE_BOOL);
    }

    public static function RendererValue($value, $type) {
        if ($value === null || $value === '' || !$type) {
            return $value;
        }

        if (is_object($value) || is_array($value)) {
            return $value;
        }

        switch ($type) {
            case self::TYPE_INT:
                $valueInt = intval($value);
                if (is_nan($valueInt)) {
                    echo "<br>ERROR RendererValue. $value no es entero";
                }
                else {
                    $value = $valueInt;
                }
            break;

            case self::TYPE_FLOAT:
                $valueFloat = floatval($value);
                if (is_nan($valueFloat)) {
                    echo "<br>ERROR RendererValue. $value no es float";
                }
                else {
                    $value = $valueFloat;
                }
            break;

            case self::TYPE_BOOL:
                if ($value === 'false') {
                    $value = false;
                }
                else {
                    $value = boolval($value);
                }
            break;            
        }

        return $value;
    }

    public function setUseNull($value=true){
        $this->useNull = $value;
        return $this;
    }

    public function apply($field){
        if (!$field || is_string($field)) {
            return $this;
        }

        foreach ($field as $prop => $value) {
            if (!$prop) {
                continue;
            }

            $this->$prop = $value;
        }

        return $this;
    }
}

?>