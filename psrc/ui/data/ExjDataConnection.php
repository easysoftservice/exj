<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjDataConnection {
    public $url = '';

    public function __construct($url) {
    	$this->setUrl($url);
    }

    public static function NewGet($url){
    	$instance = new ExjDataConnection($url);
    	$instance->setMethod(ExjDataProxy::METHOD_GET);

    	return $instance;
    }

    public function setUrl($value){
    	$this->url = trim($value);
    	return $this;
    }

    public function setMethod($methodHttp){
    	$this->method = $methodHttp;
    	return $this;
    }

    public function setDisableCaching($disable=true){
    	$this->disableCaching = $disable;
    	return $this;
    }

    public function setAutoAbort($enable=true){
    	$this->autoAbort = $enable;
    	return $this;
    }

    public function setTimeout($valMilliSeconds){
    	$this->timeout = $valMilliSeconds;
    	return $this;
    }

    public function setExtraParams($params){
    	if (is_array($params)) {
    		$params = (object) $params;
    	}

    	$this->extraParams = $params;
    	return $this;
    }

    public function setExtraParam($key, $value){
    	$key = trim($key);
    	if (!$key) {
    		return $this;
    	}

    	if (!isset($this->extraParams) || !$this->extraParams) {
    		$this->extraParams = new stdClass();
    	}

    	$this->extraParams->$key = $value;
    	return $this;
    }
}

?>