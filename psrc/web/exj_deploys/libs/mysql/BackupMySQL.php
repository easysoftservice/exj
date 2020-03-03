<?php

/* 06/10/2012. version 1.0
 * Autor: Byron Córdova
 * Genera un respaldo de la base de datos actual
 * 
 */
class BackupMySQL extends ExjObject {
	private $_newNameDB='dbx', $_outputDir='', $_nameDBCurrent='';
	private $_contentFile = '', $_SEPARATOR_LINE = "\r\n";
	private $_tables = null;
	private $_prefixOnlyTable = '';
	private $_nameFileBKDB='bk.sql';
	private $_tablesOnlyStructure=null;
	
	public function __construct($newNameDB, $outputDir='.', $prefixOnlyTable='jos_t')
	{
    	
		$this->_newNameDB = $newNameDB;
		$this->_outputDir = $outputDir;
		$this->_tables = array();
		$this->_prefixOnlyTable = $prefixOnlyTable;
		
		$cfgApp = new JConfig();
    	$this->_nameDBCurrent = $cfgApp->db;
    	
    	$this->_nameFileBKDB = '_bk_' . $this->_newNameDB. '_'. Exj::GetDateTime('%d%m%Y %H%M').'.sql';
	}

	public function addTableOnlyStructure($nameTable){
		if(!$this->_tablesOnlyStructure){
			$this->_tablesOnlyStructure = array();
		}
		
		$this->_tablesOnlyStructure[] = $nameTable;
	}
	
	
	protected function readTables(){
		$db = Exj::InstanceDatabase();
		
		$where = array();
		$where[] = "t.TABLE_SCHEMA = '$this->_nameDBCurrent'";
		$where[] = "col.TABLE_SCHEMA = '$this->_nameDBCurrent'";
		if ($this->_prefixOnlyTable) {
			$where[] = "t.TABLE_NAME LIKE '$this->_prefixOnlyTable%'";
		}
		
		$where = implode(" AND ", $where);
		
		$query = "SELECT
  t.TABLE_NAME, t.ENGINE, t.ROW_FORMAT, t.AUTO_INCREMENT,
  t.TABLE_COMMENT, cll.CHARACTER_SET_NAME AS charset_table,
  col.COLUMN_NAME, col.COLUMN_DEFAULT, col.IS_NULLABLE,
  col.COLUMN_TYPE, col.CHARACTER_SET_NAME AS charset_col,
  col.COLUMN_KEY, col.EXTRA, col.COLUMN_COMMENT, col.DATA_TYPE
FROM
  information_schema.TABLES t INNER JOIN
  information_schema.COLLATIONS cll ON t.TABLE_COLLATION = cll.COLLATION_NAME
  INNER JOIN
  information_schema.COLUMNS col ON t.TABLE_NAME = col.TABLE_NAME
WHERE
  $where 
ORDER BY
  t.TABLE_NAME, col.ORDINAL_POSITION";
		
		
		$tablesCols = $db->loadObjectList($query);
		if ($db->getErrorMsg()) {
			return false;
		}
		
		if (count($tablesCols) <= 0){
			$this->_setError("No se encontraron tablas");
			return false;
		}
		
		// ---------------------------------------------------------------
		$where = array();
		$where[] = "stt.TABLE_SCHEMA = '$this->_nameDBCurrent'";
		
		if ($this->_prefixOnlyTable) {
			$where[] = "stt.TABLE_NAME LIKE '$this->_prefixOnlyTable%'";
		}
		
		$where = implode(" AND ", $where);
		
		$query = "SELECT
  stt.TABLE_NAME, stt.COLUMN_NAME, stt.INDEX_NAME, stt.NULLABLE,
  stt.INDEX_TYPE, stt.COMMENT, stt.NON_UNIQUE, stt.SUB_PART 
FROM
  information_schema.STATISTICS stt
WHERE
  $where 
ORDER BY
  stt.TABLE_NAME, stt.COLUMN_NAME, stt.SEQ_IN_INDEX";
		$indexsCols = $db->loadObjectList($query);
		if ($db->getErrorMsg()) {
			return false;
		}
		
		foreach ($tablesCols as $tableCols) {
			$tableName = trim($tableCols->TABLE_NAME);
		//	echo "<br/>tableName: $tableName";
			
			$keyIndex = '';
			$isUniqueIndex = false;
			$keyIndexSubPart = null;
			foreach ($indexsCols as $indexCol) {
				if ($tableName != trim($indexCol->TABLE_NAME)) {
					continue;
				}
				if ($indexCol->COLUMN_NAME == $tableCols->COLUMN_NAME) {
					$keyIndex = $indexCol->INDEX_NAME;
					$keyIndexSubPart = $indexCol->SUB_PART;
					
					$isUniqueIndex = intval($indexCol->NON_UNIQUE);
					$isUniqueIndex = ($isUniqueIndex ? false:true); 
					break;
				}
			}
			
			if (isset($this->_tables[$tableName])) {
				$t = $this->_tables[$tableName];
				$t->addCol($tableCols->COLUMN_NAME, $tableCols->COLUMN_TYPE, $tableCols->DATA_TYPE, $tableCols->IS_NULLABLE, $tableCols->EXTRA, $tableCols->COLUMN_DEFAULT, $tableCols->COLUMN_COMMENT, $keyIndex, $isUniqueIndex, $keyIndexSubPart);
			}
			else {
			//	echo "<br/>add tabla: $tableName";
				$t = new BKTable();
				$t->name = $tableName;
				$t->engine = $tableCols->ENGINE;
				$t->autoIncrement = $tableCols->AUTO_INCREMENT;
				$t->comment = $tableCols->TABLE_COMMENT;
				$t->characterSetName = $tableCols->charset_table;
				
				$t->onlyStructure = false;
				if ($this->_tablesOnlyStructure) {
					if (in_array($tableName, $this->_tablesOnlyStructure)) {
						$t->onlyStructure = true;
					}
				}
				
				$t->addCol($tableCols->COLUMN_NAME, $tableCols->COLUMN_TYPE, $tableCols->DATA_TYPE, $tableCols->IS_NULLABLE, $tableCols->EXTRA, $tableCols->COLUMN_DEFAULT, $tableCols->COLUMN_COMMENT, $keyIndex, $isUniqueIndex, $keyIndexSubPart);
				
				$this->_tables[$tableName] = $t;
			}
		}
		
		return true;
	}
	
	private function _setError($msgError){
		Exj::SetErrorValidating("$msgError");
	}

	private function _addHeader(){
		$this->_addComment("Respaldo MySQL dump 1.0");
		$this->_addComment();
		$this->_addComment("-----------------------------");
		$this->_addComment("Generado por EasySoft Service");
		$this->_addComment("Tablas ". count($this->_tables));
		$this->_addNewLine();

		$this->_addCommandLineHidden("40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT");
		$this->_addCommandLineHidden("40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS");
		$this->_addCommandLineHidden("40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION");
		$this->_addCommandLineHidden("40101 SET NAMES utf8");
		$this->_addCommandLineHidden();
		$this->_addCommandLineHidden("40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0");
		$this->_addCommandLineHidden("40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");
		$this->_addCommandLineHidden("40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO'");
		$this->_addCommandLineHidden();
		
		$this->_addComment();
		$this->_addComment("Esquema $this->_newNameDB");
		$this->_addComment();
	}
	
	private function _addFooter(){
		$this->_addCommandLineHidden();
		$this->_addCommandLineHidden("40101 SET SQL_MODE=@OLD_SQL_MODE");
		$this->_addCommandLineHidden("40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
		$this->_addCommandLineHidden("40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS");
		$this->_addCommandLineHidden("40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT");
		$this->_addCommandLineHidden("40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS");
		$this->_addCommandLineHidden("40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION");
		$this->_addCommandLineHidden("40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT");
		$this->_addCommandLineHidden();
	}
	
	private function _addCommandLineHidden($cmdText=''){
		if (!$cmdText) {
			$this->_addNewLine();
			return;
		}
		$this->_contentFile .= "/*!$cmdText */;" . $this->_SEPARATOR_LINE;
	}
	
	
	private function _addCommandLine($cmdText=''){
		if (!$cmdText) {
			$this->_addNewLine();
			return;
		}
		$this->_contentFile .= "$cmdText;" . $this->_SEPARATOR_LINE;
	}
	
	
	private function _addComment($txt=''){
		$this->_contentFile .= "-- $txt" . $this->_SEPARATOR_LINE;
	}
	private function _addNewLine(){
		$this->_contentFile .= $this->_SEPARATOR_LINE;
	}
	
	public function executeBK(){
		if (!$this->readTables()) {
			return false;
		}
		
		$this->_addHeader();

		$this->_addCommandLine("CREATE DATABASE /*!32312 IF NOT EXISTS*/ $this->_newNameDB");
		$this->_addCommandLine("USE $this->_newNameDB");
		$this->_addNewLine();
		
		$haveError = false;
		foreach ($this->_tables as $keyNameTable => $t) {
			$this->_addCommandLine($t->buildStructure($this->_newNameDB, $this->_SEPARATOR_LINE));
			$this->_addNewLine();
			
			$dumpingData = $t->buildDumpingData($this->_newNameDB, $this->_SEPARATOR_LINE);
			if ($dumpingData === false) {
				$haveError = true;
				break;
			}
			if (!$dumpingData) {
				continue;
			}
			$this->_addCommandLine($dumpingData);
		}
		
		if ($haveError) {
			return false;
		}
		
		$this->_addFooter();
		
		return $this->saveFile();
	}
	
	public function getNameFileBKDB(){
		return $this->_nameFileBKDB;
	}
	
	public function getFullPathFileBKDB(){
		return $this->_outputDir . '/'. $this->getNameFileBKDB();
	}

    protected function saveFile()
    {
        if (!$this->_contentFile) return false;
 
        try
        {
            $handle = fopen($this->getFullPathFileBKDB(),'w+');
            fwrite($handle, $this->_contentFile);
            fclose($handle);
        }
        catch (Exception $e)
        {
            var_dump($e->getMessage());
            return false;
        }
 
        return true;
    }	
}

class BKTable{
	public $name;
	public $engine;
	public $autoIncrement;
	public $characterSetName;
	public $comment='';
	public $LIMIT=60;
	
	public $onlyStructure=false;
	private $_cols = null;
	
	public function __construct(){
		$this->_cols = array();
	}
	
	public function addCol($name, $type, $dataType, $isNullable, $extras, $default=null, $comment='', $keyIndex='', $isUniqueIndex=false, $keyIndexSubPart=null){
		$col = new stdClass();
		
		if ($isNullable) {
			if (strtoupper($isNullable) == 'NO') {
				$isNullable = false;
			}
		}
		
		$col->name = $name;
		$col->type = $type;
		$col->isNullable = $isNullable;
		$col->extras = $extras;
		$col->default = $default;
		$col->comment = $comment;
		$col->keyIndex = $keyIndex;
		$col->isUniqueIndex = $isUniqueIndex;
		$col->keyIndexSubPart = $keyIndexSubPart;
		$col->dataType = strtolower($dataType);
		
		// echo "<br/>name: $name keyIndex: $keyIndex";
		
		$this->_cols[] = $col;
	}
	
	public function buildStructure($nameDB = '',$SEPARATOR_LINE=null){
		if (!$SEPARATOR_LINE) {
			$SEPARATOR_LINE = "\n";
		}
		$sql = array();
		
		$nameTable = $nameDB;
		if ($nameTable) {
			$nameTable .= '.';
		}
		$nameTable .= $this->name;
		
		$sql[] = "--";		
		$sql[] = "-- Estructura para la tabla $nameTable";		
		$sql[] = "--";		
		
		$sql[] = "DROP TABLE IF EXISTS $nameTable;";
		$sql[] = "CREATE TABLE  $nameTable (";
		// columnas
		$keys = array();
		$primariesKeys = array();
		$colsSQL = array();
		$uniquesKeys = array();
		foreach ($this->_cols as $col) {
			$nameColSQL = "`$col->name`";
			$colSQL = "   $nameColSQL $col->type";
			
			if (!$col->isNullable) {
				$colSQL .= " NOT NULL";
			}
			
			if ($col->default === null || $col->default == 'NULL') {
				if ($col->isNullable) {
					$colSQL .= " default NULL";	
				}
			}
			else {
				$colSQL .= " default '$col->default'";
			}
			
			
			if ($col->extras) {
				$colSQL .= " $col->extras";
			}
			
			if ($col->comment) {
				$colSQL .= " COMMENT '$col->comment'";
			}
			
			$colsSQL[] = $colSQL;
			
			if ($col->keyIndex) {
				$keyIndex = $col->keyIndex;
				$nameColIndexSQL = $nameColSQL;
				if ($col->keyIndexSubPart) {
					$nameColIndexSQL .= "($col->keyIndexSubPart)";
				}
				if ($keyIndex == 'PRIMARY') {
					$primariesKeys[] = $nameColIndexSQL;
				}
				else {
					if ($col->isUniqueIndex) {
						$uniquesKeys[$keyIndex][] = $nameColIndexSQL;
					}
					else {
						$keys[$keyIndex][] = $nameColIndexSQL;
					}
				}
			}
		}
		
		if(count($primariesKeys)){
			$colsSQL[] = '   PRIMARY KEY (' .implode(',', $primariesKeys).')';
		}
		
		if (count($uniquesKeys)) {
			foreach ($uniquesKeys as $keyIndex => $fields) {
				$colsSQL[] = "   UNIQUE KEY $keyIndex (" .implode(',', $fields).')';
			}
		}
		
		if(count($keys)){
			foreach ($keys as $keyIndex => $fields) {
				$colsSQL[] = "   KEY $keyIndex (" . implode(',', $fields) . ')';
			}
		}
		$sql[] = implode(','.$SEPARATOR_LINE, $colsSQL);

		
		$sql[] = ") ". $this->_getOptionsCreate();
		
		$sql = implode($SEPARATOR_LINE, $sql);
		return $sql;
	}
	
	private function _getOptionsCreate(){
		$optionsCreate = array();
		if ($this->engine) {
			$optionsCreate[] = "ENGINE=$this->engine";
		}
		if ($this->autoIncrement) {
			$optionsCreate[] = "AUTO_INCREMENT=$this->autoIncrement";
		}
		if ($this->characterSetName) {
			$optionsCreate[]= "DEFAULT CHARSET=$this->characterSetName";
		}
		if ($this->comment) {
			$optionsCreate[]= "COMMENT '$this->comment'";
		}
		
		return implode(' ', $optionsCreate);
	}
	
	private function _getFieldsSQL(){
		$fields = array();
		foreach ($this->_cols as $col) {
			$fields[] = "`$col->name`";
		}
		return implode(',', $fields);
	}
	
	private $_totalReg=0;
	
	public function buildDumpingData($nameDB = '',$SEPARATOR_LINE=null){
		if ($this->onlyStructure) {
			return '';
		}
		
		$query = "SELECT COUNT(*) FROM $this->name";
		$db = Exj::InstanceDatabase();
		$this->_totalReg = $db->loadResult($query);
		if ($db->getErrorMsg()) {
			return false;
		}
		if ($this->_totalReg <= 0) {
			return '';
		}
		
		$nameTable = $nameDB;
		if ($nameTable) {
			$nameTable .= '.';
		}
		$nameTable .= $this->name;
		
		$sql = array();
		$sql[] = "--";
		$sql[] = "-- Dumping data para tabla $nameTable Registros $this->_totalReg";
		$sql[] = "--";
		$sql[] = "";
		$sql[] = "/*!40000 ALTER TABLE $this->name DISABLE KEYS */;";
		
		// Inserts
		$fields = $this->_getFieldsSQL();

		$haveError = false;
		$LIMIT_BLOQ = 60000;
		while (--$LIMIT_BLOQ) {
			$bloqDataSQL = $this->_getBloqDataSQL($SEPARATOR_LINE);
			if ($bloqDataSQL === false) {
				$haveError = true;
				break;
			}
			if (!$bloqDataSQL) {
				break;
			}
			
			$sql[] = "INSERT INTO $nameTable ($fields) VALUES ";			
			$sql[] = $bloqDataSQL. ';';
		}
		
		if ($haveError) {
			return false;
		}
		
		if ($LIMIT_BLOQ <= 0){
			$sql[] = "/* SE HA FORZADO LA SALIDA LIMITE DE BLOQUES 60000 */";
		}
		
		
		$sql[] = "/*!40000 ALTER TABLE $this->name ENABLE KEYS */";
		$sql = implode($SEPARATOR_LINE, $sql);
		return $sql;
	}
	
	private $_offsetData=0;
	private function _getBloqDataSQL($SEPARATOR_LINE){
		if ($this->_offsetData > $this->_totalReg) {
			return '';
		}
		
		$db = Exj::InstanceDatabase();
		$query = "SELECT * FROM $this->name LIMIT $this->LIMIT OFFSET $this->_offsetData";
		$itemsData = $db->loadObjectList($query);
		if ($db->getErrorMsg()) {
			return false;
		}
		
		if (count($itemsData) <= 0) {
			echo "TEST se ha ejecutado select pasó el offset";
			return '';
		}
		
		$this->_offsetData += $this->LIMIT;
		
		$bloqData = array();
		foreach ($itemsData as $itemData) {
			$rowData = array();
			foreach ($this->_cols as $col) {
				$colName = $col->name;
				$value = $itemData->$colName;
				if ($value === null) {
					$value = "NULL";
				}
				else {
					switch ($col->dataType) {
						case 'text':
						case 'enum':
						case 'datetime':
						case 'date':
						case 'time':
						case 'varchar':
						case 'mediumtext':
						case 'tinytext':
						case 'longblob':
							if ($value) {
								$value = addslashes($value);
								$value = str_replace("\r\n", '\r\n', $value);
								$value = str_replace("\n", '\n', $value);
							}
							
							$value = "'$value'";
						break;
					}
				}
				$rowData[] = $value;
			}
			$rowData = implode(',', $rowData);
			$bloqData[] = " ($rowData)";
		}

		return implode(','. $SEPARATOR_LINE, $bloqData);
	}
	
}

?>