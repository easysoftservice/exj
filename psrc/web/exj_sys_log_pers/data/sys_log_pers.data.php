<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppSysLogPersData
 *
 */
class AppSysLogPersData extends ExjData {
	const USER_TYPE_SUPER_ADMIN = 'Super Administrator';
	const USER_TYPE_ADMIN = 'Administrador';

	const TYPE_PROP_STRING = 'STRING';
	const TYPE_PROP_INT = 'INT';
	const TYPE_PROP_FLOAT = 'FLOAT';
	const TYPE_PROP_DATE = 'DATE';
	const TYPE_PROP_DATETIME = 'DATETIME';
	
	const MSG_NOT_HAVE_HISTORY = 'No existe historial de cambios del registro seleccionado.';
	
	/**
	 * Lista de Logs de Persistencia
	 *
	 * @return array de object
	 */
	public static function LoadListSysLogsItems(ExjResponse &$response, $paramsCriteria)
	{
        global $exj;
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("i.id_log_pers_item, ofc.cod_empresa, t.name_table, t.alias_table,
  p.alias_prop, p.name_prop, p.type_prop, i.action_pers,
  i.value_old, i.value_new, i.ref_change,
  i.id_primary_key_current, i.id_primary_key_root, i.alias_model,
  u.username AS usr_change, i.modificado_dt");
        
        $dbQuery->setTables("exj_log_pers_items i 
  LEFT JOIN jos_users u ON i.id_usuario_modifico = u.id 
  INNER JOIN exj_log_pers_props p ON i.id_log_pers_prop = p.id_log_pers_prop 
  INNER JOIN exj_log_pers_tables t ON p.id_log_pers_table = t.id_log_pers_table 
  LEFT JOIN app_loc_empresas ofc ON i.id_empresa = ofc.id_empresa");
        

		$criteria = new AppSysLogPersCriteriaModel(false);
		if ($criteria->bind($paramsCriteria)) {
			$criteria->addConditionsQuery($dbQuery, array(
					'id_log_pers_table',
					'id_primary_key_current', 
					'id_primary_key_root')
			);
			
			$dbQuery->addConditions("(t.id_log_pers_table = $criteria->id_log_pers_table OR t.id_parent_table = $criteria->id_log_pers_table)");
			
			
			if (!$criteria->isEmptyField('id_primary_key_current') && $criteria->isEmptyField('id_primary_key_root')) {
				$criteria->id_primary_key_root = $criteria->id_primary_key_current;
			}
			
			if (!$criteria->isEmptyField('id_primary_key_current') && !$criteria->isEmptyField('id_primary_key_root')) {
				$dbQuery->addConditions("(id_primary_key_current = $criteria->id_primary_key_current OR id_primary_key_root = $criteria->id_primary_key_root)");
			}
			else{
				if (!$criteria->isEmptyField('id_primary_key_current')) {
					$dbQuery->addConditions("id_primary_key_current = $criteria->id_primary_key_current");
				}
				if (!$criteria->isEmptyField('id_primary_key_root')) {
					$dbQuery->addConditions("id_primary_key_root = $criteria->id_primary_key_root");
				}
			}
		}
		
		if (!$criteria->isValid()) {
			$response->setMsgError($criteria->getBrokenRules());
			return false;
		}
		
		
        // $dbQuery->setOrdersFirst("cod_empresa");
        $dbQuery->addOrders("id_log_pers_item");
        
        $dbQuery->addMappingSort('modificado_dt', 'i.modificado_dt');
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("i.id_log_pers_item");
		$items = $dbQuery->getRows();
		
	// $dbQuery->writeQueryExecuted();
		
		if (!$dbQuery->isValid()) {
			$response->setMsgError($dbQuery->getErrorMsg());
			return false;
		}
		
		if ($total == 0){
			$response->setMsgInfo(self::MSG_NOT_HAVE_HISTORY);
			$response->setDataTopics($items, $total);
			return true;
		}
		
	//	self::AddPropLastChangeToItems($items);
		
		self::RenderListSysLogsItems($items);
		
        // $dbQuery->writeQueryExecuted();
        
        $response->setDataTopics($items, $total);
    
        return true;
	}
	
	public static function RenderListSysLogsItems(&$items){
		if (count($items) == 0) {
			return ;
		}
		
		$itemsIdsLogs = array();
		
		foreach ($items as &$item) {
			if ($item->type_prop == self::TYPE_PROP_DATETIME) {
				if ($item->value_old) {$item->value_old = ExjDate::ConvertToDateTimeDisplay($item->value_old);}
				if ($item->value_new) {$item->value_new = ExjDate::ConvertToDateTimeDisplay($item->value_new);};
			}
			elseif ($item->type_prop == self::TYPE_PROP_DATE){
				if ($item->value_old) {$item->value_old = ExjDate::ConvertToDateDisplay($item->value_old);};
				if ($item->value_new) {$item->value_new = ExjDate::ConvertToDateDisplay($item->value_new);};
			}
			elseif ($item->type_prop == self::TYPE_PROP_INT) {
				if ($item->value_old) {$item->value_old = intval($item->value_old);};
				if ($item->value_new) {$item->value_new = intval($item->value_new);};
				// echo "<br/>item->name_prop: $item->name_prop";
				switch ($item->name_prop) {
					case 'confirmado_abo':
					case 'es_activo_prd':
					case 'es_activo_prov':
					case 'es_activa_zona':
					case 'es_activo_bod':
					case 'es_activo_car':
					case 'es_activo_cli':
					case 'es_activo_cat':
					case 'es_cat_personalizada':
						if ($item->value_old !== null) {
							$item->value_old = $item->value_old ? 'Si':'No';
						}
						if ($item->value_new !== null) {
							$item->value_new = $item->value_new ? 'Si':'No';
						}
					break;
					
					case 'id_identificacion_tipo':
						self::_AddIdsLogItem(
							$itemsIdsLogs, $item,
							'app_identificacion_tipos',
							'id_identificacion_tipo',
							'nombre_itipo'
						);
					break;

					case 'id_hld_catalog_state':
					case 'id_hld_catalog_priority':
					case 'id_hld_catalog_response':
					case 'id_hld_catalog_hld':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_exj_helpdesk_catalogs', 'id_hld_catalog', 'name_hld_catalog');
					break;
					
					case 'id_sit_dest':
					case 'id_sit':
					case 'id_sit_rate':
					case 'id_sit_company':
						$tableX = 'jos_app_loc_sites city LEFT JOIN jos_app_loc_sites state ON city.id_sit_parent = state.id_sit';
						$fkX = 'city.id_sit';
						$ftX = "CONCAT_WS(', ', city.name_sit, state.cod_sit)";
						
						self::_AddIdsLogItem($itemsIdsLogs, $item, $tableX, $fkX, $ftX);
					break;
					
					case 'id_cliente':
						self::_AddIdsLogItem(
							$itemsIdsLogs,
							$item,
							'app_clientes', 'id_cliente', 'num_identificacion'
						);
					break;

					case 'id_loc_zip':
					case 'id_loc_zip_company':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_app_loc_zip', 'id_loc_zip', 'code_zip');
					break;
					
					case 'id_cat_item_status':
					case 'id_cat_item_type_driver':
					case 'id_cat_item_civil_status':
					case 'id_cat_item_type':
					case 'id_cat_item_payment_type':
					case 'id_cat_item_scale_inf':
					case 'id_cat_item_status_inf':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_app_catalogs_items', 'id_cat_item', 'name_cat_item');
					break;
					
					case 'id_user':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_users', 'id', 'name');
					break;
					
					case 'id_empresa':
						self::_AddIdsLogItem(
							$itemsIdsLogs,
							$item,
							'app_loc_empresas', 'id_empresa', 'nom_empresa'
						);
					break;

					case 'id_cli_categoria':
						self::_AddIdsLogItem(
							$itemsIdsLogs,
							$item,
							'app_cli_categorias',
							'id_cli_categoria',
							'nombre_cat'
						);
					break;

					case 'id_pago_forma':
						self::_AddIdsLogItem(
							$itemsIdsLogs,
							$item,
							'app_pagos_formas',
							'id_pago_forma',
							'nombre_pf'
						);
					break;
					
					case 'id_hld_catalog_priority':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_exj_helpdesk_catalogs', 'id_hld_catalog', 'name_hld_catalog');
					break;
					
					case 'id_persona':
						$tableX = 'jos_app_personas p';
						$fkX = 'p.id_persona';
						$ftX = "CONCAT_WS(' ', p.nombres_persona, p.alias_persona, p.apellidos_persona)";
						
						self::_AddIdsLogItem($itemsIdsLogs, $item, $tableX, $fkX, $ftX);
					break;
					
					case 'id_file':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_app_files', 'id_file', 'nameext_file');
					break;
					
					case 'id_company_app':
						self::_AddIdsLogItem($itemsIdsLogs, $item, 'jos_app_companies', 'id_company_app', 'name_company');
					break;
					
					case 'id_group_acl_aro':
						self::_AddIdsLogItem(
							$itemsIdsLogs, 
							$item, 
							'jos_core_acl_aro_groups', 'id', 'name'
						);
					break;
				}
				
			}
			
			$item->alias_model = ExjText::_($item->alias_model);
		} // for
		
		self::_SetterTextToIdsLogsItems($items, $itemsIdsLogs);
	}
	
	private static function _SetterTextToIdsLogsItems(&$items, $itemsIdsLogs){
		if (count($itemsIdsLogs) == 0) {
			return ;
		}
		
		$db = Exj::InstanceDatabase();
		$isSuccess = true;
		
		foreach ($itemsIdsLogs as $itemIdLog) {
			$fk = $itemIdLog->fieldKey;
			$ft = $itemIdLog->fieldText;
			$ids = $itemIdLog->ids; // 3 dimensiones
			
			$idsTexts = array();
			foreach ($ids as $id_log_pers_item => $idsValues) {
				foreach ($idsValues as $textOldNew => $idsValuesOldNews) {
					foreach ($idsValuesOldNews as $idText) {
						if (!in_array($idText, $idsTexts)) {
							$idsTexts[] = $idText;
						}
					}
				}
			}
			
			$idsTexts = implode(',', $idsTexts);
			
			$query = "SELECT $fk AS value_id, $ft AS value_text";
			$query .= " FROM " . $itemIdLog->nameTable;
			$query .= " WHERE $fk IN ($idsTexts)";

			$itemsTexts = $db->loadObjectList($query);
			if (!$db->isValid()) {
				$isSuccess = false;
				break;
			}
			
			foreach ($items as &$item) {
				$id_log_pers_item = $item->id_log_pers_item;

				foreach ($ids as $id_log_pers_itemX => $idsValuesX) {
					if ($id_log_pers_itemX != $id_log_pers_item) {
						continue;
					}
					
					foreach ($idsValuesX as $textOldNewX => $idsValuesOldNewsX) {
						foreach ($idsValuesOldNewsX as $idTextX) {
							foreach ($itemsTexts as $itemText) {
								if ($itemText->value_id != $idTextX) {
									continue;
								}
								
								if ($textOldNewX == 'olds') {
									$item->value_old = $itemText->value_text;
								}
								if ($textOldNewX == 'news') {
									$item->value_new = $itemText->value_text;
								}
							}
						}
					}
				}
			}
		}
		
		return $isSuccess;
	}
	
	private static function _AddIdsLogItem(&$itemsIdsLogs, $itemLogItem, $nameTable, $fieldKey, $fieldText){
		if (!$itemLogItem->value_old && !$itemLogItem->value_new) {
			return ;
		}
		
		$newItemIdLog = null;
		if (count($itemsIdsLogs) > 0) {
			foreach ($itemsIdsLogs as &$itemIdLog) {
				if ($itemIdLog->nameTable == $nameTable) {
					$newItemIdLog = $itemIdLog;
					break;
				}
			}
		}
		
		$idItem = $itemLogItem->id_log_pers_item;

		$isNew = false;
		if (!$newItemIdLog) {
			$isNew = true;
			$newItemIdLog = new stdClass();
			$newItemIdLog->nameTable = $nameTable;
			$newItemIdLog->fieldKey = $fieldKey;
			$newItemIdLog->fieldText = $fieldText;
			$newItemIdLog->ids = array();
		}
		
		if ($itemLogItem->value_old) {
			if (isset($newItemIdLog->ids[$idItem]['olds'])) {
				if (!in_array($itemLogItem->value_old, $newItemIdLog->ids[$idItem]['olds'])) {
					$newItemIdLog->ids[$idItem]['olds'][] = $itemLogItem->value_old;
				}
			}
			else {
				$newItemIdLog->ids[$idItem]['olds'][] = $itemLogItem->value_old;
			}
		}

		if ($itemLogItem->value_new) {
			if (isset($newItemIdLog->ids[$idItem]['news'])) {
				if (!in_array($itemLogItem->value_new, $newItemIdLog->ids[$idItem]['news'])) {
					$newItemIdLog->ids[$idItem]['news'][] = $itemLogItem->value_new;
				}
			}
			else {
				$newItemIdLog->ids[$idItem]['news'][] = $itemLogItem->value_new;
			}
		}
		
		
		if ($isNew) {
			$itemsIdsLogs[] = $newItemIdLog;
		}
	}

	static function getInfoPerson($id_persona){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $id_company = ExjUser::GetIdCompania();
        
        $sql = "SELECT
  usr.id AS id_user, usr.name, usr.username, usr.block,
  if(isnull(peo.email_person), usr.email, peo.email_person) AS email,
  usr.usertype, usr.lastvisitDate, syu.id_log_pers_item,
  peo.nro_doc_persona, peo.nombres_persona,
  peo.apellidos_persona, dty.name_doc,
  ofc.nom_empresa, peo.id_doc_tipo
FROM
  jos_users usr INNER JOIN
  jos_exj_sys_users syu ON usr.id = syu.id_user INNER JOIN
  jos_app_personas peo ON syu.id_persona = peo.id_persona INNER JOIN
  app_loc_empresas ofc ON syu.id_empresa = ofc.id_empresa INNER JOIN
  jos_app_doc_tipos dty ON peo.id_doc_tipo = dty.id_doc_tipo
WHERE
  ofc.id_company = $id_company AND peo.id_persona = $id_persona";
        
        $db->setQuery($sql);
        $infoPerson = null;
        $db->loadObject($infoPerson);
        
        if ($db->getErrorMsg()) {
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
	
        return $infoPerson;
	}

	/**
	 * Obtiene una lista lookup de Tablas de Logs
	 *
	 * @return array
	 */
	static function GetLookupTables(){
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  t.id_log_pers_table AS value, t.alias_table AS text, t.name_table, t.name_field_key
 FROM
   exj_log_pers_tables t
 ORDER BY
   t.order_table, t.alias_table";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	global $exj;
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}
	
	static function GetLookupPropTypes(){
        $items = array();
        
        $items[] = self::newItemLookup(self::TYPE_PROP_STRING, ExjText::__(self::TYPE_PROP_STRING));
        $items[] = self::newItemLookup(self::TYPE_PROP_INT, ExjText::__(self::TYPE_PROP_INT));
        $items[] = self::newItemLookup(self::TYPE_PROP_FLOAT, ExjText::__(self::TYPE_PROP_FLOAT));
        $items[] = self::newItemLookup(self::TYPE_PROP_DATE, ExjText::__(self::TYPE_PROP_DATE));
        $items[] = self::newItemLookup(self::TYPE_PROP_DATETIME, ExjText::__(self::TYPE_PROP_DATETIME));
        
        return $items;
	}
	
	/**
	 * Lista de LogsPersistencias de Joomla
	 *
	 * @param array $items
	 * @param int $total
	 * @param bool $onlyActives
	 * @param bool $exceptSuperAdmin
	 * @return bool true si fué satisfactorio
	 */
	static function loadLookupLogTables(&$items, &$total, $onlyActives=true, $exceptSuperAdmin=true){
		$dbQuery = new ExjDBQuery();
		
		$dbQuery->setFields("u.id AS value, u.name AS text, u.username, u.usertype");
        
        $dbQuery->setTables("jos_users u");
        
        if ($onlyActives) {
        	$dbQuery->addConditions("u.block = 0");
        }
        
        if ($exceptSuperAdmin) {
        	$dbQuery->addConditions("u.usertype <> '". self::USER_TYPE_SUPER_ADMIN ."'");
        }
        
        $dbQuery->addOrders("u.usertype, u.name");
        
        $dbQuery->setQueryFieldValue('u.id');
        $dbQuery->setQueryField('u.name');
        $dbQuery->setQueryLike(ExjDBQuery::QUERY_LIKE_ALL);
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("u.id");
		$items = $dbQuery->getRows();
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		return true;
	}
	
	static function LoadLogTable(&$dataTable, $nameTable){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT
  lt.id_log_pers_table, lt.name_table, lt.name_field_key
FROM
  exj_log_pers_tables lt
WHERE
  lt.name_table = '$nameTable'";
		
		$db->setQuery($query, 0, 1);
		
		$dataTable = null;
		$db->loadObject($dataTable);
		if (!$db->isValid()) {
			return false;
		}
		
		return ($dataTable ? true:false);
	}
	
}

?>