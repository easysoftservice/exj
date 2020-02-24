<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para leer las respuestas que vienen del servidor, por medio del controlador usa una instancia de esta clase para leer los datos que vienen del cliente.
 *
 */
class ExjRequest {
    public $method, $controller, $action, $id=null, $model, $fail;
    public $callback;
    // parametros
    public $paramData=null, $paramDataChanged=null, $params=null, $paramCriteria=null;
    public $paramValuesCriteria = null;
    
    private $_restful;

    public function __construct($restful = false) {
        $this->setRestFul($restful);
        $this->method = (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"]:'');
        $this->model = '';
        $this->callback = '';
        $this->id = null;
        
        $this->paramDataChanged = null;
        $this->paramCriteria = null;
        $this->paramValuesCriteria = null;
        $this->params = null;

        $this->parseRequest();
    }
    public function isRestful() {
    	$isRestFulParam = self::GetParam('isRestFul', null);
    	if ($isRestFulParam !== null) {
    		return $isRestFulParam;
    	}
    	
        return $this->_restful;
    }
    
    public function setRestFul($restful){
    	$this->_restful = $restful;
    }
    
    
    protected function parseRequest() {
        if ($this->method == 'PUT') {   // <-- Have to jump through hoops to get PUT data
            $raw  = '';
            $httpContent = fopen('php://input', 'r');
            while ($kb = fread($httpContent, 1024)) {
                $raw .= $kb;
            }
            fclose($httpContent);
            
            $params = array();
            parse_str($raw, $params);
            
			$this->fail='';
			if(isset($params['fail'])){
				$this->fail = $params['fail'];
			}
            
            if (isset($params['dataChanged'])) {
                // $this->paramDataChanged = Exj::JsonDecodeSlashes(stripslashes($params['dataChanged']));
                $this->paramDataChanged = Exj::JsonDecodeSlashes(Exj::StripslashesWithEndLine($params['dataChanged']));
            } else {
                $paramsTest = Exj::JsonDecodeSlashes(Exj::StripslashesWithEndLine($raw));
                if ($paramsTest) {
                	$params = $paramsTest;
                	$this->paramDataChanged = $params->dataChanged;
                }
            }

            if (isset($params['data'])) {
                $this->paramData = Exj::JsonDecodeSlashes(Exj::StripslashesWithEndLine($params['data']));
            } else {
                $paramsTest = Exj::JsonDecodeSlashes(Exj::StripslashesWithEndLine($raw));
                if ($paramsTest) {
                	$params = $paramsTest;
                	$this->paramData = $params->data;
                }
            }
            
            if (isset($params['criteria'])) {
                $this->paramCriteria = Exj::JsonDecodeSlashes(stripslashes($params['criteria']));
            } else {
                $paramsTest = Exj::JsonDecodeSlashes(stripslashes($raw));
                
				$this->paramCriteria = null;
				if ($paramsTest) {
					$params = $paramsTest;
					if(isset($params->criteria)){
						$this->paramCriteria = $params->criteria;
					}
				}
            }
            
        	$this->params = $params;
        } else {
            // grab JSON data if there...
            $this->params = $_REQUEST;
            
            if ($this->params) {
				$this->fail = '';
				if(isset($this->params['fail'])){
					$this->fail = $this->params['fail'];
				}
                
                if (isset($this->params['dataChanged'])) {
                	$this->paramDataChanged = Exj::JsonDecodeSlashes(Exj::StripslashesWithEndLine($this->params['dataChanged']));
                }
                if (isset($this->params['data'])) {
                	$this->paramData = Exj::JsonDecodeSlashes(Exj::StripslashesWithEndLine($this->params['data']));
                }
                
                if (isset($this->params['criteria'])) {
                	$this->paramCriteria = Exj::JsonDecodeSlashes(stripslashes($this->params['criteria']));
                }
                
                if (isset($this->params['valuesCriteria'])) {
                	$this->paramValuesCriteria = Exj::JsonDecodeSlashes(stripslashes($this->params['valuesCriteria']));
                }
            } else {
                $raw  = '';
                $httpContent = fopen('php://input', 'r');
                while ($kb = fread($httpContent, 1024)) {
                    $raw .= $kb;
                }
                fclose($httpContent);
                
                $this->params = Exj::JsonDecodeSlashes(stripslashes($raw));
                if (isset($params->dataChanged)) {
                	$this->paramDataChanged = $params->dataChanged;	
                }
                if (isset($params->data)) {
                	$this->paramData = $params->data;
                }
                if (isset($params->criteria)) {
                	$this->paramCriteria = $params->criteria;
                }
                
                if (isset($params->valuesCriteria)) {
                	$this->paramValuesCriteria = $params->valuesCriteria;
                }
                
                if($this->method == 'DELETE'){
                	$this->fail = (strpos($raw, 'fail=') !== false);
                } else {
                	if (isset($params->fail)) {
                		$this->fail = $params->fail;
                	}
                }
            }
        }
        
        // Quickndirty PATH_INFO parser
        // var_dump($_SERVER["PATH_INFO"]);
        if (isset($_SERVER["PATH_INFO"])){
        	$pathInfo = $_SERVER["PATH_INFO"];
            // $cai = '/^\/([a-z]+\w)\/([a-z]+\w)\/([0-9]+)$/';  // /controller/action/id
            // $cai = '/^\/([a-z_]+\w)\/([a-zA-Z]+\w)\/([0-9]+)$/';  // /controller/action/id
            $cai = '/^\/([a-z_A-Z]+\w)\/([a-zA-Z]+\w)\/([0-9-]+)$/';  // /controller/action/id
            // $ca =  '/^\/([a-z]+\w)\/([a-z]+)$/';              // /controller/action
            $ca =  '/^\/([a-z_A-Z]+\w)\/([a-zA-Z]+)$/';              // /controller/action
            $ci = '/^\/([a-z_A-Z]+\w)\/([0-9]+)$/';               // /controller/id
            $c =  '/^\/([a-z_A-Z]+\w)$/';                             // /controller
            $i =  '/^\/([0-9]+)$/';                             // /id
            $matches = array();
            if (preg_match($cai, $pathInfo, $matches)) {
                $this->controller = $matches[1];
                $this->action = $matches[2];
                $this->id = $matches[3];
       //         echo 'Linea: '.__LINE__ . ' method: '. $this->method;
            } else if (preg_match($ca, $pathInfo, $matches)) {
                $this->controller = $matches[1];
                $this->action = $matches[2];
     //           echo 'Linea: '.__LINE__ . ' method: '. $this->method;
            } else if (preg_match($ci, $pathInfo, $matches)) {
                $this->controller = $matches[1];
                $this->id = $matches[2];
   //             echo 'Linea: '.__LINE__ . ' method: '. $this->method;
            } else if (preg_match($c, $pathInfo, $matches)) {
                $this->controller = $matches[1];
   //             echo 'Linea: '.__LINE__ . ' method: '. $this->method;
            } else if (preg_match($i, $pathInfo, $matches)) {
                $this->id = $matches[1];
   //             echo 'Linea: '.__LINE__ . ' method: '. $this->method;
            }
            /*
            else {
            	// /mails/mail_to/1
            	echo 'Linea: '.__LINE__ . ' method: '. $this->method;
            	echo "<br/>pathInfo: " . $pathInfo;
            }
            */
        }
        
        
        if ($this->id === null) {
        	if (is_array($this->paramData)) {
	        	if (isset($this->paramData['id'])) {
	        		$this->id = $this->paramData['id'];
	        	}
        	}
        	elseif (is_object($this->paramData)){
	        	if (isset($this->paramData->id)) {
	        		$this->id = $this->paramData->id;	
	        	}
        	}
        }
        
        $this->model = ExjRequest::GetParam('model');
        if (!$this->model && $this->controller) {
        	$this->model = $this->controller;
        	if (substr($this->model, -1, 1) == "s") {
        		$this->model = substr($this->model, 0, -1);
        	}
        }

    	$this->callback = ExjRequest::GetParam('callback');
    	
    	// decode chars
    	if ($this->paramDataChanged) {
    		ExjTransferCharacters::decodeUTF8ToISO($this->paramDataChanged);
    	}
    	
    	if ($this->paramData) {
    		ExjTransferCharacters::decodeUTF8ToISO($this->paramData);
    	}
    	
    	/*
    	if ($this->paramCriteria) {
    		ExjTransferCharacters::decodeUTF8ToISO($this->paramCriteria);
    	}
    	*/
    	/*
    	if ($this->params) {
    		ExjTransferCharacters::decodeUTF8ToISO($this->params);
    	}
    	*/
    	
    }
    
    public function getParamFromDataChanged($nameParam, $valueDefault=''){
    	$nameParam = trim($nameParam);
    	if (!$this->paramDataChanged || !$nameParam) {
    		return $valueDefault;
    	}
    	
    	$p = $this->paramDataChanged;
    	if (!isset($p->$nameParam)) {
    		return $valueDefault;
    	}
    	
    	return $p->$nameParam;
    }

    /**
     * Devuelve el valor del parámetro de una criteria, este valor es el rawValue
     *
     * @param string $nameParam
     * @param mixed $valueDefault
     * @return string
     */
    public function getParamFromValuesCriteria($nameParam, $valueDefault=''){
    	// echo '<br/>'.__METHOD__." nameParam: $nameParam";
    	$nameParam = trim($nameParam);
    	if (!$this->paramValuesCriteria || !$nameParam) {
    		return $valueDefault;
    	}
    	
    	$p = $this->paramValuesCriteria;
    	if (!isset($p->$nameParam)) {
    		return $valueDefault;
    	}
    	
    	return $p->$nameParam;
    }
    
    
    static function GetParamPagingStart($valueDefault=0){
    	return self::GetParamInt('start', 0);
    }
    
    /**
     * Devuelve un parámetro pasado por la UI
     *
     * @param string $nameParam
     * @param string $valueDefault
     * @return mixed
     */
    public static function GetParam($nameParam, $valueDefault=''){
        if (!isset($_REQUEST[$nameParam])) {
        	return $valueDefault;
        }
        
        $valueParam = $_REQUEST[$nameParam];
        if ($valueParam) {
	        if ($valueParam === 'true' || $valueParam === 'false') {
	        	$valueParam = json_decode($valueParam);
	        }
        }
        
        return $valueParam;
    }
    
    /**
     * Obtiene parámetro option o componente
     *
     * @return string
     */
    public static function GetParamOption(){
    	return trim(self::GetParam('option'));
    }

    public static function GetParamInt($nameParam, $valueDefault=0){
        $value = self::GetParam($nameParam, $valueDefault);
        if (!$value) {
        	$value = 0;
        }
        
        $value = intval($value);
        return $value;
    }
    
    
    public static function SetParam($nameParam, $value, $overwriteParam=true){
    	if (!isset($_REQUEST[$nameParam])) {
    		$_REQUEST[$nameParam] = $value;
    	}
    	else if ($overwriteParam) {
    		$_REQUEST[$nameParam] = $value;
    	} 
    	
    	return $_REQUEST[$nameParam];
    }
    
    /**
     * Envia parámetros para consulta o query
     *
     * @param int $pageSize Tamaño de la pagina o limite de la consulta
     * @param string $defaultSort Nombre del campo a ordenar, por defecto ninguno
     * @param string $defaultDir Dir de ordenar, ASC o DESC
     * @param bool $overwriteParam Sobrescribe el parámetro, por defecto true
     */
    public static function SetParamsQuery($pageSize, $defaultSort='', $defaultDir='', $overwriteParam=true){
    	if (is_numeric($pageSize)) {
    		$pageSize = intval($pageSize);
    		self::SetParam('limit', $pageSize, $overwriteParam);
    	}
		
		if ($defaultSort) {
			self::SetParam('sort', $defaultSort, $overwriteParam);
		}
		
		if ($defaultDir){
			self::SetParam('dir', $defaultDir, $overwriteParam);	
		}
    }
    
    public static function SetParamsQueryFromModelList($modelList, $overwriteParam=true){
    	if (!$modelList) {
    		return false;
    	}
    	
    	if (!is_object($modelList)) {
    		$exj->setErrorValidating("ERROR AL LLAMAR A " . __METHOD__.".<br/>El 1er parámetro no es un objeto de la instancia de: ExjListModel");
    		return false;
    	}
    	
    	if (!($modelList instanceof ExjListModel)) {
    		global $exj;
    		$exj->setErrorValidating("ERROR AL LLAMAR A " . __METHOD__.".<br/>El 1er parámetro no es una instancia de la clase: ExjListModel");
    		return false;
    	}
    	
    	if (is_numeric($modelList->pageSize)) {
    		$pageSize = intval($modelList->pageSize);
    		self::SetParam('limit', $modelList->pageSize, $overwriteParam);
    	}
		
		if ($modelList->defaultSort) {
			self::SetParam('sort', $modelList->defaultSort, $overwriteParam);	
		}

		if ($modelList->isSortDesc()) {
			self::SetParam('dir', 'DESC', $overwriteParam);
		}
		else {
			self::SetParam('dir', 'ASC', $overwriteParam);
		}
    }
    

    /**
     * Eliminar los parametros para consulta o query
     *
     */
    public static function ClearParamsQuery(){
    	self::SetParam('limit', 0);
    	self::SetParam('sort', '');
    	self::SetParam('dir', '');
    }
    
    public static function IsAjax() {
	   return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

    public static function IsConsole(){
        if (isset($_SERVER['SESSIONNAME']) && strtolower($_SERVER['SESSIONNAME']) == 'console') {
            return true;
        }

        return false;
    }
    
}

?>