<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para clases con soporte de sesión
 *
 */
class ExjClassSession extends ExjSession{
	private $_nameClass='';

	public function __construct() {
        $this->setClass();
        $this->loadFromSession();
    }

    public function getNameClass() {
    	if (!$this->_nameClass) {
    		$this->_nameClass = get_class($this);
    	}

    	return $this->_nameClass;
    }

	/**
	 * Escribe en archivo log en la clase ExjClassSession
	 *
	 * @param mixed $mixed
	 * @param mixed $mixed2
	 */
	public function writeLog($mixed, $mixed2 = ''){
		/*
		static $logClassSesion;
		if (!isset($logClassSesion)) {
			
			$logClassSesion = new ExjHandlerLogData(__CLASS__);
			$logClassSesion->disableVerificateLastTimeToWrite();
		}
		
		$logClassSesion->write($mixed, $mixed2);
		*/
	}
	
	
	/**
	 * Envia el nombre de la clase final
	 *
	 * @param string $nameClass
	 */
	public function setClass($nameClass=''){
		if ($nameClass) {
			$this->_nameClass = $nameClass;
		}
		else {
			$nameClass = $this->getNameClass();
		}
		
		// ExjLog::info("nameClass: $nameClass class: " . get_class($this));
		$this->setPKToSession($nameClass);
		return $this;
	}

	/**
	 * Bindeo del objeto pasado por parámetro a la clase final
	 *
	 * @param object $obj
	 */
	public function bindToSession($obj){
		$this->writeLog(__METHOD__, $this->getNameClass());
		
		$this->copyObjToThis($obj);
		$this->setToSession($obj);
	}
	

	/**
	 * Carga desde sesion las propiedades de la clase final, antes de usar esta función se debe llamar antes a: bindToSession
	 *
	 * @return bool true si se ha setado, false sino
	 */
	public function loadFromSession(){
		$obj = $this->getFromSession();	
		
		return $this->copyObjToThis($obj);
	}
}

?>