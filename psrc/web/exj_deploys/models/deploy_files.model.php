<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDeployFilesModel
 */
class AppDeployFilesModel extends ExjClass {
	
	private $_pathRoot='';
	private $_folderApp='';
	private $_version='1.0.0';
	
	private $_pathRelease='';
	private $_nameFileBKDB=null;
	private $_uriRelease='';
	private $_subPathRelease='';
	
	private $_commentJSDeploy='';
	private $_commentPHPDeploy='';
	
	private $_numFilesJsEncoded=0;
	private $_sizeTotalFilesJsEncoded=0;
	
	private $_numFilesPHPEncoded=0;
	private $_sizeTotalFilesPHPEncoded=0;

	private $_numFilesCopiedJS = 0;
	private $_numFilesCopiedPHP = 0;
	private $_numFilesCopiedIMG = 0;
	private $_numFilesCopiedCSS = 0;
	private $_numFilesCopiedOTROS = 0;
	
	private $_subPathPreProduccion;
	private $_pathPreProduccion;
	private $_uriPreProduccion;
	private $_folderPreProduccion;
	
	private $_PREFIXPACKJS;
    private $_minifierHelperJs=null;
	
	public function __construct($version){
		$this->_version = $version;

        $this->_PREFIXPACKJS = ExjResource::PREFIX_FILEJS_PACK;
		
    	$this->_splitPath($this->_pathRoot, $this->_folderApp);

    	// $this->_folderPreProduccion = $this->_folderApp;
    	$this->_folderPreProduccion = trim(Exj::GetValueCfg('folderDeploy', ''));
        if (!$this->_folderPreProduccion) {
            $this->_folderPreProduccion = ExjDatabase::GetNameDB();
        }
        
        $this->_subPathRelease = ExjString::ConcatPaths(
            'release', $this->_folderPreProduccion
        );

        $this->_subPathRelease = ExjString::ConcatPaths(
            $this->_subPathRelease, 'ver'.$this->_version
        );
    	
    	$this->_pathRelease = $this->_pathRoot . '/'.$this->_subPathRelease;
    	$this->_uriRelease = $this->_getURIServer() . '/' . $this->_subPathRelease;
    	

    	$this->_subPathPreProduccion = 'release/'.$this->_folderPreProduccion.'/produccion';
    	$this->_pathPreProduccion = $this->_pathRoot . '/'.$this->_subPathPreProduccion;
    	$this->_uriPreProduccion = $this->_getURIServer() . '/' . $this->_subPathPreProduccion;
    	

    	ExjEvent::Fire(__FUNCTION__, array($this->_pathRelease), $this);

        if ($this->_validatePathDir($this->_pathRelease)->haveError()) {
    		return false;
    	}
    	
    	
    	// $nameProduct = strtoupper($this->_folderApp);
    	$nameProduct = Exj::GetTitleApp();
    	$this->_commentJSDeploy  = "/*";
    	$this->_commentJSDeploy .= "\r\n $nameProduct $this->_version";
    	$this->_commentJSDeploy .= "\r\n EasySoft Service 2013 Loja - Ecuador";
    	$this->_commentJSDeploy .= "\r\n Autor: Byron Vinicio Córdova Mora";
    	$this->_commentJSDeploy .= "\r\n Todos los Derechos Reservados. Se prohibe la decodificación del Archivo";
    	$this->_commentJSDeploy .= "\r\n";
    	$this->_commentJSDeploy .= "*/";
    	$this->_commentJSDeploy .= "\r\n";
    	
    	$this->_commentPHPDeploy = "<?php";
    	$this->_commentPHPDeploy .= "\r\n";
    	$this->_commentPHPDeploy .= $this->_commentJSDeploy;
    	$this->_commentPHPDeploy .= "?>\r\n";
    	
    	// TODO: PRUEBAS

		// expandimos el tiempo de máxima ejecución
		set_time_limit(180);
	}
	
	/**
	 * Construye el deploy en un directorio diferente
	 *
	 * @return bool false si ha ocurrido algún error
	 */
    public function buildDeployFiles(){
    	$this->writeLogFile(__METHOD__ . " INICIO. Ln: " . __LINE__);
    	$this->writeLogFile(debug_backtrace());
    	
    	if ($this->haveError()) {
    		return false;
    	}

        $this->minifyFilesJs();

        ExjEvent::Fire('beforeCopyFilesFromRoot', array(), $this);

        $depPathRootBase = new AppDeployCfgPathRoot(Exj::GetPathBase());

        $depPathRootBase->allowFolderRootNames = array(
            'app',
            'templates',
            'api',
            'boot'
        );

        $depPathRootBase->allowCopyFilesExtras = array(
            'libraries/vendor/composer/installed.json'
            /* 'plugins/authentication/rideclientes.php' */
        );


        $depPathRootBase->exceptCopyFileExts = array(
            'db',
            'txt',
            'php-dist',
            'sql',
            'doc',
            'xls',
            'pdf'
        );

        $depPathRootBase->exceptCopyFileNames = array(
            '-debug.',
            '-src.',
            '.gitignore',
            'thumbs.db',
            'changelog.php',
            'configuration.php',
            'install.php',
            'configuration.php-dist',
            'json.php',
            'composer.lock',
            'app-minify.js',
            'app-minify-cfg.json',
            'package.json',
            'package-lock.json',
            'CHANGELOG.php',
            'COPYRIGHT.php',
            'CREDITS.php'
        );

        $tplSy = ExjResource::GetNameTemplateSys();

        $depPathRootBase->exceptCopyFolderNames = array(
            '.git',
            'extjs331',
            'extjs340',
            'pkg_phpexcel',
            'pkg_phpword',
            'adodb5',
            '/src',
            'pkg_pclzip',
            '/tests',
            '_bk',
            '_bk1',
            '_bk2',
            '/templates/system',
            "/templates/$tplSy/js"
        );

        $depPathRootBase->exceptEncodeFileNames = array(
            'app-minify.js',
            'modules.php',
            'pagination.php',
            'mod_login.php',
            'ja_vars.php',
            'component.php',
            'default_item.php',
            'offline.php',
            'error.php',
            '_item.php',
            'copyright.php'
        );

        $depPathRootBase->exceptEncodeFolderNames = array(
            "/templates/$tplSy/html/*",
            "/templates/$tplSy/js/*"
        );

        $depPathRootBase->allowOnlyOfuscarFileNames = array(
            "ja_templatetools.php",
            "index.php"
        );
    
       $depPathRootExj = new AppDeployCfgPathRoot(Exj::GetPathDirProviderExj());
       $depPathRootExj->copyProps($depPathRootBase);
       $depPathRootExj->allowFolderRootNames = array();
       $depPathRootExj->exceptCopyFileExts[] = 'js';
       $depPathRootExj->exceptCopyFolderNames[] = '/js';
       $depPathRootExj->exceptCopyFolderNames[] = 'exj_deploys';
       $depPathRootExj->exceptCopyFolderNames[] = 'exj_components';
       $depPathRootExj->exceptCopyFolderNames[] = 'exj_component_tpls';

       $depPathRootExj->exceptEncodeFileNames[] = 'exj_main.js';
       $depPathRootExj->exceptEncodeFileNames[] = 'exj_base.js';
       $depPathRootExj->exceptEncodeFileNames[] = $this->_PREFIXPACKJS.'exj_base.js';
       $depPathRootExj->exceptEncodeFileNames[] = $this->_PREFIXPACKJS.'exj_main.js';

       $depPathRootExj->allowOnlyOfuscarFileNames = array();
       $depPathRootExj->allowCopyFilesExtras = array();
       $depPathRootExj->exceptEncodeFolderNames = array(
            '/joomla',
            '/joomla/*'
        );
       $depPathRootExj->setSubFolderDest('libraries/vendor/easysoftservice/exj');

        $pathsBasesOrigs = array(
            $depPathRootBase,
            $depPathRootExj
        );

    	foreach ($pathsBasesOrigs as $depPath) {
            if ($this->_copyFilesFromRoot($depPath)->haveError()) {
                break;
            }

            if ($this->_copyFilesExtras($depPath)->haveError()) {
                break;
            }
        }

        if ($this->haveError()) {
            return false;
        }

        ExjEvent::Fire('afterCopyFilesFromRoot', array(), $this);

        // $this->_setError("Prueba deploy xxx");
        // return false;	
    	
    	if ($this->_buildBackupDB()->haveError()) {
    		return false;
    	}

        if ($this->_minifierHelperJs) {            
            $pJs = ExjString::ConcatPaths(
                $this->_pathRelease . '/app',
                ExjResource::GetPathRelativeHelperPublicJs()
            );

            if ($this->_validatePathDir(dirname($pJs))->haveError()) {
                return false;
            }

            $this->writeLogFile(__METHOD__ . " minifierHelperJs->minify pJs: $pJs");

            $this->_minifierHelperJs->minify(
                $pJs
            );
        }

        ExjEvent::Fire('afterBuildDeployFiles', array(), $this);
    	
        return (!$this->haveError());
    } // buildDeploy

    protected function minifyFilesJs(){
        ExjEvent::Fire('beforeMinifyFilesJs', array(), $this);

        ExjResource::WriteFileCfgMinify();
        shell_exec('node app-minify');

        ExjEvent::Fire('afterMinifyFilesJs', array(), $this);

        ExjResource::ReBuildAllFilesJsAppPack();
    }
    
    private function _buildBackupDB()
    {
		$bk = new BackupMySQL(ExjDatabase::GetNameDB(), $this->_pathRelease);

		$bk->executeBK();
		if ($this->haveError()) {
			return $this;
		}

    	$this->_nameFileBKDB = $bk->getNameFileBKDB();
		
		return $this;	
    }
    
    public function copyToPreProduction($pathDeploy, $pathPreProduccion){
    	if ($pathDeploy == $pathPreProduccion) {
    		$this->_setError("No se puede copiar al mismo directorio:<br/>$pathDeploy");
    		return false;
    	}
    	
    	if ($this->_pathRoot == $pathPreProduccion) {
    		$this->_setError("El path de destino no puede ser el mismo del código fuente:<br/>$this->_pathRoot");
    		return false;
    	}
    	
    	if ($this->_validatePathDir($pathPreProduccion)->haveError()) {
    		return false;
    	}
    	
    	if (!file_exists($pathDeploy)) {
    		$this->_setError("No existe el dir origen: $pathDeploy");
    		return false;
    	}
    	
    	$success = true;
    	
        $d = dir($pathDeploy);
        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            
            $pathOrig = $pathDeploy . '/' . $entry;
            $pathDest = $pathPreProduccion . '/' . $entry;
            
            if (is_dir($pathOrig)) {            	
                $this->copyToPreProduction($pathOrig, $pathDest);
                continue;
            }
                       
           ExjEvent::Fire(__FUNCTION__, array($pathOrig, $pathDest), $this);
            
            if (!copy($pathOrig, $pathDest)) {
            	$success = false;
            	$this->_setError("No se pudo copiar archivo: $entry<br/>$pathOrig <br/>hacia:<br/>$pathDest");
            	break;
            }            
        }

        $d->close();
    	
    	return $success;
    }
    
    private function _createSubDirs($pathDir){
    	if (!$pathDir) {
    		$this->_setError("Error Creando sub-directorios. El path no existe");
    		return false;
    	}
    	
    	if (file_exists($pathDir)) {
    		return true;
    	}
    	else{
    		if (!ExjFile::MkDirSilent($pathDir)) {
    			return $this->_createSubDirs(dirname($pathDir));
    		}
    		
    		return true;
    	}
    }
    
    private function _copyFilesExtras(AppDeployCfgPathRoot $depPathRoot)
    {
    	if (empty($depPathRoot->allowCopyFilesExtras)) {
    		return $this;
    	}
    	
    	$pathBaseOrig = $depPathRoot->getDirRoot();
        $pathBaseDest = $depPathRoot->rendererFolterDest($this->_pathRelease);
    	
    	foreach ($depPathRoot->allowCopyFilesExtras as $fullPathFilePHPExtra) {
    		$nameFile = basename($fullPathFilePHPExtra);

            /*
            if (!$depPathRoot->canCopyFile($nameFile)) {
            	continue;
            }
            */
            
            $pathOrig = $pathBaseOrig . '/' . $fullPathFilePHPExtra;
            $pathDest = $pathBaseDest . '/' . $fullPathFilePHPExtra;

            $this->writeLogFile(__FUNCTION__." pathOrig: $pathOrig");
            
            if (!file_exists($pathOrig)) {
            	$this->_setError(
                    "Archivos Extras PHP. No se existe el archivo : $pathOrig"
                );
            	
            	break;
            }
                        
            $pathDirDest = dirname($pathDest);

            if (!ExjFile::ValidateDir($pathDirDest)) {
            	$this->_setError(
                    "No se pudo crear el directorio destino: $pathDirDest"
                );
                
                break;
            }
            
            if ($this->_copyFile($depPathRoot, $pathOrig, $pathDest)->haveError()) {
            	break;
            }            
    	}
    	
    	return $this;
    }
    
    public function writeLogFile($strline){
    	// para dar seguimiento
    	return $this;
    	
    	if ($strline === null) {
    		$strline = '';
    	}
    	
    	if (is_array($strline) || is_object($strline)) {
    		if (is_array($strline)) {
    			$strLines = array();
    			foreach($strline  as $back) {
					if (isset($back['file'])) {
						$strLines[] = $back['file'] . ': ' . $back['line'];
					}
					else {
						$strLines[] = var_export($back, true);
					}
				}
				
				$strline = implode("\n", $strLines);
    		}
    		else {
    			$strline = var_export($strline, true);
    		}
    	}
    	
    	$strline = str_replace(array("<br>", "<br\>"), "\n", $strline);
    	    	
        ExjLog::info("AppDeployFilesModel. ".$strline);
        return $this;
    }
    
    private function _copyFilesFromRoot(AppDeployCfgPathRoot $depPathRoot)
    {
    	
    	$pathBaseOrig = $depPathRoot->getDirRoot();
    	$pathBaseDest = $this->_pathRelease;

        $pathBaseDest = $depPathRoot->rendererFolterDest($pathBaseDest);
    	
    	$this->writeLogFile(
            __METHOD__ . " INICIO. ORIG: $pathBaseOrig DEST: $pathBaseDest"
        );

        ExjEvent::Fire(__FUNCTION__, array($pathBaseDest), $this);
    	
        $d = dir($pathBaseOrig);
        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            $this->writeLogFile("_copyFilesFromRoot entry: $entry");
            
            $pathOrig = $pathBaseOrig . '/' . $entry;
            $pathDest = $pathBaseDest . '/' . $entry;
            
            if (is_dir($pathOrig)) {
                if (!$depPathRoot->allowFolderRoot($entry)) {
                    $this->writeLogFile("No se copia el dir root: $entry");
                    continue;
                }

                if(empty($depPathRoot->allowFolderRootNames)){
                    if (!$depPathRoot->canCopyFolder($entry, $pathOrig)) {
                        $this->writeLogFile("2. No se copia el dir root: $entry");
                        continue;
                    }
                }

	            $this->writeLogFile(
                    "ANTES DE _copyAllDir $pathOrig"
                );

                // ccc
                if ($this->_copyAllDir($depPathRoot, $pathOrig, $pathDest)->haveError())
                {
                    break;
                }
                
                continue;
            }

            if (!$depPathRoot->canCopyFile($entry)) {
            	continue;
            }
            
            $this->writeLogFile("Copiando. Archivo: $pathOrig");
            if ($this->_copyFile($depPathRoot, $pathOrig, $pathDest)->haveError()) {
            	break;
            }
        }

        $d->close();
    	
        $this->writeLogFile(__METHOD__ . " FINALIZADO");
    	
    	return $this;
    }
    
    private function _copyAllDir($depPathRoot, $pathDirOrig, $pathDirDest)
    {    	
        $d = dir($pathDirOrig);
        
	//	$this->writeLogFile("_copyAllDir. $pathDirDest INICIO");
        
        if (!file_exists($pathDirDest)) {
        	$this->writeLogFile(
                "_copyAllDir. No existe el dir $pathDirDest SE LO CREA"
            );

        	ExjFile::MkDirRecursive($pathDirDest);
        }
        
        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            
            $pathOrig = $pathDirOrig . '/' . $entry;
            $pathDest = $pathDirDest . '/' . $entry;
            
            if (is_dir($pathOrig)) {
	            if (!$depPathRoot->canCopyFolder($entry, $pathOrig)) {
	            	continue;
	            }
            	
                $this->_copyAllDir($depPathRoot, $pathOrig, $pathDest);
            }
            
            if (!$depPathRoot->canCopyFile($entry)) {
            	continue;
            }
            
            if ($this->_copyFile($depPathRoot, $pathOrig, $pathDest)->haveError())
            {
            	break;
            }
        }

        $d->close();
        
        return $this;
    }
    
	public function getNumFilesCopiedCSS(){
		return $this->_numFilesCopiedCSS;
	}
	public function getNumFilesCopiedIMG(){
		return $this->_numFilesCopiedIMG;
	}
	public function getNumFilesCopiedJS(){
		return $this->_numFilesCopiedJS;
	}
	public function getNumFilesCopiedOTROS(){
		return $this->_numFilesCopiedOTROS;
	}
	public function getNumFilesCopiedPHP(){
		return $this->_numFilesCopiedPHP;
	}
	
	private function _encodeJavaScript($codeJs){
        $packer = new Tholu\Packer\Packer($codeJs, 'Normal', true, false, true);
        return $packer->pack();
	}

	private function _encodePHP($codePHP, &$numWordsOfuscadas, $onlyOfuscar=false){
		$packerPHP = new PHPPacker($codePHP, 'Normal', true, true, $onlyOfuscar);
		
		$packerPHP->addOfuscarVar('$exj');
		$packerPHP->addOfuscarVar('$nameField');
		$packerPHP->addOfuscarVar('$numFilaActual');
		$packerPHP->addOfuscarVar('$addControlesUI');
		$packerPHP->addOfuscarVar('$dbQuery');
		$packerPHP->addOfuscarVar('$paramsCriteria');
		$packerPHP->addOfuscarVar('$name_component');
		$packerPHP->addOfuscarVar('$nameFileController');
		$packerPHP->addOfuscarVar('$nameClassModel');
		$packerPHP->addOfuscarVar('$nameEditableModel');
		$packerPHP->addOfuscarVar('$nameListModel');
		$packerPHP->addOfuscarVar('$fechaNacimiento');
		$packerPHP->addOfuscarVar('$ano_diferencia');
		$packerPHP->addOfuscarVar('$valuePercent');
		$packerPHP->addOfuscarVar('$itemsTopbarExtrasLeft');
		$packerPHP->addOfuscarVar('$itemsMenuReporte');
		$packerPHP->addOfuscarVar('$nameBtnReporte');
		$packerPHP->addOfuscarVar('$menuReporte');
		$packerPHP->addOfuscarVar('$isModeDebugUI');
		$packerPHP->addOfuscarVar('$componentsHelpers');
		$packerPHP->addOfuscarVar('$nameComponent');
		$packerPHP->addOfuscarVar('$pathComponets');
		$packerPHP->addOfuscarVar('$nameFileJs');
		$packerPHP->addOfuscarVar('$namesFiles');
		$packerPHP->addOfuscarVar('$decimales');
		$packerPHP->addOfuscarVar('$applyTrim');
		$packerPHP->addOfuscarVar('$ui_fields');
		$packerPHP->addOfuscarVar('$ui_columns');
		$packerPHP->addOfuscarVar('$uriBase');
		$packerPHP->addOfuscarVar('$srcScript');
		$packerPHP->addOfuscarVar('$scripts');
		$packerPHP->addOfuscarVar('$onlyName');
		$packerPHP->addOfuscarVar('$extFile');
		$packerPHP->addOfuscarVar('$nameFile');
		$packerPHP->addOfuscarVar('$forceFit');
		$packerPHP->addOfuscarVar('$dirName');
		$packerPHP->addOfuscarVar('$fileBaseSystem');
		
		$packerPHP->addOfuscarVar('$pathFramework');
		$packerPHP->addOfuscarVar('$msgError');
		$packerPHP->addOfuscarVar('$exceptPrefix');
		$packerPHP->addOfuscarVar('$valueDefault');
		$packerPHP->addOfuscarVar('$message');
		$packerPHP->addOfuscarVar('$fields');
		$packerPHP->addOfuscarVar('$varsObj');
		$packerPHP->addOfuscarVar('$actionsOk');
		$packerPHP->addOfuscarVar('$actionOk');
		$packerPHP->addOfuscarVar('$itemAccessUsr');
		$packerPHP->addOfuscarVar('$idAccess');
		// $packerPHP->addOfuscarVar('$id_company'); // esta es un var de clase
		$packerPHP->addOfuscarVar('$fieldsExtras');
		$packerPHP->addOfuscarVar('$listMain');
		$packerPHP->addOfuscarVar('$componentHelper');
		$packerPHP->addOfuscarVar('$paramsQuery');
		$packerPHP->addOfuscarVar('$pathFile');
	//	$packerPHP->addOfuscarVar('$estados');
	//	$packerPHP->addOfuscarVar('$estado');
		$packerPHP->addOfuscarVar('$charSeparatorLine');
		$packerPHP->addOfuscarVar('$ClassController');
		$packerPHP->addOfuscarVar('$nameClassEditable');
		$packerPHP->addOfuscarVar('$nameClassList');
		$packerPHP->addOfuscarVar('$paramEncodeJson');
		$packerPHP->addOfuscarVar('$compareValues');
		
		$packerPHP->addOfuscarVar('$isLoad');
		$packerPHP->addOfuscarVar('$prefix');
		$packerPHP->addOfuscarVar('$value1');
		$packerPHP->addOfuscarVar('$value2');
		$packerPHP->addOfuscarVar('$date1');
		$packerPHP->addOfuscarVar('$date2');
		$packerPHP->addOfuscarVar('$value');
		$packerPHP->addOfuscarVar('$items'); // probar
		$packerPHP->addOfuscarVar('$criteria');
		$packerPHP->addOfuscarVar('$dateRaw');
		$packerPHP->addOfuscarVar('$itemRadio');
		
		// CLASES
		$packerPHP->addOfuscarClass('ExjTransferCharacters');
		$packerPHP->addOfuscarClass('ExjController');
		$packerPHP->addOfuscarClass('ExjClassSession');
		$packerPHP->addOfuscarClass('ExjCriteriaModel');
		$packerPHP->addOfuscarClass('ExjDataResult');
		$packerPHP->addOfuscarClass('ExjDBQuery');
        $packerPHP->addOfuscarClass('ExjImportModel');
        $packerPHP->addOfuscarClass('ExjError');
        $packerPHP->addOfuscarClass('ExjDataTopicsResponse');
		$packerPHP->addOfuscarClass('ExjMsgResponse');
		// $packerPHP->addOfuscarClass('ExjuiController');
		$packerPHP->addOfuscarClass('ExjResponse');
		$packerPHP->addOfuscarClass('ExjObject');
		$packerPHP->addOfuscarClass('ExjSession');
		
		$packerPHP->addOfuscarClass('ExjRequest');
		$packerPHP->addOfuscarClass('ExjApi');
		$packerPHP->addOfuscarClass('ExjClass');
		$packerPHP->addOfuscarClass('ExjUI');
		$packerPHP->addOfuscarClass('ExjField');
		$packerPHP->addOfuscarClass('ExjHelper');
		$packerPHP->addOfuscarClass('ExjUtil');
		$packerPHP->addOfuscarClass('ExjDatabase');
	//	$packerPHP->addOfuscarClass('ExjResource');
		$packerPHP->addOfuscarClass('ExjModels');
		$packerPHP->addOfuscarClass('ExjHelperFile');
		$packerPHP->addOfuscarClass('ECBHError');
		$packerPHP->addOfuscarClass('ExjHelperInfoUser');
		$packerPHP->addOfuscarClass('ExjPlugin');
		$packerPHP->addOfuscarClass('AppHandlerLogData');
		$packerPHP->addOfuscarClass('AppGlobalExtraPlugin');
	//	$packerPHP->addOfuscarClass('AppFeDocumentosData');
		
		/*
		$packerPHP->addOfuscarClass('ExjEditableModel');
		$packerPHP->addOfuscarClass('EditableModel');
		*/
		
		// metodos o funciones
		$packerPHP->addOfuscarClass('GetNamModUIFromOption');
		$packerPHP->addOfuscarClass('convertStrListToArray');
		$packerPHP->addOfuscarClass('convertToDateTimeDB');
		$packerPHP->addOfuscarClass('covertToNameClass');
		$packerPHP->addOfuscarClass('GetPrefixClassApp');
		$packerPHP->addOfuscarClass('SetRestFulToRequest');
        $packerPHP->addOfuscarClass('LogWriteDelayed');
        $packerPHP->addOfuscarClass('LogWriteError');
        $packerPHP->addOfuscarClass('SetErrorMsgGlobal');
        $packerPHP->addOfuscarClass('GetErrorMsgGlobal');
		$packerPHP->addOfuscarClass('ConvertNumberIntToLetters');
		$packerPHP->addOfuscarClass('writeWithCallback');
        $packerPHP->addOfuscarClass('renderValuePointToComa');
		$packerPHP->addOfuscarClass('getTotalNormalized');
		$packerPHP->addOfuscarClass('SetBufferDebugTimeDemora');
		$packerPHP->addOfuscarClass('GetVersionApp');
		$packerPHP->addOfuscarClass('GetSecondsDemora');
		$packerPHP->addOfuscarClass('getDirectoryDownload');
		$packerPHP->addOfuscarClass('validateAccess');
		$packerPHP->addOfuscarClass('setBufferDebugMethod');
		$packerPHP->addOfuscarClass('validateError');
		$packerPHP->addOfuscarClass('getNameClassController');
		$packerPHP->addOfuscarClass('DispatchRestful');
		$packerPHP->addOfuscarClass('LoadPropertyFromMixed');
		$packerPHP->addOfuscarClass('GetFieldsVarsFromObject');
		$packerPHP->addOfuscarClass('getBufferDebugTrace');
		$packerPHP->addOfuscarClass('writeBufferDebugTrace');
		$packerPHP->addOfuscarClass('getHrefForUI');
		$packerPHP->addOfuscarClass('getErrorExist');
		$packerPHP->addOfuscarClass('getControllerRaw');
		$packerPHP->addOfuscarClass('isPageHorizontal');
		$packerPHP->addOfuscarClass('IncludePHPExcel');
        $packerPHP->addOfuscarClass('IncludePHPWord');
		$packerPHP->addOfuscarClass('copyAllDir');
		$packerPHP->addOfuscarClass('getPathBaseApp');
		$packerPHP->addOfuscarClass('addBrokenRuler');
		$packerPHP->addOfuscarClass('_canUploadSizeFile');
		$packerPHP->addOfuscarClass('deleteImportReadItems');
		$packerPHP->addOfuscarClass('setMsgError');
		$packerPHP->addOfuscarClass('haveMsgText');
		// $packerPHP->addOfuscarClass('getErrorMsg'); // usado en db
		$packerPHP->addOfuscarClass('setQueryLike');
		$packerPHP->addOfuscarClass('setOrdersFirst');
		$packerPHP->addOfuscarClass('listModelRead');
		$packerPHP->addOfuscarClass('registerControlsUI');
		$packerPHP->addOfuscarClass('registerRules');
		$packerPHP->addOfuscarClass('registerFields');
		$packerPHP->addOfuscarClass('getLookupEstados');
		$packerPHP->addOfuscarClass('GetNameFileHelperJs');
		$packerPHP->addOfuscarClass('GetUriExtJs');
		$packerPHP->addOfuscarClass('setDataTopics');
		$packerPHP->addOfuscarClass('paramData');
		$packerPHP->addOfuscarClass('isValidParamsToUpdate');
		$packerPHP->addOfuscarClass('isValidParamsToCreate');
		$packerPHP->addOfuscarClass('registerFieldString');
		$packerPHP->addOfuscarClass('registerFieldInt');
		$packerPHP->addOfuscarClass('registerControlUI');
		$packerPHP->addOfuscarClass('beforeDestroy');
		$packerPHP->addOfuscarClass('setValueId');
		$packerPHP->addOfuscarClass('haveError');
		$packerPHP->addOfuscarClass('newBotonMenu');
		$packerPHP->addOfuscarClass('newTextField');
		$packerPHP->addOfuscarClass('newTextArea');
		$packerPHP->addOfuscarClass('newJsonStoreSimple');
		$packerPHP->addOfuscarClass('newButton');
		$packerPHP->addOfuscarClass('newNumberField');
		$packerPHP->addOfuscarClass('newFieldUI');
		$packerPHP->addOfuscarClass('registerTable');
		$packerPHP->addOfuscarClass('getItemsTopbarExtrasLeft');
		$packerPHP->addOfuscarClass('getItemsTopbarExtrasRight');
		$packerPHP->addOfuscarClass('reportInit');
		$packerPHP->addOfuscarClass('fixPageHorizontal');
		$packerPHP->addOfuscarClass('fixPaperSizeA4');
		$packerPHP->addOfuscarClass('fixPaperSizeFOLIO');
		$packerPHP->addOfuscarClass('reportRegisterCriteria');
		$packerPHP->addOfuscarClass('registerColInt');
		$packerPHP->addOfuscarClass('reportProperties');
		$packerPHP->addOfuscarClass('reportLoadItems');
		$packerPHP->addOfuscarClass('reportLoadData');
		$packerPHP->addOfuscarClass('reportDetailBefore');
		$packerPHP->addOfuscarClass('reportDetail');
		$packerPHP->addOfuscarClass('reportCustomHeadersDetail');
		$packerPHP->addOfuscarClass('saveExcel2007');
		$packerPHP->addOfuscarClass('_prepareDocument');
		$packerPHP->addOfuscarClass('bindCriterias');
		$packerPHP->addOfuscarClass('getParamsCriteria');
		$packerPHP->addOfuscarClass('getAliasFromFields');
		$packerPHP->addOfuscarClass('listInit');
		$packerPHP->addOfuscarClass('listRegisterFields');
		$packerPHP->addOfuscarClass('listRegisterCols');
		$packerPHP->addOfuscarClass('onGetData');
		$packerPHP->addOfuscarClass('SetParamsQueryFromModelList');
		$packerPHP->addOfuscarClass('clearParamsQuery');
		$packerPHP->addOfuscarClass('_getPathFront');
		$packerPHP->addOfuscarClass('GetPathbaseFront');
		$packerPHP->addOfuscarClass('buildURLModel');
		$packerPHP->addOfuscarClass('getNameFileController');
		$packerPHP->addOfuscarClass('addConditionsQuery');
		$packerPHP->addOfuscarClass('setErrorValidating');
		$packerPHP->addOfuscarClass('GetInfoUsrIdCompany');
		$packerPHP->addOfuscarClass('GetInfoUsrNameInstitucion');
		$packerPHP->addOfuscarClass('GetInfoUsrIdPais');
		$packerPHP->addOfuscarClass('GetInfoUsrIdProvincia');
		$packerPHP->addOfuscarClass('GetInfoUsrOffsetTime');
		$packerPHP->addOfuscarClass('_getInfoUsr');
		$packerPHP->addOfuscarClass('GetNameModelEditableFromNameClass');
		$packerPHP->addOfuscarClass('GetNameClassModelEditableFromName');
		$packerPHP->addOfuscarClass('GetNameClassModelListFromName');
		$packerPHP->addOfuscarClass('CalcularEdad');
		$packerPHP->addOfuscarClass('renderPercent');
		$packerPHP->addOfuscarClass('RenderDateTime');
		$packerPHP->addOfuscarClass('convertToDateDB');
		$packerPHP->addOfuscarClass('isEqualLike');
		$packerPHP->addOfuscarClass('isValidDateChars');
		$packerPHP->addOfuscarClass('RenderDatesRange');
		$packerPHP->addOfuscarClass('RenderDate');
		$packerPHP->addOfuscarClass('setNameModelController');
		$packerPHP->addOfuscarClass('setReportDownload');
		$packerPHP->addOfuscarClass('setConfig');
		$packerPHP->addOfuscarClass('registerColCustom');
		$packerPHP->addOfuscarClass('registerColDateTime');
		$packerPHP->addOfuscarClass('registerColDate');
		$packerPHP->addOfuscarClass('registerFieldDate');
		$packerPHP->addOfuscarClass('getColFromDataIndex');
		$packerPHP->addOfuscarClass('colAlignRight');
		$packerPHP->addOfuscarClass('to_ui_fields');
		$packerPHP->addOfuscarClass('to_ui_columns');
		$packerPHP->addOfuscarClass('to_ui_cfgPagingToolbar');
		$packerPHP->addOfuscarClass('listButtonAddCustom');
		$packerPHP->addOfuscarClass('to_ui_cfgTopToolbar');
		$packerPHP->addOfuscarClass('newButtonEdit');
		$packerPHP->addOfuscarClass('newButtonDelete');
		$packerPHP->addOfuscarClass('newMenuItemHTML');
		$packerPHP->addOfuscarClass('newMenuItemPDF');
		$packerPHP->addOfuscarClass('newMenuItemExcelXLSX');
		$packerPHP->addOfuscarClass('newMenuItemExcelXLS');
		$packerPHP->addOfuscarClass('ApplyAction');
		$packerPHP->addOfuscarClass('to_ui_cfgGrid');
		$packerPHP->addOfuscarClass('setBaseParam');
		$packerPHP->addOfuscarClass('to_ui_cfgStore');
		$packerPHP->addOfuscarClass('getPosCellFromIndex');
		$packerPHP->addOfuscarClass('writeJavaScript');
		$packerPHP->addOfuscarClass('getTemplateJavaScript');
        $packerPHP->addOfuscarClass('GetSrcScripts');
		$packerPHP->addOfuscarClass('DeleteFileSimilar');
		$packerPHP->addOfuscarClass('GetComponentsHelpers');
		$packerPHP->addOfuscarClass('getPathComponets');
		$packerPHP->addOfuscarClass('getComponetsApp');
		$packerPHP->addOfuscarClass('getSrcs_ExtJs');
		$packerPHP->addOfuscarClass('getSrcs_CompBase');
		$packerPHP->addOfuscarClass('getSrcs_CompCalendar');
		$packerPHP->addOfuscarClass('buildFileJs');
		$packerPHP->addOfuscarClass('getNameFileJsFromComp');
		$packerPHP->addOfuscarClass('pathInfoFileDir');
		$packerPHP->addOfuscarClass('assignValueToFields');
		$packerPHP->addOfuscarClass('writeBackTrace');
		$packerPHP->addOfuscarClass('loadFromRequest');
		$packerPHP->addOfuscarClass('getFieldsOfThisObj');
		$packerPHP->addOfuscarClass('convertArrayToObject');
		$packerPHP->addOfuscarClass('structureIsEqual');
		$packerPHP->addOfuscarClass('getBrokenRules');
		$packerPHP->addOfuscarClass('RenderSizeBytes');
		$packerPHP->addOfuscarClass('render_SINO');
		$packerPHP->addOfuscarClass('buildSrcs');
		$packerPHP->addOfuscarClass('registerCol');
		$packerPHP->addOfuscarClass('SetErrorDB');
		$packerPHP->addOfuscarClass('_getListAccessUsr');
		$packerPHP->addOfuscarClass('newItemRadio');
		$packerPHP->addOfuscarClass('newMenuItemLink');
		$packerPHP->addOfuscarClass('newMenuItem');
		$packerPHP->addOfuscarClass('DatesDif');
		
		$packerPHP->addOfuscarClass('TrasferCharsDecodeUTF8ToISO');
		$packerPHP->addOfuscarClass('TrasferCharsEncodeISOToUTF8');
		$packerPHP->addOfuscarClass('decodeUTF8ToISO');
        $packerPHP->addOfuscarClass('encodeISOToUTF8');
		$packerPHP->addOfuscarClass('JsonDecodeSlashes');

		$packerPHP->addOfuscarClass('GetUserGID');
		$packerPHP->addOfuscarClass('loadList');
		$packerPHP->addOfuscarClass('getCount');
		$packerPHP->addOfuscarClass('getRows');
		$packerPHP->addOfuscarClass('isValid');
		$packerPHP->addOfuscarClass('writeLogLn');

		// VARIABLES PUBLICAS DE UN CLASE
		$packerPHP->addOfuscarClass('requiereSelectionReport');
		$packerPHP->addOfuscarClass('addParamsPaggingInit');
		$packerPHP->addOfuscarClass('addItemsTopbarExtras');
		
		// VARIABLES PRIVADAS
		$packerPHP->addOfuscarClass('_numFilaActual');
		$packerPHP->addOfuscarClass('_controllerRaw');
		$packerPHP->addOfuscarClass('_verAppServer');
		$packerPHP->addOfuscarClass('_hLogData');
				
		return $packerPHP;
	}
	
	public function encodeFilePHP($pathFileOrig, &$pathFileDest, $onlyOfuscar=false)
    {
		if (!file_exists($pathFileOrig)){
			$this->_setError("No existe el archivo: $pathFileOrig");
			return false;
		}
		
		$codePHP = file_get_contents($pathFileOrig);

        $nfDest = basename($pathFileDest, '.php');
        /*
        if ($nfDest == 'index') {
            if (strpos($codePHP, '$isReleasedExjApi') !== false) {
                $codePHP = str_replace('$isReleasedExjApi = false', '$isReleasedExjApi = true', $codePHP);
                $codePHP = str_replace('$isReleasedExjApi', '$_bv_'. date("dhis").'cm', $codePHP);
            }
        }
        */

        if ($nfDest == 'CfgExj') {
            $codePHP = str_replace(
                '$isReleased = false',
                '$isReleased = true',
                $codePHP
            );
       }

		$numWordsOfuscadas = 0;
		$packerPHP = $this->_encodePHP($codePHP, $numWordsOfuscadas, $onlyOfuscar);

        $codeEncoded = $packerPHP->pack();
        $numWordsOfuscadas = $packerPHP->getNumWordsOfuscadas();

        
        $nfEncoded = $packerPHP->findWordOfuscada($nfDest);
        if ($nfEncoded) {
            $pathFileDest = dirname($pathFileDest).'/'.$nfEncoded.'.php';
        }
        

		
		/*
		if ($numWordsOfuscadas == 0) {
			echo "<br/>ENCODE CERO OFUSCACION: ". $pathFileOrig;
		}
		*/
		
		if ($this->_commentPHPDeploy && !$onlyOfuscar) {
			$codeEncoded = $this->_commentPHPDeploy . $codeEncoded;
		}
		
		$sizeFileEncoded = file_put_contents($pathFileDest, $codeEncoded);
		
		$this->_numFilesPHPEncoded += 1;
		$this->_sizeTotalFilesPHPEncoded += $sizeFileEncoded;
		
		return true;
	}
	
	
	private function _encodeFileJs($pathFileOrig, $pathFileDest){
		if (!file_exists($pathFileOrig)) {
			$this->_setError("No existe el archivo: $pathFileOrig");
			return false;
		}
		
		$codeJs = file_get_contents($pathFileOrig);
		$codeEncoded = $this->_encodeJavaScript($codeJs);
		
		if ($this->_commentJSDeploy) {
			$codeEncoded = $this->_commentJSDeploy . $codeEncoded;
		}
		
		$sizeFileEncoded = file_put_contents($pathFileDest, $codeEncoded);
		
		$this->_numFilesJsEncoded += 1;
		$this->_sizeTotalFilesJsEncoded += $sizeFileEncoded;
		
		return true;
	}
    
	private function _copyFile(AppDeployCfgPathRoot $depPathRoot, $pathFileOrig, $pathFileDest)
    {
		if (!file_exists($pathFileOrig)) {
			return $this->_setError("No existe el archivo: $pathFileOrig");
		}

		$infoFile = pathinfo($pathFileOrig);
		$extFile = $infoFile['extension'];
		$nameFile = $infoFile['basename']; // nombre archivo+extension
		$extFile = strtolower($extFile);
		$isCopiedFile = false;
        $isPacked = false;
		
		if ($extFile == 'js') {
			$pathDir = $infoFile['dirname']; // del origen

            $isDirAppWeb = (strpos($pathDir, '/app/web/')!==false);
            if ($isDirAppWeb) {
                // NOTE: NO COPIAR NI HACER packer a .js
                // echo "\nNo copy: $pathDir";
                $this->writeLogFile("_copyFile CANCEL. $pathFileDest");
                return $this;
            }

            // $isDirAppPublic = (strpos($pathDir, '/app/public/')!==false);
            

			if (!$this->_PREFIXPACKJS){
				$this->_PREFIXPACKJS = 'pkg';
			}

            $isHelperJs = (strpos($nameFile, '.helper')!==false);

            if (strpos($nameFile, $this->_PREFIXPACKJS)===0) {
                $isPacked = true;
            }

            if ($isPacked) {
                $nameFileJsPKG = $nameFile;
            }
            else{
                $nameFileJsPKG = $this->_PREFIXPACKJS . $nameFile; // cuidado con esto, puede sobrescribir el código
            }
			
			$pathFileDestJs = $pathDir . '/' . $nameFileJsPKG;

			if (!$isPacked && $depPathRoot->canEncodeFile($nameFileJsPKG, $pathFileOrig))
            {
				if (!$this->_encodeFileJs($pathFileOrig, $pathFileDestJs)) {
                    /*
                    $this->writeLogFile(
                        "No se aplico: encodeFileJs. hacia: $pathFileDestJs"
                    );
                    */
					return $this;
				}

                if ($isHelperJs) {
                    if (!$this->_minifierHelperJs) {
                        $this->_minifierHelperJs = new MatthiasMullie\Minify\JS();
                    }
                    $this->_minifierHelperJs->add($pathFileDestJs);
                }
			}
			else {

                if (!$isPacked) {
                    $minifier = new MatthiasMullie\Minify\JS($pathFileOrig);
                    $minifier->minify($pathFileDestJs);
                }
                

				/*
                if (!copy($pathFileOrig, $pathFileDestJs)) {
					$this->_setError("No se pudo copiar el archivo: $pathFileOrig");
					return false;
				}
                */
			}
			
			$infoFileDest = pathinfo($pathFileDest);
			$pathDirDest = $infoFileDest['dirname'];
			
			$pathFileDest = $pathDirDest . '/' . $nameFileJsPKG;
			$pathFileOrig = $pathFileDestJs;
		}
		elseif ($extFile == 'php') {
			if ($depPathRoot->canEncodeFile($nameFile, $pathFileOrig)) {
				
                $onlyOfuscar = $depPathRoot->canOnlyOfuscarFile(
                    $nameFile, $pathFileOrig
                );
				
				if (!$this->encodeFilePHP($pathFileOrig, $pathFileDest, $onlyOfuscar)) {
					return $this;
				}

				$isCopiedFile = true;
				// echo "<br/>SE HA OFUSCADO: $pathFileDest";
			}
		}
		
		if (!$isCopiedFile) {
			if (!copy($pathFileOrig, $pathFileDest)) {
				$this->_setError("No se pudo copiar el archivo: $pathFileOrig");
				return $this;
			}
			
            if ($extFile == 'js') {
                $this->writeLogFile(
                    "_copyFile. JS. COPIADO. isPacked: ".
                      ($isPacked?'SI':'NO').
                      " $pathFileOrig -> $pathFileDest"
                );
            }
		}
		
		switch ($extFile) {
			case 'js':
				$this->_numFilesCopiedJS += 1;
			break;

			case 'php':
				$this->_numFilesCopiedPHP += 1;
			break;

			case 'css':
				$this->_numFilesCopiedCSS += 1;
			break;

			case 'ico':
			case 'png':
			case 'gif':
			case 'jpg':
				$this->_numFilesCopiedIMG += 1;
			break;
			
			default:
			//	echo " ESTE ARCHIVO NO SOPORTADO";
				$this->_numFilesCopiedOTROS += 1;
			break;
		}
		
		$this->writeLogFile(
            "COPIADO pathFileDest: $pathFileDest js: $this->_numFilesCopiedJS php: $this->_numFilesCopiedPHP"
        );

        ExjEvent::Fire(__FUNCTION__, array($pathFileDest), $this);
		
		return $this;
	}
	
	public function getNumFilesJsEncoded(){
		return $this->_numFilesJsEncoded;
	}
	public function getSizeTotalFilesJsEncoded(){
		return $this->_sizeTotalFilesJsEncoded;
	}

	public function getPathRelease(){
		return $this->_pathRelease;
	}
	
	public function getNameFileBKDB(){
		return $this->_nameFileBKDB;
	}
	
	
	public function getURIRelease(){
		return $this->_uriRelease;
	}
	
	public function getPathPreProduccion(){
		return $this->_pathPreProduccion;
	}
	public function getURIPreProduccion(){
		return $this->_uriPreProduccion;
	}
	
	private function _setError($msgError) {
		$msgError = "Deployando Archivos.<br/>" . $msgError;
		
		$this->writeLogFile("ERROR. $msgError");
		
        Exj::GetError()->setMsgFile($msgError);
        return $this;
	}

	
    private function _splitPath(&$pathRoot, &$folderApp){
    	$pathBaseApp = Exj::GetPathBase();
    	$folderApp = 'app';
    	
    	$pos = strrpos($pathBaseApp, '/');
    	if ($pos === false) {
    		$pathRoot = 'C:/app';
    		return;
    	}
    	
    	$pathRoot = substr($pathBaseApp, 0, $pos);
    	$folderApp = substr($pathBaseApp, $pos+1);
    }
    
	private function _validatePathDir($pathDir){
    	if (!ExjFile::ValidateDir($pathDir)) {
			$this->_setError("No se pudo crear el path: ". $pathDir);
    	}
    	
    	return $this;
	}
	
    

    private function _getURIServer(){
    	$URIRoot = ExjHandlerFile::GetURIRoot();
    	
    	$pos = strrpos($URIRoot, '/');
    	if ($pos === false) {
    		return $URIRoot;
    	}
    	
    	if ($pos == strlen($URIRoot)-1) {
    		$URIRoot = substr($URIRoot, 0, $pos);
	    	$pos = strrpos($URIRoot, '/');
	    	if ($pos === false) {
	    		return $URIRoot;
	    	}
    	}
    	
    	$URIRoot = substr($URIRoot, 0, $pos);
    	return $URIRoot;
    }
    	
	public static function GetExtensionFile($file){
		$ext = '';
		if (!$file) {
			return $ext;
		}
		
		$pos = strrpos($file, ".");
		if ($pos === false) {
			return $ext;
		}

		$ext = substr($file, $pos+1);
		$ext = strtolower($ext);
		return $ext;
	}
	
}

?>