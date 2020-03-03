<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Servicio FTP, los errores son enviados a la Base, recuperarlos desde la instanca base
 *
 */
class ExjFTPHelper extends ExjObject {
	private $_host;
	private $_userName;
	private $_password;
	private $_port=21;
	const SEPARATOR_DIR='/';
	
	private $_conn_id=0;
	private $_pathRoot = '';
	private $_canOverwriteFile = false;
	
	public function __construct($host, $port=21, $userName='anonymous', $password='', $pathRoot='/'){
		$this->_host = $host;
		$this->_port = $port;
		
		$this->_userName = $userName;
		$this->_password = $password;

		
		if (strpos($host, '@') !== false || (strpos($host, 'ftp://') !== false)) {
			self::ParseURLFTP($host, $this->_host, $this->_port, $this->_userName, $this->_password, $pathRoot);
		}
		
		if ($pathRoot != self::SEPARATOR_DIR) {
			$pathRoot = "/$pathRoot/";
			$pathRoot = str_replace("//", self::SEPARATOR_DIR, $pathRoot);
		}
		
		$this->_pathRoot = $pathRoot;
		
		if (!$this->_host) {
			$this->_setError("No se ha especificado el host del servidor FTP");
			return ;
		}
	}
	
	public static function ParseURLFTP($pathFTP, &$host, &$port, &$user, &$pass, &$path){
		$partesURL = parse_url($pathFTP);
		if (!$partesURL || count($partesURL) == 0) {
			return ;
		}
		
		if (isset($partesURL['host']) && $partesURL['host']) {
			$host = $partesURL['host'];
		}
		else {
			// ver si esta asi: ftp://localhost
			if (strpos($pathFTP, 'ftp://') !== false) {
				$partesURL['path'] = self::SEPARATOR_DIR;
				$host = str_ireplace('ftp://', '', $pathFTP);
			}
		}
		
		if (isset($partesURL['port']) && $partesURL['port']) {
			$port = $partesURL['port'];
		}

		if (isset($partesURL['user']) && $partesURL['user']) {
			$user = $partesURL['user'];
		}

		if (isset($partesURL['pass'])) {
			$pass = $partesURL['pass'];
		}

		if (isset($partesURL['path']) && $partesURL['path']) {
			$path = $partesURL['path'];
		}
	}
	
	public function enableOverwriteFileFTP($overwriteFile=true){
		$this->_canOverwriteFile = $overwriteFile;
	}
	
	private function _validateConnect(){
		if ($this->_conn_id) {
			return true;
		}
		
		if (!$this->isValid()) {
			return false;
		}
		
		$this->_conn_id = @ftp_connect($this->_host, $this->_port);
		if (!$this->_conn_id) {
			$this->_setError("Could not connect to the FTP server.<br/>Host: $this->_host Port: $this->_port");
			return false;
		}
		
		if (! @ftp_login($this->_conn_id, $this->_userName, $this->_password)) {
			$this->_setError("Connection refused.<br/>Logged on as: $this->_userName Password: " . ($this->_password ? '***':'(Without Password)'));
			return false;
		}
		
		return true;
	}

	/**
	 * Cambia de modo pasivo o no
	 *
	 * @param bool $pasive
	 * @return bool FALSE si no pudo cambiar a modo
	 */
	public function changeToModePasive($pasive = true){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		// turn passive mode on off
		return ftp_pasv($this->_conn_id, $pasive);
	}
	
	public function getPathComplete($dir){
		$dir = $this->_pathRoot . $dir;
		
		$dir = str_replace("//", self::SEPARATOR_DIR, $dir);
		$dir = str_replace("\\", self::SEPARATOR_DIR, $dir);
		
		return $dir;
	}
	
	public function getDataArrayFiles($filesFTP){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		if (!is_array($filesFTP)) {
			$this->_setError("No se ha enviado archivos FTP en forma de arreglo. Ref: " . __METHOD__);
			return null;
		}
		$data = array();
		
		foreach ($filesFTP as $fileFTP) {
			$item = array();
			
			$item[] = $fileFTP; // basename($fileFTP);
			$item[] = ExjUtil::RenderSizeKBFile(ftp_size($this->_conn_id, $fileFTP));
			
			$dateFile = ftp_mdtm($this->_conn_id, $fileFTP);
			if ($dateFile == -1) {
				$item[] = '';
			}
			else {
				$item[] = date("Y-m-d H:i:s", $dateFile);
				
			}
			
			$data[] = $item;
		}
		return $data;
	} // getDataArrayFiles
	
	public function getFiles($emptyFilesIsError = true, $dir=''){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		$dir = $this->getPathComplete($dir);
		
		$contents = ftp_nlist($this->_conn_id, $dir);
		if (!is_array($contents)) {
			$this->_setError("No se pudo acceder al Directorio: $dir");
			return null;
		}
		
		$extFileCompare = '.txt';
		$lengthExtFile = strlen($extFileCompare);
		
		$filesFTP = array();
		foreach ($contents as $pathFile) {
			// echo "<br />test: $pathFile";
			$nameFile = basename($pathFile);
			if (!$nameFile) {
				continue;
			}
			
			$extFile = substr($nameFile, - $lengthExtFile, $lengthExtFile);
			if ($extFile != $extFileCompare) {
				continue;
			}
			
			$filesFTP[] = $pathFile;
			
			/*
			if(is_file(trim($nameFile))){
				$filesFTP[] = $pathFile;
			}
			else {
				echo "<br />NO es archvo: $nameFile ";
			}
			*/
		}
		
//		print_r($contents);
		
		if (count($filesFTP) == 0) {
			if ($emptyFilesIsError) {
				$this->_setError("No existen archivos en el Directorio ftp: $dir");
				return null;
			}
		}
		
		return $filesFTP;
	} // getFiles
	
	public function getInfoServerFTP($dir='', $charSeparatorLine='<br />'){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		$info = array();
		$info[] = "SERVIDOR FTP: $this->_host Puerto: $this->_port";
		
		$type = ftp_systype($this->_conn_id);
		
		$info[] = "Proporcionado por: $type";
		
		$info[] = "PROBANDO A MODO PASIVO ON: " . ($this->changeToModePasive() ? 'SI':'NO');
		$info[] = "PROBANDO A MODO PASIVO OFF: " . ($this->changeToModePasive(false) ? 'SI':'NO');
		
		
		$dir = $this->getPathComplete($dir);
		
		 // ftp_nlist
		$contents = ftp_rawlist($this->_conn_id, $dir);
		if (is_array($contents)) {
			$info[] = "Lista de Archivos en el directorio: $dir:";
			if (count($contents) == 0){
				$info[] = "(DIRECTORIO VACIO)";
			}
			else {
				foreach ($contents as $content) {
					$info[] = $content;
				}
			}
			
		}
		else {
			$info[] = "ERROR. No se pudo acceder al directorio: $dir. Posiblemente no existe el directorio.";
		}
		
		return implode($charSeparatorLine, $info);
	}
	
	public function upLoadContentFile($contentFile, $nameFile, $pathDest='', $closeConnection=true){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		global $exj;
		
		$pathFileOrig = ExjHandlerFile::GetDirectoryTemp() . $nameFile;
		if (Exj::GetError()->haveError()) {
			return false;
		}
		
		$msgError = '';
		ExjHelperFile::CreateFile($pathFileOrig, $contentFile, $msgError);
		if ($msgError) {
			$this->_setError($msgError);
			return false;
		}
		
		$isLoaded = $this->upLoadFile($pathFileOrig, $pathDest, $closeConnection);
		
		// borramos del temporal
		unlink($pathFileOrig);
		
		return $isLoaded;
	}
	
	/**
	 * Carga el archivo hacia el directorio destino FTP
	 *
	 * @param string $pathFileOrig
	 * @param string $pathDest
	 * @param bool $closeConnection Defecto true
	 * @param FTP_XXX $modeFile
	 * @return mixed Si huvo error retorna false, sino retorna el path destino copiado
	 */
	public function upLoadFile($pathFileOrig, $pathDest='', $closeConnection=true, $modeFile = FTP_BINARY){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		if (!$this->isValid()) {
			return false;
		}
		
		if(!file_exists($pathFileOrig)){
			$this->_setError("No existe el archivo origen: $pathFileOrig");
			return false;
		}
		
		// nombre del archivo
		$nameFile = basename($pathFileOrig);
		// se arma el path destino del archivo
		$pathFileDest = $this->getPathComplete($pathDest) . $nameFile;
		
		
		$result = '';
		if (!ftp_alloc($this->_conn_id, filesize($pathFileOrig), $result)) {
			$this->_setError("No hay espacio en el Servidor FTP.<br/>Respuesta del Servidor: $result");
			return false;
		}
		
		$lastChange = $this->getLastModifiedFile($pathFileDest);
		if ($lastChange) {
			if ($this->_canOverwriteFile) {
				if (!@ftp_delete($this->_conn_id, $pathFileDest)) {
					$this->_setError("El archivo ya existe.<br/>No se pudo eliminar <b>$nameFile</b>");
					return false;
				}
			}
			else {
				$this->_setError("Archivo ya existe<br/><b>$nameFile</b><br/>Ultimo cambio: $lastChange");
				return false;
			}
		}

		if(!@ftp_put($this->_conn_id, $pathFileDest, $pathFileOrig, $modeFile)){
			$this->_setError("Ocurrieron problemas mientras se cargaba.<br/>Archivo: $pathFileDest<br/>Servidor: $this->_host<br/>Comprobar permisos de escritura del directorio:<br/>".$this->getPathComplete($pathDest));
			return false;
		}
		
		if ($closeConnection) {
			$this->closeConnection();
		}
		
		return $pathFileDest;
	} // upLoadFile
	
	/**
	 * Valida el directorio, sino existe intenta crearlo, si existe se cambia al directorio indicado
	 *
	 * @param string $pathDir
	 * @return bool false se se genera algún error sino true
	 */
	public function validateDirectory($pathDir){
		if (!$this->_validateConnect()) {
			return false;
		}

		if(!@ftp_chdir($this->_conn_id, $pathDir)){ 
	         @ftp_chdir($this->_conn_id,"/"); 
	         if(!@ftp_mkdir($this->_conn_id, $pathDir)){ 
	         	$this->_setError("No se pudo crear directorio por FTP.<br>Directorio: $pathDir");
	         	return false;
	         } 
       	}
		
       	return true;
	}
	
	public function chdirToRoot(){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		if (!@ftp_chdir($this->_conn_id, "/")) {
			return false;
		}
		
		return true;
	}
	
	public function getLastModifiedFile($fileFTP, $formatedDateTime=true){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		$dt = ftp_mdtm($this->_conn_id, $fileFTP);
		if ($dt == -1) {
			return '';
		}
		
		if ($formatedDateTime) {
			$dt = date(Exj::GetValueCfg('uiFormatDatetimeDef'), $dt);
		}
		
		return $dt;
	}

	/**
	 * Indica si un archivo existe en el server FTP
	 *
	 * @param string $pathFileDest
	 * @return mixed Retorna false si hay un error, sino retorna un string con la fecha del ultimo cambio, sino existe retorna un string vacio
	 */
	public function fileExist($pathFileDest){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		$lastChange = $this->getLastModifiedFile($pathFileDest);
		return $lastChange;
	}

	/**
	 * Carga el Archivo Remoto, al directorio download/ftp/_temp_xxx
	 * Si el archivo destino ya existe, se sobrescribe
	 *
	 * @param string $pathFileRemote
	 * @param bool $isPathComplete
	 * @param TipyFTP $modeFile
	 * @return string Path completo a donde se ha guardado el archivo
	 */
	public function downLoadFile($pathFileRemote, $isPathComplete=true, $modeFile = FTP_BINARY){
		if (!$this->_validateConnect()) {
			return false;
		}
		
		if (!$this->isValid()) {
			return false;
		}
		
		if (!$isPathComplete) {
			$pathFileRemote = $this->getPathComplete($pathFileRemote);
		}
		
		$nameFile = basename($pathFileRemote);
		if (!$nameFile) {
			$this->_setError("No se ha especificado nombre de Archivo para descargar. Ref: $pathFileRemote");
			return false;
		}
		
		$subDirExtra = '_temp_' . ExjUser::GetId();
		
		$pathFileLocal = Exj::GetDirectoryDownloadFTP();
		$pathFileLocal .= $subDirExtra;
		
		if (!file_exists($pathFileLocal)) {
			if (!ExjFile::MkDir($pathFileLocal)) {
				$this->_setError("No se pudo crear el directorio: $pathFileLocal");
				return false;
			};
		}
		
		if (!is_writable($pathFileLocal)) {
			if(!ExjFile::ChModFull($pathFileLocal)){
				$this->_setError("No se pudo cambiar a modo de escritura al directorio: $pathFileLocal");
				return false;
			}
		}
		
		$pathFileLocal .= "/$nameFile";
		
		if (!@ftp_get($this->_conn_id, $pathFileLocal, $pathFileRemote, $modeFile)) {
		    $this->_setError("No se pudo descargar el archivo: $nameFile desde el Servidor FTP. Archivo: $pathFileRemote");
		    return false;
		}

		return $pathFileLocal;
	}
	
	public function closeConnection(){
		if (!$this->_conn_id) {
			return ;
		}
		
		ftp_close($this->_conn_id);
		$this->_conn_id = 0;
	}
	
	private function _setError($msgError){
		Exj::SetErrorValidating("ERROR FTP.<br/>$msgError");
		
		$this->closeConnection();
	}
	
	public function isValid() {
		return (!Exj::GetError()->haveError());
	}	
}

?>