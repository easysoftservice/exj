<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Objeto base de la aplicación
 */
class ExjObject {

    const ERROR_CODE_DEFAULT = 1100;
    const PREFIX_COMP_FRAMEWORK = 'exj_';
    const PREFIX_COMP_APP = 'app_';
    const PREFIX_TABLES_APP = 'app_';
    const END_LINE = "\n";

    private $_bufferDebug = null;
    private $_enableBufferDebug = false;
    private $_msgError = '';
    private $_exceptFieldsToObject = null;
    
    public static function Merge($obj1, $obj2){
    	return ((object)array_merge((array)$obj1, (array)$obj2));
    }

    /**
     * Informa si a ocurrido un error
     *
     * @return bool
     */
    public function haveError() {
        return ($this->_msgError ? true : false);
    }

    public function getErrorMsg() {
        return $this->_msgError;
    }
    
    protected function clearErrorMsg() {
        $this->_msgError = '';
    	return $this;
    }

    /**
     * Obtiene el nombre de la clase del modelo instanciado
     *
     * @return string
     */
    public function getClassStr($prefixStr = '<br/>Clase:') {
        if (!$prefixStr) {
            return get_class($this);
        }

        return $prefixStr . ' ' . get_class($this);
    }

    /**
     * Setea mensaje de error
     *
     * @param mixed $errorMsg Puede ser tipo string o una instancia de ExjModels
     * @return bool true si se seteo el mensaje de error
     */
    protected function setErrorMsg($errorMsg) {
        if ($errorMsg && $errorMsg instanceof ExjModels) {
            return $this->setErrorModel($errorMsg);
        }
        if (!$errorMsg && $this->_msgError) {
            return false;
        }

        $this->_msgError = $errorMsg;
        return $errorMsg;
    }

    protected function setErrorModel(ExjModels $model) {
        $brokenRules = $model->getBrokenRules();
        if (!$brokenRules) {
            $brokenRules = "ERROR DESCONOCIDO!";
        }

        $brokenRules .= "<br/>Referencia: " . get_class($model);
        return $this->setErrorMsg($brokenRules);
    }

    /**
     * Envia un mensaje de error a la base $exj
     *
     * @param string $errorMsg
     */
    protected function setErrorMsgToBase($errorMsg) {
        $this->setErrorMsg($errorMsg);
        if ($errorMsg) {
            global $exj;
            $exj->setErrorValidating($errorMsg);
        }
    }

    public static function GetErrorsReferences() {
        $errorsRefs = array("odbc_connect", 'Cannot open file', 'ODBC Visual FoxPro Driver', 'ODBC', 'SQL error');
        $errorsRefs[] = 'SELECT ';
        $errorsRefs[] = 'INSERT ';
        $errorsRefs[] = 'UPDATE ';
        $errorsRefs[] = 'DELETE ';

        return $errorsRefs;
    }

    /**
     * Retorna mensaje de error para presentar al usuario final
     *
     * @param string $msgErrorCustom No requerido, si no se especifica se toma de la fn getErrorMsg
     * @return string Si no hay error se retorna cadena vacia
     */
    public function getErrorMsgDisplay($msgErrorCustom = '') {
        $error = $msgErrorCustom;
        if (!$error) {
            $error = $this->getErrorMsg();
        }

        if (!$error) {
            return "";
        }

        $errorsRefs = self::GetErrorsReferences();

        $codeError = self::ERROR_CODE_DEFAULT;
        $error = strtolower($error);
        foreach ($errorsRefs as $errorRef) {
            $codeError += 1;
            $pos = strpos($error, strtolower($errorRef));
            if ($pos !== false) {
                $codeError .= 'x1';
                break;
            }
        }

        $errorMsgDisplay = "ERROR $codeError.";
        $errorMsgDisplay .= ' ' . ExjText::__("An error occurred try again later.");

        return $errorMsgDisplay;
    }

    /**
     * Devuelve un array de campos de esta clase en este orden: [name][value]
     *
     * @return array Arreglo de 2 dimensiones
     */
    public function getVars() {
        return get_object_vars($this);
    }

    public function bufferDebugEnable($enable = true) {
        $this->_enableBufferDebug = $enable;
    }

    public function isEnableDebug() {
        return $this->_enableBufferDebug;
    }
    
    public function printDebug($str, $convHTMLEnt = false){
    	if ($this->_enableBufferDebug) {
    		Exj::Write($str === null ? 'NULO': ($convHTMLEnt ? htmlentities($str):$str));
    	}
    	
    	return $this;
    }


    
    public function printLabelValue($lbl, $value){
    	if (!$this->_enableBufferDebug) {
    		return $this;
    	}
    	
    	if ($value !== null && is_bool($value)) {
    		$value = ($value ? 'SI':'NO');
    	}
    	return $this->printDebug(' '.$lbl.': ')->printDebug($value, true);
    }
    
    public function printBreakLabelValue($lbl, $value){
    	return $this->printLabelValue("<br><b>$lbl</b>", $value);
    }
    
    public function printBreak(){
    	return $this->printDebug('<br>');
    }

    /**
     * Escribe en consola el primer parámetro. Para mostar en consola usar antes bufferDebugEnable()
     *
     * @param mixed $text Puede ser string, object o array
     * @param string $endLine
     * @return bool
     */
    public function writeDebug($text, $endLine = "<br/>") {
        if (!$this->_enableBufferDebug) {
            return false;
        }

        if ($text !== null) {
            return false;
        }

        Exj::Write($text);
        Exj::Write($endLine);

        return true;
    }

    public function bufferDebugAdd($msg, $metodo = '') {
        if (!$this->_enableBufferDebug) {
            return false;
        }

        if (!$this->_bufferDebug) {
            $this->_bufferDebug = array();
        }

        if ($metodo) {
            $msg = "METODO: $metodo. " . $msg;
        }

        $this->_bufferDebug[] = $msg;
        return true;
    }

    public function getBufferDebugStr($inHTML = true) {
        if (!$this->_bufferDebug) {
            return '';
        }

        $sep = "\n";
        $str = '';
        if ($inHTML) {
            $sep = "<br/>";
            $str = "<b>DEPURACION</b>:<br/>";
        }

        $str .= implode($sep, $this->_bufferDebug);

        return $str;
    }

    public function bufferDebugPrint($inHTML = true) {
        $str = $this->getBufferDebugStr($inHTML);
        if (!$str) {
            return false;
        }

        Exj::Write($str);
        return true;
    }

    /**
     * Convierte un array en un objeto
     *
     * @param array $array
     * @param bool $fieldsToLower
     * @return object
     */
    public static function ConvertArrayToObject($array, $fieldsToLower = false) {
        if (!is_array($array)) {
            return $array;
        }

        $object = new stdClass();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $name => $value) {
                $name = trim($name);
                if ($fieldsToLower) {
                    $name = strtolower($name);
                }
                if (!$name) {
                    continue;
                }

                $object->$name = self::ConvertArrayToObject($value);
            }
            return $object;
        } else {
            return false;
        }
    }

    public function convertObjectToArray($object, $setNameIndexArray = true) {
        if (!is_object($object)) {
            return $object;
        }

        $arreglo = array();
        if (is_object($object)) {
            $vars = get_object_vars($object);

            foreach ($vars as $name => $value) {
                if ($setNameIndexArray) {
                    $arreglo[$name] = $this->convertObjectToArray($value);
                } else {
                    $arreglo[] = $this->convertObjectToArray($value, false);
                }
            }

            return $arreglo;
        } else {
            return array($object);
        }
    }

    /**
     * Devuelve un array de los campos de este objeto
     *
     */
    function getFieldsOfThisObj($exceptPrefix = '_') {
        $vars = $this->getVars();
        $fields = array();
        $nExcept = strlen($exceptPrefix);
        foreach ($vars as $name => $value) {
            if ($nExcept && ($nExcept <= strlen($name))) {
                if (substr($name, 0, $nExcept) == $exceptPrefix) {
                    continue;
                }
            }

            $fields[] = $name;
        }

        return $fields;
    }

    function loadFromRequest($paramEncodeJson = '', $valueDefault = '') {
        $vars = $this->getVars();

        if ($paramEncodeJson) {
            global $exj;
            $objDecode = $exj->getParamRequestDecode($paramEncodeJson, $valueDefault);
            if ($objDecode && is_object($objDecode)) {
                $this->copyObjToThis($objDecode);
                return true;
            }
        }

        foreach ($vars as $name => $value) {
            $this->$name = JRequest::getVar("$name", $valueDefault);
        }

        return true;
    }
    
    public static function PrintBackTrace($message='', $maxTrace=15){
    	if (function_exists('debug_backtrace')) {
            echo '<div align="left">';
            if ($message) {
                if (is_string($message)) {
                    Exj::Write("<br><b>$message</b>");
                } else {
                    Exj::Write('<br>'.$message);
                }
            }
            $debugTraces = debug_backtrace();
            if (!$debugTraces || count($debugTraces) == 0) {
                Exj::Write("<b>No hay back trace.</b>");
            }

            $lnTrace = 0;
            foreach ($debugTraces as $back) {
                if (@$back['file']) {
                    $lnTrace += 1;
                	// echo '<br />' . str_replace( JPATH_ROOT, '', $back['file'] ) . ':' . $back['line'];
                    Exj::Write("<br />[$lnTrace] " . $back['file'] . ': ' . $back['line']);
                    
                    if ($maxTrace && $lnTrace >= $maxTrace) {
                    	break;
                    }
                }
            }
            echo '</div>';
        } else {
            Exj::Write("<br>No existe la función: debug_backtrace");
        }
    }

    public function writeBackTrace($message = '') {
        if (function_exists('debug_backtrace')) {
            echo '<div align="left">';
            if ($message) {
                if (is_string($message)) {
                    Exj::WriteLn("<b>$message</b>", '');
                } else {
                    Exj::WriteLn($message);
                }
            }
            $debugTraces = debug_backtrace();
            if (!$debugTraces || count($debugTraces) == 0) {
                Exj::WriteLn("No hay back trace. " . get_class($this));
            }

            foreach ($debugTraces as $back) {
                if (@$back['file']) {
                    // echo '<br />' . str_replace( JPATH_ROOT, '', $back['file'] ) . ':' . $back['line'];
                    Exj::Write('<br/>' . $back['file'] . ': ' . $back['line']);
                }
            }
            echo '</div>';
        } else {
            Exj::WriteLn("No existe la función: debug_backtrace");
        }
    }

    public function writeClassLn($instanceClass, $msg, $addBackTrace = true, $endLine = '<br/>', $colorText = 'blue') {
        if ($colorText) {
            echo "<div style='color: " . $colorText . "'>";
        }

        if ($instanceClass && is_object($instanceClass)) {
            Exj::Write('<b>' . get_class($instanceClass) . '</b> ');
        }

        Exj::WriteLn($msg, $endLine);
        if ($addBackTrace) {
            $this->writeBackTrace("SEGUIMIENTO");
        }

        if ($colorText) {
            echo "</div>";
        }
    }

    public function writeErrorClassLn($instanceClass, $msg, $endLine = '<br/>') {
        $this->writeClassLn($instanceClass, $msg, true, $endLine, 'red');
    }

    /**
     * Asigna un valor a todos los campos del objeto, excepto los del 2do parametro
     *
     * @param mixed $valueDefault Valor por defecto
     * @param string $prefixEcept
     */
    function assignValueToFields($valueDefault, $prefixEcept = '_') {
        $varsObj = get_object_vars($this);
        $nPrefix = strlen($prefixEcept);
        foreach ($varsObj as $name => $value) {
            if ($name == '_bufferDebug' || $name == '_enableDebug') {
                continue;
            }

            if ($nPrefix > 0) {
                if (strlen($name) >= $nPrefix) {
                    if (substr($name, 0, $nPrefix) == $prefixEcept) {
                        continue;
                    }
                }
            }

            $this->$name = $valueDefault;
        }

        return $this;
    }

    public function toObject($prefixEcept = '_') {
        $obj = new stdClass();
        $varsObj = get_object_vars($this);
        $nPrefix = strlen($prefixEcept);
        $exceptFields = $this->_exceptFieldsToObject;

        foreach ($varsObj as $name => $value) {
            if ($name == '_exceptFieldsToObject') {
                continue;
            }

            if ($nPrefix > 0) {
                if (strlen($name) >= $nPrefix) {
                    if (substr($name, 0, $nPrefix) == $prefixEcept) {
                        continue;
                    }
                }
            }

            if (!empty($exceptFields) && in_array($name, $exceptFields)) {
                continue;
            }

            $obj->$name = $value;
        } // foreach

        return $obj;
    }

    public function setExceptFieldsToObject($values){
        if (is_array($values) || $values === null) {
            if ($this->_exceptFieldsToObject) {
                $this->_exceptFieldsToObject = array_merge(
                    $this->_exceptFieldsToObject, $values
                );
            }
            else{
                $this->_exceptFieldsToObject = $values;
            }
        }
        elseif ($values = trim($values)) {
            if ($this->_exceptFieldsToObject) {
                if (in_array($values, $this->_exceptFieldsToObject)) {
                    return $this;
                }
            }
            else{
                $this->_exceptFieldsToObject = array();
            }

            $this->_exceptFieldsToObject[] = $values;
        }
        
        return $this;
    }

    /**
     * Convierte las propiedades de este objeto a un string con formato para params
     *
     * @param string $prefixEcept
     * @return string
     */
    public function toStrParams($prefixEcept = '_') {
        $obj = $this->toObject($prefixEcept);

        $varsObj = get_object_vars($obj);

        $strParams = array();
        foreach ($varsObj as $name => $value) {
            $strParams[] = "$name=$value";
        }

        $strParams = implode(self::END_LINE, $strParams);

        return $strParams;
    }

    public function getNumFieldsSetteds() {
        $numFieldsSetteds = 0;

        $fields = $this->getFieldsOfThisObj();
        foreach ($fields as $field) {
            $value = $this->$field;
            if ($value == '') {
                continue;
            }

            if ($value) {
                $numFieldsSetteds += 1;
            }
        }

        return $numFieldsSetteds;
    }

    /**
     * Carga valores de este objeto desde params
     *
     * @param string $strParams
     * @return bool
     */
    public function loadValuesFromParams($strParams) {
        if (!$strParams || !is_string($strParams)) {
            return false;
        }

        $params = explode(self::END_LINE, $strParams);
        $objToSetter = new stdClass();
        foreach ($params as $param) {
            $keyValues = explode('=', $param);
            if (count($keyValues) == 0) {
                continue;
            }

            $name = $keyValues[0];
            $value = '';
            if (isset($keyValues[1])) {
                $value = $keyValues[1];
            }

            $objToSetter->$name = $value;
        }

        //	print_r($objToSetter);

        return $this->bindData($objToSetter);
    }

    /**
     * Carga las propiedades de la clase actual al objeto pasado por parámetro
     *
     * @param object $obj
     */
    function loadValuesToObj(&$obj) {
        $vars = get_object_vars($obj);
        foreach ($vars as $name => $value) {
            if (!isset($this->$name)) {
                continue;
            }
            $obj->$name = $this->$name;
        }
    }

    /**
     * Copia las propiedades del objeto a la clase actual, la clase y el objeto deben tener la misma estructura
     *
     * @param object $obj
     * @return int Nro de campos seteados, o retorna false si el parámetro no es un objeto
     */
    public function copyObjToThis($obj) {
        if (!is_object($obj)) {
            return false;
        }

        $numSetteds = 0;

        $varsObj = get_object_vars($this);
        foreach ($varsObj as $name => $value) {
            if (!isset($obj->$name)) {
                continue;
            }
            $this->$name = $obj->$name;

            $numSetteds += 1;
        }

        return $numSetteds;
    }

// copyObjToThis

    public function bindData($obj) {
        return $this->copyObjToThis($obj);
    }

    /**
     * Compara la estructura del Objeto con la pasado en el Parámetro
     *
     * @param string $msgError
     * @param Object $obj
     * @param Array o string $fieldsCompare Si es string debe separarse por una coma
     * @return boolean false si ha ocurrido algún error
     */
    function structureIsEqual(&$msgError, $obj, $fieldsCompare = '', $compareValues = false) {
        $msgError = '';

        if (!$obj) {
            $msgError = "Objeto no definido";
            return false;
        }
        if (!is_object($obj)) {
            $msgError = "No se ha especificado un Objeto";
            return false;
        }

        if ($fieldsCompare) {
            if (!is_array($fieldsCompare)) {
                $fieldsCompare = explode(',', $fieldsCompare);
            }
            foreach ($fieldsCompare as &$fieldCompare) {
                $fieldCompare = trim($fieldCompare);
            }
        }

        $varsObj = get_object_vars($this);
        foreach ($varsObj as $name => $value) {
            if ($fieldsCompare) {
                if (!in_array($name, $fieldsCompare)) {
                    continue;
                }
            }
            if (!isset($obj->$name)) {
                $msgError = "El Objeto no tiene el campo: $name";
                break;
            }

            if ($compareValues) {
                if ($value != $obj->$name) {
                    $msgError = "Valores diferentes para campo: $name. Valores: " . $value . ' != ' . $obj->$name;
                    break;
                }
            }
        } // foreach

        if ($msgError) {
            return false;
        }

        return true;
    }

    function convertStrListToArray($strList, $separator = ',') {
        if (is_array($strList)) {
            return $strList;
        }

        $listArray = array();
        $strList = trim($strList);
        if (!$strList) {
            return $listArray;
        }

        $listArray = explode($separator, $strList);
        foreach ($listArray as &$item) {
            $item = trim($item);
        }

        return $listArray;
    }

}

?>