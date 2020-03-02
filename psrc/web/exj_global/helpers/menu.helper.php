<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Menú de la aplicación
 *
 */
class ExjHelperMenu extends ExjClassSession {
	public $idMenu= 0;
	public $moduleNameAccess='';
	public $isAccessModule= false;
	public $isNew= false; 
	public $isSave = false;
	public $isView = false;
	public $isTrash = false;
	public $isReports = true;
	public $numCatHlp = -1;

    private function _reset(){
    	$this->idMenu = 0;
    	$this->moduleNameAccess = '';
    	$this->isAccessModule = false;
    	$this->isNew = false;
    	$this->isSave = false;
    	$this->isView = false;
    	$this->isTrash = false;
    	$this->isReports = true;
    	$this->numCatHlp = -1;
    	
    	$this->writeLog(__METHOD__);
    }
    
    public function fixFullAccess(){
    	$this->isAccessModule = true;
    	$this->isNew = true;
    	$this->isSave = true;
    	$this->isTrash = true;
    	$this->isReports = true;
    }
    
    public function isAccessHelp(){
    	if (!$this->numCatHlp) {
    		return false;
    	}
    	return ($this->numCatHlp > 0);
    }
    
    public function fixAccessOnlyNewTrash(){
    	$this->isAccessModule = true;
    	$this->isNew = true;
    	$this->isTrash = true;
    	$this->isReports = false;
        return $this;
    }
    
    public function fixAccessOnlyTrash(){
    	$this->isAccessModule = true;
    	$this->isNew = false;
    	$this->isTrash = true;
    	$this->isReports = false;
        return $this;
    }

    public function fixAccessOnlyEdit(){
    	$this->isAccessModule = true;
    	$this->isNew = false;
    	$this->isTrash = false;
    	$this->isReports = false;
    	
    	$this->isSave = true;
        return $this;
    }
    
    public function fixAccessReadOnly(){
    	$this->isAccessModule = true;
    	$this->isNew = false;
    	$this->isTrash = false;
    	$this->isReports = false;
        return $this;
    }

	
    public function clearMenuData(){
    	$this->writeLog(__METHOD__);
    	$this->_reset();
    	$this->autoResetToSession();
        return $this;
    }
    
    public function addMenuData($dataMenu){
    	if (!$dataMenu) {
    		return false;
    	}
    	if (is_array($dataMenu) && count($dataMenu) == 0) {
    		return false;
    	}

		$this->writeLog(__METHOD__);
    	
    	return $this->addToSession($dataMenu);
    }
    
    public function getMenuData(){
		$this->writeLog(__METHOD__);
		
    	return $this->getFromSession();
    }
    
    /**
     * Carga los accesos al menú de la aplicación
     *
     * @param int $idMenu
     * @return bool true si se han cargado los accesos al menú
     */
    public function loadMenuAccess($idMenu){
		$this->writeLog(__METHOD__, "idMenu: $idMenu");
		
    	$this->_reset();
    	
    	$menu = $this->getMenu($idMenu);
    	if (!$menu) {
    		$this->writeLog("No existe menu con idMenu: $idMenu");
    		return false;
    	}
    	
    	$access = $menu->access;
    	$this->copyObjToThis($access);
    	$this->moduleNameAccess = $access->moduleName;
    	$this->idMenu = $idMenu;
    	
    	if (isset($menu->numCatHlp)) {
	    	$this->numCatHlp = $menu->numCatHlp;
    	}
    	
		$this->writeLog(__METHOD__, "OK");

    	return true;
    }
    
    
    public function getMenu($id){
		$this->writeLog(__METHOD__, "id: $id");
		
    	$dataMenus = $this->getMenuData();
    	if (!$dataMenus) {
			$this->writeLog(__METHOD__, "No se obtubo menuData");
    		return null;
    	}
    	
    	$id = intval($id);
    	$menu = null;
    	
    	foreach ($dataMenus as $itemsMenus) {
    		$menu = $this->_getMenu($id, $itemsMenus);
    		if ($menu) {
    			break;
    		}
    	}

		$this->writeLog(__METHOD__, "OK");
    	
    	return $menu;
    }
    
    private function _getMenu($id, $itemsMenus){
    	$menu = null;
    	if (!$itemsMenus) {
			$this->writeLog(__METHOD__, "No existen itemsMenus con ID: $id");
    		return $menu;
    	}
    	
		foreach ($itemsMenus as $itemMenu) {
			if (is_string($itemMenu)) {
				continue;
			}
			/* print_r($itemMenu); */
			if (!isset($itemMenu->id)) {
//				echo "<br/>No está definido el item menu, el item siguiente: ";
	//			print_r($itemMenu);
				continue;
			}
			
			if ($id == $itemMenu->id) {
				$menu = $itemMenu;
				break;
			}
			else {
				if (!isset($itemMenu->children) || !$itemMenu->children) {
					continue;
				}
				
				$menu = $this->_getMenu($id, $itemMenu->children);
				if ($menu) {
					break;
				}
			}
		}

		$this->writeLog(__METHOD__.' OK', "id: $id Menu encontrado: " . ($menu ? 'SI':'NO'));
		
		return $menu;
    }	
}

?>