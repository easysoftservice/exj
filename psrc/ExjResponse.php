<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjResponse
 * Clase base para dar respuesta al cliente, existen algunos métodos en el controlador que requieren una instancia de esta clase.
 */
class ExjResponse extends ExjObject {
    public $success, $data, $errors, $tid, $trace;
    public $dataBuffer, $status, $DataTopics, $Msg;

    private $_format='json';
    private $_FORMAT_HTML='html';
    private $_FORMAT_JSON='json';
    private static $_ExceptFieldsToObject=null;
    
    /**
     * Clase base para dar respuesta al cliente
     *
     * @param mixed $data Cualquier tipo por los general en un object
     */
    public function __construct($data=null) {
    	$this->_format = $this->_FORMAT_JSON;
    	
        $this->success = false;
        $this->data    = $data;
        if ($this->data) {
        	$this->success = true;
        }
        
        $this->dataBuffer = '';
        $this->status = Exj::ESTADO_OK; // ok
        
        
		$this->Msg = new stdClass();        
		$this->Msg->title = '';
		$this->Msg->text = '';
		$this->Msg->type = Exj::MSG_TIPO_NINGUNO;
		
		
		$this->DataTopics = new stdClass();
		$this->DataTopics->topics = array();
		$this->DataTopics->total = -1;
		
		$message = '';
		if ($data) {
			if (is_array($data)) {
				$message  = isset($data["message"]) ? $data["message"] : '';
			}
			if (is_object($data)) {
				$message  = isset($data->message) ? $data->message : '';
			}
		}
        
        if ($message) {
        	$this->setMsgInfo($message);	
        }
    }

    public static function NewResponseOffline(){
        $obj = new stdClass();
        $obj->_offline = true;
        $obj->_offline_message = "SE HAN ACTUALIZADO CAMBIOS EN EL SERVIDOR.<br />SE REQUIERE ELIMINAR EL CACHE DE SU NAVEGADOR, E INGRESAR AL SISTEMA...";
        $obj->_offline_message .= '<p style="color:blue;">';
        $obj->_offline_message .= "<br />Para eliminar el cache de sú navegador <b>Mozilla Firefox, Chrome, IE</b> haga lo siguiente:";
        $obj->_offline_message .= "<br />Ahora presione las teclas: Ctrl+Shift y la tecla Delete (Eliminar). Seleccione solamente el casillero: Cache y presione ENTER";
        $obj->_offline_message .= '</p>';

        $obj->_offline_message = ExjText::__($obj->_offline_message);
        
        $r = new ExjResponse($obj);
        return $r;
    }

    public static function NewResponseReloadApp($textExtraInfo=''){
        $obj = new stdClass();
        $obj->_reloadApp = new stdClass();
        $obj->_reloadApp->titleToShow = Exj::GetTitleApp();

        $obj->_reloadApp->msgToShow = ExjText::__("Recientemente se ha cambiado de Empresa.");
        $obj->_reloadApp->msgToShow .= '<br/>' . ExjText::__("Empresa actual: ") . ExjUser::GetNombreEmpresa() . '.';
        $obj->_reloadApp->msgToShow .= '<h2>' . ExjText::__("El sistema automáticamente se actualizará.") . '</h2>';

        if ($textExtraInfo) {
            $obj->_reloadApp->msgToShow .= '<br>'. $textExtraInfo;
        }

        $r = new ExjResponse($obj);
        return $r;   
    }
    
    
    public function fixFormatHTML(){
    	$this->_format = $this->_FORMAT_HTML;
    }
    public function fixFormatJSON(){
    	$this->_format = $this->_FORMAT_JSON;
    }

    public function addToMsg($msgExtra, $strConcat='<br>'){
        if (!$msgExtra || empty($msgExtra)) {
            return $this;
        }

        if (is_array($msgExtra)) {
            $msgExtra = implode($strConcat, $msgExtra);
        }

        if ($this->Msg->text) {
            $this->Msg->text .= $strConcat . $msgExtra;
        }
        else{
            $this->Msg->text = $msgExtra;
        }

        return $this;
    }
    
    public function setMsg($msg, $msgTitle, $msgType) {
		if(!$msg){
			$msgType = Exj::MSG_TIPO_NINGUNO;
		}
		else {
			global $exj;
			$exj->parseTextResult($msg);
		}
		
		if (strtoupper($msgTitle) == 'ERROR') {
			$msgTitle = ExjText::__($msgTitle);
		}
		else {
			$msgTitle = ExjText::_($msgTitle);
		}

        if (is_array($msg)) {
            $msg = implode('<br>', $msg);
        }
    	
		$this->Msg->title = $msgTitle;
		$this->Msg->text = $msg;
		$this->Msg->type = $msgType;
		
		if ($msgType == Exj::MSG_TIPO_ERROR) {
			$this->status = Exj::ESTADO_ERROR;	
		}

		if ($this->status == Exj::ESTADO_ERROR) {
			global $exj;
			$exj->logWrite($msg, Exj::TIPO_ERROR_VALIDINGDATA);
		}
		
		return $this;
    }
    
    public function getErrorMsg(){
    	$errorMsg = '';

		if (isset($this->Msg) && $this->Msg->type == Exj::MSG_TIPO_ERROR) {
			$errorMsg = $this->Msg->text;
		}
    	
    	return $errorMsg;
    }

    /**
     * Indica si es válida la respuesta
     *
     * @return bool true se ha producido algún error, false sino
     */
    public function isValid(){
    	return ($this->status == Exj::ESTADO_ERROR);
    }
    
    /**
     * Envia mensaje de error a la UI
     *
     * @param string $msg
     * @param string $msgTitle
     * @return ExjResponse
     */
    public function setMsgError($msg, $msgTitle= "ERROR") {
		$this->setMsg($msg, $msgTitle, Exj::MSG_TIPO_ERROR);
		global $exj;
		$exj->logWrite($msg, Exj::TIPO_ERROR_VALIDINGDATA);
		
		return $this;
    }
    
    public function setMsgInfo($msg, $msgTitle= '') {
		return $this->setMsg($msg, $msgTitle, Exj::MSG_TIPO_INFO);
    }
    
    /**
     * Envia a la UI para mostra un mensaje de advertencia
     *
     * @param string $msg
     * @param string $msgTitle
     * @return ExjResponse
     */
    public function setMsgWarning($msg, $msgTitle= '') {
		return $this->setMsg($msg, $msgTitle, Exj::MSG_TIPO_WARNING);
    }
    public function setMsgNotify($msg, $msgTitle= '') {
		return $this->setMsg($msg, $msgTitle, Exj::MSG_TIPO_NOTIFY);
    }
    public function setMsgHTML($msg, $msgTitle= '') {
		return $this->setMsg($msg, $msgTitle, Exj::MSG_TIPO_HTML);
    }

    public function haveMsgText() {
    	return ($this->Msg->text ? true: false);
    }

    public function haveMsgError() {
    	if (!$this->haveMsgText()) {
    		return false;
    	}
    	
    	return ($this->Msg->type == Exj::MSG_TIPO_ERROR);
    }
    
    /**
     * Envia un objeto a la UI
     *
     * @param mixed $dataObject
     * @return ExjResponse
     */
    public function setDataObject($dataObject) {
    	$this->data = $dataObject;
    	
    	return $this;
    }
    
    /**
     * Forza a salir del sistema
     *
     * @param bool $forceExit por defecto true
     */
    public function forceExit($forceExit = true){
		$objForceExit = new stdClass();
		$objForceExit->forceExit = $forceExit;
		
		$this->setDataObject($objForceExit);
    }
    
    public function setDataFooter($dataFooter, $prop=''){
    	if (!$this->data || !is_object($this->data)) {
    		$this->data = new stdClass();
    	}
    	
    	if ($prop) {
    		$this->data->dataFooter->$prop = $dataFooter;
    		return ;
    	}
    	
    	$this->data->dataFooter = $dataFooter;
    }
    
    public function &getDataFooter($objDefault = null){
    	if (!$this->data) {
    		return $objDefault;
    	}
    	
    	if (!isset($this->data->dataFooter)) {
    		return $objDefault;
    	}
    	
    	return $this->data->dataFooter;
    }
    
    /**
     * Envia datos items y total
     *
     * @param array $topics
     * @param int $total
     * @return ExjResponse
     */
    public function setDataTopics($topics, $total= -1) {
    	if ($total == -1) {
    		$total = count($topics);
    	}
    	
		$this->DataTopics->topics = $topics;
		$this->DataTopics->total = $total;
		
	//	$this->_addPropOrdToTopics();
		return $this;
    }
    
    private function _addPropOrdToTopics(){
    	if (!$this->DataTopics->topics || !is_array($this->DataTopics->topics)) {
    		return false;
    	}
    	
    	if (count($this->DataTopics->topics) == 0) {
    		return true;
    	}
    	
    	$numOrd = 1;
    	foreach ($this->DataTopics->topics as &$item) {
    		if (isset($item->isData) || isset($item->isHeader)) {
    			if (isset($item->isData) && !$item->isData) {
    				$numOrd = 0;
    			}
    			elseif (isset($item->isHeader) && $item->isHeader) {
    				$numOrd = 0;
    			}
    		}
    		
    		$item->_ord = $numOrd;
    		$numOrd += 1;
    	}
    	
    	/*
    	echo "add prop _ord:<br/>";
    	print_r($this->DataTopics->topics);
    	*/
    }
    
    /**
     * Retorna un array de items
     *
     * @return array
     */
    public function &getItemsDataTopics(){
    	return $this->DataTopics->topics;
    }
    
    public function getTotalDataTopics(){
    	if (!isset($this->DataTopics->total) || $this->DataTopics->total < 0) {
    		return 0;
    	}
    	
    	return $this->DataTopics->total;
    }
    
    public function loadTopics(&$items){
    	$items = $this->DataTopics->topics;
    }
    
    public function haveData() {
    	return ($this->data !== null);
    }
    public function haveDataTopics() {
    	if ($this->DataTopics->total >= 0) {
    		return true;
    	}
    	
    	if (!$this->DataTopics->topics) {
    		return false;
    	}
    	
    //	return true;
    	
    	return (count($this->DataTopics->topics) >= 0);
    }

    
    public function verify() {
		if ($this->Msg->type == Exj::MSG_TIPO_ERROR) {
			$this->status = Exj::ESTADO_ERROR;
		}
		
		if(!$this->haveDataTopics() && !$this->haveData() && !$this->haveMsgText()){
			$this->status = Exj::ESTADO_ERROR;
			$this->setMsg("No se han servido datos de listados ni objeto. Consulte con el Administrador.", "ERROR", Exj::MSG_TIPO_ERROR);
		}
		
		$this->success = ($this->status == Exj::ESTADO_OK);
    }
    
    private $_addLinkRegresarSistemaInHTML = false;
    
    public function addLinkRegresarSistemaInHTML($enable = true){
    	$this->_addLinkRegresarSistemaInHTML = $enable;
    }
    
    private $_includeTemplate = false;
    
    public function includeTemplateForHTML($includeTemplate = true){
    	$this->_includeTemplate = $includeTemplate;
    	if ($this->_includeTemplate) {
    		if (!$this->haveData()) {
    			$dataOK = new stdClass();
    			$dataOK->result = 1;
    			$this->setDataObject($dataOK);
    		}
    	}
    }
    
    public function to_html($verifyResponse = true) {
    	if ($verifyResponse) {
    		$this->verify();	
    	}
    	
    	global $exj;
    	$htmlDataBuffer = '';
    	
    	if ($this->dataBuffer && ExjUser::IsRolSuperAdmin() && strlen($this->dataBuffer) > 3) {
    		$htmlDataBuffer = '<div style="background-color: gray;color: yellow; font-size: 12px;" ><div><b>dataBuffer</b></div>' . $this->dataBuffer . '</div>';
    	}
    	
    	if (!$this->haveMsgText() && !$exj->haveError()) {
    		if ($this->_includeTemplate) {
    			return $htmlDataBuffer;
    		}
    	}
    	
    	$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
    	
    	$html .='<html xmlns="http://www.w3.org/1999/xhtml"  dir="ltr" lang="es" id="vbulletin_html">';
    	$html .='<head>';
    	$html .='<title>GYMCloud</title>';
    	$html .='</head>';
    	
    	$html .='<body>';
    	
    	$html .='<header id="header">';
    	$html .='</header>';
    	
    	$html .='<div id="content-wrapper">';
    	$html .='<section id="content">';
    	$html .='</section>';
    	
    	$html .='</div>'; // content-wrapper
    	$html .='<footer id="footer">';
    	$html .='<div id="footer-content">';
    	
    	if ($this->haveMsgText()) {
    		if ($this->haveMsgError()) {
    			$html .= '<h3 style="color:red;">' . $this->Msg->title . '</h3>';
    		}
    		else {
    			$html .= '<h3>' . $this->Msg->title . '</h3>';
    		}
    		
    		ExjTransferCharacters::encodeISOToUTF8($this->Msg->text);
			
			$html .= '<div>'. $this->Msg->text .'</div>';
    	}
    	else {
    		
    		$msgError = 'Error Desconocido';
    		if ($exj->haveError()) {
    			$msgError = $exj->getErrorMsg();
    		}
    		
    		ExjTransferCharacters::encodeISOToUTF8($msgError);
    		
    		$html .= '<h2>'. $msgError .'</h2>';
    	}
    	
    	if ($this->_addLinkRegresarSistemaInHTML) {
	    	$uri = new JURI();
	    	$html .= '<a href="'.$uri->root().'">';
	    	$html .= 'Regresar al Sistema';
	    	$html .= '</a>';
    	}
    	
    	if ($htmlDataBuffer) {
    		$html .= $htmlDataBuffer;
    	}
    	
    	$html .='</div>'; // footer-content
    	$html .='</footer>';
    	$html .='</body>';
    	$html .='</html>';
    	
    	return $html;
    } // to_html

    public static function SetExceptFieldsToObj($fields){
        self::$_ExceptFieldsToObject = $fields;
    }

    public function to_json($verifyResponse = true) {
    	if ($verifyResponse) {
    		$this->verify();	
    	}

        if (self::$_ExceptFieldsToObject) {
            $this->setExceptFieldsToObject(self::$_ExceptFieldsToObject);
        }
    	
    	$obj = $this->toObject();
    	ExjTransferCharacters::encodeISOToUTF8($obj);
    	
    	/*
    	if (!ExjRequest::IsAjax()){
    		if ($this->haveMsgText()) {
    			$msgHTML = '<h3>' . $obj->Msg->title . '</h3>';
    			$msgHTML .= '<div>'.$obj->Msg->text.'</div>';
    			
    			return $msgHTML;
    		}
    		else {
    			return "Acceso Denegado";
    		}
    	}
    	*/
    	
    //	$obj->testxx = 111;
    	
    	return json_encode($obj);
    }
    
    public function to_json_onlyTopics() {
    	return json_encode($this->DataTopics->topics);
    }

    /**
     * Escribe en la salida de respuesta y termina
     *
     * @param bool $verifyResponse true para verificar si los datos que se van a aenviar son consistentes
     */
    public function writeExit($verifyResponse = true) {
    	$this->write($verifyResponse);
    	exit();
    }
    
    public function isFormatJSON(){
    	if (!$this->_format) {
    		return true;
    	}
    	
    	return ($this->_format == $this->_FORMAT_JSON);
    }
    public function isFormatHTML(){
    	return ($this->_format == $this->_FORMAT_HTML);
    }
    
    public function write($verifyResponse = true) {
    	if (Exj::InstanceRequest()->callback) {
    		$this->writeWithCallback(Exj::InstanceRequest()->callback);
    		return ;
    	}
    	
    	$this->cleanToWrite();
    	if ($this->isFormatHTML()) {
    		$responseHTML = $this->to_html($verifyResponse);
    		if ($responseHTML) {
    			echo $responseHTML;
    		}
    		return ;
    	}
    	
    	echo $this->to_json($verifyResponse);
    }
    
    protected function catchBufferOut(){
		global $exj;
		
		$this->dataBuffer = ob_get_contents();
		if ($this->dataBuffer) {
			$this->dataBuffer = trim($this->dataBuffer);
		}
		if (strlen($this->dataBuffer) <= 6) {
			$this->dataBuffer = '';
		}
		
		if ($this->dataBuffer) {
			$exj->logWrite($this->dataBuffer, Exj::TIPO_ERROR_BUFFER);
		}
		
		if (!ExjUser::IsModeDebug()){
			$this->dataBuffer = '';
		}
		
		$exj->parseTextResult($this->dataBuffer);
    }
    
    public function cleanToWrite(){
    	if ($this->_includeTemplate && !$this->haveError()) {
    		return ;
    	}
    	
    	$this->catchBufferOut();
    	
		ob_end_clean();

		// start capturing output into a buffer
		ob_start();
    }
    
    public function writeWithCallback($callback, $verifyResponse = true) {
    	if (!$callback) {
    		$this->write($verifyResponse);
    		return;
    	}
    	
    	$this->cleanToWrite();
    	echo "$callback(" . $this->to_json($verifyResponse) . ")";
    }
    
    
    public function writeOnlyTopics() {
    	$this->cleanToWrite();
    	echo $this->to_json_onlyTopics();
    }

    public function setDataIniSession($msg){
        $this->data = new stdClass();
        $this->data->ini_session = true;
        $this->data->msg = ExjText::__($msg);

        return $this;
    }

    public function setDataOffline($msg){
        $this->data = new stdClass();
        $this->data->_offline = true;
        $this->data->_offline_message = $msg;

        return $this;
    }
}

?>