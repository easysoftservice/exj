<?php

defined('_JEXEC') or die('Acceso Restringido');

class ExjUITreePanel extends ExjUIPanel {

	public function __construct() {
        $this->setXType(self::XTYPE_TreePanel);
    }

    public function setAnimate($value=true){
    	$this->animate = $value;
    	return $this;
    }

    public function setContainerScroll($value=true){
        $this->containerScroll = $value;
        return $this;
    }

    public function setRootVisible($value=true){
        $this->rootVisible = $value;
        return $this;
    }

    public function setRoot($treeNode){
        $this->root = $treeNode;
        return $this;
    }

    
    public function setEnableDD($value=true){
        $this->enableDD = $value;
        return $this;
    }
    

    public function setLines($value=true){
        $this->lines = $value;
        return $this;
    }

    public function setSingleExpand($value=true){
        $this->singleExpand = $value;
        return $this;
    }

    public function setUseArrows($value=true){
        $this->useArrows = $value;
        return $this;
    }

    public function setDataUrl($value){
        $this->dataUrl = $value;
        return $this;
    }

    
	
}

?>