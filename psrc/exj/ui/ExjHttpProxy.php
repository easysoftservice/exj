<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjHttpProxy extends ExjDataProxy {
	public $conn;

    public function __construct($connection) {
    	if (is_string($connection)) {
    		$this->conn = ExjDataConnection::NewGet($connection);
    	}
    	elseif(is_object($connection)){
    		$this->conn = $connection;
    	}
    	else{
    		throw new Exception("ExjHttpProxy connection debe ser ExjDataConnection o string", 1);
    	}

    	foreach ($this->conn as $key => $value) {
    		if ($value === null) {
    			continue;
    		}

    		$this->$key = $value;
    	}
    }
}

?>