<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * @class ExjModels
 * Clase base para modelos: Criteria, Editable
 */
class ExjModels extends ExjObject {
    const VALUE_NOSETTER = '__nosetter__';
    const POSTFIXCLASS_CRITERIAMODEL = 'CriteriaModel';
    const POSTFIXCLASS_EDITABLEMODEL = 'EditableModel';
    const POSTFIXCLASS_EDITABLECHILDMODEL = 'EditableChildModel';

    private $_fields, $_brokenRules, $_controlsUI = null;
    protected $_dataChilds = null;
    private $_addControlesUI = false;
    private $_dataModel = null;
    private $_enableAutoLoadDataModel = false;
    private $_params = null;
    private $_childsListModel = null;
    private $_dataToBind = null;
    private $_isForceUnSettedControlsUI = false;
    private $_nameComponentDefault = '';
    private $_isUserAccessReadOnly = null;
    private $_paramsToUI = null;
    private $_fieldKey = '';

    public function __construct($addControlesUI = true) {
        $this->_addControlesUI = $addControlesUI;
        $this->_isUserAccessReadOnly = null;
        $this->_paramsToUI = null;
//		echo "<br/>Ejecutando constructor de " . $this->getClassStr('Clase:');

        $this->initModel();

        $this->registerFields();

        if ($addControlesUI) {
            $this->registerControlsUI();
        }

        $this->registerRules();
        $this->registerChildsNamesEditable();

        if ($this->_enableAutoLoadDataModel) {
            $this->_loadDataModel();
        }
    }

    /**
     * Fija valor para No setear los controles UI
     *
     * @param bool $isForceUnSetted Defecto true
     */
    public function fixForceUnSettedControlsUI($isForceUnSetted = true) {
        $this->_isForceUnSettedControlsUI = $isForceUnSetted;
    }

    /**
     * Fija el nombre del componente para incluir los modelos
     *
     * @param string $nameComponent
     */
    public function fixNameComponentDefault($nameComponent) {
        $this->_nameComponentDefault = trim($nameComponent);
    }

    public function readNameComponent(&$nameComponent) {
        if ($nameComponent) {
            $nameComponent = trim($nameComponent);
        }

        if (!$nameComponent) {
            $nameComponent = $this->_nameComponentDefault;
        }

        return $nameComponent;
    }

    /**
     * overwrited. Inicio del Modelo base
     *
     */
    protected function initModel() {
        
    }

    protected function getAliasModel($nameClass = '') {
        $isThisClass = ($nameClass ? false : true);

        $aliasModel = $nameClass;

        if (!$aliasModel) {
            $aliasModel = get_class($this);
        }

        // AppTrackerMovContainerEditableChildModel
        // str_replace(Exj::GetPrefixClassApp() , '', $aliasModel)

        $strApp = Exj::GetPrefixClassApp();

        $posApp = strpos($aliasModel, $strApp);
        if ($posApp === 0) {
            $aliasModel = substr($aliasModel, strlen($strApp));
        }

        if ($isThisClass) {
            $postfixClass = $this->getNamePostfixClass();
            //	 echo " postfixClass: $postfixClass ";

            if ($postfixClass) {
                $posClass = strrpos($aliasModel, $postfixClass);
                if ($posClass !== false) {
                    $aliasModel = substr($aliasModel, 0, $posClass);
                }
            }
        } else {
            $postsfixClasses = self::GetPostsfixClasses();

            foreach ($postsfixClasses as $postfixClass) {
                $posClass = strrpos($aliasModel, $postfixClass);
                if ($posClass !== false) {
                    $aliasModel = substr($aliasModel, 0, $posClass);
                    break;
                }
            }
        }

        //	echo "<br/>Original aliasModel: $aliasModel";

        $nChars = strlen($aliasModel);

        if ($nChars <= 3) {
            return $aliasModel;
        }

        $posUpper = -1;
        for ($i = $nChars - 1; $i >= 0; $i--) {
            $char = $aliasModel{$i};
            //	echo "<br/>$i char: $char";

            if ($char == strtoupper($char)) {
                $posUpper = $i;
                break;
            }
        }

        if ($posUpper == -1) {
            $aliasModel = ucfirst($aliasModel);
        } elseif ($posUpper == 0) {
            $aliasModel = ucfirst($aliasModel);
        } else {
            $aliasModel = substr($aliasModel, $posUpper);
        }

        return $aliasModel;
    }

    static function GetPostsfixClasses() {
        $postsfixClasses = array();

        $postsfixClasses[] = self::POSTFIXCLASS_CRITERIAMODEL;
        $postsfixClasses[] = self::POSTFIXCLASS_EDITABLECHILDMODEL;
        $postsfixClasses[] = self::POSTFIXCLASS_EDITABLEMODEL;

        return $postsfixClasses;
    }

    public function getNamePostfixClass() {
        $postfixClass = '';
        if ($this instanceof ExjCriteriaModel) {
            $postfixClass = self::POSTFIXCLASS_CRITERIAMODEL;
        } elseif ($this instanceof ExjEditableChildModel) {
            $postfixClass = self::POSTFIXCLASS_EDITABLECHILDMODEL;
        } elseif ($this instanceof ExjEditableModel) {
            $postfixClass = self::POSTFIXCLASS_EDITABLEMODEL;
        }

        if ($postfixClass) {
            return $postfixClass;
        }

        $postsfixClasses = self::GetPostsfixClasses();
        $nameClass = get_class($this);

        foreach ($postsfixClasses as $itemPostfixClass) {
            $posClass = strrpos($nameClass, $itemPostfixClass);
            if ($posClass !== false) {
                $postfixClass = $itemPostfixClass;
                break;
            }
        }

        return $postfixClass;
    }

    private $_wasLoadDataModel = false;

    private function _loadDataModel() {
        //	echo "<br/>Metodo: " . __METHOD__;
        if ($this->_wasLoadDataModel) {
            return true;
        }

        $this->_wasLoadDataModel = false;

        if ($this->loadDataModel($this->_dataModel) !== false && $this->_addControlesUI) {
            $this->_wasLoadDataModel = true;

            $this->bindData($this->_dataModel);
            $this->setValuesControlsUI();
            return true;
        }

        //	echo " No se cargó";

        return false;
    }

    public function enableAutoLoadDataModel($enable = true) {
        $this->_enableAutoLoadDataModel = $enable;
    }

    /**
     * Carga los datos del modelo y los setea a los controles UI
     *
     * @return bool
     */
    public function loadDataUI() {
        return $this->_loadDataModel();
    }

    /**
     * Indica si se adicionaron controles UI al modelo
     *
     * @return bool
     */
    public function isAddControlesUI() {
        return $this->_addControlesUI;
    }

    /**
     * overwrited. Registro de Campos
     *
     */
    protected function registerFields() {
        
    }

    /**
     * overwrited. Registro de controles UI
     *
     */
    protected function registerControlsUI() {
        
    }

    public function autoRegisterControlsUI($fieldsTextArea = null) {
        $fields = $this->getFields();
        foreach ($fields as $field) {
            $name = $field->getName();
            $added = false;
            $valueDefault = null;
            if (isset($this->$name)) {
                if ($this->isSettedField($name)) {
                    $valueDefault = $this->$name;
                }
            }

            switch ($field->type) {
                case ExjField::TYPE_DATE:
                    $this->registerControlUI(ExjUI::NewDateField($name, $field->alias, '', $valueDefault));
                    break;

                case ExjField::TYPE_FLOAT:
                    $this->registerControlUI(ExjUI::NewNumberField($name, $field->alias));
                    break;

                default:
                    if ($fieldsTextArea && count($fieldsTextArea) > 0) {
                        if (in_array($name, $fieldsTextArea)) {
                            $this->registerControlUI(ExjUI::NewTextArea($name, $field->alias));
                            $added = true;
                        }
                    }

                    if (!$added) {
                        $this->registerControlUI(ExjUI::NewTextField($name, $field->alias));
                    }
                    break;
            }
        }
    }

    public function setParams($params) {
        $this->_params = $params;
    }

    public function setParam($param, $value) {
        $param = trim($param);
        if (!$param) {
            return false;
        }

        if (!$this->_params) {
            $this->_params = new stdClass();
        }

        $this->_params->$param = $value;
    }

    public function getParams() {
        return $this->_params;
    }

    public function getParamOrField($nameParam, $valueDefault = '') {
        $value = $this->getParam($nameParam, $valueDefault);
        if ($value) {
            return $value;
        }

        if ($this->isSettedField($nameParam)) {
            return $this->$nameParam;
        }

        return $value;
    }

    /**
     * Obtiene el valor de un parámetro enviado desde la UI
     *
     * @param string $nameParam
     * @param mixed $valueDefault
     * @param bool $strictOnlyParams Defecto true, si es true solo buscará en los parámetros
     * @return mixed
     */
    public function getParam($nameParam, $valueDefault = '', $strictOnlyParams = true) {
        if (!$nameParam) {
            return $valueDefault;
        }

        if ($this->_params) {
            if (is_object($this->_params)) {
                if (isset($this->_params->$nameParam)) {
                    return $this->_params->$nameParam;
                }
            } elseif (is_array($this->_params)) {
                if (isset($this->_params[$nameParam])) {
                    return $this->_params[$nameParam];
                }
            }
        } else {
            // si no hay parametros tomar desde el mismo objeto
            if ($strictOnlyParams) {
                if ($this->isSettedField($nameParam) && isset($this->$nameParam)) {
                    return $this->$nameParam;
                }
            }
        }

        if (!$strictOnlyParams) {
            if ($this->isSettedField($nameParam)) {
                return $this->$nameParam;
            }

            return $this->getParamFromDataToBind($nameParam, $valueDefault);
        }

        return $valueDefault;
    }

    public function getParamInt($nameParam, $valueDefault = 0) {
        $paramInt = $this->getParam($nameParam, $valueDefault);
        if (!$paramInt) {
            $paramInt = 0;
        }

        $paramInt = intval($paramInt);
        return $paramInt;
    }

    /**
     * overwrited. Carga datos del objeto del modelo
     *
     * @param object $dataModel
     * @return bool Retornar false para no cargar datos
     */
    protected function loadDataModel(&$dataModel) {
        return false;
    }

    /**
     * overwrited. Registro de Reglas
     *
     */
    protected function registerRules() {
        
    }

    /**
     * overwrited. Se llama cuando ya se hayan cargado el modelo.
     * Registro de controles UI
     *
     */
    public function afterLoadRegisterControlsUI() {
        /* echo "<br/>Método no sobrecargado: ". __METHOD__.' Clase: '. get_class($this).'<br/>'; */
    }

    /**
     * Carga datos al modelo después de setear parámetros. Esta función es llamada cuando se llama atraves de editableModel desde el controlador
     *
     */
    /*
      public function loadDataModelAfterSetterParams(){

      }
     */

    public function addFieldRegister(ExjField $field){
        $name = $field->getName();

        $this->_fields[$name] = $field;

        // indica q aún no está seteado
        $this->$name = self::VALUE_NOSETTER;
        
        //  echo "<br/>name: $name <br/>";
        //  print_r($field);

        return $this;
    }

    private function &_registerField($name, $type, $alias = '', $isNullable = false, $isRequired = true, $allowZero = true, $nameFieldDB = '', $isComparationEqual=false) {
        
        $field = new ExjField($name, $type, $alias, $isNullable, $isRequired, $allowZero);
        $field->setNameFieldDB($nameFieldDB);
        $field->isComparationEqual = $isComparationEqual;

        $this->addFieldRegister($field);

        return $field;
    }

    /**
     * Devuelve el alias del campo, si no existe ese campo en la clase, se devuelve el mismo nombre
     *
     * @param string $name
     * @return string
     */
    public function getFieldAlias($name) {
        $name = trim($name);
        if (!$name) {
            $this->addBrokenRuler("ERROR GRAVE. No se indicó el nombre al llamar a la función: getFieldAlias");
            return $name;
        }

        if (!$this->_fields) {
            return $name;
        }
        if (count($this->_fields) == 0) {
            return $name;
        }

        if (!isset($this->_fields[$name])) {
            return $name;
        }

        $f = $this->_fields[$name];

        return $f->alias;
    }

    /**
     * Devuelve el valor del campo, si no está setado devuelve el valor por defecto
     *
     * @param string $nameField Si no está definido el campo retorna valor por defecto
     * @param mixed $defaultValue Cualquier tipo, por defecto null
     * @return mixed Valor del campo registrado
     */
    public function getValueField($nameField, $defaultValue = null) {
        if (!isset($this->$nameField)) {
            return $defaultValue;
        }

        if (self::IsSettedValue($this->$nameField)) {
            return $this->$nameField;
        }

        return $defaultValue;
    }

    /**
     * Devuelve el nombre del campo de la DB, según el campo registrado con registerFieldXXX
     *
     * @param string $nameField
     * @return string Si no se definió esta propiedad retorna string vacio
     */
    public function getNameFieldDB($nameField) {
        if (!$nameField) {
            return '';
        }

        $nameField = trim($nameField);
        if (!$nameField) {
            $this->addBrokenRuler("ERROR GRAVE. No se indicó el nombre al llamar a la función: getNameFieldDB");
            return '';
        }

        if (!$this->_fields) {
            return '';
        }
        if (count($this->_fields) == 0) {
            return '';
        }

        if (!isset($this->_fields[$nameField])) {
            return '';
        }

        $f = $this->_fields[$nameField];

        return $f->nameFieldDB;
    }

    /**
     * Adiciona el mapeo de campos de critera y nombre de campos de la db
     *
     * @param string $nameFieldCriteria
     * @param string $fieldDB
     * @return bool true si logró adicionar el mapeo, sino retorna false, generando un error
     */
    public function addMappingNameFieldDB($nameFieldCriteria, $fieldDB) {
        $nameFieldCriteria = trim($nameFieldCriteria);
        $fieldDB = trim($fieldDB);
        if (!isset($this->_fields[$nameFieldCriteria])) {
            $this->addBrokenRuler("Error Add Mapping FieldDB. No está definido el campo criteria: $nameFieldCriteria");
            return false;
        }

        $f = $this->_fields[$nameFieldCriteria];
        $f->setNameFieldDB($fieldDB);

        return true;
    }

    /**
     * Devuelve la última consulta enviada a la db
     *
     * @return string
     */
    public function getLastQuery() {
        $db = Exj::InstanceDatabase();
        return $db->getQuery();
    }

    /**
     * Indica si estan seteados los campos, excepto los pasados por parametro
     *
     * @param mixed $namesFieldsExcept Puede ser string separados por , o array
     * @return bool 
     */
    public function isSettedFields($namesFieldsExcept = '', $exceptFieldEmpties = false) {
        // echo '<h2>'. get_class($this).' -> '. __METHOD__.'</h2>';

        $fieldsSetted = $this->getFields(true);
        if (count($fieldsSetted) == 0) {
            /*
              echo 'RETURN FALSE, NINGUN CAMPO SETEADO<br/>';
              print_r($this->toObject());
              echo "<br/>";
             */
            return false;
        }

        if (!$namesFieldsExcept) {
            return true;
        }

        if (!is_array($namesFieldsExcept)) {
            $namesFieldsExcept = explode(',', $namesFieldsExcept);
        }

        if (count($namesFieldsExcept) == 0) {
            return true;
        }

        $isSetted = false;
        foreach ($fieldsSetted as $field) {
            $nameField = $field->getName();
            foreach ($namesFieldsExcept as $nameFieldExcept) {
                $nameFieldExcept = trim($nameFieldExcept);
                if ($nameField == $nameFieldExcept) {
                    continue;
                }

                if ($exceptFieldEmpties) {
                    if (!isset($this->$nameField)) {
                        continue;
                    }
                    if (!$this->$nameField) {
                        continue;
                    }
                }

                $isSetted = true;
                break;
            }
        }

//		echo 'RETURN ' . ($isSetted ? 'SI':'NO').'<br/>';

        return $isSetted;
    }

    public function isSettedField($name) {
        if (!isset($this->$name)) {
            return true;
        }

        return self::IsSettedValue($this->$name);
    }

    public function isEmptyField($name) {
        if (!$this->isSettedField($name)) {
            return true;
        }

        return ($this->$name ? false : true);
    }

    /**
     * Determina si los campos estas vacios o no
     *
     * @param mixed $names Arreglo de campos o una cadena separada por comas
     * @return bool
     */
    public function isEmptyFields($names) {
        if (!is_array($names)) {
            $names = explode(',', $names);
        }

        $nEmpties = 0;
        foreach ($names as $name) {
            $name = trim($name);
            if (!$name) {
                continue;
            }
            if ($this->isEmptyField($name)) {
                $nEmpties += 1;
            }
        }

        if (count($names) == $nEmpties) {
            return true;
        }

        return false;
    }

    static function IsSettedValue($value) {
        return ($value !== self::VALUE_NOSETTER);
    }

    /**
     * Devuelve un objeto solo con los campos seteados
     *
     * @return object
     */
    public function toObjectOnlySetted() {
        $varsObj = get_object_vars($this->toObject());
        $objSetted = new stdClass();
        foreach ($varsObj as $name => $value) {
            if (!self::IsSettedValue($value)) {
                continue;
            }
            if ($name == 'id' && !$value) {
                continue;
            }

            $objSetted->$name = $value;
        }

        return $objSetted;
    }

    public function resetField($nameField) {
        if (!isset($this->_fields[$nameField])) {
            return false;
        }

        $this->$nameField = self::VALUE_NOSETTER;
        return true;
    }

    /**
     * Registra campo de tipo string
     *
     * @param string $name
     * @param string $alias
     * @param bool $isNullable
     * @param bool $isRequired
     * @return ExjField
     */
    public function registerFieldString($name, $alias = '', $isNullable = false, $isRequired = true, $isComparationEqual=false) {
        return $this->_registerField($name, 'string', $alias, $isNullable, $isRequired, true, '', $isComparationEqual);
    }

    /**
     * Registra campo de tipo string nullable
     *
     * @param string $name
     * @param string $alias
     * @param string $isNullable
     * @param string $isRequired
     * @return ExjField
     */
    public function registerFieldStringNullable($name, $alias = '', $isNullable = true, $isRequired = false, $isComparationEqual=false) {
        return $this->registerFieldString($name, $alias, $isNullable, $isRequired, $isComparationEqual);
    }

    /**
     * Registra campo ID de tipo entero
     *
     * @param string $name Nombre de campo, por lo general el de la db
     * @param string $alias Alias del nombre del campo
     * @param bool $isNullable true si el campo acepta nulos
     * @param bool $isRequired
     * @param bool $allowZero Permitir valor cero, por defecto false
     * @return ExjField
     */
    public function registerFieldId($name, $alias = '', $isNullable = false, $isRequired = true, $allowZero = false, $nameFieldDB = '') {
        return $this->_registerField($name, ExjField::TYPE_INT, $alias, $isNullable, $isRequired, $allowZero, $nameFieldDB);
    }

    public function registerFieldIdNullable($name, $alias = '', $isRequired = false, $allowZero = false, $nameFieldDB = '') {
        return $this->registerFieldId($name, $alias, true, $isRequired, $allowZero, $nameFieldDB);
    }

    public function registerFieldInt($name, $alias = '', $isRequired = true, $allowZero = true, $nameFieldDB = '') {
        return $this->_registerField($name, ExjField::TYPE_INT, $alias, false, $isRequired, $allowZero, $nameFieldDB);
    }

    public function registerFieldIntNullable($name, $alias = '', $isRequired = false, $allowZero = true, $nameFieldDB = '')
    {
        return $this->_registerField($name, ExjField::TYPE_INT, $alias, true, $isRequired, $allowZero, $nameFieldDB);
    }

    public function registerFieldFloat($name, $alias = '', $isNullable = false, $isRequired = true, $allowZero = true) {
        return $this->_registerField($name, ExjField::TYPE_FLOAT, $alias, $isNullable, $isRequired, $allowZero);
    }

    public function registerFieldFloatNullable($name, $alias = '', $isRequired = false, $allowZero = true) {
        return $this->_registerField($name, ExjField::TYPE_FLOAT, $alias, true, $isRequired, $allowZero);
    }

    public function registerFieldDateTime($name, $alias = '', $isNullable = false, $isRequired = true) {
        return $this->_registerField($name, ExjField::TYPE_DATETIME, $alias, $isNullable, $isRequired);
    }

    public function registerFieldDateTimeNullable($name, $alias = '', $isRequired = false) {
        return $this->_registerField($name, ExjField::TYPE_DATETIME, $alias, true, $isRequired);
    }

    public function registerFieldDate($name, $alias = '', $isNullable = false, $isRequired = true, $nameFieldDB = '')
    {
        // echo '<br/>' . __METHOD__. " name: $name alias: $alias";;
        return $this->_registerField($name, ExjField::TYPE_DATE, $alias, $isNullable, $isRequired, true, $nameFieldDB);
    }

    public function registerFieldDateNullable($name, $alias = '', $isRequired = false, $nameFieldDB = '') {
        return $this->_registerField($name, ExjField::TYPE_DATE, $alias, true, $isRequired, true, $nameFieldDB);
    }

    public function registerFieldListModel($name, $alias = '', $isNullable = false, $isRequired = false) {
        return $this->_registerField($name, ExjField::TYPE_ListModel, $alias, $isNullable, $isRequired);
    }

    public function getFields($onlySetter = false) {
        $fields = array();
        if (!$this->_fields) {
            return $fields;
        }
        foreach ($this->_fields as $name => $field) {
            if ($onlySetter) {
                if (!$this->isSettedField($name)) {
                    continue;
                }
            }
            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Setea el parametro value al objeto, si el campo no pertenece a los campos registrados en el modelo, no se setea
     *
     * @param string $nameField
     * @param mixed $value
     * @return bool true si se seteo el campo, sino false
     */
    public function setValueToField($nameField, $value) {
        $nameField = trim($nameField);
        if (!$nameField) {
            return false;
        }

        $found = false;
        foreach ($this->_fields as $name => $field) {
            if ($nameField == $name) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return false;
        }

        $this->$nameField = $value;
        return true;
    }

    public function getFieldsNames() {
        $fieldsNames = array();
        foreach ($this->_fields as $name => $field) {
            $fieldsNames[] = $name;
        }

        return $fieldsNames;
    }

    /**
     * Informa si es válido
     *
     * @return bool true si es válido, sino false
     */
    public function isValid() {
        if ($this->getBrokenRules()) {
            return false;
        }

        return (count($this->_brokenRules) == 0);
    }

    /**
     * Valida si el modelo es válido, si no es válido se envia el error a la clase base
     *
     * @return bool
     */
    public function validate() {
        if ($this->isValid()) {
            return true;
        }

        Exj::SetErrorValidating($this->getBrokenRules());

        return false;
    }

    public function clearBrokenRules() {
        $this->_brokenRules = array();
    }

    /**
     * Informa si hay reglas rotas
     *
     * @return bool
     */
    public function haveBrokenRules($addErrorToBase = false) {
        $brokenRules = $this->getBrokenRules();
        if (!$brokenRules) {
            return false;
        }

        if ($addErrorToBase) {
            global $exj;
            Exj::SetErrorValidating($brokenRules);
        }

        return true;
    }

    /**
     * Adiciona reglas rotas
     *
     * @param string $msg
     * @return ExjModels
     */
    public function addBrokenRuler($msg) {
        if (strlen($msg) > 1) {
            $lastIndex = strlen($msg) - 1;
            $lastChar = $msg{$lastIndex};
            if ($lastChar != '.') {
                $msg .= '.';
            }

            $msg = stripslashes($msg);
        }

        $this->_brokenRules[] = $msg;
        return $this;
    }

    /**
     * Obtiene información del item de los cambios realizados en el registro
     *
     * @param objetc $item
     * @param bool $addEndLine
     * @param string $charEndLine
     * @return string Texto renderizado
     */
    static function GetInfoUsuarioChanged($item, $addEndLine = true, $charEndLine = "<br/>") {
        $infoUserChanged = '';
        if (!$item) {
            return $infoUserChanged;
        }

        $infoUserChanged = array();
        if (isset($item->name_usr)) {
            $infoUserChanged[] = ExjText::__('User') . ': ' . $item->name_usr;
        }
        if (isset($item->modificado_dt)) {
            $infoUserChanged[] = ExjText::__('Changed') . ': ' . ExjDate::ConvertToDateTimeDisplay($item->modificado_dt);
        }

        if (count($infoUserChanged) == 0) {
            return '';
        }

        $infoUserChanged = implode($charEndLine, $infoUserChanged);
        $infoUserChanged = '<h1>' . ExjText::__('Made by: ') . '</h1>' . $infoUserChanged;

        if ($addEndLine) {
            $infoUserChanged = $charEndLine . $charEndLine . $infoUserChanged;
        }

        return $infoUserChanged;
    }

    public function getBrokenRules($separator = '<br/>') {
        if (count($this->_brokenRules) == 0) {
            return '';
        }

        return implode($separator, $this->_brokenRules);
    }

    public function normalizeValue(ExjField $field, $valueRaw, &$value) {
        $value = $field->rendererValue($valueRaw);
        if ($field->haveError()) {
            $this->addBrokenRuler($field->getErrorMsg());
            return false;
        }

        return true;
    }

    /**
     * Renderiza el valor a setear en control UI
     *
     * @param string $nameField
     * @param mixed $value Pasado por referencia
     * @param object $controlUI
     */

    /**
     * Overwrited. Renderiza el valor a setear en control UI
     *
     * @param string $nameField
     * @param mixed $value Pasado por referencia
     * @param object $controlUI Pasado por referencia
     * @return bool si se retorna false no se setea el control UI
     */
    protected function renderValueToUI($nameField, &$value, &$controlUI) {

        return true;
    }

    private function _validateValueToStore($nameField, $value) {
        $controlUI = $this->getControlUI($nameField);
        if (!$controlUI) {
            return false;
        }

        if (!isset($controlUI->store)) {
            return true;
        }

        if (!isset($controlUI->store->data) || !is_array($controlUI->store->data)) {
            return true;
        }

        if (isset($controlUI->valueField)) {
            $valueField = $controlUI->valueField;
        } else {
            $valueField = 'value';
        }

        $itemsCombo = $controlUI->store->data;
        $itemFound = false;
        $isComboYesNo = false;
        if (isset($controlUI->isComboYesNo) && $controlUI->isComboYesNo) {
            $isComboYesNo = true;
        }

        if ($isComboYesNo) {
            if ($value == 0) {
                $value = ExjUI::VALUE_NO;
            } elseif ($value == 1) {
                $value = ExjUI::VALUE_YES;
            }
        }

        //	echo '<br/><b>'.get_class($this). '</b>';
        //	echo "<br/>nameField: $nameField valor: $value isComboYesNo: $isComboYesNo";
        if ($isComboYesNo) {
            $controlUI->$valueField = $value;

            //	print_r($controlUI);
            return true;
        }

        foreach ($itemsCombo as $itemCombo) {
            if (!isset($itemCombo->$valueField)) {
                continue;
            }

            $valueItemCombo = $itemCombo->$valueField;
            if ($valueItemCombo == $value) {
                $itemFound = true;
                break;
            }
        }

        if (!$itemFound) {
            //	echo '<h2>'.get_class($this). '</h2>';
            echo "<br/>No se encuentra value en store. nameField: $nameField valor: $value itemsCombo:<br/>";
            print_r($itemsCombo);

            $newItem = null;
            $fieldsCombo = (isset($controlUI->fields) ? $controlUI->fields : null);
            $this->loadItemComboBox($newItem, $nameField, $value, $fieldsCombo);
            if (!$newItem || !is_object($newItem)) {
                if (!$this->haveBrokenRules()) {
                    $this->addBrokenRuler("ERROR. No se cargó item para combobox. Campo: $nameField<br/>Clase: " . get_class($this));
                }
                //	 print_r($controlUI);
                echo $this->getBrokenRules();
                return false;
            }

            $controlUI->store->data[] = $newItem;
            //	echo "<br/>Adicionado item a combobox nameField: $nameField";
        }

        return true;
    }

    /**
     * Setea el primer parámetro según el resultado
     *
     * @param object $newItem Por referencia
     * @param array $result
     * @param string $setterPropColor Si no se desea que se setee la propiedad color, setear texto vacio
     * @return bool Retorna false si se adicionó una regla rota
     */
    public function setterObjectFromResult(&$newItem, $result, $setterPropColor = 'red') {
        $newItem = null;
        if (!$result) {
            $this->addBrokenRuler("ERROR SETEANDO OBJETO.<br/>Resultado de Consulta no retornó nada.<br/>Clase: " . get_class($this));
            return false;
        }

        if (is_array($result)) {
            if (count($result) == 1) {
                $newItem = $result[0];
                if ($setterPropColor) {
                    $newItem->color = $setterPropColor;
                }

                return true;
            }

            if (count($result) == 0) {
                $this->addBrokenRuler("ERROR SETEANDO OBJETO.<br/>Resultado de Consulta retornó 0.<br/>Clase: " . get_class($this));
            } else {
                $totalItems = count($result);
                $this->addBrokenRuler("ERROR SETEANDO OBJETO.<br/>Resultado de Consulta retornó mas de 1 item. Total Items: $totalItems.<br/>Clase: " . get_class($this));
            }

            return false;
        } elseif (is_object($result)) {
            $newItem = $result;
        } else {
            $this->addBrokenRuler("ERROR SETEANDO OBJETO.<br/>Resultado de Consulta no es array ni objeto.<br/>Clase: " . get_class($this));
            return false;
        }

        return true;
    }

    /**
     * overwrited. Carga un item para combobox
     *
     * @param object $newItem
     * @param string $nameField
     * @param mixed $value Porlo general de tipo int
     * @param array $fieldsCombo
     */
    protected function loadItemComboBox(&$newItem, $nameField, $value, $fieldsCombo = null) {
        $this->addBrokenRuler("<br/>Por implementar Carga de item ComboBox.<br/>Clase: " . get_class($this) . " <br/>Método a Sobrecargar: " . __METHOD__ . "<br/>nameField: $nameField Value: $value");
        return false;
    }

    public function getValueFromControlUI($nameField, $defaultValue = '') {
        $controlUI = $this->getControlUI($nameField);
        if (!$controlUI) {
            return $defaultValue;
        }

        if (isset($controlUI->defaultValue)) {
            return $controlUI->defaultValue;
        }

        //	print_r($controlUI);

        return $defaultValue;
    }

    public function setValueControlUI($nameField, $value) {
        /*
          echo '<h2>'.get_class($this).'</h2>';
          echo __METHOD__. " nameField: $nameField value: $value<br/>";
         */

        $this->_validateValueToStore($nameField, $value);

        if ($this->_isForceUnSettedControlsUI) {
            //	echo "-> No se setea el control UI, porque está forzado para no setearse. <br/>";
            //	print_r($this->getControlUI($nameField));
            return false;
        }

        $controlUI = $this->getControlUI($nameField);
        if (!$controlUI) {
            return false;
        }

        $prop = 'value';

        if ($this->renderValueToUI($nameField, $value, $controlUI) === false) {
            return (!$this->haveBrokenRules());
        }

        if ($controlUI instanceof ExjUIComboBox) {
            //  echo "<br/> - ComboBox $controlUI->name valor: $value";
            if ($controlUI->isModeRemote()) {
                //  echo " -> No se seteo el combo por ser modo remoto<br/>";
                $controlUI->lazyValue = $value; // esta prop es controlada en la UI
                //print_r($controlUI);
                return false;
            } else {
                // echo "<br> $controlUI->name COMBOBOX local value: $value";
                
                // $controlUI->setValue("$value");

                $controlUI->setValueValidate($value);

                // print_r($controlUI);

                return true;
            }
        }

        if (isset($controlUI->cfgStore)) {
            $prop = 'data';
            //	print_r($controlUI);

            $data = new ExjDataResult();
            $data->setItems($value);

            // $controlsUI->setData($data);
            $response = new ExjResponse();
            $response->setDataTopics($value, count($value));

            $controlUI->$prop = $response;

            return true;
        }


        $controlUI->$prop = $value;
        // 	print_r($controlUI);
        // echo "<br>$controlUI->name $prop $value ";

        return true;
    }

    /**
     * Setea a controles UI, los valores seteados del modelo
     *
     * @return int Número de controles UI seteados
     */
    public function setterControlsUI() {
        // echo '<h2>'.get_class($this) . ' Método: '. __METHOD__.'</h2>';
        // debug_print_backtrace();

        $nSeteed = 0;
        if (!$this->isAddControlesUI()) {
            //	echo "<br/>No se permite seteo a controles UI<br/>";
            return $nSeteed;
        }

        $fields = $this->getFields(true);
        if (count($fields) == 0) {
            return $nSeteed;
        }

        foreach ($fields as $field) {
            $nameField = $field->getName();

            if (!isset($this->$nameField)) {
                //		echo "<br/>Campo vacio nameField: $nameField";
                continue;
            }

            $value = $this->$nameField;
            if ($this->setValueControlUI($nameField, $value)) {
                $nSeteed += 1;
            }
        }

        return $nSeteed;
    }

    /**
     * Obtiene control UI
     *
     * @param string $name
     * @return ExjUIComponent
     */
    public function &getControlUI($name) {

        $controlsUI = &$this->_controlsUI;
        if (!$controlsUI) {
            $controlsUI = $this->_controlsUI = array();
        }

        $controlUIEmpty = null;

        if (count($controlsUI) == 0) {
            return $controlUIEmpty;
        }

        if (!isset($controlsUI[$name])) {
            return $controlUIEmpty;
        }

        return $controlsUI[$name];
    }

    public function findControlUI($name) {
        $value = null;
        if (empty($this->_controlsUI) || !$name) {
            return $value;
        }

        $value = (
            isset($this->_controlsUI[$name]) ? $this->_controlsUI[$name] : null
        );

        return $value;
    }

    /**
     * Obtiene Combobox desde controles UI
     *
     * @param string $name
     * @return ExjUIComboBox
     */
    public function &getComboBoxUI($name) {
        return $this->getControlUI($name);
    }

    /**
     * Aplica validación para: Admitir letras y espacios en blanco
     *
     * @param string $name
     * @return bool
     */
    public function applyValidationTextGeneral($name, $textUpper = false, $maxLength = 45, $minLength = 1) {
        return $this->_fixValidation($name, 'textgeneral', $textUpper, $minLength, $maxLength);
    }

    public function applyValidationTextFono($name, $maxLength = 15) {
        return $this->_fixValidation($name, 'textfono', false, null, $maxLength);
    }

    public function applyValidationTextCorreo($name, $maxLength = 80) {
        return $this->_fixValidation($name, ExjField::VALIDATION_email, false, null, $maxLength);
    }

    public function applyValidationTextNameExtendido($name, $textUpper = false, $maxLength = 80, $minLength = 3) {
        return $this->_fixValidation($name, ExjField::VALIDATION_textnameext, $textUpper, $minLength, $maxLength);
    }

    public function applyValidationTextURL($name, $maxLength = 255, $minLength = null) {
        return $this->_fixValidation($name, 'texturl', false, $minLength, $maxLength);
    }

    public function applyValidationTextName($name, $textUpper = false, $maxLength = 80, $minLength = 1) {
        return $this->_fixValidation($name, 'textname', $textUpper, $minLength, $maxLength);
    }

    /**
     * validación q acepta números y el punto
     *
     * @param string $name
     * @param string $maxLength
     * @param string $minLength
     * @return bool
     */
    public function applyValidationTextCodeNums($name, $maxLength = 9, $minLength = 1) {
        return $this->_fixValidation($name, 'textcodenums', false, $minLength, $maxLength);
    }

    public function applyValidationTextCode($name, $textUpper = true, $maxLength = 30, $minLength = 2) {
        return $this->_fixValidation($name, 'textcode', $textUpper, $minLength, $maxLength);
    }

    public function applyValidationNumberInt($name, $maxLength = 6, $minLength = null) {
        return $this->_fixValidation($name, '', false, $minLength, $maxLength);
    }

    public function applyValidationNumberFloat($name, $maxLength = 9, $minLength = null) {
        return $this->_fixValidation($name, '', false, $minLength, $maxLength);
    }

    public function applyValidationTextCodeZip($name, $textUpper = true, $maxLength = 30, $minLength = 2) {
        return $this->_fixValidation($name, 'textzipcode', $textUpper, $minLength, $maxLength);
    }

    public function applyValidationTextCodeSoloLetras($name, $textUpper = true, $maxLength = 3, $minLength = 3) {
        return $this->_fixValidation($name, 'textcodeletters', $textUpper, $minLength, $maxLength);
    }

    public function applyValidationTextMemo($name, $maxLength = 240, $minLength = null, $textUpper = false) {
        return $this->_fixValidation($name, 'textmemo', $textUpper, $minLength, $maxLength);
    }

    public function applyValidationClear($name, $maxLength = 240, $minLength = null) {
        return $this->_fixValidation($name, '', false, $minLength, $maxLength);
    }

    private function _fixValidation($name, $validationType, $textUpper = false, $minLength = null, $maxLength = null) {

        if (!isset($this->_fields[$name])) {
            $this->addBrokenRuler(
                "No está registrada la propiedad: $name para fijar la validación: $validationType"
            );
            return false;
        }

        $field = &$this->_fields[$name];
        $field->setValidationType($validationType)->toUpper($textUpper);

        if ($maxLength !== null) {
            $field->setMaxLength($maxLength);
        }
        if ($minLength !== null) {
            $field->setMinLength($minLength);
        }

        // esto se hace cuando se envia el obj al cliente
        /*
          $controlUI = $this->getControlUI($name);
          if ($controlUI) {
          $controlUI->vtype = $field->validationType;
          if ($field->isToUpper) {
          $controlUI->cls = 'exj-text-upper';
          }
          }
         */

        return true;
    }

    public function applyValidationNumDoc($nameNumDoc, $nameTypeDoc, $prefixId) {
        if (!$this->_fixValidation($nameNumDoc, 'numdoc', true, null, 15)) {
            return false;
        }

        $this->_fixValidation($nameTypeDoc, 'numdoc', true);

        $cmpNumDoc = $this->getControlUI($nameNumDoc);
        $cmpTypeDoc = $this->getControlUI($nameTypeDoc);

        if (!$cmpNumDoc) {
            return true;
        }

        if (!$prefixId) {
            $prefixId = '_';
        }

        $cmpNumDoc->id = $prefixId . '_' . $nameNumDoc;
        if ($cmpTypeDoc) {
            $cmpTypeDoc->id = $prefixId . '_' . $nameTypeDoc;
        }

        $cmpNumDoc->typeDocComp = '';
        if ($cmpTypeDoc) {
            $cmpNumDoc->typeDocComp = $cmpTypeDoc->id;
            $cmpTypeDoc->nroDocComp = $cmpNumDoc->id;
        }

        return true;
    }

    public function applyValidationDateRange($nameFrom, $nameUntil, $prefixId) {
        $dfFrom = $this->getControlUI($nameFrom);
        $dfUntil = $this->getControlUI($nameUntil);

        if ($dfFrom && $dfUntil) {
            if (!$prefixId) {
                $prefixId = '_';
            }

            $dfFrom->id = $prefixId . '_' . $nameFrom;
            $dfUntil->id = $prefixId . '_' . $nameUntil;

            $dfFrom->vtype = 'daterange';
            $dfFrom->endDateField = $dfUntil->id;

            $dfUntil->vtype = 'daterange';
            $dfUntil->startDateField = $dfFrom->id;

            $dfFrom->minText = 'La fecha de este campo debe ser después de {0}';
            $dfUntil->minText = $dfFrom->minText;

            $dfFrom->maxText = 'La fecha de este campo debe ser antes de {0}';
            $dfUntil->maxText = $dfFrom->maxText;
        }

        // aplicar a los campos en la parte del servidor
        $fields = &$this->getFields();
        foreach ($fields as &$field) {
            if ($field->getName() == $nameFrom) {
                $field->endDateField = $nameUntil;
            }
            if ($field->getName() == $nameUntil) {
                $field->startDateField = $nameFrom;
            }
        }

        return true;
    }

    /**
     * Indica si el usuario actual tiene acceso de solo lectura
     *
     * @return bool
     */
    public function isUserAccessReadOnly() {
        if ($this->_isUserAccessReadOnly !== null) {
            return $this->_isUserAccessReadOnly;
        }

        $this->_isUserAccessReadOnly = ExjRequest::GetParam('isReadOnlyAccess', null);

        if ($this->_isUserAccessReadOnly !== null) {
            return $this->_isUserAccessReadOnly;
        }

        $this->_isUserAccessReadOnly = !Exj::IsUserAccessEdit();

        // echo "<br/>Consultando desde " . $this->getClassStr('Clase:') . " isUserAccessReadOnly: " . ($this->_isUserAccessReadOnly ? 'SI' : 'NO');

        return $this->_isUserAccessReadOnly;
    }

    public function registerControlUILabel($nameField, $fieldLabel = '') {
        $controlUI = ExjUI::NewLabelUI();
        $controlUI->name = $nameField;
        $controlUI->setFieldLabel($fieldLabel);

        $this->registerControlUI($controlUI, $nameField);
    }

    public function registerClassEditableModel($ClassModel){
        $instanceModel = new $ClassModel(true);

        return $this->registerEditableModel($instanceModel);
    }

    public function registerEditableModel(ExjModels $model){
        $fields = $model->getFields();
        foreach ($fields as $field) {
            $this->addFieldRegister($field);
        }

        $controlsUI = $model->getControlsUI();
        foreach ($controlsUI as $controlsUI) {
            $this->registerControlUI($controlsUI);
        }

        return $this;
    }

    /**
     * Registra control para la ui.
     *
     * @param ExjUIComponent $controlUI
     * @param string $nameField. No Requerido sino se lo define, se lo calcula desde el $controlUI
     * @return bool
     */
    public function registerControlUI(ExjUIComponent $controlUI, $nameField = '') {
        if (!$controlUI) {
            return false;
        }

        if (!is_object($controlUI)) {
            return false;
        }

        if (!$nameField) {
            if (isset($controlUI->name)) {
                $nameField = $controlUI->name;
            }
        }

        if (!$nameField) {
            return false;
        }

        if (!$controlUI->isSettedFieldLabel() && isset($this->_fields[$nameField])) {
        	if (!isset($controlUI->boxLabel) || !$controlUI->boxLabel) {
	        	$f = $this->_fields[$nameField];
	            if (isset($f->alias)) {
	                $controlUI->setFieldLabel($f->alias);
	            }
        	}
        }

        //	$f = $this->_fields[$nameField];
        //		print_r($f);

        /*
          if (isset($controlUI->fieldLabel)) {
          $controlUI->fieldLabel = ExjText::_($controlUI->fieldLabel);
          }
         */

        if (!$this->_controlsUI) {
            $this->_controlsUI = array();
        }

        if ($this->beforeAddControlUI($controlUI, $nameField) === false) {
            return false;
        }

        $this->_controlsUI[$nameField] = $controlUI;

        $this->afterAddControlUI($controlUI);

        return true;
    }

    protected function afterAddControlUI(ExjUIComponent $controlUI){

    }

    
    public function getControlsUI(){
        $items = array();
        if (empty($this->_controlsUI)) {
            return $items;
        }

        foreach ($this->_controlsUI as $nameField => $controlUI) {
            $items[] = $controlUI;
        }

        return $items;
    }

    /**
     * Antes de adicionar el control UI
     *
     * @param object $controlUI
     * @param string $nameField
     * @return bool false no adiciona el control UI
     */
    protected function beforeAddControlUI(&$controlUI, $nameField) {
        return true;
    }

    public function setValuesControlsUI() {
        if (!$this->haveControlsUI()) {
            return false;
        }

        foreach ($this->_controlsUI as &$ctrlUI) {
            $name = $ctrlUI->name;
            if (!$this->isSettedField($name)) {
                // echo "<br/>No está seteado: $name Metodo: " . __METHOD__;
                continue;
            }
            $value = $this->$name;


            //	echo "<br/><b>name</b>: $name valor: $value <br/>ctrlUI:<br/>";
            // print_r($ctrlUI);


            if (isset($ctrlUI->xtype)) {
                if ($ctrlUI->xtype == 'panel' || $ctrlUI->xtype == 'container') {
                    if (!isset($ctrlUI->items) || !$ctrlUI->items) {
                        $ctrlUI->items = array();
                    }

                    if (isset($ctrlUI->fieldLabel) && $ctrlUI->fieldLabel == $name) {
                        unset($ctrlUI->fieldLabel);
                    }

                    $this->renderItemsUIForContainer($ctrlUI->items, $name, $value);

                    continue;
                }
            }

            $ctrlUI->value = $value;
        }
    }

    /**
     * overwrite. Renderiza los items del objeto UI container o panel
     *
     * @param array $itemsCnt Por referencia, adicionar items UI a este parámetro, por defecto tiene un array vacio
     * @param string $name Nombre del campo
     * @param string $value Valor este valor podria ser de tipo string
     */
    protected function renderItemsUIForContainer(&$itemsCnt, $name, $value) {
        
    }

    public function load($id = null, $noFoundAddError = true) {
        $this->loadDataUI();
        return $this;
    }

    public function haveControlsUI() {
        if (!$this->_controlsUI) {
            return false;
        }

        if (count($this->_controlsUI) == 0) {
            return false;
        }

        return true;
    }

    public function controlsUI_enableKeyEvents($enableKeyEvents = true) {
        if (!$this->haveControlsUI()) {
            return false;
        }

        foreach ($this->_controlsUI as &$controlUI) {
            if (!$controlUI->xtype) {
                continue;
            }

            switch ($controlUI->xtype) {
                case 'textfield':
                case 'numberfield':
                case 'datefield':
                    $controlUI->enableKeyEvents = $enableKeyEvents;
                    break;
            }
        }

        return true;
    }

    /**
     * Indica si existen datos como childs en el modelo
     *
     * @return bool
     */
    public function haveDataChilds() {
        if (!$this->_dataChilds || !is_array($this->_dataChilds)) {
            return false;
        }

        return (count($this->_dataChilds) > 0);
    }

    /**
     * Obtiene los datos o items del modelo o modleos childs
     *
     * @return mixed Si no se han seteado items childs null, sino array
     */
    public function getDataChilds() {
        if ($this->haveDataChilds()) {
            return $this->_dataChilds;
        }

        return null;
    }

    /**
     * Bindeo datos a la clase del modelo
     *
     * @param object $data
     * @param bool $usePostFixNames Defecto false
     * @return int numero de campos de la clase bindeados
     */
    public function bind($data = '', $usePostFixNames = false) {
        $nFieldsBinded = 0;

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

        if (isset($data->_dataChilds)) {
            $this->_dataChilds = $data->_dataChilds;
            $nFieldsBinded += 1;
            /*
              echo "<br/>" .__METHOD__.' _dataChilds:<br/>';
              print_r($this->_dataChilds);
             */
        }

        $this->_dataToBind = $data;

        /*
          echo "<br/>Clase: " . get_class($this) .' Método: ' . __METHOD__.'<br/>';
          echo "this->_postFixNames: $this->_postFixNames usePostFixNames: " . ($usePostFixNames ? 'SI':'NO').'<br/>';
         */

        $vars = get_object_vars($data);
        foreach ($fields as $f) {
            $nfModel = $f->getName();
            if ($usePostFixNames && $this->_postFixNames) {
                $nfModel .= $this->_postFixNames;
            }
            $valueRaw = null;
            $found = false;
            foreach ($vars as $nameField => $valueField) {
                //	echo "<br/>Comparando: $nfModel == $nameField";
                if ($nfModel == $nameField) {
                    $valueRaw = $valueField;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // echo "<br/>NO found: $nfModel";
                continue;
            }

            if ($usePostFixNames && $this->_postFixNames) {
                $nameField = $f->getName();
            }

            $value = null;
            if (!$this->normalizeValue($f, $valueRaw, $value)) {
                continue;
            }

            if ($this->$nameField !== $value) {
                ++$nFieldsBinded;
            }

            // echo "<br/>SETEANDO: nameField: $nameField value: $value";

            $this->$nameField = $value;
        }

        // echo "<br/>RETURN nFieldsBinded: $nFieldsBinded<br/>";

        return $nFieldsBinded;
    }


    public function getDataToBind() {
        return $this->_dataToBind;
    }

    public function getParamFromDataToBind($nameParam, $valueDefault = null) {
        if (!$this->_dataToBind) {
            return $valueDefault;
        }

        if (!is_object($this->_dataToBind)) {
            return $valueDefault;
        }

        if (isset($this->_dataToBind->$nameParam)) {
            return $this->_dataToBind->$nameParam;
        }

        return $valueDefault;
    }

    public function setParamToDataToBind($nameParam, $value) {
        if (!$this->_dataToBind) {
            return false;
        }

        $this->_dataToBind->$nameParam = $value;
        return true;
    }

    public function setFieldKeyToModel($fieldKey) {
        $this->_fieldKey = $fieldKey;
    }

    public function getFieldKeyFromModel() {
        return $this->_fieldKey;
    }

    /**
     * Registro de modelo de lista hija
     *
     * @param string $nameModelList
     * @param string $nameController
     * @param string $fieldKey
     * @param mixed $params
     * @param ExjHelperMenu $hMenu No Requerido
     */
    public function registerChildListModel($nameModelList, $nameController, $fieldKey = '', $params = null, $hMenu = null, $nameEditableModel = '', $canLoadData = true) 
    {
        if (!$fieldKey) {
            $fieldKey = $this->_fieldKey;
            //	echo __METHOD__. " fieldKey: $fieldKey";
        }
        if ($params && is_array($params)) {
            $params = self::ConvertArrayToObject($params);
        }

        $childListModel = new ExjChildListModel($this, $nameModelList, $fieldKey);

        $childListModel->setNameController($nameController)
            ->setParams($params)
            ->setNameEditableModel($nameEditableModel)
            ->setCanLoadData($canLoadData);

        if ($hMenu) {
            $childListModel->setHelperMenu($hMenu);
        }

        if (!$this->_childsListModel) {
            $this->_childsListModel = array();
        }

        //	print_r($childListModel);

        $this->_childsListModel[] = $childListModel;

        return $childListModel;
    }

    public function findChildListModel($nameModel)
    {
        $value = null;
        if (empty($this->_childsListModel)) {
            // echo "<br>findChildListModel. nameModel: $nameModel VACIO";
            return $value;
        }

        foreach ($this->_childsListModel as $childListModel) {
            // echo "<br>childListModel->nameModel: $childListModel->nameModel";
            if ($childListModel->nameModel == $nameModel) {
                $value = $childListModel;
                break;
            }
        }

        return $value;
    }

    private function _getChildsListModelToUI() {
        if (!$this->_childsListModel) {
            return null;
        }

        $childs = array();

        foreach ($this->_childsListModel as $childListModel) {
            $nameModel = $childListModel->nameModel;
            if (!$nameModel) {
                continue;
            }
            
            $nameController = $childListModel->nameController;
            $fieldKey = $childListModel->fieldKey;
            $nameEditableModel = $childListModel->nameEditableModel;
            $canLoadData = $childListModel->canLoadData;

            $xListModel = $childListModel->getInstanceListModel();

            // echo "SE HA CREADO INSTANCIA DE: ClassListModel: $ClassListModel";

            if (!$this->isSettedField($fieldKey)) {
                $this->setValueId(0);
            }
            
            if ($canLoadData) {
                $xListModel->readData();
            }

            $xEditableModel = null;
            if ($nameEditableModel) {
                
                $ClassEditableModel = ExjUtil::GetNameClassModelChildEditableFromName(
                    $nameEditableModel
                );

                if (!class_exists($ClassEditableModel)) {
                    $msgWarning = "No se ha establecido nombre a Clase: $ClassEditableModel. Nombre editable model: $nameEditableModel Clase: " . get_class($this);

                    // $this->addBrokenRuler($msgWarning);
                    echo "<br/>ERROR. $msgWarning";
                    continue;
                }
                $xEditableModel = new $ClassEditableModel(true);
            }

            if ($this->eachChildListModel($xListModel, $xEditableModel, $childListModel) === false)
            {
                continue;
            }


            $child = new stdClass();
            $child->dataIndex = $nameModel;
            $child->nameController = $nameController;
            // $child->nameComponent = $nameComponent;

            $child->listModel = $xListModel->to_ui();
            $child->uiEditable = null;
            if ($xEditableModel) {
                $child->uiEditable = $xEditableModel->to_ui();
            }

         //   print_r($child);

            $childs[] = $child;
        }   

        return $childs;
    }

    /**
     * overwrited. Se ejecuta por cada iteracción de cada lista hija
     *
     * @param ExjListModel $objListModel Pasado por referencia
     * @param ExjEditableModel $objEditableModel Pasado por referencia
     * @param Object $childListModel Solo Lectura
     * @return bool retornar false para parar no adicionarlo como child
     */
    protected function eachChildListModel(&$objListModel, &$objEditableModel, $childListModel) {
        return true;
    }

    private $_postFixNames = '';

    /**
     * Setea un post fijo en el nombre de cada campo, estos nombres son los enviados a la UI
     *
     * @param string $postFixNames Nombre del post fijo a adicionar a los campos
     * @param string $separator Por defecto texto vacio
     */
    public function setPostFixNames($postFixNames, $separator = '') {
        $this->_postFixNames = $separator . $postFixNames;
    }

    public function getParamModel($nameProp) {
        $value = null;
        $items = $this->getParamsModel();
        if (empty($items)) {
            return $value;
        }

        foreach ($items as $item) {
            if ($item->name_prop == $nameProp) {
                $value = $item;
                break;
            }
        }

        return $value;
    }

    public function getParamsModel(){
        $paramsModel = array();

        $items = AppModelsParamsData::GetItemsFromSession();
        if (!$items || empty($items)) {
            return $paramsModel;
        }

        $selfNameClass = get_class($this);
        foreach ($items as $item) {
            if ($item->name_class == $selfNameClass) {
                $paramsModel[] = $item;
            }
        }

        return $paramsModel;
    }

    private function _validateControlsUI(){
        if (empty($this->_controlsUI)) {
            return $this;
        }

        // echo '<br>_validateControlsUI. ' . get_class($this);

        $itemsParams = $this->getParamsModel();

        foreach ($this->_controlsUI as $fieldName => &$controlUI) {
            $this->_validateComboBox($controlUI);

            if (empty($itemsParams)) {
                continue;
            }

            foreach ($itemsParams as $item) {            
                if ($fieldName != $item->name_prop) {
                    continue;
                }

                if ($item->label_prop) {
                    $controlUI->setFieldLabel($item->label_prop);
                }

                if ($item->is_hidden) {
                    $controlUI->setHidden();
                }

                if ($item->value_def_prop && !$controlUI->getValue()) {
                    $controlUI->setValue($item->value_def_prop);
                }                
            }
        }

        return $this;
    }

    private function _validateComboBox($controlUI){
        if (!($controlUI instanceof ExjUIComboBox)) {
            return $this;
        }

        if ($controlUI->getValue() !== null) {
            return $this;
        }

        $controlUI->setValueOneItem();

        return $this;
    }

    public function to_ui_items() {
        $fields = $this->_fields;
        $this->_validateControlsUI();
        $controlsUI = $this->_controlsUI;

        $isReadOnlyAccess = $this->isUserAccessReadOnly();
        $isInstanceEditable = ($this instanceof ExjEditableModel);
        //	echo "<br/>Clase: " . get_class($this) . ' Método: '. __METHOD__. " isReadOnlyAccess: " . ($isReadOnlyAccess ? 'SI':'NO');
        $items = array();
        if (!$fields) {
            return $items;
        }


        foreach ($fields as $name => $field) {
            $item = new stdClass();

            $item->name = $name . $this->_postFixNames;
            $item->type = $field->type;
            $item->isNullable = $field->isNullable;
            $item->isRequired = $field->isRequired;
            $item->fieldLabel = $field->alias;

            $item->control = null;
            if (isset($controlsUI[$name])) {
                $c = new stdClass();
                $c->type = '';
                $c->component = $controlsUI[$name];
                if ($c->component && isset($c->component->name)) {
                    $c->component->name .= $this->_postFixNames;
                }
                if ($c->component && isset($c->component->xtype)) {
                    $c->type = $c->component->xtype;
                }

                $c->component->allowBlank = ($item->isRequired ? false : true);
                if (!$c->component->allowBlank) {

                    if (isset($c->component->fieldLabel)) {
                        $c->component->blankText = $c->component->fieldLabel . " es requerido";
                    } else {
                        $c->component->blankText = "Este campo es requerido";
                    }
                }

                // validaciones
                if ($field->validationType) {
                    if (!isset($c->component->vtype)) {
                        $c->component->vtype = $field->validationType;
                    }
                }

                if ($field->maxLength) {
                    $c->component->maxLength = $field->maxLength;
                    $c->component->maxLengthText = "La máxima longuitud para este campo es: {0}";
                }
                if ($field->minLength) {
                    $c->component->minLength = $field->minLength;
                    $c->component->minLengthText = "La mínima longuitud para este campo es: {0}";
                }




                // config del control
                if ($field->isToUpper) {
                    if (!isset($c->component->cls) || (!$c->component->cls)) {
                        $c->component->cls = 'exj-text-upper';
                    }
                }

                if ($isReadOnlyAccess) {
                    $hidden = (isset($c->component->hidden) ? $c->component->hidden : false);
                    if ($isInstanceEditable) {
                        self::FixComponentUIReadOnly($c->component);
                    }

                    $this->validateControlUIInReadOnly($name, $hidden, $c->component);
                    if ($hidden) {
                        $c->component->hidden = true;
                    }
                } else {
                    $isReadOnly = (isset($c->component->readOnly) ? $c->component->readOnly : false);
                    $isHidden = (isset($c->component->hidden) ? $c->component->hidden : false);
                    
                    $this->validateControlUIInEdit(
                        $name, $c->component, $isReadOnly, $isHidden
                    );
                }

                // xxx

                $item->control = $c;
            }


            $items[] = $item;
        }

        return $items;
    }

    /**
     * Fija a un componente UI que solo sea de solo lectura
     *
     * @param object $componentUI
     * @param bool $readOnly Defecto true
     */
    public static function FixComponentUIReadOnly(&$componentUI, $readOnly = true) {
        if (!$componentUI) {
            return;
        }

        if (!is_object($componentUI)) {
            return;
        }

        $componentUI->readOnly = $readOnly;

        if ($readOnly) {
            if (isset($componentUI->cls) && $componentUI->cls) {
                $componentUI->cls .= ' exj-component-readonly';
            } else {
                $componentUI->cls = 'exj-component-readonly';
            }
        } else {
            if (isset($componentUI->cls) && $componentUI->cls) {
                $componentUI->cls = str_replace('exj-component-readonly', '', $componentUI->cls);
            }
        }
    }

    /**
     * Valida control UI cuando sea el acceso edit, o no sea de solo lectura
     *
     * @param string $name
     * @param object $component Pasado por referencia
     * @param bool $isReadOnly
     * @param bool $isHidden
     */
    protected function validateControlUIInEdit($name, &$component, $isReadOnly, $isHidden) {
        
    }

    /**
     * Valida el control UI cuando sea el acceso solo de lectura
     *
     * @param string $name
     * @param bool $hidden
     * @param object $component
     */
    protected function validateControlUIInReadOnly($name, &$hidden, &$component) {
        
    }

    /**
     * overwrited. Registro de modelos de lista hijos. Esto es llamado desde el controlador
     *
     */
    public function registerChildsListModel() {
        
    }

    /**
     * overwrited. Registro de nombres childs editables
     *
     */
    protected function registerChildsNamesEditable() {
        
    }

    private $_childsNamesEditables = null;

    public function registerChildNameEditable($nameEditable) {
        if (!$this->_childsNamesEditables) {
            $this->_childsNamesEditables = array();
        }

        $this->_childsNamesEditables[] = $nameEditable;
        return $this;
    }

    public function unRegisterChildNameEditable($nameEditable) {
        if (!$this->_childsNamesEditables) {
            return;
        }

        foreach ($this->_childsNamesEditables as $index => $nameRegistered) {
            if ($nameRegistered == $nameEditable) {
                unset($this->_childsNamesEditables[$index]);
            }
        }
    }

    public function haveChildsEditables() {
        if (!$this->_childsNamesEditables) {
            return false;
        }

        return (count($this->_childsNamesEditables) == 0 ? false : true);
    }

    public function getChildsNamesEditables() {
        return $this->_childsNamesEditables;
    }

    /**
     * Envia parámetros a la UI
     *
     * @param string $nameParam
     * @param mixed $valueParam
     */
    public function setParamToUI($nameParam, $valueParam) {
        if ($this->beforeAddParamUI($nameParam, $valueParam) === false) {
            return $this;
        }

        if (!$this->_paramsToUI) {
            $this->_paramsToUI = new stdClass();
        }

        $this->_paramsToUI->$nameParam = $valueParam;
        return $this;
    }

    protected function beforeAddParamUI($nameParam, $valueParam) {
        return true;
    }

    public function setterUserRolsToParamsUI() {
        $this->setParamToUI('isUserRolAccounting', (ExjUser::IsRolContabilidad() ? 1 : 0));
        $this->setParamToUI('isUserRolCustomer', (ExjUser::IsRolCliente() ? 1 : 0));
        $this->setParamToUI('isUserRolSuperAdmin', (ExjUser::IsRolSuperAdmin() ? 1 : 0));
        $this->setParamToUI('isUserRolDispatcher', (ExjUser::IsRolRecaudador() ? 1 : 0));
        $this->setParamToUI('isUserRolAdministrator', (ExjUser::IsRolAdministrador() ? 1 : 0));
    }

    /**
     * Devuelve la UI, esta función se puede sobrescribir, use parent para llamar a la función padre
     *
     * @return object. Estructura: items
     */
    public function to_ui() {
        $ui = new stdClass();

        if (!$this->_childsListModel) {
            $this->registerChildsListModel();
        }

        $ui->items = $this->to_ui_items();
        $ui->childsList = $this->_getChildsListModelToUI();
        if ($this->_paramsToUI) {
            $ui->params = $this->_paramsToUI;
        }

        return $ui;
    }

    public function validateModel(ExjModels $model){
        if ($model->haveBrokenRules()) {
            $this->addBrokenRuler($model->getBrokenRules());
            return false;
        }

        return true;
    }

}

?>