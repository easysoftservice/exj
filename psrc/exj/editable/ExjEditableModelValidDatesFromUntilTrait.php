<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

// no usado
trait ExjEditableModelValidDatesFromUntilTrait {
	// protected $useValidDatesFromUntil = true;

	public $valid_from_date;
	public $valid_until_date;

	protected function registerValidDatesFromUntil($addControlesUI){
        $this->registerFieldDate('valid_from_date', 'Vigente desde');
        $this->registerFieldDateNullable('valid_until_date', 'Vigente hasta');

        if ($addControlesUI) {
            $this->registerControlUI(ExjUI::NewDateField('valid_from_date'));
            $this->registerControlUI(ExjUI::NewDateField('valid_until_date'));
        }

        return $this;
    }
    
}

?>