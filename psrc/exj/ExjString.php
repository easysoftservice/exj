<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjString {

    /**
     * Remplaza un string por otro todas las coincidencias
     *
     * @param string $strSearch
     * @param string $strReplace
     * @param string $strValue
     * @return string
     */
    public static function ReplaceAll($strSearch, $strReplace, $strValue) {
        if (!$strValue) {
            return $strValue;
        }

        if ($strSearch == $strReplace) {
            return $strValue;
        }

        while (strpos($strValue, $strSearch) !== false) {
            $strValue = str_replace($strSearch, $strReplace, $strValue);
        }

        return $strValue;
    }

    public static function ReplaceApostrofe(&$str, $applyTrim = true) {
        if (!trim($str)) {
            if ($applyTrim) {
                $str = trim($str);
            }
            return $str;
        }

        $str = str_replace("'", "_", $str);
        if ($applyTrim) {
            $str = trim($str);
        }

        return $str;
    }

    public static function HaveNumber($str){
        return (preg_match('/[0-9]/', $str) ? true:false);
    }

    public static function HaveLetter($str){
        return (preg_match('/[a-z]|[áéíóúÁÉÍÓÚñÑ]/i', $str) ? true:false);
    }

    public static function HaveOnlyNumbers($str){
        return (preg_match('/^[0-9]+$/', $str) ? true:false);
    }

    public static function IsTimeHourMin($str){
        return (preg_match('/^[0-9]{2}(h|H|:)[0-9]{2}$/', $str) ? true:false);
    }

    public static function IsDateDMY($str){
        // acepta: 10/02/1976 ó 08-03-2018 no acepta: 32/02/1976 ó 1976-02-10
        return (preg_match('/^([0-2][0-9]|3[0-1])(\/|-)(0[1-9]|1[0-2])\2(\d{4})$/', $str) ? true:false);
    }

    public static function IsDateDB($str){
        return (preg_match('/^(\d{4})\-(\d{2})\-(\d{2})$/', $str) ? true:false);
    }

    public static function IsDateTimeDB($str){
        return (preg_match('/^(\d{4})\-(\d{2})\-(\d{2})\s(\d{2})\:(\d{2})\:(\d{2})$/', $str) ? true:false);
    }


    public static function SplitTags($str){
        return preg_split('/(<[^>]*[^\/]>)/i', $str, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    }

    public static function ConcatWordsWithSpace($str1, $str2){
        if ($str1 && $str2) {
            $lastChar1 = substr($str1, -1, 1);
            $firstChar2 = substr($str2, 0, 1);
            if ($lastChar1 != ' ' && $firstChar2 != ' ') {
                if (self::HaveLetter($lastChar1) && self::HaveLetter($firstChar2)) {
                    // ej: hola mundo
                    return ($str1.' '.$str2);
                }
            }
        }

        // ej: hola,
        return ($str1.$str2);
    }

    public static function ExplodeChars($str){
        return preg_split('//', $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    public static function EsNumeroValido($strValue, $maxDecimales = 5){
        if (empty($strValue)) {
            return false;
        }
        
        // caso que se consideran números validos como: 3,123,36
        // pero no 3,12,36
        // solo acepta máximo 5 decimales
        if (preg_match("/^\d+([,.]\d{3})*([,.]\d{1,$maxDecimales}+)?$/", $strValue)) {
            // Exj::Write(' '.htmlentities($strValue).' número por expresión');
            return true;
        }
        
        return false;
    }

    public static function ExplodeNumbers($strNumber){
        return preg_split('/(?<=\D)(?=\d)|\d+\K/', $strNumber);
    }

    public static function FirstWord($str) {
        if ($str) {
            $str = trim($str);

            $partes = preg_split('/\w+\K/', $str);
            if (count($partes) >= 1) {
                $testWord = trim($partes[0]);
                if ($testWord) {
                    return $testWord;
                }
            }

            $posWord = strpos($str, ' ');
            if ($posWord !== false) {
                $str = substr($str, 0, $posWord);
            }
        }

        return $str;
    }

    public static function IsStrLower($str){
        if (!$str) {
            return false;
        }

        if (!self::HaveLetter($str)) {
            return false;
        }

        return ($str === strtolower($str));
    }

    public static function ConcatFrase($str, $frase){
        if ($frase === '' || $frase === null) {
            return $str;
        }

        if ($str === null) {
            $str = '';
        }

        $frase = ltrim($frase);

        if ($str) {
            $str = rtrim($str);
            
            if ($str) {
                $lastChar = substr($str, -1);
                $tienePunto = ($lastChar=='.');
                if (!$tienePunto && self::HaveLetter($lastChar)) {
                    $str .= '.';
                    $tienePunto = true;
                }

                if ($tienePunto) {
                    $firstChar = substr($frase, 0, 1);
                    if (self::IsStrLower($firstChar)) {
                        $frase = ucfirst($frase);
                    }
                }

                $str .= ' ';
            }
        }

        $str .= $frase;
        return $str;
    }


}

?>