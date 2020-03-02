<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUpgradeEditableModel
 */
class AppSysUpgradeEditableModel extends ExjEditableModel {
	const ESTADO_PENDIENTE = -1;
	const ESTADO_EJECUTADO_CODE = 3;
	const ESTADO_EJECUTADO_DB = 6;
	const ESTADO_EJECUTADO_TODO = 15;

	public $id_sys_upg;
	public $version_upg;
	public $file_zip_code;
	public $file_zip_sql;
	public $state_upg;
	public $desc_upg;
	public $id_file_code;
	public $id_file_sql;

	/**
	 * overwrited. Inicio del modelo editable
	 *
	 */
	protected function initEditableModel(){
		$this->enableTransactionOnDestroy();
	}
	
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_sys_upgrades';
		$fieldKey = 'id_sys_upg';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('version_upg', 'Versión de la Aplicación');
		$this->registerFieldStringNullable(
			'file_zip_code', 'Archivo Código Postal'
		);
		$this->registerFieldStringNullable(
			'file_zip_sql', 'Archivo Zip SQL'
		);
		$this->registerFieldInt('state_upg', 'Estado');
		$this->registerFieldIdNullable(
			'id_file_code', 'Id de arcchivo para código'
		);
		$this->registerFieldIdNullable(
			'id_file_sql', 'Id de arcchivo para db'
		);
		$this->registerFieldString('desc_upg', 'Descripción');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    //	$this->registerControlUI($this->_comboVersiones());
    	$this->registerControlUI($this->_comboEstados());
    	$this->registerControlUI(ExjUI::NewTextField('version_upg', 'Versión'));
    	$this->registerControlUI(ExjUI::NewTextArea('desc_upg', 'Descripción'));
    	
    	$this->registerControlUI(ExjUI::NewFileUploadField('file_zip_code', 'Código Zip', true));
    	$this->registerControlUI(ExjUI::NewFileUploadField('file_zip_sql', 'SQL Zip', true));
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		$this->applyValidationClear('file_zip_code');
		$this->applyValidationClear('file_zip_sql');
	}
	
	
	private function _comboEstados(){
		global $exj;
		// $exj->includeHelperCustom('sys_upgrade_ui', 'exj_sys_upgrades');
    	
    	return AppSysUpgradeUIHelper::NewComboSimpleEstados();
	}

	private function _comboVersiones(){
		global $exj;
		// $exj->includeHelperCustom('sys_upgrade_ui', 'exj_sys_upgrades');
    	
    	return AppSysUpgradeUIHelper::NewComboSimpleVersiones();
	}
	
	private $_deleted_id_file_code=null;
	private $_deleted_id_file_sql=null;

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	$this->load($id);
    	if ($this->haveBrokenRules()) {
    		return false;
    	}

    	/*
    	$this->addBrokenRuler("test de delete usr sys");
    	return false;
    	*/
		
    	$this->_deleted_id_file_code = $this->id_file_code;
    	$this->_deleted_id_file_sql = $this->id_file_sql;
    	
    	return true;
    }
    
    
    /**
     * overwrited. Después de eliminar
     *
     * @param int $id
     * @param int $affectedRows
     * @return bool. Retornar false para cancelar el eliminado y adicionar la regla rota
     */
    protected function afterDestroy($id, $affectedRows){
    	
    	if (!$this->_deleted_id_file_code && !$this->_deleted_id_file_sql) {
    		return true;
    	}
    	
		global $exj;
		// $exj->includeModelEditable('file', 'exj_files');
		$fileEditableModel = new AppFileEditableModel(false);
    	
    	if ($this->_deleted_id_file_code) {
    		$fileEditableModel->destroy($this->_deleted_id_file_code);
    	}

    	if ($this->_deleted_id_file_sql) {
    		$fileEditableModel->destroy($this->_deleted_id_file_sql);
    	}

		if ($fileEditableModel->haveBrokenRules()) {
			$this->addBrokenRuler($fileEditableModel->getBrokenRules());
			return false;
		}
    	
    	return true;
    }
    
    
    /**
     * overwrited. Inicio de Guardar
     *
     */
    protected function initSave(){
    	if ($this->isNew()) {
    		$this->state_upg = self::ESTADO_PENDIENTE;
    	}
    	
    	return true;
    }
    

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	return $this->_canSave();
    }
    private function _canSave(){
    	if (!$this->isEmptyField('file_zip_code') && $this->isEmptyField('id_file_code')) {
    		$this->addBrokenRuler("No se ha seteado o cargado el archivo de código");
    		return false;
    	}
    	if (!$this->isEmptyField('file_zip_sql') && $this->isEmptyField('id_file_sql')) {
    		$this->addBrokenRuler("No se ha seteado o cargado el archivo de SQL");
    		return false;
    	}
    	
    	if (!$this->isSettedField('version_upg')) {
    		return true;
    	}
    	
    	if ($this->isNew()) {
	    	if ($this->isEmptyField('file_zip_code') && $this->isEmptyField('file_zip_sql')) {
				$this->addBrokenRuler("No se ha cargado archivo zip de código ni de SQL");
	    		return false;
	    	}
    	}
    	
    	
    	$paramCriteria = new stdClass();
    	$paramCriteria->version_upg = $this->version_upg;

    	/*
    	if ($this->haveBrokenRules()) {
    		echo " aqui xxxxx";
    		return false;
    	}
    	*/
    	
    	global $exj;
    	// $exj->includeModel();
    	
    	$topics=null;
    	$total=0;
		if (!AppSysUpgradeModel::loadListSysUpgrades($topics, $total, $paramCriteria)) {
			return false;
		}
		
		// $db = Exj::InstanceDatabase();
		// $db->writeLastQuery();
		
		if (!$total) {
			return true;
		}
		
		$item = $topics[0];
		if ($item->id_sys_upg == $this->id) {
			return true;
		}
		
		$this->addBrokenRuler("Ya se encuentra registrado.<br/>Versión: $item->version_upg<br/>Código: $item->file_zip_code,<br/>DB: $item->file_zip_sql");
		return false;
    }
    
    public function executeCode(){
    	$fullPathFile = '';
    	$nameFile = ''; // sin extensión
    	if (!$this->_prepareExecuteZips('id_file_code', $fullPathFile, $nameFile)) {
    		return false;
    	}

    	$pathRootApp = Exj::GetPathBase();
    	
   // 	$this->addBrokenRuler("test ". __METHOD__);
   // 	return false;
   		
   		// $reflector = new \ReflectionClass('PclZip');
		// echo $reflector->getFileName();
    	
    	// chown(filename, 'apache')

    	$fileZip = new PclZip($fullPathFile);
    	// PCLZIP_OPT_SET_CHMOD
    	$resExtract = $fileZip->extract(
    		PCLZIP_OPT_PATH, $pathRootApp,
    		PCLZIP_OPT_REMOVE_PATH, $nameFile,
    		PCLZIP_OPT_REPLACE_NEWER
    	);
    	
		if ($resExtract == 0) {
			$this->addBrokenRuler("ERROR UPGRADE PCLZIP: ".$fileZip->errorInfo(true));
		    return false;
		}

		$strCommand = "composer dump --working-dir=$pathRootApp";
		$strCommand = escapeshellcmd($strCommand);

		$ret = shell_exec($strCommand);
		// $ret = system($strCommand);
		if ($ret) {
			$this->addBrokenRuler("EJECUTANDO composer dump: $ret");
			return false;
		}

		if (!$this->_afterExecuteZips(self::ESTADO_EJECUTADO_CODE)) {
			return false;
		}
    	
    	return true;
    }
    
    private function _prepareExecuteZips($nameFieldFile, &$path_file, &$name_file){
    	$this->load();
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	if (!$this->$nameFieldFile) {
    		$this->addBrokenRuler("No se ha cargado archivo del campo: $nameFieldFile");
    		return false;
    	}
    	

    	$file = new AppFileEditableModel(false);
    	$file->setValueId($this->$nameFieldFile);
    	$file->load();
    	if ($file->haveBrokenRules()) {
    		$this->addBrokenRuler($file->getBrokenRules());
    		return false;
    	}
    	if (!$file->path_file) {
    		$this->addBrokenRuler("No se ha cargado de forma adecuada el archivo");
    		return false;
    	}
    	
    	$path_file = $file->path_file;
    	$name_file = $file->name_file;
    
    	if (!Exj::IsReleasedApp()) {
    		$this->addBrokenRuler(
    			"No se puede ejecutar actualizaciones en el entorno de desarrollo.<br/>URL: ". Exj::GetServerURLClient()
    		);
    		return false;
    	}
    	
    	
    	return true;
    }
    
    private function _afterExecuteZips($newState){
    	if ($this->state_upg != $newState) {
	    	if ($this->state_upg == self::ESTADO_PENDIENTE) {
	    		$this->state_upg = $newState;
	    	}
	    	else {
	    		$this->state_upg = self::ESTADO_EJECUTADO_TODO;
	    	}
    	}
    	
    	$this->save();
    	
    	return !$this->haveBrokenRules();
    }

    public function executeSql($usrDB='', $pwdDB=''){
    	$fullPathFile = '';
    	$nameFile = ''; // sin extensión
    	if (!$this->_prepareExecuteZips('id_file_sql', $fullPathFile, $nameFile)) {
    		return false;
    	}
    	
    	$nameDir = dirname($fullPathFile);
    	if (!$nameDir || ($nameDir == '.') || ($nameDir == '..')) {
    		$this->addBrokenRuler("No se pudo obtener el dir del archivo sql");
    		return false;
    	}
    	$nameDir .= '/dbtemp/';
    	

    	$fileZip = new PclZip($fullPathFile);
    	
		if ($fileZip->extract(PCLZIP_OPT_PATH, $nameDir, PCLZIP_OPT_REMOVE_PATH, $nameFile, PCLZIP_OPT_REPLACE_NEWER) == 0)
		{
			$this->addBrokenRuler($fileZip->errorInfo(true));
		    return false;
		}
		
		$filesSQL = array();
		if ($dh = opendir($nameDir)) {
	        while (($file = readdir($dh)) !== false) {
	        	if ($file == '.' || $file == '..') {
	        		continue;
	        	}
	        	$fullPathFileSql = $nameDir;
	        	$fullPathFileSql .= $file;
	        	
	        	if (filetype($fullPathFileSql) == 'dir') {
	        		Exj::WriteLn("No se toma en cuenta a dir: $fullPathFileSql");
	        		continue;
	        	}
	        	
	        	$partesFile = pathinfo($file);
	        	if (strtolower($partesFile['extension']) != 'sql') {
	        		Exj::WriteLn("El archivo <b>$file</b> no tiene la extensión sql");
	        		continue;
	        	}
	        	
	        	$filesSQL[] = $fullPathFileSql;
	        	
	            // Exj::WriteLn("filename: $file");
	        }
	        closedir($dh);
	    }
	    
	    if (count($filesSQL) == 0) {
	    	$this->addBrokenRuler("En el archivo <b>$nameFile</b> no existen archivos sql.<br/><b>Dir</b>: $nameDir");
	    	return false;
	    }
		
	    $db = Exj::InstanceDatabase();

	    if ($usrDB) {
	    	$db->changeUserPwd($usrDB, $pwdDB);
	    	// echo "<br>Cambio de usr: $usrDB pwd: $pwdDB a DB";
	    }
	    
	    foreach ($filesSQL as $fileSQL) {
	    //	Exj::WriteLn("fileSQL: $fileSQL");
			$handle = fopen($fileSQL, "rb");
			$contentsSQL = fread($handle, filesize($fileSQL));
			fclose($handle);
			
			if (!$contentsSQL) {
				Exj::WriteLn("El archivo: <b>$fileSQL</b> está vacio");
				continue;
			}
			
		//	Exj::WriteLn($contentsSQL);
			
			$db->setQuery($contentsSQL);
			$db->queryBatch();
			if ($db->getErrorMsg()) {
				$this->addBrokenRuler($db->getErrorMsg());
				break;
			}
			
			if (!unlink($fileSQL)) {
				Exj::WriteLn("No se pudo eliminar: <b>$fileSQL</b>");
			}
	    }
	    
	    if ($this->haveBrokenRules()) {
	    	return false;
	    }

	    // -----------------------------------------------------------
		if (!$this->_afterExecuteZips(self::ESTADO_EJECUTADO_DB)) {
			return false;
		}
		
    	
    	return true;
    }
    
}

?>