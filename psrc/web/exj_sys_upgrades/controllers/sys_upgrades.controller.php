<?php
/**
 * @class AppSysUpgradesController
 * Controlador para Actualizaciones
 */
class AppSysUpgradesController extends ExjController {
	
	
	/**
	 * Inicio del Controlador
	 *
	 */
	protected function initController(){
	}
	
	/**
	 * overwrited. Carga de Archivo. Definiciones
	 *
	 * @param string $FILETYPE_MODULE
	 * @param string $subFolder
	 * @param bool $addFolderUser
	 * @param string $msgError
	 * @return bool
	 */
	public function uploadFileConfig(&$FILETYPE_MODULE, &$subFolder, &$addFolderUser, &$msgError)
	{
		$FILETYPE_MODULE = ExjHelperFile::ARCHIVOTIPO_MODULO_UPGRADES;
		$addFolderUser = false;
		
		$version_upg = $this->getParam('version_upg', 0);
		if (!$version_upg) {
			$msgError = "Versión de actualización no indicada";
			return false;
		}
		
		if (!$this->_validateNameFiles($msgError, $version_upg)) {
			return false;
		}
		
		$subFolder = 'upg_'. $version_upg;
		
		return true;
	}
	
	private function _validateNameFiles(&$msgError, $version_upg){
		$file_zip_code = $this->getParamFromDataChanged('file_zip_code');
		if ($file_zip_code) {
			if (strpos($file_zip_code, $version_upg) === false) {
				$msgError = "El archivo a cargar <b>$file_zip_code</b>, debe tener la versión de actualización: <b>$version_upg</b>";
				return false;
			}
		}

		$file_zip_sql = $this->getParamFromDataChanged('file_zip_sql');
		if ($file_zip_sql) {
			if (strpos($file_zip_sql, $version_upg) === false) {
				$msgError = "El archivo a cargar <b>$file_zip_sql</b>, debe tener la versión de actualización: <b>$version_upg</b>";
				return false;
			}
		}
		
		return true;
	}

	/**
	 * overwrite. Después que se a cargado el archivo. Soportado para transacciones DB
	 *
	 * @param int $id_file
	 * @param string $msgError
	 * @param bool $showMsgSuccess
	 * @param string $pathFile
	 */
	public function uploadFileAfter($id_file, &$msgError, &$showMsgSuccess, $pathFile, $nameFileUI) {
		// $version_upg = $this->getParam('version_upg', 0);
	//	$nameFile = basename($pathFile);
	//	$msgError = " nameFile: $nameFile ";
	
		switch ($nameFileUI) {
			case 'file_zip_code':
				$this->paramDataChanged->id_file_code = $id_file;	
			break;
		
			case 'file_zip_sql':
				$this->paramDataChanged->id_file_sql = $id_file;
			break;
		}
	}	
	
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppSysUpgradeModel::loadListSysUpgrades($topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}


	/**
	 * override. Creación
	 */
	public function create() {
		$response = new ExjResponse();
		global $exj;
		$exj->returnHTML = false;
		
		
		if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		$responseUploadError = null;
		$this->_uploadFiles($responseUploadError);
		if ($responseUploadError) {
			return $responseUploadError;
		}
		
		
		return AppSysUpgradeModel::saveSysUpgrade(0, $this->paramDataChanged, $this->paramData);
	}
	
	private function _uploadFiles(&$responseUploadError){
		if ($this->getParamFromDataChanged('file_zip_code')) {
			$responseUploadError = $this->uploadFile('file_zip_code');
			if ($responseUploadError->haveMsgError()) {
				return $responseUploadError;
			}
		}
		if ($this->getParamFromDataChanged('file_zip_sql')) {
			$responseUploadError = $this->uploadFile('file_zip_sql');
			if ($responseUploadError->haveMsgError()) {
				return $responseUploadError;
			}
			
		}
		$responseUploadError = null;
		return true;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		global $exj;
		$exj->returnHTML = false;
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$responseUploadError = null;
		$this->_uploadFiles($responseUploadError);
		if ($responseUploadError) {
			return $responseUploadError;
		}
		
		return AppSysUpgradeModel::saveSysUpgrade($this->id, $this->paramDataChanged, $this->paramData);
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppSysUpgradeModel::destroy($this->id, 'AppSysUpgradeEditableModel', $response, '', 'sys_upgrade');
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppSysUpgradesReportModel
	 */
	public function getDataReport(){		
		$dataReport = new AppSysUpgradesReportModel();
		
		return $dataReport;
	}
	
	public function executeFileZip(){
		$response = new ExjResponse();
		
		$id_sys_upg = $this->getParamId('id_sys_upg');
		$executeCode = $this->getParam('executeCode', -1);
		$executeSql = $this->getParam('executeSql', -1);
		
		if ($executeCode == -1 && $executeSql == -1) {
			$response->setMsgError("No se ha indicado el comando a ejecutar!");
			return $response;
		}
		$executeCode = intval($executeCode);
		$executeSql = intval($executeSql);
		if (!$executeCode && !$executeSql) {
			$response->setMsgError("No se ha indicado el comando a ejecutar <b>Código</b> ni <b>SQL</b>!");
			return $response;
		}
		
		
		$upgrade = new AppSysUpgradeEditableModel(false);
		$upgrade->setValueId($id_sys_upg);
		
		if ($executeCode) {
			$upgrade->executeCode();
		}
		if ($executeSql) {
			$usrDB = $this->getParamFromDataChanged('usr');
			$pwdDB = $this->getParamFromDataChanged('pwd');

			$upgrade->executeSql($usrDB, $pwdDB);
		}
		
		if ($upgrade->haveBrokenRules()) {
			$response->setMsgError($upgrade->getBrokenRules());
			return $response;
		}
		
		$response->setMsgInfo("Se ha ejecutado con éxito");
		return $response;
	}

	
	public function execScriptSql(){
		$response = new ExjResponse();

		$scriptSQL = $this->getParam('scrbsql');
		if (!$scriptSQL) {
			$response->setMsgError("Script requerido");
			return $response;
		}
		$scriptSQL = trim($scriptSQL);
		if (strlen($scriptSQL) <= 3) {
			$response->setMsgError("Script no válido");
			return $response;
		}

		if (preg_match('/(drop)\s+(database)/i', $scriptSQL)) {
			$response->setMsgError("No se permite eliminar base de datos");
			return $response;
		}


		$usr = $this->getParam('usr', null);
		$pwd = $this->getParam('pwd', null);
		if ($pwd == null) {
			$pwd = '';
		}

		AppSysUpgradeModel::ExecScriptSql($response, $scriptSQL, $usr, $pwd);

		if (!$response->haveMsgText()) {
			$response->setMsgNotify("Script ejecutado con éxito");
		}
		
		return $response;
	}

	public function backupDB(){
		$response = new ExjResponse();

		$path_mysqldump = trim($this->getParam('path_mysqldump', ''));
		if (!$path_mysqldump) {
			$response->setMsgError("No se indicó ruta de programa de backup!");
			return $response;
		}

		if (!file_exists($path_mysqldump)) {
			$response->setMsgError("No se existe: $path_mysqldump");
			return $response;
		}

		if (!is_file($path_mysqldump)) {
			$response->setMsgError("No es un archivo: $path_mysqldump");
			return $response;
		}

		$usrDB = trim($this->getParamFromDataChanged('usr'));
		$pwdDB = $this->getParamFromDataChanged('pwd');
		if (!$usrDB) {
			$response->setMsgError("Usuario es requerido");
			return $response;
		}

		AppSysUpgradeModel::BackupDB($response, $path_mysqldump, $usrDB, $pwdDB);

		return $response;
	}

	public function searchProgBackup(){
		$response = new ExjResponse();
		$pathProgBackup = trim($this->getParam('path_probk', ''));
		if (!$pathProgBackup) {
			$response->setMsgError("No se indicó ruta/programa de backup");
			return $response;
		}

		$pathProgBackup = str_replace('\\', '/', $pathProgBackup);
		if (!is_dir($pathProgBackup)) {
			$response->setMsgError("Directorio no existe: $pathProgBackup");
			return $response;
		}

		$pathReal = realpath($pathProgBackup);
		if (!$pathReal) {
			$response->setMsgError("Directorio real no existe: $dirSearch");
			return $response;
		}
		$pathReal = str_replace('\\', '/', $pathReal);

		$namProg = 'mysqldump.exe';

		// echo "<br>pathReal: " . htmlentities($pathReal);
		AppSysUpgradeModel::SearchProgBackup($response, $pathReal, $namProg);


		return $response;
	}

	public function getDataFilesBks() {
		$response = new ExjResponse();
		
		$topics = AppSysUpgradesData::GetDataFilesBks($msgError);
		if ($msgError) {
			$response->setMsgError($msgError);
		}
		
		$response->setDataObject($topics);
		return $response;
	}

	public function deleteFileBk() {
		$response = new ExjResponse();

		$nameFile = $this->getParam('nameFile', '');

		AppSysUpgradeModel::DeleteFileBk($response, $nameFile);
		return $response;
	}

	public function DownloadFile(){
		$response = new ExjResponse();
		$response->fixFormatHTML();
		$response->addLinkRegresarSistemaInHTML();

		$nameFile = $this->getParam('nameFile', '');

		AppSysUpgradeModel::DownloadFile($response, $nameFile);
		return $response;
	}

	public function rebuildJs(){
		$response = new ExjResponse();

		$outFiles = ExjResource::ReBuildAllFilesJsAppPack();
		if (empty($outFiles)) {
			return $response->setMsgInfo("Ningún archivo js!", 'ERROR');
		}

		return $response->setMsgInfo(
			"Archivos reconstruídos:<br>" . implode('<br>', $outFiles)
		);
	}
}

?>