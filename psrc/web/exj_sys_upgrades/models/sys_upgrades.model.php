<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUpgradeModel
 * Modelo para Actualización
 */
class AppSysUpgradeModel extends ExjModel {
	
    public static function saveSysUpgrade($id, $dataChanged, $paramData) {
    	global $exj;
    	
    	try {
    		ExjDBTrx::Start();
    		
			$sysUser = new AppSysUpgradeEditableModel(false);
			if ($sysUser->bind($dataChanged)) {
				$sysUser->setValueId($id);
				
				$sysUser->setParams($paramData);
				
				if (!$sysUser->save()) {
					// NOTE: La entidad ya hace roolback
					return $sysUser->validateResponse();
				}
			}
    		
    		ExjDBTrx::Commit();
    	}
    	catch (Exception $ex){
    		$exj->setErrorException($ex);
    		ExjDBTrx::Rollback();
    	}
		
		return $sysUser->validateResponse();
    }

    static function loadListSysUpgrades(&$items, &$total, $paramsCriteria=null) {    	
    	return AppSysUpgradesData::loadListSysUpgrades($items, $total, $paramsCriteria);
    }

    public static function ExecScriptSql(ExjResponse $response, $scriptSQL, $usr, $pwd)
    {
        $db = Exj::InstanceDatabase();
        if ($usr) {
            $db->changeUserPwd($usr, $pwd);
            // echo "<br>Cambio de usr: $usr pwd: $pwd a DB";
        }

        if (strpos($scriptSQL, ' ')===false) {
            $scriptSQL = "SELECT * FROM $scriptSQL LIMIT 30";
        }


        $isSelect = (bool) preg_match('/^\b(SELECT)\b/i', $scriptSQL);
        $isShowInfo = false;
        if (!$isSelect) {
            $isShowInfo = (bool) preg_match('/^\b(DESCRIBE|SHOW)\b/i', $scriptSQL);
        }
        
        $iniStartTrxDB = false;
        $resultSQL = '';
        if ($isSelect || $isShowInfo) {
            $scriptSQL = rtrim($scriptSQL, ';');
            if (strpos(';', $scriptSQL)!==false) {
                $response->setMsgError("No se permite varias Consultas");
                return;
            }

            if ($isSelect && !preg_match('/(LIMIT)\s+([0-9\s\,])+$/i', $scriptSQL)) {
                // no tiene límite, se adiciona límite
                $scriptSQL .= ' LIMIT 100';
            }

            $resultSQL = $db->loadObjectList($scriptSQL);
        }
        else{
            ExjDBTrx::start();
            $iniStartTrxDB = true;
            $db->setQuery($scriptSQL);
            $db->queryBatch();

        }
        
        if ($db->getErrorMsg()) {
            if ($iniStartTrxDB) {
                ExjDBTrx::rollback();
            }
            
            $response->setMsgError($db->getErrorMsg());
            return;
        }

        if ($iniStartTrxDB) {
            ExjDBTrx::commit();
        }


        $dataResponse = new stdClass();

        if ($isSelect || $isShowInfo) {
            if (empty($resultSQL)) {
                $resultSQL = "Registros <b>0</b>";
                $dataResponse->resultSQL = $resultSQL;
            }
            else{
                $listModel = new ExjListModel((new ExjHelperMenu())->fixAccessReadOnly());
                $listModel->fixModeLocal();
                $listModel->setReportDownload(false, false, false);
                $listModel->fixGridHeight(159);
                
                $firstRow = $resultSQL[0];
                $fieldKey='';
                foreach ($firstRow as $field => $value) {
                    if(!$fieldKey){
                        $fieldKey = $field;
                    }

                    $listModel->registerFieldString($field, $field);
                    $listModel->registerCol($field, ExjListModel::COL_ANCHO_DEFECTO);
                }

                $listModel->setConfig('', $fieldKey, false);
                $listModel->registerFieldString($fieldKey, $fieldKey);

                $listModel->setData($resultSQL);

                $dataResponse->grid = $listModel->to_ui();
            }

            // print_r($listModel->to_ui());
        }
        else{
            $resultSQL = $db->getAffectedRows();
            $resultSQL = "Registros afectados: " . $resultSQL;
            $dataResponse->resultSQL = $resultSQL;
        }

        $response->setDataObject($dataResponse);
    }

    public static function SearchProgBackup(ExjResponse $response, $dirToSearch, $nameFile)
    {
        $maxSeg = 9; // segundos máximo de espera
        $result = ExjHelperFile::SearchFileInDir(
            $nameFile, $founds, $dirToSearch, $maxSeg,
            ['window','google','git','apache','ESET','Java','python','image', 'Chrome',
            'laragon', 'Microsoft',
            '/^[0-9\$]+/']
        );

        if ($result === true && empty($founds)) {
            $response->setMsgError(
                "No se encontró: $nameFile<br>En el directorio: $dirToSearch"
            );
            return;
        }

        $dataResponse = new stdClass();
        $dataResponse->founds = $founds;
        $dataResponse->dirToRecall = (is_string($result) ? $result:false);

        $response->setDataObject($dataResponse);
       // print_r($dataResponse);
    }

    public static function GetDirTempBks(&$msgError){
        $msgError = '';
        $dirTempBk = ExjHandlerFile::GetDirectoryTemp() . 'bk/';
        
        if (!ExjFile::ValidateDir($dirTempBk)) {
            $msgError = 'No se pudo crear dir temp'.$dirTempBk;
        }

        // echo "<br>dirTempBk: $dirTempBk";

        return $dirTempBk;
    }

    public static function GetFilesBks(&$msgError, $fullPath=false){
        $dirTempBk = self::GetDirTempBks($msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return null;
        }

        $dirTempBk = rtrim($dirTempBk, '/');

        $files = array();

        $d = dir($dirTempBk);
        while (false !== ($entry = $d->read())) {
            if (!$entry || $entry == '.' || $entry == '..') {
                continue;
            }

            $pathFile = $dirTempBk.'/'.$entry;
            if (is_file($pathFile)) {
                if ($fullPath) {
                    $files[] = $pathFile;
                }
                else{
                    $files[] = $entry;
                }                
            }
        }

        $d->close();

        return $files;
    }

    public static function BackupDB(ExjResponse $response, $path_mysqldump, $usrDB, $pwdDB)
    {
        $dirTempBk = self::GetDirTempBks($msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return;
        }

        $nameDB = ExjDatabase::GetNameDB();

        // $path_mysqldump = 'mysql';
        $path_mysqldump = trim($path_mysqldump, '"');

        $cmdBackup = '"'. $path_mysqldump.'"'.' -e';
        $cmdBackup .= ' -u'.$usrDB;
        if ($pwdDB) {
            $cmdBackup .= ' -p'.$pwdDB;
        }
        
        $cmdBackup .= ' -h'.'localhost';
        $cmdBackup .= ' '.$nameDB;
        $cmdBackup .= ' > ';

        $nameFileSql = 'bk_'.$nameDB. '_' . date('dmY');
        $nameExtFileSql = $nameFileSql . '.sql';
        
        $pathFileSql = $dirTempBk.$nameExtFileSql;
        if (file_exists($pathFileSql)) {
            unlink($pathFileSql);
        }

        $cmdBackup .= $pathFileSql;

    //    echo "cmdBackup: $cmdBackup<br>";

        shell_exec($cmdBackup);

        if (!file_exists($pathFileSql)) {
            $response->setMsgError("No se creó el archivo de BackupDB: $nameExtFileSql");
            return;
        }

        // si es tamaño 0
        if (filesize($pathFileSql) <= 0) {
            $response->setMsgError(
                "Se creó el archivo vacío: $nameExtFileSql<br>".
                "Verificar usuario y contraseña de DB"
            );
            return;
        }

        // comprimir
        $pathFileZip = $dirTempBk.$nameFileSql.'.zip';
        if (file_exists($pathFileZip)) {
            unlink($pathFileZip);
        }

        $fileZip = new PclZip($pathFileZip);
        if ($fileZip->add($pathFileSql, PCLZIP_OPT_REMOVE_PATH, dirname($pathFileSql)) == 0) {
            $response->setMsgError($fileZip->errorInfo(true));
            return;
        }

        $dataFiles = AppSysUpgradesData::GetDataFilesBks($msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return;
        }

        // print_r($dataFiles);

        $response->setDataObject($dataFiles);

        // save en params del sistema
        $rowParam = AppSysParametersData::GetRowFromCode(
            AppSysParametersHelper::CODE_PATH_PROG_BKDB,
            'id_sys_param,type_param,value_param'
        );

        $id_sys_param = null;
        if ($rowParam) {
            if ($rowParam->value_param != $path_mysqldump) {
                $id_sys_param = $rowParam->id_sys_param;
            }
        }
        else{
            $id_sys_param = 0;
        }

        if ($id_sys_param !== null) {
            $paramSys = new AppSysParameterEditableModel(false);
            $paramSys->setValueId($id_sys_param);
            $paramSys->value_param = $path_mysqldump;
            if ($paramSys->isNew()) {
                $paramSys->code_param = AppSysParametersHelper::CODE_PATH_PROG_BKDB;
                $paramSys->type_param = AppSysParametersHelper::TYPE_STRING;
                $paramSys->name_param = 'Ruta programa de backup DB';
            }

            $paramSys->save();
            if ($paramSys->haveBrokenRules()) {
                $response->setMsgError($paramSys->getBrokenRules());
            }
        }

        $response->setMsgNotify("Generación satisfactoria sql y zip: $nameFileSql");
    }

    private static function _GetPathFileBk(ExjResponse $response, $nameFile, $prefixMsg='')
    {
        $pathFile = '';
        $nameFile = trim($nameFile);
        if ($prefixMsg) {
            $prefixMsg .= '. ';
        }

        if (!$nameFile) {
            $response->setMsgError($prefixMsg."Nombre de archivo requerido");
            return $pathFile;
        }

        $dirTempBk = self::GetDirTempBks($msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return $pathFile;
        }

        $pathFile = $dirTempBk . $nameFile;
        if (!file_exists($pathFile)) {
            $response->setMsgError($prefixMsg."No existe archivo: $nameFile");
            return $pathFile;
        }
        if (!is_file($pathFile)) {
            $response->setMsgError($prefixMsg."No es un archivo: $nameFile");
            return $pathFile;
        }

        return $pathFile;
    }

    public static function DeleteFileBk(ExjResponse $response, $nameFile){
        $pathFile = self::_GetPathFileBk($response, $nameFile, 'Eliminar');
        if ($response->haveMsgError()) {
            return $response;
        }

        if (!unlink($pathFile)) {
            return $response->setMsgError("No se pudo eliminar el archivo: $nameFile");
        }

        $dataFiles = AppSysUpgradesData::GetDataFilesBks($msgError);
        if ($msgError) {
            $response->setMsgWarning($msgError);
        }
        else{
            $response->setMsgInfo("Archivo: $nameFile eliminado");
        }

        return $response->setDataObject($dataFiles);
    }

    public static function DownloadFile(ExjResponse $response, $nameFile){
        $pathFile = self::_GetPathFileBk($response, $nameFile, 'Descargar');
        if ($response->haveMsgError()) {
            return $response;
        }

        AppBasedownloadModel::DownloadFile($pathFile);
    }
}

?>