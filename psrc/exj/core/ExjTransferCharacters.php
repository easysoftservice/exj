<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Clase base para hacer encode y decode de caracteres entre ISO y UTF8
 *
 */
class ExjTransferCharacters {

    public static function encodeISOToUTF8(&$object) {
        if (!$object) {
            return;
        }

        if (is_array($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::encodeISOToUTF8($value);
                } elseif (is_string($value)) {
                    $value = mb_check_encoding($value, 'UTF-8') ? $value : utf8_encode($value);
                } elseif (is_object($value)) {
                    self::encodeISOToUTF8($value);
                }
            }
        } elseif (is_object($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::encodeISOToUTF8($value);
                } elseif (is_string($value)) {
                    $value = mb_check_encoding($value, 'UTF-8') ? $value : utf8_encode($value);
                } elseif (is_object($value)) {
                    self::encodeISOToUTF8($value);
                }
            }
        } else {
            $object = mb_check_encoding($object, 'UTF-8') ? $object : utf8_encode($object);
//			$object = utf8_encode($object);
        }
    }

    /**
     * Decodificación de caracteres de utf8 a iso
     */
    public static function decodeUTF8ToISO(&$object) {
        if (is_array($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::decodeUTF8ToISO($value);
                } elseif (is_string($value)) {
                    $value = mb_check_encoding($value, 'UTF-8') ? utf8_decode($value) : $value;
                    //			    $value = utf8_decode($value);
                } elseif (is_object($value)) {
                    self::decodeUTF8ToISO($value);
                }
            }
        } elseif (is_object($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::decodeUTF8ToISO($value);
                } elseif (is_string($value)) {
                    $value = mb_check_encoding($value, 'UTF-8') ? utf8_decode($value) : $value;
                    // $value = utf8_decode($value);
                } elseif (is_object($value)) {
                    self::decodeUTF8ToISO($value);
                }
            }
        } else {
            $object = mb_check_encoding($object, 'UTF-8') ? utf8_decode($object) : $object;
//			$object = utf8_decode($object);
        }
    }

}
?>