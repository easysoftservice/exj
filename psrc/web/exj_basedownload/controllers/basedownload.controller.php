<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppBasedownloadController
 * Controlador base para descargas de archivos
 */
class AppBasedownloadController extends ExjController {
	private $_CONTENT_TYPE_PDF = 'application/pdf';
	private $_CONTENT_TYPE_EXCEL_XLS = 'application/vnd.ms-excel';
	private $_CONTENT_TYPE_EXCEL_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
	private $_CONTENT_TYPE_FORCE_DOWNLOAD = 'application/force-download';
	private $_CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';
	private $_CONTENT_TYPE_XML = 'text/xml';
	private $_CONTENT_TYPE_XML_UTF8 = 'application/xml; charset=utf-8';
	
	private $_CONTENT_DISPOSITION_ATTACHMENT = 'attachment'; // descarga
	private $_CONTENT_DISPOSITION_INLINE = 'inline'; // visualización
	private $_canViewFile = false;
	
	
	private $_timeCacheSeg=120; // 2 minutos
	
	
	public function dispatch() {
		// global $exj;
		
		$pathFile = ExjRequest::GetParam('idFile');
		$entrada = ExjRequest::GetParam('entry'); // in out
		$isPathFull = ExjRequest::GetParamInt('idFull');
		$fileName = ExjRequest::GetParam('fileName');
		$this->_canViewFile = ExjRequest::GetParamInt('canView');
		
		if (!$pathFile) {
			return $this->_setError('No se indicó la entrada del archivo!');
		}
		
		// return $this->_setError('Prueba de error');
		
		if (!$isPathFull && !$entrada) {
			return $this->_setError('No se indicó el tipo de entrada');
		}
		
		$fullPathFile = AppBasedownloadModel::DecodePathFile($pathFile, $entrada, $isPathFull);
		
		if (!file_exists($fullPathFile)) {
			// echo "fullPathFile: $fullPathFile";
			if (!ExjUser::IsRolSuperAdmin()) {
				$pathFile = basename($pathFile);
			}
			return $this->_setError("El archivo:<br/>$pathFile <br/>no existe!");
		}
		
		
		$this->_writeFile($fullPathFile, $fileName);
		exit();
	}
	
	private function _setError($msg){
		$response = new ExjResponse();
		$response->fixFormatHTML();
		
		$response->setMsgError($msg, 'ERROR DESCARGANDO ARCHIVO');
		
		return $response;
	}

	private function _writeFile($pathFile, $fileName=''){
		$path_parts = pathinfo($pathFile);
		
		header('Cache-Control: maxage=0');
	//	header('Cache-Control: maxage='.$this->_timeCacheSeg);
	
		header('Expires: '.date(DATE_COOKIE,time()+$this->_timeCacheSeg)); // Cache for 2 mins
	//	header('Expires: '.gmdate('D, d M Y H:i:s', time()+$this->_timeCacheSeg).' GMT'); // Cache for 2 mins
		header('Pragma: public');
		
		// $path_parts['dirname'];
		$extFile = $path_parts['extension'];
		$extFile = strtolower($extFile);
		
		if (!$fileName) {
			$fileName = $path_parts['basename'];
		}
		else {
			$fileName .= '.'.$extFile;
		}
		
		$contentTypes = array();
		switch ($extFile) {
			case 'pdf':
				$contentTypes[] = $this->_CONTENT_TYPE_PDF;
			break;

			case 'xlsx':
//				$contentTypes[] = $this->_CONTENT_TYPE_FORCE_DOWNLOAD;
				$contentTypes[] = $this->_CONTENT_TYPE_EXCEL_XLSX;
				header("Content-Transfer-Encoding: Binary");
			break;

			case 'xls':
				$contentTypes[] = $this->_CONTENT_TYPE_EXCEL_XLS;
				header("Content-Transfer-Encoding: Binary");
			break;
			
			case 'xml':
				$contentTypes[] = $this->_CONTENT_TYPE_XML_UTF8;
			//	$contentTypes[] = $this->_CONTENT_TYPE_XML;
				header("Content-Transfer-Encoding: Binary");
			break;
			
			case 'zip':
				$contentTypes[] = $this->_CONTENT_TYPE_OCTET_STREAM;
				header("Content-Transfer-Encoding: Binary");
			break;
		
			default:
				$contentTypes[] = $this->_CONTENT_TYPE_FORCE_DOWNLOAD;
			//	$contentTypes[] = $this->_CONTENT_TYPE_OCTET_STREAM;
				header("Content-Transfer-Encoding: Binary");
			break;
		}
		
		foreach ($contentTypes as $contentType) {
			header('Content-Type: '.$contentType);
		}
		
		
		$contentDisposition = $this->_CONTENT_DISPOSITION_ATTACHMENT;
		if ($this->_canViewFile) {
			$contentDisposition = $this->_CONTENT_DISPOSITION_INLINE;
		}
		
		header('Content-Disposition: '. $contentDisposition.';filename="'.$fileName.'"');
		
	//	echo "<hola>666</hola>";
		readfile($pathFile);
	}
}

?>