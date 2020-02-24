<?php

defined('_JEXEC') or die('Acceso Restringido');

class ExjFile {
	const MODE_FULL = 0777;
	
	public static function MkDir($pathDir, $recursive=false) {
		return mkdir($pathDir, self::MODE_FULL, $recursive);
	}

	public static function MkDirSilent($pathDir, $recursive=false) {
		return @mkdir($pathDir, self::MODE_FULL, $recursive);
	}

	public static function MkDirRecursive($pathDir) {
		return self::MkDir($pathDir, true);
	}

	public static function ValidateDir($pathDir) {
		if (file_exists($pathDir)) {
            return true;
        }

		return self::MkDirRecursive($pathDir);
	}

	public static function ChModFull($path) {
		return chmod($path, self::MODE_FULL);
	}
	
}

?>