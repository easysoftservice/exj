<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * 
 *
 */
class AppGlobalDataMenu extends ExjObject {
	public $id;
	public $parentId;
	public $text;
	public $iconCls;
	public $children;
	public $menu;
	public $leaf;
	public $page_title;
	public $handler;
	public $nameModule;
	public $numCatHlp=0;
	public $access;
	public $tooltip;
	
	public $expanded;
	public $singleClickExpand;
	
    public function __construct($parentId, $text, $iconCls='', $page_title='') {
    	$this->text = $text;
    	$this->iconCls = $iconCls;
    	$this->parentId = $parentId;

    	
    	$this->children = null;
    	$this->leaf = true;
    	
    	$this->menu = null;
    	$this->page_title = $page_title;
    	$this->handler = 'onItemClickMenu';
    	$this->nameModule = '';
    	$this->numCatHlp = 0;
    	$this->access = null;
    	
    	$this->singleClickExpand = true;
    	$this->expanded = false;
    	
    	$this->tooltip = '';
    	if ($this->page_title && ($this->text != $this->page_title)) {
    		$this->tooltip = $this->page_title;
    	}
    }
    
    private function _createMenu(){
    	if ($this->menu) {
    		return;
    	}

		$this->handler = null;
		$this->nameModule = '';
    	
    	$this->menu = new stdClass();
    	$this->menu->items = array();
    }

    public function addChildren($parentId, $text, $iconCls='', $pageTitle='') {
    	if ($this->children == null) {
    		$this->children = array();
    		$this->leaf = false;
    	}
    	
    	$this->_createMenu();
    	
    	$itemMenu = new AppGlobalDataMenu($parentId, $text, $iconCls, $pageTitle);
    	
    	$this->children[] = $itemMenu;
    	$this->menu->items[] = $itemMenu;
    }
    
    public function setChildrens($childrens) {
    	if (!$childrens) {
    		return;
    	}
    	if (count($childrens) == 0) {
    		return;
    	}
    	
    	$this->leaf = false;
    	$this->children = $childrens;
    	
    	$this->_createMenu();
    	$this->menu->items = $childrens;
    }
    
    
    static function getNodeRoot($items){
    	$firstItem = null;
    	if (!$items) {
    		return $firstItem;
    	}
    	
    	foreach ($items as $item) {
    		$firstItem = $item;
    		break;
    	}
    	
    	return $firstItem;
    }
    
    static function getChilds($parentId, $dataMenus){
    	// funcion recursiva, recorre los nodos hijos
    	
    	$childs = array();
    	
    	if (isset($dataMenus[$parentId])) {
    		$dataChilds = $dataMenus[$parentId];
    		foreach ($dataChilds as $dataChild) {
    			if ($dataChild->text == '-') {
    				// es separador
    				$childs[] = '-';
    				continue;
    			}
    			
    			
				$menu = new AppGlobalDataMenu($dataChild->parent, $dataChild->text, $dataChild->iconCls, $dataChild->page_title);
				$menu->id = $dataChild->id;	
				$menu->nameModule = $dataChild->nameModule;
				$menu->access = $dataChild->access;
				$menu->numCatHlp = $dataChild->numCatHlp;
				if (isset($dataChild->idPanelToRenderVU) && $dataChild->idPanelToRenderVU) {
					$menu->idPanelToRenderVU = $dataChild->idPanelToRenderVU;
				}
				
    			$subChilds = self::getChilds($dataChild->id, $dataMenus);
    			$menu->setChildrens($subChilds);
    			$childs[] = $menu->toObject();
    		}
    	}
    	
    	return $childs;
    }
    
    static function parseData($dataMenus){
    	$nodesRoot = AppGlobalDataMenu::getNodeRoot($dataMenus);
    	$itemsMenus = array();
    	if (count($nodesRoot) == 0) {
    		return $itemsMenus;
    	}
    	
		foreach ($nodesRoot as $nodeRoot) {
			$nodeId = $nodeRoot->id;
			$parentId = $nodeRoot->parent;
			
			
			$itemMenu = new AppGlobalDataMenu($nodeId, $nodeRoot->text, $nodeRoot->iconCls, $nodeRoot->page_title);
			$itemMenu->id = $nodeId;
			$itemMenu->nameModule = $nodeRoot->nameModule;
			$itemMenu->access = $nodeRoot->access;
			$itemMenu->numCatHlp = $nodeRoot->nro_cat_hlp;
			$itemMenu->expanded = true;
			if (isset($nodeRoot->idPanelToRenderVU) && $nodeRoot->idPanelToRenderVU) {
				$itemMenu->idPanelToRenderVU = $nodeRoot->idPanelToRenderVU;
			}
			
			$childrens = AppGlobalDataMenu::getChilds($nodeId, $dataMenus);
			$itemMenu->setChildrens($childrens);
			
			$itemsMenus[] = $itemMenu->toObject();
		}
		
		return $itemsMenus;
    }
    
}
	
?>