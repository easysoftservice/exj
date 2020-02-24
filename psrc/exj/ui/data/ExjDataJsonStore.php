<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjDataJsonStore extends ExjDataStore {

    public function __construct() {
    	parent::__construct(ExjUIComponent::XTYPE_JsonStore);
    }

}

?>