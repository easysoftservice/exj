<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIHidden extends ExjUIField {

	public function __construct() {
        parent::__construct(self::XTYPE_Hidden);
    }
}

?>