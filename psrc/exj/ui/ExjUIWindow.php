<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIWindow extends ExjUIPanel {

	public function __construct() {
        $this->setXType(self::XTYPE_Window);
    }

    public function setClosable($value = true){
    	$this->closable = $value;
    	return $this;
    }

    public function setCloseAction($value){
    	$this->closeAction = $value;
    	return $this;
    }

    public function setMaximizable($value = true){
    	$this->maximizable = $value;
    	return $this;
    }

    public function setMaximized($value = true){
    	$this->maximized = $value;
    	return $this;
    }

    public function setMinimizable($value = true){
    	$this->minimizable = $value;
    	return $this;
    }

    public function setResizable($value = true){
    	$this->resizable = $value;
    	return $this;
    }
}

?>