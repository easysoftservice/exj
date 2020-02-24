<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Manejador de base de datos con adodb.
 *
 */
class ExjADODB extends ExjObject {
	const DATE_MIN_EMPTY = '1899-12-30';
	const TIME_LIMIT_DELAYED = 6; // segundos
	
	const SEG_RETRY_SLEEP = 2; // segundos de espera para reintento de consulta
	const SEG_RETRY_MAX = 21; // maximo tiempo de espera para reintentos
	
	
	private $_db = null;
	private $_isConnected=false;
	private $_numRetryQuery=0;
	
	private $_conn_dsn='';
	private $_conn_uid='';
	private $_conn_pwd='';
	
	private $_conn_sourceDB='';
	private $_conn_driver='';
	
	private $_errorMsg='';
	private $_errorRef='';
	
	private $_query_fields='*';
	private $_query_from='';
	private $_query_where='';
	private $_query_pag_limit=-1;
	private $_query_pag_offset=-1;
	private $_applyTrimToValuesItems=true;

	private $_query_sort='';
	private $_query_dir='ASC';
	private $_query_query='';
	private $_query_value='';
	private $_withPagination=true;
	
	private $_isInitialized = false;
	private $_log = null;
	private $_timeDelayed = -1;
	private $_lastQuery = null;
	
	/**
	 * Constructor de ExjADODB
	 *
	 */
	public function __construct(){
		
	}
	
	private function _validateInitialization(){
		if ($this->_isInitialized) {
			return true;
		}
		
		require(self::GetDirADODB().'/adodb.inc.php');
		
		/*
		echo __FUNCTION__ . '<br/>';
		debug_print_backtrace();
		*/
		
    	$this->initDB($this->_db);
    	
    	$this->connect();
		
		$this->_isInitialized = true;
		
		return true;
	}
	
	/**
	 * Escribe en el archivo de log para esta clase
	 *
	 * @param mixed $valueLog1
	 * @param midex $valueLog2
	 */
	public function writeLog($valueLog1, $valueLog2 = null){
		if (!$this->_log) {
			$this->_log = new ExjHandlerLogData(__CLASS__);
			$this->_log->disableVerificateLastTimeToWrite();
		}
		
		$this->_log->write($valueLog1, $valueLog2);
	}
	
	public function writeErrorLog($scope=''){
		$error = $this->getErrorMsg();
		if (!$error) {
			return ;
		}
		
		$this->writeLog($error, $scope);
	}

	/**
	 * Overwrited Mensaje a presentar al usuario.
	 *
	 * @param string $msgErrorCustom
	 * @return string
	 */
	public function getErrorMsgDisplay($msgErrorCustom = ''){
		$errorMsgDisplay = parent::getErrorMsgDisplay($msgErrorCustom);
		if (!$errorMsgDisplay) {
			return $errorMsgDisplay;
		}
		
		$this->writeLog($errorMsgDisplay, $this->getErrorMsg());
		
		return $errorMsgDisplay;
	}
	
	static function GetDirADODB(){
		return Exj::GetPathBase() . '/components/exj_base/lib/php/adodb/adodb5';
	}
	
	static function IsDateEmpty($strDate){
		if (!$strDate) {
			return false;
		}
		
		$strDate = trim($strDate);
		if (strtotime($strDate) <= strtotime(self::DATE_MIN_EMPTY)) {
			return true;
		}
		
		return false;
	}
	
	static function IsDate($str){
		if (!$str) {
			return false;
		}
		
		if (!is_string($str)) {
			return false;
		}
		
		$str = trim($str);
		if (!$str) {
			return false;
		}
		
		if (strlen($str) != 10) {
			return false;
		}
		
		// 1899-12-30
		
		if (ereg("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $str)) {
			return true;
		}
		
		return false;
	}
	
	protected function initDB(&$db){
		$this->_db = new ADODB_odbc();
	}
	
	public function connect(){
		if ($this->_isConnected) {
			return true;
		}
		
    	$this->initConnectDSN($this->_conn_dsn);
    	$this->initConnectLogin($this->_conn_uid, $this->_conn_pwd);
    	
    	$this->initConnectDriver($this->_conn_driver);
    	if (!$this->_conn_dsn) {
    		$this->initConnectSourceDB($this->_conn_driver);
    	}
    	
    	$connStr = $this->_getConnectString();
    	
    	if ($connStr) {
    		try {
    			$this->_db->Connect($connStr);
    		}
    		catch (Exception $ex){
    			$this->_setError(Exj::MSG_ERROR_DISPLAY);
    			$this->writeLog(__FUNCTION__, $ex->getMessage());
    		}
    		
    		if ($this->_db->ErrorMsg()) {
    			$this->_setError(Exj::MSG_ERROR_DISPLAY);
    			$this->writeLog(__FUNCTION__, $this->_db->ErrorMsg());
    			
    			return false;
    		}
    		
	    	if (!$this->haveError()) {
				$this->_isConnected = true;
	    	}
    	}
    	else {
    		$this->_setError('ERROR. No se ha definido conexiones');
    	}

		// $this->writeLog(__FUNCTION__, 'CONECTADO: '. ($this->_isConnected ? 'SI':'NO'));
    	
    	return $this->_isConnected;
	}
	
	public function applyTrimToValuesItems($applyTrimToValuesItems = true){
		$this->_applyTrimToValuesItems = $applyTrimToValuesItems;
	}
	
	private function _setError($errorMsg, $addError = false){
		if ($addError) {
			
		}
		$this->_errorMsg = $errorMsg;
		
		$this->writeLog(__METHOD__, $errorMsg);
	}
	
	protected function setError($errorMsg){
		$this->_setError($errorMsg);
	}
	
	
	public function disconnect(){
		if (!$this->_isConnected) {
			return ;
		}
		
		$this->_db->Disconnect();
		$this->_isConnected = false;
	}
	
	/**
	 * Indica si ha ocurrido algún error
	 *
	 * @return bool
	 */
	public function haveError(){
		if ($this->isRetryingQuery()) {
			return false;
		}
		if ($this->_db && $this->_db->ErrorMsg()) {
			return true;
		}
		
		return ($this->_errorMsg ? true:false);
	}
	
	private function _setErrorReference($errorRef){
		$this->_errorRef = $errorRef;
	}
	
	/**
	 * Indica si se puede presentar el error al cliente
	 *
	 * @return bool
	 */
	public function canShowError(){
		if ($this->isErrorFromDB()) {
			return false;
		}
		
		return true;
	}

	/**
	 * Indica si el error se generó desde la base de datos
	 *
	 * @return bool
	 */
	public function isErrorFromDB(){
		if ($this->_db && $this->_db->ErrorMsg()) {
			return true;
		}
		
		
		return false;
	}
	
	public function getErrorMsgDB(){
		if ($this->_db && $this->_db->ErrorMsg()) {
			return $this->_db->ErrorMsg();
		}
		
		return "";
	}
	
	/**
	 * Devuele el mensaje de error, si no ha ocurrido algún error retorna texto vacio
	 *
	 * @return string
	 */
	public function getErrorMsg($convertTildesToHTML=false){
		$msgError = '';
		if ($this->_db && $this->_db->ErrorMsg()) {
			$msgError = $this->_db->ErrorMsg();
		}
		
		if (!$msgError) {
			$msgError = $this->_errorMsg;
		}
		
		if ($msgError && $this->_errorRef) {
			$msgError .= '<br/><b>REFERENCIA</b>:<br/>'.$this->_errorRef;
		}
		elseif ($this->_errorRef){
			$msgError = 'ERROR.<br/>'.$this->_errorRef;
		}
		
		if ($convertTildesToHTML) {
			ExjHelper::convertCharsTildeToHTML($msgError);
		}
		
		
		return $msgError;
	}
	
	public function isConnected(){
		return $this->_isConnected;
	}
	
	private function _getConnectString(){
		if (!$this->_conn_dsn && !$this->_conn_sourceDB) {
			return false;
		}
		
		$conn = array();
		
		if ($this->_conn_dsn) {
			$conn[] = "DSN=$this->_conn_dsn";
		}
		
		if ($this->_conn_sourceDB && $this->_conn_driver) {
			$conn[] = "Driver=$this->_conn_driver";
			$conn[] = "SourceDB=$this->_conn_sourceDB";
			$conn[] = "SourceType=DBC";
			$conn[] = "Exclusive=No";
			$conn[] = "BackgroundFetch=No";
			$conn[] = "Collate=Machine";
			$conn[] = "Null=Yes";
			$conn[] = "Deleted=Yes";
		}
		
		if (count($conn) == 0) {
			return false;
		}
		
		$conn[] = "Uid=$this->_conn_uid";
		$conn[] = "Pwd=$this->_conn_pwd";
		
		$conn = implode(';', $conn);
		if ($conn) {
			$conn .= ';';
		}
		
		return $conn;
	}

	protected function initConnectDSN(&$dsn){
		
	}
	
	protected function initConnectLogin(&$uid, &$pwd){
		$uid = '';
		$pwd = '';
	}

	protected function initConnectDriver(&$driver){
		
	}
	
	protected function initConnectSourceDB(&$sourceDB){
		
	}
	
	private function _dispathResponseError(ExjResponse &$response){
		global $exj;
		
		$msgError = $this->getErrorMsg();
		if ($msgError && !$exj->haveError() && !ExjUser::IsModeDebug()) {
			$exj->setErrorDB($msgError);
		}
		
		if (!$msgError && $exj->haveError()) {
			$msgError = $exj->getErrorMsg();
		}
		
		if (!$msgError) {
			return $response;
		}
		
		$response->setMsgError($msgError);
		
		return $response;
	}

	/**
	 * Retorna un objeto ExjResponse, si existe un error se envia el mensaje del error
	 *
	 * @param bool $executeQuery Si se fija en true se ejecuta la consulta seteada anteriormente
	 * @param object $dataResponse
	 * @return ExjResponse
	 */
	public function dispatchResponse($executeQuery=false, $disconnect = true, $dataResponse=null){
		$response = new ExjResponse();
		
		if ($this->haveError()) {
			if ($disconnect) { $this->disconnect(); }
			return $this->_dispathResponseError($response);
		}
		
		if ($dataResponse) {
			$response->setDataObject($dataResponse);
		}
		
		if ($executeQuery) {
			$total = $this->executeQueryCount();
			if ($this->haveError()) {
				if ($disconnect) { $this->disconnect(); }
				return $this->_dispathResponseError($response);
			}
			
			$topics = $this->executeQueryObjectList();
			if ($this->haveError()) {
				if ($disconnect) { $this->disconnect(); }
				return $this->_dispathResponseError($response);
			}
			
			$response->setDataTopics($topics, $total);
		}
		
		if ($disconnect) { $this->disconnect(); }
		
		return $response;
	}

	public function dispatchResponseQuery($dataResponse=null, $disconnect = true){
		return $this->dispatchResponse(true, $disconnect, $dataResponse);
	}
	
	
	public function setQueryFIELDS($fields){
		$this->_query_fields = $fields;
	}
	
	public function setQueryFROM($query_from){
		$this->_query_from = $query_from;
	}
	
	private $_debug=false;
	
	public function enableDebug($enable = true){
		$this->_debug = $enable;
	}
	
	protected function printDebug($text, $endLine='<br/>'){
		if (!$this->_debug) {
			return ;
		}
		
		echo '-> '.$text . $endLine;
	}
	
	public function setQueryWHERE($query_where, $addCondition=true, $conditionSQL = 'AND'){
		$this->printDebug("Add condición a consulta: $query_where");
		
		if (!$addCondition) {
			$this->_query_where = $query_where;
			return ;
		}
		
		if ($this->_query_where) {
			$this->_query_where .= " $conditionSQL ";
		}
		
		$this->_query_where .= $query_where;
	}
	
	public function addConditionANDToQuery($condition){
		$this->setQueryWHERE($condition, true);
	}
	public function addConditionORToQuery($condition){
		$this->setQueryWHERE($condition, true, 'OR');
	}
	
	public function validateWithResponse(ExjResponse &$response){
		if ($this->haveError()) {
			$response->setMsgError($this->getErrorMsg());
			return false;
		}
		
		return true;
	}
	
	
	public function addConditionFieldDateValueANDToQuery($field, $value, $operator='='){
		$this->addConditionFieldValueANDToQuery($field, $value, ExjTypesVar::Date(), $operator);
	}

	public function addConditionFieldDateBetweenANDToQuery($field, $valueFrom, $valueUntil){
		$this->addConditionFieldDateValueANDToQuery($field, $valueFrom, '>=');
		$this->addConditionFieldDateValueANDToQuery($field, $valueUntil, '<=');
	}

	public function addConditionFieldStringANDToQuery($field, $value, $operator='='){
		$this->addConditionFieldValueANDToQuery($field, $value, ExjTypesVar::String(), $operator);
	}
	
	public function addConditionFieldLikeRightANDToQuery($field, $value){
		$this->addConditionFieldStringANDToQuery($field, $value.'%', 'LIKE');
	}

	public function addConditionFieldLikeLeftANDToQuery($field, $value){
		$this->addConditionFieldStringANDToQuery($field, '%'.$value, 'LIKE');
	}
	public function addConditionFieldLikeAllANDToQuery($field, $value){
		$this->addConditionFieldStringANDToQuery($field, '%'.$value.'%', 'LIKE');
	}
	
	
	/**
	 * Adiciona una condicion con IN
	 *
	 * @param string $field
	 * @param string $value
	 */
	public function addConditionFieldConjuntoANDToQuery($field, $value){
		$this->addConditionFieldValueANDToQuery($field, $value, ExjTypesVar::Conjunto(), 'IN');
	}
	
	public function addConditionFieldValueANDToQuery($field, $value, ExjTypesVar $typeVar, $operator='='){
		$condition = $field . " $operator ";
		
		
		$useComillas = true;
		
		if ($typeVar->isDate() || $typeVar->isDateTime()) {
			$useComillas = false;
			if ($typeVar->isDate()){
				$typeVar->setFormatDate('%Y/%m/%d');
			}
			else {
				$typeVar->setFormatDate('%Y/%m/%d %H:%M:%S');
			}
			
			
			$value = $typeVar->renderValue($value);
			$value = '{^'. $value .'}';
		}
		else {
			$value = $typeVar->renderValue($value);
			
			if ($typeVar->isBool()) {
				$useComillas = false;
				$value = ($value ? 'TRUE':'FALSE');
			}
			
			if ($typeVar->isConjunto()) {
				$useComillas = false;
			}
		}
		
		
		
		if ($value === null) {
			$useComillas = false;
			$value = 'NULL';
		}
		
		
		if ($useComillas) {
			$condition .= '"'. $value.'"';
		}
		else {
			$condition .= $value;
		}
		
	//	 echo "<br/>condition: $condition";
		
		$this->addConditionANDToQuery($condition);
	}
	
	public function setParamsCriteria($paramsCriteria, $nameModelCriteria){
		if (!$paramsCriteria) {
			return false;
		}
		if (!$nameModelCriteria) {
			$this->_setError("No se indicó el nombre del modelo criteria");
			return false;
		}
				
		$ClassCriteria = Exj::GetNameClassCriteria($nameModelCriteria);
		
	//	echo "ClassCriteria: $ClassCriteria<br/>";
	//	print_r($paramsCriteria);
//		return true;
		
		$criteria = new $ClassCriteria(false);
		if (!($criteria instanceof ExjCriteriaModel)) {
			$this->_setError("La clase criteria: $ClassCriteria debe heredar de la clase: ExjCriteriaModel");
			return false;
		}
		
		
		if ($criteria->bind($paramsCriteria)) {
			 $criteria->addConditionsToBaseADODB($this);
		}
		
		return true;
	}
	
	public function getAliasTableFromField($nameField, $searchInFields=true){
		$this->printDebug(__METHOD__ . " Parámetro: $nameField");
		
		$aliasTable = $this->_getAliasTableFromString($nameField, $this->_query_from);

		if (!$aliasTable && $searchInFields) {
			$aliasTable = $this->_getAliasTableFromString($nameField, $this->_query_fields);
		}
		
		$this->printDebug(__METHOD__ . " Retornando: $aliasTable");
		
		/*
		echo '<br/>PROBANDO...';
		echo "<br/>codigo = |" . $this->getFieldFromAlias('codigo', 'codigo').'|';
		echo "<br/>codigox = |" . $this->getFieldFromAlias('cod', 'codigox as cod').'|';
		echo "<br/>codigo = |" . $this->getFieldFromAlias('codigo', 'codigo, nombre').'|';
		echo "<br/>nombre = |" . $this->getFieldFromAlias('nombre', 'codigo,nombre').'|';
		echo "<br/>nombrex = |" . $this->getFieldFromAlias('nombrex', 'codigo,nombre').'|';
		echo "<br/>nombre = |" . $this->getFieldFromAlias('Name', 'codigo,nombre').'|';
		echo "<br/>codigo = |" . $this->getFieldFromAlias('cod', 'codigo AS cod,nombre').'|';
		echo "<br/>nombre = |" . $this->getFieldFromAlias('nom', 'codigo AS cod,nombre nom').'|';
		echo "<br/>nombrex = |" . $this->getFieldFromAlias('nom', 'codigo AS cod,nombrex as nom').'|';
		echo "<br/>sum(x) = |" . $this->getFieldFromAlias('suma', 'codigo AS cod,sum(x) as suma').'|';
		echo "<br/>sum(x) = |" . $this->getFieldFromAlias('sum', 'codigo AS cod,sum(x) as sum').'|';
		*/
		
		return $aliasTable;
	}
	
	public function getFieldFromAlias($nameAlias, $fields=''){
		$this->printDebug(__METHOD__ . " Parámetro: $nameAlias");
		// codigo, nombre
		// codigo,nombre
		// codigo cod, nombre nom
		// codigo AS cod, nombre AS nom
		$nameField = trim($nameAlias);
		
		if (!$fields) {
			$fields = $this->_query_fields;
		}
		
		$fields = trim($fields);
		if (!$fields) {
			return $nameField;
		}
		if (strtolower($fields) == strtolower($nameAlias)) {
			return $fields;
		}
		
		$fieldsArray = explode(",", $fields);
		
		foreach ($fieldsArray as $fieldAlias) {
			$fieldAlias = trim($fieldAlias);
			if (strtolower($fieldAlias) == strtolower($nameAlias)) {
				$nameField = $fieldAlias;
				break;
			}
			
			$posFinal = strripos($fieldAlias, $nameAlias);
			if ($posFinal !== false) {
				$posIni = strripos($fieldAlias, ' ');
				if ($posIni !== false) {
					$nameField = substr($fieldAlias, 0, $posIni);
					
					$posAS = strripos($nameField, ' as');
					if ($posAS !== false) {
						$nameField = substr($fieldAlias, 0, $posAS);
					}
					$nameField = trim($nameField);
					break;
				}
			}
		}
		
		$this->printDebug(__METHOD__ . " Retornando: $nameField");
		
		return $nameField;
	}
	
	private function _getAliasTableFromString($nameField, $text){
		$aliasTable = '';
		$nameField = trim($nameField);
		$text = trim($text);
		
		if (!$nameField || !$text) {
			return $aliasTable;
		}
		
		self::DelCharExcept($text);
		
		$posFinal = strpos($text, $nameField);
		if ($posFinal === false) {
			return $aliasTable;
		}
		$posFinal = $posFinal-1;
		if ($posFinal <= 0) {
			return $aliasTable;
		}
		
		
		
//		$charsSeparators = array(" ", ",");
		
		$posIni = $posFinal;
		$offsetChar = 0;
		while (--$posIni > 0) {
			$char = substr($text, $posIni, 1);
			// in_array($char, $charsSeparators)
			if ($char == ' ' || ($char == ',')) {
				$offsetChar = 1;
				break;
			}
		}
		if ($posIni <= 0) {
			return $aliasTable;
		}
		
		$aliasTable = substr($text, $posIni+$offsetChar, $posFinal-$posIni-$offsetChar);
		$aliasTable = trim($aliasTable);
		return $aliasTable;
	}
	
	static function DelCharExcept(&$str){
		if (!$str) {
			return ;
		}
		
		$charExcept = array("", "´");
		
		$str = str_replace($charExcept, "",  $str);
	}
	
	public function setQueryLIMIT($limit, $offset=0){
		$this->_query_pag_limit = $limit;
		$this->_query_pag_offset = $offset;
	}
	
	public function getQueryPaginationOffset(){
		return $this->_query_pag_offset;
	}
	public function getQueryPaginationLimit(){
		return $this->_query_pag_limit;
	}
	public function isFirstPagePagination(){
		return ($this->_query_pag_offset <= 0);
	}
	
	public function getNumberPagePagination(){
		$numPage = 0;
		if ($this->_query_pag_limit) {
			$numPage = intval($this->_query_pag_offset / $this->_query_pag_limit)+1;
		}
		
		// echo "<br/>numPage: $numPage <br/>";
		return $numPage;
	}
	
	private function _readParamsRequest(){
		if ($this->_query_pag_limit <= 0 && $this->_withPagination) {
			$this->_query_pag_offset = ExjRequest::GetParam('start', 0);
			$this->_query_pag_limit = ExjRequest::GetParam('limit', 0);
		}
		
		if (!$this->_fixedQuerySort) {
			$this->_query_sort = ExjRequest::GetParam('sort', '');
			$this->_query_dir = ExjRequest::GetParam('dir', "ASC");
		}
		
		$this->_query_query = ExjRequest::GetParam('query', '');
		$this->_query_value = ExjRequest::GetParam('value', '');
		
		$this->printDebug("PARAMETROS LEIDOS DESDE REQUEST: sort: $this->_query_sort dir: $this->_query_dir limit: $this->_query_pag_limit start: $this->_query_pag_offset query: $this->_query_query");
	}
	
	public function resetQuery(){
		$this->_query_fields = '*';
		$this->_query_from = '';
		$this->_query_where = '';
		$this->_query_pag_limit = -1;
		$this->_query_pag_offset = -1;
		
		$this->_query_sort = '';
		$this->_query_dir = 'ASC';
		$this->_query_query = '';
		$this->_query_value = '';
		$this->_withPagination = true;
		$this->_fixedQuerySort = false;
		$this->_orderFields = null;
		$this->_timeDelayed = -1;
		$this->_numRetryQuery = 0;
		$this->_lastQuery = null;
	}
	
	private $_fixedQuerySort = false;
	private $_orderFields = null;
	
	public function addQueryOrderFields($nameFieldFrom, $nameFieldTo){
		$nameFieldFrom = trim($nameFieldFrom);
		
		if (!$this->_orderFields) {
			$this->_orderFields = array();
		}
		
		$this->_orderFields[$nameFieldFrom] = $nameFieldTo;
	}
	
	private function _getFieldAlias($nameFieldKey){
		if (!$this->_orderFields) {
			return $nameFieldKey;
		}
		$nameFieldKey = trim($nameFieldKey);
		
		if (isset($this->_orderFields[$nameFieldKey])) {
			return $this->_orderFields[$nameFieldKey];
		}
		
		return $nameFieldKey;
	}
	
	public function setQueryOrderSQL($sqlOrder, $dir=''){
		$this->_query_sort = $sqlOrder;
		$this->_query_dir = $dir;
		$this->_fixedQuerySort = true;
	}
	
	public function _getQueryStr($returnCount=false){
		if (!$this->_query_fields && !$returnCount) {
			$this->_setError("No se ha definido campos para la consulta");
			return false;
		}
		if (!$this->_query_from) {
			$this->_setError("No se ha definido FROM para la consulta");
			return false;
		}
		
		$fields = $this->_query_fields;
		if ($returnCount) {
			$fields = "COUNT(*)";
		}
		
		$query = "SELECT $fields FROM " . $this->_query_from;
		if ($this->_query_where) {
			$query .= " WHERE $this->_query_where";
		}
		
		
		if ($this->_query_sort && !$returnCount) {
//			echo "this->_query_sort: $this->_query_sort";
			$fieldAlias = $this->_getFieldAlias($this->_query_sort);
			if ($fieldAlias) {
				$query .= " ORDER BY $fieldAlias";
				if ($this->_query_dir) {
					$query .= ' '. $this->_query_dir;
				}
			}
		}
		
		return $query;
	}

	private function _exeQueryCount(){
		$this->_validateInitialization();
		
		$query = $this->_getQueryStr(true);
		
		if (!$this->_isConnected) {
			$this->connect();
		}
		
		if ($this->haveError()) {
			return false;
		}
		
		$this->printDebug("Ejecutando QueryCount: $query");
		$this->_readTimeDelayedStart();
		
		$this->_setLastQuery($query, true);
		
		$total = $this->_db->GetOne($query);
		
		$this->_readTimeDelayedEnd($query);
		
		if ($this->haveError()) {
			return false;
		}
		
		$this->printDebug("Ejecutado queryCount: total: ". $total);
		
		return $total;
	}
	
	private function _setLastQuery($query, $isCount=false){
		$this->_lastQuery = new stdClass();
		
		$this->_lastQuery->query = $query;
		$this->_lastQuery->isCount = $isCount;
		$this->_lastQuery->limit = $this->_query_pag_limit;
		$this->_lastQuery->offset = $this->_query_pag_offset;
	}
	
	/**
	 * Retorna la última consulta ejecutada
	 *
	 * @return object Devuelve null si no se ha ejecutado consulta
	 */
	public function getLastQuery(){
		return $this->_lastQuery;
	}
	
	public function getLastQueryToString(){
		if (!$this->_lastQuery) {
			return '';
		}
		
		$lastQueryToString  = 'Consulta: ' . $this->_lastQuery->query . '<br/>';
		$lastQueryToString .= ' Count: ' . ($this->_lastQuery->isCount ? 'SI':'NO');
		$lastQueryToString .= ' Limit: ' . $this->_lastQuery->limit;
		$lastQueryToString .= ' Offset: ' . $this->_lastQuery->offset;
		
		return $lastQueryToString;
	}

	private function _clearErrors(){
		$this->_errorRef = '';
		$this->_errorMsg = '';
	}
	
	static function GetErrorsDBRetry(){
		$errorsDBRetry = array();
		
/*
ERROR 1103x1. Ocurrió un error inténtelo más tarde. [Microsoft][ODBC Visual FoxPro Driver]This file is incompatible with the current version of Visual FoxPro.  Run 30UPDATE.PRG to update the file to the current version.
*/		

		$errorsDBRetry[] = "This file is incompatible with the current version of Visual FoxPro";
		$errorsDBRetry[] = "Cannot open file";
		// para pruebas xxx
		// $errorsDBRetry[] = "sociosXXX";
		
		return $errorsDBRetry;
	}
	
	private function _isErrorRetry(){
		$msgErrorDB = $this->getErrorMsgDB();		
		if (!$msgErrorDB) {
			return false;
		}
		
		$isErrorRetry = false;
		$errors = self::GetErrorsDBRetry();
		foreach ($errors as $errorRetry) {
			$pos = stripos($msgErrorDB, $errorRetry);
			if ($pos !== false) {
				$isErrorRetry = true;
				break;
			}
		}
		
		return $isErrorRetry;
	}
	
	private function _validateRetry(){
		if (!$this->_isErrorRetry()) {
			$this->_numRetryQuery = 0;
			return false;
		}
		
		global $exj;
		
		$segDemoraTotal = $exj->getSecondsDemora();

		// para pruebas xxx
		/*
		if ($this->_numRetryQuery == 3) {
			$this->setQueryFROM(str_replace("sociosXXX", "socios", $this->_query_from));
		}
		*/
		/////
		
		$SegRetryMax = self::SEG_RETRY_MAX;
		
		if ($segDemoraTotal >= $SegRetryMax) {
			$this->writeLog("Se cancelan Reintentos. Demora total: $segDemoraTotal seg. Reintentos: ". $this->_numRetryQuery);
			$this->_numRetryQuery = 0;
			return false;
		}
		
		$demoraLastQuery = $this->getTimeDelayedQuerySeg();
		if ($demoraLastQuery > 9) {
			$this->writeLog('Consulta demoró mucho. Reintentos: '. $this->_numRetryQuery, "Demora ultima consulta: $demoraLastQuery");
			$this->_numRetryQuery = 0;
			return false;
		}
		
		$SegRetrySleep = self::SEG_RETRY_SLEEP;
		
		$maximosReintentos = ($SegRetryMax / ($segDemoraTotal+$SegRetrySleep))+6;
		if ($maximosReintentos > 10) {
			$maximosReintentos = 9;
		}
		
		$this->_numRetryQuery += 1;
		if ($this->_numRetryQuery >= $maximosReintentos) {
			$this->_numRetryQuery = 0; // termina los reintentos
			return false;
		}
		
		$this->writeLog("Reintentando Consulta. Reintento: ". $this->_numRetryQuery. " Demora total: $segDemoraTotal seg.", $this->getLastQueryToString());
		
		sleep($SegRetrySleep); // espera al server para que actualize
		
		return true;
	}
	

	public function executeQueryCount(){
		$result = $this->_exeQueryCount();
		
		if (!$this->isErrorFromDB()) {
			return $result;
		}
		
		$retrySuccess = false;
		while ($this->_validateRetry()) {
			$result = $this->_exeQueryCount();
			if ($result !== false) {
				// no hay error
				$retrySuccess = true;
				break;
			}
		}
		
		if ($retrySuccess) {
			$this->writeLog("COUNT. REINTENTO SATISFACTORIO. Reintentos: ". $this->_numRetryQuery . " Resultado: $result", $this->getLastQueryToString());
			
			$this->_numRetryQuery = 0;
			$this->_clearErrors();
		
			if ($this->isErrorFromDB()) {
				$this->writeLog("Se reintentó satisfactoriamente pero la db informa del error.", $this->getErrorMsgDB());
			}
		}
		
		return $result;
	}
	
	
	public function executeQueryObjectList($query='', $limit= -1, $offset=0){
		$result = $this->_exeQueryObjectList($query, $limit, $offset);
		
		if (!$this->isErrorFromDB()) {
			return $result;
		}
		
		$retrySuccess = false;
		while ($this->_validateRetry()) {
			$result = $this->_exeQueryObjectList($query, $limit, $offset);

			if ($result && count($result) > 0) {
				$retrySuccess = true;
				break;
			}
			
			if (!$this->isErrorFromDB()) {
				$retrySuccess = true;
				break;
			}
		}
		
		if ($retrySuccess) {
			$this->writeLog("LIST. REINTENTO SATISFACTORIO. Reintentos: ". $this->_numRetryQuery . " Registros leidos: " . count($result), $this->getLastQueryToString());
			$this->_numRetryQuery = 0;
			$this->_clearErrors();
			
			if ($this->isErrorFromDB()){
				$this->writeLog("Se reintentó satisfactoriamente pero la db informa del error.", $this->getErrorMsgDB());
			}
		}
		
		return $result;
	}
	
	/**
	 * Indica si es está reintentando un consulta
	 *
	 * @return bool
	 */
	public function isRetryingQuery(){
		return ($this->_numRetryQuery > 0);
	}
	
	public function clearPagination(){
		$this->_query_pag_limit = -1;
		$this->_query_pag_offset = -1;
		$this->_withPagination = false;
	}
	
	public function clearOrderQuery(){
		$this->setQueryOrderSQL('');
	}
	
	public function executeQueryFields(&$table='', $fields='*', $returnOnlyNames=true, &$recordFirst=null){
		$this->_validateInitialization();
		
		if (!$table){
			$table = $this->_query_from;
		}
		if (!$table) {
			$this->_setError("No se ha definido la tabla para extraer los campos");
			return false;
		}
		
		$query = "SELECT $fields FROM $table";
		
		$cursor = $this->_db->SelectLimit($query, 1);
		if ($this->haveError()) {
			$this->_setErrorReference("Consulta: $query");
			return false;
		}
		
		$fieldTypes =  $cursor->FieldTypesArray();
		
		while (!$cursor->EOF) {
			$recordFirst = $cursor->fields;
			break;
		}
		
		if (!$returnOnlyNames) {
			$cursor->Close();
			return $fieldTypes;
		}
		
		$namesFields = array();
		foreach ($fieldTypes as $fieldType) {
			$namesFields[] = $fieldType->name;
		}
		
		$cursor->Close();
		
		return $namesFields;
	}
	
	/**
	 * Presenta en pantalla las tablas que se pasen por el parámetro
	 *
	 * @param array $tables
	 */
	public function printsFields($tables){
		$numTables = count($tables);
		if ($numTables == 1) {
			$this->printFields($tables[0]);
			return ;
		}
		
		$cols = intval($numTables/2);
		
		if ($cols > 5) {
			$cols = 5;
		}
		
		echo '<table>';
		$indexTable = -1;
		
		while ($indexTable < $numTables) {
			echo '<tr valign="top">';
			for ($i=0; $i < $cols; $i++){
				$table = '';
				if ($indexTable < $numTables) {
					$table = $tables[++$indexTable];
				}
				
				echo '<td>';
				if ($table) {
					$this->printFields($table);
				}
				echo '</td>';
			}
			echo '</tr>';
		}
		
		echo '</table>';
	}

	/**
	 * Presenta en pantalla los campos de una tabla
	 *
	 * @param string $table
	 * @param string $fields
	 * @param bool $returnOnlyNames
	 */
	public function printFields($table='', $fields='*', $returnOnlyNames=false, &$recordFirst=null){
		$fieldsTypes = $this->executeQueryFields($table, $fields, $returnOnlyNames, $recordFirst);
		if ($this->haveError()) {
			echo $this->getErrorMsg();
			return ;
		}
		
		echo '<br/>'.count($fieldsTypes) . " CAMPOS DE LA TABLA: <b>$table</b><br/>";
		
		$html = '<table border="1">';
		$html .= "<tr>";
		
		$html .= "<td>NRO</td>";
		$html .= "<td>CAMPO</td>";
		if (!$returnOnlyNames) {
			$html .= "<td>LONGITUD</td>";
			$html .= "<td>TIPO</td>";
		}
		
		$html .= "</tr>";
		
		$numField = 0;
		foreach ($fieldsTypes as $fieldsType) {
			$numField += 1;
			
			$html .= "<tr>";
			$html .= "<td>$numField</td>";
			
			if (is_object($fieldsType)) {
				// echo "$fieldsType->name $fieldsType->max_length $fieldsType->type";
				$html .= "<td>$fieldsType->name</td>";
				$html .= "<td>$fieldsType->max_length</td>";
				$html .= "<td>$fieldsType->type</td>";
				
				$html .= "</tr>";
				continue;
			}
			
			$html .= "<td>$fieldsType</td>";
			
			$html .= "</tr>";
		}

		$html .= "</table>";
		
		echo $html;
		// print_r($recordFirst);
		if ($recordFirst) {
			echo "<h3>PRIMER REGISTRO</h3>";
			$indexRecord=0;
			foreach ($recordFirst as $value) {
				echo ++$indexRecord . ' -> '.$value. '<br/>';
			}
		}
	}
	
	public function executeQueryObjectFirst($query=''){
		$items = $this->executeQueryObjectList($query, 1);
		if (!$items) {
			return $items;
		}
		
		if (count($items) == 0) {
			return false;
		}
		
		return $items[0];
	}
	
	public function executeQueryResult($query='', $resultDefault=''){
		$item = $this->executeQueryObjectFirst($query);
		if ($this->haveError()) {
			return false;
		}
		
		$resultValue = $resultDefault;
		
		if (is_object($item)) {
			$objVars = get_object_vars($item);
			foreach ($objVars as $name => $value) {
				$resultValue = $value;
				break;
			}
		}
		
		return $resultValue;
	}
	

	/**
	 * Ejecuta la consulta y carga los resultados en el objeto de respuesta
	 *
	 * @param ExjResponse $response
	 * @return bool false si ha ocurrido algún error sino true
	 */
	public function executeQueryLoadResponseDataTopics(ExjResponse &$response, $resetQuery=true){
		$this->printDebug("Iniciando. " . __METHOD__);
		
		if ($this->haveError()) {
			$response->setMsgError($this->getErrorMsg());
			return false;
		}
		
		$total = $this->executeQueryCount();
		
		if ($this->haveError()) {
			$response->setMsgError($this->getErrorMsg());
			return false;
		}
		
		$items = $this->executeQueryObjectList();
		if ($this->haveError()) {
			$response->setMsgError($this->getErrorMsg());
			return false;
		}
		
		if ($resetQuery) {
			$this->resetQuery();
		}
		
		$response->setDataTopics($items, $total);
		
		return true;
	}
	
	private function _withLimit(){
		return ($this->_query_pag_limit > 0);
	}
	
	private function _readTimeDelayedStart(){
		$this->_timeDelayed = microtime(true);
		return $this->_timeDelayed;
	}
	
	private function _readTimeDelayedEnd($query=''){
		if ($this->_timeDelayed == -1) {
			return 0;
		}
		
		$this->_timeDelayed = microtime(true) - $this->_timeDelayed;
		$this->_timeDelayed = round($this->_timeDelayed, 3);
		
		
		if ($this->_timeDelayed >= self::TIME_LIMIT_DELAYED) {
			if ($query) {
				if ($this->_withLimit()) {
					$this->writeLog("Consulta demoró $this->_timeDelayed segundos. Límite: " . $this->_query_pag_limit, $query);
				}
				else {
					$this->writeLog("Consulta demoró $this->_timeDelayed segundos. SIN LIMITE", $query);
				}
			}
			else {
				$this->writeLog("Proceso demoró $this->_timeDelayed segundos.", debug_backtrace());
			}
		}
		
		
		return $this->_timeDelayed;
	}
	
	
	private function _exeQueryObjectList($query='', $limit=-1, $offset=0){
		$this->printDebug('Iniciando: '. __METHOD__);
		$this->_validateInitialization();
		
		if ($limit > 0) {
			$this->setQueryLIMIT($limit, $offset);
		}
		$this->_readParamsRequest();
		
		
		if (!$query) {
			$query = $this->_getQueryStr();
		}
		
		if (!$this->_isConnected) {
			$this->connect();
		}
		
		$this->printDebug("Ejecutar consulta: $query");
		
		if ($this->haveError()) {
			$this->printDebug('<b>ERROR</b>: '.$this->getErrorMsg());
			return false;
		}
		
		$this->_setLastQuery($query);
		
		$this->_readTimeDelayedStart();
		
		$cursor = null;
		if ($this->_withLimit()) {
			$cursor = $this->_db->SelectLimit($query, $this->_query_pag_limit, $this->_query_pag_offset);
		}
		else {
			$cursor = $this->_db->Query($query);
		}
		
		$this->_readTimeDelayedEnd($query);
		
		$objList = array();
		if ($this->isErrorFromDB()) {
			$this->_setErrorReference("Consulta: $query");
			
			return $objList;
		}
		
		if (!$cursor) {
			$this->_setError("La consulta es inválida!");
			return $objList;
		}
		
		
		$fieldTypes =  $cursor->FieldTypesArray();
		$namesFields = array();
		foreach ($fieldTypes as $fieldType) {
			$namesFields[] = $fieldType->name;
		}
    	
		while (!$cursor->EOF) {
			$item = new stdClass();
			$indexField = 0;
			foreach ($cursor->fields as $fieldValue) {
				$nameField = $namesFields[$indexField];
				if ($this->_applyTrimToValuesItems && $fieldValue) {
					$fieldValue = trim($fieldValue);
				}
				
				if ($fieldValue && self::IsDate($fieldValue)) {
					if (self::IsDateEmpty($fieldValue)) {
						$fieldValue = null;
					}
				}
				
				$item->$nameField = $fieldValue;
				$indexField += 1;
			}
			
			$objList[] = $item;
			
			$cursor->MoveNext();
		}
		$cursor->Close();
		
		
		$this->printDebug("Consulta Ejecutada OK");
		
		return $objList;
	}

	public function getTimeDelayedQuerySeg(){
		return $this->_timeDelayed;
	}
	
	public function getTimeDelayedQueryStr(){
		if ($this->_timeDelayed < 0) {
			return "Demora: No se ejecutó consulta";
		}
		
		return "Demora: " . $this->_timeDelayed . ' segundos';
	}
	
} // CLASS ExjADODB

/**
 * Adodb para foxpro
 *
 */
class ExjADODB_VFP extends ExjADODB {

	protected function initDB(&$db){
		require(ExjADODB::GetDirADODB().'/drivers/adodb-vfp.inc.php');
		
		$db = new ADODB_vfp();
	}

	protected function initConnectDriver(&$driver){
		$driver = 'Microsoft Visual FoxPro Driver';
	}
	
} // class ExjADODB_VFP


?>