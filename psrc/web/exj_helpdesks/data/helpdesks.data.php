<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppHelpdesksData
 *
 */
class AppHelpdesksData extends ExjData {
	
	/**
	 * Lista de Helpdesks
	 *
	 * @return array de object
	 */
	static function loadListHelpdesks(&$items, &$total, $paramsCriteria=null){
        global $exj;
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("hld.id_helpdesk, cat_hld.name_hld_catalog AS name_hld,
  hld.is_default_hld, usr.name AS name_usr, hld.modificado_dt,
  hld.id_hld_catalog_hld, cat_hld.color_hld_catalog AS color_hld");
        
        $dbQuery->setTables("jos_exj_helpdesks hld INNER JOIN
  jos_exj_helpdesk_catalogs cat_hld ON hld.id_hld_catalog_hld =
    cat_hld.id_hld_catalog INNER JOIN
  jos_users usr ON hld.id_usuario_modifico = usr.id");
        
        if ($paramsCriteria) {
			// $exj->includeModelCriteria('helpdesks');
			$criteriaHelpdesks = new AppHelpdesksCriteriaModel(false);
			if ($criteriaHelpdesks->bind($paramsCriteria)) {
				$criteriaHelpdesks->addConditionsQuery($dbQuery);
			}
        }
        
        $dbQuery->addConditions("hld.esta_activo_hld = 1");
        $dbQuery->addConditions("cat_hld.is_active = 1");
        
        $dbQuery->addOrders("cat_hld.order_hld_catalog");
        
  		/* -------LOAD PARAMS--------------------- */
        $dbQuery->loadRowsCount($items, $total, "hld.id_helpdesk");		
        // $dbQuery->writeQueryExecuted();
        ExjText::_ArrayObjects($items, 'name_hld');
        
        return $dbQuery->isValid();
	}
	
	static function getLookupHelpdesks(){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  hld.id_helpdesk AS value, cat_hld.name_hld_catalog AS text,
  hld.is_default_hld, hld.id_hld_catalog_hld,
  cat_hld.color_hld_catalog AS color_hld,
  cat_hld.desc_hld_catalog AS description
 FROM 
  jos_exj_helpdesks hld INNER JOIN
  jos_exj_helpdesk_catalogs cat_hld ON hld.id_hld_catalog_hld =
    cat_hld.id_hld_catalog
 WHERE 
  hld.esta_activo_hld = 1 AND cat_hld.is_active = 1 
 ORDER BY 
  cat_hld.order_hld_catalog, hld.is_default_hld DESC";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        // xxx description
        ExjText::_ArrayObjects($items, 'text,description');
        
        return $items;
	}

	static function getLookupEstados($value=null){
		return self::getLookupCatalogo('ESTADO', $value);
	}
	static function getLookupPrioridades($value=null){
		return self::getLookupCatalogo('PRIORIDAD', $value);
	}
	static function getLookupMesasAyuda(){
		return self::getLookupCatalogo('MESA AYUDA');
	}
	static function getLookupRespuestas(){
		return self::getLookupCatalogo('RESPUESTA');
	}
	
	static function getLookupCatalogo($type_hld_catalog, $value=null){
        $db = Exj::InstanceDatabase();
        
        $whereSQL = array();
        $whereSQL[] = "cat.is_active = 1";
        $whereSQL[] = "cat.type_hld_catalog = '$type_hld_catalog'";
        
        if ($value) {
        	$whereSQL[] = "cat.id_hld_catalog = $value";
        }
        
        $whereSQL = implode(" AND ", $whereSQL);
        
        $sql = "SELECT
  cat.id_hld_catalog AS value, cat.name_hld_catalog AS text,
  cat.desc_hld_catalog AS description, cat.sample_hld_catalog AS sample,
  cat.color_hld_catalog AS color, cat.css_hld_catalog AS css
 FROM 
  jos_exj_helpdesk_catalogs cat
 WHERE $whereSQL 
 ORDER BY 
  cat.order_hld_catalog";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	global $exj;
        	
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        
        // xxx sample, description
        ExjText::_ArrayObjects($items, 'text,description');
        
        return $items;
	}
	
	static function loadHelpDesk($id_helpdesk, &$data){
        global $exj;
        $db = Exj::InstanceDatabase();
        
		
		$sql = "SELECT
  hld.id_helpdesk, cat_hld.name_hld_catalog,
  hld.is_default_hld, hld.id_hld_catalog_hld,
  cat_hld.color_hld_catalog AS color_hld, cat_hld.is_active
 FROM 
  jos_exj_helpdesks hld 
  INNER JOIN jos_exj_helpdesk_catalogs cat_hld ON hld.id_hld_catalog_hld = cat_hld.id_hld_catalog 
 WHERE 
  hld.esta_activo_hld = 1 AND hld.id_helpdesk = $id_helpdesk";
		
		$db->setQuery($sql);
		$db->loadObject($data);
		if ($db->haveError()) {
			return false;
		}
		if (!$data) {
			self::setError("No se encontró Mesa de Ayuda con ID: $id_helpdesk");
			return false;
		}
		
		$data->name_hld_catalog = ExjText::_($data->name_hld_catalog);
		
		return true;
	}		
	
}

?>