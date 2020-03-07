<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppRolUsersData
 *
 */
class AppRolUsersData extends ExjData {
	const USER_TYPE_SUPER_ADMIN = 'Super Administrator';
	const USER_TYPE_ADMIN = 'Administrador';
	
	static function LoadListRolUsersActives(ExjResponse &$response, &$items, &$total, $paramsCriteria=null){
		// return self::LoadListRolUsers($response, $items, $total, $paramsCriteria, true);
		return self::LoadListRolUsers($response, $items, $total, $paramsCriteria, false);
	}
	
	private static function _GetSQLUsersOfc($inOfficeCurrent = true){
		$id_empresa_current = ExjUser::GetIdEmpresa();
		$id_company = ExjUser::GetIdCompania();
		
		$where = array();
		$where[] = "subq_ofc.id_company = $id_company";
		
		if ($inOfficeCurrent) {
			$where[] = "subq_uofc.id_empresa = $id_empresa_current";
		}
		else {
			$where[] = "subq_uofc.id_empresa <> $id_empresa_current";
		}
		
		$where = implode(" AND ", $where);
		
		$subQueryOfcs = "SELECT 
		  subq_uofc.id_sys_user 
		FROM 
		  jos_exj_sys_user_empresas subq_uofc 
		  INNER JOIN app_loc_empresas subq_ofc ON subq_uofc.id_empresa = subq_ofc.id_empresa
		WHERE $where 
		GROUP BY subq_uofc.id_sys_user";
		
		return $subQueryOfcs;
	}
	
	private static function _AddRelationsUsersEmpresas(ExjDBQuery &$instanceDBQuery, $inOfficeCurrent = true){
		$subQueryOfcs = self::_GetSQLUsersOfc($inOfficeCurrent);	
		
		$instanceDBQuery->addTableJoin("($subQueryOfcs) AS usr_ofc ON sys_user.id_sys_user = usr_ofc.id_sys_user", 'INNER JOIN');
	}
	
	/**
	 * Lista de Usuarios del Sistema
	 *
	 * @param ExjResponse $response
	 * @param array $items
	 * @param int $total
	 * @param object $paramsCriteria
	 * @param bool $onlyUsersActives
	 * @return array
	 */
	public static function LoadListRolUsers(ExjResponse &$response, &$items, &$total, $paramsCriteria=null, $onlyUsersActives=false){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("sys_user.id_sys_user, sys_user.id_user, 
        j_user.name AS user_name, 
        j_user.username AS user_login, 
        j_user.email AS user_email, 
        ofc.nom_empresa, sys_user.id_empresa,
        j_user.lastvisitDate AS date_lastvisit,
  sys_user.id_persona, sys_user.sys_type_theme,
  sys_user.modificado_dt, usr_chg.username AS name_usrchg");
        if ($onlyUsersActives) {
        	$dbQuery->setFields("1 AS is_user_active");
        }
        else {
        	$dbQuery->setFields("IF(j_user.block=0, 1,0) AS is_user_active");
        }
       
        $dbQuery->setFields("p.nro_doc_persona, p.id_doc_tipo, p.id_sit, p.id_loc_zip, p.nombres_persona, p.alias_persona,
        p.apellidos_persona, p.dir_person, p.email_person, p.tlf_persona, p.cell_person, p.birth_date, p.type_sexo, 
        p.id_cat_item_civil_status, p.id_loc_zip, sit.name_sit, sit.id_sit_parent");
        
         
        $dbQuery->setTables("jos_exj_sys_users sys_user 
   INNER JOIN jos_users j_user ON sys_user.id_user = j_user.id 
   INNER JOIN app_loc_empresas ofc ON sys_user.id_empresa = ofc.id_empresa 
  LEFT JOIN jos_users usr_chg ON sys_user.id_usuario_modifico = usr_chg.id");
        
        // filtra los usuario por cada empresa actual
        self::_AddRelationsUsersEmpresas($dbQuery, true);
        
        $dbQuery->addTableJoin('jos_app_personas p ON sys_user.id_persona = p.id_persona', 'LEFT JOIN');
        $dbQuery->addTableJoin('jos_app_loc_sites sit ON p.id_sit = sit.id_sit', 'LEFT JOIN');
        
    //   $dbQuery->addConditions("ofc.id_company = $id_company");
        if ($onlyUsersActives) {
        	$dbQuery->addConditions("j_user.block = 0");
        }
        
        
		// $exj->includeModelCriteria('rol_users', 'exj_rol_users');
		$criteriaRolUsers = new AppRolUsersCriteriaModel(false);
		
		if ($criteriaRolUsers->bind($paramsCriteria)) {
			$criteriaRolUsers->addConditionsQuery($dbQuery);
		}
		
		if (!$criteriaRolUsers->isValid()) {
			$response->setMsgError($criteriaRolUsers->getBrokenRules());
			$items = array();
        	$total =0;
        	return true;
		}
		
		if (!$criteriaRolUsers->gid) {
			$items = array();
        	$total =0;
        	return true;
		}
        
//        $dbQuery->setOrdersFirst("j_user.block, ses.time DESC, ses.client_id");
 //       $dbQuery->addOrders("ses.client_id DESC,nom_empresa,usertype");
        
  		/* -------LOAD PARAMS--------------------- */
  		$dbQuery->loadRowsCount($items, $total, "sys_user.id_sys_user");
	//	$dbQuery->writeQueryExecuted();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		self::RenderListRolUsers($items);
		self::AddPropOrdToItems($items);
		
     //   $dbQuery->writeQueryExecuted();
    
        return true;
	}
	
	static function LoadListRolUnassignedUsers(ExjResponse &$response, &$items, &$total, $paramsCriteria=null){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        $id_ofc_current = ExjUser::GetIdEmpresa();
        
        $dbQuery = new ExjDBQuery();
        // xxx
        $dbQuery->setFields("sys_user.id_sys_user, sys_user.id_user, 
        j_user.name AS user_name, 
        j_user.username AS user_login, 
        j_user.email AS user_email, 
        ofc.nom_empresa, sys_user.id_empresa,
        j_user.lastvisitDate AS date_lastvisit,
        IF(j_user.block=0, 1,0) AS is_user_active,
        grp.name AS user_rol,
  sys_user.id_persona, sys_user.sys_type_theme,
  sys_user.modificado_dt, usr_chg.username AS name_usrchg");
      
        $dbQuery->setTables("jos_exj_sys_users sys_user INNER JOIN
  jos_users j_user ON sys_user.id_user = j_user.id LEFT JOIN
  jos_users usr_chg ON sys_user.id_usuario_modifico = usr_chg.id
  INNER JOIN
  exj_rols r ON j_user.gid = r.id_group_acl_aro INNER JOIN
  jos_core_acl_aro_groups grp ON r.id_group_acl_aro = grp.id
  INNER JOIN
  app_loc_empresas ofc ON sys_user.id_empresa = ofc.id_empresa");
        
        $dbQuery->addConditions("ofc.id_company = $id_company");
     //  	$dbQuery->addConditions("j_user.block = 1");
     	
     	if (!ExjUser::IsRolSuperAdmin()) {
     		$dbQuery->addConditions("sys_user.id_empresa = $id_ofc_current");		
     	}
     
       // filtra los usuario que no están en la empresa actual
        $sqUsersExeptOfcCurrent = "SELECT 
		  su_all.id_sys_user 
		FROM
		  jos_exj_sys_users su_all LEFT JOIN 
		  (
		        SELECT
		        sq_u.id_sys_user
		        FROM 
		        jos_exj_sys_users sq_u INNER JOIN jos_exj_sys_user_empresas sq_o ON sq_u.id_sys_user = sq_o.id_sys_user
		        WHERE 
		         sq_o.id_empresa = $id_ofc_current
		        GROUP BY 
		        sq_u.id_sys_user  
		  ) su_assigned ON su_all.id_sys_user = su_assigned.id_sys_user 
		WHERE 
		  su_assigned.id_sys_user IS NULL";
        $dbQuery->addTableJoin("($sqUsersExeptOfcCurrent) AS sq_usr_ofc ON sys_user.id_sys_user = sq_usr_ofc.id_sys_user", 'INNER JOIN');
        
		// $exj->includeModelCriteria('rol_users', 'exj_rol_users');
		$criteriaRolUsers = new AppRolUsersCriteriaModel(false);
		
		$gidParam = 0;
		
		// redefine la definición de criteria
		$criteriaRolUsers->registerFieldInt('gid', 'Rol', false, true, true, 'j_user.gid');
		
		if ($criteriaRolUsers->bind($paramsCriteria)) {
			// NOTA: SE PRESENTAN TODOS POR COMPORTAMIENTO DE LA VESION 1.0
			$gidParam = $criteriaRolUsers->getValueField('gid', 0);
			$criteriaRolUsers->resetField('gid');
			
			$criteriaRolUsers->addConditionsQuery($dbQuery);
		}
		
		if (!$criteriaRolUsers->isValid()) {
			$response->setMsgError($criteriaRolUsers->getBrokenRules());
			$items = array();
        	$total =0;
        	return true;
		}
		
       $dbQuery->setOrdersFirst("IF(j_user.gid = $gidParam, 0, j_user.gid)");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("sys_user.id_sys_user");
		$items = $dbQuery->getRows();
		// $dbQuery->writeQueryExecuted();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		if (count($items) == 0) {
			$total = 0;
			return true;
		}
		
		foreach ($items as &$item) {
			$item->is_user_inactive = ($item->is_user_active ? 0:1);
			$item->is_user_delete = 1;
		}
		
		self::RenderListRolUsers($items);
		self::AddPropOrdToItems($items);
		
   //    $dbQuery->writeQueryExecuted();
    
        return true;
	}

	/**
	 * Carga información del usuario sobre la empresa pasada por parámetro
	 *
	 * @param object $dataInfo
	 * @param int $id_user
	 * @param int $id_empresa
	 * @return bool false si ocurrio un error sino true
	 */
	static function LoadInfoUserOffice(&$dataInfo, $id_user, $id_empresa=null){
		$db = Exj::InstanceDatabase();
		
		if (!$id_empresa) {
			$id_empresa = ExjUser::GetIdEmpresa();
		}
		
		$query = "SELECT 
		  sys_usr.id_sys_user, usr_ofc.id_empresa AS id_ofc_assigned, sys_usr.id_empresa AS id_ofc_usr_current
		FROM
		  jos_exj_sys_users sys_usr LEFT JOIN
		  (SELECT x.id_sys_user, x.id_empresa  FROM jos_exj_sys_user_empresas AS x 
		  WHERE x.id_empresa = $id_empresa) usr_ofc ON sys_usr.id_sys_user = usr_ofc.id_sys_user
		WHERE
		  sys_usr.id_user = $id_user";
		
		$db->setQuery($query);
		$db->loadObject($dataInfo);
		
		if (!$db->isValid()) {
			$dataInfo = null;
			return false;
		}
		
		return true;
	}
	
	static function RenderListRolUsers(&$items){
		// $offset_timeCOU = Exj::GetOffsetTimeFromCountry();
		// echo "<br/>offset_time PAIS: $offset_timeCOU";
		
	//	print_r($items[0]);
		
		$ids_sys_users = array();
		foreach ($items as $itemTmp) {
			$ids_sys_users[] = $itemTmp->id_sys_user;
		}
		
		// cargar las ofcinas que tiene relaciondas
		$itemsOfficesRelateds = array();
		if (!self::LoadOfficesRelatedUser($itemsOfficesRelateds, $ids_sys_users)) {
			$itemsOfficesRelateds = null;
		}
		
		foreach ($items as &$item) {
			// $item->tiempoSesion = '';
			/*
			if ($item->time_session) {
				$item->tiempoSesion = date("Y-m-d H:i:s", $item->time_session);
			}
			*/
			$item->itemsOfcsRel = null;
			$item->valueFirstOfcsRel = null;
			if ($itemsOfficesRelateds && count($itemsOfficesRelateds) > 0) {
				foreach ($itemsOfficesRelateds as $itemOfficesRelated) {
					if ($itemOfficesRelated->id_sys_user == $item->id_sys_user) {
						if (!$item->itemsOfcsRel) {
							$item->itemsOfcsRel = array();
						}
						if (!$item->valueFirstOfcsRel) {
							$item->valueFirstOfcsRel = $itemOfficesRelated->id_empresa;
						}
						
						$itemOfcsRel = new stdClass();
						$itemOfcsRel->value = $itemOfficesRelated->id_empresa;
						$itemOfcsRel->text = $itemOfficesRelated->nom_empresa;
						$itemOfcsRel->cod_empresa = $itemOfficesRelated->cod_empresa;
						
						$item->itemsOfcsRel[] = $itemOfcsRel;
					}
				}
			}
			
			if ($item->date_lastvisit) {
				// echo "<br/>date_lastvisit: $item->date_lastvisit";
				
			//	$offset_time = $offset_timeCOU;
				$offset_time = 0;
			//	$item->offset_time_sit = ($item->offset_time_sit / 1000) * 3600;
	//			$offset_time += $item->offset_time_sit;
//				echo "<br/>OFC: $item->nom_empresa item->offset_time_sit: $item->offset_time_sit offset_time FINAL: $offset_time";
				
				$item->date_lastvisit  = Exj::RenderDateTimeOffset($item->date_lastvisit, $offset_time);
			//	echo " -> $item->date_lastvisit";
			}
		}
		
		// print_r($items);
	}

	/**
	 * Lista de Usuarios
	 *
	 * @param bool $onlyUsersNoAssigned true solo los asginados como usuarios del sistema
	 * @param int $blocked 0 incidica que se mostrarán solo los No bloqueados, null todos
	 * @return array
	 */
	static function getLookupUsuarios($onlyUsersNoAssigned = true, $blocked = 0){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $id_company = ExjUser::GetIdCompania();
        
        $whereSQL = array();
        if ($onlyUsersNoAssigned) {
        	$whereSQL[] = "sq_syu.id_sys_user Is Null";
        }
        if ($blocked !== null) {
        	$blocked = ($blocked ? 1:0);
			$whereSQL[] = "usr.block = $blocked";
        }
        
        if (count($whereSQL) > 0) {
        	$whereSQL = implode(" AND ", $whereSQL);
        }
        else {
        	$whereSQL = '';
        }
        
        if ($whereSQL){
        	$whereSQL = " WHERE $whereSQL ";
        }
        
        
        $sql = "SELECT
  usr.id AS value, usr.username AS text, usr.name AS name_usr,
  usr.block, usr.lastvisitDate AS date_lastvisit, usr.usertype, sq_syu.id_sys_user, 
  IF(id_sys_user Is Null, 'green', 'blue') AS color, 
  IF(id_sys_user Is Null, 1, 0) AS is_user_free
 FROM
  jos_users usr LEFT JOIN
  (  
 SELECT
  syu.id_sys_user, syu.id_user
 FROM
  jos_exj_sys_users syu INNER JOIN 
  app_loc_empresas ofi ON syu.id_empresa = ofi.id_empresa
 WHERE
  ofi.id_company = $id_company
 ORDER BY
  syu.modificado_dt DESC
  ) AS sq_syu ON usr.id = sq_syu.id_user
  $whereSQL
 ORDER BY sq_syu.id_sys_user";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
     //   echo '<br/>query: '.$db->getQuery();
        
        return $items;
	}
	
	
	
	/**
	 * Lista de Usuarios de Joomla
	 *
	 * @param array $items
	 * @param int $total
	 * @param bool $onlyActives
	 * @param bool $exceptSuperAdmin
	 * @return bool true si fué satisfactorio
	 */
	static function loadLookupJUsers(&$items, &$total, $onlyActives=true, $exceptSuperAdmin=true){
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
	
	/**
	 * Obtiene información de un usuario dado el email, sino se encuentra retorna null
	 *
	 * @param string $user_email
	 * @param ExjResponse $response
	 * @return mixed Si lo encuentra retorna object, sino se encuentra null, si ocurre algun error false
	 */
	static function GetInfoUsuarioJoomlaFromEmail($user_email, ExjResponse &$response){
		$db = Exj::InstanceDatabase();
		
		if (!$user_email || !trim($user_email)) {
			$response->setMsgError("No se pudo obtener informacion de usuario, no se indicó email!");
			return false;
		}
		
		$query = "SELECT 
		  u.id, u.name, u.username, u.email, u.block, u.usertype 
		FROM 
		  jos_users u 
		WHERE 
		  u.email LIKE '$user_email' 
		LIMIT 1";
		
		$infoUser = null;
		$db->setQuery($query);
		$db->loadObject($infoUser);
		if (!$db->isValid()) {
			$response->setMsgError($db->getErrorMsg());
			return false;
		}

		return $infoUser;
	}
	
	/**
	 * Obtiene información de un usuario dado el email, sino se encuentra retorna null
	 *
	 * @param string $username
	 * @return mixed Si lo encuentra retorna object, sino se encuentra null, si ocurre algun error false
	 */
	public static function GetInfoUsuarioJoomlaFromUserName($username){
		$db = Exj::InstanceDatabase();
		
		if (!$username || !trim($username)) {
			global $exj;
			Exj::SetErrorValidating("No se pudo obtener informacion de usuario, no se indicó username!");
			return false;
		}
		
		$query = "SELECT 
		  u.id, u.name, u.username, u.email, u.block, u.usertype, u.gid 
		FROM 
		  jos_users u 
		WHERE 
		  u.username LIKE '$username'";
		$query .= ' LIMIT 1';
		
		$infoUser = null;
		$db->setQuery($query);
		$db->loadObject($infoUser);
		if (!$db->isValid()) {
			return false;
		}

		return $infoUser;
	}
	
	public static function CreateUserJoomlaTmp($username, $name, $gid, $email, $block=0){
		if (!$email) {
			$email = '';
		}
		
		$usertype = '';
		switch ($gid) {
			case Exj::GetValueCfg('ugidCliente'):
				$usertype = 'CLIENTE';
			break;
			case Exj::GetValueCfg('ugidAdministrador'):
				$usertype = 'Administrator';
			break;
		}
		
		$registerDate = Exj::GetDateTime();
		$activation = '';
		$params = "";
		// $password = '';
		// clave temporal: prueba1269
//		$password = '0e398bec7a07d2edfe6b4bc78b4d2646:njQRunJp8ztSn684VeBi8GfhLUmHwNaq';
		
		// el password en el mismo que username
		/*
		if (!class_exists('JUserHelper')) {
			jimport('joomla.user.helper');
		}
		*/
		
		$salt		= JUserHelper::genRandomPassword(32);
		$crypt		= JUserHelper::getCryptedPassword($username, $salt);
		$password	= $crypt.':'.$salt;
		
		
		$query = "INSERT INTO jos_users(name,username,email,password,usertype,block,gid,registerDate,activation,params)";
		$query .= " VALUES ('$name','$username','$email', '$password', '$usertype', $block, $gid, '$registerDate','$activation','$params')";
		
		$db = Exj::InstanceDatabase();
		
		$db->query($query);
		
		if (!$db->isValid()) {
			return false;
		}
		
		$id = $db->insertid();
		return $id;
	}
	
	/**
	 * Carga las empresas relaciondas al usuario del sistema
	 *
	 * @param array $items
	 * @param mixed $id_sys_user Pueder ser un array de ids o un valor int
	 * @return bool true si la consulta se ejecutó con éxito, sino false
	 */
	static function LoadOfficesRelatedUser(&$items, $id_sys_user){
		$db = Exj::InstanceDatabase();
		
		if (!$id_sys_user || ($id_sys_user && is_array($id_sys_user) && count($id_sys_user) == 0)) {
			$items = array();
			return true;
		}
		
		if (is_array($id_sys_user) && count($id_sys_user) == 1) {
			$id_sys_user = $id_sys_user[0];
		}
		
		if (is_array($id_sys_user)) {
			$conditionWhere = "su_ofc.id_sys_user IN (" . implode(',', $id_sys_user).")";
		}
		else {
			$conditionWhere = "su_ofc.id_sys_user = $id_sys_user";
		}
		
		$query = "SELECT 
		  su_ofc.id_sys_user, su_ofc.id_empresa, ofc.nom_empresa, ofc.cod_empresa 
		FROM 
		  jos_exj_sys_user_empresas su_ofc INNER JOIN app_loc_empresas ofc ON su_ofc.id_empresa = ofc.id_empresa
		WHERE ($conditionWhere)";
		$query .= " ORDER BY ofc.is_main DESC, ofc.nom_empresa";
		
		$items = $db->loadObjectList($query);
		if (!$db->isValid()) {
			return false;
		}

		return true;
	}
	
	/**
	 * Obtiene información del usuario del sistema
	 *
	 * @param int $id_user ID del usuario de Joomla
	 * @return mixed Si ocurre un error retorna false, si existe información retorna un object, si no existe null
	 */
	public static function GetInfoUsuarioSystemFromUserJoomla($id_user){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
		  su.id_sys_user, ofc.nom_empresa, juser.username AS user_related,
		  juser.email, su.modificado_dt, user_reg.username AS user_change
		FROM 
		  jos_exj_sys_users su LEFT JOIN 
		  app_loc_empresas ofc ON su.id_empresa = ofc.id_empresa INNER JOIN 
		  jos_users juser ON su.id_user = juser.id LEFT JOIN 
		  jos_users user_reg ON su.id_usuario_modifico = user_reg.id 
		WHERE 
		  su.id_user = $id_user";
		
		$db->setQuery($query);
		$infoUser = null;
		$db->loadObject($infoUser);
		if (!$db->isValid()) {
			return false;
		}

		return $infoUser;
	}
	
	public static function GetID_ACL_ARO($idUserJoomla, &$msgError){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
		  aa.id
		FROM 
		  jos_core_acl_aro aa
		WHERE 
		  aa.value = $idUserJoomla";
		
		$msgError = '';
		$id = $db->loadResult($query);
		if (!$db->isValid()) {
			$msgError = $db->getErrorMsg();
			return false;
		}

		return $id;
	}
	
	public static function Import_ACL_ARO($idUserJoomla, $name, &$msgError, $section_value='users'){
		$idACL_ARO = self::GetID_ACL_ARO($idUserJoomla, $msgError);
		if ($msgError){
			return false;
		}
		
		if ($idACL_ARO) {
			return $idACL_ARO;
		}
		
		$db = Exj::InstanceDatabase();
		
		$name = trim($name);
		
		$query = "INSERT INTO jos_core_acl_aro(section_value,value,name,hidden)";
		$query .= " VALUES('$section_value', '$idUserJoomla', '$name', 0)";
		
		$db->query($query);
		if (!$db->isValid()) {
			return false;
		}
		
		$idACL_ARO = $db->insertid();
		return $idACL_ARO;
	}
	
	public static function Import_GROUPS_ARO_MAP($idUserJ_ACL_ARO, $gid, &$msgError){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
		  Count(gam.aro_id) AS nro
		FROM 
		  jos_core_acl_groups_aro_map gam
		WHERE 
		  gam.group_id = $gid AND gam.aro_id = $idUserJ_ACL_ARO";
		
		$nro = $db->loadResult($query);
		if (!$db->isValid()) {
			$msgError = $db->getErrorMsg();
			return false;
		}
		
		if ($nro > 0) {
			return $nro;
		}
		
		// NO SE ENCUENTRA, ADD
		
		$query = "INSERT INTO jos_core_acl_groups_aro_map(group_id,section_value,aro_id)";
		$query .= " VALUES($gid, '', '$idUserJ_ACL_ARO')";
		
		$db->query($query);
		if (!$db->isValid()) {
			$msgError = $db->getErrorMsg();
			return false;
		}
		
		return true;
	}
}

?>