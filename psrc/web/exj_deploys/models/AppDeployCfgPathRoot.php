<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class AppDeployCfgPathRoot {
	private $_dirRoot;
	private $_extsFilesProcess = null;
	private $_subFolderDest = '';

	public $allowFolderRootNames = array();
	public $exceptCopyFileExts = array();
	public $exceptCopyFileNames = array();
	public $exceptCopyFolderNames = array();
	public $exceptEncodeFileNames = array();
	public $exceptEncodeFolderNames = array();
	public $allowCopyFilesExtras = null;
	public $allowOnlyOfuscarFileNames = array();
	private $_exceptEncodeFolderNamesFind = null;

	public function __construct($dirRoot) {
		$this->_dirRoot = $dirRoot;
		if (!file_exists($this->_dirRoot)) {
			throw new Exception("Error Deploy. dirRoot: $dirRoot no existe", 1);
		}
	}

	public function setSubFolderDest($value) {
		$this->_subFolderDest = $value;
		return $this;
	}

	public function rendererFolterDest($pathDir) {
		if ($this->_subFolderDest) {
			$pathDir = ExjString::ConcatPaths($pathDir, $this->_subFolderDest);
			ExjFile::ValidateDir($pathDir);
		}

		return $pathDir;
	}

	public function copyProps(AppDeployCfgPathRoot $depPath) {
		foreach ($depPath as $prop => $value) {
			if (substr($prop, 0, 1) == '_') {
				continue;
			}

			$this->$prop = $value;
		}

		return $this;
	}

	public function getDirRoot() {
		return $this->_dirRoot;
	}

	public function setExtsFilesProcess($value) {
		if ($value && is_string($value)) {
			$value = explode(',', $value);
		}

		$this->_extsFilesProcess = (empty($value) ? null : $value);
		return $this;
	}

	public function canProcessExtFile($value) {
		if (!$value) {
			return false;
		}

		if (empty($this->_extsFilesProcess)) {
			return true;
		}

		return in_array($value, $this->_extsFilesProcess);
	}

	public static function InArray($value, $items) {
		if (empty($items)) {
			return false;
		}

		return in_array($value, $items);
	}

	public static function InSearchArray($value, $items) {
		if (empty($items)) {
			return false;
		}

		$found = false;
		foreach ($items as $item) {
			if (strpos($value, $item) !== false) {
				$found = true;
				break;
			}
		}

		return $found;
	}

	
	public function allowFolderRoot($value) {
		if (!$value || $value == '.' || $value == '..') {
			return false;
		}

		if (empty($this->allowFolderRootNames)) {
			return true;
		}

		return self::InArray($value, $this->allowFolderRootNames);
	}

	public function canCopyFile($value) {
		$ext = self::GetExtensionFile($value);
		if (!$ext || self::IsCompressFile($value)) {
			return false;
		}

		if (self::InArray($ext, $this->exceptCopyFileExts)) {
			return false;
		}
		
		return !self::InSearchArray($value, $this->exceptCopyFileNames);
	}

	public function canCopyFolder($value, $pathDir) {
		if (!$value || $value == '.' || $value == '..') {
			return false;
		}
	
		if (ExjString::EndWith($pathDir, $this->exceptCopyFolderNames)) {
			// ExjLog::info("canCopyFolder. excluye: $pathDir");
			return false;
		}

		return true;
	}

	public function canOnlyOfuscarFile($value, $pathFile) {
		if (!$value) {
			return false;
		}

		return self::InArray($value, $this->allowOnlyOfuscarFileNames);
	}

	public function canEncodeFile($value, $pathFile) {
		if (!$value) {
			return false;
		}

		if (self::InArray($value, $this->exceptEncodeFileNames)) {
			return false;
		}

		$dirFile = dirname($pathFile);

		return !$this->foundInItems(
			'exceptEncodeFolderNames',
			$dirFile
		);
	}

	protected function foundInItems($namePropItems, $toFind) {
		if (!$toFind) {
			return false;
		}

		$valuesItems = $this->$namePropItems;

		$namePropContent = '_'.$namePropItems.'Find';
		$valuesContents = array();
		if (isset($this->$namePropContent)) {
			$valuesContents = $this->$namePropContent;
		}
		else {
			$valuesContents = array();
			foreach ($valuesItems as $idxSrc => $strSource) {
				if (strpos($strSource, '*') !== false) {
					$strFind = str_replace('*', '', $strSource);
					if ($strFind) {
						$valuesContents[] = $strFind;
					}

					unset($valuesItems[$idxSrc]);
				}
			}

			if (!empty($valuesContents)) {
				$valuesItems = array_values($valuesItems);
				$this->$namePropItems = $valuesItems;
			}

			$this->$namePropContent = $valuesContents;

			/*
			ExjLog::info(
				"foundInItems. $namePropItems values: ". print_r($valuesItems, true).
				" valuesContents: " . print_r($valuesContents, true)
			);
			*/
		}

		if ($valuesItems && ExjString::EndWith($toFind, $valuesItems)) {
			return true;
		}

		if ($valuesContents && ExjString::Content($toFind, $valuesContents)) {
			/*
			ExjLog::info(
				"foundInItems. found content: toFind: $toFind"
			);
			*/
			return true;
		}

		return false;
	}
	

	public static function IsFileJs($value) {
		return (strlen($value) >= 4 && substr($value, -3) == '.js');
	}

	public static function IsFilePhp($value) {
		return (strlen($value) >= 4 && substr($value, -4) == '.php');
	}

	public static function GetExtensionFile($value) {
		$ext = '';
		if (!$value) {
			return $ext;
		}

		$posPoint = strrpos($value, '.');
		if ($posPoint !== false) {
			$ext = substr($value, $posPoint+1);
		}

		return $ext;
	}

	public static function IsCompressFile($value) {
		if (!$value || strlen($value) <= 3) {
			return false;
		}

		$ext = self::GetExtensionFile($value);
		if (!$ext) {
			return false;
		}

		return in_array(strtolower($ext), array(
			'zip',
			'rar',
			'gz',
			'iso'
		));
	}
}

?>