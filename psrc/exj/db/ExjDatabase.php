<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Manejador de base de datos.
 * Con esta clase se podrá hacer consultas a la base de datos, o hacer persistencia de datos.
 */
class ExjDatabase extends ExjObject {
    const SEG_DEMORA_MAX = 3;

    protected $_db;
    private $_isAutoCommit = false;
    private $_isStartedTransaction = false;
    private $_timeIniQuery = -1;
    private $_segundosDemoraLastQuery = -1;

    /**
     * Decodifoca los caracteres de UTF8 a ISO
     *
     * @var bool Por defecto true
     */
    public $decodeUTF8toISO = true;

    public function __construct() {
        $this->_isAutoCommit = false;
        $this->_db = & JFactory::getDBO();
    }

    public function getDBO(){
        return $this->_db;
    }

    public static function GetNameDB(){
        $nameDB = trim(self::GetResultFromQuery('SELECT database() AS name_db'));
        if (!$nameDB) {
            throw new Exception("Error Base de datos. Cambio de Usr, sin DBname", 1);
        }
        // echo '<br>nameDB: ' . $nameDB;

        return $nameDB;
    }

    public function changeUserPwd($usr, $pwd){
        $this->getDBO()->_resource->change_user($usr, $pwd, self::GetNameDB());
        return $this;
    }

    public static function ChangeUserPwdDB($usr, $pwd, $instanceDB=null){
        if (!$instanceDB) {
            $instanceDB = Exj::InstanceDatabase();
        }

        return $instanceDB->changeUserPwd($usr, $pwd);
    }


    public function setQuery($sql, $offset = 0, $limit = 0) {
        $this->_timeIniQuery = microtime(true);

        $this->_db->setQuery($sql, $offset, $limit);
        return $this;
    }

    /**
     * Obtiene array de objetos de la base de datos
     * @param string $query
     * @param bool $writeQuery
     * @return array de objetos, si ocurre error false
     */
    public static function GetObjectList($query, $writeQuery = false) {
        global $exj;
        $db = Exj::InstanceDatabase();
        $items = $db->loadObjectList($query);
        if (Exj::GetError()->haveError()) {
            return false;
        }
        
        if (!$db->isValid()) {
            return false;
        }
        
        if($writeQuery){
            $db->writeLastQuery();
        }

        return $items;
    }
    
    /**
     * Obtiene un objeto desde la base de datos si existe sino null
     * @param string $query
     * @param bool $writeQuery
     * @return object Si no se encontró null, si error false
     */
    public static function GetObjectFromQuery($query, $writeQuery = false) {
        $db = Exj::InstanceDatabase();

        $obj = null;
        $db->setQuery($query)->loadObject($obj);
        
        if (Exj::GetError()->haveError()) {
            return false;
        }
        
        if($writeQuery){
            $db->writeLastQuery();
        }

        return $obj;
    }
    
    /**
     * Resultado de la consulta, es decir, el primer campo de la consulta
     *
     * @param string $query
     * @param bool $writeQuery
     * @return string|null|bool false si exsite error
     */
    public static function GetResultFromQuery($query, $writeQuery = false) {
        global $exj;
        $db = Exj::InstanceDatabase();

        $db->setQuery($query);
        $res =  $db->loadResult();
        if (Exj::GetError()->haveError()) {
            ExjLog::error('GetResultFromQuery', Exj::GetError());
            return false;
        }
        
        if($writeQuery){
            $db->writeLastQuery();
        }

        return $res;
    }
    
    public static function ExecuteQuery($query, $writeQuery = false) {
    	$db = Exj::InstanceDatabase();
        $db->query($query);
        if ($db->getErrorMsg()) {
            return false;
        }
        
        if($writeQuery){
            $db->writeLastQuery();
        }
        
        // $nRowsAffected = $db->getAffectedRows();
        return true;
    }

    public static function GetAffectedRowsOfLastQuery() {
        $db = Exj::InstanceDatabase();
        $num = $db->getAffectedRows();
        if (!$num) {
            $num = 0;
        }

        return $num;
    }

    public static function GetLastError() {
        $db = Exj::InstanceDatabase();
        $errorMsg = $db->getErrorMsg();

        return $errorMsg;
    }


    public function loadObjectList($query = '', $offset = 0, $limit = 0) {
        if ($query) {
            $this->setQuery($query, $offset, $limit);
        }

        //	echo "<br/>query: $query";

        $list = $this->_db->loadObjectList();
        $this->validateError();

        //	echo '<br/>'. __METHOD__ . " ES VALIDO: " . ($this->isValid() ? 'si':'no');

        $this->_decode($list);

        return $list;
    }

    private function _decode(&$obj) {
        if (!$obj) {
            return $obj;
        }

        if ($this->decodeUTF8toISO) {
            if ($obj) {
                ExjTransferCharacters::decodeUTF8ToISO($obj);
            }
        } else {
            $this->decodeUTF8toISO = true;
        }
    }

    private $_lastErrorDB = '';

    protected function validateError() {
        global $exj;

        $lastQuery = $this->getQuery();

        if (!$this->isValid()) {
            $errorDb = $this->getErrorMsg();
            $this->_lastErrorDB = $errorDb;

            Exj::LogWrite($errorDb, ExjError::TIPO_ERROR_DATABASE);
            //	$this->writeErrorClassLn($this, $errorDb);
            Exj::LogWriteDelayed(__CLASS__);
            
            if (ExjUser::IsRolSuperAdmin()) {
            	$resError = $errorDb;
            	if (strlen($resError) > 300) {
            		$resError = substr($resError, 0, 300).'...';
            	}
            	
            	Exj::PrintBackTrace("ERROR SQL. $resError");
            }

            Exj::SetErrorDB($errorDb);
            return Exj::GetError()->msgError;
        }

        $this->_validateQueryDelayed($lastQuery);

        return false;
    }

    /**
     * Obtiene los segundos que se tomó de la última consulta
     *
     * @return float
     */
    public function getSegundosDemoraLastQuery() {
        return $this->_segundosDemoraLastQuery;
    }

    private function _validateQueryDelayed($strQuery) {
        if ($this->_timeIniQuery != -1) {
            $this->_segundosDemoraLastQuery = round(microtime(true) - $this->_timeIniQuery, 2);

            if ($this->_segundosDemoraLastQuery > self::SEG_DEMORA_MAX) {
                global $exj;

                $strQuery .= "<br/>Consulta demoró $this->_segundosDemoraLastQuery segundos";

                Exj::LogWrite($strQuery, ExjError::TIPO_ERROR_DELAYED);
            }
        } else {
            Exj::LogWriteDelayed($strQuery);
        }
    }

    public function haveError() {
        return Exj::GetError()->haveError();
    }

    /**
     * Indica si es válido las consutas envidas
     *
     * @param bool $validateWithLastError
     * @return bool
     */
    public function isValid($validateWithLastError = true) {
        $msgError = $this->getErrorMsg();
        if ($msgError) {
        	// echo "<br><b>isValid</b>. msgError:<br>$msgError";
            return false;
        }

        if ($validateWithLastError && $this->_lastErrorDB) {
            return false;
        }

        return true;
    }

    /**
     * Retorna el número de error
     *
     * @access public
     * @return int El número de error para la consulta reciente
     */
    function getErrorNum() {
        return $this->_db->getErrorNum();
    }

    /**
     * Retorna el mesnaje de error
     *
     * @access public
     * @return string El mensaje de error de la consulta reciente
     */
    public function getErrorMsg($escaped = false) {
        if ($this->_lastErrorDB) {
            return $this->_lastErrorDB;
        }

        return trim($this->_db->getErrorMsg($escaped));
    }

    /**
     * Retorna la consulta activa
     *
     * @access public
     * @return string The current value of the internal SQL vairable
     */
    public function getQuery() {
        return $this->_db->getQuery();
    }

    public function writeLastQuery() {
        $lastQuery = $this->getQuery();

        $segDemora = $this->getSegundosDemoraLastQuery();
        if ($segDemora != -1) {
            $lastQuery .= "<br>Demora: $segDemora segundos";
        }

        echo '<br>' . $lastQuery;
    }

    /**
     * Ejecuta una consulta
     *
     * @access public
     * @return mixed A database resource if successful, FALSE if not.
     */
    public function query($query = '') {
        if ($query) {
            $this->setQuery($query);
        }

        $res = $this->_db->query();
        $this->validateError();
        return $res;
    }

    /**
     * Ejecuta varias consultas
     *
     * @access public
     * @param bool $abort_on_error Por defecto true
     * @param bool $p_transaction_safe Por defecto false
     * @return bool true se ha sidi satisfactorio, sino false.
     */
    public function queryBatch($abort_on_error = true, $p_transaction_safe = false) {
        $res = $this->_db->queryBatch($abort_on_error, $p_transaction_safe);
        $this->validateError();
        return $res;
    }

    /**
     * Retorna número de filas afectadas de una consulta reciente
     *
     * @access public
     * @return int The number of affected rows in the previous operation
     * @since 1.0.5
     */
    function getAffectedRows() {
        return $this->_db->getAffectedRows();
    }

    function loadResult($sql = '') {
        if ($sql) {
            $this->setQuery($sql);
        }

        $res = $this->_db->loadResult();
        $this->validateError();
        return $res;
    }

    /**
     * Carga la primera fila de una consulta dentro de un objeto
     *
     * @access public
     * @param object
     */
    public function loadObject(&$obj) {
        $obj = $this->_db->loadObject();
        $this->validateError();

        $this->_decode($obj);

        return $obj;
    }

    public function loadRowList() {
        $obj = $this->_db->loadRowList();
        $this->validateError();

        $this->_decode($obj);

        return $obj;
    }

    /**
     * Retorna el ID generado desde una operación INSERT previa
     *
     * @access public
     * @return int
     */
    public function insertid() {
        return $this->_db->insertid();
    }

    public function isAutoCommitDB() {
        $autoCommit = $this->loadResult("SELECT @@AUTOCOMMIT;");
        if ($this->getErrorMsg()) {
            global $exj;
            Exj::SetErrorDB($this->getErrorMsg());
            return null;
        }
        $autoCommit = intval($autoCommit);

        return ($autoCommit ? true : false);
    }

    public function autoCommitDisable($forseUpdate = false) {
        if (!$forseUpdate && $this->_isAutoCommit) {
            return true;
        }
        $this->setQuery('SET AUTOCOMMIT = 0;');
        $this->query();
        if ($this->getErrorMsg()) {
            global $exj;
            Exj::SetErrorDB($this->getErrorMsg());
            return false;
        }

        $this->_isAutoCommit = true;
        return true;
    }

    private function _setErrorToBase($msg) {
        Exj::SetErrorValidating('Base de Datos. ' . $msg);
    }

    private function _printDebugBackTrace($metodo, $strLine='') {
        Exj::PrintBackTrace("Método: $metodo " . ($strLine ? "Ln: $strLine":''). 'Trasas:');
        return $this;
    }

    public function transactionCommit() {
        if (!$this->_isStartedTransaction) {
            $this->_setErrorToBase("No se puede hacer commit, no se iniciado transacción");
            $this->_printDebugBackTrace(__METHOD__);
            return false;
        }
        
        // $this->_printDebugBackTrace(__METHOD__);
        $this->query("COMMIT;");
        $error = $this->validateError();
        $this->_isStartedTransaction = false;
        return ($error ? false : true);
    }

    public function transactionRollback() {
        if (!$this->_isStartedTransaction) {
            $this->_setErrorToBase("No se puede hacer roolback, no se iniciado transacción");
            $this->_printDebugBackTrace(__METHOD__);
            return false;
        }

        // test xxx
        // $this->_printDebugBackTrace(__METHOD__ . ' OK');

//		echo "<br/>DB: ". __METHOD__;
        $this->query("ROLLBACK;");
        $error = $this->validateError();
        $this->_isStartedTransaction = false;
        return ($error ? false : true);
    }

    public function transactionStart() {

        // ttt
//		echo '<br/> -> '.__METHOD__.'<br/>';
        //	debug_print_backtrace();

        if ($this->_isStartedTransaction) {
            echo "ADVERTENCIA. " . __METHOD__ . " Ya se a iniciado transacción, iniciado de nuevo una transacción...";
            $this->_printDebugBackTrace(__METHOD__);
        }

        $this->setQuery("SET AUTOCOMMIT = 0;START TRANSACTION;");
        if ($this->queryBatch()) {
            $this->_isStartedTransaction = true;
            return true;
        }
        return false;
    }

    public function isStartedTransaction() {
        return $this->_isStartedTransaction;
    }

}

?>