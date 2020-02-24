<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIComponent {
    const XTYPE_BoxComponent = 'box'; // Ext.BoxComponent
    const XTYPE_Button = 'button'; // Ext.Button
    const XTYPE_Component = 'component'; // Ext.Component
    const XTYPE_Container = 'container'; // Ext.Container
    const XTYPE_DataView = 'dataview'; // Ext.DataView
    const XTYPE_DatePicker = 'datepicker'; // Ext.DatePicker
    const XTYPE_Editor = 'editor'; // Ext.Editor
    const XTYPE_EditorGridPanel = 'editorgrid'; // Ext.grid.EditorGridPanel
    const XTYPE_GridPanel = 'grid'; // Ext.grid.GridPanel
    const XTYPE_ListView = 'listview'; // Ext.ListView
    const XTYPE_Panel = 'panel'; // Ext.Panel
    const XTYPE_TreePanel = 'treepanel'; // Ext.tree.TreePanel
    const XTYPE_Window = 'window'; // Ext.Window
    const XTYPE_TextField = 'textfield'; // Ext.form.TextField
    const XTYPE_HtmlEditor = 'htmleditor'; // Ext.form.HtmlEditor
    const XTYPE_Field = 'field'; // Ext.form.Field
    const XTYPE_TextArea = 'textarea'; // Ext.form.TextArea
    const XTYPE_NumberField = 'numberfield'; // Ext.form.NumberField
    const XTYPE_DateField = 'datefield'; // Ext.form.DateField
    const XTYPE_TriggerField = 'trigger'; // Ext.form.TriggerField
    const XTYPE_RadioGroup = 'radiogroup'; // Ext.form.RadioGroup
    const XTYPE_CheckboxGroup = 'checkboxgroup'; // Ext.form.CheckboxGroup
    const XTYPE_ComboBox = 'combo'; // Ext.form.ComboBox
    const XTYPE_CompositeField = 'compositefield'; // Ext.form.CompositeField
    const XTYPE_TimeField = 'timefield'; // Ext.form.TimeField
    const XTYPE_JsonStore = 'jsonstore'; // Ext.data.JsonStore
    const XTYPE_Toolbar = 'toolbar'; // Ext.Toolbar
    const XTYPE_Hidden = 'hidden'; // Ext.form.Hidden
    const XTYPE_Label = 'label'; // Ext.form.Label

    public $xtype;

    public function __construct($xtype = '') {
        if ($xtype) {
            $this->setXType($xtype);
        }
        else{
            $this->setXType(self::XTYPE_Component);
        }
    }

    public function setXType($type){
        $this->xtype = $type;
        return $this;
    }

    public function setHtml($value){
        $this->html = $value;
        return $this;
    }

    public function setHidden($value=true){
        $this->hidden = $value;
        return $this;
    }

    public function isHidden() {
        return (isset($this->hidden) ? $this->hidden : false);
    }

    public function setProperty($prop, $value){
        $prop = trim($prop);
        if ($prop) {
            $this->$prop = $value;
        }

        return $this;
    }

    public function setDisabled($value=true){
        $this->disabled = $value;
        return $this;
    }

    public function setLabelSeparator($value){
        $this->labelSeparator = $value;
        return $this;
    }

    public function setHttpProxy(ExjHttpProxy $httpProxy){
        $this->_httpProxy = $httpProxy;
        return $this;
    }

    public function setConnAjax(ExjDataConnection $connAjax){
        $this->_connAjax = $connAjax;
        return $this;
    }


    public function setFieldLabel($str){
        $this->fieldLabel = $str;
        return $this;
    }

    public function getFieldLabel(){
        return (isset($this->fieldLabel) ? $this->fieldLabel : null);
    }

    public function isSettedFieldLabel() {
        return (isset($this->fieldLabel) ? true:false);
    }

    /**
     * Setea estilo clave valor
     *
     * @param string $key
     * @param string $value
     * @return ExjUIComponent
     */
    public function setStyleKeyValue($key, $value) {
        $key = trim($key);

        if (!isset($this->style) || !is_object($this->style)) {
            $this->style = new stdClass();
        }

        $this->style->$key = $value;

        return $this;
    }

    /**
     * Envia estilo de color al componente
     *
     * @param string $color
     * @return ExjUIComponent
     */
    public function setStyleColor($color) {
        return $this->setStyleKeyValue('color', $color);
    }

    /**
     * Setea estilo negrilla al control UI
     *
     * @return ExjUIComponent
     */
    public function setStyleFontBold() {
        return $this->setStyleKeyValue('font-weight', 'bold');
    }

    /**
     * Envia una acción a ejecutar en el cliente
     *
     * @param string $exjAction
     * @param bool $applyToItems
     * @return ExjUIComponent
     */
    public function setAction($exjAction, $applyToItems = false) {
        if ($exjAction) {
            $this->exjAction = $exjAction;
        }
        if ($applyToItems === true) {
            $this->applyToItems = true;
        }

        return $this;
    }

    /**
     * Envia autobind al componente al momento que se cargue la UI
     *
     * @param bool $autoBindLoad
     * @param bool $canFireEventSelect
     * @return ExjUIComponent
     */
    public function setAutoBindLoad($autoBindLoad = true, $canFireEventSelect = false) {
        $this->autoBindLoad = $autoBindLoad;

        if ($canFireEventSelect || isset($this->exjCanFireEventSelect)) {
            $this->exjCanFireEventSelect = $canFireEventSelect;
        }

        return $this;
    }

    public function setCls($value){
        $this->cls = $value;
        return $this;
    }

    public function setId($value){
        $this->id = $value;
        return $this;
    }

    public function setParamsCmp($key, $value) {
        if (!isset($this->_paramsCmp) || !$this->_paramsCmp) {
            $this->_paramsCmp = new stdClass();
        }

        $this->_paramsCmp->$key = $value;

        return $this;
    }
}
?>