<?php

defined('_JEXEC') or die('Acceso Restringido');

class ExjUIColumn {
	const TYPE_COLUMN = 'gridcolumn'; // Ext.grid.Column (Default)
	const TYPE_BOOLEAN = 'booleancolumn'; // Ext.grid.BooleanColumn
	const TYPE_NUMBER = 'numbercolumn'; // Ext.grid.NumberColumn
	const TYPE_DATE = 'datecolumn'; // Ext.grid.DateColumn
	const TYPE_TEMPLATE = 'templatecolumn'; // Ext.grid.TemplateColumn

	public $dataIndex;

    public function __construct($dataIndex) {
    	$this->setDataIndex($dataIndex);
    }

    public function setDataIndex($value){
    	$this->dataIndex = $value;
    	return $this;
    }

    public function setAlign($value){
    	$this->align = $value;
    	return $this;
    }

    public function setEditable($value=true){
    	$this->editable = $value;
    	return $this;
    }

    public function setFixed($value=true){
    	$this->fixed = $value;
    	return $this;
    }
    

    public function getHeader() {
    	return (isset($this->header) ? $this->header : null);
    }

    public function setHeader($value){
    	$this->header = $value;
    	return $this;
    }

    public function setId($value){
    	$this->id = $value;
    	return $this;
    }

    public function setHidden($value=true){
    	$this->hidden = $value;
    	return $this;
    }

    public function setMenuDisabled($value=true){
    	$this->menuDisabled = $value;
    	return $this;
    }

    public function setResizable($value=true){
    	$this->resizable = $value;
    	return $this;
    }

    public function setSortable($value=true){
    	$this->sortable = $value;
    	return $this;
    }

    public function setTooltip($value){
    	$this->tooltip = $value;
    	return $this;
    }

    public function setWidth($value){
    	$this->width = $value;
    	return $this;
    }

    public function setXtype($value){
    	$this->xtype = $value;
    	return $this;
    }

    public function setTypeDate(){
    	return $this->setXtype(self::TYPE_DATE);
    }

    public function setRenderer($value){
    	$this->renderer = $value;
    	return $this;
    }

    public function setRendererDate($format='m/d/Y'){
    	return $this->setRenderer("Ext.util.Format.dateRenderer('$format')");
    }

    public function toObject(){
    	return (clone $this);
    }

    public function setGetClass($value){
        $this->getClass = $value;
        return $this;
    }

    public function setHandler($value){
        $this->handler = $value;
        return $this;
    }

}
