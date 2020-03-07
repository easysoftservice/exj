<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Manejador Base para templates de reportes HTML
 *
 */
class AppBaseReportTmplHandler {
	/**
	 * Unidad de medida para HTML es milímetros
	 *
	 */
	const MEDIDA_HTML='mm';
	const TPL_KEY_CONTENTHTML = '{contentHTML}';
	const TPL_KEY_ATTR_PAG = '{attrPag}';
	const TPL_KEY_ATTR_PAG_CONTENT = '{attrPagContent}';
	
	const PREFIX_NAMEFILE_CSS = 'vu_rep_';
	const ENDLINE = "\n"; // fin de linea
	const SIZE_AUTO = 'auto';
	
	const CLASS_REP_VALUE = 'vu-rep-value';
	const CLASS_REP_HVALUE = 'vu-rep-h-value';
	const CLASS_REP_ITEM_ABS = 'vu-rep-item-abs';
	const CLASS_REP_INPUT = 'vu-rep-input';
	const CLASS_REP_RADIOX = 'vu-rep-radiox';
	
	const MAX_ITEMS_FOR_PAGE = 36;
	const MAX_ITEMS_FOR_FIRSTPAGE = 30;
	
	const TITULO_DEFECTO = 'COMPROBANTE ELECTRONICO';


	private $_namePaper = '';
	private $_widthPag = 0;
	private $_heightPag = 0;
	
	private $_marginTop = -1;
	private $_marginLeft = -1;
	private $_marginBottom = -1;
	private $_marginRight = -1;
	
	private $_tpl = '';
	private $_nameTmplContent = '';
	private $_isEmptyContentHTML = false;
	private $_heightSepContents = 0;
	
	private $_nameCSS = '';
	private $_componentReport='';
	
	private $_params = null;
	private $_nPrintContent = 0;
	private $_maxPrintContent = 1;
	
	private $_baseReportParams = null;
	private $_headersFromListModel = null;
	private $_offset_items_page = 0;

	/**
	 * Constructor, medidas dadas en milímetros
	 *
	 */
	public function __construct(){
		
		/*
		$this->_widthPag = 201;
		$this->_heightPag = 250;
		*/
		
		$this->configPage($this->_widthPag, $this->_heightPag);
		$this->configMargins($this->_marginTop, $this->_marginLeft, $this->_marginBottom, $this->_marginRight);
		
		$this->_readPrinterCfgPage();
	//	print_r($this);
		$this->_validatePaper();
		
		$this->configTemplate($this->_tpl);
		$this->configNumPrintContent($this->_maxPrintContent, $this->_heightSepContents);
		
		$nameTmpl = $this->getParamBaseReportNameTemplate();
		$this->setContentTemplate($nameTmpl);
		$this->setContentCSS($nameTmpl);
		
		$paramsReport = null;
		$this->loadParams($this->getParamBaseReportDataRpt(), $paramsReport);
		$this->setParams($paramsReport);

		$this->afterConfig();
	}
	
	private function _validatePaper(){
		if (!$this->_widthPag || $this->_widthPag < 0) {
			$this->_widthPag = 216;
		}
		
		if (($this->_heightPag != self::SIZE_AUTO) && (!$this->_heightPag || $this->_heightPag < 0)) {
			$this->_heightPag = 355;
		}
		
		if ($this->_marginBottom < 0) {
			$this->_marginBottom = 0;
		}
		if ($this->_marginTop < 0) {
			$this->_marginTop = 0;
		}
		if ($this->_marginLeft < 0) {
			$this->_marginLeft = 0;
		}
		if ($this->_marginRight < 0) {
			$this->_marginRight = 0;
		}
		
	}
	
	private function _readPrinterCfgPage(){
		// verificar si ya fueron fijados valores
		if ($this->_widthPag && $this->_heightPag) {
			if ($this->_marginBottom >= 0 && $this->_marginTop >= 0 && $this->_marginLeft >= 0 && $this->_marginRight >= 0) {
				return true;
			}
		}
		
		$db = Exj::InstanceDatabase();
		
		$id_empresa = ExjUser::GetIdEmpresa();
		
		$component = $this->getParamBaseReportComponent();
		$nameTmpl = $this->getParamBaseReportNameTemplate();
		
	//	echo "<br/>_readPrinterCfgPage. component: $component nameTmpl: $nameTmpl";
		
		$query = "SELECT
	  pap.name_paper, pap.width_paper, pap.height_paper, pap.is_custom_paper,
	  cfg.margin_top_mm, cfg.margin_left_mm, cfg.margin_bottom_mm, cfg.margin_right_mm, cfg.offset_items_page 
	FROM
	  jos_exj_sys_printer_cfg_pag cfg 
	  INNER JOIN jos_exj_sys_printer_papers pap ON cfg.id_printer_paper = pap.id_printer_paper
	WHERE
	  cfg.name_component_cp = '$component' AND
	  cfg.name_tmpl_cp = '$nameTmpl' AND
	  cfg.id_empresa = $id_empresa
	ORDER BY
	  pap.is_enable_paper DESC
	LIMIT 1";
		
		$cfg = null;
		$db->setQuery($query);
		$db->loadObject($cfg);
		if (!$db->isValid()) {
			$this->printError($db->getErrorMsg());
			return false;
		}
		
		if ($cfg) {
			$this->_adjustSizePaper($cfg);
			
			if (!$this->_widthPag && !$this->_heightPag) {
				$this->_namePaper = $cfg->name_paper;
			}
			
			if (!$this->_widthPag) {
				$this->_widthPag = $cfg->width_paper;
			}
			if (!$this->_heightPag) {
				$this->_heightPag = $cfg->height_paper;
			}
			
			
			if ($this->_marginTop < 0) {
				$this->_marginTop = $cfg->margin_top_mm;
			}
			if ($this->_marginLeft < 0) {
				$this->_marginLeft = $cfg->margin_left_mm;
			}
			if ($this->_marginBottom < 0) {
				$this->_marginBottom = $cfg->margin_bottom_mm;
			}
			if ($this->_marginRight < 0) {
				$this->_marginRight = $cfg->margin_right_mm;
			}
			
			if ($this->_offset_items_page == 0) {
				$this->_offset_items_page = $cfg->offset_items_page;
			}
			
			return true;
		}
		
		if ($this->_widthPag && $this->_heightPag) {
			return true;
		}
		
		// Leer el papel por defecto del sistema
		$query = "SELECT
		  pap.name_paper, pap.width_paper, pap.height_paper, pap.is_custom_paper 
		FROM
		  jos_exj_sys_printer_papers pap
		WHERE
		  pap.is_enable_paper = 1
		ORDER BY
		  pap.is_default_paper DESC
		Limit 1";
		
		$db->setQuery($query);
		$db->loadObject($cfg);
		
		if ($cfg) {
			$this->_adjustSizePaper($cfg);
			
			if (!$this->_widthPag && !$this->_heightPag) {
				$this->_namePaper = $cfg->name_paper;
			}
			
			if (!$this->_widthPag) {
				$this->_widthPag = $cfg->width_paper;
			}
			if (!$this->_heightPag) {
				$this->_heightPag = $cfg->height_paper;
			}
			
			return true;
		}
		
		return false;
	}
	
	private function _adjustSizePaper(&$cfg){
		if (!$cfg) {
			return ;
		}
		
		if (!isset($cfg->is_custom_paper) || $cfg->is_custom_paper != 0) {
			return ;
		}
		
		if (isset($cfg->height_paper) && $cfg->height_paper > 60) {
			// compesación por margenes de UI
			$cfg->height_paper -= 2;
			// echo __METHOD__ . " cfg->height_paper: $cfg->height_paper";
		}
		
	}
	
	public function getNamePaper(){
		if (!$this->_namePaper) {
			return 'Personalizado';
		}
		
		return $this->_namePaper;
	}

	/**
	 * overwrite. Carga parámetros para el reporte
	 *
	 * @param object $dataRpt
	 * @param object $params Las propiedades del objeto deben ser las mismas definidas del reporte
	 */
	protected function loadParams($dataRpt, &$params){
		$params = $dataRpt;
	}
	
	private $_objListModel = null;
	
	public function getDivPageBreak(){
		return '<div class="page-break"></div>';
	}
	
	public function setListModel($nameListModel, $component='', $params=null){
		$ClassListModel = ExjUtil::GetNameClassModelListFromName($nameListModel);
		
		
		
		$objListModel = new $ClassListModel(null);
		$objListModel->fixModeLocal();
		$objListModel->setBaseParams($params);
		$objListModel->readData();
		
		$this->_objListModel = $objListModel;
		
		$this->_validateObjListModel($objListModel);
	}
	
	private function _validateObjListModel(ExjListModel $objListModel){
		if ($objListModel->getResponse()->haveMsgError()) {
			$this->printError($objListModel->getResponse()->getErrorMsg());
			return false;
		}
		
		return true;
	}
	
	/**
	 * Obtiene un objeto tipo List Model
	 *
	 * @return ExjListModel
	 */
	public function getObjListModel(){
		return $this->_objListModel;
	}
	
	public function getHeadersFromListModel(){
		if (!$this->_objListModel) {
			return null;
		}
		
		return $this->_getHeadersFromListModel($this->_objListModel);
	}
	
	
	
	private function _getHeadersFromListModel(ExjListModel $listModel){
		if ($this->_headersFromListModel) {
			return $this->_headersFromListModel;
		}
		
		$this->_headersFromListModel = $listModel->to_ui_columns();
		
		
		// calcular el ancho en mm
		$widthTotalPx = 0;
		foreach ($this->_headersFromListModel as $itemHeader) {
			$widthTotalPx += $itemHeader->width;
		}
		
		$maxHeaders = count($this->_headersFromListModel);
		
		// ancho dado en milimetros
		$widthMMContentPage = $this->_getWidthContentPage()-$maxHeaders-2;
		$widthTotalMM = 0;
		$indexHeader = 0;
		
		foreach ($this->_headersFromListModel as &$itemRefHeader) {
			$widthMM = ($itemRefHeader->width * $widthMMContentPage) / $widthTotalPx;
			$widthMM = round($widthMM, 0);
			
			if (++$indexHeader >= $maxHeaders) {
				// ultima columna
				$offsetWidthMM = $widthMMContentPage - ($widthTotalMM + $widthMM);
				if ($offsetWidthMM != 0) {
					$widthMM += $offsetWidthMM;
				}
				
		//		echo "<br/>indexHeader: $indexHeader offsetWidthMM: $offsetWidthMM";
			}
			
			$widthTotalMM += $widthMM;
			
			$itemRefHeader->widthMM = $widthMM;
		}
		
	//	echo "<br/>widthMMContentPage: $widthMMContentPage widthTotalMM: $widthTotalMM";
		
		// print_r($this->_headersFromListModel);
		
		return $this->_headersFromListModel;
	}
	
	public function getItemsListModel(){
		if (!$this->_objListModel) {
			return null;
		}
		
		return $this->_getItemsListModel($this->_objListModel);
	}
	
	private $_itemsListModel = null;
	
	private function _getItemsListModel(ExjListModel $listModel){
		if ($this->_itemsListModel) {
			return $this->_itemsListModel;
		}
		
		$data = $listModel->getData();
		if (!$data) {
			return null;
		}
		
		$this->_itemsListModel = $data->DataTopics->topics;
		if (!$this->_itemsListModel) {
			$this->_itemsListModel = array();
		}
		
		return $this->_itemsListModel;
	}
	
	
	public function getFieldsListModel(){
		if (!$this->_objListModel) {
			return null;
		}
		
		return $this->_getFieldsListModel($this->_objListModel);
	}
	
	private function _getFieldsListModel(ExjListModel $listModel){
		$fields = $listModel->to_ui_fields();
	//	print_r($fields);
		return $fields;
	}
	
	
	
	
	/**
	 * overwrite. Después de configurar el reporte
	 *
	 */
	protected function afterConfig(){
		
	}
	
	public function getBaseReportParams(){
		if (!$this->_baseReportParams) {
			global $baseReportParams;
			$this->_baseReportParams = $baseReportParams;
		}
		
		return $this->_baseReportParams;
	}
	
	/**
	 * Crea una instancia del contenido del reporte
	 *
	 * @return AppBaseReportTmplHandler
	 */
	public static function _CreateInstanceRepContent(){
		$NameClassReportContent = self::GetNameClassReportContent();
		
		$instanceRepContent = new $NameClassReportContent();
		
		
		return $instanceRepContent;
	}
	
	public function getAttrBody(){
		$attrBody = '';
		if ($this->getParamBaseReportOutPrint()) {
			$attrBody = 'onload="imprimirReporte()"';
		}
		
		return $attrBody;
	}
	
	public static function GetNameClassReportContent(){
		global $baseReportParams;
		
		$nameTmpl = $baseReportParams->nameTmpl;
		
		$nameClass = Exj::GetPrefixClassApp();
		$nameClass .= ExjUtil::ConvertirGionesToUcfirst($nameTmpl);
		$nameClass .= 'TmplHandler';
				
		return $nameClass;
	}
	
	public static function GetURIReportContent(){
		global $baseReportParams;
		
		$uriReportContent = '';
		$uriReportContent .= 'components';
		
		$uriReportContent .= '/'.$baseReportParams->nameCmp;
		
		return $uriReportContent;
	}
	
	public static function GetURIImageReportContent($nameFileImg){
		$uriImageReportContent = self::GetURIReportContent();
		$uriImageReportContent .= '/views/tmpl/images/' .$nameFileImg;
		
		return $uriImageReportContent;
	}

	/**
	 * Convierte los caracteres a UTF8
	 *
	 * @param string $text
	 * @return string
	 */
	public static function ParseCharset($text){
		if ($text === null || $text === "") {
			return '';
		}
		
		Exj::TrasferCharsEncodeISOToUTF8($text);
		
		return $text;
	}
	
	/**
	 * Obtiene el titulo principal del reporte
	 *
	 * @return string
	 */
	public static function GetTituloRep(){
		$tit = self::TITULO_DEFECTO;
		
		$tit = self::ParseCharset($tit);
		
		return $tit; 
	}
	
	private function _getParamBaseReport($nameParam, $valueDefault = ''){
		$brp = $this->getBaseReportParams();
		if (!$brp) {
			return $valueDefault;
		}
		
		return $brp->$nameParam;
	}
	
	public function getParamBaseReportComponent(){
		return $this->_getParamBaseReport('nameCmp');
	}

	public function getParamBaseReportNameTemplate(){
		return $this->_getParamBaseReport('nameTmpl');
	}

	public function getParamBaseReportDataRpt(){
		return $this->_getParamBaseReport('dataRpt');
	}
	public function getParamBaseReportIsPreview(){
		return $this->_getParamBaseReport('isPreView', false);
	}
	public function getParamBaseReportOutPrint(){
		return $this->_getParamBaseReport('outPrint', false);
	}
	public function getParamBaseReportOutScreen(){
		return $this->_getParamBaseReport('outScreen', true);
	}
	
	/**
	 * Indica si el reporte está en modo vista previa
	 *
	 * @return bool
	 */
	public function isPreview(){
		return $this->getParamBaseReportIsPreview();
	}
	
	/**
	 * overwrite. Configuración de la página
	 *
	 * @param int $widthPag
	 * @param int $heightPag Se puede fijar auto
	 */
	protected function configPage(&$widthPag, &$heightPag){
		
	}
	
	public function setContentTemplate($nameTpl){
		$this->_nameTmplContent = $nameTpl;
	}
	
	public function setContentCSS($nameCSS){
		$this->_nameCSS = $nameCSS;
	}
	
	public function getComponentReport(){
		if (!$this->_componentReport) {
			$this->_componentReport = $this->getParamBaseReportComponent();
		}
		
		return $this->_componentReport;
	}
	
	private function _getFileCSS($type){
		if (!$this->_nameCSS) {
			return '';
		}
		
		$fileCSS = self::PREFIX_NAMEFILE_CSS . $this->_nameCSS;
		$fileCSS .= ".$type";
		
		return self::GetPathCSS($this->getComponentReport(), $fileCSS);
	}
	
	public function getFileCSSForScreen(){
		return $this->_getFileCSS('screen');
	}
	public function getFileCSSForPrinter(){
		return $this->_getFileCSS('printer');
	}
	
	public function getLinkStylesSheetScreen(){
		$fileCSSForScreen = $this->getFileCSSForScreen();
		if (!$fileCSSForScreen) {
			return '';
		}
		
		return sprintf('<link rel="stylesheet" type="text/css" media="screen" href="%s" />', $fileCSSForScreen);
	}
	
	public function getLinkStylesSheetPrinter(){
		$fileCSSForPrinter = $this->getFileCSSForPrinter();
		if (!$fileCSSForPrinter) {
			return '';
		}
		
		return sprintf('<link rel="stylesheet" type="text/css" media="print" href="%s" />', $fileCSSForPrinter);
	}
	
	public function writeLinksStyles(){
		$linkStylesSheetScreen = $this->getLinkStylesSheetScreen();
		$linkStylesSheetPrinter = $this->getLinkStylesSheetPrinter();
		
		if ($linkStylesSheetScreen) {
			echo self::ENDLINE . $linkStylesSheetScreen;
		}
		if ($linkStylesSheetPrinter) {
			echo self::ENDLINE . $linkStylesSheetPrinter . self::ENDLINE;
		}
	}
	
	
	/**
	 * Obtiene el objeto de impresion
	 *
	 * @return AppBaseReportTmplHandler
	 */
	public static function GetScopeTmpl(){
		global $scopeTmpl;
		
		return $scopeTmpl;
	}
	
	public static function GetValueHTMLAbs($nameParam, $left, $top, $width='auto', $valueDefault='', $styleExtra=''){
		$topOffset = self::GetTopOffset();
		if ($topOffset != 0) {
			$top = floatval($top) + $topOffset;
		}

		if (is_numeric($left)) {
			$left = self::ConvertMedidaToHTML($left);
		}
		if (is_numeric($top)) {
			$top = self::ConvertMedidaToHTML($top);
		}
		if (is_numeric($width)) {
			$width = self::ConvertMedidaToHTML($width);
		}
		
		$style = "width: $width;left: $left;top: $top;";
		if ($styleExtra) {
			$style .= $styleExtra;
		}
		return self::GetValueHTML($nameParam, $valueDefault, 'span', self::CLASS_REP_ITEM_ABS, $style);
	}
	
	public static function GetTopOffset(){
		$topOffset = 0;
		
		$scope = self::GetScopeTmpl();
		if (!$scope) {
			return $topOffset;
		}
		
		$numPrintContent = $scope->getNumPrintContent();
		
		if ($numPrintContent <= 1) {
			return $topOffset;
		}
		
	//	echo "<br/>numPrintContent: $numPrintContent";
		
		$heigthCopy = $scope->getValueHeigthNumPrintContent();
		$topOffset = ($numPrintContent -1) * $heigthCopy;
		
		return $topOffset;
	}

	/**
	 * overwrited. Obtiene la compesación de la altura de las veces que se imprime el reporte
	 *
	 * @param int $numPrintContent
	 * @return float Defecto 0
	 */
	protected function getOffsetHeigthNumPrintContent($numPrintContent){
		return 0;
	}
	
	public static function GetInputHiddenValue($nameParam,  $valueDefault=''){
		return self::GetInputHidden($nameParam, self::GetValueRaw($nameParam, $valueDefault));
	}

	public static function PrintInputHiddenValue($nameParam,  $valueDefault=''){
		if (self::IsFirstNumPrintContent()) {
			echo self::GetInputHiddenValue($nameParam, $valueDefault);
		}
	}
	
	public static function GetStylePosition($left, $top, $width = null){
		$topOffset = self::GetTopOffset();
		if ($topOffset != 0) {
			$top = floatval($top) + $topOffset;
		}
		
		if (is_numeric($left)) {
			$left = self::ConvertMedidaToHTML($left);
		}
		if (is_numeric($top)) {
			$top = self::ConvertMedidaToHTML($top);
		}
		if ($width !== null && is_numeric($width)) {
			$width = self::ConvertMedidaToHTML($width);
		}
		
		$style = "left: $left;top: $top;";
		if ($width) {
			$style .= "width: $width;";
		}
		
		return $style;
	}
	
	public static function IsFirstNumPrintContent($scope=null){
		if (!$scope) {
			$scope = self::GetScopeTmpl();
		}
		
		if (!$scope) {
			return false;
		}
		
		return ($scope->getNumPrintContent() <= 1);
	}
	
	public static function PrintInputTextValueCenter($nameParam, $left, $top, $width, $valueDefault=''){
		$style = 'text-align: center;';
		self::PrintInputTextValue($nameParam, $left, $top, $width, $valueDefault, $style);
	}

	public static function PrintInputTextValue($nameParam, $left, $top, $width, $valueDefault='', $styleExtra=''){
		if (!self::IsFirstNumPrintContent()) {
			self::PrintValueHTMLAbs($nameParam, $left, $top, $width, $valueDefault, $styleExtra);
			return ;
		}
		
		$style = self::GetStylePosition($left, $top, $width);
		if ($styleExtra) {
			$style .= $styleExtra;
		}
		
		$attrs = array();
		
		$attrs[] = self::CreateAttrHTML('class', self::CLASS_REP_ITEM_ABS . ' '. self::CLASS_REP_INPUT);
		
		echo self::GetInputHTML($nameParam, self::GetValueRaw($nameParam, $valueDefault), 'text', $style, $attrs);
	}
	
	public static function PrintInputRadioCustomValue($nameParam, $leftSI, $leftNO, $top,  $valueDefault=''){
		$valueRaw = self::GetValueRaw($nameParam, $valueDefault);
		if ($valueRaw === '') {
			$valueRaw = 0;
		}
		
		$valueSI = '';
		$valueNO = '';
		$MARCK_SEL = 'X';
		
	//	if ($valueRaw != '') {
			if ($valueRaw && $valueRaw != "0"){
				$valueSI = $MARCK_SEL;
			}
			else {
				$valueNO = $MARCK_SEL;
			}
//		}
		
		$styleSI = self::GetStylePosition($leftSI, $top);
		$styleNO = self::GetStylePosition($leftNO, $top);
		
		$idSi = 'si_'.$nameParam;
		$idNo = 'no_'.$nameParam;
		
		$attrsDefs = array();
		$attrsSI = array();
		$attrsNO = array();
		
		$attrsDefs[] = self::CreateAttrHTML('class', self::CLASS_REP_ITEM_ABS . ' '. self::CLASS_REP_RADIOX);
		$attrsDefs[] = self::CreateAttrHTML('readonly', 'readonly');
		
		foreach ($attrsDefs as $attrDef) {
			$attrsSI[] = $attrDef;
			$attrsNO[] = $attrDef;
		}
		
		$jsSI = $jsNO = "this.value='$MARCK_SEL';";
		
		$jsSI .= "document.getElementById('$idNo').value='';document.getElementById('$nameParam').value='1'";
		$jsNO .= "document.getElementById('$idSi').value='';document.getElementById('$nameParam').value='0'";
		
		$attrsSI[] = self::CreateAttrHTML('onclick', $jsSI);
		$attrsNO[] = self::CreateAttrHTML('onclick', $jsNO);
		
		
		$html = self::CreateTagStart('span');
		
		$html .= self::GetInputHTML($idSi, $valueSI, 'text', $styleSI, $attrsSI);
		$html .= self::GetInputHTML($idNo, $valueNO, 'text', $styleNO, $attrsNO);
		
		
		$html .= self::GetInputHidden($nameParam, $valueRaw);
		$html .= self::CreateTagEnd('span');
		
		echo $html;
	}
	
	
	public static function GetValueRaw($nameParam,  $valueDefault=''){
		$scope = self::GetScopeTmpl();
		if (!$scope) {
			return $valueDefault;
		}
		
		$value = $scope->getParam($nameParam, $valueDefault);
		
		$value = $scope->renderValue($value, $nameParam);
		if ($value === null) {
			$value = '';
		}
		elseif ($value && is_string($value)){
			Exj::TrasferCharsEncodeISOToUTF8($value);
		}
		
		return $value;
	}
	
	public static function GetInputHidden($id, $value){
		return self::GetInputHTML($id, $value, 'hidden');
	}
	
	public static function GetInputHTML($id, $value, $type='text', $style=null, $attrsExtras=null){
		if ($style && is_array($style)) {
			$style = implode(';', $style);
		}
		
		
		
		$attrs = array();
		$attrs[] = self::CreateAttrHTML('id', $id);
		$attrs[] = self::CreateAttrHTML('type', $type);
		$attrs[] = self::CreateAttrHTML('value', $value);
		if ($style) {
			if (is_array($style)) {
				$style = implode(';', $style);
				if ($style) {
					$style .= ';';
				}
			}
			
			if ($style) {
				$attrs[] = self::CreateAttrHTML('style', $style);
			}
		}
		
		$attrs = implode(' ', $attrs);
		
		if ($attrsExtras) {
			if (is_array($attrsExtras)) {
				$attrsExtras = implode(' ', $attrsExtras);
			}
			
			$attrs .= ' '.$attrsExtras;
		}
		
		return  "<input $attrs>";
	}
	
	public static function GetValueHTML($nameParam, $valueDefault='', $tag='div', $classValue='', $styleExtra=''){
		$scope = self::GetScopeTmpl();
		if (!$scope) {
			return $valueDefault;
		}
		
		$value = $scope->getParam($nameParam, $valueDefault);
		
		if (!$classValue) {
			$classValue = self::CLASS_REP_VALUE;
		}
		
		$classValue = $scope->getClassForValue($nameParam, $classValue, $value);
		
		$attrs = array();
		if ($classValue) {
			$attrs[] = self::CreateAttrHTML('class', $classValue);
		}
		
		$style = $scope->getStyleForValue($nameParam);
		if ($styleExtra) {
			if ($style) {
				$posPuntoComa = strrpos($style, ";");
				if (($posPuntoComa === false) || ($posPuntoComa > 1 && $posPuntoComa != strlen($style)-1)) {
					$style .= ';';
				}
			}
			elseif ($style === null) {
				$style = '';
			}
			
			$style .= $styleExtra;
		}
		if ($style) {
			$attrs[] = self::CreateAttrHTML('style', $style);
		}
		
		$attrExtrasForValue = $scope->getAttrExtrasForValue($nameParam);
		if ($attrExtrasForValue) {
			$attrs[] = $attrExtrasForValue;
		}
		
		$value = $scope->renderValue($value, $nameParam);
		if ($value === null) {
			$value = '';
		}
		elseif ($value && is_string($value)){
			Exj::TrasferCharsEncodeISOToUTF8($value);
		}
		
		$html = self::CreateTagStart($tag, $attrs);
		$html .= $value;
		$html .= self::CreateTagEnd($tag);
		
		return $html;
	}
	
	/**
	 * overwrite. Renderiza el valor para presentar en el reporte
	 *
	 * @param string $value
	 * @param string $nameParam
	 * @return string
	 */
	protected function renderValue($value, $nameParam){
		return $value;
	}
	
	private $_pageNum = null;
	private $_positionItem = null;
	private $_numItemsForProcess = 0;
	
	public static function IsFirstPage(){
		$scope = self::GetScopeTmpl();
		if (!$scope) {
			return false;
		}
		
		return $scope->isFirstPag();
	}
	
	/**
	 * Crea una etiqueta HTML
	 *
	 * @param string $tag
	 * @param mixed $attrs Puede ser string o un array
	 * @return string
	 */
	public static function CreateTagStart($tag, $attrs=''){
		$htmlTag = "<$tag";
		if ($attrs) {
			if (is_array($attrs)) {
				$attrs = implode(" ", $attrs);
			}
			
			$attrs = trim($attrs);
			if ($attrs) {
				$htmlTag .= " $attrs";
			}
		}
		$htmlTag .= ">";
		
		return $htmlTag;
	}
	
	public static function CreateTagEnd($tag){
		return "</$tag>";
	}
	
	public static function GetListHTMLFromListModel(){
		$scope = self::GetScopeTmpl();
		if (!$scope) {
			return '';
		}
		
		$headers = $scope->getHeadersFromListModel();
		if (!$headers || count($headers) == 0) {
			return '';
		}
		
		$isEnableHeadersList = $scope->isEnableHeadersList();
		
		$html = self::GetHTMLTableStart('class="vu-rep-ct-datos" cellspacing="0" cellpadding="0"');
		
		$attrTRH = '';
		if (!$isEnableHeadersList) {
			$attrTRH = self::CreateAttrHTML('style', "height:0");
		}
		
		$html .= self::CreateTagStart('thead');
		$html .= self::CreateTagStart('tr', $attrTRH);
		
		foreach ($headers as $header) {
		//	echo "header:<br/>";
		//	print_r($header);
			
			$dataIndex = $header->dataIndex;
		
			$align = '';
			if (isset($header->align)) {
				$align = $header->align;
			}
			
			$width = self::ConvertMedidaToHTML($header->widthMM);
			$valueHeader = '';
			if (isset($header->header)) {
				$valueHeader = $header->header;
			}
			
			$classValue = self::CLASS_REP_HVALUE;
			$classValue = $scope->getClassForValue('h_'.$dataIndex, $classValue, $valueHeader);
			
			$stylesTHH = array();
			$stylesTHH[] = "width:$width";
			if (!$isEnableHeadersList) {
				$stylesTHH[] = "background-color: white";
				$stylesTHH[] = "border: 0 none";
			}
			
			$attrTH = self::CreateAttrHTML('style', $stylesTHH);
			if ($align) {
				$attrTH .= self::CreateAttrHTML('align', $align);
			}
			
			$html .= self::CreateTagStart('th', $attrTH);
			
			if ($isEnableHeadersList) {
				
				if ($valueHeader) {
					Exj::TrasferCharsEncodeISOToUTF8($valueHeader);
				}
				
				$html .= self::GetDivStart($classValue);
				$html .= $valueHeader;
				$html .= self::GetDivEnd();			
			}
			else {
				$html .= '';
			}
			
			$html .= self::CreateTagEnd('th');
		}
		$html .= self::CreateTagEnd('tr');
		$html .= self::CreateTagEnd('thead');
		
		$html .= self::CreateTagStart('tbody');
		
		$items = $scope->getItemsListModel();
		
		
		$fieldsListModel = $scope->getFieldsListModel();
		
		$MaxItemsForPage = $scope->calcMaxItemsForPage();
		
		
		
		if ($scope->_pageNum === null) {
			$scope->_pageNum = 1;
		}
		if ($scope->_positionItem === null) {
			$scope->_positionItem = 0;
			$scope->_numItemsForProcess = count($items);
		}
		else {
			$scope->_numItemsForProcess = count($items) - $scope->_positionItem;
		}
		
		$lastPositionItem = $scope->_positionItem;
		
		
		
		$pageRow = 0;
		foreach ($items as $item) {
			if ($lastPositionItem > 0) {
				$lastPositionItem -= 1;
				continue;
			}
			
			if ($pageRow >= $MaxItemsForPage) {
				$scope->_pageNum += 1;
				
				$scope->_numItemsForProcess = count($items) - $scope->_positionItem;
				if ($scope->_numItemsForProcess <= 0) {
					$scope->_pageNum -= 1; // offset
				}
				break;
			}
			
			$pageRow += 1;
			$scope->_positionItem += 1;
			$scope->_numItemsForProcess -= 1;
			
		//	echo "<br/>pageRow: $pageRow _pageNum: $scope->_pageNum _positionItem: $scope->_positionItem _numItemsForProcess: $scope->_numItemsForProcess";
			
			// cada fila
			$html .= self::CreateTagStart('tr');
			
			foreach ($headers as $header) {
				// cada columna
				$dataIndex = $header->dataIndex;
				$renderer = $header->renderer;
				$align = '';
				if (isset($header->align)) {
					$align = $header->align;
				}
				
				$value = '';
				if (isset($item->$dataIndex)) {
					$value = $item->$dataIndex;
					if ($value) {
						$value = self::RendererValue($value, $renderer);
						if ($value && is_string($value)) {
							$value = self::ParseCharset($value);
						}
					}
					
				}
				$classValue = self::CLASS_REP_VALUE;
				$classValue = $scope->getClassForValue($dataIndex, $classValue, $value);
				
				$html .= self::CreateTagStart('td');
				
				if ($align) {
					$styleValue = 'text-align: '.$align.';';
				}
				
				$styleValue .= $scope->getStyleForValue($dataIndex);
				
				$html .= self::GetDivStart($classValue, $styleValue, '', $scope->getAttrExtrasForValue($dataIndex));
				$html .= $scope->renderValueItemFromListModel($value, $dataIndex);
				$html .= self::GetDivEnd();	
				
				$html .=  self::CreateTagEnd('td');
			}
			
			$html .= self::CreateTagEnd('tr');
		}
		
		$html .= self::CreateTagEnd('tbody');
		$html .= self::GetHTMLTableEnd();
		
	//	echo " numItemsForProcess: " . $scope->_numItemsForProcess;
		
		return $html;
	}
	
	public function haveItemsForProcess(){
		if ($this->_numItemsForProcess === null) {
			return false;
		}
		
		if ($this->_numItemsForProcess <= 0) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Lectura de alturas de la lista, las alturas son dadas por milímetros
	 *
	 * @param int $heightTitlesPage Altura de los titulos de la lista
	 * @param int $heightHeaderRow Altura de la cabecera de la lista
	 * @param int $heightRow Altura de cada fila o item de la lista
	 */
	protected function readHeightList(&$heightTitlesPage, &$heightHeaderRow, &$heightRow){
		
	}
	
	public function calcMaxItemsForPage(){
		$maxItemsForPage = 39;
		
		$heightContentPage = $this->_getHeightContentPage();
		$isEnableHeadersList = $this->isEnableHeadersList();
		$isFirstPage = $this->isFirstPag();
		
	//	echo "<br/>heightContentPage: $heightContentPage";
		
		if ($heightContentPage == self::SIZE_AUTO) {
			$this->readOffsetItemsForPage($this->_offset_items_page, $isFirstPage, $isEnableHeadersList);
			if ($this->_offset_items_page != 0) {
				$maxItemsForPage += $this->_offset_items_page;
			}
			
			return $maxItemsForPage;
		}
		
		$heightTitlesPage = 20;
		$heightHeaderRow = 11;
		$heightRow = 5.3;
		$this->readHeightList($heightTitlesPage, $heightHeaderRow, $heightRow);
		
		if ($isEnableHeadersList) {
			$heightContentPage -= $heightHeaderRow;
		}
		if ($isFirstPage) {
			$heightContentPage -= $heightTitlesPage;
		}
		
		$maxItemsForPage = ($heightContentPage / $heightRow) - 0.21;
		$maxItemsForPage = round($maxItemsForPage, 0);
		
		
		$this->readOffsetItemsForPage($this->_offset_items_page, $isFirstPage, $isEnableHeadersList);
		if ($this->_offset_items_page != 0) {
			$maxItemsForPage += $this->_offset_items_page;
		}
		
	//	echo "<br/>maxItemsForPage: $maxItemsForPage";
		
		return $maxItemsForPage;
	}
	
	/**
	 * Lectura de compesación de items por página
	 *
	 * @param int $offsetItemsForPage Por referencia
	 * @param bool $isFirstPage Indica si es la primera página
	 * @param bool $isEnableHeadersList Indica si esta activo si se presenta las cabeceras lista
	 */
	protected function readOffsetItemsForPage(&$offsetItemsForPage, $isFirstPage, $isEnableHeadersList){
		
	}
	
	private $_enableHeadersOnlyFirstPag = false;
	
	public function isEnableHeadersList(){
		if (!$this->_enableHeadersOnlyFirstPag) {
			return true;
		}
		
		if ($this->_enableHeadersOnlyFirstPag && $this->isFirstPag()) {
			return true;
		}
		
		return false;
	}
	
	public function enableHeadersOnlyFirstPag($enable = true){
		$this->_enableHeadersOnlyFirstPag = $enable;
	}
	
	public function isFirstPag(){
		if ($this->_pageNum === null || $this->_pageNum <= 1) {
			return true;
		}
		
		return false;
	}
	
	public function getNumItemsForProcess(){
		return $this->_numItemsForProcess;
	}
	
	

	public static function RendererValue($value, $rendererUI){
		if ($value === null) {
			$value = '';
		}
		
		if (!$rendererUI || !$value) {
			return $value;
		}
		
		switch ($rendererUI) {
			case 'Exj.rendererFormatDateTime':
				$value = ExjDate::ConvertToDateTimeDisplay2($value);
			break;
			
			case 'Exj.rendererFormatDate';
				$value = ExjDate::ConvertToDateDisplay2($value);
			break;
			
			case 'Exj.renderDecimal2':
				$value = ExjUtil::RenderDecimal($value, 2);
			break;
		}

		return $value;
	}
	
	/**
	 * Renderiza el valor por cada item desde list Model
	 *
	 * @param string $value
	 * @param string $dataIndex
	 * @return string
	 */
	protected function renderValueItemFromListModel($value, $dataIndex){
		return $value;
	}
	
	public static function CreateAttrHTML($key, $value){
		if (is_array($value)) {
			$value = implode(";", $value);
		}
		return ' '.$key . '=' . '"' . $value . '"';
	}
	
	public static function GetHTMLTableStart($attrs=''){
		return "<table $attrs>";
	}
	public static function GetHTMLTableEnd(){
		return '</table>';
	}
	
	protected function getStyleForValue($nameParam){
		return '';
	}
	
	/**
	 * overwrite. Obtiene el atributo class para el valor HTML
	 *
	 * @param string $nameParam
	 * @param string $classValue Valor por defecto: vu-rep-value
	 * @param string $value Valor que se presentaría
	 * @return string
	 */
	protected function getClassForValue($nameParam, $classValue, $value){
		return $classValue;
	}
	
	/**
	 * overwrite. Obtiene atributos extras para el valor HTML
	 *
	 * @param string $nameParam
	 * @return string
	 */
	protected function getAttrExtrasForValue($nameParam){
		return '';
	}
	
	public static function PrintValueHTML($nameParam, $valueDefault='', $tag='div'){
		$valueHTML = self::GetValueHTML($nameParam, $valueDefault, $tag);
		if (!$valueHTML) {
			return false;
		}
		
		echo $valueHTML;
		
		return true;
	}
	
	/**
	 * Imprime en consola valor html para posición absoluta
	 *
	 * @param string $nameParam
	 * @param int $left
	 * @param int $top
	 * @param int $width
	 * @param string $valueDefault
	 * @return bool
	 */
	public static function PrintValueHTMLAbs($nameParam, $left, $top, $width='auto', $valueDefault='', $styleExtra=''){
		$valueHTML = self::GetValueHTMLAbs($nameParam, $left, $top, $width, $valueDefault, $styleExtra);
		if (!$valueHTML) {
			return false;
		}
		
		echo $valueHTML;
		
		return true;
	}
	
	
	public function setParam($nameParam, $value){
		if (!$this->_params) {
			$this->_params = new stdClass();
		}
		
		$this->_params->$nameParam = $value;
	}
	
	public function setParams($params){
		$this->_params = $params;
	}
	
	public function getParam($nameParam, $valueDefault=''){
		if (!$this->_params || !isset($this->_params->$nameParam)) {
			return $valueDefault;
		}
		
		return $this->_params->$nameParam;
	}
	
	public static function GetDivStart($class='', $style='', $id='', $attrExtras=''){
		$divStart = '<div';

		if ($id) {
			$divStart .= ' id="' . $id . '"' ;
		}
		
		if ($class) {
			$divStart .= ' class="' . $class . '"' ;
		}
		
		if ($style) {
			$divStart .= ' style="' . $style . '"' ;
		}
		
		if ($attrExtras) {
			$divStart .= ' '.$attrExtras;
		}
		
		$divStart .= '>';
		
		return $divStart;
	}
	
	public static function GetDivEnd(){
		return '</div>';
	}
	
	protected function configTemplate(&$tpl){
		$tpl = self::GetDivStart('vu-rep-pagina', '', '', self::TPL_KEY_ATTR_PAG).
					self::GetDivStart('vu-rep-pagina-ct', '', '', self::TPL_KEY_ATTR_PAG_CONTENT).
						self::TPL_KEY_CONTENTHTML.
					self::GetDivEnd().
			   self::GetDivEnd();
	}

	/**
	 * Configuración de márgenes de la página, las medidas son dadas en milimetros
	 *
	 * @param int $marginTop
	 * @param int $marginLeft
	 * @param int $marginBottom
	 * @param int $marginRight
	 */
	protected function configMargins(&$marginTop, &$marginLeft, &$marginBottom, &$marginRight){
		/*
		$marginTop = 15;
		$marginLeft = 15;
		$marginBottom = 3;
		$marginRight = 3;
		*/
	}
	
	public function clearMargins(){
		$this->_marginBottom = 0;
		$this->_marginLeft = 0;
		$this->_marginRight = 0;
		$this->_marginTop = 0;
	}

	/**
	 * Obtiene el contenido de la página en HTML
	 *
	 */
	protected function getContentPageHTML(){
		$this->_isEmptyContentHTML = true;
		
		return '';
	}

	
	public static function ConvertMedidaToHTML($medida){
		
		if (!$medida || $medida == self::SIZE_AUTO || $medida == 'inherit') {
			return $medida;
		}
		return $medida . self::MEDIDA_HTML;
	}
	
	private function _renderAttrPage(){
		// alto y ancho de la página
		
		$stylesPag = array();
		$stylesPag[] = 'height:'.self::ConvertMedidaToHTML($this->_heightPag);
		$stylesPag[] = 'width:'.self::ConvertMedidaToHTML($this->_widthPag);
		
		$stylesPag = implode("; ", $stylesPag);
		$stylesPag .= ';';
		
		$attrStylePag = 'style="' . $stylesPag . '"';
		
		return $attrStylePag;
	}
	
	private function _getHeightContentPage(){
		$heightContent = $this->_heightPag;
		
		if (is_numeric($this->_heightPag) && $this->_heightPag) {
			$heightContent = $this->_heightPag - $this->_marginBottom - $this->_marginTop;
		}
		
		if ($heightContent == 0) {
			$heightContent = self::SIZE_AUTO;
		}
		
		return $heightContent;
	}
	
	/**
	 * Obtiene el valor numérico de la altura del contenido del reporte
	 *
	 * @return int
	 */
	public function getValueHeightContentPage(){
		$heightContentPage = $this->_getHeightContentPage();
		
		if ($heightContentPage == self::SIZE_AUTO) {
			$heightContentPage = $this->getHeightPagForSizeAuto();
			$heightContentPage -= $this->_marginBottom;
			$heightContentPage -= $this->_marginTop;
		}
		
		return $heightContentPage;
	}
	
	/**
	 * Obtiene la altura del contenido del reporte segun el nro de impresiones del mismo 
	 *
	 */
	public function getValueHeigthNumPrintContent(){
		$height = $this->getValueHeightContentPage();
		
		if ($this->_maxPrintContent <= 1) {
			return $height;
		}
		
		$height = $height / $this->_maxPrintContent;
		/*
		if ($this->_heightSepContents > 0) {
			$height += $this->_heightSepContents;
		}
		*/
		
		$offsetHeigth = $this->getOffsetHeigthNumPrintContent($this->getNumPrintContent());
		if ($offsetHeigth != 0) {
			$height += $offsetHeigth;
		}
		
		$height = round($height, 2);
		
		return $height;
	}
	
	protected function getHeightPagForSizeAuto(){
		return 296; // por defecto alto de pagina A4 menos 1
	}
	
	
	private function _getWidthContentPage(){
		$widthContent = $this->_widthPag;
		
		$widthContent -= $this->_marginLeft;
		$widthContent -= $this->_marginRight;
		
		return $widthContent;
	}

	private function _renderAttrPageContent(){
		$stylesPag = array();
		
		$heightContent = $this->_getHeightContentPage();
		
		$stylesPag[] = 'height:'.self::ConvertMedidaToHTML($heightContent);
		
		if (!$this->_marginTop && !$this->_marginLeft && !$this->_marginRight) {
			$stylesPag[] = 'margin: 0';
		}
		else {
			$mTop = self::ConvertMedidaToHTML($this->_marginTop);
			$mLeft = self::ConvertMedidaToHTML($this->_marginLeft);
			$mRight = self::ConvertMedidaToHTML($this->_marginRight);
			
			$stylesPag[] = 'margin:' . " $mTop $mRight 0 $mLeft";
		}
		
		$stylesPag = implode("; ", $stylesPag);
		$stylesPag .= ';';
		// echo "stylesPag: $stylesPag";
		
		$attrStylePag = 'style="' . $stylesPag . '"';
		
		return $attrStylePag;
	}
	
	private function _parseAttrsPage(){
		$html = str_replace(self::TPL_KEY_ATTR_PAG, $this->_renderAttrPage(), $this->_tpl);
		$html = str_replace(self::TPL_KEY_ATTR_PAG_CONTENT, $this->_renderAttrPageContent(), $html);
		
		return $html;
	}
	
	public function printError($msg){
		Exj::TrasferCharsEncodeISOToUTF8($msg);
		echo '<p style="color:red;"><b>ERROR</b>: '. $msg . '</p>';
	}
	
	
	public function getNumPrintContent(){
		return $this->_nPrintContent;
	}
	
	private $_onlyFirstPrintContentOnPreview = false;
	
	public function enableFirstPrintContentOnPreview($enable = true){
		$this->_onlyFirstPrintContentOnPreview = $enable;
	}
	
	private function _printContent(){
		$this->_nPrintContent += 1;
		
		if ($this->_onlyFirstPrintContentOnPreview) {
			if ($this->isPreview() && $this->_nPrintContent > 1) {
				return;
			}
		}
		
		if ($this->_nameTmplContent) {
			$this->_includeFileTmpl();
		}
		
		if (!$this->_isEmptyContentHTML) {
			echo $this->getContentPageHTML();
		}
		
	}
	
	/**
	 * overwrite. Configuración del número de impresiones del contenido del reporte
	 *
	 * @param int $maxPrintContent
	 * @param int $heightSepContents Medida milímetros
	 */
	protected function configNumPrintContent(&$maxPrintContent, &$heightSepContents){
		$maxPrintContent = 1;
		$heightSepContents = 0;
	}
	
	public function setNumPrintContent($maxPrintContent, $heightSepContents=0){
		$this->_maxPrintContent = $maxPrintContent;
		$this->_heightSepContents = $heightSepContents;
	}
	
	
	protected function getSeparatorContents(){
		$style = 'height:'. ($this->_heightSepContents ? self::ConvertMedidaToHTML($this->_heightSepContents):0);
		$style .= ';';
		
		$sep = self::GetDivStart('', $style);
		// $sep .= 'xxx';
		$sep .= self::GetDivEnd();
		
		return self::ENDLINE . $sep . self::ENDLINE;
	}
	
	public static function GetPathCSS($component, $nameFileCSS){
		return "components/$component/views/tmpl/css/$nameFileCSS.css";
	}
	
	public function printHTML(){
		$html = $this->_parseAttrsPage();
		$posContent = strpos($html, self::TPL_KEY_CONTENTHTML);
		if ($posContent === false) {
			$this->printError("No se encontró keyContent: " . self::TPL_KEY_CONTENTHTML);
			return false;
		}
		
		$htmlStart = substr($html, 0, $posContent);
		$htmlEnd = substr($html, $posContent+ strlen(self::TPL_KEY_CONTENTHTML));
		
		echo $htmlStart;

		for ($i=0 ; $i < $this->_maxPrintContent; $i++){
			if ($i >= 1 && $i < $this->_maxPrintContent) {
				echo $this->getSeparatorContents();
			}
			
			$this->_printContent();
		}
				
		echo $htmlEnd;
		
		
		$outPrint = $this->getParamBaseReportOutPrint();
		$dataRpt = $this->getParamBaseReportDataRpt();
		
		$this->afterPrintHTML($outPrint, $dataRpt);
		if ($outPrint) {
			$this->onPrintReport($dataRpt);
		}
		
		return true;
	}
	
	/**
	 * overwrite. Cuando se imprime el reporte
	 *
	 * @param object $dataRpt
	 */
	protected function onPrintReport($dataRpt){
		
	}
	
	/**
	 * overwrite. Después de imprimir HTML
	 *
	 * @param bool $outPrint
	 * @param object $dataRpt
	 */
	protected function afterPrintHTML($outPrint, $dataRpt){
		
	}
	
	public function toHTML(){
		ob_start();
		$this->printHTML();

		return ob_get_clean();
	}
	
}


?>