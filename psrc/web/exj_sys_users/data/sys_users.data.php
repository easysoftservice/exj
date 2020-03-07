<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppSysUsersData
 *
 */
class AppSysUsersData extends ExjData {
	const TEMA_APP = 'APP';
	const TEMA_AZUL = 'AZUL';
	const TEMA_GRIS = 'GRIS';
	const TEMA_PROFESIONAL = 'PROFESIONAL';

	const USER_TYPE_SUPER_ADMIN = 'Super Administrator';
	const USER_TYPE_ADMIN = 'Administrador';
	
	/**
	 * Lista de Usuarios del Sistema
	 *
	 * @return array de object
	 */
	public static function loadListSysUsers(&$items, &$total, $paramsCriteria=null){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("syu.id_sys_user, syu.id_user, syu.id_empresa, syu.id_sys_lang,
  syu.id_persona, syu.enable_debug, syu.sys_type_theme,
  peo.nro_doc_persona, peo.nombres_persona, peo.apellidos_persona,
  usr.name AS name_usr, usr.usertype, usr.username AS
  username_usr, usr.lastvisitDate AS lastvisit_date, usr.block AS
  block_usr, If(usr.block, 'YES', 'NO') AS str_block_usr,
  usr_chg.name AS name_usrchg, syl.acronym_lang, peo.tipo_persona,
  peo.id_doc_tipo, peo.id_pais, peo.id_sit, peo.tlf_persona,
  peo.dir_person, peo.email_person, peo.type_sexo,
  peo.birth_date, ofi.nom_empresa, syu.modificado_dt, 
  ses.time AS time_session, ses.client_id AS client_session, 
  If(ses.time IS NULL, 'NO', 'YES') AS str_is_loggin, 
  If(ses.client_id IS NULL, '', If(ses.client_id = 1, 'Backend', 'Frontend')) AS str_client_ses,
  peo.alias_persona, peo.cell_person, peo.id_loc_zip,
  city.id_sit_parent, city.name_sit, city.cod_sit, sit_ofc.offset_time_sit");
        
        $dbQuery->setTables("jos_exj_sys_users syu 
        INNER JOIN jos_app_personas peo ON syu.id_persona = peo.id_persona 
        INNER JOIN jos_users usr ON syu.id_user = usr.id 
        INNER JOIN jos_users usr_chg ON syu.id_usuario_modifico = usr_chg.id 
        INNER JOIN jos_exj_sys_lang syl ON syl.id_sys_lang = syu.id_sys_lang 
        INNER JOIN app_loc_empresas ofi ON syu.id_empresa = ofi.id_empresa 
        LEFT JOIN jos_session ses ON syu.id_user = ses.userid 
        LEFT JOIN jos_app_loc_sites city ON peo.id_sit = city.id_sit 
        LEFT JOIN jos_app_loc_sites sit_ofc ON ofi.id_sit = sit_ofc.id_sit");
        
        $dbQuery->addConditions("ofi.id_company = $id_company");
        
        if ($paramsCriteria) {
			// $exj->includeModelCriteria('sys_users', 'exj_sys_users');
			$criteriaSysUsers = new AppSysUsersCriteriaModel(false);
			if ($criteriaSysUsers->bind($paramsCriteria)) {
				$criteriaSysUsers->addConditionsQuery($dbQuery);
			}
        }
        
//        $dbQuery->setOrdersFirst("usr.block, ses.time DESC, ses.client_id");
        $dbQuery->addOrders("ses.client_id DESC,nom_empresa,usertype");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("syu.id_sys_user");
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		self::RenderListSysUsers($items);
		
        // $dbQuery->writeQueryExecuted();
    
        return true;
	}
	
	public static function RenderListSysUsers(&$items){
		// $offset_timeCOU = Exj::GetOffsetTimeFromCountry();
		// echo "<br/>offset_time PAIS: $offset_timeCOU";
		
		foreach ($items as &$item) {
			$item->tiempoSesion = '';
			if ($item->time_session) {
				$item->tiempoSesion = date("Y-m-d H:i:s", $item->time_session);
			}
			
			if ($item->lastvisit_date) {
				// echo "<br/>lastvisit_date: $item->lastvisit_date";
				
			//	$offset_time = $offset_timeCOU;
				$offset_time = 0;
			//	$item->offset_time_sit = ($item->offset_time_sit / 1000) * 3600;
				$offset_time += $item->offset_time_sit;
//				echo "<br/>OFC: $item->nom_empresa item->offset_time_sit: $item->offset_time_sit offset_time FINAL: $offset_time";
				
				$item->lastvisit_date  = Exj::RenderDateTimeOffset($item->lastvisit_date, $offset_time);
			//	echo " -> $item->lastvisit_date";
			}
			
			$item->str_sys_type_theme = ExjText::__($item->sys_type_theme);
		}
	}

	static function getInfoPerson($id_persona){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $id_company = ExjUser::GetIdCompania();
        
        $sql = "SELECT
  usr.id AS id_user, usr.name, usr.username, usr.block,
  if(isnull(peo.email_person), usr.email, peo.email_person) AS email,
  usr.usertype, usr.lastvisitDate, syu.id_sys_user,
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
	 * Lista de Usuarios
	 *
	 * @param bool $onlyUsersNoAssigned true solo los asginados como usuarios del sistema
	 * @param int $blocked 0 incidica que se mostrarán solo los No bloqueados, null todos
	 * @return array
	 */
	public static function getLookupUsuarios($onlyUsersNoAssigned = true, $blocked = 0){
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
  usr.block, usr.lastvisitDate AS lastvisit_date, usr.usertype, sq_syu.id_sys_user, 
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
	
	static function GetLookupLenguajes(){
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT 
  syl.id_sys_lang AS value, syl.name_lang AS text, syl.acronym_lang
FROM 
  jos_exj_sys_lang syl 
ORDER BY syl.name_lang";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	global $exj;
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}
	
	public static function getLookupTemas(){
        $items = array();
        
        $items[] = self::newItemLookup(self::TEMA_APP, ExjText::__(self::TEMA_APP));
        $items[] = self::newItemLookup(self::TEMA_AZUL, ExjText::__(self::TEMA_AZUL));
        $items[] = self::newItemLookup(self::TEMA_GRIS, ExjText::__(self::TEMA_GRIS));
        $items[] = self::newItemLookup(
          self::TEMA_PROFESIONAL, ExjText::__(self::TEMA_PROFESIONAL)
        );
        
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
	public static function loadLookupJUsers(&$items, &$total, $onlyActives=true, $exceptSuperAdmin=true){
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

    $dbQuery->setQueryFieldValue('u.id')
      ->setQueryField('u.name')
      ->setQueryLike(ExjDBQuery::QUERY_LIKE_ALL);
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("u.id");
		$items = $dbQuery->getRows();
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Usuarios de GYMCloud
	 *
	 * @param string $usertype
	 * @return array
	 */
	public static function GetLookupSysUsers($usertype = ''){
        $db = Exj::InstanceDatabase();
        $id_empresa = ExjUser::GetIdEmpresa();
        
        $whereSQL = array();
        $whereSQL[] = "syu.id_empresa = $id_empresa";
        
        if ($usertype) {
        	$usertype = trim($usertype);
        	$whereSQL[] = "u.usertype = '$usertype'";
        }
        
        $whereSQL = implode(" AND ", $whereSQL);
        
        $sql = "SELECT 
		  syu.id_sys_user AS value, u.username as text, UPPER(u.name) AS name, u.usertype,
		  u.gid, u.block, u.registerDate,
		  IF(u.block = 1 OR u.activation<>'', 'red','green') AS color 
	    FROM 
		  jos_exj_sys_users syu INNER JOIN jos_users u ON syu.id_user = u.id 
	    WHERE $whereSQL ORDER BY syu.modificado_dt DESC";
        
        $items = $db->loadObjectList($sql);
        
     //   echo '<br/>query: '.$db->getQuery();
        
        return $items;
	}
}

?>