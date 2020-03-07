<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppArchivosData
 *
 */
class AppArchivosData extends ExjData {
	
	/**
	 * Lista de Archivos
	 *
	 * @return array de object
	 */
	static function loadListArchivos(&$items, &$total, $paramsCriteria=null){
        global $exj;
//        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("fls.id_file, fls.name_file, fls.path_file, fty.name_type_file,
  fty.ext_file, fty.cat_type_file, usr.name AS name_usr,
  fty.module_allow, fty.size_max_bytes");
        
        $dbQuery->setTables("jos_app_files fls INNER JOIN
  jos_app_files_type fty ON fls.id_file_type = fty.id_file_type
  INNER JOIN
  jos_users usr ON fls.id_usuario_modifico = usr.id");
        
        if ($paramsCriteria) {
			// $exj->includeModelCriteria('archivos');
			$criteriaArchivos = new AppArchivosCriteriaModel(false);
			if ($criteriaArchivos->bind($paramsCriteria)) {
				$criteriaArchivos->addConditionsQuery($dbQuery);
			}
        }
        
        // $dbQuery->addConditions("fty.id_company = $id_company");
        
        $dbQuery->addOrders("fls.name_file");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("fls.id_file");
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		
       // $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();
	}
	
	static function getLookupArchivos($module_allow=''){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $id_company = ExjUser::GetIdCompania();
        if (!$module_allow) {
        	$module_allow = ExjHelperFile::FILETYPE_MODULE_LINKS;
        }
        
        $sql = "SELECT 
  fls.id_file AS value, fls.name_file AS text, fls.path_file,
  fty.name_type_file, fty.ext_file, fty.cat_type_file,
  fty.size_max_bytes, fls.modificado_dt
 FROM 
  jos_app_files fls INNER JOIN
  jos_app_files_type fty ON fls.id_file_type = fty.id_file_type
 WHERE 
  fty.module_allow = '$module_allow' AND 
  fty.id_company = $id_company
 ORDER BY 
  fls.modificado_dt";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	global $exj;
        	
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}
	
	public static function GetLookupTipos($module_allow=''){
        $db = Exj::InstanceDatabase();
        
        $id_company = ExjUser::GetIdCompania();
    //    $id_company = 1;
        if (!$module_allow) {
        	$module_allow = ExjHelperFile::FILETYPE_MODULE_RIDE;
        }
        
        $sql = "SELECT
		  t.id_file_type AS value, t.name_type_file AS text, t.ext_file,
		  t.size_max_bytes, t.cat_type_file
		FROM
		  jos_app_files_type t
		WHERE
		  t.id_company = $id_company AND t.module_allow = '$module_allow'
		ORDER BY
		  t.id_file_type";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	global $exj;
        	
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}
	
	public static function LoadRowFileType(&$item, $ext_file, $module_allow=''){
        $db = Exj::InstanceDatabase();
        
        $id_company = ExjUser::GetIdCompania();
        if (!$module_allow) {
        	$module_allow = ExjHelperFile::FILETYPE_MODULE_RIDE;
        }
        
        $ext_file = trim(strtolower($ext_file));
        
        $sql = "SELECT 
		  ft.id_file_type, ft.name_type_file, ft.ext_file,
		  ft.cat_type_file, ft.size_max_bytes
		FROM 
		  jos_app_files_type ft 
		WHERE 
		  ft.ext_file = '$ext_file' AND
		  ft.module_allow = '$module_allow' AND
		  ft.id_company = $id_company";
        
        $db->setQuery($sql);
        $item = null;
        $db->loadObject($item);
        if ($db->getErrorMsg()) {
        	global $exj;
        	
        	Exj::SetErrorDB($db->getErrorMsg());
        	return false;
        }
        
        return true;
	}
}

?>