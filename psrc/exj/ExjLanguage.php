<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base para los lenguajes de GYMCloud.
 *
 */
class ExjLanguage extends JLanguage {

    private $_isJoomla = true;
    private $_pathCurrentFileLang = '';
    private $_isExtensionBase = false;

    function __construct($lang = null, $isExtensionBase = false) {
        $this->_isExtensionBase = $isExtensionBase;

        parent::__construct($lang);
    }

    static function & CreateInstance($lang, $isExtensionBase = false) {
        $instance = new ExjLanguage($lang, $isExtensionBase);
        $reference = & $instance;
        return $reference;
    }

    function _($string, $jsSafe = false) {
        if (!$string) {
            return $string;
        }

        if (!trim($string)) {
            return $string;
        }

        if (!$this->_isJoomla && $this->_pathCurrentFileLang) {
            if (!defined($string)) {
                $key = strtoupper($string);
                $key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;
                if (!isset($this->_strings[$key])) {
                    if ($this->_addKeyValueLang($key, $string)) {
                        $this->_strings[$key] = $string;
                    }
                }
            }
        }

        return parent::_($string, $jsSafe);
    }

    function load($extension = 'selfi', $basePath = JPATH_BASE, $lang = null, $reload = false) {
        if ($this->_isExtensionBase) {
            $extension = Exj::NAME_SPACE;
        } else {
            $nameComponent = ExjRequest::GetParam('nameComponent');
            if (!$nameComponent) {
                $nameComponent = ExjRequest::GetParam('option');
            }

            if ($nameComponent) {
                $extension = $nameComponent;
            }
        }

        //	echo "<br/>basePath: $basePath extension: $extension lang: $lang";
        //	echo " this->_isExtensionBase: ". ($this->_isExtensionBase ? 'SI':'NO');
//		return parent::load($extension, $basePath, $lang, $reload);

        if (!$lang) {
            $lang = $this->_lang;
        }

        $path = JLanguage::getLanguagePath($basePath, $lang);

        if (!strlen($extension)) {
            $extension = 'joomla';
        }

        $this->_isJoomla = ($extension == 'joomla');

        $filename = ( $extension == 'joomla' ) ? $lang : $lang . '.' . $extension;
        $filename = $path . DS . $filename . '.ini';

        $result = false;
        if (isset($this->_paths[$extension][$filename]) && !$reload) {
            // Strings for this file have already been loaded
            $result = true;
        } else {
            if ($extension != 'joomla') {
                if ($this->_validateCreateFileLang($filename)) {
                    $this->_pathCurrentFileLang = $filename;
                }
            }

            // Load the language file
//			echo "<br/>Cargando archivo: $filename";
            $result = $this->_load($filename, $extension);

            // Check if there was a problem with loading the file
            if ($result === false) {
                // No strings, which probably means that the language file does not exist
                $path = JLanguage::getLanguagePath($basePath, $this->_default);
                $filename = ( $extension == 'joomla' ) ? $this->_default : $this->_default . '.' . $extension;
                $filename = $path . DS . $filename . '.ini';

//				echo "<br/>Cargando de nuevo archivo: $filename";
                $result = $this->_load($filename, $extension, false);
            }
        }

        return $result;
    }

    private function _addKeyValueLang($key, $string) {
        $pathFile = $this->_pathCurrentFileLang;
        if (!$pathFile) {
            //		echo "<br/>No se pudo add key, no existe el path actual del lenguaje";
            return false;
        }

        if (!is_writable($pathFile)) {
//			echo "<br/>No es escribible el archivo: ($pathFile)";
            return false;
        }

        if (!$handle = fopen($pathFile, 'a')) {
            //	     echo "<br/>Cannot open file ($pathFile)";
            return false;
        }

        $contentFile = "$key=$string\n";

        if (fwrite($handle, $contentFile) === FALSE) {
            //    echo "<br/>Cannot write to file ($pathFile)";
            fclose($handle);
            return false;
        }

        fclose($handle);
        return true;
    }

    private function _validateCreateFileLang($pathFile) {
        if (file_exists($pathFile)) {
            return true;
        }

        if (!$handle = fopen($pathFile, 'w')) {
            //	     echo "<br/>Cannot open file ($pathFile)";
            return false;
        }

        $fecha = Exj::GetDateTime();
        $nameFile = basename($pathFile);

        $contentFile = '# $Id';
        $contentFile .= ": $nameFile $fecha $\n";
        $contentFile .= '# ' . Exj::GetTitleApp() . "! Project\n";
        $contentFile .= "# Copyright (C) 2018. All rights reserved.\n";
        $contentFile .= "# License Privative.\n";
        $contentFile .= "# Note: All ini files need to be saved as UTF-8 - No BOM\n";
        $contentFile .= "\n";

        if (fwrite($handle, $contentFile) === FALSE) {
            //    echo "<br/>Cannot write to file ($pathFile)";
            fclose($handle);
            return false;
        }

        fclose($handle);
        return true;
    }

}
?>