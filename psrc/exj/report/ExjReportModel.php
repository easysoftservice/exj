<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para modelo de Reportes. Los modelos de reportes deben heredar de esta clase.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[componente]/models/[componente].report.model.php
 * Nombrado de la clase: Debe tener el formato: class App[Componente]ReportModel extends ExjReportModel
 * Se debe sobre-escribir los métodos: reportInit, reportRegisterCols, reportLoadItems, si se desea dar formato a un valor del reporte se debe sobrescribir el método: rendererColValue.
 * Los formatos soportados para la generación de archivos de reporte son: PDF, Excel 95 y Excel 97 (formato comprimido).
 * Al generar los archivos excel, estos son generados con dos imagenes en la parte superior de la hoja de excel.
 * Para generar el archivo, la hoja puede ser fijada en forma horizontal o vertical, con los métodos: fixPageHorizontal(), fixPageVertical() respectivamente.
 */
class ExjReportModel {
	const CREATOR_DOCUMENT = " EXJ; GYMCloud.";
	
	const IMAGE_HEADER_LEFT = 'header_left.png';
	const IMAGE_HEADER_RIGTH = 'header_rigth.png';
	
	const STYLE_ALIGN_HORIZONTAL_RIGHT = 1;
	const STYLE_ALIGN_HORIZONTAL_CENTER = 2;
	const STYLE_ALIGN_HORIZONTAL_LEFT = 3;
	const DATAINDEX_ORD='_ord';
	const HEIGHT_HEADER_IMAGE = 87;
	
	/**
	 * Fin de Línea
	 *
	 */
	const EOF_LINE = "\n";
	
    public $id;
    
	private $_title='';
	private $_titlePage='';
	
	private $_cols = array(), $_fields = array();
	private $_fileName;
	
	private $_rowsSummary = null, $_filasSubTotals = null, $_cellsSubTotales = null;
	
	private $_data=null, $_items=null;
	private $_criterias = null;
	private $_fullPathFileSaved = '';
	private $_extensionFileSaved = '';
	
	private $_marginTop = 0.75;
	private $_marginBottom = 0.75;
	private $_marginLeft = 0.7;
	private $_marginRight = 0.7;
	
	private $_heightHeader = 0.3;
	private $_heightFooter = 0.3;
	private $_heightHeaderImage = 87;
	private $_showImagesInHeader = true;
	private $_headersFontSize = 8;

	private $_hiddenLogoLeft = false;
	private $_hiddenLogoRight = false;
	
	private $_nameCriteriaModel='';
	private $_nameComponentCriteria='';
	private $_onlyRequiredCriteria='';
	private $_functionsRenderers=null;
	
	/**
	 * Presenta o no lineas en el pdf
	 *
	 * @var bool Defecto null, se setea auto
	 */
	private $_showGridLinesForPDF=null;
	
	private $_colsShow = null;
	private $_dataCellsTitles = null;
	
	
	/**
	 * Tamaño de página cuando se trata de paginación
	 *
	 * @var int
	 */
	public $pageSize = 30;
	public $sizeFontDetail = 6;
	public $hiddenSheetHeader = false;
	public $hiddenSheetFooter = false;
	public $sheetTextFooter=null;
	public $sheetTextHeader= null;
	
	/**
	 * Presenta o no el borde de los detalles
	 *
	 * @var bool Si es null se establece como auto
	 */
	public $showBorderDetail = null;
	
	
	
	/**
	 * Presenta o no la cabecera
	 *
	 * @var bool
	 */
	public $displayHeaders = true;
	
	private $_objPHPExcel;
	private $_sheetIndex;
	private $_hFile;
	private $_numFilaActual;
	private $_params, $_format='';
	private $_paramsCriteria = null;
	private $_isOverwrite_reportDetailBefore = true;
	
	private $_conditionSumSubTotal = null;
	private $_response=null;
	private $_hideHeaders = false;
	private $_autoAlignCols = true;
	private $_showHeadersDetail = true;
	private $_addColOrder = false;
	private $_hiddenAllHeaderPage = false;
	private $_fixSizeFontToDetail = true;
	
	/**
	 * Constructor del modelo de reportes
	 *
	 * @param string $format
	 * @param string $colsShow
	 */
	public function __construct($format, $colsShow=null){
		if (!$format) {
			$format = ExjImportModel::REPORT_FORMAT_EXCELXLSX;
			$this->_extensionFileSaved = 'xlsx';
		}
		
		$this->_format = $format;
		
		if ($colsShow && is_array($colsShow)) {
			$this->_colsShow = $colsShow;
		}
		
		$this->displayHeaders = true;
		$this->_data = null;
		$this->_items = null;
		$this->_params = null;
		
		$this->_fileName = '';
		$this->_numFilaActual = 1;

		Exj::IncludePHPExcel();
		
		$this->_objPHPExcel = new PHPExcel();
		$this->_sheetIndex=0;
		$this->_objPHPExcel->setActiveSheetIndex($this->_sheetIndex);
		
		$this->fixPageVertical()->fixPaperSizeA4();
		
		$this->_hFile = null;
		$this->_showImagesInHeader = $this->isSupportedImagesInHeader();

		$this->reportInit();
		$this->_registerFirstCols();
		$this->reportRegisterCols();
		$this->reportRegisterCriteria();
		
		
		
		if ($this->_showImagesInHeader && $this->_heightHeaderImage) {
			$this->_marginTop = self::ConvertPixelToCm($this->_heightHeaderImage + 9);
//			echo "<br/>this->_marginTop: $this->_marginTop this->_heightHeaderImage: $this->_heightHeaderImage";
		}
		
		$this->reportPageMargins(
			$this->_marginTop,
			$this->_marginBottom,
			$this->_marginLeft,
			$this->_marginRight
		);

		$this->reportPageMarginsHeaderFooter(
			$this->_heightHeader, $this->_heightFooter
		);
		$this->_fixPageMargins();
		
		$this->_fixWidthCols();
		
		if ($this->showBorderDetail === null) {
			/*
			if ($this->isFormatPDF()) {
				$this->showBorderDetail = false;
			}
			else {
				$this->showBorderDetail = true;
			}
			*/
			$this->showBorderDetail = true;
		}
		
		if ($this->_showGridLinesForPDF === null) {
			$this->_showGridLinesForPDF = false;
		//	$this->_showGridLinesForPDF = $this->showBorderDetail;
		}
		
		// estilos por defecto en el sheet
		// $this->getActiveSheet()->getDefaultStyle()->getFont()->setSize(8);
	}

	public function setShowImagesInHeader($value=true){
		$this->_showImagesInHeader = $value;
		return $this;
	}

	public function setShowGridLinesForPDF($showLines=true){
		$this->_showGridLinesForPDF = $showLines;
		return $this;
	}
	
	/**
	 * Setea las columnas a presentar
	 *
	 * @param array $cols
	 */
	public function setColsShow($cols){
		$this->_colsShow = $cols;
		return $this;
	}

	/**
	 * Obitiene column a presentar
	 *
	 * @param string $dataIndex
	 * @return object|null
	 */
	public function getColShow($dataIndex){
		$colFound = null;
		
		if (!$this->_colsShow) {
			return $colFound;
		}		
		
		foreach ($this->_colsShow as $colShow) {
			if (!isset($colShow->dataIndex) || !$colShow->dataIndex) {
				continue;
			}
			
			if ($colShow->dataIndex == $dataIndex) {
				$colFound = $colShow;
				break;	
			}
		}
		
		return $colFound;
	}

	/**
	 * Obtiene respuesta para UI
	 *
	 * @return ExjResponse
	 */
	public function &getResponse(){
		if (!$this->_response) {
			$this->_response = new ExjResponse();
		}
		
		return $this->_response;
	}

	/**
	 * Oculta toda la información de la cabecera de la página
	 *
	 * @param bool $hidden
	 * @param bool $hiddenLogosHeaderPage
	 */
	public function hiddenAllHeaderPage($hidden=true, $hiddenLogosHeaderPage=true){
		$this->_hiddenAllHeaderPage = $hidden;
		$this->hiddenLogos($hiddenLogosHeaderPage);
	}
	
	public function disableAutoAlignCols($disable = true){
		$this->_autoAlignCols = !$disable;
	}
	
	public function disableShowHeadersDetail($disable = true){
		$this->_showHeadersDetail = !$disable;
		return $this;
	}
	
	public function disableFixSizeFontDetail($disable = true){
		$this->_fixSizeFontToDetail = !$disable;
	}
	
	public function haveErrorResponse(){
		return $this->getResponse()->haveMsgError();
	}
	
	public function isSupportedImagesInHeader(){
		if ($this->isFormatExcelXLSX()) {
			return true;
		}
		
		return false;
	}
	
	protected function reportPageMargins(&$top, &$bottom, &$left, &$right){
		
	}
	protected function reportPageMarginsHeaderFooter(&$heightHeader, &$heightFooter){
		
	}
	
	static function ConvertPixelToCm($sizePixel){
		return round(($sizePixel*1.30)/100, 3);
	}

	static function ConvertCmToPixel($sizeCM){
		return round(($sizeCM * 8.1), 0);
	}
	
	static function ConvertWidthColToPixel($sizeCol){
		return round(($sizeCol * 130)/18.30, 0);
	}
	public function isFormatPDF(){
		return ($this->_format == ExjImportModel::REPORT_FORMAT_PDF);
	}
	public function isFormatExcelXLS(){
		return ($this->_format == ExjImportModel::REPORT_FORMAT_EXCELXLS);
	}
	public function isFormatExcelXLSX(){
		return ($this->_format == ExjImportModel::REPORT_FORMAT_EXCELXLSX);
	}
	public function isFormatHTML(){
		return ($this->_format == ExjImportModel::REPORT_FORMAT_HTML);
	}

	public function isFormatXML(){
		return ($this->_format == ExjImportModel::REPORT_FORMAT_XML);
	}
	
	public function canViewFileInUI(){
		if ($this->isFormatPDF()) {
			return true;
		}
		if ($this->isFormatHTML()) {
			return true;
		}
		
		return false;
	}
	
	public function getParamId($name='id'){
		$value = $this->getParam($name, 0);
		if ($value && !is_numeric($value)) {
			$value = 0;
		}
		
		$value = intval($value);
		if (!$value) {
			$this->_setError("Parámetro <b>$name</b> es requerido!");
		}
		
		return $value;
	}
	
	private function _setError($msg){
		global $exj;
		$exj->setErrorValidating("ERROR EN REPORTE: $this->_title<br/>$msg");
	}
	
	public function haveError(){
		global $exj;
		if (Exj::GetError()->haveError()){
			return true;
		}
		
		if ($this->haveErrorResponse()) {
			$exj->setErrorValidating($this->getResponse()->getErrorMsg());
			return true;
		}
		
		return false;
	}
	
	public function getParams(){
		return $this->_params;
	}
	
	public function getParam($name, $defaultValue=''){
		if ($this->_params) {
			if (isset($this->_params->$name)) {
				return $this->_params->$name;
			}
		}
		
		if (isset($this->$name)) {
			return $this->$name;
		}
		
		return $defaultValue;
	}
	
	public function setParam($name, $value){
		if (!$this->_params) {
			$this->_params = new stdClass();
		}
		
		$this->_params->$name = $value;
		return $this;
	}
	
	
	private function _fixPageMargins(){
		if ($this->_isOrientationLANDSCAPE) {
			// HORIZONTAL
			$this->_marginLeft = 0.3;
			$this->_marginRight = 0.3;
		}
		else {
			$this->_marginLeft = 0.7;
			$this->_marginRight = 0.7;
		}
		
		// $pageMargins = new PHPExcel_Worksheet_PageMargins();
		$pageMargins = $this->getActiveSheet()->getPageMargins();
		
		$pageMargins->setHeader($this->_heightHeader);
		$pageMargins->setFooter($this->_heightFooter);
		
		$pageMargins->setTop($this->_marginTop);
		$pageMargins->setBottom($this->_marginBottom);
		$pageMargins->setLeft($this->_marginLeft);
		$pageMargins->setRight($this->_marginRight);
		
		// print_r($pageMargins);
		
		// $this->getActiveSheet()->setPageMargins($pageMargins);
		return $this;
	}
	
    /**
     * Seteo de configuración básica del modelo de reporte
     *
     * @param string $title
     * @param string $fileName
     */
	public function setConfig($title, $fileName='', $addNameFileDate = true){
		if ($fileName) {
			$this->_titlePage = $fileName;
		}
		else {
			$this->_titlePage = $title;
		}
		
		ExjTransferCharacters::encodeISOToUTF8($this->_titlePage);
	//	ExjTransferCharacters::decodeUTF8ToISO($this->_titlePage);
		
		if (strlen($title) > 31) {
			$title = substr($title, 0, 31);
			$title = trim($title);
			$title = trim($title, '.');
		}
		
		ExjTransferCharacters::encodeISOToUTF8($title);
	//	ExjTransferCharacters::decodeUTF8ToISO($title);
		$this->_title = $title;
		$this->setTitle($title);
		if (!$fileName) {
			$fileName = $this->_title;
		}
		
		$fileName = ExjText::_($fileName);
		$this->_clearCharsInvalid($fileName);
		
	//	ExjTransferCharacters::encodeISOToUTF8($fileName);
		ExjTransferCharacters::decodeUTF8ToISO($fileName);
		
		$this->_fileName = $fileName;
		if ($addNameFileDate) {
			$this->_fileName .= ' '. Exj::GetDateTime('%d-%m-%y');
		}

		return $this;		
	}
	
	public function getTitlePage(){
		return $this->_titlePage;
	}
	
	private function _registerFirstCols(){
		if ($this->_addColOrder) {
			$this->registerColOrder(self::DATAINDEX_ORD, 'ORD');
		}
	}
	
	/**
	 * overwrite. Inicio
	 *
	 */
	protected function reportInit(){
		
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	protected function reportRegisterCols(&$numFilaActual){
		
	}
	
	protected function reportRegisterCriteria(){
	}
	
	/**
	 * overwirted. Permite modificar el titulo del reporte, sino desea que se presente el titulo, setear el primer parámetro a vacio
	 *
	 * @param string $titleReport Por defecto es el titulo de la hoja de cálculo
	 * @param int $numFilaActual
	 */
	protected function reportTitle(&$titleReport, &$numFilaActual){
		
	}

	/**
	 * overwrited. Antes del detalle
	 *
	 * @param int $numFilaActual
	 * @param object $data
	 */
	protected function reportDetailBefore(&$numFilaActual, $data){
		$this->_isOverwrite_reportDetailBefore = false;
	}
	
	protected function reportHeadersDetail(&$numFilaActual, $items, $data=null){
		++$numFilaActual;
		$this->showHeadersDetail();
	}
	
	protected function reportCustomHeadersDetail(&$numFilaActual, $cols){
		return true;
	}
	
	/**
	 * Detalle de cada item
	 *
	 * @param int $numFilaActual
	 * @param object $data
	 * @param string $value
	 * @param string $posCell
	 * @param string $nameField
	 * @param object $dataItem
	 * @return bool
	 */
	protected function reportDetailItem(&$numFilaActual, $data, $value, $posCell, $nameField, $dataItem){
		$this->setValueCell($posCell, $value, $this->showBorderDetail);
		return true;
	}
	
	/**
	 * Después del detalle del reporte
	 *
	 * @param int $numFilaActual
	 * @param object $data
	 */
	protected function reportDetailAfter(&$numFilaActual, $data){
	}
	
	
	/**
	 * Envio de valores de la criteria al reporte, es llamado desde el controlador
	 *
	 * @param string $nameCriteria
	 * @param string $nameComponentCriteria
	 * @param bool $onlyRequired Por defecto false
	 * @return bool retornar false para evitar que se adicionen los valores de la criteria
	 */
	public function reportSetValuesCriteria(&$nameCriteria, &$nameComponentCriteria, &$onlyRequired)
	{
		
		return true;
	}
	
	public function fixValuesCriteria(
		$nameCriteria, $nameComponentCriteria, $onlyRequired=false)
	{
		$this->_nameCriteriaModel = $nameCriteria;
		$this->_nameComponentCriteria = $nameComponentCriteria;
		$this->_onlyRequiredCriteria = $onlyRequired;

		return $this;
	}

	/**
	 * Envio de valores de la criteria al reporte, esto es llamado automaticamente
	 *
	 * @param string $nameCriteriaModel
	 * @param string $nameComponentCriteria
	 * @param bool $onlyRequired Por defecto false
	 */
	protected function setValuesCriteria($nameCriteriaModel, $nameComponentCriteria='', $onlyRequired = false)
	{
		$ClassCriteria = Exj::GetNameClassCriteria($nameCriteriaModel);
		
		if (!class_exists($ClassCriteria)) {
			// echo "No existe la clase: $ClassCriteria<br/>Se va a incluir criteria nombre: $nameCriteriaModel del Componente: $nameComponentCriteria";
			global $exj;
			if (!class_exists($ClassCriteria)) {
				$exj->setErrorValidating(
					"No existe la clase criteria: $ClassCriteria<br/>Sobre-escriba el método reportSetValuesCriteria para establecer nombre de la criteria."
				);
				// echo "ERROR NO SE ENCONTRO CLASE CRITERIA";
				return false;
			}
		}
		
		// echo "<br/>setValuesCriteria. ClassCriteria: $ClassCriteria";
		
		$criteria = new $ClassCriteria(false);
		return $this->_showValuesCriteria($criteria, $onlyRequired);
	}
	
	/**
	 * Overwirted. Cambia el nombre del parámetro para mostrarlo en el reporte
	 *
	 * @param string $nameParam Valor por referencia
	 * @param mixed $valueField
	 * @return bool retornar false para evitar que se muestre este campo en el reporte
	 */
	protected function reportCriteriaNameParamValue(&$nameParam, $valueField)
	{	
		return true;
	}
	
	private function _showValuesCriteria(
		ExjCriteriaModel $criteria, $onlyRequired = false)
	{
		
		$criteria->bind($this->getParamsCriteria());
	// echo "<br>_showValuesCriteria. pc: ". print_r($this->getParamsCriteria(), true);
		
		$fields = $criteria->getFields();
		// echo "<br>_showValuesCriteria. fields: ". print_r($fields, true);
		
		$fieldsAdded = array();
		$valuesCellPrimary = array();
		$valuesCellSecundary = array();
		
		foreach ($fields as $fieldCriteria) {
			if ($onlyRequired && !$fieldCriteria->isRequired) {
				continue;
			}
			
			$nameField = $fieldCriteria->getName();
			if (in_array($nameField, $fieldsAdded)) {
				continue;
			}
			
			$valueField = $criteria->$nameField;
			$aliasField = strtoupper($criteria->getFieldAlias($nameField));
			
			if (!ExjCriteriaModel::IsSettedValue($valueField)) {
				continue;
			}

			if ($valueField === '' || $valueField===null) {
				continue;
			}

		//	echo "<br>aliasField: $aliasField valueField: $valueField";
			
			$nameParamValueCriteria = $nameField;
			if ($this->reportCriteriaNameParamValue($nameParamValueCriteria, $valueField) === false) {
				continue;
			}
			
			$isValueFromParams = false;
			if ($nameParamValueCriteria != $nameField) {
				$valueField = Exj::InstanceRequest()->getParamFromValuesCriteria(
					$nameParamValueCriteria
				);

				$isValueFromParams = true;
			//	echo "<br/>Leyendo desde paramsValuesCriteria. nameParamValueCriteria: $nameParamValueCriteria";
			}
			
			if ($valueField) {
				if ($fieldCriteria->isDateTime()) {
					$valueField = ExjDate::ConvertToDateTimeDisplay($valueField);
				}
				elseif ($fieldCriteria->isDate()) {
					$valueField = ExjDate::ConvertToDateDisplay($valueField);
				}
			}
			
			if ($fieldCriteria->isDate()) {
				if (isset($fieldCriteria->endDateField) && $fieldCriteria->endDateField) {
					$fieldIni = $nameField;
					$fieldEnd = $fieldCriteria->endDateField;
					
					$fieldsAdded[] = $fieldIni;
					$fieldsAdded[] = $fieldEnd;
					
					$valueEnd = $criteria->$fieldEnd;
					
					if (!$valueField && !$valueEnd) {
						continue;
					}
					
					$valueCell = '';
					if ($valueField) {
						$valueCell = $aliasField . ': '. $valueField;
					}
					
					if ($valueEnd) {
						if ($valueCell) {
							$valueCell .= ' - ';
						}
						$valueCell .= strtoupper($criteria->getFieldAlias($fieldEnd)) . ': '. ExjDate::ConvertToDateDisplay($valueEnd);
					}
					
					if ($fieldCriteria->isRequired) {
						$valuesCellPrimary[] = $valueCell;
					}
					else {
						$valuesCellSecundary[] = $valueCell;
					}
				
					continue;	
				}
			}
			
			// $fieldCriteria->isString()
			$valueCell = '';
			
			// echo "<br>nameField: $nameField aliasField: $aliasField valueField: $valueField";
			if ($this->rendererAliasValueCriteria($nameField, $aliasField, $valueField) === false) {
				continue;
			}

			$isFieldId = (strpos($nameField, 'id_')===0);
			
			if (!$valueField && $valueField !== '0') {
				if ($isFieldId) {
					continue;
				}

				if ($aliasField) {
					$valueCell = $this->concatValueCellLabelValue(
						$aliasField, strtoupper(ExjText::__('TODOS'))
					);
					// echo "<br>vacio field. $nameField = $valueField";
				}
			}
			elseif ($fieldCriteria->isInt()){
				if ($isFieldId) {
					// se trata de un id
					$valueParam = $valueField;
					if (!$isValueFromParams) {
						$nameParamValueCriteria = $nameField;
						if ($this->reportCriteriaNameParamValue($nameParamValueCriteria, $valueField) === false) {
							continue;
						}
					//	echo "<br/>ES ID. Leyendo desde paramsValuesCriteria. nameParamValueCriteria: $nameParamValueCriteria";
						$valueParam = Exj::InstanceRequest()->getParamFromValuesCriteria(
							$nameParamValueCriteria
						);
					}
					
					if ($valueParam) {
						$valueCell = $this->concatValueCellLabelValue(
							$aliasField, $valueParam
						);
					}
					/*
					else {
						echo "<br/> Campo ID: $nameField valor: $valueField";
					}
					*/
				}
				else {
					$valueCell = $this->concatValueCellLabelValue(
						$aliasField, $valueField
					);
				}
			}
			else {
				if (is_numeric($valueField)) {
					// echo "<br/>aliasField: $aliasField valueField: $valueField";
					$valueParamText = Exj::InstanceRequest()->getParamFromValuesCriteria(
						$nameParamValueCriteria
					);

					if ($valueParamText) {
						$valueField = $valueParamText;
					}
					// echo " valueParamText: $valueParamText";
				}
				
				$valueCell = $this->concatValueCellLabelValue(
					$aliasField, $valueField
				);
			}
			
			if ($valueCell) {
				if ($fieldCriteria->isRequired) {
					$valuesCellPrimary[] = $valueCell;
				}
				else {
					$valuesCellSecundary[] = $valueCell;
				}
				
				$fieldsAdded[] = $nameField;
			}
		} // for
		
		if (count($valuesCellPrimary) > 0) {
			$valuesCellPrimary = implode(' | ', $valuesCellPrimary);
			$valuesCellPrimary=$this->rendererValueCellCriteria($valuesCellPrimary);
			if ($valuesCellPrimary) {
				$cellsMerge = $this->setValueCellTextExpandFromIndex(
					$valuesCellPrimary, 1, false, true
				);
				
				$sizeFont = $this->getSizeFontCriteria(true);
				if ($sizeFont) {
					$this->setSizeFont($cellsMerge, $sizeFont);
				}
			}
		}
		else {
			$valuesCellPrimary = '';
		}
		
		if (count($valuesCellSecundary) > 0) {
			$valuesCellSecundary = implode(' | ', $valuesCellSecundary);
			$valuesCellSecundary=$this->rendererValueCellCriteria($valuesCellSecundary);
			if ($valuesCellSecundary) {
				$cellsMerge = $this->setValueCellTextExpandFromIndex(
					$valuesCellSecundary, 1, false
				);

				$sizeFont = $this->getSizeFontCriteria(false);
				if ($sizeFont) {
					$this->setSizeFont($cellsMerge, $sizeFont);
				}
			}
		}
		else {
			$valuesCellSecundary = null;
		}
		
		// echo "<br/>valuesCellPrimary: $valuesCellPrimary";
		// echo "<br/>valuesCellSecundary: $valuesCellSecundary";
		
		return $fieldsAdded;
	}

	protected function rendererValueCellCriteria($value){
		return $value;
	}

	protected function concatValueCellLabelValue($label, $value){
		$str = $label;
		if ($str) {
			$str .= ': ';
		}

		$str .= $value;
		return $str;
	}
	
	/**
	 * Retorna el tamaño de la letra para los filtros
	 *
	 * @param bool $isPrimary
	 * @return int
	 */
	protected function getSizeFontCriteria($isPrimary){
		return 9;
	}
	
	/**
	 * Renderiza el alias y/o valor que se presneta en el reporte
	 *
	 * @param string $nameField
	 * @param string $aliasField
	 * @param mixed $valueField
	 * @return bool false para no presentar el filtro
	 */
	protected function rendererAliasValueCriteria($nameField, &$aliasField, &$valueField){
		return true;
	}
		
	public function sheetWriteLine($text='', $bold=true, $addBorder=false){
		if (!$text) {
			$bold = false;
		}
		$this->setValueCellTitleFromIndex($text, 1, $addBorder, $bold);
	}
	public function setValueCellTitle2FromIndex($title, $indexCol=1, $addBorder=false, $bold=false, $alignCenter=true){
		$this->setValueCellTitleFromIndex(
			$title, $indexCol, $addBorder, $bold, $alignCenter
		);
	}
	public function setValueCellTitle3FromIndex($title, $indexCol=1, $addBorder=false, $bold=false, $alignCenter=false){
		$this->setValueCellTitleFromIndex(
			$title, $indexCol, $addBorder, $bold, $alignCenter
		);
	}
	
	/**
	 * Envia el valor a la columna indicada hace un merge de la fila
	 *
	 * @param string $title
	 * @param int $indexCol
	 * @param bool $addBorder
	 * @param bool $bold
	 * @param bool $alignCenter
	 * @return string cellsMerge las celdas q se hicieron merge
	 */
	public function setValueCellTitleFromIndex($title, $indexCol=1, $addBorder=false, $bold=true, $alignCenter=true)
	{
		$numColMax = count($this->_cols);
		// $numColMax += 1;
		
		if (!$title) {
			$addBorder = false;
		}
		
		$this->_numFilaActual += 1;
		$cellsMerge = $this->getPosRangeFilaFromIndex(
			$indexCol, $numColMax, $this->_numFilaActual
		);

		$this->mergeCells($cellsMerge);
		$numFilaActual = $this->_numFilaActual;
		$cellTitle = $this->getPosCellFromIndex($indexCol, $numFilaActual);
		if ($bold) {
			$this->setValueCellBold($cellTitle, $title, $addBorder);
		}
		else {
			$this->setValueCell($cellTitle, $title, $addBorder);
		}
		if ($addBorder) {
			// echo "<br/>cellsMerge: $cellsMerge";
			$this->setBorderFino($cellsMerge);
		}

		if ($alignCenter) {
			$this->setAlignmentCenter($cellTitle);
		}

		$this->afterSetValueCellTitle($cellTitle, $cellsMerge, $numFilaActual);

		// save titulos para luego poder dar comportamiento
		if (!$this->_dataCellsTitles) {
			$this->_dataCellsTitles = array();
		}
		$this->_dataCellsTitles[$numFilaActual] = $cellsMerge;
		
		// echo "<br>cellsMerge: $cellsMerge";
		
		return $cellsMerge;
	}

	public function getDataCellsTitles(){
		return $this->_dataCellsTitles;
	}

	protected function afterSetValueCellTitle($cellTitle, $cellsMerge, $numFila){
		return $this;
	}
	
	public function setValueCellTextExpandFromIndex($text, $indexCol=2, $addBorder=true, $bold=false){
		$this->_numFilaActual -= 1;
		$cellsMerge = $this->setValueCellTitleFromIndex(
			$text, $indexCol, $addBorder, $bold
		);

		$this->_numFilaActual += 1;
		
		return $cellsMerge;
	}
	
	public function setValueCellFromIndex($value, $indexCol=1, $addBorder=true, $addBold = true){
		$value = ExjText::_($value);
		$pos = $this->getPosCellFromIndex($indexCol, $this->_numFilaActual);
		if ($addBold) {
			$this->setValueCellBold($pos, $value, $addBorder);
		}
		else {
			$this->setValueCell($pos, $value, $addBorder);	
		}

		return $this;
	}

	public function hideHeaders($hideHeaders = true){
		$this->_hideHeaders = $hideHeaders;
	}
	
	public function showHeadersDetail($title=null, $numFilaActual=null){
		$cols = $this->getColumns();
		if (empty($cols)) {
			return false;
		}

		$numColMax = count($cols);
		if ($numFilaActual) {
			$this->_numFilaActual = intval($numFilaActual);
		}

		if ($title !== null) {
			$this->sheetWriteLine($title);
		}
		$this->_numFilaActual += 1;
		$lastNumFila = $this->_numFilaActual;
		
		if ($this->reportCustomHeadersDetail($this->_numFilaActual, $cols) === false) {
			return false;
		}
		
		if ($this->_hideHeaders) {
			return true;
		}
		
		/*
		if ($lastNumFila < $this->_numFilaActual) {
			$this->_numFilaActual += 1;
		}
		*/
		
		
		$indexCol = 1;
		foreach ($cols as $col){
			$cellCol = $this->getPosCellFromIndex($indexCol++);
			
			$this->setValueCellBold($cellCol, strtoupper(ExjText::_($col->header)));
			$this->setAlignmentCenter($cellCol);
			$this->setAlignmentVerticalCenter($cellCol);
		}

		
		$cellsHeaders = $this->getPosRangeFilaFromIndex(1, $numColMax, $numFilaActual);
		$this->applyStylesHeaders($cellsHeaders);
		
		return true;
	}
	
	
	
	/**
	 * Rango. Ej: C3:F3
	 *
	 * @param int $indexColStart
	 * @param int $indexColEnd
	 * @param int $indexFil
	 * @return string
	 */
	public function getPosRangeFilaFromIndex($indexColStart, $indexColEnd, $indexFil){
		$posRange = $this->getPosCellFromIndex($indexColStart, $indexFil);
		$posRange .= ':';
		$posRange .= $this->getPosCellFromIndex($indexColEnd, $indexFil);
		return $posRange;
	}

	/**
	 * Rango de columnas. Ej: C3:C6
	 *
	 * @param int $indexCol Indice de columna empesando desde 1
	 * @param int $indexRowEnd Indice de fila final empesando desde 1
	 * @param int $indexRowStart Indice de fila inicial empesando desde 1, si no se indica se toma el indice actual de la fila
	 * @return string
	 */
	public function getPosRangeColFromIndex($indexCol, $indexRowEnd, $indexRowStart = null){
		if (!$indexRowStart) {
			$indexRowStart = $this->_numFilaActual;
		}
		
		if ($indexRowStart > $indexRowEnd) {
			// esto seria un error pero se interpreta como un span
			$indexRowEnd += $indexRowStart;
		}
		
		$posRange = $this->getPosCellFromIndex($indexCol, $indexRowStart);
		$posRange .= ':';
		$posRange .= $this->getPosCellFromIndex($indexCol, $indexRowEnd);
		return $posRange;
	}
	
	/**
	 * Rango de celdas para un rowspan
	 *
	 * @param int $indexCol
	 * @param int $rowSpan Debe ser mayor a 1, si se indica un valor menor o igual a uno se setea 2
	 * @param int $indexRowStart
	 * @return string
	 */
	public function getPosRangeRowSpanFromIndex($indexCol, $rowSpan=2, $indexRowStart = null){
		if (!$indexRowStart) {
			$indexRowStart = $this->_numFilaActual;
		}
		
		if ($rowSpan <= 1) {
			$rowSpan = 2;
		}
		
		$indexRowEnd = $indexRowStart + $rowSpan -1;
		
		return $this->getPosRangeColFromIndex($indexCol, $indexRowEnd, $indexRowStart);
	}
	
	/**
	 * Posicion de una celda. Ej: A3
	 *
	 * @param int $indexCol
	 * @param int $indexFil
	 * @return string
	 */
	public function getPosCellFromIndex($indexCol, $indexFil=null){
		if (!$indexFil) {
			$indexFil = $this->_numFilaActual;
		}
		
		return $this->getPosColFromIndex($indexCol) . $indexFil;
	}
	/**
	 * Posicion de la Columna, Ej: C
	 *
	 * @param int $indexCol
	 * @return string
	 */
	public function getPosColFromIndex($indexCol){
		$indexCol -= 1;
		if ($indexCol < 0) {
			$indexCol = 0;
		}
		return chr(ord('A')+$indexCol);
	}
	
	public function &getActiveSheet(){
		$activeSheet = $this->_objPHPExcel->getActiveSheet();
		return $activeSheet;
	}
	
	/**
	 * Obtiene un celda de la coordenada indicada
	 *
	 * @param	string			$pCoordinate	Coordinate of the cell
	 * @throws	Exception
	 * @return	PHPExcel_Cell	Cell que ha sido encontrada, sino retorna null
	 */
	public function getCell($pCoordinate = 'A1'){
		return $this->getActiveSheet()->getCell($pCoordinate);
	}

	
	private function _isInRangeCell(PHPExcel_Cell $cell, $rangeCells){
		return $cell->isInRange($rangeCells);
	}
	
	/**
	 * Determina si la celda indicada esta en el rango
	 *
	 * @param string $pCoordinate
	 * @param string $rangeCells
	 * @return bool
	 */
	public function isInRangeCell($pCoordinate = 'A1', $rangeCells){
		$cell = $this->getCell($pCoordinate);
		if (!$cell) {
			$pCoordinate = strtoupper($pCoordinate);
			
			list($rangeStart,$rangeEnd) = PHPExcel_Cell::rangeBoundaries($rangeCells);
	
			// Translate properties
			list($myColumn, $myRow)	= sscanf($pCoordinate,'%[A-Z]%d');
			$myColumn	= PHPExcel_Cell::columnIndexFromString($myColumn);
	
			// Verify if cell is in range
			return (($rangeStart[0] <= $myColumn) && ($rangeEnd[0] >= $myColumn) &&
					($rangeStart[1] <= $myRow) && ($rangeEnd[1] >= $myRow)
				   );
		}
		
		return $this->_isInRangeCell($cell, $rangeCells);
	}
	
	
	private function _getMergeCells(PHPExcel_Worksheet $worksheet){
		return $worksheet->getMergeCells();
	}
	
	/**
	 * Retorna un array de las celdas merge
	 *
	 * @return array
	 */
	public function getMergeCells(){
		return $this->_getMergeCells($this->getActiveSheet());
	}
	
	/**
	 * Determina el la celda está en alguna celda merge
	 *
	 * @param string $pCoordinate Ejemplo: A1
	 * @return bool 
	 */
	public function isCellInMergeCells($pCoordinate){
		$mergeCells = $this->getMergeCells();
		if (!$mergeCells || count($mergeCells) == 0) {
			return false;
		}
		
	//	echo '<br/>' . __METHOD__  . " pCoordinate: $pCoordinate";
		
		$found = false;
		foreach ($mergeCells as $mergeCell) {
		//	echo "<br/> -> mergeCell: $mergeCell";
			if ($this->isInRangeCell($pCoordinate, $mergeCell)) {
				$found = true;
				break;
			}
		}
		
		return $found;
	}
	
	private function _setBreakByColumnAndRow(PHPExcel_Worksheet $worksheet, $numRow = null){
		if (!$numRow) {
			$numRow = $this->_numFilaActual;
		}
		
		if ($numRow <= 1) {
			return false;
		}
		
		return $worksheet->setBreakByColumnAndRow(0, $numRow, PHPExcel_Worksheet::BREAK_ROW);
	}
	
	public function setBreakPage($numRow = null){
		return $this->_setBreakByColumnAndRow($this->getActiveSheet(), $numRow);
	}
	
	public function showUtilTableCol(ExjUtilTableCol $col, &$indexCol, &$numFilaActual, $numColsTotal=30){
		$indexColEnd = 0;
		$indexRowEnd = 0;
		$rangeCellsMerge = '';
		
		$value = $col->value;
		$posCell = $this->getPosCellFromIndex($indexCol, $numFilaActual);
		
		// verificar si la celda a poner el valor ya no está merged
		$isOverflowIndexCol = false;
		while ($this->isCellInMergeCells($posCell)) {
			if ($indexCol >= $numColsTotal) {
				$isOverflowIndexCol = true;
				break;
			}
			
			$indexCol += 1;
			$posCell = $this->getPosCellFromIndex($indexCol, $numFilaActual);
		}
		
		if ($isOverflowIndexCol) {
			echo "<br/>ERROR. isOverflowIndexCol en: " . __METHOD__;
			return false;
		}
		
		$indexColStart = $indexCol;
		
		
		if ($col->colspan > 1) {
			$indexColEnd = $indexColStart + $col->colspan -1;
		}
		
		if ($col->rowspan > 1 && $col->colspan > 1) {
			$rangeCellsMerge = $this->getPosCellFromIndex($indexColStart, $numFilaActual);
			$rangeCellsMerge .= ':';
			$rangeCellsMerge .= $this->getPosCellFromIndex($indexColEnd, $numFilaActual + $col->rowspan -1);
		}
		elseif ($col->colspan > 1){
			$rangeCellsMerge = $this->getPosRangeFilaFromIndex($indexColStart, $indexColEnd, $numFilaActual);
		}
		elseif ($col->rowspan > 1){
			$rangeCellsMerge = $this->getPosRangeRowSpanFromIndex($indexCol, $col->rowspan, $numFilaActual);
		}
		
	//	echo "<br/> ->Fila: $numFilaActual COLUMNA: $indexCol colspan: $col->colspan rowspan: $col->rowspan indexColEnd: $indexColEnd posCell: $posCell | rangeCellsMerge: $rangeCellsMerge Valor: " . (is_object($value) ? 'obj' : $value);
		
		if ($value && is_object($value)) {
			if ($value instanceof ExjUtilImage) {
				$this->setImageCell($value, $indexCol, $indexColEnd, ($col->rowspan <= 1));
			}
			else {
				$this->setValueCell($posCell, 'Objeto desconocido', $this->showBorderDetail);
			}
		}
		else {
			if ($col->isTypeFloat) {
				$this->setFormatCells($posCell, ExjColumnReport::FORMAT_FLOAT);
			}
			
			$this->setValueCell($posCell, $value, $this->showBorderDetail);
			
			if (($value !== null) && ($value || $value !== '')) {
				if ($col->isfontBold) {
					$this->setBoldFont($posCell);
				}
				
				if ($col->fontSize) {
					$this->setSizeFont($posCell, $col->fontSize);
				}
			}
		}
		
		$cellColApplied = $posCell;
		if ($rangeCellsMerge) {
			$this->mergeCells($rangeCellsMerge, $this->showBorderDetail);
			$cellColApplied = $rangeCellsMerge;
		}
		
		if ($col->isTitle || $col->isHeader) {
			$this->applyStylesHeaders($cellColApplied);
		}
		
		switch ($col->align) {
			case ExjUtilTable::ALIGN_CENTER:
				$this->setAlignmentCenter($cellColApplied);
			break;
		
			case ExjUtilTable::ALIGN_LEFT:
				$this->setAlignmentLeft($cellColApplied);
			break;
			
			case ExjUtilTable::ALIGN_RIGHT:
				$this->setAlignmentRight($cellColApplied);
			break;
		}
		
		if ($indexColEnd) {
			$indexCol = $indexColEnd;
		}
		
		return true;
	}
		
	public function setImageCell(ExjUtilImage $objImage, $indexColIni, $indexColEnd=0, $fixHeightRow = false){
		$drawing = new PHPExcel_Worksheet_Drawing();
		
		$indexColImage = ($indexColEnd ? $indexColEnd: $indexColIni);
		
		$posCell = $this->getPosCellFromIndex($indexColImage);
	//	echo "<br/>indexColIni: $indexColIni indexColEnd: $indexColEnd posCell: $posCell";
		
		$drawing->setName($objImage->alt);
		$drawing->setDescription($objImage->alt);
		$drawing->setPath($objImage->src);

		if (!$objImage->height || !$objImage->width) {
			$drawing->setResizeProportional(true);
		}
		
		if ($objImage->height) {
			$drawing->setHeight($objImage->height);
		}
		
		if ($objImage->width) {
			$drawing->setWidth($objImage->width);
		}
		
		if ($fixHeightRow) {
			$this->setRowHeight($drawing->getHeight());
		}
		
		$drawing->setCoordinates($posCell);
		
		if ($objImage->offsetX === null) {
			$widthColImagePX = self::ConvertWidthColToPixel($this->getWidthColFromIndex($indexColImage));
			
			$objImage->offsetX = $widthColImagePX - $drawing->getWidth() - 3;
		}
		
		if ($objImage->offsetX) {
		 	$drawing->setOffsetX($objImage->offsetX);
		}
		
		if ($objImage->offsetY === null) {
			$objImage->offsetY = $drawing->getHeight()* 0.06;
		}
		
		if ($objImage->offsetY) {
			$drawing->setOffsetY($objImage->offsetY);
		}
		
		$drawing->setWorksheet($this->getActiveSheet());
	}	
	
	
	private $_isOrientationLANDSCAPE=false;
	
	public function fixPageVertical(){
		$this->getActiveSheet()->getPageSetup()->setOrientation(
			PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT
		);

		$this->_isOrientationLANDSCAPE = false;
		return $this->_fixPageMargins();
	}
	public function fixPageHorizontal(){
		$this->getActiveSheet()->getPageSetup()->setOrientation(
			PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE
		);

		$this->_isOrientationLANDSCAPE = true;
		return $this->_fixPageMargins();
	}
	

	public function fixPaperSizeA4(){
		$this->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
		return $this;
	}
	public function fixPaperSizeFOLIO(){
		$this->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);

		return $this;
	}
	
	public function setTitle($title){
		$title = ExjText::_($title);
		$this->_clearCharsInvalid($title);
		
		ExjTransferCharacters::encodeISOToUTF8($title);
		$this->getActiveSheet()->setTitle($title);
		return $this;
	}
	
	public function getTitleActiveSheet(){
		return $this->getActiveSheet()->getTitle();
	}
	
	private function _clearCharsInvalid(&$text){
		if (!$text) {
			return $text;
		}
		
		$text = str_replace("/", ' ', $text);
		return $text;
	}
	
	/**
	 * Envia un valor a la casilla indicada
	 *
	 * @param string $posCell Ej: A1
	 * @param string $value
	 */
	public function setValueCell($posCell, $value, $addBorder=true){
		if ($value) {
			ExjTransferCharacters::encodeISOToUTF8($value);
			
			if (strpos($value, '<br/>') !== false) {
				$value = str_replace('<br/>', "\n", $value);
			}
	
		    if (strpos($value, "&nbsp;") !== false) {
				$value = str_replace("&nbsp;", " ", $value);
			}
		}

		$this->getActiveSheet()->setCellValue($posCell, $value);
		if ($addBorder) {
			$this->setBorderFino($posCell);
		}

		return $this;
	}

	public function setValueCellBold($posCell, $value, $addBorder=true){
		$this->setValueCell($posCell, $value, $addBorder);
		$this->setBoldFont($posCell);
		return $this;
	}
	
	
	/**
	 * overwriten. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	protected function reportProperties(&$subject, &$description, &$category){
		$subject = $this->_title;
		$description = 'Documento generado por Exj ' . Exj::GetNameApp();
		$category = 'Reporte';
	}
	

	/**
	 * Ancho de la columna indicada
	 *
	 * @param string $col Ej: A
	 * @param int $width
	 */
	public function setWidthCol($col, $width){
		$col = $this->getCol($col);
		$this->getActiveSheet()->getColumnDimension($col)->setWidth($width);
		return $this;
	}
	
	/**
	 * Retorna el nombre de la columna, ej: A, B o C
	 *
	 * @param string o numerico $mixed Si es valor numerico, indicar el indice de la col, es decir, 0 para la columna A
	 * @return string ej: A, B, C etc
	 */
	public function getCol($mixed){
		if (!is_numeric($mixed)) {
			if (strlen($mixed) > 1) {
				$mixed = substr($mixed, 0, 1);
			}
			return $mixed;
		}
		
		$ordA = ord('A');
		$col = chr($ordA + intval($mixed));
		return $col;
	}

	public function getWidthCol($col){
		$col = $this->getCol($col);
		
		return $this->getActiveSheet()->getColumnDimension($col)->getWidth();
	}

	/**
	 * Devuelve el ancho de la columna indicada
	 *
	 * @param int $indexCol la primera columna empieza en 1
	 * @return float -1 si la columna no existe
	 */
	public function getWidthColFromIndex($indexCol){
		if ($indexCol <= 0) {
			$indexCol = 1;
		}
		
		$ordA = ord('A');
		$col = chr($ordA + intval($indexCol) -1);
		
		// echo '<br/>'.__METHOD__ . " indexCol: $indexCol col: $col";
		
		return $this->getActiveSheet()->getColumnDimension($col)->getWidth();
	}
	
	/**
	 * Fija en la cabecera de la página
	 *
	 * @param string $titleHeader Si no se define se establece el nombre de la oficina del usuario
	 */
	private function _sheetShowHeader(){
		if ($this->sheetTextHeader === null) {
			$this->sheetTextHeader = $this->_objPHPExcel->getProperties()->getTitle();
		}
		
		ExjTransferCharacters::encodeISOToUTF8($this->sheetTextHeader);
		
//		$this->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&B'.$this->sheetTextHeader . '&R&G');
		$this->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&G'. '&R&G');
	}
	
	private function _sheetShowFooter(){
		if (!$this->sheetTextFooter) {
			$this->sheetTextFooter = ExjUser::GetNombreEmpresa();
		}
		
//		$textFooter = '&L&B' . $this->sheetTextFooter;
		$textFooter = '&L&B'. ExjText::__('Impreso el'). ' &D';
		// $textFooter = '&L&B'. ExjText::__('Impreso el'). ' &d';
		
		// $textFooter = '&L&C' . $this->sheetTextFooter;
		
		$textFooter .= '&R'.ExjText::__('Página'). ' &P '.ExjText::__('de').' &N';
		ExjTransferCharacters::encodeISOToUTF8($textFooter);
		$this->getActiveSheet()->getHeaderFooter()->setOddFooter($textFooter);
	}
	
	private function _loadImageDrawing(&$objDrawing, $nameFileImagen, $description, $name=''){
		if (!$name) {
			$name = $description;
		}
		$objDrawing->setName($name);
		$objDrawing->setDescription($description);
		$objDrawing->setPath($this->_getFilePathImage($nameFileImagen));

		$objDrawing->setResizeProportional(true);
		$objDrawing->setHeight($this->_heightHeaderImage);
		
		return $objDrawing;
	}
	
	/**
	 * Oculta o no los logos en el header de la página
	 *
	 * @param bool $hidden Defecto true
	 */
	public function hiddenLogos($hidden = true){
		$this->hiddenLogoLeft($hidden)->hiddenLogoRight($hidden);
		if ($hidden){
			$this->_heightHeaderImage = 0;
		}
		else {
			$this->_heightHeaderImage = self::HEIGHT_HEADER_IMAGE;
		}

		return $this;
	}
	
	public function hiddenLogoLeft($hidden = true){
		$this->_hiddenLogoLeft = $hidden;
		return $this;
	}
	public function hiddenLogoRight($hidden = true){
		$this->_hiddenLogoRight = $hidden;
		return $this;
	}
	
	private function _addImagesHeaders(){
		if (!$this->_hiddenLogoLeft) {
			$objDrawingLeft = new PHPExcel_Worksheet_HeaderFooterDrawing();
			$this->_loadImageDrawing($objDrawingLeft, self::IMAGE_HEADER_LEFT, 'Logo Izquierda', 'Logo1');
			$this->getActiveSheet()->getHeaderFooter()->addImage($objDrawingLeft, PHPExcel_Worksheet_HeaderFooter::IMAGE_HEADER_LEFT);
		}
		
		if (!$this->_hiddenLogoRight) {
			$objDrawingRight = new PHPExcel_Worksheet_HeaderFooterDrawing();
			$this->_loadImageDrawing($objDrawingRight, self::IMAGE_HEADER_RIGTH, 'Logo Derecha', 'Logo2');
			$this->getActiveSheet()->getHeaderFooter()->addImage($objDrawingRight, PHPExcel_Worksheet_HeaderFooter::IMAGE_HEADER_RIGHT);
			
		}
	}
	
	
	/**
	 * Setea el alto de la fila indicada de la hoja activa
	 *
	 * @param int $height
	 * @param int $numFilaActual si no se indica se toma la fila actual es ese momento
	 */
	public function setRowHeight($height, $numFilaActual = null){
		if (!$numFilaActual) {
			$numFilaActual = $this->getNumRowCurrent();
		}
		
		$this->getActiveSheet()->getRowDimension($numFilaActual)->setRowHeight($height);
	}
	
	private function _addImagesInSheet(){
		if ($this->_hiddenLogoLeft && $this->_hiddenLogoRight) {
			return false;
		}
		
		$this->getActiveSheet()->insertNewRowBefore(1, 1);
		
		$this->getActiveSheet()->getRowDimension('1')->setRowHeight(
			$this->_heightHeaderImage*0.96
		);

		$numCols = count($this->_cols);
		$lastIndexCol = $numCols-1;
		
		if (!$this->_hiddenLogoLeft) {
			$objDrawingLeft = new PHPExcel_Worksheet_Drawing();
			
			$this->_loadImageDrawing($objDrawingLeft, self::IMAGE_HEADER_LEFT, 'Logo Izquierda', 'Logo1');
			$objDrawingLeft->setCoordinates('A1');
			
			$objDrawingLeft->setWorksheet($this->getActiveSheet());
		}

		// ------------------------
		if (!$this->_hiddenLogoRight) {
			$objDrawingRight = new PHPExcel_Worksheet_Drawing();
			$this->_loadImageDrawing($objDrawingRight, self::IMAGE_HEADER_RIGTH, 'Logo Derecha', 'Logo2');
		//	$objDrawingRight->setResizeProportional(true);
			
			// xxx
			// TODO: Con el Formato PDF no se visualizan bien las imagenes
			if (!$this->isFormatPDF()) {
				$this->mergeCells("A1:" . $this->getCol($lastIndexCol).'1');
				$lastIndexCol += 1;
			}
			
			$lastCol = $this->getCol($lastIndexCol);
			$objDrawingRight->setCoordinates($lastCol.'1');
			$objDrawingRight->setOffsetX($objDrawingRight->getWidth()*-1);
			
			$objDrawingRight->getShadow()->setVisible(true);
			
			$objDrawingRight->setWorksheet($this->getActiveSheet());
		}
		
		return true;
	}
	
	private function _getFilePathImage($nameFile){
		$pathBaseImages = ExjString::ConcatPaths(
			Exj::GetPathResources(), 'images/report/'
		);
		
		return ($pathBaseImages.$nameFile);
	}
	
	public function addRowSum($numRowIniSum, $numRowFinSum, $addTotal = false){
		if (!$this->_items || count($this->_items) == 0) {
			return false;
		}
		
		if ($numRowIniSum >= $numRowFinSum) {
			return false;
		}
		
		$cols = $this->getColumns();
		$indexColSum = 0;
		foreach ($cols as $colSum) {
			$indexColSum += 1;
			$posCellSum = $this->getPosCellFromIndex($indexColSum);
			$nameFieldSum = $colSum->dataIndex;
			
			// echo "<br/>indexColSum: $indexColSum field: $nameFieldSum";
			
			$rowSummay = $this->_getRowSummaryFromNameColumn($nameFieldSum);
			if (!$rowSummay) {
				$this->setValueCell($posCellSum, '', false);
				continue;
			}
	
			$formulaSum = '=SUM(';
			$formulaSum .= $this->getPosCellFromIndex($indexColSum, $numRowIniSum);
			$formulaSum .= ':';
			$formulaSum .= $this->getPosCellFromIndex($indexColSum, $numRowFinSum);
			$formulaSum .= ')';
			
			// echo " Fijando formula: $formulaSum";
			
			if ($rowSummay->headerPreCell && ($indexColSum >= 2)) {
				$posCellPre = $this->getPosCellFromIndex($indexColSum-1);
				$this->setValueCellBold($posCellPre, $rowSummay->headerPreCell, true);
				// $this->setAlignmentRight($posCellPre);
				
				if (!$this->_cellsSubTotales) {
					$this->_cellsSubTotales = array();
				}
				$this->_cellsSubTotales[] = $posCellPre;
				
				// echo " Fijando titulo: $rowSummay->headerPreCell en pos: $posCellPre";
			}
			
			$this->setValueCellBold($posCellSum, $formulaSum);
		}
		
		
		if (!$this->_filasSubTotals) {
			$this->_filasSubTotals = array();
		}
		$this->_filasSubTotals[] = $this->_numFilaActual;
		
		if ($addTotal) {
			$this->_numFilaActual += 1;
			
			$indexColSum = 0;
			foreach ($cols as $colSum) {
				$indexColSum += 1;
				$posCellSum = $this->getPosCellFromIndex($indexColSum);
				$nameFieldSum = $colSum->dataIndex;
				
				$rowSummay = $this->_getRowSummaryFromNameColumn($nameFieldSum);
				if (!$rowSummay) {
					$this->setValueCell($posCellSum, '', false);
					continue;
				}

				if ($rowSummay->headerPreCell && ($indexColSum >= 2)) {
					$posCellPre = $this->getPosCellFromIndex($indexColSum-1);
					$this->setValueCellBold($posCellPre, 'TOTAL', true);
					// $this->setAlignmentRight($posCellPre);
					$this->_cellsSubTotales[] = $posCellPre;
				}
				
				$formulaSum = array();
				foreach ($this->_filasSubTotals as $filaSubTotal) {
					$cellSubTotal = $this->getPosCellFromIndex($indexColSum, $filaSubTotal);
					$formulaSum[] = $cellSubTotal;
				}
				
				$formulaSum = implode('+', $formulaSum);
				$formulaSum = '='.$formulaSum;
				
				$this->setValueCellBold($posCellSum, $formulaSum, true);
			}			
		}
		
		return true;
	}
	
	private function _fixSheetColsStylesSubTotales(){
		if (!$this->_cellsSubTotales) {
			return false;
		}
		
		foreach ($this->_cellsSubTotales as $cell) {
			$this->setAlignmentRight($cell);
			$this->setSizeFont($cell, 11);
		}
	}
	
	private function _readDataAll(){
		$this->reportLoadData($this->_data);
		$this->_items = null;
		$this->reportLoadItems($this->_items);
		if ($this->haveError()) {
			return false;
		}
		
		if (!$this->_items) {
			$this->getResponse()->loadTopics($this->_items);
		}
		
		
		if (!$this->_data) {
			$response = $this->getResponse();
			$this->_data = $response->getDataFooter();
		}

		return true;
	}
	
	private function _getPassword($prefixPwd=''){
		$pwd = $this->_fileName;
		if (!$pwd) {
			$pwd = $this->_title;
		}
		if (!$pwd) {
			$pwd = Exj::GetDateTime('%d-%m-%Y');
		}
		
		$pwd = md5($pwd);
		$pwd = $prefixPwd. $pwd;
		$pwd = substr($pwd, 0, 15);

		// echo "<br/>pwd: $pwd";
		
		return $pwd;
	}
	
	private function _fixSecurityDocument(PHPExcel_DocumentSecurity &$docSecurity){
		$docSecurity->setLockWindows(true);
		$docSecurity->setLockStructure(true);
		$docSecurity->setLockRevision(true);
		
//		echo "<br/>x->isSecurityEnabled:". ($docSecurity->isSecurityEnabled() ? 'SI':'NO');
		
		$docSecurity->setWorkbookPassword($this->_getPassword('doc'));
		$docSecurity->setRevisionsPassword($this->_getPassword('rev'));
		
	//	echo "<br/>WorkbookPassword: ". $docSecurity->getWorkbookPassword();
		$this->_objPHPExcel->setSecurity($docSecurity);
	}
	
	private function _fixWorksheetProtection(PHPExcel_Worksheet_Protection &$worksheetProtection){
		$worksheetProtection->setSheet(true);
		$worksheetProtection->setDeleteColumns(true);
		$worksheetProtection->setInsertColumns(true);
		$worksheetProtection->setInsertHyperlinks(true);
		$worksheetProtection->setInsertRows(true);
		$worksheetProtection->setSort(true);
		
		$worksheetProtection->setPassword($this->_getPassword('sheet'));
		
		
		$this->getActiveSheet()->setProtection($worksheetProtection);
	}
	
	/**
	 * Renderiza un regsitro, si se retorna false, se renderizan las columnas
	 *
	 * @param object $dataItem
	 * @param int $numFilaActual Por referencia
	 * @param unknown_type $forceExit Por referencia
	 * @return bool retornar true para no se renderizen los valores de las columnas
	 */
	protected function renderRowCustom($dataItem, &$numFilaActual, &$forceExit){
		
		return false;
	}
	
	private function _prepareDocument(){
		$this->_verifyCriterias();
		if ($this->haveError()) {
			return false;
		}


		$subject = '';
		$description = '';
		$category = '';
		$this->reportProperties($subject, $description, $category);
		$subject = ExjText::_($subject);
		$description = ExjText::_($description);
		$category = ExjText::__($category);
		
		ExjTransferCharacters::encodeISOToUTF8($subject);
		ExjTransferCharacters::encodeISOToUTF8($description);
		ExjTransferCharacters::encodeISOToUTF8($category);
		
		$this->_objPHPExcel->getProperties()->setCreator(self::CREATOR_DOCUMENT)
			 ->setLastModifiedBy(self::CREATOR_DOCUMENT)
			 ->setTitle(ExjText::_($this->_title))
			 ->setSubject($subject)
			 ->setDescription($description)
			 ->setKeywords("openxml php Exj " . Exj::GetNameApp())
			 ->setCategory($category);
							 
		// echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;
		
		// $this->_fixSecurityDocument($this->_objPHPExcel->getSecurity());
		// $this->_fixWorksheetProtection($this->getActiveSheet()->getProtection());
		
		
		if (!$this->hiddenSheetHeader) {
			$this->_sheetShowHeader();
		}
		
		if (!$this->hiddenSheetFooter) {
			$this->_sheetShowFooter();
		}

		// echo "<br>Rep. 1 PC: " . print_r($this->getParamsCriteria(), true);
		if (!$this->_readDataAll()) {
			return false;
		}
		// echo "<br>Rep. 2 PC: " . print_r($this->getParamsCriteria(), true);
		
		// echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;
		$titleReport = $this->getTitlePage();
		if ($titleReport) {
			$titleReport = strtoupper($titleReport);
		}
		$this->reportTitle($titleReport, $this->_numFilaActual);
		if (!$this->_hiddenAllHeaderPage && $titleReport) {
			$posCellTitle = $this->setValueCellTitleFromIndex($titleReport);
			$this->applyStylesHeaders($posCellTitle, 12);
			$this->_numFilaActual += 1;
		}
		
		
		if (!$this->_hiddenAllHeaderPage && $this->_nameCriteriaModel) {
			$this->setValuesCriteria(
				$this->_nameCriteriaModel,
				$this->_nameComponentCriteria,
				$this->_onlyRequiredCriteria
			);
		}
							 
		$this->reportDetailBefore($this->_numFilaActual, $this->_data);
		if (!$this->_isOverwrite_reportDetailBefore || $titleReport) {
			//echo " this->_numFilaActual: $this->_numFilaActual";
			
			$this->_numFilaActual -= 1;
			
			if ($this->_numFilaActual < 0) {
				$this->_numFilaActual = 0;
			}
			
			// echo " this->_numFilaActual: $this->_numFilaActual";
		}
		
	//	echo " this->_numFilaActual: $this->_numFilaActual";
		if ($this->_hiddenAllHeaderPage) {
			if ($this->_numFilaActual == 0) {
				$this->_numFilaActual = -1;
			}
		}
		
		if ($this->_showHeadersDetail) {
			$this->reportHeadersDetail(
				$this->_numFilaActual, $this->_items, $this->_data
			);
		}
		
		$indexFilIniDetail = $this->_numFilaActual+1;
		
		$numCols = 1;
		if ($this->_items && count($this->_items) > 0) {
			$cols = $this->getColumns();
			$numCols = count($cols);
			
			// $this->_prepareDetail($indexFilIniDetail);
			
			$forceExit = false;
			
			$numRowIniSum = 0;
			$numRowFinSum = 0;
			
			foreach ($this->_items as $item) {
				$this->_numFilaActual += 1;
				
				if ($this->_foundConditionSumSubTotal($item)) {
					if (!$numRowIniSum) {
						$numRowIniSum = $this->_numFilaActual;
					}
					elseif ($numRowIniSum != $this->_numFilaActual) {
						$numRowFinSum = $this->_numFilaActual-1;
					}
					
					if ($numRowFinSum) {
						// echo "<br/>Fila: $this->_numFilaActual numRowIniSum: $numRowIniSum numRowFinSum: $numRowFinSum";
						$this->addRowSum($numRowIniSum, $numRowFinSum);
						
						$this->_numFilaActual += 1;
						
						$numRowIniSum = $this->_numFilaActual;
						$numRowFinSum = 0;								
					}
				} // if sumSubTotal
				
				if ($this->renderRowCustom($item, $this->_numFilaActual, $forceExit)) {
					if ($forceExit) {
						break;
					}
					
					continue;
				}
				
				
				$indexCol = 0;
				foreach ($cols as $col) {
					$indexCol += 1;
					
					$posCell = $this->getPosCellFromIndex($indexCol);
					$nameField = $col->dataIndex;
					
					$valueRaw = '';
					$value = $valueRaw;
					
					if ($col->isCalc) {
						$valueRaw = '=';
						$valueRaw .= $col->function; // SUMA
						$valueRaw .= '(';
						$valueRaw .= $col->colIni . $this->_numFilaActual;
						$valueRaw .= ':';
						if (!$col->colFin) {
							$valueRaw .= $this->getPosCellFromIndex($indexCol-1);
						}
						else {
							$valueRaw .= $col->colFin . $this->_numFilaActual;
						}
						
						$valueRaw .= ')';
						$value = $valueRaw;
						// echo "<br/>indexCol: $indexCol valueRaw: $valueRaw ";
					}
					elseif (isset($item->$nameField)) {
						$valueRaw = $item->$nameField;
					}
					
					if ($this->rendererColValue($nameField, $valueRaw, $item, $this->_numFilaActual, $posCell) === false) {
						$value = $valueRaw;
					}
					
					$typeCol = '';
					if ($this->validateFieldValueRenderer($nameField, $valueRaw, $typeCol)) 
					{
					//	$value = $valueRaw;
					}

					$this->_prepareCellValue(
						$value, $valueRaw, $posCell, $col, $typeCol
					);
					
					$result = $this->reportDetailItem(
						$this->_numFilaActual,
						$this->_data,
						$value,
						$posCell,
						$nameField,
						$item
					);
					
					if ($result === null) {
						$this->_numFilaActual -= 1;
						$forceExit = true;
						break;
					}
					if ($result === false) {
						$forceExit = true;
						break;
					}
				}
				
				if ($forceExit) {
					break;
				}
			}
			
			if ($numRowIniSum) {
				if ($numRowIniSum != $this->_numFilaActual) {
					$numRowFinSum = $this->_numFilaActual;
				}
				
				if ($numRowFinSum) {
					$this->_numFilaActual += 1;
					// echo "<br/>FINAL Fila: $this->_numFilaActual numRowIniSum: $numRowIniSum numRowFinSum: $numRowFinSum";
					$this->addRowSum($numRowIniSum, $numRowFinSum, true);
					$numRowIniSum = 0;
					$numRowFinSum = 0;								
				}
			}
		}

		$indexFilIniDetail = $this->rendererRowIniDetail($indexFilIniDetail);
		
		if ($indexFilIniDetail < $this->_numFilaActual) {
			if ($this->_fixSizeFontToDetail) {
				$rangeCellsDetail = $this->getPosCellFromIndex(1, $indexFilIniDetail);
				$rangeCellsDetail .= ':';
				$rangeCellsDetail .= $this->getPosCellFromIndex($numCols);
			
				$this->setSizeFont($rangeCellsDetail, $this->sizeFontDetail);
				// echo "<br/>setSizeFont -> rango: $rangeCellsDetail sizeFont: $this->sizeFontDetail";
				$this->afterSetSizeFontDetail($rangeCellsDetail, $this->sizeFontDetail);
			}
			
			$this->_fixSheetColsAlignDetail($indexFilIniDetail, $this->_numFilaActual);
			$this->_fixSheetColsStylesSubTotales();
		}
		
		$numFilaActualLast = $this->_numFilaActual;
		$this->setSheetAlignmentAjustarTexto();
		
		$this->reportDetailAfter($this->_numFilaActual, $this->_data);
		
		if ($numFilaActualLast < $this->_numFilaActual) {
			$this->setSheetAlignmentAjustarTexto($numFilaActualLast);
			$numFilaActualLast = $this->_numFilaActual;
		}
		
		if ($this->_showImagesInHeader) {
			$this->_addImagesHeaders();
		}
		else {
			if (!$this->isFormatPDF()) {
				$this->_addImagesInSheet();
			}
		}
		
							 
		if ($this->_sheetIndex > 0) {
			$this->setActiveSheetIndex(0);
		}
		
		return true;
	}

	protected function rendererRowIniDetail($rowIni){
		return $rowIni;
	}

	protected function afterSetSizeFontDetail($rangeCells, $sizeFont){
		return $this;
	}
	
	public function saveExcel2007(){
		$this->_extensionFileSaved = 'xlsx';
    	$pathFile = $this->getPathFile();
    	if (!$pathFile) {
    		return false;
    	}
    	
    	$this->_prepareDocument();


		$objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel2007');
		$objWriter->save($pathFile);
		
		$this->_fullPathFileSaved = $pathFile;
		
		return true;
	}
	
	
	public function saveExcel95(){
		$this->_extensionFileSaved = 'xls';
    	$pathFile = $this->getPathFile();
    	if (!$pathFile) {
    		return false;
    	}
    	
    	$this->_prepareDocument();
     	
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel5');
		$objWriter->save($pathFile);
		$this->_fullPathFileSaved = $pathFile;
		
		return true;
	}
	
	/**
	 * Guarda el archivo xml
	 *
	 * @param string $pathFile
	 * @param string $msgError
	 * @return bool, 
	 */
	protected function saveCustomXML(&$pathFile, &$msgError, $items){
		$msgError = "No se ha implementado el método: " . __METHOD__;
		return false;
	}

	public function saveXML(){
		$this->_verifyCriterias();
		if ($this->haveError()) {
			return false;
		}
		
		$this->_extensionFileSaved = 'xml';
    	$pathFile = $this->getPathFile();
    	if (!$pathFile) {
    		return false;
    	}
    	
    	if (!$this->_readDataAll()){
    		return false;
    	}
    	
    	$msgError = '';
    	
    	if ($this->saveCustomXML($pathFile, $msgError, $this->_items) === false) {
    		if (!$msgError) {
    			$msgError = 'Error desconocido';
    		}
    		$this->_setError($msgError);
    		return false;
    	}
    	
    	if ($msgError) {
    		$this->_setError($msgError);
    		return false;
    	}
     	
		$this->_fullPathFileSaved = $pathFile;
		
		return true;
	}
	
	/**
	 * Convina celdas
	 *
	 * @param string $rangeCells Ej: A18:E22
	 */
	public function mergeCells($rangeCells, $addBorder = false){
		$this->getActiveSheet()->mergeCells($rangeCells);
		
		if ($addBorder) {
			$this->setBorderFino($rangeCells);
		}
		// $this->setAlignmentAjustarTexto($rangeCells);

		return $this;
	}
	
	public function mergeCellsFromIndex($indexColStart, $indexColEnd, $addBorder = true, $numFilaActual=null, $format='', $STYLE_ALIGN=0){
		if (!$numFilaActual) {
			$numFilaActual = $this->_numFilaActual;
		}
		
		$posRange = $this->getPosRangeFilaFromIndex($indexColStart, $indexColEnd, $numFilaActual);

		if ($format) {
			$this->setFormatCells($posRange, $format);
		}
		
		$this->mergeCells($posRange, $addBorder);
		
		if ($STYLE_ALIGN){
			$this->setAlignmentCustom($posRange, $STYLE_ALIGN);
		}
	}
	
	
	/**
	 * Tamaño de la fuente
	 *
	 * @param string $pCellCoordinate Ej: A3:A6 o A3
	 * @param int $size
	 */
	public function setSizeFont($pCellCoordinate, $size){
		// echo "<br>setSizeFont. cell: $pCellCoordinate size: $size";
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getFont()->setSize($size);
	}
	
	/**
	 * Fija negrilla en la casilla indicada
	 *
	 * @param string $posCell Ej: A1
	 */
	public function setBoldFont($posCell){
		$this->getActiveSheet()->getStyle($posCell)->getFont()->setBold(true);
	}

	/**
	 * Fija valores en las celdas indicadas por el primer parámetro
	 *
	 * @param array $rowsGroupRows Arreglo de objeto creados por el método: NewRowColumnHeaderGroup
	 * @param int $numRowOffset Defecto 1, es el nro de filas que se adicionan antes de fijar las cabeceras de grupo, sino se desea que se sumen filas, fijar el valor a 0 o null
	 */
	public function setValuesColumnHeaderGroup($rowsGroupRows, $numRowOffset=1){
		if ($numRowOffset) {
			$this->_numFilaActual += $numRowOffset;
		}
		
		$indexColLast = 0;
		$numFilaActual = null; // auto
		
		foreach ($rowsGroupRows as $rowGroupRows) {
			$indexColIni = $indexColLast + 1;
			$indexColEnd = $indexColIni + $rowGroupRows->colspan - 1;
			
			$cell = $this->getPosCellFromIndex($indexColIni, $numFilaActual);
			$this->setValueCellBold($cell, $rowGroupRows->header);
			
			$this->setAlignmentCustom($cell, $rowGroupRows->align);
			
			$cellsMerge = $this->getPosRangeFilaFromIndex($indexColIni, $indexColEnd, $numFilaActual);
			$this->mergeCells($cellsMerge, true);
			$this->applyStylesHeaders($cellsMerge, 12);
			
			$indexColLast = $indexColEnd;
		}
	}
	
	/**
	 * Crea un objeto para setValuesColumnHeaderGroup
	 *
	 * @param string $header
	 * @param int $colspan
	 * @param int $STYLE_ALIGN Constante STYLE_ALIGN_HORIZONTAL_XXX por defecto center
	 * @return object
	 */
	static function NewRowColumnHeaderGroup($header, $colspan, $STYLE_ALIGN=null){
		$row = new stdClass();
		$row->header = $header;
		$row->colspan = $colspan;
		
		if ($STYLE_ALIGN === null) {
			$STYLE_ALIGN = self::STYLE_ALIGN_HORIZONTAL_CENTER;
		}
		$row->align = $STYLE_ALIGN;
		
		return $row;
	}	

	public function setAlignmentCustom($pCellCoordinate, $STYLE_ALIGN){
		switch ($STYLE_ALIGN) {
			case self::STYLE_ALIGN_HORIZONTAL_CENTER:
				$this->setAlignmentCenter($pCellCoordinate);
			break;
		
			case self::STYLE_ALIGN_HORIZONTAL_LEFT:
				$this->setAlignmentLeft($pCellCoordinate);
			break;

			case self::STYLE_ALIGN_HORIZONTAL_RIGHT:
				$this->setAlignmentRight($pCellCoordinate);
			break;
		}
	}
	

	public function setAlignmentRight($pCellCoordinate){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	}
	public function setAlignmentCenter($pCellCoordinate){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->
		   getAlignment()->
		   setHorizontal(
		   	PHPExcel_Style_Alignment::HORIZONTAL_CENTER
		);
		return $this;
	}
	public function setAlignmentLeft($pCellCoordinate){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
	}
	
	public function setAlignmentVerticalCenter($pCellCoordinate){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}

	public function setAlignmentReducirHastaAjustar($pCoordinate, $encoger=true){
		$this->getActiveSheet()->getStyle($pCoordinate)->getAlignment()->setShrinkToFit($encoger);
	}

	public function setAlignmentAjustarTexto($pCoordinate, $envolver=true){
		$this->getActiveSheet()->getStyle($pCoordinate)->getAlignment()->setWrapText($envolver);
	}
	
	public function setSheetAlignmentAjustarTexto($filaIni=1, $numCols=0){
		if (!$numCols && $this->_cols) {
			$numCols = count($this->_cols);
		}
		if (!$numCols) {
			return false;
		}
		
		$cellsAll = $this->getPosCellFromIndex(1, $filaIni);
		$cellsAll .= ':';
		$cellsAll .= $this->getPosCellFromIndex($numCols, $this->_numFilaActual);
		$this->setAlignmentAjustarTexto($cellsAll);
	}
	
	
	public function autoAddColOrder($addColOrder=true){
		$this->_addColOrder = $addColOrder;
	}
	
	public function setHeadersFontSize($size){
		$this->_headersFontSize = $size;
	}
	public function getHeadersFontSize(){
		if (!$this->_headersFontSize) {
			return 6;
		}
		return $this->_headersFontSize;
	}
	
	public function applyStylesHeaders($rangeCells, $fontSize = 0){
		$size = $fontSize;
		if (!$size) {
			$size = $this->getHeadersFontSize();
		}
		
		$styleHeaders = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
			'font'    => array(
				'bold' => true,
				'size' => $size
			),
			'fill' => array(
	 			'type'       => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
	  			'rotation'   => 90,
	 			'startcolor' => array(
	 				'argb' => 'FFA0A0A0'
	 			),
	 			'endcolor'   => array(
	 				'argb' => 'FFFFFFFF'
	 			)
	 		)
		);
		
		$this->getActiveSheet()->getStyle($rangeCells)->applyFromArray($styleHeaders);
		return $this;
	}
	
	public function setBorderFino($pCellCoordinate, $color='FF000000'){
		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => $color),
				),
			),
		);
		
		$this->getActiveSheet()->getStyle($pCellCoordinate)->applyFromArray(
			$styleThinBlackBorderOutline
		);

		return $this;
	}
	public function setBorderGrueso($pCellCoordinate, $color='FF993300')
	{
		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					'color' => array('argb' => $color),
				),
			),
		);
		
		$this->getActiveSheet()->getStyle($pCellCoordinate)->applyFromArray(
			$styleThinBlackBorderOutline
		);

		return $this;
	}

	public function setFillSOLID($pCellCoordinate, $color='FF808080'){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getFill()->getStartColor()->setARGB($color);
	}
	public function setFillGRADIENT_LINEAR($pCellCoordinate, $color='FFA0A0A0'){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR);
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getFill()->getStartColor()->setARGB($color);
	}
	
	public function createSheet(){
		$this->_objPHPExcel->createSheet();
		$this->_sheetIndex += 1;
		$this->setActiveSheetIndex($this->_sheetIndex);
	}
	
	public function setActiveSheetIndex($sheetIndex){
		$this->_objPHPExcel->setActiveSheetIndex($sheetIndex);
	}

	
	public function setShowGridLines($display=true){
		$this->getActiveSheet()->setShowGridLines($display);
	}

	/**
	 * Antes de Guardar el reporte, esta función es llamada desde el controlador
	 *
	 * @param ExjController $scopeController
	 * @return bool si se retorna false se cancela la descarga, pero se debe informar del error.
	 */
	public function beforeSave(ExjController $scopeController, ExjResponse &$response){
		
		return true;
	}
	
	public function save(){
		// echo '<br/>TEST: ' . __METHOD__ ;
		if ($this->isFormatPDF()) {
			return $this->savePDF();
		}
		elseif ($this->isFormatExcelXLSX()){
			return $this->saveExcel2007();
		}
		elseif ($this->isFormatExcelXLS()) {
			return $this->saveExcel95();
		}
		elseif ($this->isFormatXML()) {
			return $this->saveXML();
		}
		
		$this->_setError("El formato $this->_format no está soportado");
		return false;
	}
	
	
	
	public function savePDF(){
		$this->_extensionFileSaved = 'pdf';
    	$pathFile = $this->getPathFile();
    	if (!$pathFile) {
    		return false;
    	}
    	
    	$this->_prepareDocument();
    	$this->setShowGridLines($this->_showGridLinesForPDF);

    	// $className = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class;
        // PHPExcel_IOFactory::registerWriter('Pdf', $className);
        
    	
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'PDF');
	//	$objWriter->setUseInlineCss(false);
		$objWriter->save($pathFile);
		$this->_fullPathFileSaved = $pathFile;
		
		return true;
	}

	public function getExtensionFileSaved(){
		return $this->_extensionFileSaved;
	}
	
	public function getFullPathFileSaved($encodeBase64=false){
		if (!$this->_fullPathFileSaved) {
			return $this->_fullPathFileSaved;
		}
		if ($encodeBase64) {
			return base64_encode($this->_fullPathFileSaved);
		}
		
		return $this->_fullPathFileSaved;
	}
	
	public function getPathFile($extension=''){
		if (!$extension) {
			$extension = $this->_extensionFileSaved;
		}
		
    	$hFile = new ExjHandlerFile($this->getFileName($extension));
    	if ($hFile->haveError()) {
    		return false;
    	}
    	
    	$this->_hFile = $hFile;
    	return $hFile->getPathFileOut();
	}
	
	public function getURIFileDownload(){
		if (!$this->_hFile) {
			return '';
		}
		return $this->_hFile->getURIFileOut();
	}

	
	public function getFileName($extension=''){
		$fileName = $this->_fileName;
		
		if ($extension) {
			$fileName .= ".$extension";
		}
		
		return $fileName;
	}
	
	/**
	 * overwrite. Carga datos para el reporte
	 *
	 * @param obj $data
	 * @return bool
	 */
	protected function reportLoadData(&$data){
		return null;
	}
	
	/**
	 * overwrite. Cuando se devuelve data, esta función debe ser sobrescrita
	 *
	 * @param array $items
	 * @return bool
	 */
	protected function reportLoadItems(&$items){
		return null;
	}


	public function registerCriteriaInt($name, $alias, $required=true){
		$this->registerCriteria($name, $alias, $required, ExjColumnReport::FORMAT_INT);
	}
	public function registerCriteriaDate($name, $alias, $required=true){
		$this->registerCriteria($name, $alias, $required, ExjColumnReport::FORMAT_DATE);
	}
	
	public function registerCriteria($name, $alias, $required=true, $type=''){
		if (!$type) {
			$type = ExjColumnReport::FORMAT_STRING;
		}
		
		$name = trim($name);
		if (!$name) {
			return false;
		}
		
		$type = trim($type);
		if ($type) {
			$type = strtolower($type);
		}
		if (!$this->_criterias) {
			$this->_criterias = array();
		}
		
		if (!$alias) {
			$alias = $name;
		}
		
		$criteria = new stdClass();
		if (isset($this->_criterias[$name])) {
			$criteria = &$this->_criterias[$name];
		}
		else {
			$criteria->name = $name;
		}
		$criteria->alias = $alias;
		$criteria->type = $type;
		$criteria->required = $required;
		$criteria->isInt = ($type == 'int');
		
		$this->_criterias[$name] = $criteria;
		
		return true;
	}
	
	private function _verifyCriterias(){
		$criterias = $this->_criterias;
		if (empty($criterias)) {
			return true;
		}
		
		foreach ($criterias as $name => $criteria) {
			$valueRaw = $this->getParam($name);
			
			if ($criteria->required && !$valueRaw) {
				$this->_setError("$criteria->alias es requerido");
				break;
			}
		}
		
		return !$this->haveError();
	}
	
	public function bindCriterias($data) {
		if (empty($data)) {
			// echo "<br>bindCriterias. data está vacío.";
			return true;
		}

		foreach ($data as $key => $value) {
			if (is_numeric($key) || $value==='' || $value===null) {
				continue;
			}

			$this->setParamCriteria($key, $value);
		}
		
		/*
		$this->_paramsCriteria = clone $data;
		if (is_array($this->_paramsCriteria)) {
			$this->_paramsCriteria = ExjObject::ConvertArrayToObject(
				$this->_paramsCriteria
			);
		}
		*/

		// echo "<br>bindCriterias: ". print_r($this->getParamsCriteria(), true);
		
		$criterias = $this->_criterias;
		if (empty($criterias)) {
			/*
			echo "<br>bindCriterias. return sin criterias data: ". print_r($this->_paramsCriteria, true);
			*/
			return true;
		}

		$parCri = $this->_paramsCriteria;

		foreach ($criterias as $name => $criteria) {
			if (!isset($parCri->$name)) {
				continue;
			}

			$value = $parCri->$name;
			if ($criteria->isInt) {
				if (!$value) {
					$value = 0;
				}
				$value = intval($value);
			}
			
			// echo "<br>setParam. $name = $value";
			$this->setParam($name, $value);
		}
		
		return true;
	}
	
	public function getParamsCriteria(){
		return $this->_paramsCriteria;
	}

	public function getCloneParamsCriteria(){
		if (empty($this->_paramsCriteria)) {
			return $this->_paramsCriteria;
		}

		return (clone $this->_paramsCriteria);
	}

	public function setParamCriteria($name, $value){
		$name = trim($name);
		if (!$name) {
			return $this;
		}
		
		if (!$this->_paramsCriteria) {
			$this->_paramsCriteria = new stdClass();
		}

		// echo "<br>setParamCriteria. $name = $value";
		$this->_paramsCriteria->$name = $value;
		return $this;
	}
	
	public function registerColBoolYesNo($dataIndex, $header, $width=null){
		$this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_BOOLYESNO
		)->setAlignLeft();
	}
	
	public function registerColInt($dataIndex, $header, $width=null){
		$this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_INT
		)->setAlignRight();
	}
	public function registerColDate($dataIndex, $header, $width=null){
		if (!$width) {
			$width = 15;
		}
		
		$this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_DATE
		)->setAlignCenter();
	}
	public function registerColFloat($dataIndex, $header, $width=null){
		$this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_FLOAT
		)->setAlignRight();
	}
	public function registerColDateTime($dataIndex, $header, $width=null){
		if (!$width) {
			$width = 18;
		}
		
		$this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_DATETIME
		)->setAlignCenter();
	}
	
	public function registerColCalc($dataIndex, $header, $function, $colIni, $colFin=null, $width=null, $type='int', $align='right'){
		if (!$width) {
			$width = strlen($header);
		}
		
		$col = $this->registerCol($dataIndex, $header, $width, $type);
		
		$col->setAlign($align)->setIsCalc();
		if (!$colIni) {
			$colIni = 'A';
		}
		$col->colIni = $colIni;
		$col->colFin = $colFin;
		$col->function = $function;
		
		return $col;
	}
	
	public function registerColCalcSum($dataIndex, $header, $colIni, $colFin=null, $width=null){
		return self::registerColCalc($dataIndex, $header, 'SUM', $colIni, $colFin, $width=null, 'int');
	}
	
	public function registerColStringRight($dataIndex, $header, $width=null){
		return $this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_STRING
		)->setAlignRight();
	}
	
	public function registerColStringCenter($dataIndex, $header, $width=null){
		return $this->registerCol(
			$dataIndex, $header, $width, ExjColumnReport::FORMAT_STRING
		)->setAlignCenter();
	}
	
	public function registerColOrder($dataIndex, $header='', $width=9, $type='int'){
		return $this->registerCol($dataIndex, $header, $width, $type);
	}
	
	
	
	public function fixWidthColOrder($width){
		if (!$this->_cols) {
			return false;
		}
		
		foreach ($this->_cols as &$col) {
			if ($col->dataIndex == self::DATAINDEX_ORD) {
				$col->setWidthRaw($width)->setWidth($width);
			//	echo "<br/>Fijando ancho a col ORD width: $width";
				break;
			}
		}
	}
	
	/**
	 * Registra campos desde el modelo de lista
	 *
	 * @param mixed $fieldsFilter array o strings separados por coma
	 * @return int Nro de columnas registradas, false si ocurre un error
	 */
	public function registerColsFromListModel($fieldsFilter=null){
		$nameClassReportModel = get_class($this);
		
		$nameClassListModel = str_replace(
			'ReportModel', 'ListModel', $nameClassReportModel
		);
		
		if (!class_exists($nameClassListModel)) {
			$this->_setError("No se pudo cargar list model: $nameClassListModel");
			return false;
		}
		
		$instanceListModel = new $nameClassListModel(
			ExjHelperMenu::CreateAccessReadOnly()
		);

		return $this->_addColsFromListModel($instanceListModel, $fieldsFilter);
	}
	
	private function _addColsFromListModel(ExjListModel $listModel, $fieldsToFilter){
		if ($fieldsToFilter && !is_array($fieldsToFilter)) {
			$fieldsFilter = trim($fieldsFilter);
			
			if ($fieldsFilter == "*") {
				$fieldsFilter = null;
			}
			else {
				$fieldsToFilter = str_replace(array(", ", " ,"), ',', $fieldsToFilter);
				$fieldsToFilter = explode(',', $fieldsToFilter);
			}
		}
		
		$cols = $listModel->to_ui_columns();
		$fields = $listModel->to_ui_fields();
		
		$dataIndexsHidden = $this->getDataIndexsHidden();
		if ($dataIndexsHidden) {
			if (!is_array($dataIndexsHidden)) {
				$dataIndexsHidden = explode(',', $dataIndexsHidden);
				foreach ($dataIndexsHidden as &$dataIndexH) {
					$dataIndexH = trim($dataIndexH);
				}
			}
			
		}

		foreach ($cols as $col) {
			if (!isset($col->dataIndex)) {
				continue;
			}
			
			$dataIndex = $col->dataIndex;
			
			$colShow = null;
			if ($this->_colsShow) {
				$colShow = $this->getColShow($dataIndex);
				if (!$colShow) {
					continue;
				}
			}

			if (isset($col->hidden) && $col->hidden) {
				if ($colShow) {
					$col->hidden = false;
				}
				else {
					continue;	
				}
			}
			
			if ($fieldsToFilter) {
				$foundField = false;
				foreach ($fieldsToFilter as $fieldToFilter) {
					if ($fieldToFilter == $dataIndex || $fieldToFilter == '*') {
						$foundField = true;
						break;
					}
				}
				
				if (!$foundField) {
					continue;
				}
			}

			if (isset($col->xtype) && $col->xtype) {
				if ($col->xtype == 'actioncolumn') {
					continue;
				}

				// echo "<br>Test. Col a reporte: ". print_r($col, true);
			}
			
			
			$fieldOK=null;
			foreach ($fields as $field) {
				if ($field->name == $dataIndex) {
					$fieldOK=$field;
					break;
				}
			}
			if (!$fieldOK) {
				continue;
			}
			
			
			
			if ($dataIndexsHidden) {
				if (in_array($dataIndex, $dataIndexsHidden)) {
					continue;
				}
			}
			
			$typeField = ExjColumnReport::FORMAT_STRING;
			switch ($fieldOK->type) {
				case ExjField::TYPE_INT:
					$typeField = ExjColumnReport::FORMAT_INT;
				break;
			
				case ExjField::TYPE_FLOAT:
					$typeField = ExjColumnReport::FORMAT_FLOAT;
				break;
				
				case ExjField::TYPE_DATE:
					$typeField = ExjColumnReport::FORMAT_DATE;
				break;
				
				case ExjField::TYPE_DATETIME:
					$typeField = ExjColumnReport::FORMAT_DATETIME;
				break;
				
				case ExjField::TYPE_BOOL:
					$typeField = ExjColumnReport::FORMAT_BOOLYESNO;
				break;
			}
			
			$align = ExjColumnReport::ALIGN_CENTER;
			if (isset($col->align) && $col->align) {
				$align = $col->align;
			}
			
			$header = '';
			if (isset($col->header) && $col->header) {
				$header = strtoupper($col->header);
			}
			
		//	echo "<br>";
		//	print_r($col);
			if (isset($col->renderer) && $col->renderer) {
				$this->addFunctionRenderer($dataIndex, $col->renderer);
			}
			
		//	echo "<br>dataIndex: $dataIndex colShow: " . ($colShow ? print_r($colShow, true): 'VACIO');
			if ($colShow) {
				if (isset($colShow->width) && $colShow->width) {
					$col->width = $colShow->width;
				}
			}
			
			$this->registerCol($dataIndex, $header, $col->width, $typeField)
				->setAlign($align);
		}
	
		return (count($this->_cols));
	}
	
	/**
	 * Obtiene los dataIndex que no se presentarían en el reporte
	 *
	 * @return string|array
	 */
	protected function getDataIndexsHidden(){
		return null;
	}
	
	public function addFunctionRenderer($dataIndex, $fnRenderer){
		if (!$this->_functionsRenderers) {
			$this->_functionsRenderers = array();
		}
		
		$this->_functionsRenderers[$dataIndex] = $fnRenderer;
		return $this;
	}
	
	protected function validateFieldValueRenderer($dataIndex, &$value, &$type){
		if (!$this->_functionsRenderers) {
			return false;
		}
		
		if (!isset($this->_functionsRenderers[$dataIndex])) {
			return false;
		}
		
		$fnRenderer = $this->_functionsRenderers[$dataIndex];
		
		return $this->_callMethodRendererValue($fnRenderer, $value, $type);
	}
	
	/**
	 * Renderiza la edad según una fecha
	 *
	 * @param string $fechaNac
	 * @param ExjColumnReport $type
	 * @return int
	 */
	protected function rendererEdad($fechaNac, &$type){
		$type = ExjColumnReport::FORMAT_INT;
		
		return ExjUtil::CalcularEdad($fechaNac);
	}
	
	protected function rendererTextSiNo($value, &$type){
		$type = ExjColumnReport::FORMAT_STRING;
		
		if (!$value || $value == '0') {
			return 'NO';
		}
		
		return 'SI';
	}
	
	protected function renderNumberBlue2($value, &$type){
		$type = ExjColumnReport::FORMAT_INT;

		return $value;
	}

	protected function renderDecimal2($value, &$type){
		$type = ExjColumnReport::FORMAT_FLOAT;

		return $value;
	}

	protected function renderDecimal2ZeroRed($value, &$type){
		$type = ExjColumnReport::FORMAT_FLOAT;

		return $value;
	}

	protected function renderNumberBlue($value, &$type){
		$type = ExjColumnReport::FORMAT_INT;

		return $value;
	}
	
	protected function rendererTextLastChange($value, &$type){
		$type = ExjColumnReport::FORMAT_STRING;
		
		return $value;
	}
	
	protected function renderFTPDoc($value, &$type){
		$type = ExjColumnReport::FORMAT_STRING;
		
		return $value;
	}
	
	protected function renderLinkDoc($value, &$type){
		$type = ExjColumnReport::FORMAT_STRING;
		
		return $value;
	}
	
	
	protected function rendererText($text, &$type){
		$type = ExjColumnReport::FORMAT_STRING;
		
		if ($text === null) {
			$text = '';
		}
		
		return $text;
	}
	
	protected function rendererFormatDate($fecha, &$type){
		$type = ExjColumnReport::FORMAT_DATE;
		if (!$fecha || strlen($fecha) <= 5) {
			return $fecha;
		}
		
		return ExjDate::ConvertToDateDisplay($fecha);
	}
	
	protected function rendererFormatDateTime($fecha, &$type){
		$type = ExjColumnReport::FORMAT_DATETIME;
		if (!$fecha || strlen($fecha) <= 5) {
			return $fecha;
		}
		
		return ExjDate::ConvertToDateTimeDisplay($fecha);
	}

	protected function rendererUserName($text, &$type){
		return $this->rendererText($text, $type);
	}
	

	
	private function _callMethodRendererValue($nameMethod, &$value, &$type){
		$nameMethod = str_replace("Exj.", '', $nameMethod);
	//	echo "<br>_callMethodRendererValue nameMethod: $nameMethod valor: $value";
		
		if (method_exists($this, $nameMethod)) {
			if ($nameMethod != 'rendererFormatDate' || $type != ExjColumnReport::FORMAT_DATE) {
				$value = $this->$nameMethod($value, $type);
			}
			
		//	echo " -> $value";
		}
		else {
			$this->_setError("No existe el método: $nameMethod (value, type) <br>Clase: " . get_class($this));
			return false;
		}
		
		return true;
	}

	public function registerCol($dataIndex, $header, $width=null, $type='string'){

		$col = ExjColumnReport::Create($dataIndex);
		$col->setHeader($header)->setType($type)
			->setPosIndexCol(count($this->_cols)+1)
			->setWidthRaw($width);
		
		$this->_cols[] = $col;
		
		return $col;
	}
	
	public function changeHeaderCol($dataIndex, $header){
		$foundCol = false;
		foreach ($this->_cols as &$col) {
			if ($col->dataIndex == $dataIndex) {
				$col->setHeader($header);
				$foundCol = true;
				break;
			}
		}
		
		return $foundCol;
	}
		
	public function setConditionSumSubTotal($nameFieldItem, $dataConditional){
		$this->_conditionSumSubTotal = new stdClass();
		
		$this->_conditionSumSubTotal->nameFieldItem = $nameFieldItem;
		$this->_conditionSumSubTotal->dataConditional = $dataConditional;
	}
	
	private function _foundConditionSumSubTotal($itemData){
		if (!$this->_conditionSumSubTotal) {
			return false;
		}
		if (!$itemData) {
			return false;
		}
		
		$nameFieldItem = $this->_conditionSumSubTotal->nameFieldItem;
		if (!isset($itemData->$nameFieldItem)) {
			return false;
		}
		
		$dataConditional = $this->_conditionSumSubTotal->dataConditional;
		
		return ($itemData->$nameFieldItem == $dataConditional);
	}
	
	
	public function registerRowSumSubTotal($nameColumn, $headerPreCell=''){
		if (!$this->_rowsSummary) {
			$this->_rowsSummary = array();
		}
		
		$row = new stdClass();
		$row->nameColumn = $nameColumn;
		$row->headerPreCell = $headerPreCell;
		
		$this->_rowsSummary[] = $row;
	}
	
	private function _getRowSummaryFromNameColumn($nameColumn){
		if (!$this->_rowsSummary) {
			return null;
		}
		
		$rowFound = null;
		foreach ($this->_rowsSummary as &$row) {
			if ($row->nameColumn == $nameColumn) {
				$rowFound = $row;
				break;
			}
		}
		
		return $rowFound;
	}
	
	
	
	private function _fixSheetColsAlignDetail($filaIni, $filaFin){
		if (!$this->_autoAlignCols) {
			return ;
		}
		
		if ($filaIni > $filaFin) {
			return false;
		}
		
		foreach ($this->_cols as $col) {
			$posCol = $this->getPosColFromIndex($col->posIndexCol); // ej: A
			
			$cellCol = $posCol . $filaIni;
			$cellCol .= ':';
			$cellCol .= $posCol . $filaFin;

			// echo "<br>align. cellCol: $cellCol";
			
			if ($col->isAlignLeft()) {
				$this->setAlignmentLeft($cellCol);
			}
			elseif($col->isAlignCenter()) {
				$this->setAlignmentCenter($cellCol);
			}
			elseif ($col->isAlignRight()) {
				$this->setAlignmentRight($cellCol);
			}
		}

		$this->afterAlignDetail($filaIni, $filaFin);
	}

	protected function afterAlignDetail($filaIni, $filaFin){
		return $this;
	}
	
	/**
	 * Obtiene el número de la fila actual
	 *
	 * @return int
	 */
	public function getNumRowCurrent(){
		return $this->_numFilaActual;
	}
	
	private function _prepareDetail($filaIni = null){
		if (!$this->_items) {
			return false;
		}
		if (!$this->_cols) {
			return false;
		}
		
		if (!$filaIni) {
			$filaIni = $this->_numFilaActual;
		}
		
		$filaFin = $filaIni + count($this->_items);
		
		if ($filaFin <= 1) {
			return false;
		}
		
		$indexCol = 1;
		foreach ($this->_cols as $col) {
			$rangeCol = $this->getPosCellFromIndex($indexCol, $filaIni);
			$rangeCol .= ':';
			$rangeCol .= $this->getPosCellFromIndex($indexCol, $filaFin);
			
			$this->setFormatCells($rangeCol, $col->type);
			++$indexCol;
		}
	}
	
	public function setFormatCells($pCoordinate, $format='string'){
		if (!$format) {
			$format = ExjColumnReport::FORMAT_STRING;
		}
		
		switch ($format) {
			case ExjColumnReport::FORMAT_STRING:
			case ExjColumnReport::FORMAT_BOOLYESNO:
				$this->setFormatTypeString($pCoordinate);
			break;
			case ExjColumnReport::FORMAT_DATE:
				$this->setFormatTypeDate($pCoordinate);
			break;
			case ExjColumnReport::FORMAT_DATETIME:
				$this->setFormatTypeDateTime($pCoordinate);
			break;
			case ExjColumnReport::FORMAT_INT:
				$this->setFormatTypeInt($pCoordinate);
			break;
			case ExjColumnReport::FORMAT_FLOAT:
				$this->setFormatTypeFloat($pCoordinate);
			break;
		}
	}

	/**
	 * overwrited. Renderiza el valor de una columna
	 *
	 * @param string $dataIndex Nombre de la columna
	 * @param mixed $value Pasado por referencia
	 * @param object $item Objeto de la fila
	 * @param int $numFila Numero de la fila
	 * @param string $posCell Posicion de la celda
	 * @return bool Si se retorna false no se renderiza el valor
	 */
	protected function rendererColValue($dataIndex, &$value, $item, $numFila, $posCell){
		
		return true;
	}
	
	private function _prepareCellValue(
		&$value, $valueRaw, $posCell, ExjColumnReport $col, $type='')
	{
		$value = $valueRaw;
		
		if (!$type) {
			$type = $col->type;
		}
		
		if ($col->isCalc) {
			$this->renderCol($col->dataIndex, $value, $posCell);
			return $value;
		}
		
		$this->setFormatCells($posCell, $type);
		
		if ($col->isTypeBool()) {
			$value = $valueRaw = ExjUtil::RendererSiNo($valueRaw);
		}
		
		if ($valueRaw) {
			switch ($type) {
				case ExjColumnReport::FORMAT_DATE:
					if ($valueRaw && strlen($valueRaw) > 5) {
						$value = ExjDate::ConvertToDateDisplay($valueRaw);	
					}
					
				break;
				case ExjColumnReport::FORMAT_DATETIME:
					if ($valueRaw && strlen($valueRaw) > 5) {
						$value = ExjDate::ConvertToDateTimeDisplay($valueRaw);	
					}
				break;
				
				case ExjColumnReport::FORMAT_STRING:
				case ExjColumnReport::FORMAT_BOOLYESNO:
					if (is_numeric($value)) {
						// echo "<br/>$col->dataIndex ES NUM: $value";
						if ($col->isAlignRight()) {
							$value = " $value";
						}
						else {
							$value = "$value ";
						}
					}
				break;
			}
		}
		
		return $value;
	}
	
	public function setFormatTypeString($pCoordinate){
		// echo "<br/>setFormatTypeString: $pCoordinate";
		$this->getActiveSheet()->getStyle($pCoordinate)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
	}
	public function setFormatTypeInt($pCoordinate){
		$this->getActiveSheet()->getStyle($pCoordinate)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
	}
	public function setFormatTypeFloat($pCoordinate){
		$this->getActiveSheet()->getStyle($pCoordinate)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);
	}
	public function setFormatTypeDate($pCoordinate){
		$this->getActiveSheet()->getStyle($pCoordinate)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DDMMYYYY);
	}
	public function setFormatTypeDateTime($pCoordinate){
		$this->getActiveSheet()->getStyle($pCoordinate)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
	}
	
	private function _fixWidthCols(){
		$cols = $this->getColumns();
		if (!$cols) {
			return false;
		}
		if (count($cols) == 0) {
			return false;
		}
		
		$indexCol = 0;
		foreach ($cols as $col) {
			$this->setWidthCol($indexCol++, $col->width);
		}
		
		
		$this->_setFitPage($this->getActiveSheet()->getPageSetup());
		
		return true;
	}
	
	
	public function getColFromDataIndex($dataIndex){
		$colFound = null;
		foreach ($this->_cols as &$col) {
			if ($col->dataIndex == $dataIndex) {
				$colFound = $col;
				break;
			}
		}
		
		return $colFound;
	}

	public function setColumnWidthFixed($dataIndex, $width){
		if ($col = $this->getColFromDataIndex($dataIndex)) {
			$col->setWidthFixed($width);
		}

		return $this;
	}
	
	
	public function colAlignRight($dataIndex){
		$c = &$this->getColFromDataIndex($dataIndex);
		if (!$c) {
			return false;
		}
		
		return $c->setAlignRight();
	}
	public function colAlignCenter($dataIndex){
		$c = &$this->getColFromDataIndex($dataIndex);
		if (!$c) {
			return false;
		}
		
		return $c->setAlignCenter();
	}
	
	public function isPageHorizontal(){
		return (
			$this->getActiveSheet()->getPageSetup()->getOrientation() == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE
		);
	}
	public function isPageVertical(){
		return !$this->isPageHorizontal();
	}

	public function isPageSizeA4(){
		return ($this->getActiveSheet()->getPageSetup()->getPaperSize() == PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	}
	public function isPageSizeFOLIO(){
		return ($this->getActiveSheet()->getPageSetup()->getPaperSize() == PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
	}

	private function _setFitPage(PHPExcel_Worksheet_PageSetup &$pageSetup){
	//	$pageSetup->setFitToPage();
		// $pageSetup->setFitToWidth();
	}
	
	public function getWidthPage(){
		$widthPage = 102; // VERTICAL POR DEFECTO
		if ($this->isPageHorizontal()) {
			$widthPage = 144; // HORIZONTAL POR DEFECTO
		}
		
		if ($this->isFormatPDF()) {
		//	echo "<h1>FORMATO PDF</h1>";
			if ($this->isPageSizeFOLIO()) {
		//		echo "<br/>Es tamaño FOLIO";
				
				if ($this->isPageHorizontal()) {
					$widthPage += 36;
				}
				else {
					$widthPage += 6;
				}
			}
			elseif ($this->isPageSizeA4()){
			//	echo "<br/>Es tamaño A4";
				
				if ($this->isPageHorizontal()) {
					$widthPage += 18;
				}
				else {
					$widthPage += 3;
				}
			}
		}
		else {
			// FORMATOS EXCEL
			if ($this->isPageSizeFOLIO()) {
			//	echo "<br/>Es tamaño FOLIO";
				
				if ($this->isPageHorizontal()) {
					$widthPage -= 15;
				}
				else {
					$widthPage -= 12;
				}
			}
			elseif ($this->isPageSizeA4()){
			//	echo "<br/>Es tamaño A4";
				
				if ($this->isPageHorizontal()) {
					$widthPage -= 3;
				}
				else {
					$widthPage -= 15;
				}
			}
		}
		
		if ($widthPage <= 30) {
			$widthPage = 33;
		}
		
	//	echo " widthPage: $widthPage";
		return $widthPage;
	}
	
	public function getColumns(){
		$nCols = count($this->_cols);
		$widthPage = $this->getWidthPage();
		$widthDefault = round($widthPage/$nCols, 3);
		$widthTotCols = 0;
		foreach ($this->_cols as &$col) {
			if (!$col->widthRaw) {
				$col->widthRaw = $widthDefault;
			}
			else {
				$col->widthRaw = floatval($col->widthRaw);
			}
			
			$widthTotCols += $col->widthRaw;
		}
		
		foreach ($this->_cols as &$col) {
			if ($col->isWidthFixed()) {
				continue;
			}

			$percentCol = ($col->widthRaw)/$widthTotCols;
			
			$col->setWidth(round($percentCol*$widthPage, 3));
		}
		
		// ExjTransferCharacters::encodeISOToUTF8($this->_cols);
		
		return $this->_cols;
	}
	
	/**
	 * Devuelve el header del campo pasado por parametro
	 *
	 * @param string $nameField
	 * @return string
	 */
	private function _getAliasFromFields($nameField){
		$header = '';
		
		if (isset($this->_fields[$nameField])) {
			$f = $this->_fields[$nameField];
			$header = $f->header;
		}
		
		return $header;
	}

	public static function NewRichText($str=''){
		$objRichText = new PHPExcel_RichText();
		$objRichText->createText($str);
		return $objRichText;
	}

	public static function NewStyleColor($pARGB=PHPExcel_Style_Color::COLOR_BLACK){
		return (new PHPExcel_Style_Color($pARGB));
	}

	/*
	 * Crea una celda richText,
	 * luego se puede usar $rt->createTextRun('XXX')->getFont()->setBold(true)
	*/
	public function newCellRichText($posCell, $str=''){
		$objRichText = self::NewRichText($str);
		$this->getCell($posCell)->setValue($objRichText);
		return $objRichText;
	}
}

?>