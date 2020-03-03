<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppDeploysData
 *
 */
class AppDeploysData extends ExjData {
	
	/**
	 * Lista de Deploys
	 *
	 * @param array $items
	 * @param int $total
	 * @param object $paramsCriteria No requerido
	 * @return bool false si a ocurrido algún error
	 */
	public static function loadListDeploys(&$items, &$total, $paramsCriteria=null){
        global $exj;
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("dpy.id_deploy, dpy.version_dpy, dpy.path_dpy,
        dpy.file_bkdb,
        dpy.is_copied_preprod,
  dpy.num_filesphp, dpy.num_filesjs, dpy.num_filescss,
  dpy.num_filesimg, 
  dpy.num_filesotros, dpy.num_filesjs_encoded, dpy.size_filesjs_encoded,
  dpy.url_dpy, dpy.obs_dpy, dpy.modificado_dt,
  usr.name AS name_usr");
        
        $dbQuery->setTables("jos_exj_deploys dpy INNER JOIN 
  jos_users usr ON dpy.id_usuario_modifico = usr.id");
        
        if ($paramsCriteria) {
			$criteriaDeploys = new AppDeploysCriteriaModel(false);
			
			if ($criteriaDeploys->bind($paramsCriteria)) {
				$criteriaDeploys->addConditionsQuery($dbQuery);
			}
			
			if (!$criteriaDeploys->isValid()) {
				Exj::SetErrorValidating($criteriaDeploys->getBrokenRules());
				return false;
			}
        }
        
        
//        $dbQuery->setOrdersFirst("dpy.version_dpy DESC");
        $dbQuery->addOrders("dpy.modificado_dt");
        
  		/* -------LOAD PARAMS--------------------- */
  		$dbQuery->loadRowsCount($items, $total, "dpy.id_deploy");
		if (!$dbQuery->isValid()) {
			self::setError($dbQuery->getErrorMsg());
			return false;
		}
		
       // $dbQuery->writeQueryExecuted();
       
       foreach ($items as &$item) {
       		$item->size_filesjs_encoded = ExjUtil::RenderSizeBytes($item->size_filesjs_encoded);
       		$item->isCopiedPreProd = ExjUtil::render_SINO($item->is_copied_preprod);
       }
       
       return $dbQuery->isValid();
	}
	
	/**
	 * Lista de Componentes
	 *
	 * @param array $items
	 * @param int $total
	 * @param object $params
	 * @return bool false si a ocurrido algún error
	 */
	public static function loadListComps(&$items, &$total, $params=null){
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("cat.id, grp.name AS name_comp, cat.name AS title_comp,
  cat.published, cat.trash,  
  IF(cat.published = 1, 'SI', 'NO') AS render_published,  
  IF(cat.trash = 1, 'SI', 'NO') AS render_trash");
        
        $dbQuery->setTables("jos_groups grp INNER JOIN 
   jos_k2_categories cat ON cat.access = grp.id");
        
        $dbQuery->addConditions("grp.name <> 'exj_deploys'");
        
        $dbQuery->addOrders("cat.name");
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("cat.id");
	//	$dbQuery->writeQueryExecuted();
		$items = $dbQuery->getRows();
		
       // $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();
	}

	/**
	 * @param string
	 */
	public static function GetItemFromVersion($version=''){
		if (!$version) {
			$version = Exj::GetVersionApp();
		}

		$query = "SELECT * FROM jos_exj_deploys WHERE version_dpy='$version'";
		return ExjDatabase::GetObjectFromQuery($query);
	}
}
?>