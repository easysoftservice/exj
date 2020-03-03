<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Manejador de Logs del Sistema
 *
 */
class ExjHandlerLogData extends ExjObject {
	const CHAR_COMMENT = '#';
	const END_LINE = "\n";
	
	private $_pathLog = 'logs', $_pathFile='';
	private $_nameFile = '';
	private $_lastTimeAdd = '', $_lastTypeError = -1;
	private $_verificateLastTimeToWrite = true;
	private $_isAddedInfoHead = false;
	
	public function __construct($prefixNameFile=''){
		$this->_pathLog = JPATH_BASE.'/storage/logs';
		
		if ($prefixNameFile) {
			$prefixNameFile = str_replace(":", "_", $prefixNameFile);
			$prefixNameFile .= '_';
		}
		
		$this->_nameFile = $prefixNameFile . date("Y_m_d");
		$this->_nameFile .= '.log.php';
		
		$this->_pathFile = $this->_pathLog.'/'. $this->_nameFile;
	}
	
	static function GetInfoHeadFile(){
		$infoHead = array();
		$infoHead[] = self::CHAR_COMMENT . "<?php die('Acceso restringido para archivos Log'); ?>";
		$infoHead[] = self::CHAR_COMMENT . "Versión: " . Exj::GetVersionApp();
		$infoHead[] = self::CHAR_COMMENT . "Software: Exj de EasySoft service";
		
		$infoHead = implode(self::END_LINE, $infoHead);
		$infoHead .= self::END_LINE;

		return $infoHead;
	}
	
	public function getNameFile(){
		return $this->_nameFile;
	}
	
	private function _writeToFile($msgLog){
		/*
		if (!is_writable($this->_pathFile)) {
		    Exj::WriteLn("El archivo $this->_pathFile no es escribible");
		    return false;
		}
		*/
		
		if (!$this->_isAddedInfoHead) {
			if (!file_exists($this->_pathFile)) {
				$msgLog = self::GetInfoHeadFile() . $msgLog;
			}
			
			$this->_isAddedInfoHead = true;
		}

	    if (!$handle = @fopen($this->_pathFile, 'a')) {
	         Exj::WriteLn("No se puede abrir el archivo ($this->_pathFile)");
	         return false;
	    }
	
	    if (@fwrite($handle, $msgLog) === false) {
	        Exj::WriteLn("No se puede escribir en el archivo ($this->_pathFile)");
	        fclose($handle);
	        return false;
	    }
	    
	    fclose($handle);
	    return true;
	}
	
	public function disableVerificateLastTimeToWrite($disabled = true){
		$this->_verificateLastTimeToWrite = !$disabled;
	}
	
	private $_isModeConsole = null;
	
	public function writeLogLn($msg, $typeError='', $addTraces = true){
		
		if ($this->_isModeConsole === null) {
			$this->_isModeConsole = Exj::IsModeConsole();
		}
		
		if ($this->_isModeConsole) {
			$msg = "CONSOLA. ". $msg;
		}
		
		$itemLog = new AppLogItem($msg, $typeError, $addTraces);
		if ($this->_verificateLastTimeToWrite && $this->_lastTimeAdd == $itemLog->col2Time) {
			// if ($this->_lastTypeError == $itemLog->col7TypeError || ($itemLog->col7TypeError == ExjError::TIPO_ERROR_BUFFER)) {
			if ($this->_lastTypeError == $itemLog->col7TypeError) {
				return false;
			}
		}
		
		$this->_lastTimeAdd = $itemLog->col2Time;
		$this->_lastTypeError = $itemLog->col7TypeError;
		
		return $this->_writeToFile($itemLog->toStringLog(self::END_LINE));
	}

	/**
	 * Escribe en el archivo de logs
	 *
	 * @param string, object, array $mixed
	 * @return bool
	 */
	public function writeMixedLn($mixed, $mixed2=null){
		if (!$mixed) {
			return false;
		}
		if (is_object($mixed) || is_array($mixed)) {
			$mixed = print_r($mixed, true);
		}
		if ($mixed2 !== null) {
			if (is_object($mixed2) || is_array($mixed2)) {
				$mixed2 = print_r($mixed2, true);
			}
			
			if ($mixed2 !== '') {
				$mixed .= ' '. $mixed2;
			}
		}
		
		return $this->writeLogLn($mixed, '', false);
	}
	
	/**
	 * Escribe en el archivo de logs. Alias del método writeMixedLn
	 *
	 * @param string, object, array $mixed
	 * @return bool
	 */
	public function write($mixed, $mixed2 = null){
		return $this->writeMixedLn($mixed, $mixed2);
	}
	
	public function getItemsLog($fileLog='', $criteria=null){
		$itemsLog = array();
		
		$pathFileLog = '';
		if ($fileLog) {
			$pathFileLog = $this->_pathLog . '/'. $fileLog;
		}
		else {
			$pathFileLog = $this->_pathFile;
		}
		
		
		if (!file_exists($pathFileLog)) {
	      //  Exj::WriteLn("No existe el archivo: $pathFileLog");
			return $itemsLog;
		}

	    if (!$handle = @fopen($pathFileLog, 'r')) {
	         Exj::WriteLn("No se puede abrir el archivo ($pathFileLog)");
	         return $itemsLog;
	    }
	    
	    $sizeFile = filesize($pathFileLog);
	    $dataLogs = '';
	    if ($sizeFile > 0) {
	    	$dataLogs = fread($handle, $sizeFile);
	    }
	    
		fclose($handle);
		
		if (!$dataLogs) {
			return $itemsLog;
		}
		
		$itemsLogsStrs = explode(self::END_LINE, $dataLogs);
		$id = 1;
		
		foreach ($itemsLogsStrs as $itemLogStr) {
			if (!$itemLogStr || strlen($itemLogStr) <= 6) {
				continue;
			}
			
			$charComentario = substr($itemLogStr, 0, 1);
			if ($charComentario == self::CHAR_COMMENT) {
				continue;
			}
			
			$logItem = new AppLogItem();
			$logItem->loadFromString($itemLogStr);
			
			$logItem->col1Id = $id++;
			
			if (!$logItem->isCriteria($criteria)) {
				continue;
			}
			
			// add var
			$logItem->col7TypeErrorStr = ExjError::GetTextTypeError(
				$logItem->col7TypeError
			);
			
			$itemsLog[] = $logItem->toObject();
		}
		
		return $itemsLog;
	}
	
	public function getFilesLogs($maxFiles = 30){
		$filesLogs = array();
		$FILES_EXCEPTIONS = array();
		$FILES_EXCEPTIONS[] = 'index.php';
		
		if (is_dir($this->_pathLog)) {
		    if ($dh = opendir($this->_pathLog)) {
		    	$id = 0;
		        while (($file = readdir($dh)) !== false) {
		        	$pathFile = $this->_pathLog .'/'. $file;
		        	
		        	if (filetype($pathFile) != 'file') {
		        		continue;
		        	}
		        	
		        	$pathParts = pathinfo($pathFile);
		        	$extFile = $pathParts['extension'];
		        	
	        		if (in_array(strtolower($pathParts['basename']), $FILES_EXCEPTIONS)){
	        			continue;
	        		}
		        	
		        	if ($extFile != 'log' && $extFile != 'php') {
		        		
		        		continue;
		        	}
		        	$id += 1;
		        	
				//	$pathParts['dirname'];
				//	$pathParts['basename'];
					
					$timeLastChange = filectime($pathFile);
					
//					Exj::WriteLn("ultimo cambio: $pathFile ". $timeLastChange);
		            
					$fileLog = new stdClass();
		            $fileLog->id = $id;
		            $fileLog->name = $file;
		            $fileLog->sizeStr = ExjUtil::RenderSizeBytes(filesize($pathFile));
		            $fileLog->isCurrent = ($this->_nameFile == $file);
		            $fileLog->isCurrentStr = ($fileLog->isCurrent ? 'SI':'NO');
		            $fileLog->timeLastChange = date("H:i:s", $timeLastChange);
		            
		            $filesLogs[] = $fileLog;
		            
		        }
		        closedir($dh);
		    }
		}
		

	//	$nextWeek = time() + (7 * 24 * 60 * 60);

		
	//	print_r($filesLogs);
		
		return $filesLogs;
	}
	
}

class AppLogItem extends ExjObject {
	public $col1Id;
	public $col2Time='00:00:00';
	public $col3IdCompany=0;
	public $col4UserName='';
	public $col5Delayed= -1;
	public $col6RequestMethod='';
	public $col7TypeError = 0;
	public $col8Msg='';
	public $col9Traces ='';
	public $col10PathInfo = '';
	public $col11UserAgent = '';
	public $col12Query = '';
	
	
	private $_separator = '|';

	public function __construct($msg='', $typeError='', $addTraces = false){
		global $exj;
		
		$msg = str_replace("\r\n", "<br/>", $msg);
		$msg = str_replace("\n", "<br/>", $msg);
		
		$this->col1Id = time();
		$this->col2Time = date("H:i:s"); // Exj::GetDateTime("%H:%M:%S");
		$this->col3IdCompany = ExjUser::GetIdCompania();
		$this->col4UserName = sprintf("%-15s", Exj::GetUserUserName());
		

		$this->col5Delayed = sprintf("%-7s",Exj::GetServerDelayed());
		$this->col6RequestMethod = sprintf("%-6s", Exj::GetServerRequestMethod());
		

		if (!$typeError) {
			$typeError = ExjError::TIPO_ERROR_NINGUNO;
		}
		$this->col7TypeError = sprintf("%-2s", $typeError) ;
		$this->col8Msg = $msg;
		if ($addTraces) {
			$this->addTraces();
		}
		
		$this->col10PathInfo = Exj::GetServerPathInfo();
		$this->col11UserAgent = Exj::GetServerUserAgent();
		$this->col12Query = Exj::GetServerQuery(true);
	}
	
	public function addTraces(){
		if ($this->col9Traces) {
			return ;
		}
		
		$this->col9Traces = $this->_getTracesStr();
	}
	
	public function toStringLog($endLine = "\n"){
		$items = $this->convertObjectToArray($this->toObject(), false);
		return implode($this->_separator, $items) . $endLine;
	}
	
	public function loadFromString($dataStr){
		$this->col1Id = 0;
		
		if (!$dataStr) {
			return false;
		}
		
		$arrayData = explode($this->_separator, $dataStr);
		
		$indexCol = 0;
		foreach ($arrayData as $valueLog) {
			$indexCol += 1;
			switch ($indexCol) {
				case 1:
					$this->col1Id = $valueLog;
				break;
				
				case 2:
					$this->col2Time = $valueLog;
				break;
			
				case 3:
					$this->col3IdCompany = $valueLog;
				break;
			
				case 4:
					$this->col4UserName = $valueLog;
				break;
			
				case 5:
					$this->col5Delayed = $valueLog;
				break;
			
				case 6:
					$this->col6RequestMethod = $valueLog;
				break;

				case 7:
					$this->col7TypeError = $valueLog;
				break;

				case 8:
					$this->col8Msg = $valueLog;
				break;

				case 9:
					$this->col9Traces = $valueLog;
				break;
				
				case 10:
					$this->col10PathInfo = $valueLog;
				break;

				case 11:
					$this->col11UserAgent = $valueLog;
				break;

				case 12:
					$this->col12Query = $valueLog;
				break;
				
				default:
					$this->writeErrorClassLn($this, "Indice $indexCol no soportado");
				break;
			}
		}
		
		return true;
	}
	
	public function isCriteria($criteria){
		if (!$criteria || !is_object($criteria)) {
			return true;
		}
		
		$vars = get_object_vars($criteria);
		$isOK = true;
		foreach ($vars as $name => $valueCriteria) {
			if (!$valueCriteria) {
				continue;
			}
			if (!isset($this->$name)) {
				continue;
			}
			
			
			if ($valueCriteria != trim($this->$name)) {
			//	Exj::WriteLn("$this->col1Id name: $name $valueCriteria NO IGUAL " . $this->$name);
				$isOK = false;
				break;
			}
		}
		
		return $isOK;
	}
	
	private function _getTracesStr(){
		$traces = debug_backtrace();
		if (!$traces || count($traces) == 0) {
			return "No se pudo obtener seguimiento del código";
		}
		
		$maxTraces = 6;
		
		$tracesStr = array();
		$index = -1;
		foreach ($traces as $trace) {
			if (++$index <= 2) {
				continue;
			}
			
			$pathFileTrace = $trace['file'];
			
			$posFileBase = stripos($pathFileTrace, 'tbase.php');
			if ($posFileBase !== false) {
				// lo encontró
				if (count($tracesStr) == 0) {
					continue;
				}
			}
			
			$tracesStr[] = $pathFileTrace . " " . $trace['line'];
			
			if (count($tracesStr) >= $maxTraces) {
				break;
			}
		}
		
		if (count($tracesStr) == 0) {
			$tracesStr = $traces;
		}
		
		return implode("<br/>", $tracesStr);
	}
	
}


?>