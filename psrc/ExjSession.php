<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base que gestiona la sesión de la aplicación web.
 *
 */
class ExjSession extends ExjObject {

    private $_pk = 'pk';

    public function __construct() {

        if (!isset($_SESSION['pk']) || !$_SESSION['pk']) {
            $_SESSION['pk'] = 1020;         // <-- start fake pks at 1020
            $_SESSION['rs'] = 'exj';    // <-- populate $_SESSION with data.
        }
    }

    // fake a database pk
    public function pk() {
        return $_SESSION['pk'] ++;
    }

    // fake a resultset
    public function rs() {
        return $_SESSION['rs'];
    }

    public function insert($rec) {
        array_push($_SESSION['rs'], $rec);
    }

    public function update($idx, $attributes) {
        $_SESSION['rs'][$idx] = $attributes;
    }

    public function destroy($idx) {
        return array_shift(array_splice($_SESSION['rs'], $idx, 1));
    }

    /* ----------------- */

    /**
     * Envia algun valor a sesión, con una frase clave
     *
     * @param string $pk
     * @param mixed $value
     * @param bool $overwrite
     * @return bool true si ha sido satisfactorio
     */
    protected function setWithPK($pk, $value, $overwrite = true) {
        // 	echo "<br/>Inicio de "  . __METHOD__ . " pk: $pk ";

        if (!$pk) {
            return false;
        }
        if (!$overwrite && isset($_SESSION[$pk])) {
            return false;
        }

        // ExjLog::info("ExjSession. setWithPK. pk: $pk value: ".print_r($value, true));

        $_SESSION[$pk] = $value;

        return true;
    }

    protected function addWithPK($pk, $value) {
        if (!$pk) {
            return false;
        }
        if (!isset($_SESSION[$pk])) {
            $_SESSION[$pk] = array();
        } elseif ($_SESSION[$pk] === null) {
            $_SESSION[$pk] = array();
        }

        $_SESSION[$pk][] = $value;
        return true;
    }

    /**
     * Devuelve el valor q ha sido seteado en sesión
     *
     * @param string $pk
     * @param mixed $valueDefault
     * @return midex El valor seteado, si no se ha seteado antes se devuelve el valor por defecto
     */
    protected function getWithPK($pk, $valueDefault = null) {
        // echo "<br/>Inicio de " . __METHOD__. " pk = $pk";

        if (!$pk) {
            return $valueDefault;
        }
        if (!isset($_SESSION[$pk])) {
            return $valueDefault;
        }

        // echo "<br/>Se retorno valor OK";

        return $_SESSION[$pk];
    }

    public function setterObjToSession($objToSetter) {
        if (!$this->copyObjToThis($objToSetter)) {
            return false;
        }

        $pk = $this->_pk;
        //	echo "<br/>Probando " . __METHOD__ . " pk = $pk";

        if (!$pk) {
            return false;
        }

        if (!isset($_SESSION[$pk])) {
            return false;
        }

        $objSession = &$_SESSION[$pk];
        if (!$objSession) {
            return false;
        }

        if (!is_object($objSession)) {
            //		echo "<br/>Objeto de sesion no es un objeto.";
            return false;
        }

        $varsObj = get_object_vars($objToSetter);
        foreach ($varsObj as $name => $value) {
            if (!isset($objSession->$name) || is_object($value)) {
                //			echo "<br/>No se setea el Campo: $name Valor: " . (is_object($value) ? 'objeto': $value);
                continue;
            }

            //		echo "<br/>Seteando a sesion Campo: $name Valor: $value";

            $objSession->$name = $value;
        }

        //	echo "<br/>Seteo con éxito de la clase: " . get_class($this);

        return true;
    }

    public function saveValueToSession($name, $value, $noSetFixValue = false) {
        $pk = $this->_pk;

        if (!$pk) {
            return false;
        }

        if (!isset($_SESSION[$pk])) {
            return false;
        }

        $obj = &$_SESSION[$pk];
        if (!$obj) {
            return false;
        }

        if (is_object($obj)) {
            if (!isset($obj->$name)) {
                if (!$noSetFixValue) {
                    return false;
                }
            }
            $obj->$name = $value;
        } else if (is_array($obj)) {
            if (!isset($obj[$name])) {
                if (!$noSetFixValue) {
                    return false;
                }
            }
            $obj[$name] = $value;
        } else {
            global $exj;

            $exj->setErrorValidating("Objeto de sesión no es Objeto.<br/>No se pudo guardar en sesión. name: $name el valor: $value.<br/>Clave usada: $pk");
            return false;
        }

        return true;
    }

    public function setPKToSession($pk) {
        if (!is_string($pk)) {
            $pk .= '';
        }
        $this->_pk = $pk;
    }

    public function setToSession($value, $overwrite = true) {
        return $this->setWithPK($this->_pk, $value, $overwrite);
    }

    public function getFromSession($valueDefault = null) {
        return $this->getWithPK($this->_pk, $valueDefault);
    }

    public function autoResetToSession($pk = 'pk') {
        $valueReset = null;
        if (!isset($_SESSION[$pk])) {
            $_SESSION[$pk] = null;
            return $valueReset;
        }

        $value = $_SESSION[$pk];
        if (is_array($value)) {
            $valueReset = array();
        } elseif (is_object($value)) {
            $valueReset = null;
        } elseif (is_string($value)) {
            $valueReset = '';
        } elseif (is_numeric($value)) {
            $valueReset = 0;
        }

        $_SESSION[$pk] = $valueReset;

        return $valueReset;
    }

    public function addToSession($value) {
        return $this->addWithPK($this->_pk, $value);
    }

    public static function Set($name, $value, $namespace = 'exjDefault') {
        $session = & JFactory::getSession();

        /*
        if ($value && (is_array($value) || is_object($value))) {
            $tipo = is_array($value) ? 'ARRAY('.count($value).')' : get_class($value);
            ExjLog::info(
                "ExjSession::Set $namespace name: $name tipo: $tipo value: ".print_r($value, true)
            );
        }
        */

        if (!empty($value)) {
            /*
            $tipo = '';
            if (is_array($value)) {
                $tipo = 'ARRAY('.count($value).')';
            }
            elseif (is_object($value)) {
                $tipo = get_class($value);
            }
            elseif (is_string($value)) {
                $tipo = "string(".strlen($value).")";
            }
            elseif (is_bool($value)) {
                $tipo = 'bool';
            }
            elseif (is_numeric($value)) {
                $tipo = 'numeric';
            }
            
            ExjLog::info(
                "ExjSession::Set $namespace name: $name tipo: $tipo value: ".print_r($value, true)
            );
            */

            // se debe enviar en utf8
            if (is_array($value) || is_object($value) || is_string($value)) {
                Exj::TrasferCharsEncodeISOToUTF8($value);
            }
        }

        return $session->set($name, $value, $namespace);
    }

    public static function &Get($name, $default = null, $namespace = 'exjDefault') {
        $session = & JFactory::getSession();

        return $session->get($name, $default, $namespace);
    }

    static function Has($name, $namespace = 'exjDefault') {
        $session = & JFactory::getSession();
        return $session->has($name, $namespace);
    }

    static function IsActive() {
        $session = & JFactory::getSession();
        return ($session->getState() == 'active');
    }

    static function IsNew() {
        $session = & JFactory::getSession();
        return $session->isNew();
    }

    public static function CreateAccessReadOnly() {
        $hMenu = new ExjHelperMenu();
        $hMenu->fixAccessReadOnly();

        return $hMenu;
    }

    public static function CreateAccessDelete() {
        $hMenu = new ExjHelperMenu();
        $hMenu->fixAccessOnlyTrash();

        return $hMenu;
    }
    
    public static function CreateAccessNewEditDelete() {
        $hMenu = new ExjHelperMenu();
        $hMenu->fixFullAccess();
        $hMenu->isReports = false;

        return $hMenu;
    }

}

?>