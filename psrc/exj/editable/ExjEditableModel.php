<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base para el modelo Editable. Los modelos editables deben heredar de esta clase.
 * [drivers] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[drivers]/models/[driver].editable.model.php
 * Nombrado de la clase: Debe tener el formato: class App[driver]EditableModel extends ExjEditableModel
 * La clase editable debe sobrescribir los métodos: readTable, registerFields, registerControlsUI
 */
class ExjEditableModel extends ExjModels {
    protected $useValidDatesFromUntil = false;

    public $id;
    // public $attributes;

    public $modificado_dt, $id_usuario_modifico; // todas las tablas deben tener
    private $_table, $_fieldKey;
    private $_response;
    private $_isSaved = false;
    private $_startedAutoTransaction = false;
    private $_useTransactionOnSave = false;
    private $_useTransactionOnDestroy = false;
    private $_disableAllTransactionsDB = false;
    private $_isDataChildsSaved = false;
    private $_listChildModelsUI = null;
    private $_listParentModelsUI = null;
    private $_editableChildModelsUI = null;
    private $_isDirty = false;
    private $_forceUsePostFixNamesInBind = false;
    private $_itemsLoadedFromCriteria = null;
    private $_autoRegisterFechaCreacion = false;
    private $_wasNew = null;

    /**
     * Constrains
     *
     * @var ExjEditableConstraintsForeignKeys
     */
    private $_constraints = null;

    public function __construct($addControlesUI = true, ExjResponse $response = null) {
        // $this->id = isset($params['id']) ? $params['id'] : null;
        // $this->attributes = $params;
        $this->_table = '';
        $this->_fieldKey = '';
        $this->_brokenRules = array();
        $this->_isDirty = true;

        if (!$response) {
            $response = new ExjResponse();
        }
        $this->setResponse($response);

        if (!is_bool($addControlesUI)) {            
            self::PrintBackTrace();
            throw new Exception("addControlesUI DEBE SER BOOL. Clase: ".get_class($this), 1);
        }

        parent::__construct($addControlesUI);

        $this->initEditableModel();

        if (!$this->_table || !$this->_fieldKey) {
            $nameTable = '';
            $fieldKey = $this->_fieldKey;
            $this->readTable($nameTable, $fieldKey);
            if (!$nameTable) {
                $msgError = "No se ha definido la tabla para el Modelo Editable." . $this->getClassStr();
                if ($this->getClassStr('') == 'ExjEditableModel') {
                    $this->addBrokenRuler($msgError);
                    return $this;
                }

                throw new Exception($msgError);
            }
            $this->registerTable($nameTable, $fieldKey);
        }

        $this->_registerValidDatesFromUntil($addControlesUI);

        // $this->writeClassLn($this, "Iniciado", false);
        $this->afterInitEditableModel();
    }

    private function _registerValidDatesFromUntil($addControlesUI){
        if (!$this->useValidDatesFromUntil) {
            return;
        }

        $this->valid_from_date = null;
        $this->valid_until_date = null;

        $this->registerFieldDate('valid_from_date', 'Vigente desde');
        $this->registerFieldDateNullable('valid_until_date', 'Vigente hasta');

        if ($addControlesUI) {
            $this->registerControlUI(ExjUI::NewDateField('valid_from_date'));
            $this->registerControlUI(ExjUI::NewDateField('valid_until_date'));
        }
    }

    public function getValueDateValidFrom(){
        if (!$this->isSettedField('valid_from_date')) {
            return null;
        }
        return $this->valid_from_date;
    }

    public function getValueDateValidUntil(){
        if (!$this->isSettedField('valid_until_date')) {
            return null;
        }
        return $this->valid_until_date;
    }

    public function isValidDatesFromUntil($dateCurrent=''){
        if (!$this->useValidDatesFromUntil) {
            return true;
        }

        $dateFrom = $this->getValueDateValidFrom();
        $dateUntil = $this->getValueDateValidUntil();

        if (!$dateFrom && !$dateUntil) {
            return true;
        }

        if (!$dateCurrent) {
            $dateCurrent = Exj::GetDate();
        }

        if ($dateFrom && $dateFrom > $dateCurrent) {
            return false;
        }

        if ($dateUntil && $dateUntil < $dateCurrent) {
            return false;
        }

        return true;
    }

    /**
     * Obtiene alias del modelo editable
     *
     * @return string
     */
    public function getAliasEditableModel() {
        return $this->getAliasModel(get_class($this));
    }

    /**
     * Auto registra el campo: datet_creacion para ser persistido en la creación de la entidad
     *
     * @param bool $autoRegister
     */
    public function autoRegisterFechaCreacion($autoRegister = true) {
        $this->_autoRegisterFechaCreacion = $autoRegister;
    }

    /**
     * Obtine objeto de respuesta
     *
     * @return ExjResponse
     */
    public function &getResponse() {
        return $this->_response;
    }

    /**
     * Valida la respuesta del objeto editable
     *
     * @return ExjResponse
     */
    public function validateResponse() {
        $response = $this->getResponse();

        if ($this->haveBrokenRules()) {
            $response->setMsgError($this->getBrokenRules());
        }

        return $response;
    }

    /**
     * Forza a usar el post fijo al momento de bindear los datos, por defecto no se forza a usar los post fijos en nombres de campos
     *
     * @param bool $usePostFixNames Defecto true
     */
    public function forceUsePostFixNamesInBind($usePostFixNames = true) {
        $this->_forceUsePostFixNamesInBind = $usePostFixNames;
    }

    /**
     * overwrited. Despues que se inicia el modelo editable
     *
     */
    protected function afterInitEditableModel() {
        
    }

    /**
     * overwrited. Inicio del modelo editable
     *
     */
    protected function initEditableModel() {
        
    }

    public function registerTable($nameTable, $fieldKey) {
        $this->_brokenRules = array();
        $fieldKey = trim($fieldKey);

        $this->_table = $nameTable;
        $this->_fieldKey = $fieldKey;
        $this->setFieldKeyToModel($fieldKey);
        $this->id = $this->$fieldKey = null; // se crea dinamicamente
        $this->registerFieldId($fieldKey);

        $this->registerFieldId('id_usuario_modifico', 'Modificado por');
        $this->registerFieldDateTime('modificado_dt', 'Cambio');
        if ($this->_autoRegisterFechaCreacion) {
            $this->registerFieldDateTime('datet_creacion', 'Creación');
        }
    }

    /**
     * Registrar contraint tipo foreing key como único, esto ayuda al comportamiento cuando varios usuarios modifican la misma ventana o mismos datos
     *
     * @param string $nameField Campo o lista (array) de campos separados por coma, si fk es clave principal se proboca un error
     * @param bool $isActionUpdate Si es false y se esta editando no se setar el campo fk
     * @param bool $autoSeteerPrimaryKey Si es true cuando existe el campo fk y campo clave es 0, se setea la clave para que se cambie modo editar
     */
    public function registerConstraintForeignKeyUnique($nameField, $isActionUpdate = false, $autoSeteerPrimaryKey = true) {
        if (!$this->_constraints) {
            $this->_constraints = new ExjEditableConstraints();
        }

        $this->_constraints->addConstraintForeignKey($nameField, true, $isActionUpdate, $autoSeteerPrimaryKey);
    }

    /**
     * overwrited. Lectura de la tabla a editar
     *
     * @param string $nameTable Nombre de la tabla
     * @param string $fieldKey  Nombre del campo principal de la tabla
     */
    protected function readTable(&$nameTable, &$fieldKey) {
        
    }

    /**
     * Devuelve el nombre del campo clave de la tabla editable
     *
     * @return string
     */
    public function getNameFieldKey() {
        return $this->_fieldKey;
    }

    public function getFieldKey() {
        return $this->getNameFieldKey();
    }

    public function getNameTable() {
        return $this->_table;
    }

    /**
     * Actualiza registros
     *
     * @param mixed $sets string o array
     * @param mixed $where string o array
     */
    public function update(&$nRowsAffected, $sets, $where = null) {
        if (!$sets) {
            $this->addBrokenRuler("No se pudo actualizar.<br/>No se han indicado campos a actualizar.<br/>Ref: " . get_class($this));
            return false;
        }
        if (is_array($sets)) {
            $sets = implode(", ", $sets);
        }

        if ($where && is_array($where)) {
            $where = implode(" AND ", $where);
        }
        if (!$where) {
            if (!$this->id) {
                $this->addBrokenRuler("No se pudo actualizar.<br/>No se han indicado filtros y no está en modo de edición.<br/>Ref: " . get_class($this));
                return false;
            }
            $where = "$this->_fieldKey = $this->id";
        }

        $sql = "UPDATE " . $this->getNameTable();
        $sql .= " SET $sets";
        $sql .= " WHERE $where";

        $db = Exj::InstanceDatabase();
        $db->query($sql);
        if ($db->getErrorMsg()) {
            $this->addBrokenRuler($db->getErrorMsg());
            return false;
        }

        $nRowsAffected = $db->getAffectedRows();

        return true;
    }

    /**
     * Actualiza todos los registros excepto el registro actual
     *
     * @param int $nRowsAffected
     * @param mixed $sets
     * @param mixed $where
     * @return bool false si no es posible actualizar
     */
    public function updateExceptCurrent(&$nRowsAffected, $sets, $where = null) {
        if (!$this->id) {
            $this->addBrokenRuler("No se pudo actualizar excepto el actual<br/>No está en modo editar.<br/>Ref: " . get_class($this));
            return false;
        }

        $whereExceptCurrent = array();
        $whereExceptCurrent[] = "$this->_fieldKey <> $this->id";
        if ($where) {
            if (is_array($where)) {
                $where = implode(" AND ", $where);
            }
            $whereExceptCurrent[] = $where;
        }

        return $this->update($nRowsAffected, $sets, $whereExceptCurrent);
    }

    /**
     * Obtiene el valor del campo o parametro
     *
     * @param string $nameField
     * @param mixed $defaultValue
     * @return mixed Por lo general string
     */
    public function getValueFieldOrParam($nameField, $defaultValue = null) {
        $value = $this->getValueField($nameField, $defaultValue);
        if ($value === $defaultValue) {
            $value = $this->getParam($nameField, $defaultValue, true);
        }

        return $value;
    }

    public function getValueFieldSetted($nameField, $isRequired = true) {
        if (!$this->isSettedField($nameField)) {
            $this->addBrokenRuler("No se ha seteado el campo: $nameField");
            return false;
        }

        if (!isset($this->$nameField)) {
            $this->addBrokenRuler("No existe el campo: $nameField");
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
    public function canDestroyRelationTable($id, $nameTable, $nameEntity, $nameFieldKey = '', $msgInvalid = 'No se puede eliminar') {
        if ($this->haveBrokenRules()) {
            return false;
        }

        $nroRel = $this->getNumRecordsRelationTable($id, $nameTable, $nameFieldKey);
        if ($this->haveBrokenRules()) {
            return false;
        }

        if ($nroRel) {
            if ($msgInvalid) {
                if ($msgInvalid == 'No se puede eliminar') {
                    $msgInvalid = ExjText::__($msgInvalid);
                } else {
                    $msgInvalid = ExjText::_($msgInvalid);
                }

                $msgInvalid .= '.';
            }

            $msg = $msgInvalid;
            if ($msg) {
                $msg .= '<br/>';
            }
            $msg .= "Existen <b>$nroRel</b> $nameEntity ";
            $msg .= "relacionad";
            $charAO = 'o';
            if (strlen($nameEntity) >= 2) {
                $parteNameEntity = strtolower(substr($nameEntity, -2));
                if (strpos($parteNameEntity, 'a') !== false) {
                    $charAO = 'a';
                }
            }

            $msg .= $charAO;

            if ($nroRel > 1) {
                $msg .= "s";
            }
            $msg .= ".";

            $this->addBrokenRuler($msg);
            return false;
        }

        return true;
    }

    public function getNumRecordsRelationTable($id, $nameTable, $nameFieldKey = '') {
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

    private function _isSettedStrWhere($strWhere){
        $isSetted = false;
        if (!$strWhere) {
            return $isSetted;
        }

        $strWhere = trim(strtolower($strWhere));
        $condiciones = explode(' and ', $strWhere);
        foreach ($condiciones as $condicion) {
            $partes = explode('=', trim($condicion));
            if (count($partes) <= 1) {
                continue;
            }

            $prop = trim($partes[0]);
            if ($prop && $this->isSettedField($prop)) {
                $isSetted = true;
                break;
            }
        }

        return $isSetted;
    }

    private $_dataFoundInvalidateCodeUnique = null;

    /**
     * Comprueba si se puede guardar un campo único en la tabla
     *
     * @param string $nameFieldUnique
     * @param string $prefixMsg
     * @param mixed $whereExtra Puede ser un array o string. Filtros sql serán adicionados al WHERE
     * @return bool, true si se puede guardar, false sino y se agregan reglas rotas
     */
    public function canSaveCodeUnique($nameFieldUnique, $prefixMsg = 'Código', $whereExtra = null, $nameFieldInfo = '', $prefixInfo = '') 
    {
        $this->_dataFoundInvalidateCodeUnique = null;
        $nameFieldUnique = trim($nameFieldUnique);
        if (!$nameFieldUnique) {
            $this->addBrokenRuler(
                "Error verificando $prefixMsg único al guardar.<br/>No definido campo único"
            );
            return false;
        }

        if ($whereExtra) {
            if (is_array($whereExtra)) {
                $whereExtra = trim(implode(" AND ", $whereExtra));
            }
        }

        $criteriaUnique = '';

        if (!$this->isSettedField($nameFieldUnique)) {
            if ($whereExtra && !$this->isNew()) {
                if ($this->_isSettedStrWhere($whereExtra)) {
                    $criteriaUnique = $this->getValueFieldOrDB($nameFieldUnique);
                }
            }

            if (!$criteriaUnique) {
                return true;
            }
        }

        if (!$criteriaUnique) {
            if (!isset($this->$nameFieldUnique)) {
                $this->addBrokenRuler(
                    "Error verificando código único al guardar.<br/>No está definido el campo: $nameFieldUnique"
                );
                return false;
            }

            $this->$nameFieldUnique = trim($this->$nameFieldUnique);
            $criteriaUnique = $this->$nameFieldUnique;
        }

        if (!$criteriaUnique) {
            return true;
        }

        ExjTransferCharacters::encodeISOToUTF8($criteriaUnique);
        //	ExjTransferCharacters::decodeUTF8ToISO($criteriaUnique);

        $nameTable = $this->getNameTable();
        $nameFieldKey = $this->getNameFieldKey();

        $fieldSQLExtra = '';
        if ($nameFieldInfo) {
            $fieldSQLExtra = $nameFieldInfo . ', ';
        }

        $sql = "SELECT 
  usr.name AS name_usr, $fieldSQLExtra $nameTable.modificado_dt, $nameFieldKey, $nameFieldUnique
 FROM 
  $nameTable LEFT JOIN jos_users usr ON $nameTable.id_usuario_modifico = usr.id
 WHERE 
  $nameFieldUnique = '$criteriaUnique' AND $nameFieldKey <> $this->id";
        
        if (!empty($whereExtra)) {
            $sql .= ' AND ' . $whereExtra;
        }
        $sql .= " LIMIT 1";

        $row = ExjDatabase::GetObjectFromQuery($sql);
        if (ExjDatabase::GetLastError()) {
            $this->addBrokenRuler(ExjDatabase::GetLastError());
            return false;
        }

        if ($row === false) {
            $this->addBrokenRuler(
                "Error en consulta, verificando $prefixMsg único."
            );

            return false;
        }

        if (!$row) {
            return true;
        }


        ExjTransferCharacters::decodeUTF8ToISO($row);
        //   	Exj::StripslashesToObject($row);
        // echo 'get_magic_quotes_gpc esta activo: '.get_magic_quotes_gpc();
        // verificar si se han cambiado mayusculas o minusculas en el código, si es asi, hay que actualizar
        ExjTransferCharacters::decodeUTF8ToISO($criteriaUnique); // convertir a iso ya que datos de la db están en iso
        $valueIdUniqueFromDB = $row->$nameFieldKey;
        $valueCodeUniqueFromDB = $row->$nameFieldUnique;
        if ($criteriaUnique !== $valueCodeUniqueFromDB) {
            //	echo "<br/>Codes son diferentes se procede a actualizar con ID: $valueIdUniqueFromDB. Valores: $criteriaUnique = $valueCodeUniqueFromDB";
            $this->setValueId($valueIdUniqueFromDB);
            return true;
        }


        $msg = '';
        if ($prefixMsg) {
            $msg .= ExjText::_($prefixMsg) . ': ';
        }

        $msg .= "<b>$criteriaUnique</b> " . ExjText::__('ya existe') . '.';
        if ($nameFieldInfo) {
            $msg .= '<br/>';
            if ($prefixInfo) {
                $msg .= "$prefixInfo " . ExjText::__('asociado') . ': ';
            }

            $msg .= $row->$nameFieldInfo . '<br/>';
        }

        if ($row->modificado_dt) {
            $row->modificado_dt = ExjDate::ConvertToDateTimeDisplay2($row->modificado_dt);
        }

        $msg .= "<br/>" . ExjText::__("Ha sido modificado por:");
        $msg .= "<br/>" . $row->name_usr . ' - ' . $row->modificado_dt;

        //	print_r($row);
        $dataFoundInvalidateCodeUnique = new stdClass();
        $dataFoundInvalidateCodeUnique->id = $valueIdUniqueFromDB;
        $dataFoundInvalidateCodeUnique->code = $valueCodeUniqueFromDB;
        //	$dataFoundInvalidateCodeUnique->data = $row;

        $this->_dataFoundInvalidateCodeUnique = $dataFoundInvalidateCodeUnique;

        $this->addBrokenRuler($msg);
        return false;
    }

    /**
     * Indica si fué invalidado por no ser código único
     *
     * @return bool
     */
    public function hasInvalidateCodeUnique() {
        return ($this->_dataFoundInvalidateCodeUnique ? true : false);
    }

    public function getIdInvalidateCodeUnique() {
        if ($this->_dataFoundInvalidateCodeUnique) {
            return $this->_dataFoundInvalidateCodeUnique->id;
        }

        return null;
    }

    public function getCodeInvalidateCodeUnique() {
        if ($this->_dataFoundInvalidateCodeUnique) {
            return $this->_dataFoundInvalidateCodeUnique->code;
        }

        return null;
    }

    public function loadIdCodeInvalidateCodeUnique(&$id, &$code) {
        $id = null;
        $code = null;

        if ($this->_dataFoundInvalidateCodeUnique) {
            $id = $this->_dataFoundInvalidateCodeUnique->id;
            $code = $this->_dataFoundInvalidateCodeUnique->code;

            return true;
        }

        return false;
    }

    const EXP_AUTO_INCREMENT = 'auto_increment';

    public function resolverExpressionValue($nameField, $value) {
        if (!$value) {
            return $value;
        }

        $value = trim($value);

        switch (strtolower($value)) {
            case self::EXP_AUTO_INCREMENT:
                $value = "$nameField + 1";
                if ($this->isNew()) {
                    $this->addBrokenRuler("No se puede fijar auto increment al campo: $nameField, porque no está en modo de edición");
                }
                break;
        }

        return $value;
    }

    private $_fieldsExpressions = null;

    public function isFieldExpression($nameField) {
        if (!$this->_fieldsExpressions) {
            return false;
        }

        if (!isset($this->$nameField)) {
            return false;
        }

        return in_array($nameField, $this->_fieldsExpressions);
    }

    public function addFieldExpression($nameField) {
        if (!$this->_fieldsExpressions) {
            $this->_fieldsExpressions = array();
        }

        if (!in_array($nameField, $this->_fieldsExpressions)) {
            $this->_fieldsExpressions[] = $nameField;
        }
    }

    public function setterFieldExpressionAutoIncrement($nameField) {
        if ($this->isNew()) {
            $this->setValueToField($nameField, 1);
        } else {
            $this->setValueToField($nameField, self::EXP_AUTO_INCREMENT, true);
        }
    }

    /**
     * Overwrited. Setea el parametro value al objeto, si el campo no pertenece a los campos registrados en el modelo, no se setea
     *
     * @param string $nameField
     * @param mixed $value
     * @param bool $isExpressionValue
     * @return bool true si se seteo el campo, sino false
     */
    public function setValueToField($nameField, $value, $isExpressionValue = false) {
        $nameField = trim($nameField);

        if ($isExpressionValue && $value) {
            $value = $this->resolverExpressionValue($nameField, $value);
            if ($this->haveBrokenRules()) {
                return false;
            }

            // add a campos de expresiones
            $this->addFieldExpression($nameField);
        }


        if (!parent::setValueToField($nameField, $value)) {
            return false;
        }

        if ($this->_fieldKey == $nameField) {
            if ($value < 0) {
                $value = 0;
            }

            $this->setValueId($value);
        }

        return true;
    }

    public function setId($id, $addToParam = true) {
        return $this->setValueId($id, $addToParam);
    }

    public function setValueId($id, $addToParam = true) {
        $fieldKey = $this->_fieldKey;
        if (!$fieldKey) {
            return false;
        }

        $this->$fieldKey = $id;
        $this->id = $id;

        if ($addToParam && $id) {
            $this->setParam($fieldKey, $id);
        }

        return true;
    }

    public function fixValueIdFromParam($nameParam = '') {
        if (!$nameParam) {
            $nameParam = $this->getFieldKey();
        }

        $id = $this->getParam($nameParam, '');
        if ($id === '') {
            $this->addBrokenRuler("No se ha seteado el parámetro: $nameParam para fijar el ID de la clase: " . get_class($this));
            return false;
        }
        if ($id && $id < 0) {
            $id = 0;
        }

        return $this->setValueId($id, false);
    }

    public function setParam($param, $value) {
        $param = trim($param);
        if (!$param) {
            return false;
        }

        if ($param == $this->_fieldKey) {
            $value = intval($value);
            $this->setValueId($value, false);
        }

        return parent::setParam($param, $value);
    }

    /**
     * Envia el objeto response del controlador
     *
     * @param ExjResponse $response
     */
    public function setResponse(ExjResponse $response) {
        $this->_response = $response;

        if (!isset($this->_response->data)) {
            $this->_response->data = new stdClass();
        }

        // $this->_response->setDataObject();
    }

    private function _validateConstraints() {
        if (!$this->_constraints) {
            return true;
        }

        $itemsConstraints = $this->_constraints->getConstraints();
        foreach ($itemsConstraints as $itemConstraint) {
            $itemConstraint = ExjEditableConstraint::Parse($itemConstraint);
            if (!$itemConstraint) {
                continue;
            }

            if ($itemConstraint->isForeignKey) {
                $nameFieldKey = $this->_fieldKey;

                $namesFKs = explode(',', $itemConstraint->nameField);

                $conditionsFKs = array();
                foreach ($namesFKs as $nameFK) {
                    $nameFK = trim($nameFK);
                    if ($nameFieldKey == $nameFK) {
                        $this->addBrokenRuler("El campo clave (primary key): $nameFieldKey está definido como Foreign Key");
                        continue;
                    }

                    if ($this->isSettedField($nameFK) && $this->$nameFK) {
                        $conditionsFKs[] = "$nameFK='" . $this->$nameFK . "'";
                    }
                }

                if ($this->haveBrokenRules()) {
                    break;
                }

                if (count($conditionsFKs) == count($namesFKs)) {
                    $conditionsFKs = implode(' AND ', $conditionsFKs);
                    if ($itemConstraint->isUnique) {
                        $dataEditable = null;

                        $this->loadToObjectCustom($dataEditable, $conditionsFKs, "$nameFieldKey,modificado_dt");
                        if ($dataEditable) {
                            $valueKey = $dataEditable->$nameFieldKey;


                            //	echo "<br>VALIDANDO FK: $conditionsFKs nameFieldKey: $nameFieldKey valueKey: $valueKey CLASE: " . get_class($this);

                            if ($valueKey) {
                                if ($this->isNew()) {
                                    if ($itemConstraint->autoSeteerPrimaryKey) {
                                        $this->setValueId($valueKey);
                                        echo "<br>FK $conditionsFKs Cambiando a modo edit con id: $valueKey Clase:" . get_class($this);
                                    } else {
                                        $lastChangeEditable = $dataEditable->modificado_dt;
                                        if ($lastChangeEditable) {
                                            $lastChangeEditable = ExjDate::ConvertToDateTimeDisplay($lastChangeEditable);
                                        }
                                        $aliasModel = $this->getAliasModel();

                                        $this->addBrokenRuler("Otro usuario cambió datos en <b>$aliasModel</b>.<br>Ultimo cambio: $lastChangeEditable<br>Por favor actualice la página para obtener los últimos cambios.");
                                    }
                                } elseif (!$itemConstraint->isActionUpdate) {
                                    // echo "<br>Reset a FK $conditionsFKs";
                                    if (count($namesFKs) == 1) {
                                        $nameFK = $namesFKs[0];
                                        $valueFK = $this->$nameFK;
                                        $this->resetField($nameFK);
                                        $this->setParam($nameFK, $valueFK);
                                    }
                                }
                            }
                        }
                    } else {
                        /* TODO: POR IMPLEMENTAR CUANDO NO ES UNICO */
                    }
                }
            }
        }

        return (!$this->getBrokenRules());
    }

    private function _fixIdToResponse() {
        $this->_response->data->id = $this->id;
    }

    public function setPropToResponse($key, $value) {
        $this->_response->data->$key = $value;
    }

    public function getParamId($nameParam, $allowZero = false) {
        $paramId = $this->getParamInt($nameParam);
        if (!$paramId) {
            if ($allowZero && $paramId === 0) {
                return $paramId;
            }

            $this->addBrokenRuler("No se ha indicado: " . $this->getFieldAlias($nameParam) . $this->getClassStr());
            // debug_print_backtrace();
        }

        return $paramId;
    }
    
    /**
     * Busca el campo indicado, en campos seteados o en params o en el objeto bindeado al modelo editable
     *
     * @param string $nameField
     * @param mixed $defaultValue
     * @return mixed Por lo general tipo string|int|float
     */
    public function findField($nameField, $defaultValue = null){
    	$value = $this->getValueFieldOrParam($nameField, $defaultValue);
    	if (empty($value)) {
    		$value = $this->getParamFromDataToBind($nameField, $defaultValue);
    		if (empty($value)) {
    			$value = $defaultValue;
    		}
    	}
    	
    	return $value;
    }

    /**
     * Busca campo Id tipo int
     *
     * @param string $nameField
     * @param int|null $defaultValue
     * @return int|null
     */
    public function findIdField($nameField, $allowZero = false){
    	$value = $this->findField($nameField);
    	if ($value) {
    		if (is_nan(intval($value))) {
    			$this->addBrokenRuler($this->getFieldAlias($nameParam). " tiene el valor: $value, debe ser un valor entero. Ref: ".get_class($this));
    			$value = null;
    		}
    		else{
    			$value = intval($value);
    			if (!$allowZero && $value === 0) {
    				$this->addBrokenRuler("Tiene valor 0: " . $this->getFieldAlias($nameParam) . ' '. $this->getClassStr());
    			}
    		}
    	}
    	
    	return $value;
    }

    /**
     * Indica si la entidad es nueva
     *
     * @return bool Si es false indica que se está editando
     */
    public function isNew() {
        $valueId = $this->id;
        if (!self::IsSettedValue($valueId)) {
            return true;
        }

        if ($valueId && is_numeric($valueId)) {
            if ($valueId <= 0) {
                return true;
            }

            return false;
        }

        return ($valueId ? false : true);
    }

    public function isEdit() {
        return (!$this->isNew());
    }

    

    static function create($params) {
        $obj = new self(get_object_vars($params));
        $obj->save();
        return $obj;
    }

    static function find($id) {
        global $dbh;
        $found = null;
        foreach ($dbh->rs() as $rec) {
            if ($rec['id'] == $id) {
                $found = new self($rec);
                break;
            }
        }
        return $found;
    }

    /**
     * Carga en $obj solo un registro de la tabla según los filtros
     *
     * @param object $obj
     * @param mixed $where string o array de filtros
     * @param string $fields
     * @return bool true si encontró alguna coincidencia
     */
    public function loadToObjectCustom(&$obj, $where, $fields = '*') {
        if (is_array($where)) {
            $where = implode(' AND ', $where);
        }

        $db = Exj::InstanceDatabase();

        $sql = "SELECT $fields ";
        $sql .= " FROM $this->_table";
        $sql .= " WHERE $where";
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

    /**
     * Carga desde parametro criteria hacia un objeto pasado por parámetro
     *
     * @param object $obj Si el objeto es nulo es porque no se encontró
     * @param object $criteria
     * @param string $fields
     * @return bool Si ocurren errores se retorna false, sino true
     */
    public function loadDBFromCriteriaToObject(&$obj, $criteria, $fields = "*") {
        global $exj;
        $obj = null;

        $this->renderCriteriaToLoad($criteria);

        $fieldsCriteria = Exj::GetFieldsVarsFromObject($criteria);
        if (Exj::GetError()->haveError()) {
            return false;
        }

        $fieldsBinary = null;
        if (isset($criteria->fieldsBinary)) {
        	$fieldsBinary = $criteria->fieldsBinary;
        	if (!is_array($fieldsBinary)) {
        		$fieldsBinary = explode(',', $fieldsBinary);
        	}
        }
        
        foreach ($fieldsCriteria as $fieldCriteria) {
            if ($this->getFieldFromName($fieldCriteria) == null) {
                continue;
            }

            $valueCriteria = $criteria->$fieldCriteria;
            if ($fieldsBinary && in_array($fieldCriteria, $fieldsBinary)) {
            	$fieldCriteria = 'binary ' . $fieldCriteria;
            }
            
            if($valueCriteria === null){
                $criteriaSQL[] = "$fieldCriteria IS NULL";
            }
            else{
                $criteriaSQL[] = "$fieldCriteria = '$valueCriteria'";
            }
        }

        if (count($criteriaSQL) == 0) {
            /*
              echo "<br/>CLASE: " . get_class($this).'<br/>';
              print_r($fieldsCriteria);
              print_r($this->getFieldsNames());
             */

            $this->addBrokenRuler("La criteria enviada para carga de registro no concuerda con campos del modelo: " . get_class($this));
            return false;
        }

        $criteriaSQL = implode(" AND ", $criteriaSQL);

        $query = "SELECT $fields ";
        $query .= " FROM $this->_table";
        $query .= " WHERE $criteriaSQL";
        
       // echo '<br>'. __CLASS__." query: $query";

        $items = ExjDatabase::GetObjectList($query);
        if ($items === false) {
            return false;
        }
        
        $this->_itemsLoadedFromCriteria = $items;

        $nroRegLoaded = count($items);

        if ($items && $nroRegLoaded > 0) {
            $obj = $items[0];
        }

        return true;
    }

    /**
     * Carga hacia un objeto, datos de la tabla editable
     *
     * @param object $obj Por referencia
     * @param int $id
     * @param mixed $fields Puede ser string o un array de campos
     * @return bool
     */
    public function loadToObject(&$obj, $id = null, $fields = "*") {
        if (!$id) {
            if ($this->isNew()) {
                return false;
            }

            $id = $this->id;
        }

        $db = Exj::InstanceDatabase();

        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        $sql = "SELECT $fields ";
        $sql .= " FROM $this->_table";

        $criteria = array();
        $criteria[] = $this->_fieldKey . " = " . $id;

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

        //	$db->writeLastQuery();

        return true;
    }

    /**
     * Genera un código único para la tabla que se está editando, el código tienen este formato: yymmXXXX, las X son los 2 caractres de las primeras palabras, ejemplo: 1209BYCO, 1209BYRO, 1209III, 1209BYRO2
     *
     * @param string $nameFieldCode
     * @param string $nameFieldText
     * @param string $nameFieldDate
     * @param string $textDefault No requerido, por defecto 'COD'
     * @return string Si ocurre algún error se retorna false
     */
    public function generateCodeUniqueFromTextDate($nameFieldCode, $nameFieldText, $nameFieldDate, $textDefault = 'COD') {
        $code = '';

        $valueText = $this->$nameFieldText;
        $fechaRaw = $this->$nameFieldDate;

        if (!self::IsSettedValue($fechaRaw)) {
            $fechaRaw = null;
        }
        if (!self::IsSettedValue($valueText)) {
            $valueText = '';
        }

        if (!$valueText || !$fechaRaw) {
            if (!$this->isNew()) {
                $obj = null;

                if ($this->loadToObject($obj)) {
                    if (!$valueText) {
                        $valueText = $obj->$nameFieldText;
                    }
                    if (!$fechaRaw) {
                        $fechaRaw = $obj->$nameFieldDate;
                    }
                }
            }
        }
        if (!$valueText) {
            $valueText = $textDefault;
        }

        $dateStart = null;
        if ($fechaRaw) {
            $dateStart = strtotime($fechaRaw);
        }
        $prefixFecha = date("ym", $dateStart);


        $code = $prefixFecha;
        $code .= self::getCodeFromText($valueText);

        if ($this->haveBrokenRules()) {
            return false;
        }

        // ver si ya existe
        if ($this->foundCode($code, $nameFieldCode)) {
            $secCode = 1;
            $codeTest = '';
            while (true) {
                $secCode += 1;
                $codeTest = $code . $secCode;
                if (!$this->foundCode($codeTest, $nameFieldCode)) {
                    break;
                }
            }
            $code = $codeTest;
        }

        if ($this->haveBrokenRules()) {
            return false;
        }

        $this->$nameFieldCode = $code;

        return $code;
    }

    public function foundCode($codeTest, $nameFieldCode) {
        $db = Exj::InstanceDatabase();

        $id = 0;
        if (!$this->isNew()) {
            $id = $this->id;
        }

        $sql = "SELECT COUNT($this->_fieldKey) ";
        $sql .= " FROM $this->_table";

        $criteria = array();
        $criteria[] = "$nameFieldCode = '$codeTest'";
        if ($id) {
            $criteria[] = $this->_fieldKey . " <> " . $id;
        }
        $sql .= " WHERE " . implode(" AND ", $criteria);

        $numFound = $db->loadResult($sql);
        if ($db->haveError()) {
            return false;
        }

        if (!$numFound) {
            return false;
        }
        $numFound = intval($numFound);

        return ($numFound > 0);
    }

    static function getCodeFromText($text, $maxLong = 4) {
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
                if (strlen($w) <= 1) {
                    continue;
                }

                if (in_array($w, $articulos)) {
                    continue;
                }
                if (strlen($w) <= 3) {
                    $code .= $w;
                } else {
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

    /*
      static function update($id, $params) {
      global $dbh;
      $rec = self::find($id);

      if ($rec == null) {
      return $rec;
      }
      $rs = $dbh->rs();

      foreach ($rs as $idx => $row) {
      if ($row['id'] == $id) {
      $rec->attributes = array_merge($rec->attributes, get_object_vars($params));
      $dbh->update($idx, $rec->attributes);
      break;
      }
      }
      return $rec;
      }
     */

    /**
     * overwrited. Antes de eliminar un registro
     *
     * @param int $id
     * @return bool Retirnar false para cancelar la eliminación
     */
    protected function beforeDestroy($id) {

        return true;
    }

    /**
     * overwrited. Después de eliminar
     *
     * @param int $id
     * @param int $affectedRows
     * @return bool. Retornar false para cancelar el eliminado y adicionar la regla rota
     */
    protected function afterDestroy($id, $affectedRows) {
        return true;
    }

    /**
     * overwrited. Inicio de Guardar
     *
     * @return bool
     */
    protected function initSave() {
        return true;
    }

    /**
     * Antes Guardar
     *
     * @return bool
     */
    protected function beforeSave() {
        return true;
    }

    private function _processDeleteChildEditable(ExjEditableChildModel &$modelChild, ExjEditableModel &$modelParent, &$totalAffectedRows, $idParent) {
        $namesChilds = $modelChild->getChildsNamesEditables();
        $fk = $modelParent->getNameFieldKey();

        $criteria = new stdClass();
        $criteria->$fk = $idParent;
        $nroRegLoadedChilds = 0;
        $modelChild->loadDBFromCriteria($criteria, $nroRegLoadedChilds);
        if ($modelChild->haveBrokenRules()) {
            $this->addBrokenRuler($modelChild->getBrokenRules());
            return false;
        }

        /*
          echo '<br/>Método: '. __METHOD__ . " nroRegLoadedChilds: $nroRegLoadedChilds fk: $fk";
          echo "<br/>Clase Hija: " . get_class($modelChild);
         */
        if ($nroRegLoadedChilds <= 0) {
            // echo "<br/>1. modelChild->destroy($idParent, $fk)";
            $totalAffectedRows += $modelChild->destroy($idParent, $fk);
        } else {
            // echo "<br/>2. modelChild->destroy($modelChild->id)";
            $totalAffectedRows += $modelChild->destroy($modelChild->id);

            if ($nroRegLoadedChilds > 1) {
                $itemsForLoader = $modelChild->getItemsLoadedFromCriteria();
                foreach ($itemsForLoader as $itemForLoader) {
                    if (!$modelChild->loadData($itemForLoader)) {
                        $this->addBrokenRuler("No se pudo cargar data para eliminar childs!");
                        return false;
                    }

                    //	echo "<br/>3. modelChild->destroy($modelChild->id)";
                    $totalAffectedRows += $modelChild->destroy($modelChild->id);
                    if ($modelChild->haveBrokenRules()) {
                        break;
                    }
                }
            }
        }

        if ($modelChild->haveBrokenRules()) {
            $this->addBrokenRuler($modelChild->getBrokenRules());
            return false;
        }

        return true;
    }

    private $_totalRowsChildsDeletes = null;

    public function haveChildsDeletes() {
        if ($this->_totalRowsChildsDeletes === null) {
            return false;
        }

        return ($this->_totalRowsChildsDeletes > 0);
    }

    public function getTotalRowsChildsDeletes() {
        if ($this->_totalRowsChildsDeletes === null) {
            return 0;
        }

        return $this->_totalRowsChildsDeletes;
    }

    /**
     * Elimina en cascada registros. Se deben registrar las entidades editables usando registerChildNameEditable
     *
     * @param int $idParent
     * @return bool or number totalAffectedRows si se eliminó todo con éxito, sino retorna false
     */
    public function destroyChilds($idParent = 0) {

        $childsNamesEditables = $this->getChildsNamesEditables();
        if (!$childsNamesEditables || count($childsNamesEditables) == 0) {
            $this->addBrokenRuler(
                "No se puede eliminar en cascada, no hay nombres childs editables registrados.<br/>Clase: " . get_class($this)
            );
            return false;
        }

        if (!$idParent || $idParent < 0) {
            $idParent = $this->getId();
        }

        if (!$idParent || !self::IsSettedValue($idParent)) {
            $this->addBrokenRuler("No se seteo ID para eliminar en cascada. Clase: " . get_class($this));
            return false;
        }

        //	echo '<h3>'.$this->getClassStr().'</h3> Método: '. __METHOD__. " idParent: $idParent<br/>";
        if ($this->_totalRowsChildsDeletes === null) {
            $this->_totalRowsChildsDeletes = 0;
        }
        foreach ($childsNamesEditables as $childNameEditable) {
            $ClassChildEditable = ExjUtil::GetNameClassModelChildEditableFromName($childNameEditable);

            if (!class_exists($ClassChildEditable)) {
                $this->addBrokenRuler(
                    "Eliminar childs. No existe clase: $ClassChildEditable"
                );

                return false;
            }

            $objChildEditable = new $ClassChildEditable(false, $this->getResponse());
            if (!$this->_processDeleteChildEditable($objChildEditable, $this, $this->_totalRowsChildsDeletes, $idParent)) {
                break;
            }
        }

        if ($this->haveBrokenRules()) {
            return false;
        }

        return true;
    }

    // No Usado
    private function _reveseNamesChilds(&$namesReverse, $childsNamesEditables, $idParent, $objChildx) {
        if (!$childsNamesEditables) {
            return false;
        }

        global $exj;

        foreach ($childsNamesEditables as $childNameEditable) {
            $ClassChildEditable = ExjUtil::GetNameClassModelChildEditableFromName($childNameEditable);
            $objChildEditable = new $ClassChildEditable(false, $this->getResponse());
            $namesChilds = $objChildEditable->getChildsNamesEditables();
            $fk = $objChildx->getNameFieldKey();

            $criteria = new stdClass();
            $criteria->$fk = $idParent;
            $nroRegLoadedChilds = 0;
            $objChildEditable->loadDBFromCriteria($criteria, $nroRegLoadedChilds);
            if ($objChildEditable->haveBrokenRules()) {
                $this->addBrokenRuler($objChildEditable->getBrokenRules());
                break;
            }

            $idFK = $idParent;

            if ($namesChilds && count($namesChilds) > 0) {
                if ($nroRegLoadedChilds) {
                    $idParent = $objChildEditable->getId();

                    if ($nroRegLoadedChilds > 1) {
                        $itemsLoadeds = $objChildEditable->getItemsLoadedFromCriteria();
                        for ($i = 1; $i < count($itemsLoadeds); $i++) {
                            $itemLoaded = $itemsLoadeds[$i];
                            print_r($itemLoaded);
                        }
                        // xxx
                    }
                }
                /// echo "<br/>Recursion con: $ClassChildEditable nroRegLoadedChilds: $nroRegLoadedChilds";

                $this->_reveseNamesChilds($namesReverse, $namesChilds, $idParent, $objChildEditable);
            }

            $valueFK = new stdClass();
            $valueFK->fk = $fk;
            $valueFK->id = $idFK;
            $valueFK->objEditable = $objChildEditable;

            $namesReverse[$childNameEditable] = $valueFK;
        }

        return true;
    }

    /**
     * Destruye o elimina un registro de la db
     *
     * @param int $id Identificador único de la tabla
     * @param string $fieldCriteria Si no se define se toma el campo principal de la tabla
     * @return mixed El número de registros eliminados, false sino se pudo eliminar
     */
    public function destroy($id, $fieldCriteria = '') {
        $db = Exj::InstanceDatabase();

        if (!$fieldCriteria) {
            $fieldCriteria = $this->_fieldKey;
        }

        try {
            if ($this->_useTransactionOnDestroy) {
                if (!$this->_autoTransactionStart(true)) {
                    $this->writeClassLn($this, "No se pudo iniciar auto transacción, ya está iniciada una transacción antes del proceso");
                }
            }

            if (!$id) {
                $this->addBrokenRuler('No se ha indicado Id para eliminar.');
                return false;
            }
            //  $this->setValueId($id);

            if ($this->beforeDestroy($id) === false) {
                if (!$this->haveBrokenRules()) {
                    $this->addBrokenRuler("Método beforeDestroy no devolvio el motivo para no eliminar.<br/>Adicione la regla rota.");
                }
                $this->_autoTransactionEnd();
                return false;
            }

            $sql = "DELETE FROM " . $this->_table;
            $sql .= " WHERE $fieldCriteria = $id";

            $db->setQuery($sql);
            $db->query();

            if (!$db->isValid()) {
                $this->addBrokenRuler($db->getErrorMsg());
                $this->_autoTransactionEnd();
                return false;
            }

            $affectedRows = $db->getAffectedRows();

            /*
              echo '<h3>'.$this->getClassStr().'</h3> Método: ' . __METHOD__;
              echo "<br/>SQL ejecutado: $sql affectedRows: $affectedRows";
             */

            if ($this->afterDestroy($id, $affectedRows) === false) {
                if (!$this->haveBrokenRules()) {
                    $this->addBrokenRuler("Método afterDestroy no devolvio el motivo para no eliminar.<br/>Adicione la regla rota.");
                }
                $this->_autoTransactionEnd();
                return false;
            }

            $this->_autoTransactionEnd();
        } catch (Exception $ex) {
            $this->addBrokenRuler($ex->getMessage());
            $this->_autoTransactionEnd();
            return false;
        }

        return $affectedRows;
    }

    public function enableTransactionOnSave($enable = true) {
        if (ExjDBTrx::IsStartedTransaction()) {
            return $this;
        }

        $this->_useTransactionOnSave = $enable;
        return $this;
    }

    public function enableTransactionOnDestroy($enable = true) {
        if (ExjDBTrx::IsStartedTransaction()) {
            return $this;
        }

        $this->_useTransactionOnDestroy = $enable;
        return $this;
    }

    public function disableAllTransactionsDB($disabled = true) {
        $this->_disableAllTransactionsDB = $disabled;
    }

    public function isDisableAllTransactionsDB(){
        return $this->_disableAllTransactionsDB;
    }

    /**
     * Devuelve el valor del campo clave (FK) de la entidad
     *
     * @return int
     */
    public function getId() {
        $f = $this->_fieldKey;
        return $this->$f;
    }

    /**
     * Retorna el objeto field
     *
     * @param string $name
     * @return mixed Si lo encuentra retorna el objeto sino null
     */
    public function getFieldFromName($name) {
        $fieldFound = null;
        $fields = $this->getFields();
        foreach ($fields as $field) {
            if ($field->getName() == $name) {
                $fieldFound = $field;
                break;
            }
        }

        return $fieldFound;
    }

    public function getAliasFromField($field) {
        if (!$field) {
            return '';
        }

        $alias = $field->alias;
        if (!$alias) {
            $alias = $field->getName();
        }

        return $alias;
    }

    /**
     * Bindea solo la data cambiada
     *
     * @param object $data
     * @return int Número de registros bindeados
     */
    public function bindOnlyDataChanged($data = '', $onlyDataSetted = true) {
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
            $nameField = $f->getName();
            if ($nameField == $this->_fieldKey) {
                if ($this->id) {
                    continue;
                }
            }
            $valueRaw = null;
            $found = false;
            foreach ($vars as $nameFieldVar => $valueField) {
                if ($nameField == $nameFieldVar) {
                    $valueRaw = $valueField;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // echo "<br/> NO FOUND: $nameField | ";

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

            $valueEditable = $this->$nameField;
            if ($valueEditable == $valueNew) {
                // echo " <br/> RESET AL CAMPO: $nameField valor: $valueRaw ";
                $this->resetField($nameField);
                continue;
            }

            // echo " <br/> FIJANDO CAMPO: $nameField valueEditable: $valueEditable valueNew: $valueNew NUMCARS:  " . strlen($valueEditable) . ' y '. strlen($valueNew);

            $this->$nameField = $valueNew;
            ++$nFieldsBinded;
        }

        return $nFieldsBinded;
    }

// bindOnlyDataChanged

    private function _isFieldGeneric($nameField) {
        if ($this->_autoRegisterFechaCreacion && $nameField == 'datet_creacion') {
            return true;
        }

        return ($nameField == 'id_usuario_modifico' || $nameField == 'modificado_dt');
    }

    private function _validateFieldsToSave() {
        if ($this->haveBrokenRules()) {
            return false;
        }

        $isNew = $this->isNew();
        $fieldsAll = $this->getFields();

        foreach ($fieldsAll as $field) {
            $name = $field->getName();
            // $field->type
            if ($name == 'id' || $name == $this->_fieldKey) {
                continue;
            }
            if ($this->_isFieldGeneric($name)) {
                continue;
            }

            if ($isNew) {
                if ($field->isRequired) {
                    if (!$this->isSettedField($name)) {
                        $this->addBrokenRuler('<b>' . $this->getAliasFromField($field) . "</b> " . ExjText::__('es requerido') . '.');
                        continue;
                    }
                }
            }

            if ($this->isSettedField($name)) {
                /*
                  if (!isset($this->$name)) {
                  continue;
                  }
                 */

                $valueRaw = $this->$name;
                if ($field->isNullable) {
                    if($valueRaw === ''){
                        $valueRaw = $this->$name = null;
                    }
                    elseif(!$field->isAllowZero && $valueRaw == 0){
                        $valueRaw = $this->$name = null;
                    }
                }

                if ($field->isRequired) {
                    if (!$valueRaw) {
                        if ($field->isAllowZero && $valueRaw == 0) {
                            continue;
                        }
                        if ($field->isNullable && $valueRaw === null) {
                            continue;
                        }

                        $this->addBrokenRuler('<b>' . $this->getAliasFromField($field) . "</b> es requerido.");
                        continue;
                    }
                }
            }
        }

        if ($this->haveBrokenRules()) {
            $this->addBrokenRuler(($isNew ? 'Creando':'Editando') . ' <b>'. $this->getAliasEditableModel().'</b>');
            
            if (ExjUser::IsRolSuperAdmin()) {
                $nameClassSelf = get_class($this);
                $this->addBrokenRuler('Clase: '. $nameClassSelf);
                Exj::PrintBackTrace('Existen reglas rotas en editable model: '. $nameClassSelf);
            }

            return false;
        }

        return true;
    }

    private $_isModeAuntenticate = false;

    public function fixModeAutenticate($isModeAuntenticate = true) {
        $this->_isModeAuntenticate = $isModeAuntenticate;
    }

    public function validateValueExpressionDefined($value, $nameField) {
        $isValueExp = false;
        if (!$value) {
            return $isValueExp;
        }

        switch ($value) {
            case self::EXP_AUTO_INCREMENT:
                // adicionar las expresiones definidas
                $value = $this->resolverExpressionValue($nameField, $value);
                if (isset($this->$nameField) && $value != $this->$nameField) {
                    $this->$nameField = $value;
                }
                $isValueExp = true;
                break;
        }

        return $isValueExp;
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
            $this->addBrokenRuler(Exj::GetErrorMsgGlobal());
            return false;
        }


        $this->id_usuario_modifico = ExjUser::GetId();
        // 	$this->modificado_dt = Exj::GetDateTimeFromServer();
        $this->modificado_dt = Exj::GetDateTime();
        if ($this->_autoRegisterFechaCreacion && $this->isNew()) {
            $this->datet_creacion = Exj::GetDateTime();
        }

        if (!$this->id_usuario_modifico) {
            if ($this->_isModeAuntenticate) {
                // el usuario que cambia es el superusuario
                $this->id_usuario_modifico = 62;
            }

            if (!$this->id_usuario_modifico) {
                $this->addBrokenRuler("Sessión ha sido finalizado");
                return false;
            }
        }

        if (!$this->_table) {
            $this->addBrokenRuler("No se ha registrado tabla");
            return false;
        }
        if (!$this->_fieldKey) {
            $this->addBrokenRuler("No se ha registrado campo clave de tabla");
            return false;
        }

        $fieldSetter = $this->getFields(true);

        $fk = $this->_fieldKey;
        $numFields = 0;
        foreach ($fieldSetter as $field) {
            $nameField = $field->getName();
            if (!$nameField) {
                continue;
            }
            if ($nameField == $fk) {
                continue;
            }
            if ($this->_isFieldGeneric($nameField)) {
                continue;
            }

            ++$numFields;

            $valueField = $this->$nameField;

            if ($this->validateValueExpressionDefined($valueField, $nameField)) {
                $this->addFieldExpression($nameField);
            }

            if ($this->isFieldExpression($nameField)) {
                if ($valueField) {
                    continue;
                }
            }

            if ($field->isNumeric() && $valueField) {
                if (!is_numeric($valueField)) {
                    $this->addBrokenRuler("$field->alias: debe tener un valor numérico, pero tiene: $valueField");
                    continue;
                }
            }

            if ($valueField) {
                if ($field->isInt()) {
                    $valueField = intval($valueField);
                    $this->$nameField = $valueField;
                }
                if ($field->isFloat()) {
                    $valueField = floatval($valueField);
                    $this->$nameField = $valueField;
                }
            }


            if ($field->isDate() || $field->isDateTime()) {
                if ($field->isNullable) {
                    if (!$valueField || $valueField == '0000-00-00' || $valueField == '0000-00-00 00:00:00') {
                        $valueField = null;
                        $this->$nameField = $valueField;
                        continue;
                    }
                }

                if (!$valueField && !$field->isRequired) {
                    if ($field->isDate()) {
                        $valueField = '0000-00-00';
                    } else {
                        $valueField = '0000-00-00 00:00:00';
                    }
                    $this->$nameField = $valueField;
                    continue;
                }
            }

            if (!$field->isNullable && $valueField === null) {
                $this->addBrokenRuler("$field->alias: no debe tener valor nulo");
                continue;
            }

            if ($field->isRequired) {
                if ($field->isNumeric() && $field->isAllowZero) {
                    if ($valueField === 0 || $valueField === '0') {
                        continue;
                    } else {
                        if ($field->isFloat() && $valueField === 0.00) {
                            continue;
                        }
                        // print_r($field);
                    }
                }


                if (!$field->type || $field->isString()) {
                    if (!$valueField && $valueField !== "0") {
                        $this->addBrokenRuler("$field->alias: tipo string, es requerido");
                    }
                } elseif ($field->isNumeric() && $valueField === 0) {
                    $this->addBrokenRuler("$field->alias: tipo numérico, es requerido");
                } elseif (!$valueField) {
                    // echo "valueField: $valueField";
                    //	print_r($field);
                    $this->addBrokenRuler("$field->alias: tipo $field->type, es requerido");
                }
            }
        }

        if ($numFields == 0) {
            if (!$this->haveDataChilds()) {
                if (!$this->isDirty()) {
                    $this->addBrokenRuler("No se han registrado campos o propiedades en objeto editable." . $this->getClassStr());
                }
            }
        }

        return (count($this->_brokenRules) == 0);
    }

    private function _getValuesToSql() {
        $fieldsSetter = $this->getFields(true);
        if (count($fieldsSetter) == 0) {
            return null;
        }

        $fk = $this->_fieldKey;

        $autoSetterDateTimeCrecion = ($this->_autoRegisterFechaCreacion && $this->isNew());

        $fieldsUpdate = array();
        $fields = array();
        $values = array();

        foreach ($fieldsSetter as $field) {
            $nameField = $field->getName();
            if ($nameField == $fk) {
                continue;
            }

            if ($nameField == 'datet_creacion') {
                if ($autoSetterDateTimeCrecion) {
                    if (!isset($this->datet_creacion)) {
                        $this->datet_creacion = Exj::GetDateTime();
                    }
                } else {
                    continue;
                }
            }

            $valueField = $this->$nameField;

            // normalización para SQL
            if ($valueField === null && $field->isNullable) {
                $valueField = "null";
            } elseif (!$valueField || !$this->isFieldExpression($nameField)) {
                if ($field->isInt()) {
                    $valueField = intval($valueField);
                } else {
                    //	$valueField = addslashes($valueField);
                    $valueField = "'$valueField'";
                }
            }

            $fieldsUpdate[] = "$nameField = $valueField";
            $fields[] = $nameField;
            $values[] = $valueField;
        }

        $ret = new stdClass();
        $ret->fieldsUpdate = implode(", ", $fieldsUpdate);
        $ret->fields = implode(", ", $fields);
        $ret->values = implode(", ", $values);

        return $ret;
    }

    /**
     * Overwrited. Renderiza la criteria antes de cargar el modelo editable
     *
     * @param object $objCriteria
     */
    protected function renderCriteriaToLoad(&$objCriteria) {
        
    }

    public static function GetItems(
        ExjResponse $response, 
        $fields='*', 
        $where = null
    ){
        $items = null;

        if ($where && is_array($where)) {
            $where = implode(' AND ', $where);
        }

        if (empty($fields)) {
            $fields = '*';
        }
        else{
            if (is_array($fields)) {
                $fields = implode(',', $fields);
            }
        }

        $instanceModel = new static(false);

        $table = $instanceModel->getNameTable();
        if(!$table){
            $response->setMsgError(
                "GetItems. No se definió tabla en " . get_class($instanceModel)
            );
            return $items;
        }
        
        $query = "SELECT $fields FROM $table";
        if (!empty($where)) {
            $query .= " WHERE ($where)";
        }

        // echo "GetItems. $query";
        $items = ExjDatabase::GetObjectList($query);
        if ($items === false) {
            $response->setMsgError(
                "Error al consultar items de ",get_class($instanceModel)
            );
        }

        return $items;
    }

    public function getItemsLoadedFromCriteria($exceptCurrentLoaded = true) {
        if (!$this->_itemsLoadedFromCriteria || count($this->_itemsLoadedFromCriteria) == 0) {
            return $this->_itemsLoadedFromCriteria;
        }

        if (!$exceptCurrentLoaded) {
            return $this->_itemsLoadedFromCriteria;
        }
        $id = $this->id;
        if (!$id) {
            return $this->_itemsLoadedFromCriteria;
        }

        $itemsReturn = array();
        $fk = $this->getFieldKey();
        foreach ($this->_itemsLoadedFromCriteria as $itemLoadedFromCriteria) {
            if (isset($itemLoadedFromCriteria->$fk)) {
                if ($id == $itemLoadedFromCriteria->$fk) {
                    continue;
                }
            }

            $itemsReturn[] = $itemLoadedFromCriteria;
        }

        return $itemsReturn;
    }

    /**
     * Carga datos al modelo editable, si el resultado de la consulta es mas de uno se carga el primer registro
     *
     * @param object $criteria
     * @param int $nroRegLoaded
     * @param bool $decodeChars
     * @return bool
     */
    public function loadDBFromCriteria($criteria, &$nroRegLoaded, $decodeChars = true) {
        $nroRegLoaded = 0;

        $criteriaSQL = array();

        $this->setValueId(0);

        $this->renderCriteriaToLoad($criteria);

        global $exj;

        $fieldsCriteria = Exj::GetFieldsVarsFromObject($criteria);
        if (Exj::GetError()->haveError()) {
            return false;
        }

        foreach ($fieldsCriteria as $fieldCriteria) {
            /*
              if (!isset($this->$fieldCriteria)) {
              continue;
              }
             */

            if ($this->getFieldFromName($fieldCriteria) == null) {
                continue;
            }

            $valueCriteria = $criteria->$fieldCriteria;

            $criteriaSQL[] = "$fieldCriteria = '$valueCriteria'";
        }

        if (count($criteriaSQL) == 0) {
            /*
              echo "<br/>CLASE: " . get_class($this).'<br/>';
              print_r($fieldsCriteria);
              print_r($this->getFieldsNames());
             */

            $this->addBrokenRuler("La criteria enviada para carga de registro no concuerda con campos del modelo: " . get_class($this));
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
        $this->_itemsLoadedFromCriteria = $items;

        if ($items) {
            $nroRegLoaded = count($items);

            $item = $items[0];

            $this->loadData($item, false, $decodeChars);
            $fieldKey = $this->_fieldKey;
            if ($this->$fieldKey) {
                $this->id = $this->$fieldKey;
            }
        }

        return true;
    }

    public function loadData($data, $fixValueKey = true, $decodeChars = true) {
        if (!$data || !is_object($data)) {
            return false;
        }

        if ($decodeChars) {
            ExjTransferCharacters::decodeUTF8ToISO($data);
        }
        $this->_setDataToEditable($data);
        if ($fixValueKey) {
            $fieldKey = $this->_fieldKey;
            if (isset($data->$fieldKey)) {
                $this->setValueId($data->$fieldKey);
            } else {
                $this->setValueId(0);
            }
        }

        return true;
    }

    public function reset($clearBrokenRules = false) {
        $this->_isDirty = false;
        $this->_totalRowsChildsDeletes = null;
        $this->_dataToSetterForClone = null;
        $this->_isStartedTrxDBInClonation = false;

        $fields = $this->getFields();
        foreach ($fields as $f) {
            $this->resetField($f->getName());
        }
        $this->setValueId(0);

        if ($clearBrokenRules && $this->haveBrokenRules()) {
            $this->clearBrokenRules();
        }
        
        return $this;
    }

    private $_dataToSetterForClone = null;

    public function setDataToSetterForClone($objToSetter) {
        $this->_dataToSetterForClone = $objToSetter;
    }

    public function getDataToSetterForClone() {
        return $this->_dataToSetterForClone;
    }

    /**
     * Overwrited. Leer parámetros de clonacion del objeto editable
     *
     * @param int $id
     * @param bool $inCascade Defecto es true
     * @param int $indexEditable
     * @param mixed $instanceEditableParent
     * @return bool si se retorna false se cancela la clonación
     */
    protected function readCloneEditable($id, &$inCascade, $indexEditable, $instanceEditableParent) {

        return true;
    }

    private $_isStartedTrxDBInClonation = false;

    /**
     * Clone el objeto editable y sus hijos, para personalizar el proceso use: setDataToSetterForClone y readCloneEditable
     *
     * @param int $id No Reuqerido
     * @param int $indexEditable Defecto 0
     * @return bool true si fué satisfactorio, sino retorna false
     */
    public function cloneEditable($id = 0, $indexEditable = 0) {
        if (!$id) {
            $id = $this->id;
        }

        if (!$id || $id < 0) {
            $this->addBrokenRuler("No se indicó el ID para clone el objeto editable: " . $this->getClassStr(''));
            return false;
        }

        $this->setValueId($id);
        if (!$this->load()) {
            return false;
        }

        $dataToSetter = $this->_dataToSetterForClone;
        $inCascade = true;
        $instanceEditableParent = $this->getInstanceEditableParent();

        // se lee conf personalizada para clonación
        if ($this->readCloneEditable($id, $inCascade, $indexEditable, $instanceEditableParent) === false) {
            // echo "<br/>Se canceló la clonación desde --> ". $this->getClassStr('Clase: ');
            return true;
        }

        if ($this->haveBrokenRules()) {
            return false;
        }
        if ($dataToSetter && !is_object($dataToSetter)) {
            $this->addBrokenRuler("Error Clonando Editable.<br/>La data a setear no es un objeto." . $this->getClassStr());
            return false;
        }

        $this->setValueId(0);

        // setear valores
        if ($dataToSetter) {
            $fk = $this->getNameFieldKey();
            if (isset($dataToSetter->$fk)) {
                unset($dataToSetter->$fk);
            }

            $this->bind($dataToSetter);
        }

        // echo '<br/><br/>' . __METHOD__ . " ID: $id ". $this->getClassStr('Clase: ');
        //	print_r($this->getDataSetted());
        $this->enableTransactionOnSave(false);
        if (!ExjDBTrx::IsStartedTransaction()) {
            ExjDBTrx::Start();
            $this->_isStartedTrxDBInClonation = true;
            // echo "<br/> - INICIANDO TRX DB" . $this->getClassStr('Clase: ');
        }

        $this->save();
        if ($this->haveBrokenRules()) {
            // echo "<br/>NO SE GUARDO. DATA SETEADA: <br/>";
            // print_r($this->getDataSetted());
            return false;
        }
        // echo "<br/>SE GUARDO, NUEVO ID: " . $this->id;

        if (!$inCascade) {
            return true;
        }

        if (!$this->haveChildsEditables()) {
            // echo "<br/>No tiene childs editables.";
            return true;
        }

        // vemos si tiene hijos
        $childsNamesEditables = $this->getChildsNamesEditables();
        global $exj;

        foreach ($childsNamesEditables as $childNameEditable) {
            
            $ClassChildEditable = ExjUtil::GetNameClassModelChildEditableFromName($childNameEditable);
            $objChildEditable = new $ClassChildEditable(false, $this->getResponse(), $this);

            // echo "<br/>Clonando child: $ClassChildEditable";

            if (!$this->_cloneChildEditable($objChildEditable, $id)) {
                // echo "<br/>NO SE PUDO Clonar child: $ClassChildEditable";
                break;
            }
        }

        $resultSuccessClonation = (!$this->haveBrokenRules());

        if ($this->_isStartedTrxDBInClonation) {
            // echo "<br/> - INICIANDO FINALIZACION TRX DB" . $this->getClassStr('Clase: ');

            $this->_isStartedTrxDBInClonation = false;

            if (ExjDBTrx::IsStartedTransaction()) {
                // echo "--> FINALIZANDO TRX DB OK";

                if ($resultSuccessClonation) {
                    ExjDBTrx::Commit();
                } else {
                    ExjDBTrx::Rollback();
                }
            }
        }


        return $resultSuccessClonation;
    }

    private function _cloneChildEditable(ExjEditableChildModel &$objChildEditable, $valueFKParentOriginal) {
        if (!$objChildEditable->cloneChildEditable($valueFKParentOriginal)) {
            $this->addBrokenRuler($objChildEditable->getBrokenRules());
            return false;
        }

        return true;
    }

    /**
     * Retorna una consulta SQL personalizada
     *
     * @param string $queryLast Consulta por defecto a ejecutar
     * @param int $id
     * @param string $table
     * @param string $fieldKey
     * @return string Si se retorna un tipo que no sea string no se toma en cuenta lo retornado en esta función
     */
    protected function getQueryCustomForLoad($queryLast, $id, $table, $fieldKey) {
        return false;
    }

    private $_dataSelf=null;
    public function getDataFromId($id = null) {
        if ($id === null) {
            $id = $this->id;
        }

        if (!$id) {
            return null;
        }
        if (!self::IsSettedValue($id)) {
            return null;
        }

        if (!$this->_dataSelf) {
            $this->_dataSelf = array();
        }
        
        if (!isset($this->_dataSelf[$id])) {
            $query = "SELECT * ";
            $query .= " FROM $this->_table";
            $query .= " WHERE $this->_fieldKey = $id";

            $this->_dataSelf[$id] = ExjDatabase::GetObjectFromQuery($query);
        }

        return $this->_dataSelf[$id];
    }

    /**
     * Carga en la propiedades de esta clase desde la db
     *
     * @param int $id
     * @return bool true si encontró el Id, false sino
     */
    public function load($id = null, $noFoundAddError = true) {
        // se sobre-escribe la función
        //	echo '<h3>'.$this->getClassStr(). ' Método: '.__METHOD__.'</h3>';

        $db = Exj::InstanceDatabase();
        if ($id === null) {
            $id = $this->id;
        }
        if (!$id) {
            $this->addBrokenRuler("No se ha definido id para load() en<br/>Modelo editable: " . get_class($this));
            self::PrintBackTrace();
            return false;
        }
        if (!self::IsSettedValue($id)) {
            $this->addBrokenRuler("No se ha seteado id para load() en modelo editable.");
            return false;
        }

        $query = "SELECT * ";
        $query .= " FROM $this->_table";
        $query .= " WHERE $this->_fieldKey = $id";

        $queryCustom = $this->getQueryCustomForLoad($query, $id, $this->_table, $this->_fieldKey);
        if ($queryCustom && is_string($queryCustom) && strlen($queryCustom) > 15) {
            $query = $queryCustom;
        }

        $obj = null;
        $db->setQuery($query);
        $db->loadObject($obj);
        if ($db->getErrorMsg()) {
            $this->addBrokenRuler($db->getErrorMsg());
            return false;
        }
        if (!$obj) {
            if ($noFoundAddError) {
                $this->addBrokenRuler("ID: $id no se encuentra.<br/>Clase: " . get_class($this) . "<br/>Query: " . $query);
            }

            return false;
        }
        
        $this->normalizeLoadObjectToEditable($obj, $id);

        $this->_setDataToEditable($obj);
        $this->id = $id;

        return true;
    }
    
    /**
     * overwrited. Normalización del objeto que setea al modelo editable
     *
     * @param object $obj
     * @param int $id
     */
    protected function normalizeLoadObjectToEditable(&$obj, $id){
    	
    }

    private function _setDataToEditable($data, $noFoundSetValue = true, $valueNoFound = null) {
        if (!$data) {
            return;
        }

        // echo " <br/>Fijando a editable: ";
        // print_r($data);
        $fields = $this->getFields();
        foreach ($fields as $f) {
            $name = $f->getName();

            // echo " <br/>name: $name ";
            if (isset($data->$name)) {
                $this->$name = $data->$name;
                // echo " ENCONTRADO this->name: ". $this->$name;
            } else {
                if ($f->isNullable) {
                    $this->$name = null;
                } else {
                    if ($noFoundSetValue) {
                        $this->$name = $valueNoFound;
                    }
                }
            }
        }

        $this->afterSetterDataToEditable($data, $noFoundSetValue, $valueNoFound);

        $this->setterControlsUI();
    }

    /**
     * overwrited. Después se setear al modelo editable
     *
     * @param object $data
     * @param bool $noFoundSetValue
     * @param mixed $valueNoFound Por lo general tiene el valor null
     */
    protected function afterSetterDataToEditable($data, $noFoundSetValue, $valueNoFound) {
        
    }

    private function _executeRollback() {
        if ($this->_disableAllTransactionsDB) {
            return false;
        }

        if (ExjDBTrx::IsStartedTransaction()) {
            $this->bufferDebugAdd("Ejecutando Rollback. Desde: " . get_class($this));
            return ExjDBTrx::Rollback();
        }

        return false;
    }

    private function _autoTransactionStart($forceStart = false) {
        $this->_startedAutoTransaction = false;

        if ($this->_disableAllTransactionsDB) {
            return false;
        }

        if ($forceStart || $this->haveDataChilds()) {
            if (!ExjDBTrx::IsStartedTransaction()) {
                ExjDBTrx::Start();
                $this->_startedAutoTransaction = true;
                $this->bufferDebugAdd("Iniciado auto transacción");
                return true;
            }
        }

        return false;
    }

    private function _autoTransactionEnd() {
        if (!$this->_startedAutoTransaction) {
            return false;
        }

        if ($this->_disableAllTransactionsDB) {
            return false;
        }

        $this->_startedAutoTransaction = false;
        if ($this->haveBrokenRules()) {
            $this->_executeRollback();
            $this->bufferDebugAdd("Auto transacción. ROOLBACK");
            return true;
        }

        $this->bufferDebugAdd("Auto transacción. COMMIT");

        if (!ExjDBTrx::Commit()) {
            $this->addBrokenRuler("No se pudo hacer commit en auto transacción");
            return false;
        }

        //     echo "<br/>Se ejecutó commit. Clase: " . get_class($this);

        return true;
    }

    /**
     * Overwirted. Permite cancelar la acción de Guardar, retornar true para cancelar
     *
     * @param int $id Id de la entidad
     */
    protected function cancelSave($id) {
        return false;
    }

    /*
      protected function saveCustomChilds($dataChilds){

      }
     */

    /**
     * Indica si ha sido seteado el id de la entidad
     *
     * @return bool
     */
    public function isSetterValueId() {
        $id = $this->id;
        if (!self::IsSettedValue($id)) {
            return false;
        }

        if (!$id) {
            return false;
        }

        return ($id > 0);
    }

    /**
     * Indica si la entidad fué nueva después de guardar, si se usa este método antes de guardar el resultado de isNew()
     *
     * @return bool
     */
    public function wasNew() {
        if ($this->_wasNew === null) {
            return $this->isNew();
        }

        return $this->_wasNew;
    }

    public function syncEditableModel(ExjEditableModel $modelEdit){
        if ($modelEdit->isDisableAllTransactionsDB()) {
            $this->disableAllTransactionsDB();
        }

        return $this;
    }

    /**
     * Guarda en base de datos, si el id se setea se actualiza sino se crea
     *
     * @return bool true si fue satisfactorio sino false
     */
    public function save() {
        $this->_isSaved = false;

        $this->_wasNew = $this->isNew();

        $this->bufferDebugAdd("Iniciando", __METHOD__);

        if (!ExjUser::IsLogin()) {
            $this->addBrokenRuler("Se terminó la sesión de Usuario, por favor loguese nuevamente. Referencia: " . $this->getAliasModel());
        }

        if ($this->haveBrokenRules()) {
            $this->_executeRollback();
            return false;
        }

        try {
            if ($this->_useTransactionOnSave) {
                if (!$this->_autoTransactionStart(true)) {
                    $this->writeClassLn($this, "ADVERTENCIA. No se pudo iniciar auto transacción, ya está iniciada una transacción antes del proceso");
                }
            }

            $this->_saveValidDatesFromUntil();

            if (!$this->haveBrokenRules()){
                if ($this->initSave() === false) {
                    if ($this->isValid()) {
                        $this->addBrokenRuler("El método initSave no informó el Error! Referencia: " . $this->getAliasModel());
                    }
                }
            }

            if ($this->haveBrokenRules()) {
                $this->_executeRollback();
                $this->validateResponse();

                return false;
            }

            $this->id = $this->getId();

            if (!$this->_validateFieldsToSave()) {
                $this->_executeRollback();
                return false;
            }

            if (!$this->_validateConstraints()) {
                $this->_executeRollback();
                return false;
            }

            if ($this->cancelSave($this->getId()) === true) {
                return false;
            }



            if ($this->beforeSave() === false) {
                if ($this->isValid()) {
                    $this->addBrokenRuler("El método beforeSave no informó el Error!<br/>Clase: " . get_class($this));
                }
                $this->_executeRollback();
                return false;
            }

            if (!self::IsSettedValue($this->id)) {
                $this->addBrokenRuler("No se ha definido el Id del campo: $this->_fieldKey.<br/>No se puede guardar");
                $this->_executeRollback();
                return false;
            }

            if (!$this->isValid()) {
                $this->_executeRollback();
                return false;
            }


            ExjTransferCharacters::encodeISOToUTF8($this);

            global $exj;
            $db = Exj::InstanceDatabase();

            $valuesToSql = $this->_getValuesToSql();
            if (!$valuesToSql) {
                $this->addBrokenRuler("No hay campos seteados.<br/>No se puede guardar");
                return false;
            }

            if (!$this->_startedAutoTransaction) {
                // prueba a inicar transaccion si tiene entidades hijas, en este tipo
                // de entidades deben ser tablas tipo innodb
                $this->_autoTransactionStart();
            }

            if (!$this->_saveLogPersistence()) {
                if ($this->haveBrokenRules(true)) {
                    return false;
                }
            }

            $query = '';
            $isNew = false;

            if ($this->id) {
                // es update
                $query .= "UPDATE $this->_table";
                $query .= " SET $valuesToSql->fieldsUpdate";
                $query .= " WHERE $this->_fieldKey = $this->id";
            } else {
                // es new
                $isNew = true;
                $query .= "INSERT INTO $this->_table";
                $query .= " ($valuesToSql->fields)";
                $query .= " VALUES($valuesToSql->values)";
            }

            // echo "query: $query";
            $this->bufferDebugAdd("Ejecutando SQL: $query");

            $db->query($query);

            //     echo "<br/>" . __METHOD__ . $this->getClassStr(' Clase:') .' SQL: '. $db->getQuery();

            if ($db->getErrorMsg()) {
                $msgBrokenRuler = array();
                $msgBrokenRuler[] = $this->id ? "ERROR ACTUALIZANDO REGISTRO" : "ERROR INSERTANDO REGISTRO";
                $msgBrokenRuler[] = 'Clase: ' . get_class($this);

                if (ExjUser::IsRolSuperAdmin()) {
                    $msgBrokenRuler[] = '<b>Referencia</b>: ' . $db->getErrorMsg();
                } else {
                    echo $db->getErrorMsg();
                }

                $msgBrokenRuler = implode("<br/>", $msgBrokenRuler);
                $this->addBrokenRuler($msgBrokenRuler);


                $this->_executeRollback();

                return null;
            }

            $this->_isSaved = true;

            if ($isNew) {
                $this->setValueId($db->insertid());
            } else {
                $this->id = $this->getId();
            }

            $this->_fixIdToResponse();

            if ($this->afterSave($this->_response->data) === false) {
                if (!$this->haveBrokenRules()) {
                    $this->addBrokenRuler("Método afterSave no informó el error. Clase: " . get_class($this));
                }

                $this->_autoTransactionEnd();
                return $this;
            }

            $this->_saveChilds();

            if ($this->afterSaveChilds($this->_response->data) === false) {
                if (!$this->haveBrokenRules()) {
                    $this->addBrokenRuler("Método afterSaveChilds no informó el error. Clase: " . get_class($this));
                }

                $this->_autoTransactionEnd();

                return $this;
            }

            $this->_autoTransactionEnd();

            if ($this->haveBrokenRules(true)) {
                return false;
            }
        } catch (Exception $ex) {
            $this->addBrokenRuler($ex->getMessage());
            $this->_autoTransactionEnd();
            return false;
        }

        return $this;
    }

    private function _saveValidDatesFromUntil(){
        if (!$this->useValidDatesFromUntil) {
            return $this;
        }

        if ($this->isNew()) {
            if (!$this->isSettedField('valid_from_date') || !$this->valid_from_date) {
                $this->valid_from_date = Exj::GetDate();
            }

            if (!$this->isSettedField('valid_until_date') || $this->valid_until_date==='') {
                $this->valid_until_date = null;
            }
        }

        $dateFrom = $this->getValueFieldOrDB('valid_from_date');
        $dateUntil = $this->getValueFieldOrDB('valid_until_date');
        // echo "<br>dateFrom: $dateFrom dateUntil: $dateUntil";

        if ($dateFrom && $dateUntil && $dateFrom > $dateUntil) {
            $this->addBrokenRuler("Fechas de vigencia, desde es mayor que fecha hasta");
        }

        return $this;
    }

    public function getValueFieldOrDB($nameField, $valDefault=null){
        $value = $valDefault;
        $nameField = trim($nameField);

        if ($this->isSettedField($nameField)) {
            $value = $this->$nameField;
        }
        elseif (!$this->isNew()) {
            $data = $this->getDataFromId();
            if ($data && isset($data->$nameField)) {
                $value = $data->$nameField;
            }
        }

        return $value;
    }

    public function getValueFieldFromDB($nameField, $valDefault=null){
        if ($this->isNew()) {
            return $valDefault;
        }

        $data = $this->getDataFromId();
        if ($data && isset($data->$nameField)) {
            return $data->$nameField;
        }

        return $valDefault;
    }

    private function _saveLogPersistence() {
        $db = Exj::InstanceDatabase();
//    	echo '<br/>'. __METHOD__;

        if (!$this->id) {
            return true;
        }

        $fieldsSetter = $this->getFields(true);
        if (count($fieldsSetter) == 0) {
            return null;
        }

        $fk = $this->_fieldKey;

        // recuperar valores originales
        $fields = array();
        $valuesNews = array();
        foreach ($fieldsSetter as $field) {
            $nameField = $field->getName();
            if ($nameField == $fk) {
                continue;
            }

            if ($this->_isFieldGeneric($nameField)) {
                continue;
            }

            $valueField = $this->$nameField;

            // normalización
            if ($valueField === null && !$field->isNullable) {
                $valueField = '';
            }

            if ($valueField !== null && $valueField !== '') {
                switch ($field->type) {
                    case 'int':
                        $valueField = intval($valueField);
                        break;

                    case 'float':
                        $valueField = floatval($valueField);
                        break;

                    case 'datetime':
                        $valueField = ExjDate::ConvertToDateTimeDB($valueField);
                        break;

                    case 'date':
                        $valueField = ExjDate::ConvertToDateDB($valueField);
                        break;
                }
            }

            $fields[] = $nameField;

            $valueNew = new stdClass();
            $valueNew->nameField = $nameField;
            $valueNew->alias = $field->alias;
            $valueNew->type = $field->type;
            $valueNew->value = $valueField;
            $valuesNews[] = $valueNew;
        }


        if (!$this->_loadValuesOlds($fields)) {
            return false;
        }

        $nameTable = $this->_table;
        /*
          echo "<br/>tabla: $nameTable fieldKey: $fk ID=$this->id<br/>";
          echo "<br/>valuesOlds: ";
          print_r($this->_valuesOlds);
         */

        $id_log_pers_table = $db->loadResult("SELECT id_log_pers_table FROM exj_log_pers_tables WHERE name_table = '$nameTable'");
        if (!$db->isValid()) {
            return false;
        }

        if (!$id_log_pers_table) {
            $alias_table = $this->getAliasModel();
            $query = "INSERT INTO exj_log_pers_tables";
            $query .= " (name_table,alias_table,name_field_key)";
            $query .= " VALUES('$nameTable','$alias_table','$fk')";

            $db->query($query);
            if (!$db->isValid()) {
                return false;
            }
            $id_log_pers_table = $db->insertid();
        }

        $props = $db->loadObjectList("SELECT * FROM exj_log_pers_props WHERE id_log_pers_table = $id_log_pers_table");
        if (!$db->isValid()) {
            return false;
        }

        foreach ($valuesNews as &$valueNew) {
            $valueNew->id_log_pers_prop = 0;

            $name_prop = $valueNew->nameField;

            if (count($props) > 0) {
                foreach ($props as $prop) {
                    if ($prop->name_prop == $name_prop) {
                        $valueNew->id_log_pers_prop = $prop->id_log_pers_prop;
                        break;
                    }
                }
                if ($valueNew->id_log_pers_prop) {
                    continue;
                }
            }

            $alias_prop = $valueNew->alias;

            $type_prop = 'STRING';
            switch (strtolower($valueNew->type)) {
                case 'int':
                    $type_prop = 'INT';
                    break;

                case 'float':
                    $type_prop = 'FLOAT';
                    break;

                case 'datetime':
                    $type_prop = 'DATETIME';
                    break;

                case 'date':
                    $type_prop = 'DATE';
                    break;
            }

            if (!$alias_prop) {
                $alias_prop = str_replace('_', ' ', $name_prop);
                $alias_prop = ucwords($alias_prop);
            }

            if (strlen($alias_prop) >= 4) {
                if (trim(strtolower(substr($alias_prop, 0, 3))) == 'id') {
                    $alias_prop = trim(substr($alias_prop, 2));
                }
            }

            Exj::TrasferCharsEncodeISOToUTF8($alias_prop);

            $query = "INSERT INTO exj_log_pers_props";
            $query .= " (id_log_pers_table,name_prop,type_prop,alias_prop)";
            $query .= " VALUES ($id_log_pers_table,'$name_prop','$type_prop','$alias_prop')";

            $db->query($query);
            if (!$db->isValid()) {
                $this->addBrokenRuler($db->getErrorMsg());
                break;
            }

            $valueNew->id_log_pers_prop = $db->insertid();
        }

        if ($this->haveBrokenRules()) {
            return false;
        }

        /*
          echo "<br/>valuesNews: ";
          print_r($valuesNews);
         */
        // xxx

        foreach ($valuesNews as $valueNewItem) {
            $nameField = $valueNewItem->nameField;
            $value_old = null;

            if ($this->_valuesOlds && isset($this->_valuesOlds->$nameField)) {
                $value_old = $this->_valuesOlds->$nameField;
            }

            $value_new = $valueNewItem->value;

            if ($value_new === $value_old) {
                continue;
            }

            if ($value_new == $value_old) {
//				echo "<br/>No se escribio en log: $nameField Valores iguales, comparación no estricta";
                continue;
            }


            if ($nameField == $fk) {
                //			echo "<br/>No se escribio en log: $nameField porque es FK";
                continue;
            }

            if ($value_old) {
                $value_old = addslashes($value_old);
            }
            if ($value_new) {
                $value_new = addslashes($value_new);
            }

            $value_old = self::ConvertValueToSQL($value_old, 90);
            $value_new = self::ConvertValueToSQL($value_new, 90);

            $id_primary_key_current = $this->id;
            $id_primary_key_root = null;
            $id_empresa = null;
            if (isset($this->id_empresa) && self::IsSettedValue($this->id_empresa) && $this->id_empresa) {
                $id_empresa = $this->id_empresa;
            } else {
                $id_empresa = ExjUser::GetIdEmpresa();
            }

            if (!$id_empresa) {
                $id_empresa = self::ConvertValueToSQL($id_empresa);
            }

            $action_pers = 'UPDATE';
            $id_log_pers_prop = $valueNewItem->id_log_pers_prop;

            // $ref_change = null;
            $ref_change = $id_primary_key_current . 'P';
            if ($this instanceof ExjEditableChildModel) {
                $ref_change = $id_primary_key_current . 'C';

                $idsParents = array();
                self::LoadIdsParents($this, $idsParents);
                $numsIdsParents = count($idsParents);
                if ($numsIdsParents > 0) {
                    for ($i = 0; $i < $numsIdsParents; $i++) {
                        $idParent = $idsParents[$i];

                        $ref_change .= $idParent;
                        if ($i < $numsIdsParents - 1) {
                            $ref_change .= 'C';
                        } else {
                            $ref_change .= 'P';
                            $id_primary_key_root = $idParent;
                        }
                    }
                }
            }

            $ref_change = self::ConvertValueToSQL($ref_change, 45);

            $alias_model = $this->getAliasModel();
            // echo "<br/>alias_model: $alias_model";
            if (!$alias_model) {
                $alias_model = null;
            }
            $alias_model = self::ConvertValueToSQL($alias_model, 12);

            $id_usuario_modifico = $this->id_usuario_modifico;
            $modificado_dt = $this->modificado_dt;
            if (!$modificado_dt) {
                $modificado_dt = Exj::GetDateTime();
            }

            $id_primary_key_root = self::ConvertValueToSQL($id_primary_key_root);

            if ($value_old) {
                $value_old = self::ConvertValueToSQL(str_replace(array('"', "'", '\\'), '', $value_old));
                Exj::TrasferCharsEncodeISOToUTF8($value_old);
            }
            if ($value_new) {
                $value_new = self::ConvertValueToSQL(str_replace(array('"', "'", '\\'), '', $value_new));
                Exj::TrasferCharsEncodeISOToUTF8($value_new);
            }

            $query = "INSERT INTO exj_log_pers_items";
            $query .= " (id_primary_key_current,id_empresa,id_primary_key_root,alias_model,action_pers,id_log_pers_prop,value_old,value_new,ref_change,id_usuario_modifico,modificado_dt)";
            $query .= " VALUES ($id_primary_key_current,$id_empresa,$id_primary_key_root,$alias_model,'$action_pers',$id_log_pers_prop,$value_old,$value_new,$ref_change,$id_usuario_modifico,'$modificado_dt')";

            $db->query($query);
            if (!$db->isValid()) {
                $this->addBrokenRuler($db->getErrorMsg());
                break;
            }
        }

        return (!$this->haveBrokenRules());
    }

    /**
     * Retorna la instance del modelos editable padre, si el editable es child retona la instancia sino retona null
     *
     * @return mixed null o ExjEditableModel
     */
    public function getInstanceEditableParent() {
        if ($this instanceof ExjEditableChildModel) {
            return $this->getEditableParent();
        }

        return null;
    }

    static function LoadIdsParents(ExjEditableChildModel $modelChild, &$idsParents) {
        $editableParent = $modelChild->getEditableParent();
        if ($editableParent) {
            if (!$editableParent->id) {
                return;
            }

            $idsParents[] = $editableParent->id;
            if ($editableParent instanceof ExjEditableChildModel) {
                self::LoadIdsParents($editableParent, $idsParents);
            }
        }
    }

    static function ConvertValueToSQL($value, $maxChars = null) {
        if ($value === null) {
            return 'null';
        }

        if ($maxChars && is_string($value)) {
            if (strlen($value) > $maxChars) {
                $value = substr($value, 0, $maxChars - 1);
            }
        }

        return "'$value'";
    }

    private $_valuesOlds = null;

    private function _loadValuesOlds($fields, $id = null) {
        if (count($fields) == 0) {
            return false;
        }

        if (!$id) {
            $id = $this->id;
        }
        if (!$id) {
            return false;
        }

        $db = Exj::InstanceDatabase();
        $this->_valuesOlds = null;

        $query = "SELECT " . implode(', ', $fields);
        $query .= " FROM " . $this->_table;
        $query .= " WHERE " . $this->_fieldKey . '=' . $id;

        $db->setQuery($query);

        $db->loadObject($this->_valuesOlds);
        if (!$db->isValid()) {
            $this->addBrokenRuler($db->getErrorMsg());
            return false;
        }

        return ($this->_valuesOlds ? true : false);
    }

    /**
     * Indica si el modelo editable está sucio o modificado
     *
     * @return bool
     */
    public function isDirty() {
        return $this->_isDirty;
    }

    public function isDirtyField($nameField){
        if ($this->isNew()) {
            return $this->isSettedField($nameField);
        }

        if (!$this->isSettedField($nameField)) {
            return false;
        }

        $valueDB = $this->getValueFieldFromDB($nameField, self::VALUE_NOSETTER);
        if ($valueDB == self::VALUE_NOSETTER) {
            // no se encontró en DB
            return false;
        }

        $valueSetted = $this->$nameField;

        return ($valueSetted != $valueDB);
    }

    public function isDirtyAnyField($namesFields){
        if (is_string($namesFields)) {
            $namesFields = explode(',', $namesFields);
        }

        $isDirty = false;

        if (!is_array($namesFields) || empty($namesFields)) {
            return $isDirty;
        }

        foreach ($namesFields as $nameField) {
            $nameField = trim($nameField);
            if (!$nameField) {
                continue;
            }

            if ($this->isDirtyField($nameField)) {
                $isDirty = true;
                break;
            }
        }

        return $isDirty;
    }

    /**
     * overwrited. Bindeo datos a la clase del modelo
     *
     * @param object $data
     * @param bool $usePostFixNames Defecto false
     * @return int numero de campos de la clase bindeados
     */
    public function bind($data = '', $usePostFixNames = false) {
        if ($this->haveChildsEditables() && $data) {
            // se forza ensuciar al modelo, para actualización de sus hijos
            $this->_isDirty = true;
        }
        if (!$usePostFixNames && $this->_forceUsePostFixNamesInBind) {
            $usePostFixNames = true;
        }

        $nBinded = parent::bind($data, $usePostFixNames);
        if (!$nBinded) {
            return $nBinded;
        }

        $this->_isDirty = true;

        $nameFieldKey = $this->_fieldKey;

        if ($this->isSettedField($nameFieldKey)) {
            $this->id = $this->$nameFieldKey;
            //	echo "<br/>Se ha setado ID key: $nameFieldKey, valor: ". $this->id;
        }

        return $nBinded;
    }

    /**
     * Guarda multiples cambios, esto se hace cuando existen cambios en dataChilds, si no se envia esto dato se genera un error.
     *
     * @return array Arreglo de ids creados o actualizados
     */
    public function saveMultiple() {
        if (!$this->haveDataChilds()) {
            $this->addBrokenRuler("No existen múltiples datos en el store!");
            return false;
        }
        $this->_isDataChildsSaved = true;

        ExjDBTrx::Start();
        $idsSaved = array();
        $indexChild = -1;
        foreach ($this->_dataChilds as $dataChild) {
            $indexChild += 1;

            $data = null;
            if (isset($dataChild->data)) {
                $data = $dataChild->data;
            }
            if (!$data) {
                $this->addBrokenRuler("No se envió data en dataCilds!");
                break;
            }
            //	print_r($dataChild);
            $indexIds = 0;
            if (isset($dataChild->childKey)) {
                $indexIds = $dataChild->childKey;
            } else {
                $indexIds = '_child' . $indexChild;
            }

            $idsSaved[$indexIds][] = $this->_saveItemMultiple($data, $dataChild);
            if ($this->haveBrokenRules()) {
                break;
            }
        }

        if ($this->haveBrokenRules()) {
            if (ExjDBTrx::IsStartedTransaction()) {
                ExjDBTrx::Rollback();
            }

            return false;
        }

        ExjDBTrx::Commit();

        /*
          echo "All idsSaved:<br/>";
          print_r($idsSaved);
         */

        return $idsSaved;
    }

    private function _saveItemMultiple($data, $itemChild) {
        // $this->addBrokenRuler("test save multiple");
        // print_r($itemChild);

        $idsSaved = array();

        $this->_saveItems($data->news, $idsSaved);
        $this->_saveItems($data->edited, $idsSaved);

        if (isset($data->idsDeleted) && is_array($data->idsDeleted) && count($data->idsDeleted) > 0) {
            foreach ($data->idsDeleted as $idToDel) {
                $this->reset();
                $this->setValueId($idToDel);
                $this->destroy($idToDel);
                if ($this->haveBrokenRules()) {
                    break;
                }
            }

            if ($this->haveBrokenRules()) {
                return false;
            }

            return $idsSaved;
        }

        if (count($idsSaved) == 0) {
            $this->addBrokenRuler("GUARDADO MULTIPLE.<br/>No se guardaron cambios!");
            // print_r($data);
            return false;
        }

        // print_r($idsSaved);
        return $idsSaved;
    }

    private function _saveItems($items, &$idsSaved) {
        if (!$items || !is_array($items)) {
            return false;
        }

        $fk = $this->getNameFieldKey();

        foreach ($items as $item) {
            if (!isset($item->$fk)) {
                $this->addBrokenRuler("Items a Guardar no tienen definido campo clave!");
                break;
            }

            $valueKey = $item->$fk;

            if ($valueKey < 0) {
                $valueKey = 0;
            }

            $this->reset();
            if ($this->bind($item)) {
                $this->setValueId($valueKey);
                $indexOperation = ($this->isNew() ? 'news' : 'edited');

                $this->save();
                $idsSaved[$indexOperation][] = $this->getId();
            }
        }
    }

    private function _isClassParent($nameParentEditable) {
        $thisClass = get_class($this);
        $thisClass = strtolower($thisClass);
        $nameParentEditable = strtolower($nameParentEditable);

        if ($thisClass == $nameParentEditable) {
            return true;
        }

        $nameParentEditable = strtolower(ExjUtil::ConvertirGionesToUcfirst($nameParentEditable));

        $pos = strpos($thisClass, $nameParentEditable);
        if ($pos === false) {
            // echo '<br/>'.__METHOD__. " No coincide. thisClass: $thisClass nameParentEditable: $nameParentEditable";
            return false;
        }

        if ($pos <= 4) {
            return true;
        }

        return false;
    }

    private function _saveChilds() {
        if (!$this->haveDataChilds()) {
            return false;
        }


        if ($this->_isDataChildsSaved) {
            return false;
        }

        $this->bufferDebugAdd("Guandando Hijos");

        /*
          print_r($this->_dataChilds);
          $this->addBrokenRuler("Pruebas de guardado de hijos: " . $this->getClassStr());
          return false;
         */

        global $exj;

        foreach ($this->_dataChilds as &$dataChild) {
            $nameModelList = '';
            if (isset($dataChild->nameList)) {
                $nameModelList = $dataChild->nameList;
            }

            $nameModelEditable = $dataChild->nameEditable;
            $parentEditable = $dataChild->parentEditable;

            $data = $dataChild->data;
            if (!$data->haveChanges) {
                continue;
            }

            $ClassChildEditable = ExjUtil::GetNameClassModelChildEditableFromName(
                $nameModelEditable
            );

            if (!class_exists($ClassChildEditable)) {
                continue;
            }

            // echo "<br>ClassChildEditable: $ClassChildEditable";

            /*
              echo "<h2>Clase actual:</h2>" . get_class($this)." Clase editable Hija: $ClassChildEditable parentEditable: $parentEditable <br/>";
              echo 'Id de Clase Padre: '.$this->getId() . " PK Padre: ". $this->_fieldKey;

              if ($parentEditable == 'cnt_order_det') {
              echo "<br/>data: ";
              print_r($data);
              }
             */


            if ($parentEditable) {
                if (!$this->_isClassParent($parentEditable)) {
                    // echo "<br/>No es clase padre";
                    continue;
                }
            }

            if ($parentEditable == '') {
                /*
                  echo "Clase padre: " . $this->getClassStr() . " ID: " . $this->getId();
                  echo " ClassChildEditable: $ClassChildEditable nameModelEditable: $nameModelEditable";
                  echo "<br/>parentEditable: $parentEditable";
                  print_r($data);
                 */

                $this->addBrokenRuler(
                    "Test de guadado hijos de: " . $this->getClassStr()
                );
                break;
            }

            $this->bufferDebugAdd("Hijo. Clase editable: $ClassChildEditable");

            $objEditable = new $ClassChildEditable(false, $this->getResponse(), $this);
            if (!($objEditable instanceof ExjEditableModel)) {
                $this->addBrokenRuler("La clase $ClassChildEditable debe ser heredar de la clase: ExjEditableModel o ExjEditableChildModel");
                continue;
                // break;
            }

            if (count($data->idsDeleted) > 0) {
                foreach ($data->idsDeleted as $idChild) {
                    $id = intval($idChild);
                    if (is_nan($id)) {
                        $this->addBrokenRuler("No se pudo Eliminar.<br/>No es un valor numérico el valor: $idChild.<br/>Referencia: " . $this->getAliasModel($ClassChildEditable));
                        continue;
                    }
                    if ($id <= 0) {
                        continue;
                    }

                    $this->bufferDebugAdd("Hijos. Eliminando con ID: $id");

                    $objEditable->destroy($id);
                    if ($objEditable->haveBrokenRules()) {
                        $this->addBrokenRuler(
                            $objEditable->getBrokenRules() . "<br/>Eliminando Registro en: " . $this->getAliasModel($ClassChildEditable)
                        );
                    }
                }
            }

            if (count($data->edited) > 0) {
                foreach ($data->edited as $dataChange) {
                    $objEditable->reset(true);
                    if ($objEditable->bind($dataChange)) {
                        if ($objEditable->isNew()) {
                            $this->addBrokenRuler("No se pudo actualizar, no se seteo el ID en: " . $this->getAliasModel($ClassChildEditable));
                        } else {
                            $this->bufferDebugAdd("Hijos. Actualizando");
                            $objEditable->save();
                            if ($objEditable->haveBrokenRules()) {
                                $brokenRulerEditable = '';

                                if ($this->haveBrokenRules()) {
                                    $brokenRulerEditable .= "<br/>";
                                }
                                $brokenRulerEditable .= $objEditable->getBrokenRules();

                                $this->addBrokenRuler($brokenRulerEditable);
                            }
                        }
                    }
                }

                if ($this->haveBrokenRules()) {
                    $this->addBrokenRuler("Editando registro en: " . $this->getAliasModel($ClassChildEditable));
                }
            }

            if (count($data->news) > 0) {
                $fkPadre = $this->_fieldKey;

                foreach ($data->news as &$dataNew) {
                    $objEditable->reset(true);

                    if (isset($dataNew->$fkPadre) && $dataNew->$fkPadre > 0) {
                        if ($dataNew->$fkPadre != $this->getId()) {
                            //	echo "<br/>Objeto ya guardado y no pertenece a clase padre. ID: ". $dataNew->$fkPadre;
                            continue;
                        }

                        if (isset($dataNew->_savedChildOk) && $dataNew->_savedChildOk) {
                            //	echo "<br/>Objeto ya guardado y SI pertenece a clase padre. ID: ". $dataNew->$fkPadre;
                            continue;
                        }
                    }


                    if ($objEditable->bind($dataNew)) {
                        $objEditable->setValueId(0);

                        // echo '<br/>CLASE ACTUAL: '.get_class($this).'<br/>';
                        // echo "<br/>fkPadre: $fkPadre this->getId(): " . $this->getId();
                        if (!$objEditable->isSettedField($fkPadre) || (isset($objEditable->$fkPadre) && (!$objEditable->$fkPadre || $objEditable->$fkPadre <= 0))) {
                            // echo " SETEANDO ID PADRE VALOR: " . $this->getId();
                            $objEditable->$fkPadre = $this->getId();
                        }
                        /*
                          else {
                          echo " NO SE SETEO PADRE.<br/>";

                          print_r($objEditable);
                          }
                         */

                        $this->bufferDebugAdd("Hijos. Insertando. Clave principal: $fkPadre");
                        $objEditable->save();
//						echo "<br/>SE GUARDO " . get_class($objEditable). ' => '. ($objEditable->haveBrokenRules() ? 'CON ERROR':'SIN ERROR') ;

                        if ($objEditable->haveBrokenRules()) {
                            $this->addBrokenRuler($objEditable->getBrokenRules());
                        } else {
                            //	if (isset($dataNew->$fkPadre)) {
                            $dataNew->$fkPadre = $objEditable->$fkPadre;
                            $dataNew->_savedChildOk = true;
                            //	echo "<br/>GUARDADO";
                            //}
                        }
                    } else {
                        $this->addBrokenRuler("No se pudo insertar.<br/>No se pudo bindear en: " . $this->getAliasModel($ClassChildEditable));
                    }
                }

                if ($this->haveBrokenRules()) {
                    $this->addBrokenRuler("Insertando registro en: " . $this->getAliasModel($ClassChildEditable));
                }
            }

            $this->afterProcessChildEditable($ClassChildEditable, $data);
        }

        return true;
    }

    protected function afterProcessChildEditable($ClassEditable, $data){
        return $this;
    }

    /**
     * overwrited. Despues de Guardar
     *
     * @param object $responseData
     * @return bool. si se retorna false y se activa transaccion al guardar se cancelan los datos guardado
     */
    protected function afterSave(&$responseData) {
        return true;
    }

    protected function afterSaveChilds(&$responseData) {
        return true;
    }

    public function to_hash() {
        // return $this->attributes;
        return $this->toObject();
    }

    public function addParentListModelFromName($nameListModel, $hMenu = null) {
        //	echo "<br/>1. nameListModel: $nameListModel";
        $ClassList = ExjUtil::GetNameClassModelListFromName($nameListModel);

        $objList = new $ClassList($hMenu);
        // echo "<br/>2. ok nameListModel: $nameListModel ClassList: $ClassList";
        $objList->setBaseParams($this->getParams());
        //	echo "<br/>Parámetros de la clase: " . get_class($this).'<br/>';
        //	print_r($this->getParams());

        $this->addParentListModel($objList, $nameListModel);
    }

    public function addChildListModelFromName($nameListModel, $hMenu = null) {
        //	echo "<br/>1. nameListModel: $nameListModel";
        $ClassList = ExjUtil::GetNameClassModelListFromName($nameListModel);

        $objList = new $ClassList($hMenu);
        // echo "<br/>2. ok nameListModel: $nameListModel ClassList: $ClassList";
        $objList->setBaseParams($this->getParams());
        //	echo "<br/>Parámetros de la clase: " . get_class($this).'<br/>';
        //	print_r($this->getParams());

        $this->addChildListModel($objList, $nameListModel);
    }

    public function addChildListModel(ExjListModel $listModel, $name, $readData = true) {
        $name = trim($name);
        if (!$name) {
            return;
        }

        if (!$this->_listChildModelsUI) {
            $this->_listChildModelsUI = new stdClass();
        }

        if ($readData) {
            $listModel->readData();
        }

        $this->_listChildModelsUI->$name = $listModel->to_ui();
    }

    public function addParentListModel(ExjListModel $listModel, $name, $readData = true) {
        $name = trim($name);
        if (!$name) {
            return;
        }

        if (!$this->_listParentModelsUI) {
            $this->_listParentModelsUI = new stdClass();
        }

        if ($readData) {
            $listModel->readData();
        }

        $lmUI = $listModel->to_ui();
        /*
          if (isset($lmUI->cfgGrid)) {
          $lmUI->cfgGrid->haveChilds = false;
          }
         */

        $this->_listParentModelsUI->$name = $lmUI;
    }

    public function addChildEditableModelFromName($nameEditableModel, $loadFromId = true) {
        $objEditable = $this->createInstanceChild($nameEditableModel, true);
        $this->addChildEditableModel($objEditable, $nameEditableModel, $loadFromId);
    }

    public function &createInstanceChild($nameEditableModel, $addControlsUI = null) {
        
        $ClassChildEditable = ExjUtil::GetNameClassModelChildEditableFromName($nameEditableModel);
        $objChildEditable = new $ClassChildEditable($addControlsUI, null, $this);

        return $objChildEditable;
    }

    /**
     * Crea una instancia de modelo editable
     *
     * @param string $nameEditableModel
     * @param bool $addControlsUI Defecto null
     * @return ExjEditableModel
     */
    protected function &createInstanceParent($nameEditableModel, $addControlsUI = null) {
        
        $ClassParentEditable = ExjUtil::GetNameClassModelEditableFromName($nameEditableModel);

        if ($addControlsUI === null) {
            $addControlsUI = $this->isAddControlesUI();
        }

        if (!class_exists($ClassParentEditable)) {
            $ClassParentEditable = ExjUtil::GetNameClassModelChildEditableFromName($nameEditableModel);
        }

        if (!class_exists($ClassParentEditable)) {
            // debug_print_backtrace();
            $this->addBrokenRuler("No existe la clase: $ClassParentEditable.<br/>" . $this->getClassStr('Llamado desde el modelo'));
            $objRaw = new ExjEditableModel($addControlsUI, $this->getResponse());
            return $objRaw;
        }

        $objEditable = new $ClassParentEditable($addControlsUI, $this->getResponse());

        return $objEditable;
    }

    public function addChildEditableModel(ExjEditableChildModel $editableChildModel, $name, $loadFromId = true) {
        $name = trim($name);
        if (!$name) {
            return;
        }

        if (!$this->_editableChildModelsUI) {
            $this->_editableChildModelsUI = new stdClass();
        }

        // se envian parametros del padre al hijo
        // $editableChildModel->setParams($this->getParams());

        if ($loadFromId) {
            $editableChildModel->loadFromEditableParent($this);
        }
        if ($editableChildModel->isAddControlesUI()) {
            $editableChildModel->afterLoadRegisterControlsUI();
        }

        $this->_editableChildModelsUI->$name = $editableChildModel->to_ui();
    }

    public function to_ui() {
        $ui = parent::to_ui();

        $ui->fieldKey = $this->_fieldKey;
        $ui->data = $this->getDataSetted();
        if ($this->_listChildModelsUI) {
            $ui->listChildModels = $this->_listChildModelsUI;
            //	echo "ADD listChildModels";
        }

        if ($this->_listParentModelsUI) {
            $ui->listParentModels = $this->_listParentModelsUI;
            //	echo "ADD listParentModels";
        }

        if ($this->_editableChildModelsUI) {
            $ui->editableChildModels = $this->_editableChildModelsUI;
        }

        return $ui;
    }

    /**
     * Retorna un objecto con todos los campos que han sido seteados
     *
     * @return object
     */
    public function getDataSetted() {
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

                /*
                  if ($valueRaw !== $value) {
                  echo " <br/> NORMALIZADO CAMBIO EN: $name VALOR: $value = $valueRaw ";
                  }
                 */
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