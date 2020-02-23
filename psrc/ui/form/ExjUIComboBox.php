<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIComboBox extends ExjUITriggerField {

    const MODE_REMOTE = 'remote';
    const MODE_LOCAL = 'local';
    const TRIGGER_ACTION_QUERY = 'query';
    const TRIGGER_ACTION_ALL = 'all';
    const CSS_DIV_TPL = 'exj-search-item';

    public $allowBlank = true;
    public $autoSelect = true;
    public $forceSelection = false;
    public $mode = 'remote';
    public $valueField;
    public $displayField;
    public $selectOnFocus = false;
    public $emptyText = null;
    public $loadingText;
    public $triggerAction;

    public function __construct($name) {
        parent::__construct($name);
        
        $this->setXType(self::XTYPE_ComboBox);

        $this->valueField = ExjUI::NAME_FIELD_VALUE;
        $this->displayField = ExjUI::NAME_FIELD_TEXT;

        $this->setLoadingText('Cargando...');
        $this->mode = self::MODE_REMOTE;
        $this->setTriggerAction(self::TRIGGER_ACTION_QUERY);

        $this->anchor = '99%';
    }

    public function getValueField(){
        return (isset($this->valueField) ? $this->valueField : '');
    }

    public function getDisplayField(){
        return (isset($this->displayField) ? $this->displayField : '');
    }

    public function setForceSelection($value=true){
        $this->forceSelection = $value;
        return $this;
    }

    /**
     * Parsea a tipo ExjUIComboBox
     *
     * @param ExjUIComponent $componentUI
     * @return ExjUIComboBox
     */
    public static function Cast(ExjUIComponent $componentUI) {
        return $componentUI;
    }

    /**
     * Envia el texto en blanco cuando se presenta por primera vez el combo
     *
     * @param string $emptyText
     * @return ExjUIComboBox
     */
    public function setEmptyText($emptyText) {
        $this->emptyText = ($emptyText ? ExjText::__($emptyText) : $emptyText);

        return $this;
    }

    /**
     * Envia el tiempo de espera antes que la consulta se envie al servidor
     *
     * @param int $queryDelay En milisegundos
     * @return ExjUIComboBox
     */
    public function setQueryDelay($queryDelay) {
        $this->queryDelay = $queryDelay;

        return $this;
    }

    /**
     * Envia el mínimo de caracteres para la consulta al servidor
     *
     * @param int $minChars
     * @return ExjUIComboBox
     */
    public function setMinChars($minChars = 2) {
        $this->minChars = $minChars;

        return $this;
    }

    /**
     * Envia el ancho del combo y ancho de la lista
     *
     * @param int $width
     * @param int $listWidth
     * @return ExjUIComboBox
     */
    public function setWidthListWidth($width, $listWidth = 0) {
        $this->setWidth($width, true);

        if ($listWidth) {
            $this->setListWidth($listWidth);
        }

        return $this;
    }

    public function setStoreJsonPaging($url, $isValueInt = true, $fieldsExtras = null) {
        $this->setStore(ExjUI::NewJsonStorePaging(self::GetFields($isValueInt, $fieldsExtras), $url));
        return $this;
    }

    /**
     * Envia un store json simple
     *
     * @param array $data
     * @param int $isIntValue
     * @param array $fieldsExtras
     * @return ExjUIComboBox
     */
    public function setStoreJsonSimple($data, $isIntValue = true, $fieldsExtras = null) {
        
        return $this->setStore(
            ExjUI::NewJsonStoreSimple(
                self::GetFields($isIntValue, $fieldsExtras),
                $data
            )
        );
    }

    /**
     * Envia store al combo
     *
     * @param ExjDataStore $store
     * @return ExjUIComboBox
     */
    public function setStore(ExjDataStore $store) {
        $this->store = $store;

        return $this;
    }

    /**
     * Retorna store del combo, si está definido sino null
     *
     * @return ExjUIComboBox|null
     */
    public function getStore() {
        return (isset($this->store) ? $this->store : null);
    }

    /**
    * Solo se setea un valor, si solo hay un item en el store
    */
    public function setValueOneItem(){
        $store = $this->getStore();
        if (!$store) {
            return $this;
        }

        $items = $store->getItemsData();
        if (empty($items)) {
            return $this;
        }

        if (count($items) == 1) {
            $firstItem = $items[0];                
            if ($firstItem && isset($firstItem->value)) {
                $this->setValue($firstItem->value);
            }
        }

        return $this;
    }

    public function setValueFirstItem(){
        $store = $this->getStore();
        if ($store) {
            $items = $store->getItemsData();
            
            if (!empty($items)) {
                $firstItem = $items[0];                
                if ($firstItem && isset($firstItem->value)) {
                    $this->setValue($firstItem->value);
                }
            }
        }

        return $this;
    }

    /**
     * Envia en modo remoto
     *
     * @param int $pageSize
     * @param TRIGGER_ACTION_XXX $triggerAction Por defecto all
     * @return ExjUIComboBox
     */
    public function setModeRemote($pageSize = 9, $triggerAction = '') {
        $this->mode = self::MODE_REMOTE;

        $this->pageSize = $pageSize;

        if (!$triggerAction) {
            $triggerAction = self::TRIGGER_ACTION_ALL;
        }
        
        return $this->setTriggerAction($triggerAction);
    }

    public function setTriggerAction($value){
        $this->triggerAction = $value;
        return $this;
    }

    /**
     * Envia en modo local
     *
     * @param string $triggerAction
     * @return ExjUIComboBox
     */
    public function setModeLocal($triggerAction = '') {
        $this->mode = self::MODE_LOCAL;

        if (!$triggerAction) {
            $triggerAction = self::TRIGGER_ACTION_ALL;
        }

        return $this->setTriggerAction($triggerAction);
    }

    public function isModeLocal(){
        return ($this->mode == self::MODE_LOCAL);
    }

    public function isModeRemote(){
        return ($this->mode == self::MODE_REMOTE || $this->mode === null);
    }

    /**
     * Envia ancho de la lista del combo
     *
     * @param int $listWidth
     * @return ExjUIComboBox
     */
    public function setListWidth($listWidth) {
        if ($listWidth) {
            $this->listWidth = $listWidth;
        }

        return $this;
    }

    /**
     * Elimina la propiedad listWidth si está seteado
     *
     * @return ExjUIComboBox
     */
    public function clearListWidth() {
        if (isset($this->listWidth)) {
            unset($this->listWidth);
        }

        return $this;
    }

    /**
     * Envia template e itemSelector del combo
     *
     * @param array $tplContent
     * @param string $cssDiv
     * @return ExjUIComboBox
     */
    public function setTplContentItemSelector($tplContent, $cssDiv = '') {
        if (!$tplContent || count($tplContent) == 0) {
            $tplContent = array();
            $tplContent[] = '<h3><span>{text}</span></h3>';
        }

        if (!$cssDiv) {
            $cssDiv = self::CSS_DIV_TPL;
        }

        $this->itemSelector = "div.$cssDiv";

        $resultTpl = array();
        $resultTpl[] = '<tpl for="."><div class="' . $cssDiv . '">';
        if (is_array($tplContent)) {
            foreach ($tplContent as $tplItem) {
                $resultTpl[] = $tplItem;
            }
        } else {
            $resultTpl[] = $tplContent;
        }
        $resultTpl[] = '</div></tpl>';

        $this->tpl = $resultTpl;

        return $this;
    }

    /**
     * Fija al combo para criteria, se fija el emptyText a - Todo - y se adiciona un item blank al inicio de los items del store
     *
     * @param string $emtyText
     * @return ExjUIComboBox
     */
    public function fixToCriteria($emtyText = '- Todo -') {
        $this->setEmptyText($emtyText);
        $this->addItemBlankToStore();

        return $this;
    }

    public function addItemBlankToStore($forceAdd = false, $textItemDefault = '') {
        if (!isset($this->store) || !isset($this->store->data) || !isset($this->store->fields)) {
            return $this;
        }

        if (!$this->store->data || count($this->store->data) == 0) {
            if (!$forceAdd) {
                return $this;
            }
            $this->store->data = array();
        }

        $newItem = new stdClass();

        /*
          echo "<br/>Item First:<br/>";
          print_r($this->store->data[0]);
          echo "<br/>Campos:<br/>";
          print_r($this->store->fields);
         */
        foreach ($this->store->fields as $f) {
            $nameField = '';
            $typeField = '';

            if (is_object($f) && isset($f->name)) {
                $nameField = $f->name;
                if (isset($f->type)) {
                    $typeField = $f->type;
                } else {
                    $typeField = 'string';
                }
            } elseif (is_string($f)) {
                $nameField = $f;
            }

            if (!$nameField) {
                continue;
            }

            $valueEmpty = null;
            switch ($typeField) {
                case 'double':
                case 'float':
                case 'int':
                    $valueEmpty = 0;
                    break;

                case 'string':
                    $valueEmpty = '';
                    break;
            }

            switch ($nameField) {
                case 'color':
                    $valueEmpty = 'black';
                    break;

                case 'exjDisableSelect':
                    $valueEmpty = 0;
                    break;

                case 'exjDisableMsg':
                    $valueEmpty = '';
                    break;
            }

            $newItem->$nameField = $valueEmpty;
        }

        $fieldValue = (isset($this->valueField) ? $this->valueField : 'value');
        $fieldText = (isset($this->displayField) ? $this->displayField : 'text');

        $newItem->$fieldValue = ''; // importante esto está sincronizado con la UI
        // $newItem->$fieldText = '&nbsp;';
        $newItem->$fieldText = ((isset($this->emptyText) && $this->emptyText) ? $this->emptyText : ($textItemDefault ? $textItemDefault : '- TODO -'));


        /*
          echo "<br/>newItem:<br/>";
          print_r($newItem);
          */

        // inserta un item al inicio del array
        if (is_array($this->store->data)) {
          array_unshift($this->store->data, $newItem);
        }
        elseif (is_object($this->store->data)) {
          if (isset($this->store->data->DataTopics)) {
            array_unshift($this->store->data->DataTopics->topics, $newItem);
            $this->store->data->DataTopics->total += 1;
          }
        }
        
      //  print_r($this->store->data);

        return $this;
    }

    /**
     * Envia a editable el combo
     *
     * @param bool $editable
     * @param bool $ifNoEdiatbleAddItemBlank
     * @return ExjUIComboBox
     */
    public function setEditable($editable = false, $ifNoEdiatbleAddItemBlank = false) {
        $this->editable = ($editable ? true : false);
        if (!$editable && $ifNoEdiatbleAddItemBlank) {
            $this->addItemBlankToStore(false, '- NINGUNO -');
        }

        return $this;
    }

    /**
     * Envia valor por defecto cuando se presente en la UI
     *
     * @param mixed $defaultValue Por lo general de tipo int
     * @return ExjUIComboBox
     */
    public function setDefaultValue($defaultValue) {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    private static function _GetNameField($field){
        $nameField = '';
        if (empty($field)) {
            return $nameField;
        }

        if (is_object($field)) {
            if ($field instanceof ExjField) {
                $nameField = $field->getName();
            }
            elseif ($field instanceof ExjDataField) {
                $nameField = $field->name;
            }
            else{
                ExjObject::PrintBackTrace();
                throw new Exception("ComboBox. fieldsExtras. Clase no soportada: " . get_class($field), 1);
            }
        }
        else{
            $nameField = trim($f);
        }

        return $nameField;
    }

    /**
     * Obtiene los campos del combo
     *
     * @param bool $isValueInt
     * @param array $fieldsExtras
     * @return array
     */
    public static function GetFields($isValueInt = true, $fieldsExtras = null) {
        $fields = array();
        if ($isValueInt) {
            $fields[] = ExjUI::NewFieldInt(ExjUI::NAME_FIELD_VALUE);
        } else {
            $fields[] = ExjUI::NewFieldString(ExjUI::NAME_FIELD_VALUE);
        }

        $fields[] = ExjUI::NewField(ExjUI::NAME_FIELD_TEXT);

        if ($fieldsExtras && count($fieldsExtras) > 0) {
            foreach ($fieldsExtras as $f) {
                $nameFieldToAdd = self::_GetNameField($f);
                if (!$nameFieldToAdd) {
                    continue;
                }

                $isUpdated = false;
                foreach ($fields as &$fTest) {
                    $nameFieldTest = self::_GetNameField($fTest);
                    if (!$nameFieldTest) {
                        continue;
                    }

                    if ($nameFieldTest == $nameFieldToAdd) {
                        $fTest = $f;
                        $isUpdated = true;
                        //	echo "<br>Ya existe campo: $nameFieldToAdd se actualiza Combo: $this->name";
                        break;
                    }
                }

                if (!$isUpdated) {
                    if (is_object($f) && method_exists($f, 'toUI')) {
                        $fields[] = $f->toUI();
                     //   print_r($f->toUI());
                    }
                    else{
                        $fields[] = $f;
                    }
                }
            }
            //	echo "<br>Campos de: $this->name<br>";
            // print_r($fields);
        }

        return $fields;
    }

    public function setAutoSelect($value=true){
        $this->autoSelect = $value;
        return $this;
    }

    public function setSelectOnFocus($value=true){
        $this->selectOnFocus = $value;
        return $this;
    }

    public function setTypeAhead($value=true){
        $this->typeAhead = $value;
        return $this;
    }

    public function setTypeAheadDelay($value){
        $this->typeAheadDelay = $value;
        return $this;
    }

    public function setLoadingText($value){
        $this->loadingText = ExjText::__($value);
        return $this;
    }

    public function setValueValidate($value){
        $store = $this->getStore();
        if (!$store) {
            return $this->setDefaultValue($value);
        }

        $valueField = $this->getValueField();
        if (!$valueField) {
            return $this->setDefaultValue($value);
        }

        $items = $store->getItemsData();
        if (!empty($items)) {
            $foundValue = false;
            foreach ($items as $item) {
                if (!isset($item->$valueField)) {
                    continue;
                }

                if ($item->$valueField == $value) {
                    $foundValue = true;
                    break;
                }
            }

            if ($foundValue) {
                return $this->setValue($value);
            }
        }

        if (!$store->getUrl()) {
            return $this->setDefaultValue($value);
        }

        $urlAddValue = $store->getUrl().'&value='.$value;

     //   echo "<br>Combobox: $this->name no existe value: $value en store";
      //  echo "<br>url: ".$urlAddValue;
        // print_r($items);
        $store->load($urlAddValue);

        $this->setDefaultValue($value);

        return $this;
    }
}
?>