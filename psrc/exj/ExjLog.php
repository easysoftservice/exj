<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class ExjLog {
	const END_LINE = "\n";

	public static function GetPathFileLog(){
		$nameFile = '_'. date("Y_m_d").".log";
		return Exj::GetPathDirStorageLogs($nameFile);
	}

	private static function _write($msg1, $msg2='', $title=''){
		if ($msg1 === null) {
			return;
		}

		/*
		if (is_object($msg1) && $msg1 instanceof ExjError) {
			$msg1 = $msg1->rendererMsg();
			$msg1 = str_replace('<br/>', "\n", $msg1);
		}
		*/

		if (is_object($msg1) || is_array($msg1)) {
			$msg1 = print_r($msg1, true);
		}

		if (is_object($msg2) || is_array($msg2)) {
			$msg2 = print_r($msg2, true);
		}

		$strContent = '';

		$pathFile = self::GetPathFileLog();
		if (file_exists($pathFile)) {
			$strContent .= self::END_LINE;
		}

		$strContent .= date('H:s:i');
		if ($title) {
			$strContent .= ' '. $title;
		}
		
		if ($msg1) {
			$strContent .= ': ' . $msg1;
		}

		if ($msg2) {
			$strContent .= ' ' . $msg2;
		}

		// utf8_encode($strContent);

		return file_put_contents($pathFile, $strContent, FILE_APPEND);
	}

	public static function info($msg1, $msg2=''){
		return self::_write($msg1, $msg2, 'INFO');
	}

	public static function error($msg1, $msg2=''){
		return self::_write($msg1, $msg2, 'ERROR');
	}

	public static function warning($msg1, $msg=''){
		return self::_write($msg, 'WARNING');
	}
}

?>