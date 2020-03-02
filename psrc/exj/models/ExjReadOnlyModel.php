<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjReadOnlyModel
 * Baseclass para Modelo de objetos solo lectura para este ORM
 */
class ExjReadOnlyModel extends ExjModels {
    public $id;
    
    public $modificado_dt, $id_usuario_modifico; // todas las tablas deben tener
    
	private $_table, $_fieldKey;
	private $_typeFieldKey = null;
	
	private $_response;
	
	public function __construct($addControlesUI = true, ExjResponse $response=null){
        // $this->id = isset($params['id']) ? $params['id'] : null;
        // $this->attributes = $params;
        $this->_table = '';
        $this->_fieldKey = '';
		$this->_brokenRules = array();
		
		if (!$response) {
			$response = new ExjResponse();
		}
		$this->setResponse($response);
		$this->setTypeFieldKey(ExjTypesVar::Int());
		
		parent::__construct($addControlesUI);
		
		$this->initReadOnlyModel();
		
		if (!$this->_table || !$this->_fieldKey) {
			$nameTable = '';
			$fieldKey = $this->_fieldKey;
			$this->readTable($nameTable, $fieldKey);
			if (!$nameTable) {
				throw new Exception("No se ha definido la tabla para el Modelo ReadOnly."); 
			}
			$this->registerTable($nameTable, $fieldKey);
		}
		
		$this->_clearValidations();
		
		// $this->writeClassLn($this, "Iniciado", false);
		$this->afterInitReadOnlyModel();
		
	}
	
	private function _clearValidations(){
    	$fields = $this->getFields();
    	foreach ($fields as $field) {
    		$this->applyValidationClear($field->name);
    	}    	
	}
	
	public function getResponse(){
		return $this->_response;
	}

	public function validateResponse(){
		if ($this->haveBrokenRules()) {
			$this->_response->setMsgError($this->getBrokenRules());
		}
		
		return $this->_response;
	}
	
    /**
     * Antes de adicionar el control UI
     *
     * @param object $controlUI
     * @return bool false no adiciona el control UI
     */
    protected function beforeAddControlUI(&$controlUI, $nameField){
    	$controlUI->readOnly = true;
    	
    	return true;
    }
	
	
	/**
	 * overwrited. Despues que se inicia el modelo solo lectura
	 *
	 */
	protected function afterInitReadOnlyModel(){
		
	}
	
	
	
	/**
	 * overwrited. Inicio del modelo solo lectura
	 *
	 */
	protected function initReadOnlyModel(){
		
	}

	/**
	 * Registra campo de tipo string
	 *
	 * @param string $name
	 * @param string $alias
	 * @param bool $isNullable
	 * @param bool $isRequired
	 */
	public function registerFieldString($name, $alias='', $isNullable=true, $isRequired=false){
		return parent::registerFieldString($name, $alias, $isNullable, $isRequired);
	}
	
	public function registerFieldDate($name, $alias='', $isNullable=true, $isRequired=false){
		return parent::registerFieldDate($name, $alias, $isNullable, $isRequired);
	}
	
	
	/**
	 * overwrited. Se ejecuta por cada iteracción de cada lista hija
	 *
	 * @param ExjListModel $objListModel Pasado por referencia
	 * @param ExjReadOnlyModel $objReadOnlyModel Pasado por referencia
	 * @param Object $childListModel Solo Lectura
	 * @return bool retornar false para parar no adicionarlo como child
	 */
	protected function eachChildListModel(&$objListModel, &$objReadOnlyModel, $childListModel){
		return true;
	}
	
	public function registerTable($nameTable, $fieldKey){
		$this->_brokenRules = array();
		$fieldKey = trim($fieldKey);
		
		$this->_table = $nameTable;
		$this->_fieldKey = $fieldKey;
		$this->id = $this->$fieldKey = null; // se crea dinamicamente
		$this->registerFieldId($fieldKey);
		
		$this->registerFieldId('id_usuario_modifico', 'Modificado por');
		$this->registerFieldDateTime('modificado_dt', 'Date of Change');
	}
	
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	protected function readTable(&$nameTable, &$fieldKey){
		
	}
	
	public function setTypeFieldKey(ExjTypesVar $type){
		$this->_typeFieldKey = $type;
	}
	
	private function _isIntVar(ExjTypesVar $type){
		return $type->isInt();
	}
	
	public function isIntFieldKey(){
		return $this->_isIntVar($this->_typeFieldKey);
	}
	
	/**
	 * Devuelve el nombre del campo clave de la tabla solo lectura
	 *
	 * @return string
	 */
	public function getNameFieldKey(){
		return $this->_fieldKey;
	}
	
	
	public function getNameTable(){
		return $this->_table;
	}
	
	public function getValueFieldSetted($nameField, $isRequired=true){
		if (!$this->isSettedField($nameField)) {
			$this->addBrokenRuler("No se ha seteado el campo: $nameField");
			return false;
		}
		
		if (!isset($this->$nameField)) {
			$this->addBrokenRuler("No se ha existe el campo: $nameField");
			return false;
		}
		
		$value = $this->$nameField;
		
		if ($isRequired && !$value) {
			$this->addBrokenRuler("El campo: $nameField es requerido");
			return $value;
		}
		
		return $value;
	}
	
	
    /**
     * Valida si se puede eliminar un registro si esta relacionado con otra tabla
     *
     * @param mixed $id
     * @param string $nameTable
     * @param string $nameEntity
     * @param string $nameFieldKey Si no se especifica se toma el campo clave de la tabla
     * @param string $msgInvalid
     * @return bool true si es válido, sino false y se adiciona una regla rota
     */
    public function canDestroyRelationTable($id, $nameTable, $nameEntity, $nameFieldKey='', $msgInvalid='No se puede eliminar.'){
		$nroRel = $this->getNumRecordsRelationTable($id, $nameTable, $nameFieldKey);
		if ($this->haveBrokenRules()) {
			return false;
		}
		
		if ($nroRel) {
			$msg = $msgInvalid;
			if ($msg) {
				$msg .= '<br/>';
			}
			$msg .= "Existen <b>$nroRel</b> $nameEntity relacionados.";
			$this->addBrokenRuler($msg);
			return false;
		}
		
    	return true;
    }
    
    public function getNumRecordsRelationTable($id, $nameTable, $nameFieldKey=''){
    	if (!$nameFieldKey) {
    		$nameFieldKey = $this->getNameFieldKey();
    	}
    	
		$sql = "SELECT COUNT(*) FROM $nameTable";
		$sql .= " WHERE $nameFieldKey=$id";
		
		$db = Exj::InstanceDatabase();
		$nroRel = $db->loadResult($sql);
		if ($db->getErrorMsg()) {
			$this->addBrokenRuler($db->getErrorMsg());
			return false;
		}
    	
		return $nroRel;
    }
	
	public function setValueId($id){
		$fieldKey = $this->_fieldKey;
		$this->$fieldKey = $id;
		$this->id = $id;
	}
	
	public function setParam($param, $value){
		// se esta sobre-escrbiendo la función
		
		$param = trim($param);
		if (!$param) {
			return false;
		}
		
		if ($param == $this->_fieldKey) {
			if ($this->isIntFieldKey()) {
				$value = intval($value);
			}
			
			$this->setValueId($value);
		}
		
		return parent::setParam($param, $value);
	}

	/**
	 * Envia el objeto response del controlador
	 *
	 * @param ExjResponse $response
	 */
	public function setResponse(ExjResponse $response){
		$this->_response = $response;
		
		if (!isset($this->_response->data)) {
			$this->_response->data = new stdClass();
		}
	}
	
	
	public function getParamId($nameParam, $allowZero=false){
		$paramId = $this->getParamInt($nameParam);
		if (!$paramId) {
			if ($allowZero && $paramId === 0) {
				return $paramId;
			}
			$this->addBrokenRuler("No se ha indicado: " . $this->getFieldAlias($nameParam));
		}
		
		return $paramId;
	}
	
	public function isNew(){
		return ($this->id ? false:true);
	}
    
   	public function loadToObject(&$obj, $id=null){
		if (!$id) {
			if ($this->isNew()) {
				return false;
			}
			
			$id = $this->id;
		}
		
    	$db = Exj::InstanceDatabase();
    	
    	$sql = "SELECT * ";
    	$sql .= " FROM $this->_table";
    	
    	$criteria = array();
   		$criteria[] = $this->_fieldKey . " = ". $id;
   		
   		$sql .= " WHERE " . implode(" AND ", $criteria);
    	$sql .= " LIMIT 1";
    	
    	$db->setQuery($sql);
    	$obj = null;
    	$db->loadObject($obj);
		if ($db->haveError()) {
			$this->addBrokenRuler($db->getErrorMsg());
			return false;
		}
		if (!$obj) {
			return false;
		}

		return true;
	}

	static function getCodeFromText($text, $maxLong=4){
		if (!$text) {
			return '';
		}
		
		$text = trim($text);
		if (!$text) {
			return '';
		}
		
		$text = str_replace('.', '', $text);
		$text = str_replace(',', '', $text);
		$text = str_replace('_', '', $text);
		$text = str_replace("  ", ' ', $text);
		$text = strtoupper($text);
		
		if (strlen($text) <= $maxLong) {
			$text = str_replace(" ", '_', $text);
			return $text;
		}
		
		$words = explode(" ", $text);
		$code = '';
		if (count($words) > 1) {
			$articulos = array('DE', 'LOS', 'LAS', 'CON', 'EL', 'LA', 'POR', 'PARA', 'EN', 'DEL');
			foreach ($words as $w) {
				if (strlen($code) >= $maxLong) {
					break;
				}
				if (strlen($w) <= 1){
					continue;
				}
				
				if (in_array($w, $articulos)){
					continue;
				}
				if (strlen($w) <= 3){
					$code .= $w;
				}
				else {
					$code .= substr($w, 0, 2);
				}
			}
		}
		
		if (!$code) {
			$code = str_replace(" ", '', $text);
		}
		
		if (strlen($code) > $maxLong) {
			$code = substr($code, 0, $maxLong);
		}
		
		return $code;
	}
    
    public function getFieldFromName($name){
    	$fieldFound = null;
    	$fields = $this->getFields();
    	foreach ($fields as $field) {
    		if ($field->name == $name) {
    			$fieldFound = $field;
    			break;
    		}
    	}
    	
    	return $fieldFound;
    }
    
    public function getAliasFromField($field){
    	if (!$field) {
    		return '';
    	}
    	
		$alias = $field->alias;
		if (!$alias) {
			$alias = $field->name;
		}
    	
		return $alias;
    }
    
    /**
     * Bindea solo la data cambiada
     *
     * @param object $data
     * @return int Número de registros bindeados
     */
    public function bindOnlyDataChanged($data='', $onlyDataSetted=true) {
    	$nFieldsBinded = 0;
    	// echo " <br/> DEBUG A: " . __METHOD__ . '<br/>';
    	
    	if ($data && is_array($data)) {
    		$data = self::ConvertArrayToObject($data);
    	}
    	
    	if ($data && !is_object($data)) {
    		$this->addBrokenRuler("No se pudo bindear, no se especificó un objeto");
    		return $nFieldsBinded;
    	}
    	
    	$fields = $this->getFields();
    	if (count($fields) == 0) {
    		$this->addBrokenRuler("No se pudo bindear, no se a registrados campos para persistencia");
    		return $nFieldsBinded;
    	}
    	
    	if (!$data) {
    		$data = $this->toObject();
    	}
    	
    	$vars = get_object_vars($data);
    	foreach ($fields as $f) {
    		$nameField = $f->name;
    		if ($nameField == $this->_fieldKey){
    			if ($this->id) {
    				continue;
    			}
    		}
    		$valueRaw = null;
    		$found=false;
	    	foreach ($vars as $nameFieldVar => $valueField) {
	    		if ($nameField == $nameFieldVar) {
	    			$valueRaw = $valueField;
	    			$found = true;
	    			break;
	    		}
	    	}
	    	if (!$found) {
	    		// echo "<br/> NO FOUND: $f->name | ";
	    		
	    		if ($this->_isFieldGeneric($nameField)) {
	    			$this->resetField($nameField);
	    		}
	    		
	    		continue;
	    	}
	    	
	    	if ($onlyDataSetted) {
	    		if (!$this->isSettedField($nameField)) {
	    			if ($valueRaw !== null) {
	    				continue;
	    			}
	    		}
	    		if ($this->_isFieldGeneric($nameField)) {
	    			$this->resetField($nameField);
	    			continue;
	    		}
	    	}
	    	
	    	// echo "<br/> FOUND: $nameField valor: $valueRaw ";
	    	
	    	$valueNew = null;
	    	if (!$this->normalizeValue($f, $valueRaw, $valueNew)) {
	    		$this->writeClassLn($this, "NO SE PUDO NORMALIZAR: $nameField valor: $valueRaw");
	    		continue;
	    	}
	    	
	    	$valueReadOnly = $this->$nameField;
	    	if ($valueReadOnly == $valueNew) {
	    		// echo " <br/> RESET AL CAMPO: $nameField valor: $valueRaw ";
	    		$this->resetField($nameField);
	    		continue;
	    	}
	    	
	    	// echo " <br/> FIJANDO CAMPO: $nameField valueReadOnly: $valueReadOnly valueNew: $valueNew NUMCARS:  " . strlen($valueReadOnly) . ' y '. strlen($valueNew);
	    	
	    	$this->$nameField = $valueNew;
	    	++$nFieldsBinded;
    	}
    	
    	return $nFieldsBinded;
    } // bindOnlyDataChanged
    
    private function _isFieldGeneric($nameField){
		return ($nameField == 'id_usuario_modifico' || $nameField == 'modificado_dt');
    }

    /**
     * Owerwrite. Informa si es válida la entidad que va a ser guardada
     *
     * @return bool true si es válido, sino false
     */
    public function isValid() {
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
    	global $exj;
    	if (Exj::GetError()->haveError()) {
    		$this->addBrokenRuler($exj->getErrorMsg());
    		return false;
    	}

    	return (count($this->_brokenRules) == 0);
    }
    
    public function loadDBFromCriteria($criteria, &$nroRegLoaded, $decodeChars=true) {
    	$nroRegLoaded = 0;
    	
    	$criteriaSQL = array();
    	
    	$this->setValueId(0);
    	
    	global $exj;
    	
    	$fieldsCriteria = Exj::GetFieldsVarsFromObject($criteria);
    	if (Exj::GetError()->haveError()) {
    		return false;
    	}
    	
    	foreach ($fieldsCriteria as $fieldCriteria) {
    		if (!isset($this->$fieldCriteria)) {
    			continue;
    		}
    		
    		$valueCriteria = $criteria->$fieldCriteria;
    		
    		$criteriaSQL[] = "$fieldCriteria = '$valueCriteria'";
    	}
    	
    	if (count($criteriaSQL) == 0){
    		$this->addBrokenRuler("La criteria enviada para carga de registro no concuerda con campos del modelo: "  . __CLASS__);
    		return false;
    	}
    	
    	$criteriaSQL = implode(" AND ", $criteriaSQL);

    	$query = "SELECT * ";
    	$query .= " FROM $this->_table";
    	$query .= " WHERE $criteriaSQL";
    	
    	$db = Exj::InstanceDatabase();
    	$items = $db->loadObjectList($query);
    	if (Exj::GetError()->haveError()) {
    		return false;
    	}
    	
    	if ($items) {
    		$nroRegLoaded = count($items);
    		
    		$item = $items[0];
    		if ($decodeChars) {
    			ExjTransferCharacters::decodeUTF8ToISO($item);
    		}
    		$this->_setDataToReadOnly($item);
    		$fieldKey = $this->_fieldKey;
    		if ($this->$fieldKey) {
    			$this->id = $this->$fieldKey;
    		}
    	}
    	
    	return true;
    }
    
    public function reset(){
    	$fields = $this->getFields();
    	foreach ($fields as $f) {
    		$this->resetField($f->name);
    	}
    	$this->setValueId(0);
    }

    
    /**
     * Carga en la propiedades de esta clase desde la db
     *
     * @param int $id
     * @return bool true si encontró el Id, false sino
     */
    public function load($id=null, $noFoundAddError = true) {
    	$db = Exj::InstanceDatabase();
    	if ($id === null) {
    		$id = $this->id;
    	}
    	if (!$id) {
    		$this->addBrokenRuler("No se ha definido id para load() en<br/>Modelo solo lectura: " . get_class($this));
    		debug_print_backtrace();
    		return false;
    	}
    	if (!self::IsSettedValue($id)) {
    		$this->addBrokenRuler("No se ha seteado id para load() en modelo solo lectura.");
    		return false;
    	}
    	
    	$query = "SELECT * ";
    	$query .= " FROM $this->_table";
    	$query .= " WHERE $this->_fieldKey = $id";
    	
    	$obj = null;
    	$db->setQuery($query);
    	$db->loadObject($obj);
    	if ($db->getErrorMsg()) {
    		$this->addBrokenRuler($db->getErrorMsg());
    		return false;
    	}
    	if (!$obj) {
    		if ($noFoundAddError) {
    			$this->addBrokenRuler("No se encontró con ID: $id");
    		}
    		
    		return false;
    	}
    	
    	$this->_setDataToReadOnly($obj);
    	$this->id = $id;
    	
    	return true;
    }
    
    private function _setDataToReadOnly($data, $noFoundSetValue=true, $valueNoFound = null){
    	if (!$data) {
    		return;
    	}
    	
    	// echo " <br/>Fijando a solo lectura: ";
    	// print_r($data);
    	
    	$fields = $this->getFields();
    	foreach ($fields as $f) {
    		$name = $f->name;
    		
    		// echo " <br/>name: $name ";
    		if (isset($data->$name)) {
    			$this->$name = $data->$name;
    			// echo " ENCONTRADO this->name: ". $this->$name;
    		}
    		else {
    			if ($f->isNullable) {
    				$this->$name = null;
    			}
    			else {
	    			if ($noFoundSetValue) {
	    				$this->$name = $valueNoFound;
	    			}
    			}
    		}
    	}
    }
    
    
    /**
     * overwrited. Bindeo datos a la clase del modelo
     *
     * @param object $data
     * @return int numero de campos de la clase bindeados
     */
    public function bind($data='') {
    	$nBinded = parent::bind($data);
    	if (!$nBinded) {
    		return $nBinded;
    	}
    	
    	$nameFieldKey = $this->_fieldKey;
    	
    	if ($this->isSettedField($nameFieldKey)) {
    		$this->id = $this->$nameFieldKey;
    	//	echo "<br/>Se ha setado ID key: $nameFieldKey, valor: ". $this->id;
    	}
    	
    	return $nBinded;
    }
    
    
    public function to_ui() {
    	$ui = parent::to_ui();
    	
    	$ui->fieldKey = $this->_fieldKey;
		$ui->data = $this->getDataSetted();
    	
        return $ui;
    }
    
    /**
     * Retorna un objecto con todos los campos que han sido seteados
     *
     * @return object
     */
    public function getDataSetted(){
		$obj = $this->toObject();
		$dataSetted = new stdClass();
		$varsObj = get_object_vars($obj);
		foreach ($varsObj as $name => $valueRaw) {
			if (!self::IsSettedValue($valueRaw)) {
				continue;
			}
			
			$value = $valueRaw;
			
			$field = $this->getFieldFromName($name);
			if ($field) {
				$this->normalizeValue($field, $valueRaw, $value);
			}
			
			if ($name == $this->_fieldKey) {
				$dataSetted->id = $value;
			}
			
			// echo " <br/> SETEANDO: $name VALOR: $value ";
			
			$dataSetted->$name = $value;
		} // foreach
    	
		return $dataSetted;
    }
    
}

?>