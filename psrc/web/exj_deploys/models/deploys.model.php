<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDeployModel
 * Modelo para Deploy
 */
class AppDeployModel extends ExjModel {
	
    public static function SaveDeploy($id, $dataChanged) {
    	
        ExjDBTrx::Start();

		$deploy = new AppDeployEditableModel(false);
		$deploy->disableAllTransactionsDB();
		
		if ($deploy->bind($dataChanged)) {
			$deploy->setValueId($id);
			
			$deploy->buildDeployEditable();
			
			if (!$deploy->save()) {
				return $deploy->validateResponse();
			}
		}
		
		ExjDBTrx::Commit();
		
		return $deploy->validateResponse();
    }

    // para pruebas
    static function bkDB($id_deploy, &$nameFileBKDB) {
		$bk = new BackupMySQL(
            'test_GYM', 'D:/Apache/Apache2/htdocs/release/gymcloud/ver1.0.1'
        );
		
		$bk->executeBK();
		if (Exj::GetError()->haveError()) {
			return false;
		}
		
		$nameFileBKDB = $bk->getNameFileBKDB();
    	
    	return true;
    }
    
    public static function CopyToPreProduction($id_deploy, &$url_release) {
    	global $exj;
    	
    	try {
    		ExjDBTrx::Start();

			$release = new AppDpyReleaseEditableModel(false);
			$release->setValueId(0);
			$release->copyDeployProduction($id_deploy);
			
			if (!$release->save()) {
				$exj->setErrorValidating($release->getBrokenRules());
				return false;
			}
			
			$url_release = $release->url_release;

    		ExjDBTrx::Commit();
    	}
    	catch (Exception $ex){
    		$exj->setErrorException($ex);
    		ExjDBTrx::Rollback();
    		return false;
    	}
		
		return true;
    }
    
    
    public static function OfuscarPHP($id_deploy) {
		$deploy = new AppDeployEditableModel(false);
		
		$deploy->load($id_deploy);
		if ($deploy->haveBrokenRules(true)) {
			return false;
		}
    	
    	
    	// Este archivo es protegido por la ley del derechos de propiedad literaria. La ingenieria inversa de este codigo se prohibe estrictamente.
    	
    	global $exj;
    	
    	$deployFiles = new AppDeployFilesModel($deploy->version_dpy);
    	
    	
    	$subDirFile = "/app/framework/exj/psrc/core/Exj.php";
    	
    	$fileOrig = Exj::GetPathBase() . $subDirFile;
    	$fileDest = $deployFiles->getPathPreProduccion().$subDirFile;
    	
    	echo "<br/>fileOrig: $fileOrig";
    	echo "<br/>fileDest: $fileDest";
    	// return true;
    	
    	$deployFiles->encodeFilePHP($fileOrig, $fileDest, false);
    	
    	if (Exj::GetError()->haveError()) {
    		return false;
    	}
    	
    	echo "fileDest: $fileDest";
    	
    	$scriptTest = 'var $s; echo "esto es una prueba"; $s=232; echo "s: $s;"';
    	
    	$partes = explode(" ", $scriptTest);
    	$scriptTest = join("|", $partes);
    	
    	// split("|", $scriptTest);
    	
		return true;
    }
    
    public static function loadListDeploys(&$items, &$total, $paramsCriteria=null) {
    	return AppDeploysData::loadListDeploys($items, $total, $paramsCriteria);
    }
    
    public static function loadListComps(&$items, &$total, $params=null) {    	
    	return AppDeploysData::loadListComps($items, $total, $params);
    }
}

?>