<?php

defined('_JEXEC') or die('Acceso Restringido');

class ExjUIPanel extends ExjUIContainer {

	public function __construct() {
        $this->setXType(self::XTYPE_Panel);
    }

    public function setBaseCls($value){
    	$this->baseCls = $value;
    	return $this;
    }

    public function setBodyCssClass($value){
        $this->bodyCssClass = $value;
        return $this;
    }

    public function setBodyBorder($value=true){
        $this->bodyBorder = $value;
        return $this;
    }

    public function setBodyStyle($value){
        $this->bodyStyle = $value;
        return $this;
    }

    
    

    public function setBorder($value=true){
    	$this->border = $value;
    	return $this;
    }

    public function setAutoScroll($value=true){
        $this->autoScroll = $value;
        return $this;
    }

    public function setHeader($value=true){
        $this->header = $value;
        return $this;
    }
    

    public function setButtonAlign($value){
    	$this->buttonAlign = $value;
    	return $this;
    }

    public function setFrame($value=true){
        $this->frame = $value;
        return $this;
    }

    public function setIconCls($value){
        $this->iconCls = $value;
        return $this;
    }

    public function setTitle($value){
        $this->title = $value;
        return $this;
    }
}

?>