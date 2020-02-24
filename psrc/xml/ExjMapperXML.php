<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para el mapeo de data al formato XML
 *
 */
class ExjMapperXML extends ExjXML {
	private $_maps=null;
	
	public function register($nameProp, $nameField, $isValueDefault = false){
		$this->_register($nameProp, $nameField, ExjTypesVar::String(), $isValueDefault);
	}

	public function registerDate($nameProp, $nameField, $formarDate = '%d/%m/%Y'){
		$this->_register($nameProp, $nameField, ExjTypesVar::Date(false, $formarDate));
	}

	public function registerFloat($nameProp, $nameField, $isValueDefault = false){
		$this->_register($nameProp, $nameField, ExjTypesVar::Float(), $isValueDefault);
	}

	public function registerFloat2Decimals($nameProp, $nameField){
		$this->_register($nameProp, $nameField, ExjTypesVar::Float2Decimals());
	}
	
	private function _register($nameProp, $nameField, ExjTypesVar $typeVar, $isValueDefault = false){
		if (!$this->_maps) {
			$this->_maps = array();
		}
		
		$map = new stdClass();
		$map->nameProp = $nameProp;
		$map->nameField = $nameField;
		$map->isValueDefault = $isValueDefault;
		$map->typeVar = $typeVar;
		
		$this->_maps[] = $map;
	}
	
	// ExjTypesVar::Date()
	
	
	public function getSectionGlobal(){
		return 'app';
	}
	
	public function getAtributesSectionGlobal(){
		$attrs = array();
		
		$attrs[] = 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
		
		$attrs = implode(" ", $attrs);
		
		return $attrs;
	}
	
	public function getTagsHeaders(){
		return array();
	}
	
	protected function getSectionParent(){
		return 'items';
	}
	
	public function getSectionItem(){
		return 'item';
	}
	
	public function getTagsFooters(){
		return array();
	}
	
	public function parseItemToObject($dataItem){
		$itemXML = new stdClass();
		if (!$dataItem) {
			return $itemXML;
		}
		
		foreach ($this->_maps as $map) {
			$np = $map->nameProp;
			$nf = $map->nameField;
			$typeVar = $map->typeVar;
			
			$value = '';
			
			if ($map->isValueDefault) {
				$value = $nf;
			}
			else {
				$value = $dataItem->$nf;
			}
			
			$itemXML->$np = $typeVar->renderValue($value);
		}
		
		return $itemXML;
	}
	
	
	public function parseItemToXML($dataItem, $sectionItem=''){
		$itemObj = $this->parseItemToObject($dataItem);
		
		$itemsXMLs = array();
		
		$varsObj = get_object_vars($itemObj);
		foreach ($varsObj as $name => $value) {
			$itemsXMLs[] = '   '.self::StartTag($name) . $value . self::EndTag($name);
		}
		
		$itemsXMLs = implode(self::ENDLINE, $itemsXMLs);
		if (!$sectionItem) {
			return $itemsXMLs;
		}
		
		$strXML = self::StartTag($sectionItem) . self::ENDLINE;
		$strXML .= $itemsXMLs . self::ENDLINE;
		$strXML .= self::EndTag($sectionItem). self::ENDLINE;
		
		return $strXML;
	}
	

	public function getSectionParentStartToXML(){
		return self::StartTag($this->getSectionParent()). self::ENDLINE;
	}

	public function getSectionParentEndToXML(){
		return self::EndTag($this->getSectionParent());
	}
}
?>