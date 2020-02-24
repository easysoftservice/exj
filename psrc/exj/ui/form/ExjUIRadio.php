<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIRadio extends ExjUICheckbox {

	public function __construct($name) {
        parent::__construct($name);

        $this->setXType('radio');
    }	 
}

?>