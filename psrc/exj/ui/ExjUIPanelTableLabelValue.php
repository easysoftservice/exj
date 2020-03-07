<?php

defined('_JEXEC') or die('Acceso Restringido');

class ExjUIPanelTableLabelValue extends ExjUIPanel {

	public function __construct() {
        parent::__construct();
        
        $this->setLayout('table')
        	->setDefaults([
				'bodyStyle' => 'padding:3px',
				'border' => false
			])
			->setLayoutConfig([
				'columns' => 2
			]);
    }

    public function addLabelValue($label, $value) {
    	$this->addItem([
				'html' => $label,
				'cellCls' => 'exj-info-gen-highlight'
			])
    		->addItem([
				'html' => $value
			]);
    	return $this;
    }

    public function addRowHtml($value) {
    	$this->addItem([
				'html' => $value,
				'colspan' => 2
			]);
    	return $this;
    }
}

?>