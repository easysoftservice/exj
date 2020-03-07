<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppRolOptionsData
 *
 */
class AppRolOptionsData extends ExjData {
	
	/**
	 * Lista de Opciones del Rol
	 *
	 * @return array de object
	 */
	public static function LoadDataRolOptions(ExjResponse &$response, &$items, $paramsCriteria=null){

        $db = Exj::InstanceDatabase();
       //  $id_company = ExjUser::GetIdCompania();
       
        $dbQuery = new ExjDBQuery();
        
        $gid = 0;
        if ($paramsCriteria) {
			$criteria = new AppRolOptionsCriteriaModel(false);
			$criteria->bind($paramsCriteria);
			
			$gid = $criteria->gid;
			if (!ExjCriteriaModel::IsSettedValue($gid)) {
				$gid = 0;
			}
			
			$criteria->resetField('gid');
        }
        
        if (!$gid) {
			$response->setMsgError("Se requiere el Rol!");
			return false;
		}
        
        $subQueryRules = "SELECT 
      r.axo_section, Count(r.axo_value) AS nro_rules
    FROM 
      jos_noixacl_rules r INNER JOIN jos_core_acl_aro_groups aa_grp ON r.aro_value = aa_grp.value
    WHERE
      aa_grp.id = $gid AND
      r.aco_section = 'com_k2' AND r.aco_value = 'category' AND r.aro_section = 'users' 
    GROUP BY 
      r.axo_section";
        
        $dbQuery->setFields("mnu.id AS id_menu, mnu.name AS name_menu, mnu.parent
  AS id_parent_menu, mnu.sublevel, mnu.access, mnu.params,
  mnu.ordering, k2.id AS axo_section, k2.name AS name_module, axo_grp.name AS name_comp,
  subq_rules.nro_rules");
        
        $dbQuery->setTables(" jos_menu mnu 
        INNER JOIN jos_k2_categories k2 ON mnu.access = k2.access 
        LEFT JOIN ($subQueryRules) subq_rules ON k2.id = subq_rules.axo_section 
        INNER JOIN jos_groups axo_grp ON k2.access = axo_grp.id");
        
        
        $idMenuExit = 67;
        $componentsExepts = null;
        $idsRootsExepts = null;
        if (!ExjUser::IsRolSuperAdmin()) {
        	// SE OCULTAN MODULOS QUE NO PUEDEN VER USUARIOS COMUNES
        	$componentsExepts = array('app_loc_countries', 'exj_hld_incidents', 'exj_sys_users');
        	$componentsExepts[] = 'exj_helpdesks';
        	$componentsExepts[] = 'app_loc_sites';
        	
        	$idsRootsExepts = array();
        	$idsRootsExepts[] = 53; // Administration
        }
        
        $dbQuery->addConditions("mnu.menutype = 'mnu_app_main'");
        $dbQuery->addConditions("mnu.published = 1");
        $dbQuery->addConditions("mnu.type = 'component'");
        $dbQuery->addConditions("k2.published = 1");
        $dbQuery->addConditions("mnu.id <> $idMenuExit");
        if ($componentsExepts) {
	        foreach ($componentsExepts as $componentExept) {
	        	$dbQuery->addConditions("axo_grp.name <> '$componentExept'");
	        }
        }
        if ($idsRootsExepts && count($idsRootsExepts) > 0) {
        	$conditionsExepts = array();
        	foreach ($idsRootsExepts as $idRootsExept) {
        		$conditionsExepts[] = "(mnu.id <> $idRootsExept AND mnu.parent <> $idRootsExept)";
        	}
        	
        	$conditionsExepts = implode(' AND ', $conditionsExepts);
        	
        	$dbQuery->addConditions("($conditionsExepts)");
        }
        
        $dbQuery->withOutPaging();
        $dbQuery->clearParamSort();
        
        $dbQuery->setOrdersFirst("mnu.sublevel");
        $dbQuery->addOrders("mnu.parent, mnu.ordering");
        
  		/* -------LOAD PARAMS--------------------- */
		$itemsRaws = $dbQuery->getRows();
		if (!$dbQuery->isValid()) {
			return false;
		}

		// print_r($itemsRaws);
		
		$items = array();
		self::_renderItemsTree($itemsRaws, $items);
		
     //   $dbQuery->writeQueryExecuted();
     //	print_r($items[0]);
        
        return true;
	}
	
	private static function _renderItemsTree($items, &$itemsTree){
		
		foreach ($items as $item) {
			$itemsChilds = array();
			foreach ($items as $itemChild) {
				if ($item->id_menu == $itemChild->id_parent_menu) {
					$itemsChilds[] = $itemChild;
				}
			}
			
			$checked = false;
			$cls = '';
			
			if (count($itemsChilds) == 0) {
				// nodo no tiene hijos, es ultimo nodo
			//	$cls = self::_GetClassFromParams($item->params);
			//	$itemsTree[] = self::_NewNodeChildTree($item->name_menu, $checked, $cls);
			}
			else {
				$childrens = array();
				foreach ($itemsChilds as $itemChildAdd) {
					$childrens[] = self::_NewNodeChildTree($itemChildAdd);
				}
				
				$itemsTree[] = self::_NewNodeRootTree($item, $childrens);
				
				/*
				$childrens = array();
				self::_renderItemsTree($itemsChilds, $childrens);
				if (count($childrens) == 0) {
					continue;
				}
			
				$itemsTree[] = self::_NewNodeRootTree($item->name_menu, $cls, $childrens);
				*/
			}
		}
	}
	
	private static function _GetClassFromParams($params){
		$cls = '';
		if (!$params) {
			return $cls;
		}
		
		$params = explode("\n", $params);
		if (count($params) > 1) {
			foreach ($params as $paramKeyValue) {
				if (!$paramKeyValue) {
					continue;
				}
				
				$paramKeyValue = explode('=', $paramKeyValue);
				if (count($paramKeyValue) != 2) {
					continue;
				}
				
				if ($paramKeyValue[0] == 'pageclass_sfx') {
					$cls = $paramKeyValue[1];
				}
			}
		}
		
		return $cls;
	}
	
	private function _NewNodeRootTree($itemData, $childrens=null){
		$text = $itemData->name_menu;
		
		$iconCls = self::_GetClassFromParams($itemData->params);
		if (!$iconCls) {
			$iconCls = 'folder';
		}
		
		$node = new stdClass();
		$node->text = $text;
		
		// data de la entidad de permisos
		$node->axo_section = $itemData->axo_section;;
		$node->name_comp = $itemData->name_comp;;
		$node->id_menu = $itemData->id_menu;;
		$node->id_parent_menu = $itemData->id_parent_menu;
		$node->nroRules = ($itemData->nro_rules ? $itemData->nro_rules:0);
		
		if ($iconCls) {
			$node->iconCls = $iconCls;
		}
		
		$node->singleClickExpand = true;
		$node->expanded = true;
		
		$checked = false;
		
		if ($childrens) {
			$node->children = $childrens;
			foreach ($childrens as $itemChildren) {
				if ($itemChildren->checked) {
					$checked = true;
					break;
				}
			}
		}
		
		$node->originalChecked = $node->checked = $checked;
		if ($checked) {
			$node->cls = 'exj-checked';
		}
		
		return $node;
	}
	
	private static function _NewNodeChildTree($itemChild){
		$node = self::_NewNodeRootTree($itemChild);
		$node->leaf = true;
		
		if ($itemChild->nro_rules && $itemChild->nro_rules > 0) {
			$node->originalChecked = $node->checked = true;
			$node->cls = 'exj-checked';
		}
		
		unset($node->expanded);
		
		return $node;
	}
	
	/**
	 * Crea reglas para el grupo de usuario
	 *
	 * @param string $aro_value
	 * @param int $axo_section
	 * @param array $rules Array de strings
	 * @param string $msgError Si ocurre un error se retorna en esta variable el motivo del error
	 * @return bool
	 */
	public static function CreateACL_Rules($aro_value, $axo_section, $rules, &$msgError){
		$db = Exj::InstanceDatabase();
		$msgError = '';
		
		if (!$aro_value) {
			$msgError = "No se indicó ARO value para Crear reglas.";
			return false;
		}
		
		$axo_section = intval($axo_section);
		
		if (!$axo_section || is_nan($axo_section)) {
			$msgError = "No se indicó axo section para Crear reglas.";
			return false;
		}
		
		if (!$rules || !is_array($rules)) {
			$msgError = "No se indicaron las reglas a adicionar.";
			return false;
		}
		
		if (count($rules) == 0) {
			$msgError = "La lista de reglas esta vacio. No se pueden crear reglas.";
			return false;
		}
		
		$valuesSQL = array();
		$aco_section = 'com_k2';
		$aco_value = 'category';
		$aro_section = 'users';
		
		foreach ($rules as $rule) {
			$valueSQL = "'$aco_section', '$aco_value', '$aro_section', '$aro_value', $axo_section, '$rule'";
			
			$valuesSQL[] = "($valueSQL)";
		}
		
		$valuesSQL = implode(', ', $valuesSQL);
		
		$query = "INSERT INTO jos_noixacl_rules(aco_section, aco_value, aro_section, aro_value, axo_section, axo_value)";
		$query .= " VALUES $valuesSQL";
		
		$db->query($query);
		if (!$db->isValid()) {
			$msgError = $db->getErrorMsg();
			return false;
		}
		
		return true;
	}
	
	/**
	 * Elimina las reglas acl de los usuarios
	 *
	 * @param string $aro_value
	 * @param array $axo_sections Se puede indicar un valor int
	 * @param string $msgError Si ocurre un error se retorna en esta variable el motivo del error
	 * @return int Nro de registros eliminado, si ocurre un error retorna false
	 */
	public static function DeleteACL_Rules($aro_value, $axo_sections, &$msgError){
		$db = Exj::InstanceDatabase();
		$msgError = '';
		
		if (!$aro_value) {
			$msgError = "No se indicó ARO value para eliminar reglas.";
			return false;
		}
		
		if (!$axo_sections) {
			$msgError = "No se indicó axo sections para eliminar reglas.";
			return false;
		}
		
		$where = array();
		$where[] = "aco_section = 'com_k2'";
		$where[] = "aco_value = 'category'";
		$where[] = "aro_section = 'users'";
		$where[] = "aro_value = '$aro_value'"; // valor del Rol
		if (is_array($axo_sections) && count($axo_sections) == 1) {
			$axo_sections = $axo_sections[0];
		}
		
		if (is_array($axo_sections)) {
			$axo_sections = implode(',', $axo_sections);
			$where[] = "axo_section IN ($axo_sections)";
		}
		else {
			$where[] = "axo_section = $axo_sections";
		}
		
		
		$where = implode(' AND ', $where);
		
		$query = "DELETE FROM jos_noixacl_rules";
		$query .= " WHERE $where";
		
		$db->query($query);
		if (!$db->isValid()) {
			$msgError = $db->getErrorMsg();
			return false;
		}
		
		$affectedRows = $db->getAffectedRows();
		
		return $affectedRows;
	}
	
}

?>