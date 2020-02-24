<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Utilitario para textos y traducciones de idiomas.
 * Al hacer una traducción de texto usando métodos estáticos como _() y __() la equivalencia del texto lo tomará de 
 * un archivo con extensión .ini, estos archivos estarán bajo la carpeta: language, dentro de esta una subcarpeta con el prefijo del idioma,
 * y dentro de este un archivo con el nombre del componente, por ejemplo para el componente: com_sfi_drivers, el archivo de lenguaje estaria en el siguiente path:
 * language/eng/eng.com_sfi_drivers.ini, dentro del archivo habrán las equivalencia del texto, en donde habria que cambiar su equivalente al idioma que corresponda.
 */
class ExjText {

    /**
     * Translates a string into the current language
     *
     * @access	public
     * @param	string $string The string to translate
     * @param	boolean	$jsSafe		Make the result javascript safe
     * @since	2.0
     *
     */
    static function _($string, $jsSafe = false) {
        if (!$string) {
            return $string;
        }


        $lang = & self::GetLanguage();
        return $lang->_($string, $jsSafe);
    }

    /**
     * Traduce un arreglo de objetos
     *
     * @param array $items
     * @param mixed $fields Una cadena de campos separados por coma o un Array
     * @return bool true si se pudo traducir, sino retorna false
     */
    static function _ArrayObjects(&$items, $fields) {
        if (!$items) {
            return false;
        }

        if (!is_array($items)) {
            return false;
        }

        if (!count($items)) {
            return true;
        }

        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        /*
          echo "<br/>" . __METHOD__. " fields: $fields ". '<br/>';
          print_r($fields);
         */

        foreach ($items as &$item) {
            foreach ($fields as $f) {
                $f = trim($f);
                if (!$f) {
                    continue;
                }
                if (!isset($item->$f)) {
                    continue;
                }

                $item->$f = str_replace("\r\n", "<br/>", $item->$f);
                $item->$f = str_replace("\n", "<br/>", $item->$f);

                $item->$f = self::_($item->$f);
            }
        }

        return true;
    }

    /**
     * Devuelve la equivalencia del texto según el lenjuage que el usuario tenga asignado.
     *
     * @param string $string
     * @param bool $jsSafe
     * @return string Traducción del texto pasado en el primer parámetro.
     */
    static function __($string, $jsSafe = false) {
        if (!$string) {
            return $string;
        }

        $lang = & self::GetLanguageBase();
        return $lang->_($string, $jsSafe);
    }

    /**
     * Get a language object
     *
     * Returns a reference to the global {@link JLanguage} object, only creating it
     * if it doesn't already exist.
     *
     * @access public
     * @return object JLanguage
     */
    static function &GetLanguage() {
        static $instance;

        if (!is_object($instance)) {
            //get the debug configuration setting
            $conf = & JFactory::getConfig();
            $debug = $conf->getValue('config.debug_lang');

            $instance = self::_createLanguage();
            $instance->setDebug($debug);
        }

        // self::TestPrint($instance);

        return $instance;
    }

    static function &GetLanguageBase() {
        static $instance2;

        if (!is_object($instance2)) {
            //get the debug configuration setting
            $conf = & JFactory::getConfig();
            $debug = $conf->getValue('config.debug_lang');

            $instance2 = self::_createLanguage(true);
            $instance2->setDebug($debug);
        }

        //	self::TestPrint($instance2);

        return $instance2;
    }

    /**
     * Create a language object
     *
     * @access private
     * @return object JLanguage
     * @since 2.0
     */
    static function &_createLanguage($isExtensionBase = false) {
        jimport('joomla.language.language');

        $conf = & JFactory::getConfig();
        $locale = $conf->getValue('config.language');
        //	echo "<br/>locale: $locale ";

        $langAcronym = ExjUser::GetAcronimoLenguaje();
        if ($langAcronym) {
            $locale = trim(strtolower($langAcronym));
        }

        $lang = & ExjLanguage::CreateInstance($locale, $isExtensionBase);
        $lang->setDebug($conf->getValue('config.debug_lang'));

        return $lang;
    }

    static function TestPrint(ExjLanguage $lang) {
        echo "<br/>Pruebas de lenguaje:<br/>";
        print_r($lang->getPaths());
        // print_r($lang);
        echo "<br/>";
    }

}
?>