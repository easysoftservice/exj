<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjImportModel
 * Baseclass para Modelo de importaciones de archivos excel para este ORM
 */
class ExjImportModel {
	const REPORT_FORMAT_PDF = 'XPDF';
	const REPORT_FORMAT_EXCELXLSX = 'EXCELXLSX';
	const REPORT_FORMAT_EXCELXLS = 'EXCELXLS';
	const REPORT_FORMAT_HTML = 'HTML';
	const REPORT_FORMAT_XML = 'XML';
	
    public $id;
    
	protected $title='';
	
	private $_cols = array(), $_fields = array();
	private $_fileName;
	
	private $_rowsSummary = null, $_filasSubTotals = null, $_cellsSubTotales = null;
	
	private $_data=null, $_items=null;
	private $_criterias = null;
	
	/**
	 * Tamaño de página cuando se trata de paginación
	 *
	 * @var int
	 */
	public $pageSize = 30;
	public $sizeFontDetail = 9;
	public $hiddenSheetHeader = false;
	public $hiddenSheetFooter = false;
	public $sheetTextFooter=null;
	public $sheetTextHeader= null;
	
	public $showBorderDetail = true;
	
	/**
	 * Si son o no requeridas todas las columnas
	 *
	 * @var bool Por Defecto: false
	 */
	public $isRequiredAllCols = false;
	
	
	
	
	private $_objPHPExcel;
	private $_sheetIndex=0;
	private $_hFile;
	private $_numFilaActual;
	private $_params, $_format;
	private $_paramsCriteria = null;
	private $_isOverwrite_reportDetailBefore = true;
	
	
	private $_pathFile='';
	private $_id_archivo= null;
	
	private $_numFilaHeader = 1;
	private $_numFilaIniDetail = 2;
	private $_numFilaFinDetail = 0;
	
	
	/**
	 * Constructor del modelo de importación
	 *
	 * @param string $pathFile
	 * @param string $id_archivo
	 */
	public function __construct($pathFile, $id_archivo){
		$this->_pathFile = $pathFile;
		$this->_id_archivo = $id_archivo;
		
		if (!$pathFile) {
			$this->_setError("No se ha enviado el path del archivo importado");
			return $this;
		}
		
		if (!file_exists($pathFile)) {
			$this->_setError("No existe en el servidor: $pathFile");
			return false;
		}
		
		$this->_format = self::REPORT_FORMAT_EXCELXLSX;
		
		$this->_data = null;
		$this->_items = null;
		$this->_params = null;
		
		$this->_fileName = basename($pathFile);
		$this->_numFilaActual = 1;

		global $exj;
		Exj::IncludePHPExcel();
		
		/* para ver el codigo */
		/*
		$testObj = PHPExcel_IOFactory::load($pathFile);
		$testObj->getActiveSheet()->getCell('A1')->getValue(); // midex
		$testObj->getActiveSheet()->getCell('A1')->getDataType(); // string
		$testObj->getActiveSheet()->getCell('A1')->getFormattedValue(); // string
		$testObj->getActiveSheet()->getCellCollection();
		*/
		/* para ver el codigo */
		
		$this->_objPHPExcel = PHPExcel_IOFactory::load($pathFile);
		$numSheets = $this->_objPHPExcel->getSheetCount();
		if ($numSheets <= 0) {
			$this->_setError("El archivo: $this->_fileName no tiene Hojas de Cálculo");
			return $this;
		}
		
		if ($numSheets > 1) {
			$this->_setError("El archivo: $this->_fileName<br/>tiene $numSheets Hojas de Cálculo.<br/>El archivo debe tener solo <b>una Hoja de Cálculo</b>, porque no se puede determinar de que Hoja importar.");
			return $this;
		}
		
		$this->_objPHPExcel->setActiveSheetIndex($this->_sheetIndex);

		
		$this->_hFile = null;
		$this->importInit();
		$this->importRegisterCols();
		$this->importRegisterCriteria();
		
		// $this->_fixWidthCols();
	}
	
	
	
	public function splitCell($cell, &$nameCol, &$numFila){
		if (!$cell) {
			$this->_setError("No se ha enviado la celda para: " . __METHOD__);
			return false;
		}
		
		$cell .= '';
		
		$numChars = strlen($cell);
		$indexSplit = -1;
		for ($i=0; $i <= $numChars; $i++){
			$char = substr($cell, $i, 1);
		//	echo " $i char: $char ";
			if (is_numeric($char)) {
				$indexSplit = $i;
			//	echo " FOUND: $indexSplit ";
				break;
			}
		}
		
		if ($indexSplit <= 0) {
			$this->_setError("No se ha indicado una celda ColumnaFila. Celda: $cell, para: " . __METHOD__);
			return false;
		}
		
		$nameCol = substr($cell, 0, $indexSplit);
		$numFila = intval(substr($cell, $indexSplit));
		
		return true;
	}
	
	static function normalizeValueTexto($value, $valueEmpty=null){
		if (!$value) {
			return $valueEmpty;
		}
		
		$value = trim($value);
		if (!$value) {
			return $valueEmpty;
		}
		
		$charsInvalids = array("\\", "|", "s/d", "s/e", "s/t", "s/p", ";");
		$value = str_ireplace($charsInvalids, '', $value);
		$value = str_replace("  ", ' ', $value);
		$value = str_replace("/", '', $value);
		$value = str_replace("¡", 'i', $value);
		
		$value = trim($value);
		if (!$value) {
			return $valueEmpty;
		}
		
		return $value;
	}
	

	static function normalizeValueTelefono($value){
		if (!$value) {
			return null;
		}
		
		$value = trim($value);
		if (!$value) {
			return null;
		}
		
		$separadorTlf = '/';
		
		$value = str_replace("  ", ' ', $value);
		$partes = explode($separadorTlf, $value);
		if (count($partes) <= 0) {
			return null;
		}
		
		$value = array();
		foreach ($partes as $tlf) {
			$tlf = trim($tlf);
			if (!$tlf) {
				continue;
			}
			if (strlen($tlf) <= 1) {
				continue;
			}
			
			$value[] = $tlf;
		}
		
		if (count($value) == 0) {
			return null;
		}
		
		$value = implode(" $separadorTlf ", $value);
		
		return $value;
	}

	static function normalizeValueCorreo($value){
		$value = self::normalizeValueTexto($value);
		if (!$value) {
			return $value;
		}
		
		return $value;
	}
	
	static function normalizeValueDireccion($value){
		$value = self::normalizeValueTexto($value);
		if (!$value) {
			return $value;
		}
		
		$value = str_ireplace("(Domicilio)", "(Domicilio) ", $value);
		$value = str_ireplace("(Colegio)", "(Colegio) ", $value);
		if (!$value) {
			return null;
		}
		
		return $value;
	}
	
	static function normalizeValueTextoCapital($value, $defaultEmpty=''){
		$value = self::normalizeValueTexto($value, $defaultEmpty);
		if (!$value) {
			return $value;
		}
		
		$value = strtolower($value);
		$partes = explode(' ', $value);
		$articulos = array("de", "los", "las", "con", "y", "o", "la", "del", "para", "el", "por", "a");
		$wUppers = array("utpl", "unl", "mad", "upsi", "ugti", "uce", "ects");
		$words = array();
		foreach ($partes as $w) {
			if (!$w) {
				continue;
			}
			
			if (!in_array($w, $articulos)) {
				$w = ucfirst($w);
			}
			elseif (in_array($w, $wUppers)){
				$w = strtoupper($w);
			}
			
			$words[] = $w;
		}
		
		$value = implode(' ', $words);
		if (!$value) {
			return $defaultEmpty;
		}
		
		return $value;
	}
	

	static function splitApellidosNombres($apeNom, &$apellidos, &$nombres){
		$apellidos = '(Sin Apellido)';
		$nombres = '(Sin Nombre)';
		
		$apeNom = self::normalizeValueTexto($apeNom);
		if (!$apeNom) {
			return true;
		}
		
		$partes = explode(" ", $apeNom);
		if (count($partes) <= 1) {
			$apellidos = $apeNom;
			return true;
		}
		
		$words = array();
		foreach ($partes as $w) {
			if (!$w) {
				continue;
			}
			$words[] = $w;
		}
		
		$numWords = count($words);
		switch ($numWords) {
			case 1:
			$apellidos = $apeNom;
			break;

			case 2:
			$apellidos = $words[0];
			$nombres = $words[1];
			break;
		}
		
		if ($numWords <= 2) {
			return true;
		}
		
		$apellidos = array();
		$nombres = array();
		$posNom = 0;
		$articulos = array("de", "los", "las", "y");
		foreach ($words as $w) {
			if (!in_array(strtolower($w), $articulos)) {
				$posNom += 1;
			}
			if ($posNom <= 2) {
				$apellidos[] = $w;
			}
			else {
				$nombres[] = $w;
			}
		}
		
		$apellidos = implode(' ', $apellidos);
		$nombres = implode(' ', $nombres);
		
		return true;
	}
	
	static function getValueFromLookup($items, $nameSearch, $noFoundGetFirst=true){
		return self::getValueFromCatalog($items, $nameSearch, 'value', 'text', $noFoundGetFirst);
	}
	
	static function getItemFromCatalog($items, $nameSearch, $nameFieldText='text', $noFoundGetFirst=false){
		$itemCat = null;
		if (!$items || count($items) == 0) {
			return $itemCat;
		}

		foreach ($items as $item) {
			$valorText = $item->$nameFieldText;
			
		//	ExjTransferCharacters::decodeUTF8ToISO($valorText);
			/*
			echo " <br/> COMPARANDO: $valorText con $nameSearch ";
			*/
			
			if (ExjUtil::EsIgualLike($valorText, $nameSearch)) {
				$itemCat = $item;
				break;
			}
		}
		
		if (!$itemCat && $noFoundGetFirst) {
			$itemCat = $items[0];
		}
		
		return $itemCat;
	}
	
	
	static function getValueFromCatalog($items, $nameSearch, $nameFieldValue, $nameFieldText, $noFoundGetFirst=true){
		$id = 0;
		if (!$items || count($items) == 0) {
			return $id;
		}

		foreach ($items as $item) {
			$valorText = $item->$nameFieldText;
			
			ExjTransferCharacters::decodeUTF8ToISO($valorText);
			/*
			echo " <br/> COMPARANDO: $valorText con $nameSearch ";
			*/
			
			if (ExjUtil::EsIgualLike($valorText, $nameSearch)) {
				$id = $item->$nameFieldValue;
				break;
			}
		}
		
		if (!$id && $noFoundGetFirst) {
			$item = $items[0];
			if (isset($item->$nameFieldValue)) {
				$id = $item->$nameFieldValue;	
			}
		}
		
		return $id;
	}
	
	
	public function getTitleSheet(){
		return $this->getActiveSheet()->getTitle();
	}

	public function getCellCollectionSheet($sorted = true){
		return $this->getActiveSheet()->getCellCollection($sorted);
	}

	public function getValueFormattedSheet($pCordinate){
		$cell = $this->_getCellSheet($pCordinate);
		if (!$cell) {
			return '';
		}
		$valueCell = $cell->getFormattedValue();
	 //	$valueCell = $cell->getValue();
		ExjTransferCharacters::decodeUTF8ToISO($valueCell);
		return $valueCell;
	}
	
	
	public function getValueSheet($pCordinate){
		$cell = $this->_getCellSheet($pCordinate);
		if (!$cell) {
			return '';
		}
		
		$valueCell = $cell->getValue();
		ExjTransferCharacters::decodeUTF8ToISO($valueCell);
		return $valueCell;
	}

	private function _getCellSheet($pCordinate){
		return $this->getActiveSheet()->getCell($pCordinate);
	}

	public function getValueColFilaSheet($indexCol, $indexFila=null){
		if (!$indexFila) {
			$indexFila = $this->_numFilaActual;
		}
		
		$pCordinate = $this->getPosCellFromIndex($indexCol, $indexFila);
		return $this->getValueSheet($pCordinate);
	}
	
	public function getValueFormattedColFilaSheet($indexCol, $indexFila=null){
		if (!$indexFila) {
			$indexFila = $this->_numFilaActual;
		}
		
		$pCordinate = $this->getPosCellFromIndex($indexCol, $indexFila);
		return $this->getValueFormattedSheet($pCordinate);
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
	
	public function getActiveSheet(){
		return $this->_objPHPExcel->getActiveSheet();
	}
	
	
	/* funciones anteriores pendiente de eliminar */
	
	public function isFormatPDF(){
		return ($this->_format == self::REPORT_FORMAT_PDF);
	}
	public function isFormatExcelXLS(){
		return ($this->_format == self::REPORT_FORMAT_EXCELXLS);
	}
	public function isFormatExcelXLSX(){
		return ($this->_format == self::REPORT_FORMAT_EXCELXLSX);
	}
	public function isFormatHTML(){
		return ($this->_format == self::REPORT_FORMAT_HTML);
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
		$exj->setErrorValidating("ERROR IN IMPORT: $this->title<br/>$msg");
	}
	
	public function haveError(){
		global $exj;
		return $exj->haveError();
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
	}
	
	
	
	/**
	 * overwrite. Inicio
	 *
	 */
	protected function importInit(){
		
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	protected function importRegisterCols(&$numFilaActual){
		
	}
	
	protected function importRegisterCriteria(){
	}

	
	protected function reportDetailBefore(&$numFilaActual, $data){
		$this->_isOverwrite_reportDetailBefore = false;
	}
	
	protected function reportDetail(&$numFilaActual, $items, $data=null){
		++$numFilaActual;
		$this->showHeadersDetail();
	}
	
	protected function reportCustomHeadersDetail(&$numFilaActual, $cols){
		return false;
	}
	
	
	protected function reportDetailItem(&$numFilaActual, $data, $value, $posCell, $nameField, $dataItem){
		$this->setValueCell($posCell, $value, $this->showBorderDetail);
		return true;
	}
	
	protected function reportDetailAfter(&$numFilaActual, $data){
	}

	public function sheetWriteLine($text='', $bold=true, $addBorder=false){
		if (!$text) {
			$bold = false;
		}
		$this->setValueCellTitleFromIndex($text, 1, $addBorder, $bold);
	}
	
	public function setValueCellTitleFromIndex($title, $indexCol=1, $addBorder=false, $bold=true){
		$numColMax = count($this->_cols);
		// $numColMax += 1;
		
		if (!$title) {
			$addBorder = false;
		}
		
		$this->_numFilaActual += 1;
		$cellsMerge = $this->getPosRangeFilaFromIndex($indexCol, $numColMax, $this->_numFilaActual);
		$this->mergeCells($cellsMerge);
		$cellTitle = $this->getPosCellFromIndex($indexCol, $this->_numFilaActual);
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
	}
	
	public function setValueCellTextExpandFromIndex($text, $indexCol=2, $addBorder=true, $bold=false){
		$this->_numFilaActual -= 1;
		$this->setValueCellTitleFromIndex($text, $indexCol, $addBorder, $bold);
		$this->_numFilaActual += 1;
	}
	
	public function setValueCellFromIndex($value, $indexCol=1, $addBorder=true, $addBold = true){
		$pos = $this->getPosCellFromIndex($indexCol, $this->_numFilaActual);
		if ($addBold) {
			$this->setValueCellBold($pos, $value, $addBorder);
		}
		else {
			$this->setValueCell($pos, $value, $addBorder);	
		}
	}

	
	public function showHeadersDetail($title=null, $numFilaActual=null){
		$cols = $this->getColumns();
		if (!$cols || count($cols) == 0) {
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
		
		if ($this->reportCustomHeadersDetail($this->_numFilaActual, $cols) !== false) {
			return false;
		}
		
		
		$indexCol = 1;
		foreach ($cols as $col){
			$cellCol = $this->getPosCellFromIndex($indexCol++);
			
			$this->setValueCellBold($cellCol, $col->header);
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
	
	
	
	

	public function fixPaperSizeFOLIO(){
		$this->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO);
	}
	
	public function setTitle($title){
		$this->getActiveSheet()->setTitle($title);
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
		}
		
		$this->getActiveSheet()->setCellValue($posCell, $value);
		if ($addBorder) {
			$this->setBorderFino($posCell);
		}
	}

	public function setValueCellBold($posCell, $value, $addBorder=true){
		$this->setValueCell($posCell, $value, $addBorder);
		$this->setBoldFont($posCell);
	}
	
	
	/**
	 * overwriten. Lectura de propiedades del documento
	 *
	 * @param string $subject
	 * @param string $description
	 * @param string $category
	 */
	public function reportProperties(&$subject, &$description, &$category){
		$subject = $this->title;
		$description = 'Documento generado por GYMCloud';
		$category = 'Report';
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
		
		$this->getActiveSheet()->getHeaderFooter()->setOddHeader('&L&B'.$this->sheetTextHeader . '&RImpreso el &D');
	}
	
	private function _sheetShowFooter(){
		if (!$this->sheetTextFooter) {
			$this->sheetTextFooter = ExjUser::GetNombreEmpresa();
		}
		
		$textFooter = '&L&B' . $this->sheetTextFooter;
		// $textFooter = '&L&C' . $this->sheetTextFooter;
		$textFooter .= '&RPage &P of &N';
		ExjTransferCharacters::encodeISOToUTF8($textFooter);
		$this->getActiveSheet()->getHeaderFooter()->setOddFooter($textFooter);
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
			$nameFieldSum = $colSum->nameColSheet;
			
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
				$nameFieldSum = $colSum->nameColSheet;
				
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
	
	private function _prepareDocument(){
		$this->_verifyCriterias();
		if ($this->haveError()) {
			return false;
		}
		
		$subject = '';
		$description = '';
		$category = '';
		$this->reportProperties($subject, $description, $category);
		
		global $exj;
		ExjTransferCharacters::encodeISOToUTF8($subject);
		ExjTransferCharacters::encodeISOToUTF8($description);
		ExjTransferCharacters::encodeISOToUTF8($category);
		
$this->_objPHPExcel->getProperties()->setCreator(ExjUser::GetNames())
							 ->setLastModifiedBy(ExjUser::GetNames())
							 ->setTitle($this->title)
							 ->setSubject($subject)
							 ->setDescription($description)
							 ->setKeywords("openxml php mr cargo")
							 ->setCategory($category);
							 
		// echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;
		if (!$this->hiddenSheetHeader) {
			$this->_sheetShowHeader();
		}
		
		if (!$this->hiddenSheetFooter) {
			$this->_sheetShowFooter();
		}
		
							 
		$this->reportLoadData($this->_data);
		$this->reportLoadItems($this->_items);
		if ($this->haveError()) {
			return false;
		}
		
		//echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;
							 
		$this->reportDetailBefore($this->_numFilaActual, $this->_data);
		if (!$this->_isOverwrite_reportDetailBefore) {
			// echo " this->_numFilaActual: $this->_numFilaActual";
			$this->_numFilaActual -= 1;
			
			if ($this->_numFilaActual < 0) {
				$this->_numFilaActual = 0;
			}
			
			// echo " this->_numFilaActual: $this->_numFilaActual";
		}
		
		$this->reportDetail($this->_numFilaActual, $this->_items, $this->_data);
		
		
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
				
				
				$indexCol = 0;
				foreach ($cols as $col) {
					$indexCol += 1;
					
					$posCell = $this->getPosCellFromIndex($indexCol);
					$nameField = $col->nameColSheet;
					
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
					
					$this->_prepareCellValue($value, $valueRaw, $posCell, $col);
					
					$result = $this->reportDetailItem($this->_numFilaActual, $this->_data, $value, $posCell, $nameField, $item);
					
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
		
		if ($indexFilIniDetail < $this->_numFilaActual) {
			$rangeCellsDetail = $this->getPosCellFromIndex(1, $indexFilIniDetail);
			$rangeCellsDetail .= ':';
			$rangeCellsDetail .= $this->getPosCellFromIndex($numCols);
			$this->setSizeFont($rangeCellsDetail, $this->sizeFontDetail);
			
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

		/* 
		if ($this->_sheetIndex > 0) {
			$this->setActiveSheetIndex(0);
		}
		*/
		
		return true;
	}
	
	public function saveExcel2007(){
    	$pathFile = $this->getPathFile('xlsx');
    	if (!$pathFile) {
    		return false;
    	}
    	
    	$this->_prepareDocument();
    	
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel2007');
		$objWriter->save($pathFile);
		
		return true;
	}
	
	
	public function saveExcel95(){
    	$pathFile = $this->getPathFile('xls');
    	if (!$pathFile) {
    		return false;
    	}
    	
    	$this->_prepareDocument();
     	
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'Excel5');
		$objWriter->save($pathFile);
		
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
	}
	
	
	
	/**
	 * Tamaño de la fuente
	 *
	 * @param string $pCellCoordinate Ej: A3:A6 o A3
	 * @param int $size
	 */
	public function setSizeFont($pCellCoordinate, $size){
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


	public function setAlignmentRight($pCellCoordinate){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	}
	public function setAlignmentCenter($pCellCoordinate){
		$this->getActiveSheet()->getStyle($pCellCoordinate)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
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
	
	public function applyStylesHeaders($rangeCells){
		$styleHeaders = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
					'color' => array('argb' => 'FF000000'),
				),
			),
			'font'    => array(
				'bold'      => true
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
		$this->getActiveSheet()->getStyle($pCellCoordinate)->applyFromArray($styleThinBlackBorderOutline);
	}
	public function setBorderGrueso($pCellCoordinate, $color='FF993300'){
		$styleThinBlackBorderOutline = array(
			'borders' => array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THICK,
					'color' => array('argb' => $color),
				),
			),
		);
		$this->getActiveSheet()->getStyle($pCellCoordinate)->applyFromArray($styleThinBlackBorderOutline);
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
		
		$this->_setError("El formato $this->_format no está soportado");
		return false;
	}
	
	public function savePDF(){
    	$pathFile = $this->getPathFile('pdf');
    	if (!$pathFile) {
    		return false;
    	}
    	
    	$this->_prepareDocument();
    	$this->setShowGridLines(false);
    	
		$objWriter = PHPExcel_IOFactory::createWriter($this->_objPHPExcel, 'PDF');
		$objWriter->save($pathFile);
		
		return true;
	}
	
	public function getPathFile($extension){
		return $this->_pathFile;
	}
	
	public function getURIFileDownload(){
		if (!$this->_hFile) {
			return '';
		}
		return $this->_hFile->getURIFileIn();
	}

	
	public function getFileName(){
		return $this->_fileName;
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
		$this->registerCriteria($name, $alias, $required, 'int');
	}
	public function registerCriteriaDate($name, $alias, $required=true){
		$this->registerCriteria($name, $alias, $required, 'date');
	}
	
	public function registerCriteria($name, $alias, $required=true, $type='string'){
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
		if (!$criterias) {
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
	
	public function bindCriterias($data){
		if (!$data) {
			return true;
		}
		
		$this->_paramsCriteria = $data;
		
		$criterias = $this->_criterias;
		if (!$criterias) {
			return true;
		}
		
		global $exj;
		if (is_array($data)) {
			$data = ExjObject::ConvertArrayToObject($data);
			$this->_paramsCriteria = $data;
		}
		
		foreach ($criterias as $name => $criteria) {
			if (isset($data->$name)) {
				$value = $data->$name;
				if ($criteria->isInt) {
					if (!$value) {
						$value = 0;
					}
					$value = intval($value);
				}
				
				$this->setParam($name, $value);
			}
			
		}
		
		return true;
	}
	
	public function getParamsCriteria(){
		return $this->_paramsCriteria;
	}

	public function setParamCriteria($name, $value){
		$name = trim($name);
		if (!$name) {
			return false;
		}
		
		if (!$this->_paramsCriteria) {
			$this->_paramsCriteria = new stdClass();
		}
		$this->_paramsCriteria->$name = $value;
	}
	
	
	public function registerColInt($nameColSheet, $fieldTable, $isRequired = true, $header=''){
		$this->registerCol($nameColSheet, $fieldTable, $isRequired, $header, 'int');
	}
	public function registerColDate($nameColSheet, $fieldTable, $isRequired=true, $header=''){
		$this->registerCol($nameColSheet, $fieldTable, $isRequired, $header, 'date');
	}
	public function registerColFloat($nameColSheet, $fieldTable, $isRequired=true, $header=''){
		$this->registerCol($nameColSheet, $fieldTable, $isRequired, $header, 'float');
	}
	public function registerColDateTime($nameColSheet, $fieldTable, $isRequired=true, $header=''){
		$this->registerCol($nameColSheet, $fieldTable, $isRequired, $header, 'datetime');
	}
	
	public function registerColCalc($nameColSheet, $function, $colIni, $colFin=null, $type='int', $isRequired=true, $header=''){
		$col = $this->registerCol($nameColSheet, $fieldTable, $isRequired, $header, $type);
		
		$col->isCalc = true;
		if (!$colIni) {
			$colIni = 'A';
		}
		$col->colIni = $colIni;
		$col->colFin = $colFin;
		$col->function = $function;
		
		return $col;
	}
	
	public function registerColCalcSum($nameColSheet, $fieldTable, $header, $colIni, $colFin=null){
		return self::registerColCalc($nameColSheet, 'SUM', $colIni, $colFin, 'int', false, $header);
	}
	
	public function registerCol($nameColSheet, $fieldTable, $isRequired=true, $header='', $type='string'){
		$type = trim($type);
		$fieldTable = trim($fieldTable);
		
		if ($type) {
			$type = strtolower($type);
		}
		if (!$header) {
			$header = $nameColSheet;
		}

		$col = new stdClass();
		$col->nameColSheet = $nameColSheet;
		$col->fieldTable = $fieldTable;
		$col->header = $header;
		$col->posIndexCol = count($this->_cols)+1;
		$col->type = $type;
		$col->isCalc = false;
		$col->isRequired = $isRequired;
		$col->nameColCell = null; // esta columna es calculada
		
		$this->_cols[] = $col;
		
		return $col;
	}
	
	public function validateFile(){
		if ($this->haveError()) {
			return false;
		}
		
		$this->_readHeadersFile();
		if ($this->haveError()) {
			return false;
		}
		
		$this->_validateDetailFile();
		if ($this->haveError()) {
			return false;
		}
		
		return true;
	}
	
	protected function importProcessBefore($numFilaActual){
		
	}
	

	/**
	 * overwrite. Proceso de cada fila
	 *
	 * @param object $item
	 * @param int $id_archivo ID del archivo importado
	 * @param int $numFilaActual
	 * @return bool Retornar false para parar el bucle
	 */
	protected function importProcessItem($item, $id_archivo, $numFilaActual){
		return true;
	}
	
	public function processFile(){
		$this->_numFilaActual = $this->_numFilaIniDetail-1;
		$numFilaFinDetail = $this->_numFilaFinDetail;
		
		$cols = $this->_cols;
		
		$this->importProcessBefore($this->_numFilaActual);
		if ($this->haveError()) {
			return false;
		}
		
		$numCellsInvalids = 0;
		$hayErrores = false;
		while ($this->_numFilaActual < $numFilaFinDetail) {
			$this->_numFilaActual += 1;
			$item = new stdClass();
			foreach ($cols as $col) {
				$nameColCell = $col->nameColCell;
				
				$valueItem = '';
				
				if ($nameColCell) {
					$posCell = $nameColCell . $this->_numFilaActual;
					$valueItem = $this->getValueFormattedSheet($posCell);
					// $valueItem = $this->getValueSheet($posCell);
					// xxx
					
				//	echo " |<br/> Leyendo: $posCell Valor: $valueItem Campo: $col->fieldTable ";
				}
				
				if (!$this->_formattedValue($valueItem, $col)) {
					$hayErrores = true;
					break;
				}
				
				$fieldTable = $col->fieldTable;
				
				$item->$fieldTable = $valueItem;
			}
			
			if ($hayErrores) {
				break;
			}
			
			$item->id_archivo_import = $this->_id_archivo;
			
			if ($this->importProcessItem($item, $this->_id_archivo, $this->_numFilaActual) === false) {
				break;
			}
		}
		
		return (!$this->haveError());
	} // processFile
	
	private function _formattedValue(&$valueRaw, $col){
		
		$value = $valueRaw;
		
		switch ($col->type) {
			case 'string':
				if ($valueRaw) {
					$value = trim($valueRaw);
				}
			break;
			
			case 'int':
			case 'float':
				if (!$value) {
					$value = 0;
				}
				if ($col->type == 'int') {
					$value = intval($valueRaw);
				}
				if ($col->type == 'float') {
					$value = floatval($valueRaw);
				}
				
				if (is_nan($value)) {
					$this->_setError("El valor: $valueRaw No es un valor numérico");
				}
				
			break;
			
			case 'datetime':
			case 'date':
				if ($valueRaw) {
					if (!ExjUtil::EsValidoFechaCaracteres($valueRaw)) {
						$this->_setError("El valor: $valueRaw No es una fecha válida");
						return false;
					}
					
					if ($col->type == 'date') {
						$value = ExjDate::ConvertToDateDB($valueRaw);
					}
					if ($col->type == 'datetime') {
						$value = ExjDate::ConvertToDateTimeDB($valueRaw);
					}
				}
			break;
		}
		
		if ($this->haveError()) {
			return false;
		}
		
		$valueRaw = $value;
		
		return true;
	}
	
	private function _validateCols(){
		if ($this->haveError()) {
			return false;
		}
		
		if (count($this->_cols) == 0) {
			$this->_setError("No se han definido columnas");
			return false;
		}
		
		$colsRequiered = array();
		foreach ($this->_cols as $col) {
			if ($this->isRequiredAllCols) {
				if (!$col->nameColCell) {
					$colsRequiered[] = $col->header;
				}
			}
			else {
				if ($col->isRequired && !$col->nameColCell) {
					$colsRequiered[] = $col->header;
				}
			}
		}
		
		if (count($colsRequiered) > 0) {
			$msgError = "En el archivo: $this->_fileName <br/>";
			if (count($colsRequiered) == 1) {
				$msgError .= "Se requiere la siguiente columna:";
			}
			else {
				$msgError .= "Se requieren las siguientes columnas:";
			}
			
			$msgError .= "<br/> ".implode("<br/> ", $colsRequiered);
			
			$this->_setError($msgError);
			return false;
		}
		
		return true;
	}
	
	private function _readHeadersFile(){
		$cells = $this->getCellCollectionSheet();
		$numCells = count($cells);
		if ($numCells == 0) {
			$msgError = 'El archivo: ' . $this->getFileName(). " no tiene datos a importar";
			return false;
		}
		
		$cols = $this->getColumns();
		if (count($cols) >= $numCells) {
			$msgError = "El formato del archivo es incorrecto.<br/>Nro de celdas detectadas: $numCells";
			return false;
		}
		
		// print_r($cells);
		$numFilaHeader=0;
		$nameColHeader = '';
		$cellIni = $cells[0];
		$this->splitCell($cellIni, $nameColHeader, $numFilaHeader);
		if ($this->haveError()) {
			return false;
		}
		// echo " nameColIni: $nameColHeader numFilaIni: $numFilaHeader ";
		foreach ($cells as $cell) {
			$valueFormatted = $this->getValueFormattedSheet($cell);
			
			$nameColCell = '';
			$numFilaCell = 0;
			$this->splitCell($cell, $nameColCell, $numFilaCell);
			if ($numFilaCell == $numFilaHeader) {
				if (!$valueFormatted){
					continue;
				}
				// echo " |<br/>CABECERAS. PROBANDO CON: $valueFormatted celda: $cell ";
				$col = $this->getColFromNameColSheet($valueFormatted);
				if ($col) {
					$col->nameColCell = $nameColCell;
					//echo " SE ENCONTRO EN: $nameColCell ";
				}
			}
			else {
				// echo " |<br/>FILA: $numFilaCell. PROBANDO CON: $valueFormatted celda: $cell ";
				break;
			}
		}
		
		if (!$this->_validateCols()) {
			return false;
		}
		
		$cellFin = $cells[$numCells-1];
		$numFilaFinDetail = 0;
		$nameColFinDetail= '';
		$this->splitCell($cellFin, $nameColFinDetail, $numFilaFinDetail);
		if ($this->haveError()) {
			return false;
		}
		if (!$numFilaFinDetail) {
			$this->_setError("No se pudo determinar la fila final de la celda: $cellFin");
			return false;
		}
		
		$this->_numFilaHeader = $numFilaHeader;
		$this->_numFilaIniDetail = $numFilaHeader+1;
		$this->_numFilaFinDetail = $numFilaFinDetail;
		
		return true;
	}
	
	private function _validateDetailFile(){
		$numFilaIniDetail = $this->_numFilaIniDetail;
		$numFilaFinDetail = $this->_numFilaFinDetail;
		
		$cols = $this->_cols;
		
		$this->_numFilaActual = $numFilaIniDetail-1;
		$numCellsInvalids = 0;
		$cellsInvalid = array();
		while ($this->_numFilaActual < $numFilaFinDetail) {
			$this->_numFilaActual += 1;
			
			foreach ($cols as $col) {
				$nameColCell = $col->nameColCell;
				if (!$nameColCell) {
					continue;
				}
				if (!$col->isRequired) {
					continue;
				}
				
				$posCell = $nameColCell . $this->_numFilaActual;
				$valueCell = $this->getValueFormattedSheet($posCell);
				
				if (!$valueCell) {
					++$numCellsInvalids;
					if ($numCellsInvalids <= 12) {
						$cellsInvalid[] = "Columna: $col->header Celda: $posCell";
					}
				}
				else {
					$this->_formattedValue($valueCell, $col);
				}
			}
		}
		
		if (count($cellsInvalid) > 0) {
			$msgError = 'Were detected following drawbacks:<br/>';
			
			if (count($cellsInvalid) == 1) {
				$msgError .= 'It required the next cell';
			}
			else {
				$msgError .= 'These cells are required';
			}
			
			$msgError .=":<br/>" . implode("<br/>", $cellsInvalid);
			
			if ($numCellsInvalids > count($cellsInvalid)) {
				$celdasExtras = ($numCellsInvalids - count($cellsInvalid));
				$msgError .= "<br/>Existen $celdasExtras celdas mas con estos problemas.";
			}
			
			$msgError .="<br/>It has been read to the row: $numFilaFinDetail";
			
			$this->_setError($msgError);
			return false;
		}
		
		return true;
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
		if ($filaIni > $filaFin) {
			return false;
		}
		
		foreach ($this->_cols as $col) {
			$posCol = $this->getPosColFromIndex($col->posIndexCol); // ej: A
			
			$cellCol = $posCol . $filaIni;
			$cellCol .= ':';
			$cellCol .= $posCol . $filaFin;
			
			if ($col->isAlignLeft) {
				$this->setAlignmentLeft($cellCol);
			}
			elseif($col->isAlignCenter) {
				$this->setAlignmentCenter($cellCol);
			}
			elseif ($col->isAlignRight) {
				$this->setAlignmentRight($cellCol);
			}
		}
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
			$format = 'string';
		}
		
		switch ($format) {
			case 'string':
				$this->setFormatTypeString($pCoordinate);
			break;
			case 'date':
				$this->setFormatTypeDate($pCoordinate);
			break;
			case 'datetime':
				$this->setFormatTypeDateTime($pCoordinate);
			break;
			case 'int':
				$this->setFormatTypeInt($pCoordinate);
			break;
			case 'float':
				$this->setFormatTypeFloat($pCoordinate);
			break;
		
		}
	}
	
	private function _prepareCellValue(&$value, $valueRaw, $posCell, $col){
		$value = $valueRaw;
		
		if ($col->isCalc) {
			return $value;
		}
		
		$this->setFormatCells($posCell, $col->type);
		
		if ($valueRaw) {
			switch ($col->type) {
				case 'date':
					$value = ExjDate::ConvertToDateDisplay($valueRaw);
				break;
				case 'datetime':
					$value = ExjDate::ConvertToDateTimeDisplay($valueRaw);
				break;
			}
		}
		
		return $value;
	}
	
	public function setFormatTypeString($pCoordinate){
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
		}
		
		return true;
	}
	
	
	public function getColFromNameColSheet($nameColSheet){
		$colFound = null;
		foreach ($this->_cols as &$col) {
			if (ExjUtil::EsIgualLike($col->nameColSheet, $nameColSheet)) {
				$colFound = $col;
				break;
			}
		}
		
		return $colFound;
	}
	
	
	
	public function isPageHorizontal(){
		return ($this->getActiveSheet()->getPageSetup()->getOrientation() == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
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

	
	public function getWidthPage(){
	}
	
	public function getColumns(){
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
}

?>