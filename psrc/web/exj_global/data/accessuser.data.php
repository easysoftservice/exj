<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * 
 *
 */
class AppGlobalDataAccessUser extends ExjObject {
	private $_listAccess;
    private $_actionsSave = array('save', 'saveAndNew');
    private $_actionsTrash = array('trash');
    private $_actionsNew = array('add', 'saveAndNew');
    private $_actionsView = array('view');
	
    public function __construct() {
    	$this->_listAccess = $this->_getListAccessUsr();
    }
    
    public function isValid(){
    	$db = Exj::InstanceDatabase();
    	if (!$db->isValid()) {
    		return false;
    	}
    	
    	return ($this->_listAccess != null);
    }
    
    public function getActions($idAccess=null, $grp_name=''){
    	if (!$idAccess && !$grp_name) {
    		$grp_name = Exj::GetComponentCurrent();
    	}
    	
    	if ($grp_name) {
    		$grp_name = trim(strtolower($grp_name));
    		if ($grp_name == 'exj_baseui') {
    			$nameComponent = ExjRequest::GetParam('nameComponent', '');
    			if ($nameComponent) {
    				$grp_name = $nameComponent;
    			}
    			else {
    				echo "<br/>ERROR RECUPERANDO COMPONENTE. " . $this->getClassStr();
    			}
    		}
    	}
    	
    	/*
    	echo "<br/>grp_name: $grp_name _listAccess:<br/>";
    	print_r($this->_listAccess);
    	*/
    	
    	$actions = array();
		foreach ($this->_listAccess as $itemAccessUsr) {
			if ($idAccess !== null && ($idAccess == $itemAccessUsr->access)) {
				$actions[] = $itemAccessUsr->axo_value;
				continue;
			}
			
			if ($grp_name && $grp_name == trim(strtolower($itemAccessUsr->grp_name))) {
				$actions[] = $itemAccessUsr->axo_value;
			}
		}
		
		/*
		if (count($actions) == 0) {
			global $exj;
			if (ExjUser::IsRolSuperAdmin()) {
				$actions[] = 'add';
				$actions[] = 'save';
				$actions[] = 'edit';
				$actions[] = 'trash';
				$actions[] = 'saveAndNew';
				$actions[] = 'publish';
				$actions[] = 'unpublish';
				$actions[] = 'view';
			}
		}
		*/

		return $actions;
    }
    
    public function getModuleName($idAccess){
    	$moduleName = '';
		foreach ($this->_listAccess as $itemAccessUsr) {
			if ($idAccess == $itemAccessUsr->access) {
				$moduleName = $itemAccessUsr->grp_name;
				break;
			}
		}
		
		if (strtolower($moduleName) == 'registered') {
		//	echo " <br/>idAccess: $idAccess ModuleName vacio ";
			$moduleName = '';
		}
		
		return $moduleName;
    }

    public function isAccess($actions, $idAccess=null, $grp_name=''){
    	$actionsOk = $this->getActions($idAccess, $grp_name);
    	if (count($actionsOk) == 0) {
    		return false;
    	}
    	if (!$actions) {
    		return false;
    	}
    	if (!is_array($actions)) {
    		$actions = array($actions);
    	}
    	
    	/*
    	echo "<br/>actions:<br/>";
    	print_r($actions);
    	*/
    	
    	$access = false;
    	foreach ($actionsOk as $actionOk) {
//    		echo "<br/>actionOk: $actionOk";
    		if (in_array($actionOk, $actions)) {
    			$access = true;
    			break;
    		}
    	}
    	
    	return $access;
    }
    

    public function isAccessSave($idAccess=null, $grp_name=''){
    	return $this->isAccess($this->_actionsSave, $idAccess, $grp_name);
    }

    public function isAccessTrash($idAccess=null, $grp_name=''){
    	return $this->isAccess($this->_actionsTrash, $idAccess, $grp_name);
    }
    
    public function isAccessNew($idAccess=null, $grp_name=''){
    	return $this->isAccess($this->_actionsNew, $idAccess, $grp_name);
    }

    public function isAccessView($idAccess=null, $grp_name=''){
    	return $this->isAccess($this->_actionsView, $idAccess, $grp_name);
    }
    
    public function getAccess($idAccess){
    	$idAccess = intval($idAccess);
    	
    	$access = new stdClass();
    	
		$access->idAccess = $idAccess;
		$access->moduleName = $this->getModuleName($idAccess);
		
		$access->actions = $this->getActions($idAccess);
		$access->isNew = $this->isAccessNew($idAccess);
		$access->isSave = $this->isAccessSave($idAccess);
		$access->isTrash = $this->isAccessTrash($idAccess);
		$access->isView = $this->isAccessView($idAccess);

		$access->isAccessModule = (count($access->actions) > 0);
		
		
    	return $access;
    }
	
	private function _getListAccessUsr(){
    	global $exj;
        $db = Exj::InstanceDatabase();
    	
    	$gid = Exj::GetUserGID();
    	if (!$gid) {
    		$exj->setErrorDB("Usuario no está logueado");
    		return null;
    	}
    	
    	$query = "SELECT 
  rul.id, rul.aro_value, rul.axo_section, rul.axo_value,
  k2c.name AS mod_name, k2c.parent AS k2c_parent, k2c.access,
  grps.name AS grp_name 
FROM 
  jos_noixacl_rules rul INNER JOIN 
  jos_k2_categories k2c ON rul.axo_section = k2c.id INNER JOIN 
  jos_groups grps ON k2c.access = grps.id INNER JOIN 
  jos_core_acl_aro_groups aro_grp ON rul.aro_value = aro_grp.value 
WHERE 
  rul.aco_section = 'com_k2' AND
  k2c.published = 1 AND aro_grp.id = $gid";
		
    	$list = $db->loadObjectList($query);
    	if (!$db->isValid()) {
    		return null;
    	}
    	
    	// $db->writeLastQuery();
    	
    	return $list;
	}
	
}

?>