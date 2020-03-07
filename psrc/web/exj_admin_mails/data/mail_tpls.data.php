<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppMailTplsData
 *
 */
class AppMailTplsData extends ExjData {
	
	/**
	 * Lista de Plantillas
	 *
	 * @return array de object
	 */
	static function loadListPlantillas(&$items, &$total, $paramsCriteria=null){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("tpl.id_mail_tpl, tpl.title_tpl, tpl.cnt_tpl,
  tpl.is_default_tpl, tpl.type_tpl, tpl.is_published,
  tpl.subject_default, tpl.modificado_dt, usr.name AS name_usr");
        
        $dbQuery->setTables("jos_app_mail_tpls tpl INNER JOIN
  jos_users usr ON tpl.id_usuario_modifico = usr.id");
        
        if ($paramsCriteria) {
			// $exj->includeModelCriteria('tpls');
			$criteriaTpls = new AppMailTplsCriteriaModel(false);
			if ($criteriaTpls->bind($paramsCriteria)) {
				$criteriaTpls->addConditionsQuery($dbQuery);
			}
        }
        
        $dbQuery->addConditions("tpl.id_company = $id_company");
        
        // $dbQuery->setOrdersFirst("m.is_html DESC");
        $dbQuery->addOrders("tpl.modificado_dt");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("tpl.id_mail_tpl");
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		
       // $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();
	}

	static function getLookupPlantillas($type_tpl=''){
        global $exj;
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("tpl.id_mail_tpl AS value, tpl.title_tpl AS text, tpl.type_tpl,
  tpl.subject_default");
        
        $dbQuery->setTables("jos_app_mail_tpls tpl");
        
        $dbQuery->addConditions("tpl.id_company = ". ExjUser::GetIdCompania());
        $dbQuery->addConditions("tpl.is_published = 1");
        
        if ($type_tpl) {
        	$dbQuery->addConditions("tpl.type_tpl = '$type_tpl'");
        }
        
        
        $dbQuery->addOrders("tpl.type_tpl");
        $dbQuery->addOrders("tpl.title_tpl");
        
  		/* -------LOAD PARAMS--------------------- */
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		if (!$dbQuery->isValid()) {
			return array();
		}
		
       // $dbQuery->writeQueryExecuted();
        
        return $items;
	}	
	
	static function getInfoPlantilla($id_mail_tpl){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  tpl.id_mail_tpl, tpl.title_tpl, tpl.cnt_tpl,
  tpl.is_default_tpl, tpl.type_tpl, tpl.is_published,
  tpl.subject_default, tpl.modificado_dt
FROM
  jos_app_mail_tpls tpl
WHERE
  tpl.id_mail_tpl = $id_mail_tpl";
        
        $db->setQuery($sql);
        $info = null;
        $db->loadObject($info);
        if (!$info) {
        	Exj::SetErrorValidating("No se obtuvo la plantilla para correo!");
        }
		
        return $info;
	}
	
	
}

?>