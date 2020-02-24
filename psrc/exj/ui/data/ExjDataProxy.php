<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjDataProxy {
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DESTROY = 'DELETE';

    public $restful = false;


    /*
	POST     /users     create
	GET      /users     read
	PUT      /users/23  update
	DESTROY  /users/23  delete
    */

    public function setApi($api){
    	if (is_array($api)) {
    		$api = (object) $api;
    	}

    	$this->api = $api;
    	return $this;
    }

    public function setApiMethod($method, $value){
    	if (!isset($this->api)) {
    		$this->api = new staClass();
    	}

    	$this->api->$method = $value;
    	return $this;
    }

    public function setApiRead($value){
    	if (is_string($value)) {
    		$value = self::BuildUrl($value);
    	}

    	return $this->setApiMethod('read', $value);
    }

    public function setApiCreate($value){
    	if (is_string($value)) {
    		$value = self::BuildUrl($value, self::METHOD_POST);
    	}

    	return $this->setApiMethod('create', $value);
    }

    public function setApiUpdate($value){
    	if (is_string($value)) {
    		$value = self::BuildUrl($value, self::METHOD_PUT);
    	}

    	return $this->setApiMethod('update', $value);
    }

    public function setApiDestroy($value){
    	if (is_string($value)) {
    		$value = self::BuildUrl($value, self::METHOD_DESTROY);
    	}

    	return $this->setApiMethod('destroy', $value);
    }

    public function setApiLoad($value){
    	if (is_string($value)) {
    		$value = self::BuildUrl($value);
    	}

    	return $this->setApiMethod('load', $value);
    }

    public function setRestful($enable=true){
    	$this->restful = $enable;
    	return $this;
    }

    public static function BuildUrl($url, $methodHttp = self::METHOD_GET){
    	$obj = new stdClass();
    	$obj->url = $url;
    	$obj->method = $methodHttp;

    	return $obj;
    }

}

?>