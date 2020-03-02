<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppHldIncidentsData
 *
 */
class AppHldIncidentsData extends ExjData {
	const ESTADO_NUEVO = 1;
	const ESTADO_ASIGNADO = 2;
	const ESTADO_TRABAJO_PROG = 3;
	const ESTADO_PENDIENTE= 4;
	const ESTADO_RESUELTO= 5;
	const ESTADO_CERRADO= 6;
	
	
	/**
	 * Lista de HldIncident
	 *
	 * @return array de object
	 */
	public static function LoadListMain(&$items, &$total, $paramsCriteria){
        global $exj;
        $id_empresa = ExjUser::GetIdEmpresa();
        
        $idUserCurrent = ExjUser::GetId();
        
        $dbQuery = new ExjDBQuery();
        
        // name_usr_asign
        
        $dbQuery->setFields("inc.id_hld_incident, inc.title_incident, inc.start_incident,
  inc.end_incident, usr_cng.username AS name_usr_chg,
  inc.id_helpdesk, inc.id_hld_catalog_state,
  inc.id_sys_user_asignado,
  inc.id_hld_catalog_priority, inc.desc_incident, inc.modificado_dt,
  cat_state.name_hld_catalog AS name_state, cat_pri.name_hld_catalog
  AS name_pri, cat_state.color_hld_catalog AS color_state,
  cat_pri.color_hld_catalog AS color_pri, usr_cre.username AS name_usr_cre, 
  cat_hld.name_hld_catalog AS name_hld,
  cat_hld.color_hld_catalog AS color_hld, usr_cng.usertype AS typ_usr_chg, 
  usr_cre.usertype AS typ_usr_cre, inc.id_user_created,
  CONCAT_WS(' - ', cmp.siglas_com, mun.cod_empresa) AS comp_mun,
  ju_asig.username AS name_usr_asign");
        
        $dbQuery->setTables("jos_exj_helpdesk_incidents inc 
        INNER JOIN jos_exj_helpdesk_catalogs cat_state ON inc.id_hld_catalog_state = cat_state.id_hld_catalog 
		INNER JOIN jos_exj_helpdesk_catalogs cat_pri ON inc.id_hld_catalog_priority = cat_pri.id_hld_catalog 
		INNER JOIN jos_users usr_cng ON inc.id_usuario_modifico = usr_cng.id 
		INNER JOIN jos_users usr_cre ON inc.id_user_created = usr_cre.id 
		INNER JOIN jos_exj_helpdesks hld ON inc.id_helpdesk = hld.id_helpdesk
		INNER JOIN jos_exj_helpdesk_catalogs cat_hld ON hld.id_hld_catalog_hld = cat_hld.id_hld_catalog 
		INNER JOIN app_loc_empresas mun ON inc.id_empresa = mun.id_empresa
		INNER JOIN jos_exj_companies cmp ON mun.id_company = cmp.id_company 
	    LEFT JOIN jos_exj_sys_users susr ON inc.id_sys_user_asignado = susr.id_sys_user 
	    LEFT JOIN jos_users ju_asig ON susr.id_user = ju_asig.id");
        
		// Exj::IncludeClass('AppHldIncidentsCriteriaModel', 'exj_hld_incidents');
		$criteriaIncidentes = new AppHldIncidentsCriteriaModel(false);
		
		if ($criteriaIncidentes->bind($paramsCriteria)) {
			$criteriaIncidentes->addConditionsQuery($dbQuery);
		}
		
		if (ExjUser::IsRolAdministrador()) {
			$id_company = ExjUser::GetIdCompania();
			$dbQuery->addConditions("mun.id_company = $id_company");
		}
		elseif (!ExjUser::IsRolSuperAdmin()){
			$dbQuery->addConditions("inc.id_empresa = $id_empresa");
		}
		
		
		$isSuperOrAdmin = ExjUser::IsRolSuperOAdmin();
		if (!$isSuperOrAdmin) {
			$dbQuery->addConditions("inc.id_user_created = $idUserCurrent");
		}
		
		if (!$criteriaIncidentes->isValid()) {
			$exj->setErrorValidating($criteriaIncidentes->getBrokenRules());
			return false;
		}
        
//        $dbQuery->setOrdersFirst("inc.title_incident");
        $dbQuery->addOrders("inc.start_incident");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("inc.id_hld_incident");
	
		$items = $dbQuery->getRows();
		
       // $dbQuery->writeQueryExecuted();
       
       $isSuperAdmin = ExjUser::IsRolSuperAdmin();
       foreach ($items as &$item) {
       		$item->canDel = 0;
       		if ($isSuperAdmin) {
       			$item->canDel = 1;
       		}
       		elseif ($item->id_user_created == $idUserCurrent) {
       			$item->canDel = 1;
       		}
       }
       
      	ExjText::_ArrayObjects($items, 'name_state,name_pri,name_hld');
        
        return $dbQuery->isValid();
	}
	
	/**
	 * Carga una lista de Documentos del incidente
	 *
	 * @param array $items
	 * @param int $total
	 * @param int $id_hld_incident
	 */
	static function LoadListDocs(&$items, &$total, $id_hld_incident){
        // $id_empresa = ExjUser::GetIdEmpresa();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->autoAddLastChange('doc');
        
        $dbQuery->setFields("doc.id_hld_inc_doc, doc.id_hld_incident, doc.tipo_doc,
  doc.id_file, doc.valor_doc, doc.titulo_doc, doc.desc_doc,
  f.name_file, f.size_file,
  inc.id_user_created");
        
        $dbQuery->setTables("jos_exj_helpdesk_incs_docs doc 
        LEFT JOIN jos_app_files f ON doc.id_file = f.id_file 
        INNER JOIN jos_exj_helpdesk_incidents inc ON doc.id_hld_incident = inc.id_hld_incident");
		
		$dbQuery->addConditions("doc.id_hld_incident = $id_hld_incident");
		
//        $dbQuery->setOrdersFirst("doc.xxxx");
    //    $dbQuery->addOrders("doc.modificado_dt");
        
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("doc.id_hld_inc_doc");
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		if (count($items) == 0) {
			return true;
		}
		
   //     $dbQuery->writeQueryExecuted();
        
       $isSuperAdmin = ExjUser::IsRolSuperAdmin();
       $idUserCurrent = ExjUser::GetId();
       
       foreach ($items as &$item) {
       		$item->canDel = 0;
       		if ($isSuperAdmin) {
       			$item->canDel = 1;
       		}
       		elseif ($item->id_user_created == $idUserCurrent) {
       			$item->canDel = 1;
       		}
       }
       
        return true;
	}

	/**
	 * Carga una lista de Respuestas de un incidente
	 *
	 * @param array $items
	 * @param int $total
	 * @param object $paramsCriteria
	 */
	static function LoadListRespuestas(&$items, &$total, $paramsCriteria){
        global $exj;
        $id_empresa = ExjUser::GetIdEmpresa();
     //   $idUserCurrent = ExjUser::GetId();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->autoAddLastChange('res');
        
        $dbQuery->setFields("res.id_hld_inc_res, res.id_hld_incident, usr.name AS name_usr,
  res.modificado_dt, res.id_hld_catalog_state, res.response_inc_res,
  res.id_hld_catalog_response, cat_stt.name_hld_catalog AS name_state,
  cat_stt.color_hld_catalog AS color_state, inc.id_user_created");
        
        $dbQuery->setTables("jos_exj_helpdesk_incs_responses res 
        INNER JOIN jos_users usr ON res.id_usuario_modifico = usr.id 
        INNER JOIN jos_exj_helpdesk_catalogs cat_stt ON res.id_hld_catalog_state = cat_stt.id_hld_catalog 
        INNER JOIN jos_exj_helpdesk_incidents inc ON res.id_hld_incident = inc.id_hld_incident");
        
		// Exj::IncludeClass('AppHldIncResponsesCriteriaModel', 'exj_hld_incidents');
		$criteriaIncidentes = new AppHldIncResponsesCriteriaModel(false);
		
		if ($criteriaIncidentes->bind($paramsCriteria)) {
			$criteriaIncidentes->addConditionsQuery($dbQuery);
		}
		
	//	$dbQuery->addConditions("inc.id_empresa = $id_empresa");
		
		if (!$criteriaIncidentes->isValid()) {
			$exj->setErrorValidating($criteriaIncidentes->getBrokenRules());
			return false;
		}
        
//        $dbQuery->setOrdersFirst("res.xxxx");
        $dbQuery->addOrders("res.modificado_dt");
        
        
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("res.id_hld_inc_res");
		$items = $dbQuery->getRows();
		
   //     $dbQuery->writeQueryExecuted();
        
       $isSuperAdmin = ExjUser::IsRolSuperAdmin();
       $idUserCurrent = ExjUser::GetId();
       
       foreach ($items as &$item) {
       		$item->canDel = 0;
       		if ($isSuperAdmin) {
       			$item->canDel = 1;
       		}
       		elseif ($item->id_user_created == $idUserCurrent) {
       			$item->canDel = 1;
       		}
       }
       
       ExjText::_ArrayObjects($items, 'name_state');
       
        return $dbQuery->isValid();
	}
	
	static function LoadLookupUsrAsignar(&$items, &$total){
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("susr.id_sys_user AS value, CONCAT_WS(' - ', uj.username, uj.name, emp.cod_empresa) as text,
		uj.id, uj.username, uj.email, uj.usertype, uj.name,
		uj.gid, susr.id_persona, susr.id_empresa,
		emp.cod_empresa");
        
        $dbQuery->setTables("jos_users uj 
        INNER JOIN jos_exj_sys_users susr ON uj.id = susr.id_user 
        INNER JOIN 
  (SELECT ue.id_sys_user, Count(ue.id_empresa) AS nro_emps
    FROM jos_exj_sys_user_empresas ue
    GROUP BY ue.id_sys_user) sq_ue ON susr.id_sys_user = sq_ue.id_sys_user 
  INNER JOIN app_loc_empresas emp ON susr.id_empresa = emp.id_empresa");
        
        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery->addConditions("uj.block = 0");
        $dbQuery->addConditions("emp.id_company = $id_company");
        $dbQuery->addConditions("uj.gid NOT IN (38,47)");
        
        $dbQuery->addOrders("emp.cod_empresa, uj.gid, uj.name");
        $dbQuery->withOutPaging();
        
//        $dbQuery->setQueryField(array('uj.name', 'uj.username'));
        $dbQuery->setQueryField('uj.name');
        $dbQuery->setQueryLike(ExjDBQuery::QUERY_LIKE_ALL);
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("susr.id_sys_user");
		$items = $dbQuery->getRows();
		
   //    $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();		
	}
	
}

?>