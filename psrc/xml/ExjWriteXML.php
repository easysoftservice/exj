<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para escribir en archivos XML.
 *
 */
class ExjWriteXML extends ExjXML {
	
	private $_useBOM = true;
	private $_version = '1.0';
	
	public function __construct($isFormatUTF8 = true){
		$this->_useBOM = $isFormatUTF8;
	}
	
	
	public function save($pathFile, ExjMapperXML $mapperXML, $items){
		if ($mapperXML->haveError()) {
			$this->setErrorMsg($mapperXML->getErrorMsg());
			return false;
		}
		
		$fileHandle = fopen($pathFile, 'wb+');
		if ($fileHandle === false) {
			$this->setErrorMsg("No se pudo abrir el archivo $pathFile para escribir.");
			return false;
		}
		
		if ($this->_useBOM) {
			// Write the UTF-8 BOM code
			fwrite($fileHandle, "\xEF\xBB\xBF");
			Exj::TrasferCharsEncodeISOToUTF8($items);
		}
		
		$this->_writeItemsXML($fileHandle, $mapperXML, $items);
		
		fclose($fileHandle);
		return true;
	}
	
	private function _getHeadXML($addEndLine = true){
		$headXML = '<?xml';
		
		$headXML .= ' version="'.$this->_version.'"';
		$headXML .= ' encoding="UTF-8"';
		
		$headXML .= '?>';
		if ($addEndLine) {
			$headXML .= self::ENDLINE;
		}
		
		return $headXML;
	}
	
	private function _writeToFile($hFile, $line, $encodeToUTF8 = true){
		if ($encodeToUTF8 && $this->_useBOM) {
			Exj::TrasferCharsEncodeISOToUTF8($line);
		}
		
		fwrite($hFile, $line);
	}
	
	private function _writeItemsXML($hFile, ExjMapperXML $mapperXML, $items){
		$this->_writeToFile($hFile, $this->_getHeadXML());
		
		$sectionGlobal = $mapperXML->getSectionGlobal();
		
		$attrs = $mapperXML->getAtributesSectionGlobal();
		$this->_writeToFile($hFile, ExjMapperXML::StartTag($sectionGlobal, $attrs));

		$tagsHeaders = $mapperXML->getTagsHeaders();
		if ($tagsHeaders && count($tagsHeaders) > 0) {
			foreach ($tagsHeaders as $tagHeader) {
				$this->_writeToFile($hFile, $tagHeader);
			}
		}
		
		if (count($items) > 0) {
			$this->_writeToFile($hFile, $mapperXML->getSectionParentStartToXML());
			
			foreach ($items as $item) {
				$this->_writeToFile($hFile, $mapperXML->parseItemToXML($item, $mapperXML->getSectionItem()), false);
			}
			
			$this->_writeToFile($hFile, $mapperXML->getSectionParentEndToXML() . self::ENDLINE);
		}

		$tagsFooters = $mapperXML->getTagsFooters();
		if ($tagsFooters && count($tagsFooters) > 0) {
			foreach ($tagsFooters as $tagFooter) {
				$this->_writeToFile($hFile, $tagFooter);
			}
		}
		
		$this->_writeToFile($hFile, ExjMapperXML::EndTag($sectionGlobal));
	}
}
?>