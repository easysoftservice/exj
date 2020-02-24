<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para la gestión del formato XML
 *
 */
class ExjXML extends ExjObject {
	const ENDLINE = PHP_EOL;

	static function StartTag($section, $attrs=''){
		if (!$attrs) {
			return "<$section>";
		}
		
		return "<$section $attrs>" . self::ENDLINE;
	}
	

	static function EndTag($section){
		return "</$section>";
	}

	static function Tag($section, $value, $addEndLine=true){
		$tag = self::StartTag($section) . $value . self::EndTag($section);
		if ($addEndLine) {
			$tag .= self::ENDLINE;
		}
		return $tag;
	}
	
}
?>