<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppFilesData
 *
 */
class AppFilesData extends ExjData {
	
	/**
	 * Lista de Archivos
	 *
	 * @return array de object
	 */
	static function loadListArchivos(&$items, &$total, $paramsCriteria=null){
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("fls.id_file, fls.name_file, fls.nameext_file, fls.path_file,
  fls.uri_file, fls.size_file, fls.sub_folder,
  fls.modificado_dt, usr.name AS name_usr, fls.id_file_type,
  fty.name_type_file, fty.id_company, lit.name_company,
  fty.size_max_bytes, fty.module_allow, fty.cat_type_file");
        
        $dbQuery->setTables("jos_app_files fls INNER JOIN
  jos_app_files_type fty ON fls.id_file_type = fty.id_file_type
  INNER JOIN
  jos_users usr ON fls.id_usuario_modifico = usr.id LEFT JOIN
  jos_exj_companies lit ON fty.id_company =
    lit.id_company");
        
        if ($paramsCriteria) {
			$criteriaArchivos = new AppFilesCriteriaModel(false);
			if ($criteriaArchivos->bind($paramsCriteria)) {
				$criteriaArchivos->addConditionsQuery($dbQuery);
			}
        }
        
        $dbQuery->setOrdersFirst("fty.id_company");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("fls.id_file");
		$items = $dbQuery->getRows();
		
		foreach ($items as &$item) {
			$item->str_size_file = ExjUtil::RenderSizeBytes($item->size_file);
		}
        
        return $dbQuery->isValid();
	}

	static function loadLookupTipos(){
		
		$id_company = ExjUser::GetIdCompania();
		
		$sql = "SELECT
  fty.id_file_type AS value, 
  CONCAT_WS(' - ', fty.module_allow, fty.ext_file) AS text,
  fty.name_type_file, fty.size_max_bytes, fty.module_allow,
  fty.cat_type_file
FROM
  jos_app_files_type fty
WHERE
  fty.id_company = $id_company
ORDER BY
  fty.module_allow, fty.name_type_file";
		
		$db = Exj::InstanceDatabase();
		return $db->loadObjectList($sql);
	}	

	static function loadLookupModulos(){
		$id_company = ExjUser::GetIdCompania();
		
		$sql = "SELECT
  fit.module_allow AS value, 
  fit.module_allow AS text, 
  Count(fit.id_file_type) AS num_tipos
FROM
  jos_app_files_type fit
WHERE
  fit.id_company = $id_company
GROUP BY
  fit.module_allow";
		
		$db = Exj::InstanceDatabase();
		return $db->loadObjectList($sql);
	}


	public static function GetRowFileType($ext_file, $module_allow) {
		$id_company = ExjUser::GetIdCompania();

		$where = array();
		$where[] = "fty.id_company = $id_company";
		$where[] = "fty.ext_file = '$ext_file'";
		$where[] = "fty.module_allow = '$module_allow'";

		$where = implode(' AND ', $where);

		$query = "SELECT fty.id_file_type, 
  fty.name_type_file, fty.ext_file, fty.cat_type_file, fty.size_max_bytes 
	 FROM jos_app_files_type fty WHERE $where";
        $query .= " LIMIT 1";

        return self::RendererData(
        	ExjDatabase::GetObjectFromQuery($query),
        	array('id_', 'size_')
        );
	}
	
}

?>