<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppBaseReportsModel
 */
class AppBaseReportsModel extends ExjModel {
	
	public static function IncludeReport(ExjResponse &$response, $paramsGlobal){
		$response->includeTemplateForHTML();
		
		global $baseReportParams;
		$baseReportParams = $paramsGlobal;
	}
    
	/**
	 * Devuelve data de modelo de lista
	 *
	 * @param ExjHelperMenu $hMenu Instancia de la clase ExjHelperMenu
	 * @param string $nameComponent
	 * @param string $nameListModel
	 * @return object
	 */
    public static function getDataUIList($hMenu, $nameComponent, $nameListModel) {
    	$nameClaseModel = Exj::GetNameClassList($nameListModel);
    	
    	$instaceModel = new $nameClaseModel($hMenu);
    	$instaceModel->readData($nameComponent);
    	
    	return $instaceModel->to_ui();
    }
   
	/**
	 * Decodifica el path del archivo de descarga
	 *
	 * @param string $idFile
	 * @param string $entry
	 * @param int $idFull
	 * @return string Path completo del archivo
	 */
	static function DecodePathFile($idFile, $entry, $idFull){
		$pathFile = base64_decode($idFile);
		if ($entry) {
			$entry = base64_decode($entry);
		}
		
		if (!$idFull) {
			// build
			$pathFile = ExjHandlerFile::GetPathBaseFiles($entry.'/'.$pathFile);
		}
		
		return $pathFile;
	}
    

}



?>