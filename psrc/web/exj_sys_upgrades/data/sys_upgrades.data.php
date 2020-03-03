<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppSysUpgradesData
 *
 */
class AppSysUpgradesData extends ExjData {
	
	/**
	 * Lista de Actualizaciones del Sistema
	 *
	 * @return array de object
	 */
	static function loadListSysUpgrades(&$items, &$total, $paramsCriteria=null)
	{
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("upg.id_sys_upg, upg.file_zip_code, upg.file_zip_sql,
  upg.version_upg, upg.state_upg, upg.desc_upg, upg.id_file_code, upg.id_file_sql,
  upg.modificado_dt, usr.name AS name_usr");
        
        $dbQuery->setTables("jos_exj_sys_upgrades upg INNER JOIN
  jos_users usr ON upg.id_usuario_modifico = usr.id");
        
        
        if ($paramsCriteria) {
			$criteriaSysUpgrades = new AppSysUpgradesCriteriaModel(false);
			if ($criteriaSysUpgrades->bind($paramsCriteria)) {
				$criteriaSysUpgrades->addConditionsQuery($dbQuery);
			}
        }
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("upg.id_sys_upg");
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		
        // $dbQuery->writeQueryExecuted();
        
		foreach ($items as &$item) {
			$dataStateUpgrade = self::GetDataStateUpgrade($item->state_upg);
			$item->state_text = $dataStateUpgrade->text;
			$item->color = $dataStateUpgrade->color;
		}
    
        return $dbQuery->isValid();
	}


	/**
	 * Lista de Actualizaciones
	 *
	 * @param bool $onlyUsersNoAssigned true solo los asginados como usuarios del sistema
	 * @param int $blocked 0 incidica que se mostrarán solo los No bloqueados, null todos
	 * @return array
	 */
	static function getLookupVersiones(){
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  upg.id_sys_upg AS value, upg.version_upg AS text,
  upg.file_zip_code, upg.file_zip_sql, upg.state_upg
FROM
  jos_exj_sys_upgrades upg
ORDER BY
  upg.modificado_dt DESC";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
     //   echo '<br/>query: '.$db->getQuery();
        
        return $items;
	}
	
	
	static function getLookupEstados(){
        $items = array();
        
        // Exj::IncludeClass('AppSysUpgradeEditableModel', 'exj_sys_upgrades');
        
        $items[] = self::GetDataStateUpgrade(AppSysUpgradeEditableModel::ESTADO_PENDIENTE);
        $items[] = self::GetDataStateUpgrade(AppSysUpgradeEditableModel::ESTADO_EJECUTADO_CODE);
      	$items[] = self::GetDataStateUpgrade(AppSysUpgradeEditableModel::ESTADO_EJECUTADO_DB);
      	$items[] = self::GetDataStateUpgrade(AppSysUpgradeEditableModel::ESTADO_EJECUTADO_TODO);
        
        return $items;
	}
	
	public static function GetDataStateUpgrade($stateUpgrade, $toUpper = false, $isTextHTML = false, $addBold = false)
	{
		$dataStateUpgrade = new stdClass();
		$dataStateUpgrade->value = intval($stateUpgrade);
		$dataStateUpgrade->text = $dataStateUpgrade->value;
		$dataStateUpgrade->color = '';
			
		switch ($dataStateUpgrade->value) {
			case AppSysUpgradeEditableModel::ESTADO_PENDIENTE:
				$dataStateUpgrade->text = "Pendiente";
				$dataStateUpgrade->color = 'red';
			break;
			case AppSysUpgradeEditableModel::ESTADO_EJECUTADO_CODE:
				$dataStateUpgrade->text = "Código Ejecutado";
			break;
			
			case AppSysUpgradeEditableModel::ESTADO_EJECUTADO_DB:
				$dataStateUpgrade->text = "Ejecutado DB";
			break;
			
			case AppSysUpgradeEditableModel::ESTADO_EJECUTADO_TODO:
				$dataStateUpgrade->text = "Ejecutado Todo";
				$dataStateUpgrade->color = 'green';
			break;
			
			default:
				$dataStateUpgrade->text = "Estado de Actualización $dataStateUpgrade->value Desconocido";
			break;
		}
			
			
		if ($toUpper) {
			$dataStateUpgrade->text = strtoupper($dataStateUpgrade->text);
		}
		if ($addBold && $isTextHTML) {
			$dataStateUpgrade->text = "<b>$dataStateUpgrade->text</b>";
		}
		
		if ($isTextHTML && $dataStateUpgrade->color) {
			$dataStateUpgrade->text = "<span style='color:".$dataStateUpgrade->color."'>$dataStateUpgrade->text</span>";
		}
			
		return $dataStateUpgrade;
	}

	public static function GetDataFilesBks(&$msgError){
		$msgError = '';
		$pathFiles = AppSysUpgradeModel::GetFilesBks($msgError, true);
		if ($msgError) {
			return false;
		}

		$items = array();
		if (empty($pathFiles)) {
			return $items;
		}

		foreach ($pathFiles as $pathFile) {
			$item = new stdClass();
			$item->name_file = basename($pathFile);
			$item->size_file = ExjUtil::RenderSizeBytes(filesize($pathFile), 3);
			$item->d_change_file = date('Y-m-d H:i:s', filemtime($pathFile));

			$items[] = $item;
		}

		return $items;
	}
}

?>