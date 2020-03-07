<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppRolsData
 *
 */
class AppRolsData extends ExjData {
	
	/**
	 * Lista de Roles
	 *
	 * @return array de object
	 */
	static function LoadListRoles(&$items, &$total, $paramsCriteria=null){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("r.id_rol, r.id_group_acl_aro, aag.value AS code_rol, r.is_internal_rol, r.is_required_rol,
  aag.name AS name_rol, r.detail_rol, r.modificado_dt, u.username AS name_usr");
        
        $dbQuery->setTables("exj_rols r 
		  INNER JOIN jos_core_acl_aro_groups aag ON r.id_group_acl_aro = aag.id 
		  LEFT JOIN jos_users u ON r.id_usuario_modifico = u.id");
        
        /*
        if ($paramsCriteria) {
			// $exj->includeModelCriteria('rols');
			$criteriaRoles = new AppRolsCriteriaModel(false);
			if ($criteriaRoles->bind($paramsCriteria)) {
				$criteriaRoles->addConditionsQuery($dbQuery);
			}
        }
        */
        
        // solo se muestran los roles que puede ver el usuario, no los internos del sistema
        $dbQuery->addConditions("r.is_internal_rol = 0");
        
        $dbQuery->addConditions("r.id_company = $id_company");
        
     //   $dbQuery->setOrdersFirst("r.is_internal_rol DESC");
     //   $dbQuery->addOrders("r.name_rol");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("r.id_rol");
		$items = $dbQuery->getRows();
		
     //   $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();
	}
	
	static function GetLookupRols(){
        $db = Exj::InstanceDatabase();
        
   //     $parent_id = Exj::GetValueCfg('ugidPulicFrontend');
        
        $where = array();
     //   $where[] = "g.parent_id = $parent_id";
        $where[] = "r.is_internal_rol = 0";
        
        $where = implode(" AND ", $where);
        
        $sql = "SELECT 
		  r.id_group_acl_aro AS value, g.name AS text, g.value AS code_rol
		 FROM 
		  exj_rols r 
		  INNER JOIN jos_core_acl_aro_groups g ON r.id_group_acl_aro = g.id 
		 WHERE $where";
        
        $sql .= " ORDER BY r.is_required_rol DESC, g.lft, g.rgt";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}
	
	/**
	 * Obtiene el nro de usuarios relacionados con el id del rol
	 *
	 * @param int $id_rol
	 * @return int Si existe un error retorna null sino un valor numérico
	 */
	static function GetNumUserRelated($id_rol){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
     Count(u.id) AS nro_users 
  FROM 
  exj_rols r INNER JOIN jos_users u ON r.id_group_acl_aro = u.gid 
 WHERE r.id_rol = $id_rol";
		
		$numUsers = $db->loadResult($query);
		if (!$db->isValid()) {
			return null;
		}
		
		return $numUsers;
	}
	
	static function LoadRolesCodeName($id_rol_except, $codeRol, $nameRol, &$msgError){
		$items = array();
		$msgError = '';
		if (!$codeRol && !$nameRol) {
			return $items;
		}
		
		$db = Exj::InstanceDatabase();
		
		$where = array();
		if ($id_rol_except) {
			$where[] = "r.id_rol <> $id_rol_except";
		}
		if ($codeRol || $nameRol){
			$optionsORs = array();
			if ($codeRol) {
				$optionsORs[] = "aag.value = '$codeRol'";
			}
			if ($nameRol) {
				$optionsORs[] = "aag.name = '$nameRol'";
			}
			
			$optionsORs = implode(' OR ', $optionsORs);
			
			$where[] = "($optionsORs)";
		}
		
		$where = implode(' AND ', $where);
		
		$query = "SELECT
		  r.id_rol, r.id_group_acl_aro, aag.name AS name_rol, aag.value AS code_rol,
		  u.username, r.modificado_dt
		FROM
		  exj_rols r 
		  INNER JOIN jos_core_acl_aro_groups aag ON r.id_group_acl_aro = aag.id 
		  LEFT JOIN jos_users u ON r.id_usuario_modifico = u.id 
		WHERE $where";
		
		$items = $db->loadObjectList($query);
		if (!$db->isValid()) {
			$msgError = $db->getErrorMsg();
			return null;
		}
		
		return $items;
	}
	
	static function GetGroupsNotRelated($code, $name){
		$db = Exj::InstanceDatabase();
		
		$where = array();
		$where[] = "r.id_group_acl_aro IS NULL";
		
		
		$conditionsORs = array();
		if ($code){
			$conditionsORs[] = "aag.value LIKE '$code'";
		}
		if ($name){
			$conditionsORs[] = "aag.name LIKE '$name'";
		}
		
		if (count($conditionsORs) > 0) {
			$conditionsORs = implode(' OR ', $conditionsORs);
			$where[] = "($conditionsORs)";
		}
		
		$where = implode(' AND ', $where);
		$query = "SELECT 
		  aag.id, aag.parent_id, aag.name, aag.value 
		FROM 
		  jos_core_acl_aro_groups aag 
		  LEFT JOIN exj_rols r ON r.id_group_acl_aro = aag.id 
		WHERE $where";
		
		$items = $db->loadObjectList($query);
		if (!$db->isValid()) {
			return false;
		}
		
		return $items;
	}
	
	static function LoadRgtFromGroups($idGroup, &$rgt){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
		  aag.rgt 
		FROM 
		  jos_core_acl_aro_groups aag 
		WHERE 
		  aag.id = $idGroup";
		
		$rgt = $db->loadResult($query);
		if (!$db->isValid()) {
			return false;
		}
		
		return true;
	}
	
	static function InsertGroupACL_ARO($parent_id, $name, $valueLFT, $valueRGT, $code, &$idGroupNew, &$msgInvalid){
		$db = Exj::InstanceDatabase();
		$idGroupNew = null;
		$msgInvalid = '';
		
		// NOTE: No se requiere iniciar transaccion, ya que el modelo editable ya lo inicia
		
		$cmdInsert = "INSERT INTO jos_core_acl_aro_groups(parent_id, name, lft, rgt, value)";
		$cmdInsert .= " VALUES($parent_id, '$name', $valueLFT, $valueRGT, '$code')";
		
		// $db->setQuery($cmdInsert);
		$db->query($cmdInsert);
		if (!$db->isValid()){
			$msgInvalid = $db->getErrorMsg();
			return false;
		}
		
		$idGroupNew = $db->insertid();
		if (!$idGroupNew) {
			$msgInvalid = "No se generó ID del group acl aro!";
			return false;
		}
		
		// actualizar al padre
		$valueRGT += 1;
		$cmdUpdate = "UPDATE jos_core_acl_aro_groups";
		$cmdUpdate .= " SET rgt=$valueRGT";
		$cmdUpdate .= " WHERE id=$parent_id";
		$db->query($cmdUpdate);
		if (!$db->isValid()){
			$msgInvalid = $db->getErrorMsg();
			return false;
		}
		
		return true;
	}
	
	static function UpdateGroupACL_ARO($id_group_acl_aro, $codeRol, $nameRol, &$msgInvalid){
		$db = Exj::InstanceDatabase();
		$msgInvalid = '';
		
		$fields = array();
		if ($nameRol) {
			$fields[] = "name='$nameRol'";
		}
		if ($codeRol) {
			$fields[] = "value='$codeRol'";
		}
		
		if (count($fields) == 0) {
			// no hay nada que actualizar
			return true;
		}
		
		if ($codeRol) {
			// recuperamos el code anterior para actualizar las reglas
			$queryGrp = "SELECT value FROM jos_core_acl_aro_groups WHERE id=$id_group_acl_aro";
			$codeAntRol = $db->loadResult($queryGrp);
			if (!$db->isValid()) {
				$msgInvalid = $db->getErrorMsg();
				return false;
			}
			
			if ($codeAntRol && ($codeRol != $codeAntRol)) {
				$affectedRowsRules = 0;
				if (!self::ChangeARO_Rules($codeAntRol, $codeRol, $affectedRowsRules, $msgInvalid)) {
					return false;
				}
				
				// echo "<br/>Se actualizaron $affectedRowsRules filas de las reglas ACL. CodeAnt: $codeAntRol CodeNew: $codeRol";
			}
		}
		
		$fields = implode(', ', $fields);
		
		$cmdUpdate = "UPDATE jos_core_acl_aro_groups";
		$cmdUpdate .= " SET $fields";
		$cmdUpdate .= " WHERE id=$id_group_acl_aro";
		$db->query($cmdUpdate);
		if (!$db->isValid()){
			$msgInvalid = $db->getErrorMsg();
			return false;
		}
		
		return true;
	}
	
	/**
	 * Actualiza el nombre del rol del usuario en la reglas acl de permisos
	 *
	 * @param string $codeRolLast
	 * @param string $codeRolNew
	 * @param int $affectedRows
	 * @param string $msgInvalid
	 * @param string $aco_section
	 * @return bool true si no ocurren errores, sino false
	 */
	static function ChangeARO_Rules($codeRolLast, $codeRolNew, &$affectedRows, &$msgInvalid, $aco_section='com_k2'){
		$db = Exj::InstanceDatabase();
		$msgInvalid = '';
		
		$cmdUpdate = "UPDATE jos_noixacl_rules";
		$cmdUpdate .= " SET aro_value='$codeRolNew'";
		$cmdUpdate .= " WHERE aco_section='$aco_section' AND aro_value = '$codeRolLast'";
		$db->query($cmdUpdate);
		if (!$db->isValid()){
			$msgInvalid = $db->getErrorMsg();
			return false;
		}
		
		$affectedRows = $db->getAffectedRows();
		
		return true;
	}
	
	/**
	 * Obtiene el valor del grupo ACL ARO
	 *
	 * @param int $idGroup
	 * @return mixed Si existe un error retorna false, sino retorna string
	 */
	static function GetValueGroupACL_ARO($idGroup){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
		  aag.value 
		FROM 
		  jos_core_acl_aro_groups aag 
		WHERE 
		  aag.id = $idGroup";
		
		$value = $db->loadResult($query);
		if (!$db->isValid()) {
			return false;
		}
		
		return $value;
	}
}

?>