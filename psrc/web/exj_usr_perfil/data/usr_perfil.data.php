<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppUsrPerfilData
 *
 */
class AppUsrPerfilData extends ExjData {
	
	/**
	 * Lista de XXXX
	 *
	 * @return array de object
	 */
	public static function LoadListMain(&$items, &$total, $paramsCriteria=null)
	{
//        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("tit.id_diploma, tit.name_tit, tit.siglas_tit, tit.is_national,
  tit.modificado_dt, usr.name AS name_usr");
        
        $dbQuery->setTables("jos_app_diplomas tit LEFT JOIN
  jos_users usr ON tit.id_usuario_modifico = usr.id");
        
        if ($paramsCriteria) {
			$criteriaUsrPerfil = new AppUsrPerfilCriteriaModel(false);
			if ($criteriaUsrPerfil->bind($paramsCriteria)) {
				$criteriaUsrPerfil->addConditionsQuery($dbQuery);
			}
        }
        
        // $dbQuery->addConditions("tit.id_company = $id_company");
        
        $dbQuery->setOrdersFirst("tit.is_national DESC");
        $dbQuery->addOrders("tit.name_tit");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("tit.id_diploma");
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		
       // $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();
	}
	
	public static function LoadUserPerfil(&$row, $id_sys_user=0){
		$row = null;
		
		if (!$id_sys_user) {
			$id_sys_user = ExjUser::GetIdSysUser();
		}
		
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT 
		  su.id_sys_user,
		  prs.id_persona, 
		  prs.nro_doc_persona, prs.id_doc_tipo,  
          IF(prs.nombres_persona IS NULL, prs.apellidos_persona, CONCAT_WS(' ', prs.nombres_persona, prs.apellidos_persona)) AS noms_apes,
          prs.dir_person,
		  prs.tlf_persona, uj.name AS user_name,
		  prs.id_sit,
		  uj.username AS user_username, uj.email AS user_email, 
		  uj.sendEmail AS user_sendemail,
		  dt.name_doc,
		  prs.modificado_dt
	    FROM 
		  jos_app_personas prs 
		  INNER JOIN jos_exj_sys_users su ON prs.id_persona = su.id_persona 
		  INNER JOIN jos_users uj ON su.id_user = uj.id 
		  INNER JOIN jos_app_doc_tipos dt ON prs.id_doc_tipo = dt.id_doc_tipo 
	    WHERE su.id_sys_user = $id_sys_user";
		
		$db->setQuery($query);
		$db->loadObject($row);
		
		if (!$db->isValid()) {
			return false;
		}
		
		return true;
	}
	
	public static function LoadRowUserEMailRegistered(&$row, $email, $idUserJoomla=0){
		$db = Exj::InstanceDatabase();
		$row = null;
		
		if (!$idUserJoomla) {
			$idUserJoomla = ExjUser::GetId();
		}
		
		$idUserJoomla = intval($idUserJoomla);
		
		$query = "SELECT 
		  u.id, u.name, u.username
	    FROM 
		  jos_users u
	    WHERE 
		  u.id <> $idUserJoomla AND u.email = '$email'";
		
		$db->setQuery($query);
		$db->loadObject($row);
		if (!$db->isValid()) {
			return false;
		}
		
		return true;
	}
}

?>