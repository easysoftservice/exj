<?php
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
	Package:	Exj
	Defined In:	base.helper.php
	Class:		ExjHelper
	Subclasses:	
	Extends:	
 *
 */
class ExjHelper{
	
	/**
	 * Devuelve el valor de un parámetro desde una URL
	 *
	 * @param string $url
	 * @param string $param
	 * @param string $valueDefault
	 * @return string
	 */
	static function getParamFromUrl($url, $param, $valueDefault=''){
		if (!$param) {
			return '';
		}
		$param = trim($param);
		$param .= '=';
		
		$partes = split($param, $url);
		$value = $valueDefault;
		if (count($partes) >= 2) {
			$a = $partes[1];
			$partes = split("&", $a);
			if (count($partes) >= 1) {
				$value = $partes[0];
			}
		}
		
		return $value;
	}
	
	/**
	 * Devuelve el nombre del modulo de la UI
	 *
	 * @param string $option
	 */
	static function GetNamModUIFromOption($option){
		$nameModule = trim($option);
		if ($nameModule && strlen($nameModule) >= 6) {
			$nameModule = self::ClearPrefixComponentApp($nameModule);
			$nameModule = str_replace("_", " ", $nameModule);
			$nameModule = ucwords($nameModule);
			$nameModule = str_replace(" ", "", $nameModule);
		}
		if (!$nameModule) {
			$nameModule = "NoDefinido";
	//		echo "<br/>xxx test " . __METHOD__." option: $option";
		}
		
	//	echo "<br/>xxx test " . __METHOD__." option: $option nameModule: $nameModule";
		
		$nameModule = Exj::NS_UI_MODULES . ".$nameModule";
		return $nameModule;
	}
	
	static function GetPrefixComponentApp($nameComp){
		if (!$nameComp) {
			return '';
		}
		
		if (strpos($nameComp, Exj::PREFIX_COMP_FRAMEWORK) === 0) {
			return Exj::PREFIX_COMP_FRAMEWORK;
		}
		
		if (strpos($nameComp, Exj::PREFIX_COMP_APP) === 0) {
			return Exj::PREFIX_COMP_APP;
		}
		
		return '';
	}
	
	static function ClearPrefixComponentApp($nameComp){
		if (!$nameComp) {
			return $nameComp;
		}
		
		if (strpos($nameComp, Exj::PREFIX_COMP_APP) === 0) {
			$nameComp = substr($nameComp, strlen(Exj::PREFIX_COMP_APP));
			return $nameComp;
		}
		
		if (strpos($nameComp, Exj::PREFIX_COMP_FRAMEWORK) === 0) {
			$nameComp = substr($nameComp, strlen(Exj::PREFIX_COMP_FRAMEWORK));
		}
		
		return $nameComp;
	}
	
	
	/**
	 * Devueve nombre del módulo de la UI
	 *
	 * @param string $url
	 */
	static function getNamModUIFromURL($url){
		$option = ExjHelper::getParamFromUrl($url, 'option');
		return ExjHelper::GetNamModUIFromOption($option);
	}
	
	static function GetNamModUIFromGroupName($groupName){
		$groupName = trim($groupName);
		if (!$groupName) {
			return $groupName;
		}
		
		// echo "<br/>" . __METHOD__. "groupName: $groupName";
		
		if (!self::IsComponentApp($groupName)) {
		//	echo " No es componente de la Aplicación";
			return '';
		}
		
		$groupName = self::GetNamModUIFromOption($groupName);
		return $groupName;
	}
	
	public static function IsComponentApp($nameComponent){
		if (!$nameComponent) {
			return false;
		}
		if (strlen($nameComponent) <= 6) {
			return false;
		}
		
		if (strpos($nameComponent, Exj::PREFIX_COMP_APP) === 0) {
			return true;
		}
		
		if (strpos($nameComponent, Exj::PREFIX_COMP_FRAMEWORK) === 0) {
			return true;
		}
		
		return false;
	}
	
	static function convertCharsTildeToHTML(&$text){
		if (!$text) {
			return $text;
		}
		
		$text = str_replace("á", '&aacute;', $text);
		$text = str_replace("é", '&eacute;', $text);
		$text = str_replace("í", '&iacute;', $text);
		$text = str_replace("ó", '&oacute;', $text);
		$text = str_replace("ú", '&uacute;', $text);
		
		$text = str_replace("Á", '&Aacute;', $text);
		$text = str_replace("É", '&Eacute;', $text);
		$text = str_replace("Í", '&Iacute;', $text);
		$text = str_replace("Ó", '&Oacute;', $text);
		$text = str_replace("Ú", '&Uacute;', $text);
		
		$text = str_replace("ñ", '&ntilde;', $text);
		$text = str_replace("Ñ", '&Ntilde;', $text);
	//	$text = str_replace("¿", '&#191;', $text);
		$text = str_replace("¡", '&#161;', $text);
		$text = str_replace("ü", '&uuml;', $text);
		
		return $text;
	}

	
}

?>