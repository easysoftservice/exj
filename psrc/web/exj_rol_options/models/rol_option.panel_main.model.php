<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolOptionPanelMainModel
 * Modelo de Panel Principal para: Opciones del Rol
 */
class AppRolOptionPanelMainModel extends ExjPanelMainModel {
	
	/**
	 * overwrite. Inicio del Modelo Panel Principal
	 *
	 */
	public function panelInit(){
		$this->setNameModelController('rol_options');
		$this->setTitle("Permisos");
		// $this->autoHeight = true;
	//	$this->html = 'hola esto es una prueba';
	//	$this->layout = 'fit';
	//	$this->setReportDownload(true, true, true);
		
		$this->setNameTopic('Permiso');
	//	$this->autoHeight = false;
	//	$this->height = 333;
	//	$this->bodyStyle = "background-color: transparent;";
		$this->setOffsetHeight(-3);
		
		
		$this->forceEnableViewLogPers(false);
	}
	
	/**
	 * Registro de datos para JsonStore
	 *
	 * @param string $storeId Se no se indica se genera uno
	 * @param string $url Defecto vacio
	 * @param string $idProperty Defecto vacio
	 * @param bool $remoteSort Defecto true
	 */
	protected function registerJsonStore(&$storeId, &$url, &$idProperty, &$remoteSort){
		$idProperty = 'id_menu';
		$url = Exj::BuildURLModel('rol_options', 'view', 'exj_rol_options');
		
		$this->registerFieldInt('id_menu');
		$this->registerFieldInt('axo_section');
		$this->registerFieldInt('id_parent_menu');
		$this->registerFieldInt('nroRules');
		
		$this->registerFieldString('text');
		$this->registerFieldString('iconCls');
		$this->registerFieldString('name_comp');
		
		$this->registerFieldBool('singleClickExpand');
		$this->registerFieldBool('expanded');
		$this->registerFieldBool('originalChecked');
		$this->registerFieldBool('checked');
		
		$this->registerFieldRaw('children');
	}
	
	/**
	 * overwrited. Carga los items que se presentarán en la UI
	 *
	 * @param array $itemsUI Pasado por referencia
	 * @param array $items
	 * @param int $total
	 */
	protected function loadItemsUI(&$itemsUI, $items, $total){
		// print_r($items);
		
		$itemsUI[] = $this->_getTreePanelUI($items);
	}
	
	private function _getTreePanelUI($items){
		$treePanel = new ExjUITreePanel();
		$treePanel->name = 'tpSystemModules';
		$treePanel->setLayout('fit')
			->setFrame(false)
			->setBorder(false)
			->setUseArrows()
			->setAutoScroll(false)
			->setBodyCssClass('exj-body-tree')
			->setContainerScroll();

		$treePanel->enableDD = false;
		$treePanel->split = true;
	//	$treePanel->height = 333;
		$treePanel->setRootVisible();

		$treePanel->root = new stdClass();
		$treePanel->root->nodeType = 'async';
		$treePanel->root->expanded = true;
		$treePanel->root->singleClickExpand = true;
		$treePanel->root->checked = false;
		$treePanel->root->text = 'MODULOS DEL SISTEMA';
		$treePanel->root->children = $items;
		
		return $treePanel;
	}
	
	/**
	 * overwrited. Devuelve el manejador del menú
	 *
	 * @return ExjHelperMenu
	 */
	protected function getHandlerMenu(){
		$hMenu = ExjHelperMenu::CreateAccessReadOnly();
		return $hMenu;
	}
	
	
	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Derecha
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		$items = array();
		
		$btnSave = ExjUI::NewButtonSave('', 'Guarda los cambios realizados');
		$btnCancel = ExjUI::NewButtonCancel('', 'Cancela los cambios realizados');
		
		$items[] = $btnSave;
		$items[] = $btnCancel;
		
		return $items;
	}
	
	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	/*
	public function onGetData(&$items, &$total){
		// ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  ExjRolOptionsModel::LoadDataRolOptions($this->getResponse(), $items, $this->getBaseParams());
		if ($items) {
			$total = count($items);
		}
		
	//	ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	*/
	
}

?>