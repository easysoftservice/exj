<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para modelos de panel principal.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[componente]/models/[componente].container.model.php
 * Nombrado de la clase: Debe tener el formato: class App[Componente]ContainerModel extends ExjPanelMainModel
 */
class ExjPanelMainModel extends ExjModels {
    /* Propiedades del objeto UI */
    public $xtype = 'panel';
	public $title='';
	public $border= false;
	
	public $autoScroll = true;
	public $exjOffsetHeight = 0;
	
	/**
	 * Alto del control UI
	 *
	 * @var int
	 */
	public $height = 'auto';
	
	protected $typeModel = 'ExjPanelMainModel';
	
	
	
	private $_cols = array(), $_fields = array(), $_response=null;
	private $_sortDir = 'ASC';
	private $_SORT_DIR_ASC = 'ASC';
	private $_SORT_DIR_DESC = 'DESC';
	
	private $_childNameList = '', $_childNameEditable='', $_childNameComponent='', $_childKeyEditable='';
	private $_childParentEditable = '';
	
	private $_forceEnableViewLogPers = null;	

	private $_isUserAccessReadOnly = null;
	private $_textButtonDelete = 'Delete';
	
	private $_nameTopic = 'Item';
	private $_removeTopToolbar = false;
	
	/**
	 * Indica si se adiciona o no botones extras al topbar
	 *
	 * @var bool Por defecto true
	 */
	private $_addItemsTopbarExtras = true;
	
	/**
	 * Campo de defecto a ordenar la data
	 *
	 * @var string
	 */
	private $_defaultSort='';
	
	private $_requiereSelectionReport = false;
	private $_titleButtonReport = '';
	private $_forceShowReports = false;
	
	private $_hMenu=null;
	private $_nameContainerModel= '';
	private $_nameController= '';
	
	private $_isReportPDF = false;
	private $_isReportExcelXLS = false;
	private $_isReportExcelXLSX = false;
	private $_isReportHTML = false;
	
	private $_isReportXML= false;
	private $_reportXMLNameOption='';
	private $_width = null;
	
	private $_reportSendToMail = false;
	
	
	private $_baseParams=null;
	
	private $_nameComponentDefault='';
	
	private $_fixTitleToToolbar= true;
	
	
	/**
	 * Constructor del modelo de contenedor
	 *
	 * @param ExjHelperMenu $hMenu Instancia de ExjHelperMenu
	 * @param string $nameContainerModel
	 * @param string $nameController
	 */
	public function __construct(ExjHelperMenu $hMenu, $nameContainerModel='', $nameController=''){
		$newHMenu = $this->getHandlerMenu();
		if ($newHMenu !== null) {
			if ($newHMenu instanceof ExjHelperMenu) {
				$hMenu = $newHMenu;
			}
			else {
				Exj::SetErrorValidating("El método: getHandlerMenu() ha devuelto un objeto que no es instancia de ExjHelperMenu");
			}
		}

		$this->_hMenu = $hMenu;
		
		$this->setNameModelController($nameContainerModel, $nameController);
		$this->fixSortAsc();
		
		$this->panelInit();
	}
	
	public function setTitle($title){
		$this->title = $title;
	}
	
	public function setTextButtonDelete($textButtonDelete){
		$this->_textButtonDelete = $textButtonDelete;
	}
	
	/**
	 * Fija la compesación del alto del componente principal
	 *
	 * @param int $offsetHeight Por lo general deberia ser un valor negativo
	 */
	public function setOffsetHeight($offsetHeight){
		$this->exjOffsetHeight = $offsetHeight;
	}
	
	
	
	public function fixTitleToToolbar($fixed = false){
		$this->_fixTitleToToolbar = $fixed;
	}
	
    /**
     * Fija el nombre del componente para incluir los modelos
     *
     * @param string $nameComponent
     */
    public function fixNameComponentDefault($nameComponent){
    	$this->_nameComponentDefault = $nameComponent;
    }
    
    
    /**
     * Indica si el usuario actual tiene acceso de solo lectura
     *
     * @return bool
     */
	public function isUserAccessReadOnly(){
		if ($this->_isUserAccessReadOnly !== null) {
			return $this->_isUserAccessReadOnly;
		}
		
		$this->_isUserAccessReadOnly = ExjRequest::GetParam('isReadOnlyAccess', null);
		
		if ($this->_isUserAccessReadOnly !== null) {
			return $this->_isUserAccessReadOnly;
		}
		
		$this->_isUserAccessReadOnly = !Exj::IsUserAccessEdit();
		
		return $this->_isUserAccessReadOnly;
	}
    
    /**
     * Forza a activar el boton de ver logs de persistencia
     *
     * @param bool $enable por defecto true
     */
    public function forceEnableViewLogPers($enable = true){
    	$this->_forceEnableViewLogPers = $enable;
    }
	
    public function readNameComponent(&$nameComponent){
    	if (!$nameComponent) {
    	 	$nameComponent = $this->_nameComponentDefault;
    	 } 
    	 
    	 return $nameComponent;
    }
	
	/**
	 * overwrited. Devuelve el manejador del menú
	 *
	 * @return ExjHelperMenu
	 */
	protected function getHandlerMenu(){
		return null;
	}

	
	public function fixSortAsc(){
		$this->_sortDir = $this->_SORT_DIR_ASC;
	}
	public function fixSortDesc(){
		$this->_sortDir = $this->_SORT_DIR_DESC;
	}

	public function isSortAsc(){
		return ($this->_sortDir == $this->_SORT_DIR_ASC);
	}
	public function isSortDesc(){
		return ($this->_sortDir == $this->_SORT_DIR_DESC);
	}
	
	public function setNameModelController($nameContainerModel, $nameController=''){
		$this->_nameContainerModel = $nameContainerModel;
		if (!$nameController) {
			$nameController = $this->_nameContainerModel;
		}
		$this->_nameController = $nameController;
	}
	
	/**
	 * Envia si se soporta download
	 *
	 * @param bool $pdf
	 * @param bool $excelXLSX
	 * @param bool $excelXLS
	 */
	public function setReportDownload($pdf, $excelXLSX, $excelXLS, $isReportHTML=false){
		// NOTE: Se dehabilita pdf
	//	$pdf = false;
		$this->_isReportPDF = $pdf;
		$this->_isReportExcelXLS = $excelXLS;
		$this->_isReportExcelXLSX = $excelXLSX;
		$this->_isReportHTML = $isReportHTML;
	}
	
	public function setReportXMLDownload($nameOption, $nameFile){
		$this->_isReportXML = true;
		$this->_reportXMLNameOption = $nameOption;
		$this->_reportXMLNameFile = $nameFile;
	}
	
	public function getModuleName(){
		$moduleName = '';
		if (!$this->_hMenu) {
			return $moduleName;
		}
		
		if (isset($this->_hMenu->moduleNameAccess)) {
			$moduleName = $this->_hMenu->moduleNameAccess;
		}
		
		return $moduleName;
	}
	
	/**
	 * Indica si tiene acceso para crear Nuevo
	 *
	 * @return bool
	 */
	public function isAccessNew(){
		if (!$this->_hMenu) {
			return false;
		}
		
		return $this->_hMenu->isNew;
	}

	/**
	 * Indica si es posible guardar
	 *
	 * @return unknown
	 */
	public function isAccessSave(){
		if (!$this->_hMenu) {
			return false;
		}
		
		return $this->_hMenu->isSave;
	}
	
	/**
	 * Indica si tiene acceso para editar
	 *
	 * @return bool
	 */
	public function isAccessEdit(){
		return $this->isAccessSave();
	}
	
	/**
	 * Indica si tiene acceso para crear Eliminar
	 *
	 * @return bool
	 */
	public function isAccessTrash(){
		if (!$this->_hMenu) {
			return false;
		}
		
		return $this->_hMenu->isTrash;
	}

	/**
	 * Indica si tiene acceso para ver modelo solo lectura
	 *
	 * @return bool
	 */
	public function isAccessView(){
		if (!$this->_hMenu) {
			return false;
		}
		
		return $this->_hMenu->isView;
	}
	
	/**
	 * Indica si tiene acceso para ver el historial de los logs de persistencia
	 *
	 * @return bool
	 */
	public function isAccessViewLogPersistence(){
		if (!ExjUser::IsRolSuperOAdminOContabilidad()) {
			return false;
		}
		
		if ($this->_forceEnableViewLogPers !== null) {
			return $this->_forceEnableViewLogPers;
		}
		
		if (!$this->_nameController) {
			return false;
		}
		
		if ($this->_nameController == 'sys_log_pers') {
			return false;
		}
		
		if (substr($this->_nameController, 0, 4) == 'rep_') {
			return false;
		}
		
		$lengthCtrl = strlen($this->_nameController);
		
		$nameEditable = $this->_nameController;
		if (substr($this->_nameController, $lengthCtrl-1) == 's') {
			$nameEditable = substr($this->_nameController, 0, $lengthCtrl-1);
		}
		
//		echo "<br/>nameEditable: $nameEditable";
		$ClassEditable = Exj::GetNameClassEditable($nameEditable);
		if (class_exists($ClassEditable)) {
			return true;
		}
		
	//	echo "<br/>$ClassEditable NO existe";
		
		return false;
	}
	
	
	/**
	 * Indica si tiene acceso para los Reportes
	 *
	 * @return bool
	 */
	public function isAccessReports(){
		if ($this->_forceShowReports) {
			return true;
		}
		
		if (!$this->_hMenu) {
			return false;
		}
		
		if (!$this->_isReportExcelXLS && !$this->_isReportExcelXLSX && !$this->_isReportPDF && !$this->_isReportHTML) {
			return false;
		}
		
		return $this->_hMenu->isReports;
	}

	public function isAccessHelp(){
		if (!$this->_hMenu) {
			return false;
		}
		
		return $this->_hMenu->isAccessHelp();
	}
	
	public function setChildNameList($nameList){
		$this->_childNameList = $nameList;
	}
	public function setChildNameEditable($nameEditable, $keyEditable=''){
		$this->_childNameEditable = $nameEditable;
		if (!$keyEditable) {
			$keyEditable = $nameEditable;
		}
		$this->_childKeyEditable = $keyEditable;
	}
	public function setChildNameComponent($nameComponent){
		$this->_childNameComponent = $nameComponent;
	}
	
	public function setChildNameParentEditable($nameParentEditable){
		$this->_childParentEditable = $nameParentEditable;
	}
	
	/**
	 * Cuando se devuelve data, esta función debe ser sobrescrita
	 *
	 * @return object
	 */
	protected function onGetData(&$items, &$total){
		$items = array();
		$total = 0;
		
		return false;
	}
	
	public function readData(){
    	$items = null;
    	$total=0;
    	
    	$this->onGetData($items, $total);
    	if ($items && ($total <= 0)) {
    		$total = count($items);
    	}
    	
    	$this->setDataTopics($items, $total);
	}
	
	/* modelo de campos */
	public function &registerFieldString($name, $alias='', $useNull=false){
		return $this->_registerField($name, 'string', $alias, '', $useNull);
	}
	public function &registerFieldId($name, $alias='', $useNull=false){
		return $this->_registerField($name, 'int', $alias, '', $useNull);
	}
	
	public function &registerFieldIdNullable($name, $alias=''){
		return $this->registerFieldId($name, $alias, true);
	}
	
	public function &registerFieldBool($name, $alias='', $useNull = false){
		return $this->_registerField($name, 'bool', $alias, '', $useNull);
	}
	public function &registerFieldFloat($name, $alias='', $useNull = false){
		return $this->_registerField($name, 'float', $alias, '', $useNull);
	}

	public function &registerFieldFloatNullable($name, $alias=''){
		return $this->registerFieldFloat($name, $alias, true);
	}
	
	public function &registerFieldRaw($name, $alias=''){
		return $this->_registerField($name, '', $alias);
	}
	public function registerFieldDate($name, $alias='', $useNull = false){
		$this->_registerField($name, ExjField::TYPE_DATE, $alias, 'Y-m-d', $useNull);
	}

	public function &registerFieldDateTime($name, $alias='', $useNull = false){
		// $this->_registerField($name, ExjField::TYPE_DATE, $alias, 'timestamp');
		return $this->_registerField($name, ExjField::TYPE_DATE, $alias, 'Y-m-d H:i:s', $useNull);
	}
	
	private function &_registerField($name, $type, $alias='', $dateFormat='', $useNull=null) {
		if ($dateFormat && !$type) {
			$type = ExjField::TYPE_DATE;
		}
		
		if (isset($this->_fields[$name])) {
			$field = &$this->_fields[$name];
			$field->setType($type)
				->setAlias($alias)
				->setDateFormat($dateFormat);
		}
		else {
			$field = new ExjField($name, $type, $alias);
			$field->setDateFormat($dateFormat);
					
			$this->_fields[$name] = $field;	
		}
		
		if ($useNull !== null) {
			$this->_fields[$name]->useNull($useNull);
		}
		
		return $this->_fields[$name];
	}

	/**
	 * overwrite. Init del Modelo de Lista
	 *
	 */
	protected function panelInit(){
		
	}
	
	/**
	 * Devuelve modelo de fields para UI, por lo general usado en Store
	 *
	 * @return array Si no esta definidos campos retorna null
	 */
	public function to_ui_fields(){
		$ui_fields = array();
		
		if (!$this->_fields) {
			return null;
		}
		
		foreach ($this->_fields as $name => $f) {
			$ui_field = $name;
			if ($f->type) {
				$ui_field = new stdClass();
				$ui_field->name = $name;
				$ui_field->type = $f->type;
				if ($f->dateFormat) {
					$ui_field->dateFormat = $f->dateFormat;
				}
			}
			
			if ($f->isComplex) {
				$ui_field = new stdClass();
				$ui_field->name = $name;
				if (isset($f->defaultValue)) {
					$ui_field->defaultValue = $f->defaultValue;
				}
				$ui_field->convert = $f->convert;
			}
			
			if (is_object($ui_field)) {
				if ($f->isUseNull !== null) {
					$ui_field->useNull = $f->isUseNull;
				}
			}
			
			$ui_fields[] = $ui_field;
		}
		
		// print_r($ui_fields);
		
		return $ui_fields;
	}
	
	
	public function to_ui_cfgPagingToolbar(){
		$cfg = new stdClass();
		
		
		
		$cfg->xtype = 'paging';
		
		
		$cfg->displayInfo = true;
		
		
		return $cfg;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Izquierda
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		return null;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Derecha
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasRight(){
		return null;
	}
	
	/**
	 * overweited. Personalida el botón New
	 *
	 * @param string $text
	 * @param string $iconCls
	 * @param string $tooltip Si no se fija este valor se fija con el tooltip genérico
	 */
	protected function listButtonAddCustom(&$text, &$iconCls, &$tooltip){
		
	}
	
	public function setNameTopic($nameTopic){
		$this->_nameTopic = $nameTopic;
	}
	
	
	/**
	 * overwrited. Retorna un array de botones que se adicionarán en el toolbar, alado del botón New
	 *
	 * @param string $strNameTopic
	 * @return array Arreglo de botones
	 */
	protected function getButtonsAddExtras($strNameTopic){
		return null;
	}
	
	
	
	/**
	 * Establece titilo al panel de forma pura
	 *
	 * @param string $titlePage
	 * @param bool $removeTitle
	 */
	public function setTitlePage($titlePage, $removeTitle = true){
		if (!isset($this->headerCfg)) {
			$this->headerCfg = new stdClass();
		}
		
		$this->headerCfg->tag = 'h2';
		$this->headerCfg->html = $titlePage;
		$this->headerCfg->cls = 'exj-title-page';
		
		/*
		-moz-user-select: text !important;
    -khtml-user-select:text !important;
    -webkit-user-select: text !important;
		*/
	//	$this->headerCfg->style = '-moz-user-select: text;';
	//	$this->baseCls = 'x-plain';
		
		if ($removeTitle && $this->title) {
			$this->title = null;
		}
		
	}
	
	/**
	 * Remueve o elimina el top toolbar
	 *
	 * @param unknown_type $removeTopToolbar
	 */
	public function removeTopToolbar($removeTopToolbar = true){
		$this->_removeTopToolbar = $removeTopToolbar;
	}
	
	public function to_ui_cfgTopToolbar(){
		if ($this->_removeTopToolbar) {
			return null;
		}
		
		$cfg = new stdClass();
		
		
		$cfg->xtype = 'toolbar';
		$cfg->items = array();
		$strNameTopic = ExjText::_($this->_nameTopic);
		
		if ($this->isAccessNew()) {
			$textButtonNew = ExjText::__("New");
			$tooltipButtonNew = '';
			$iconClsButtonNew = '';
			$this->listButtonAddCustom($textButtonNew, $iconClsButtonNew, $tooltipButtonNew);
			
			if (!$tooltipButtonNew) {
				$tooltipButtonNew = "$textButtonNew " . $strNameTopic;
			}
			
			$btnNew = ExjUI::NewButtonAdd($textButtonNew, $tooltipButtonNew);
			if ($iconClsButtonNew) {
				$btnNew->iconCls = $iconClsButtonNew;
			}
			
			$cfg->items[] = $btnNew;
			
			$buttonsExtrasAdd = $this->getButtonsAddExtras($strNameTopic);
			if ($buttonsExtrasAdd && is_array($buttonsExtrasAdd)) {
				foreach ($buttonsExtrasAdd as $buttonExtraNew) {
					if (!is_object($buttonExtraNew)) {
						continue;
					}
					if (!isset($buttonExtraNew->iconCls)) {
						$buttonExtraNew->iconCls = 'exj-btn-new';
					}
					if (!$buttonExtraNew->iconCls) {
						$buttonExtraNew->iconCls = 'exj-btn-new';
					}
					
					$cfg->items[] = $buttonExtraNew;
				}
				
				$cfg->items[] = '-';
			}
		}
		if ($this->isAccessEdit()) {
			$cfg->items[] = ExjUI::NewButtonEdit('', ExjText::__('Edit').  ' '. $strNameTopic);
		}

		if ($this->isAccessView()) {
			if (count($cfg->items) > 0) {
				$cfg->items[] = '-';
			}
			$strVer = ExjText::__('View');
			$cfg->items[] = ExjUI::NewButtonView("$strVer $strNameTopic". '...', "$strVer $strNameTopic");
		}
		
		if ($this->_addItemsTopbarExtras) {
			$itemsTopbarExtrasLeft = $this->getItemsTopbarExtrasLeft();
			if ($itemsTopbarExtrasLeft && count($itemsTopbarExtrasLeft) > 0) {
				if (count($cfg->items) > 0) {
					$cfg->items[] = '-';
				}
				
				foreach ($itemsTopbarExtrasLeft as $item) {
					$cfg->items[] = $item;
				}
			}
		}
		
		if ($this->_fixTitleToToolbar && $this->title) {
			$tbTitleCenter = new stdClass();
			// $tbTitleCenter->xtype = 'tbtext';
			// $tbTitleCenter->xtype = 'panel';
			$tbTitleCenter->xtype = 'container';
		
			$tbTitleCenter->cls = 'exj-toolbar-title';
		//	$tbTitleCenter->itemCls = 'exj-toolbar-title';
			$tbTitleCenter->layout = 'exj_centertb'; // layout personalizado
		
			$tbTitleCenter->html = '<div class="exj-toolbar-title">'.$this->getTitlePanel() . '</div>';
			
			$cfg->items[] = $tbTitleCenter;
			
			// $this->setTitle('');
		}
		
		if (count($cfg->items) > 0) {
			$cfg->items[] = '->';
		}
		
		if ($this->_addItemsTopbarExtras) {
			$itemsTopbarExtrasRight = $this->getItemsTopbarExtrasRight();
			if ($itemsTopbarExtrasRight && count($itemsTopbarExtrasRight) > 0) {
				foreach ($itemsTopbarExtrasRight as $item) {
					$cfg->items[] = $item;
				}
			}
		}
		
		if ($this->isAccessViewLogPersistence()) {
			$cfg->items[] = ExjUI::NewButtonViewLogPersistence('', ExjText::__('Historial de Cambios') . " $strNameTopic");
		}
		
		if ($this->isAccessTrash()) {
			$cfg->items[] = ExjUI::NewButtonDelete($this->_textButtonDelete.'...', ExjText::__($this->_textButtonDelete). " $strNameTopic");
		}

		if ($this->isAccessReports()) {
			if (count($cfg->items) > 0) {
				$cfg->items[] = '-';
			}
			else {
				$cfg->items[] = '->';
			}

			// se crea en forma de menú
			$itemsMenuReporte = array();
			
			if ($this->_isReportXML) {
				$itemsMenuReporte[] = ExjUI::NewMenuItemXML($this->_reportXMLNameOption);
				if ($this->_isReportPDF || $this->_isReportExcelXLSX) {
					$itemsMenuReporte[] = '-';	
				}
			}

			if ($this->_isReportHTML) {
				$itemsMenuReporte[] = ExjUI::NewMenuItemHTML();
				if ($this->_isReportPDF || $this->_isReportExcelXLSX) {
					$itemsMenuReporte[] = '-';	
				}
			}
			
			
			
			if ($this->_isReportPDF) {
				$itemsMenuReporte[] = ExjUI::NewMenuItemPDF();
			}
			if ($this->_isReportExcelXLSX) {
				$itemsMenuReporte[] = ExjUI::NewMenuItemExcelXLSX();
			}
			if ($this->_isReportExcelXLS) {
				$itemsMenuReporte[] = ExjUI::NewMenuItemExcelXLS();
			}
			
			$nameBtnReporte = "Report";
			if ($this->_requiereSelectionReport) {
				if ($this->_titleButtonReport) {
					$nameBtnReporte = $this->_titleButtonReport;
				}
				else {
					$nameBtnReporte = ExjText::__('Report selected') . " $strNameTopic";
				}
			}
			
			$menuReporte = ExjUI::NewBotonMenu($nameBtnReporte, 'exj-btn-rep', $itemsMenuReporte, 'Report options');
			$menuReporte->setAction('rep_mnu', true);
			$menuReporte->requiereSelection = $this->_requiereSelectionReport;
			
			$cfg->items[] = $menuReporte;
		}
		
		if ($this->isAccessHelp()) {
			$cfg->items[] = ExjUI::NewButtonHelp(ExjText::__('Provide help of'). " ". $strNameTopic);
		}
		
		return $cfg;
	}
	
	/**
	 * Fija el ancho del Panel
	 *
	 * @param int $width
	 */
	public function fixGridPanel($width){
		$this->_width = $width;
	}

	/**
	 * Fija el alto del Panel
	 *
	 * @param int $height
	 */
	public function fixPanelHeight($height){
		$this->height = $height;
	}
	
	public function getTitlePanel(){
		return ExjText::_($this->title);
	}
	
	private function _buildCfgGrid($forceFit= true){
		
	}
	
	public function setBaseParams($objParams){
		$this->_baseParams = $objParams;
	}
	
	public function setBaseParam($param, $value){
		if (!$param) {
			return false;
		}
		
		if (!$this->_baseParams) {
			$this->_baseParams = new stdClass();
		}
		
		if ($param == 'criteria' && is_string($value)) {
			$paramCriteria = json_decode(stripslashes($value));
			$vars = get_object_vars($paramCriteria);
			foreach ($vars as $nameCriteria => $valueCriteria) {
				$this->setBaseParam($nameCriteria, $valueCriteria);
			}
		}
		else {
			$this->_baseParams->$param = $value;
		}
		
		return $this->_baseParams;
	}
	
	public function getBaseParams(){
		return $this->_baseParams;
	}
	
	/**
	 * Devuelve parametro desde baseParams
	 *
	 * @param string $nameParam
	 * @param mixed $default
	 * @return mixed
	 */
	public function getParamFromBaseParams($nameParam, $default=''){
		if (!$this->_baseParams) {
			return $default;
		}
		
		if (isset($this->_baseParams->$nameParam)) {
			return $this->_baseParams->$nameParam;
		}
		
		return $default;
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
		
	}
	
	protected function to_ui_cfgStore($remoteSort = true){
		$storeId = 'sto' . get_class($this);
		$root = 'DataTopics.topics';
		$totalProperty = 'DataTopics.total';
		$idProperty = '';
		$url = '';
		
		$this->registerJsonStore($storeId, $url, $idProperty, $remoteSort);
		
		$fieldsUI = $this->to_ui_fields();
		if (!$fieldsUI && !$url) {
			return null;
		}
		
		if (!$fieldsUI) {
			$fieldsUI = array();
			if ($idProperty) {
				$fieldsUI[] = $idProperty;
			}
		}
		
		$cfg = new stdClass();
		$cfg->autoDestroy = true;
		
		if ($storeId) {
			$cfg->storeId = $storeId;
		}
		
		$cfg->fields = $fieldsUI;
		
		$cfg->remoteSort = $remoteSort;
		if ($idProperty) {
			$cfg->idProperty = $idProperty;
		}
		
		$cfg->url = $url;
		
		$bp = $this->getBaseParams();
		$cfg->baseParams = new stdClass();
		if ($bp) {
			$cfg->baseParams = $bp;
		}
	
		$cfg->root = $root;
		$cfg->totalProperty = $totalProperty;
		
		return $cfg;
	}
	
	public function setDataTopics($items, $total= -1){
		$response = $this->getResponse();
		$this->_setDataTopicsToRespose($response, $items, $total);
	}
	
	private function _setDataTopicsToRespose(ExjResponse &$response, $items, $total){
		if ($items && $total < 0) {
			$total = count($items);
		}
		
		$response->setDataTopics($items, $total);
	}
	
	public function setResponse(ExjResponse $response){
		$this->_response = $response;
	}
	
	/**
	 * Retorna instancia de clase ExjResponse
	 *
	 * @return ExjResponse
	 */
	public function &getResponse(){
		if (!$this->_response) {
			$this->_response = new ExjResponse();
		}
		
		return $this->_response;
	}

	
	private function _getDataResponse(){
		$response = $this->getResponse();
		return $response->toObject();
	}

	/**
	 * Devuelve el alias del campo pasado por parametro
	 *
	 * @param string $nameField
	 * @return string
	 */
	private function _getAliasFromFields($nameField){
		$alias = '';
		
		if (isset($this->_fields[$nameField])) {
			$f = $this->_fields[$nameField];
			$alias = trim($f->alias);
		}
		
		return $alias;
	}
	
	/**
	 * Devuelve un objeto para ser usado en la UI.
	 * Para store y grid
	 *
	 * @return object
	 */
	public function to_ui(){
		$uiModel = parent::to_ui();
		
		$selfObj = $this->toObject();
		
		$ui = new stdClass();
		$vars = get_object_vars($selfObj);
		foreach ($vars as $name => $value) {
			$ui->$name = $value;
		}
		
		$uiModel->sortDir = $this->_sortDir; 
		$uiModel->cfgStore = $this->to_ui_cfgStore();
		
		$uiModel->dataResponse = $this->_getDataResponse();
		
		$ui->dataModel = $uiModel;
		
		$itemsUI = $this->_itemsUI;
		if (!$itemsUI) {
			$itemsUI = array();
		}
		
		$dt = $uiModel->dataResponse->DataTopics;
		$this->loadItemsUI($itemsUI, $dt->topics, $dt->total);
		if ($itemsUI) {
			$ui->items = $itemsUI;
		}
		
		$tbar = $this->to_ui_cfgTopToolbar();
		if ($tbar) {
			$ui->tbar = $tbar;
			// print_r($tbar);
			if (isset($ui->title)) {
				unset($ui->title);
			}
		}
		
		
		$this->renderUIContainerModel($ui);
		
		return $ui;
	}
	
	private $_itemsUI = null;
	public function addItemUI($itemUI){
		if (!$this->_itemsUI) {
			$this->_itemsUI = array();
		}
		
		$this->_itemsUI[] = $itemUI;
	}
	
	/**
	 * overwrited. Carga los items que se presentarán en la UI
	 *
	 * @param array $itemsUI Pasado por referencia
	 * @param array $items
	 * @param int $total
	 */
	protected function loadItemsUI(&$itemsUI, $items, $total){
		
	}
	
	/**
	 * overwrite. Renderiza el objeto pasado a la UI
	 *
	 * @param object $ui
	 */
	protected function renderUIContainerModel(&$ui){
		
	}

	
}

?>