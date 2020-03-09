<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Base para la construcción de la UI, según framework Extjs
 *
 */
class ExjUI {

    const VALUE_YES = '1';
    const VALUE_NO = '2';
    const VALUE_NONE = 0;
    const NAME_FIELD_VALUE = 'value';
    const NAME_FIELD_TEXT = 'text';

    /**
     * Formato fecha y hora (Y-m-d H:i:s) de la base de datos
     *
     */
    const FORMAT_DATETIME_SERVER = 'Y-m-d H:i:s';

    /**
     * Formato fecha (Y-m-d) de la base de datos
     *
     */
    const FORMAT_DATE_SERVER = 'Y-m-d';

    /**
     * Crea un objeto JsonStore para uso sin paginación
     *
     * @param string $fields
     * @param array $data
     * @param string $rootData
     * @return ExjDataStore
     */
    public static function NewJsonStoreSimple($fields, $data = array(), $rootData = '') {

        $cfg = new ExjDataJsonStore();
        $cfg->setAutoDestroy()
            ->setData($data, $rootData)
            ->setFields($fields);

        return $cfg;
    }

    /**
     * JsonStore remoto para paginación
     *
     * @param array $fields
     * @param string $url
     * @param string $idProperty
     * @return ExjDataStore
     */
    public static function NewJsonStorePaging($fields, $url, $idProperty = 'value') {
        $cfg = new ExjDataJsonStore();
        $cfg->setAutoDestroy();
        $cfg->setUrl($url)->setFields($fields);

        return $cfg->setterPathProxy()->setIdProperty($idProperty);
    }

    /**
     * Campo para password
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor
     * @return ExjUIField
     */
    public static function NewPasswordField($name, $fieldLabel = '', $anchor = '99%') {

        $cfg = self::NewTextField($name, $fieldLabel, $anchor);
        $cfg->setInputType('password');

        return $cfg;
    }

    /**
     * toolbar
     *
     * @param array $items
     * @param string $cls
     * @return ExjUIComponent
     */
    public static function NewToolbarUI($items, $cls = '') {
        $cfg = new ExjUIToolbar();
        $cfg->setItems($items);

        if ($cls) {
            $cfg->setCls($cls);
        }

        return $cfg;
    }

    /**
     * Label
     *
     * @param string $id
     * @param string $html
     * @param string $text
     * @param string $anchor
     * @return ExjUIComponent
     */
    public static function NewLabelUI($id = null, $html = null, $text = null, $anchor = null) {
        $cfg = new ExjUILabel();
        if ($anchor) {
            $cfg->setAnchor($anchor);
        }

        if ($html !== null) {
            $cfg->setHtml($html);
        }
        if ($text !== null) {
            $cfg->setText($text);
        }
        if ($id !== null) {
            $cfg->setId($id);
        }

        return $cfg;
    }

    /**
     * Nuevo Campo UI
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor Si se indica en px se fija ancho fijo
     * @return ExjUIField
     */
    public static function NewFieldUI($name, $fieldLabel = null, $anchor = '99%') {
        $cfg = new ExjUIField();
        if ($name) {
            $cfg->setName($name);
        }

        if ($fieldLabel !== null) {
            $cfg->setFieldLabel($fieldLabel);
        }
        if ($anchor) {
            $cfg->setAnchor($anchor);
        }

        return $cfg;
    }

    /**
     * TextField
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor Porcentaje del ancho
     * @return ExjUITextField
     */
    public static function NewTextField($name, $fieldLabel = '', $anchor = '99%') {
        $comp = new ExjUITextField($name, $fieldLabel, $anchor);
        return $comp;
    }

    public static function NewTextFieldLabel($name, $anchor = '99%') {
        $comp = self::NewTextField($name, '', $anchor);

        $comp->setDisabled()
            ->setStyleKeyValue('border', 0)
            ->setStyleKeyValue('background', 'none');

        return $comp;
    }

    /**
     * HtmlEditor
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor Porcentaje del ancho
     * @param int $height
     * @return ExjUIField
     */
    public static function NewHtmlEditor($name, $fieldLabel = '', $anchor = '99%', $height = 210) {
        $cfg = new ExjUIHtmlEditor($name, $fieldLabel, $anchor);

        if ($height !== null) {
            $cfg->setHeight($height);
        }

        return $cfg;
    }

    /**
     * Crea objeto compositefield
     *
     * @param string $fieldLabel Si es null no se setea
     * @param mixed $anchor int o string
     * @return ExjUICompositeField
     */
    public static function NewCompositeField($fieldLabel = '', $anchor = '99%') {
        $compositeField = new ExjUICompositeField();

        if ($fieldLabel !== null) {
            $compositeField->fieldLabel = $fieldLabel;
        }

        $compositeField->setAnchor($anchor);

        return $compositeField;
    }

    /**
     * Crea un objeto textfield desactivo (disabled)
     *
     * @param string $name
     * @param string $anchor Por defecto 99%
     * @param string $fieldLabel
     * @return ExjUIField
     */
    static function NewTextFieldDisabled($name, $anchor = '99%', $fieldLabel = '') {
        $cfg = self::NewTextField($name, $fieldLabel, $anchor);
        $cfg->setDisabled();

        return $cfg;
    }

    /**
     * Crea un objeto textfield de solo lectura (readOnly)
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor Por defecto 99%
     * @return object
     */
    static function NewTextFieldReadOnly($name, $fieldLabel = '', $anchor = '99%') {
        $cfg = self::NewTextField($name, $fieldLabel, $anchor);

        return $cfg->setReadOnly()->setCls('exj-item-readonly');
    }

    /**
     * Crea un objeto textarea
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor
     * @param mixed $height por lo general de tipo int
     * @return ExjUIField
     */
    public static function NewTextArea($name, $fieldLabel = '', $anchor = '99%', $height = 'auto') {
        $cfg = new ExjUITextArea($name, $fieldLabel, $anchor);
        $cfg->setHeight($height);

        return $cfg;
    }

    public static function NewFileUploadField($name = 'file_upload', $fieldLabel = 'Archivo', $allowBlank = false, $anchor = '96%') {
        $fieldLabel = ExjText::__($fieldLabel);

        $xtype = 'fileuploadfield';
        $fileUploadField = new ExjUIComponent($xtype);
        $fileUploadField->xtype = $xtype;
        $fileUploadField->name = $name;
        $fileUploadField->fieldLabel = $fieldLabel;
        $fileUploadField->anchor = $anchor;

        $fileUploadField->emptyText = 'Seleccione un archivo click en el icon';
        $fileUploadField->blankText = 'Seleccione el archivo, clic en el siguiente icon';

        $fileUploadField->msgTarget = 'side';
        $fileUploadField->allowBlank = $allowBlank;

        $fileUploadField->buttonCfg = new stdClass();
        $fileUploadField->buttonCfg->text = '';
        $fileUploadField->buttonCfg->iconCls = 'exj-btn-uploadfile';

        return $fileUploadField;
    }

    /**
     * TimeField
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $minValue 
     * @return ExjUIComboBox
     */
    public static function NewTimeField($name, $fieldLabel = '', $minValue = '01:00:00', $maxValue = '23:00:00', $increment = 30) {
        
        $comp = new ExjUITimeField($name);
        $comp->setFormat('H:i:s')
            ->setFieldLabel($fieldLabel)
            ->setWidth(81)
            ->setIncrement($increment)
            ->setMinValue($minValue)
            ->setMaxValue($maxValue);

        return $comp;
    }

    public static function NewNumberFieldInfo($name, $fieldLabel = '', $anchor = '72px'){
       return self::NewNumberField(
            $name,
            $fieldLabel,
            $anchor
        )->setDisabled()
           ->setStyleKeyValue('text-align', 'right')
            ->setStyleKeyValue('font-size', '18px'); 
    }

    /**
     * NumberField
     *
     * @param strin $name
     * @param string $fieldLabel
     * @param string $anchor
     * @param int $isIntValue
     * @param number $valueDefault
     * @return ExjUIField
     */
    public static function NewNumberField($name, $fieldLabel = '', $anchor = '66px', $isIntValue = false, $valueDefault=null) {
        $cfg = new ExjUINumberField($name, $fieldLabel, $anchor);

        $cfg->setAllowNegative(false);

        if ($isIntValue) {
            $cfg->setAllowDecimals(false)->setMinValue(0);

            $cfg->setMinText(ExjText::__("El mínimo valor para este campo es 0"));
        } else {
            $cfg->setAllowDecimals();
        }
        
        if ($valueDefault !== null) {
        	$cfg->setValue($valueDefault);
        }

        // print_r($cfg);

        return $cfg;
    }

    public static function NewNumberFieldForInt($name, $fieldLabel = '', $anchor = '60px', $valueDefault=null)
    {
        return self::NewNumberField($name, $fieldLabel, $anchor, true, $valueDefault);
    }

    public static function NewNumberFieldForIntNegative($name, $fieldLabel = '', $anchor = '99%', $valueDefault=null) {
        $cfgNumber = self::NewNumberField($name, $fieldLabel, $anchor, true, $valueDefault);
        $cfgNumber->allowNegative = true;
        unset($cfgNumber->minValue);
        unset($cfgNumber->minText);

        return $cfgNumber;
    }

    static function NewRootNode($id, $text = '', $nodeType = 'async') {
        $obj = new stdClass();

        $obj->nodeType = $nodeType;
        $obj->text = ExjText::__($text);
        $obj->draggable = false;
        $obj->id = "$id";

        return $obj;
    }

    static function NewTreePanel($name, $rootNode, $dataUrl, $fieldLabel = '', $title = '', $width = '99%', $height = 150, $tevExpandRootNode = true, $rootVisible = false, $enableDD = true) {
        $cfg = new ExjUITreePanel();
        if ($title) {
            $cfg->setTitle($title);
        }

        $cfg->name = $name;
        $cfg->width = $width;
        $cfg->split = true;
        
        $cfg->setUseArrows()
            ->setAnimate()
            ->setRootVisible($rootVisible)
            ->setContainerScroll()
            ->setBorder()
            ->setAutoScroll()
            ->setHeight($height)
            ->setAutoHeight(false)
            ->setEnableDD($enableDD);

        $cfg->tevExpandRootNode = $tevExpandRootNode;
        $cfg->fieldLabel = $fieldLabel;

        return $cfg->setDataUrl($dataUrl)->setRoot($rootNode);
    }

    public static function NewPanel($title = '', $items = array(), $layout = null) {
        $cfg = new ExjUIPanel();

        if ($title) {
            $cfg->setTitle($title);
        }
        
        $cfg->setHeader($title ? true : false);

        $cfg->setItems($items);
        if ($layout === null) {
            if (count($items) == 1) {
                $layout = 'fit';
            } elseif (count($items) > 1) {
                $layout = 'anchor';
            }
        }
        if ($layout) {
            $cfg->setLayout($layout);
        }

        $cfg->setDefaultsKeyValue('msgTarget', 'qtip')
            ->setAutoHeight()
            ->setAutoScroll();

        return $cfg;
    }

    static function NewFormPanel($title = '', $items = array(), $layout = 'form') {
        $cfg = self::NewPanel($title, $items, $layout);

        $cfg->xtype = 'form';
        $cfg->defaultType = ExjUIComponent::XTYPE_TextField;
        $cfg->bodyStyle = 'padding:3px';

        return $cfg;
    }

    static function IsWidthDateMin() {
        $pos = strpos(Exj::GetValueCfg('uiFormatDatetimeDef'), 'y');
        return ($pos !== false);
    }

    private static function _GetOffsetWidthDate($isDateTime = false) {
        $offset = 0;

        if (self::IsWidthDateMin()) {
            $offset = -18;
        }

        return $offset;
    }

    /**
     * DateField
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $anchor
     * @param string $dateDefault
     * @param bool $convertToDate
     * @return ExjUIField
     */
    public static function NewDateField($name, $fieldLabel = '', $anchor = '', $dateDefault = null, $convertToDate = true)
    {
        $cfg = new ExjUIDateField($name, $fieldLabel, $anchor);

        if (!$anchor) {
            $cfg->setWidth(96 + self::_GetOffsetWidthDate());
        }

        $cfg->setFormat(Exj::GetValueCfg('uiFormatDateDef'));

        if ($dateDefault) {
            if ($convertToDate) {
                $cfg->setValue(ExjDate::ConvertToDateDisplay($dateDefault));
            } else {
                $cfg->setValue($dateDefault);
            }
        }

        return $cfg;
    }

    public static function NewDateTimeField($name, $fieldLabel = '', $anchor = '', $dateDefault = null) {
        if ($dateDefault) {
            $dateDefault = ExjDate::ConvertToDateTimeDisplay($dateDefault);
        }

        $cfg = self::NewDateField($name, $fieldLabel, $anchor, $dateDefault, false);

        $formatDatetime = Exj::GetValueCfg('uiFormatDatetimeDef');
        $cfg->setFormat($formatDatetime)
            ->setAltFormats(self::FORMAT_DATETIME_SERVER . '|' . $formatDatetime);

        if (!$anchor) {
            $cfg->setWidth(135 + self::_GetOffsetWidthDate());
        }

        return $cfg;
    }

    /**
     * RadioGroup
     *
     * @param string $name
     * @param string $fieldLabel
     * @param array $items
     * @return ExjUIField
     */
    public static function NewRadioGroup($name, $fieldLabel = '', $items = array()) {
        $radioGroup = new ExjUIRadioGroup();
        $radioGroup->setName($name);

        if ($fieldLabel) {
            $radioGroup->setFieldLabel($fieldLabel);
        }

        return $radioGroup->setItems($items);
    }

    /**
     * Checkbox
     *
     * @param string $name
     * @param string $boxLabel
     * @param bool $checked
     * @param string $fieldLabel
     * @return ExjUIField
     */
    public static function NewCheckbox($name, $boxLabel = null, $checked = false, $fieldLabel = '') {

        $comp = new ExjUICheckbox($name);
        if ($fieldLabel) {
            $comp->setFieldLabel($fieldLabel);
        }
        

        if ($boxLabel !== null) {
            $comp->setBoxLabel($boxLabel);
        }

        if (!$fieldLabel) {
            $comp->setLabelSeparator('');
        }

        $comp->setChecked($checked);

        return $comp;
    }

    public static function NewRadio($name, $boxLabel = null, $checked = false) {
        $comp = new ExjUIRadio($name);
        if ($boxLabel !== null) {
            $comp->setBoxLabel($boxLabel);
        }

        if ($checked) {
            $comp->setChecked();
        }

        return $comp;
    }

    static function NewItemRadio($boxLabel, $name, $inputValue = null, $checked = false) {
        $itemRadio = new stdClass();
        $itemRadio->boxLabel = ExjText::__($boxLabel);
        $itemRadio->name = $name;
        if ($inputValue !== null) {
            $itemRadio->inputValue = $inputValue;
        }
        $itemRadio->checked = $checked;

        return $itemRadio;
    }

    static function NewRadioGroupSiNo($name, $fieldLabel, $checkedSi = true, $valueSi = 1, $valueNo = 0) {
        $items = array();
        $items[] = ExjUI::NewItemRadio('SI', $name, $valueSi, $checkedSi);
        $items[] = ExjUI::NewItemRadio('NO', $name, $valueNo, !$checkedSi);

        return ExjUI::NewRadioGroup($name, $fieldLabel, $items);
    }

    /**
     * Crea un objeto con las propiedades value, text
     *
     * @param mixed $value Valor del item, cualquier tipo
     * @param mixed $text Texto del item, cualquier tipo
     * @param bool|null $toUpperText Por defecto false
     * @return object
     */
    public static function NewItemLookup($value, $text = '', $toUpperText = null) {
        $itemLookup = new stdClass();

        if (!$text) {
            $text = $value;
            if ($toUpperText === null) {
                if (strtoupper($value) === $value) {
                    $toUpperText = true;
                }
            }
        }

        $itemLookup->value = $value;
        $itemLookup->text = (is_numeric($text) ? $text : ExjText::__($text));
        if ($toUpperText) {
            $itemLookup->text = strtoupper($itemLookup->text);
        }

        return $itemLookup;
    }

    /**
     * Adiciona un item para selecciona - Todos -
     *
     * @param array $items
     * @param object $itemTodos Defecto null
     */
    public static function AddItemAllToLookup(&$items, $itemTodos = null) {
        if (!$items || count($items) == 0) {
            return;
        }

        if (!$itemTodos) {
            $itemTodos = new stdClass();
        }

        $itemTodos->value = 0;
        $itemTodos->text = '- Todos -';

        array_unshift($items, $itemTodos);
    }

    /**
     * Crea un objecto combobox con paginación, este combo hace llamada ajax para traer datos del servidor al cliente
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $url
     * @param array $fieldsExtras
     * @param string $tplContent
     * @param string $emptyText
     * @param int $listWidth
     * @param bool $isValueInt
     * @return ExjUIComboBox
     */
    public static function NewComboPaging($name, $fieldLabel, $url, $fieldsExtras = null, $tplContent = null, $emptyText = '- Seleccione -', $listWidth = null, $isValueInt = true) {
        $combo = new ExjUIComboBox($name);
        $combo->setModeRemote(9);

        $combo->setEmptyText($emptyText);
        $combo->selectOnFocus = true;
        $combo->fieldLabel = $fieldLabel;

        $combo->setListWidth($listWidth);

        $combo->setTplContentItemSelector($tplContent);

        $combo->setStoreJsonPaging($url, $isValueInt, $fieldsExtras);

        return $combo;
    }

    /**
     * Nuevo combo para buscar, este es para paginación
     *
     * @param string $name
     * @param string $fieldLabel
     * @param string $url
     * @param array $fieldsExtras
     * @param array $tplContent
     * @param int $listWidth Ancho de la lista
     * @param string $emptyText
     * @param bool $isValueInt Indica si el campo value es entero
     * @return ExjUIComboBox
     */
    public static function NewComboSearch($name, $fieldLabel, $url, $fieldsExtras = null, $tplContent = null, $listWidth = 0, $emptyText = 'Buscar...', $isValueInt = true) {
        
        $combo = self::NewComboPaging(
            $name,
            $fieldLabel,
            $url,
            $fieldsExtras, $tplContent, $emptyText, $listWidth, $isValueInt
        );

        $combo->isComboSearch = true;

        $combo->setTypeAhead(false)
            ->setLoadingText('Buscando...')
            ->setHideTrigger()
            ->setTriggerAction(ExjUIComboBox::TRIGGER_ACTION_QUERY);

        return $combo;
    }

    /**
     * ComboBox simple. 
     *
     * @param string $name Nombre del ComboBox
     * @param string $fieldLabel
     * @param array $data Datos del combo
     * @param array $fieldsExtras Campos extras. No es requerido. Use ExjUI::NewField
     * @param string $emptyText Por defecto: - Seleccione -
     * @return ExjUIComboBox
     */
    public static function NewComboSimple($name, $fieldLabel, $data = null, $fieldsExtras = null, $emptyText = '- Seleccione -', $isIntValue = true)
    {
        $combo = new ExjUIComboBox($name);
        $combo->setModeLocal();

        if ($fieldLabel) {
            /* $combo->fieldLabel = ExjText::__($fieldLabel); */
            $combo->setFieldLabel($fieldLabel);
        }

        $combo->typeAhead = true;
        $combo->setEmptyText($emptyText);
        $combo->selectOnFocus = true;
        $combo->lazyRender = true;
        $combo->setForceSelection();

        $combo->setStoreJsonSimple($data, $isIntValue, $fieldsExtras);

        return $combo;
    }

    public static function NewComboSimpleProxy($name, $fieldLabel, $url, $fieldsExtras=null, $isIntValue=true){
        $combo = new ExjUIComboBox($name);
        $combo->setModeLocal();

        if ($fieldLabel) {
            $combo->setFieldLabel($fieldLabel);
        }

        $combo->typeAhead = true;
        $combo->setEmptyText('- Seleccione -');
        $combo->selectOnFocus = true;
        $combo->lazyRender = true;
        $combo->setForceSelection();
        $combo->setAnchor('99%');

        $combo->setStoreJsonSimple(null, $isIntValue, $fieldsExtras);

        $combo->getStore()
            ->setterPathProxy()
            ->setIdProperty(ExjUI::NAME_FIELD_VALUE)
            ->setUrl($url);

        return $combo;
    }

    /**
     * ComboBox simple para strings
     *
     * @param string $name
     * @param string $fieldLabel
     * @param array $items Array de string u objects ItemLookups
     * @param int $width Defecto 102
     * @param string $emptyText
     * @return ExjUIComboBox
     */
    public static function NewComboSimpleItemsStrings($name, $fieldLabel, $items, $width = 102, $emptyText = '- Seleccione -') {
        if (!empty($items)) {
            foreach ($items as &$item) {
                if ($item && !is_object($item)) {
                    $item = self::NewItemLookup($item);
                }
            }
        }

        $combo = self::NewComboSimple($name, $fieldLabel, $items, null, $emptyText, false);
        $combo->setWidth($width);
        $combo->forceSelection = true;
        $combo->setEditable(false);

        return $combo;
    }

    /**
     * Combo Simple con items Si No Todos
     *
     * @param string $name
     * @param string $fieldLabel
     * @param bool $fixValueYes
     * @return ExjUIComboBox
     */
    public static function NewComboSimpleSiNoTodos($name, $fieldLabel='', $fixValueYes = null) {
        $itemsSiNoAll = array();

        $strAll = '- ' . ExjText::__('Todos') . ' -';

        $itemsSiNoAll[] = ExjUI::NewItemLookup(0, $strAll);
        $itemsSiNoAll[] = ExjUI::NewItemLookup(ExjUI::VALUE_YES, ExjText::__('Si'));
        $itemsSiNoAll[] = ExjUI::NewItemLookup(ExjUI::VALUE_NO, ExjText::__('No'));
        $cmbSiNoAll = ExjUI::NewComboSimple($name, $fieldLabel, $itemsSiNoAll, null, $strAll, false);
        $cmbSiNoAll->forceSelection = true;
        $cmbSiNoAll->setEditable(false);

        $cmbSiNoAll->width = 63;
        if ($fixValueYes !== null) {
            if ($fixValueYes) {
                $cmbSiNoAll->value = ExjUI::VALUE_YES;
            } else {
                $cmbSiNoAll->value = ExjUI::VALUE_NO;
            }
        }

        return $cmbSiNoAll;
    }

    static function NewComboSimpleSiNo($name, $fieldLabel, $fixValueNo = true, $valueYes = null, $valueNo = null) {
        $items = array();

        if ($valueYes === null) {
            $valueYes = ExjUI::VALUE_YES;
        }

        if ($valueNo === null) {
            $valueNo = ExjUI::VALUE_NO;
        }

        $items[] = ExjUI::NewItemLookup($valueYes, ExjText::__('Si'));
        $items[] = ExjUI::NewItemLookup($valueNo, ExjText::__('No'));
        $cmb = ExjUI::NewComboSimple($name, $fieldLabel, $items, null, ExjText::__('Seleccione'), false);
        if ($valueYes == ExjUI::VALUE_YES && $valueNo == ExjUI::VALUE_NO) {
            $cmb->isComboYesNo = true;
        }

        $cmb->forceSelection = true;
        $cmb->setEditable(false);
        $cmb->width = 90;
        if ($fixValueNo) {
            $cmb->value = $valueNo;
        }

        return $cmb;
    }

    /**
     * Crea un objeto input de tipo hidden
     *
     * @param string $name
     * @return ExjUIField
     */
    public static function NewHidden($name) {
        $cmp = new ExjUIHidden();
        return $cmp->setName($name);
    }

    /**
     * Component UI personalizado
     *
     * @param string $name
     * @param ExjModels|object $objUI
     * @return ExjUIComponent
     */
    public static function NewCmpUI($name, $objUI) {
        $cfg = new ExjUIComponent('tevui');

        $cfg->name = $name;

        if ($objUI instanceof ExjModels) {
            $cfg->ui = $objUI->to_ui();
        }
        else{
            $cfg->ui = $objUI;
        }
        
        //	$cfg->cmpHidden = self::NewHidden($name);

        return $cfg;
    }

    /**
     * FieldDate
     *
     * @param string $name
     * @param string $dateFormat
     * @return ExjUIField
     */
    public static function NewFieldDate($name, $dateFormat = 'Y-m-d') {
        $cfg = self::NewField($name, 'date');
        if ($dateFormat) {
            $cfg->dateFormat = $dateFormat;
        }


        return $cfg;
    }

    /**
     * FieldDateTime
     *
     * @param string $name
     * @param string $dateFormat
     * @return ExjUIField
     */
    public static function NewFieldDateTime($name, $dateFormat = 'Y-m-d H:i:s') {
        $cfg = self::NewField($name, 'datetime');
        if ($dateFormat) {
            $cfg->dateFormat = $dateFormat;
        }

        return $cfg;
    }

    /**
     * Campo tipo int
     *
     * @param string $name
     * @param bool $useNull
     * @return ExjDataField
     */
    public static function NewFieldInt($name, $useNull = null) {
        return self::NewField($name, ExjDataField::TYPE_INT, $useNull);
    }

    /**
     * Campo tipo float
     *
     * @param string $name
     * @param bool $useNull
     * @return ExjDataField
     */
    public static function NewFieldFloat($name, $useNull = null) {
        return self::NewField($name, ExjDataField::TYPE_FLOAT, $useNull);
    }

    /**
     * Campo tipo string
     *
     * @param string $name
     * @param bool $useNull
     * @return ExjDataField
     */
    static function NewFieldString($name, $useNull = null) {
        return self::NewField($name, ExjDataField::TYPE_STRING, $useNull);
    }

    /**
     * Campo del mismo formato del servidor
     *
     * @param string $name
     * @param bool $useNull
     * @return ExjDataField
     */
    static function NewFieldRaw($name, $useNull = true) {
        return self::NewField($name, '', $useNull);
    }

    /**
     * Field para Data
     *
     * @param string $name
     * @param string $type
     * @param bool $useNull
     * @return ExjDataField
     */
    public static function NewField($name, $type = '', $useNull = null) {
        $f = new ExjDataField($name, $type);

        if ($useNull !== null) {
            $f->useNull = $useNull;
        }


        return $f;
    }

    public static function AddFieldsExtrasDisableSelect(&$fieldExtras) {
        $fieldExtras[] = ExjUI::NewFieldInt('exjDisableSelect');
        $fieldExtras[] = ExjUI::NewFieldString('exjDisableMsg');
    }

    /**
     * Botón
     *
     * @param string $text
     * @param string $tooltip
     * @param string $iconCls
     * @param string $exjAction
     * @return ExjUIButton
     */
    public static function NewButton($text, $tooltip = '', $iconCls = '', $exjAction = '') {
        $button = new ExjUIButton($text, $tooltip);
        $button->setIconCls($iconCls);
        $button->setAction($exjAction);

        return $button;
    }

    /**
     * Botón Nuevo
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    public static function NewButtonAdd($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Nuevo';
        }
        return self::NewButton($text, $tooltip, 'exj-btn-new', 'add');
    }

    /**
     * Botón Historial de Cambios
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    public static function NewButtonViewLogPersistence($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Historial de Cambios...';
        }
        return self::NewButton($text, $tooltip, 'exj-btn-view', 'viewlogpers');
    }

    /**
     * Botón Eliminar
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    static function NewButtonDelete($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Eliminar...';
        }
        return self::NewButton($text, $tooltip, 'exj-btn-delete', 'del');
    }

    /**
     * Botón Editar
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    static function NewButtonEdit($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Editar';
        }

        return self::NewButton($text, $tooltip, 'exj-btn-edit', 'edit');
    }

    /**
     * Botón Imprimir...
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    static function NewButtonPrint($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Imprimir...';
        }
        return self::NewButton($text, $tooltip, 'exj-btn-printer', 'print');
    }

    /**
     * Botón Ayuda
     *
     * @param string $tooltip
     * @param string $text
     * @return ExjUIButton
     */
    static function NewButtonHelp($tooltip, $text = '') {
        return self::NewButton($text, $tooltip, 'exj-btn-help', 'hlp');
    }

    /**
     * Botón Ver...
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    static function NewButtonView($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Ver...';
        }

        return self::NewButton($text, $tooltip, 'exj-btn-view', 'view');
    }

    /**
     * Crea un objeto botón con menú
     *
     * @param string $text
     * @param string $iconCls
     * @param array $itemsMenu
     * @param string $tooltip
     * @return ExjUIButton
     */
    static function NewBotonMenu($text, $iconCls, $itemsMenu, $tooltip = '') {
        $btn = self::NewButton($text, $tooltip, $iconCls);

        $btn->menu = $itemsMenu;

        return $btn;
    }

    public static function NewMenuItemPrint() {
        $cfg = self::NewMenuItem('Imprimir', 'exj-btn-printer')->setAction('print');

        return $cfg;
    }

    /**
     * Item de menú
     *
     * @param string $text
     * @param string $iconCls
     * @return ExjUIComponent
     */
    public static function NewMenuItem($text, $iconCls = '') {
        $cfg = new ExjUIComponent('menuitem');

        if ($iconCls === null) {
            $iconCls = '';
        }

        $cfg->text = ExjText::__($text);
        $cfg->iconCls = $iconCls;

        return $cfg;
    }

    /**
     * Item tipo ckeck para menus
     *
     * @param string $text
     * @param string $iconCls
     * @param bool $checked
     * @param string $itemCls
     * @return ExjUIComponent
     */
    public static function NewMenuCheckItem($text, $iconCls = '', $checked = false, $itemCls = '') {
        $cfg = self::NewMenuItem($text, $iconCls);

        $cfg->xtype = 'menucheckitem';
        $cfg->checked = $checked;
        if ($itemCls) {
            $cfg->itemCls = $itemCls;
        }

        return $cfg;
    }

    static function NewMenuItemHTML() {
        $cfg = self::NewMenuItem('Imprimir', 'exj-btn-printer')->setAction('rep_html');
        $cfg->format = ExjImportModel::REPORT_FORMAT_HTML;

        return $cfg;
    }

    static function NewMenuItemXML($name = 'XML') {
        if (!$name) {
            $name = 'XML';
        }

        $cfg = self::NewMenuItem($name, 'exj-btn-xml')->setAction('rep_xml');
        $cfg->format = ExjImportModel::REPORT_FORMAT_XML;

        return $cfg;
    }

    static function NewMenuItemPDF() {
        $cfg = self::NewMenuItem('PDF', 'exj-btn-pdf')->setAction('rep_pdf'); // rep_pdf
        $cfg->format = ExjImportModel::REPORT_FORMAT_PDF;

        return $cfg;
    }

    static function NewMenuItemExcelXLS() {
        $cfg = self::NewMenuItem('Excel 95 (.xls)', 'exj-btn-excel_xls')->setAction('rep_excelxls');
        $cfg->format = ExjImportModel::REPORT_FORMAT_EXCELXLS;

        return $cfg;
    }

    static function NewMenuItemExcelXLSX() {
        $cfg = self::NewMenuItem('Excel 97/2000/XP (.xlsx)', 'exj-btn-excel_xlsx')->setAction('rep_excelxlsx');
        $cfg->format = ExjImportModel::REPORT_FORMAT_EXCELXLSX;

        return $cfg;
    }

    static function NewMenuItemLinkPDF($href = '', $hrefTarget = '_parent') {
        $cfg = self::NewMenuItemLink('PDF', 'exj-btn-pdf', $href, $hrefTarget)->setAction('rep_pdf');

        return $cfg;
    }

    static function NewMenuItemLinkExcelXLS($href = '', $hrefTarget = '_parent') {
        $cfg = self::NewMenuItemLink('Excel xls', 'exj-btn-excel_xls', $href, $hrefTarget)->setAction('rep_excelxls');

        return $cfg;
    }

    static function NewMenuItemLinkExcelXLSX($href = '', $hrefTarget = '_parent') {
        $cfg = self::NewMenuItemLink('Excel xlsx', 'exj-btn-excel_xlsx', $href, $hrefTarget)->setAction('rep_excelxlsx');

        return $cfg;
    }

    /**
     * Item como link o href para menus
     *
     * @param string $text
     * @param string $iconCls
     * @param string $href
     * @param string $hrefTarget
     * @return ExjUIComponent
     */
    public static function NewMenuItemLink($text, $iconCls = '', $href = '', $hrefTarget = '_parent') {
        $cfg = self::NewMenuItem($text, $iconCls);
        $cfg->hrefTarget = $hrefTarget;
        $cfg->href = $href;

        return $cfg;
    }

    /**
     * Botón Guardar
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    public static function NewButtonSave($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Guardar';
        }

        return self::NewButton($text, $tooltip, 'exj-btn-save', 'save');
    }

    /**
     * Botón Cancelar
     *
     * @param string $text
     * @param string $tooltip
     * @return ExjUIButton
     */
    public static function NewButtonCancel($text = '', $tooltip = '') {
        if (!$text) {
            $text = 'Cancelar';
        }
        return self::NewButton($text, $tooltip, 'exj-btn-cancel', 'cancel');
    }
}

?>