<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para capa de datos. Las capas de datos podrían deben heredar de esta clase.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[componente]/data/[componente].data.php
 * Nombrado de la clase: Debe tener el formato: class App[Componente]Data extends ExjData
 * Por lo general los métodos creados en la capa de datos, son estáticos, de esta forma, la capa es independiente
 */
class ExjData extends ExjObject {
	const COLOR_INACTIVE='red';
	const COLOR_DEFAULT='black';
	const COLOR_DISABLED='#a0a0a0';
	
	private $_cacheSQL = null;
	
	static function setError($msg){
		global $exj;
		$exj->setErrorDB($msg);
	}
	
	static function RenderFechasRangos($date1, $date2){
		return ExjUtil::RenderFechasRangos($date1, $date2);
	}
	
	static function FechasDiferencia($date1, $date2){
		return ExjUtil::FechasDiferencia($date1, $date2);
	}
	
	/**
	 * Setea propiedades para selección de un item y propiedad de color del item
	 *
	 * @param object $item
	 * @param string $msg Si el mensaje es un string vacio se puede seleccionar el item
	 * @param string $color
	 */
	public static function SetterItemDisableSelect(&$item, $msg='', $color=''){
		$item->exjDisableMsg = $msg;
		if ($msg) {
			$item->exjDisableSelect = 1;
		}
		
		if (!isset($item->exjDisableSelect)) {
			$item->exjDisableSelect = 0;
		}
		
		if ($item->exjDisableSelect) {
			if (!$color) {
				$color = self::COLOR_DISABLED;
			}
		}
		else {
			if (!$color || ($color == self::COLOR_DISABLED)) {
				$color = self::COLOR_DEFAULT;
			}
		}
		
		self::SetterItemColor($item, $color);
	}
	
	public static function SetterItemDisableSelectInactive(&$item, $msg=''){
		self::SetterItemDisableSelect($item, $msg, self::COLOR_INACTIVE);
	}
	
	/**
	 * Setea propiedad color al objeto pasado por parámetro
	 *
	 * @param object $item
	 * @param string $color
	 * @param bool $overwriteProp Por defecto true
	 */
	public static function SetterItemColor(&$item, $color='', $overwriteProp=true){
		if (!$color) {
			$color = self::COLOR_DEFAULT;
		}
		
		if (!isset($item->color)) {
			$item->color = $color;
		}
		else {
			if (!$item->color) {
				$item->color = $color;
			}
			elseif ($overwriteProp){
				$item->color = $color;
			}
		}
	}
	
	/**
	 * Adiciona a cache
	 *
	 * @param string $scope
	 * @param string $key
	 * @param mixed $value
	 */
	public function addCacheSQL($scope, $key, $value){
		$itemCache = new stdClass();
		$itemCache->key = $key;
		$itemCache->value = $value;
		
		if(!$this->_cacheSQL){
			$this->_cacheSQL = array();
		}
		
		$this->_cacheSQL[$scope][] = $itemCache;
	}

	/**
	 * Retorna el valor guardado en cache
	 *
	 * @param string $scope
	 * @param string $key
	 * @param mixed $valueDefault Por defecto null
	 * @return mixed Valor guadado en cache si no existe retorna el valor por defecto
	 */
	public function getValueFromCacheSQL($scope, $key, $valueDefault = null){
		if (!$this->_cacheSQL) {
			return $valueDefault;
		}
		
		$valueSQL = $valueDefault;
		foreach ($this->_cacheSQL as $scopeCache => $itemsCache) {
			foreach ($itemsCache as $itemCache) {
				if ($scopeCache != $scope) {
					continue;
				}
				
				if ($itemCache->key == $key) {
					$valueSQL = $itemCache->value;
					// echo "<br/>Recuperando Cache scope: $scope key: $key valueSQL: $valueSQL ";
					break;
				}
			}
		}
		
		return $valueSQL;
	}	
	
	/**
	 * Ordena el arreglo items
	 *
	 * @param array $items Pasado por referencia
	 * @param string $fieldSort Si no se define este se lee desde parametros, parámetro: sort
	 * @param string $dir Si no se define este se lee desde parámetros, parámetro; dir
	 * @param bool $isNumeric null auto
	 * @return array de indices, si no se ordenó retorna false
	 */
	static function SortItems(&$items, $fieldSort='', $dir='', $isNumeric=null){
		if (!$fieldSort) {
			$fieldSort = Exj::InstanceRequest()->getParam('sort');
		}
		
		if (!$fieldSort) {
			return false;
		}
		
		if (!$dir) {
			$dir = Exj::InstanceRequest()->getParam('dir', 'ASC');
		}
		
		if (!$dir) {
			$dir = 'ASC';
		}
		
		$dir = strtoupper($dir);
		
		return ExjUtil::SortArrayOfObjects($items, $fieldSort, $isNumeric, ($dir == 'ASC'));
	}
	
	public static function NewItemLookup($value, $text=''){
		if (!$text) {
			$text = $value;
		}
		
		$item = new stdClass();
		$item->value = $value;
		$item->text = $text;
		
		return $item;
	}
	
	public static function AddPropLastChangeToItems(&$items, $nameFieldUsrChange='usr_change', $namePropLastChange = 'last_change', $nameFieldDateRegister='modificado_dt'){
		if (count($items) == 0) {
			return ;
		}
		
		foreach ($items as &$item) {
			$item->$namePropLastChange = '';
			if ($item->$nameFieldDateRegister) {
				$item->$namePropLastChange = ExjDate::ConvertToDateTimeDisplay($item->$nameFieldDateRegister);
			}
			if ($item->$nameFieldUsrChange) {
				$item->$namePropLastChange .= ' por ' . $item->$nameFieldUsrChange;				
			}
		}
	}
	
	/**
	 * Adiciona la propiedad _ord según el parámetro de paginación start
	 *
	 * @param array $items
	 */
	static function AddPropOrdToItems(&$items){
		if (count($items) == 0) {
			return ;
		}
		
		$start = ExjRequest::GetParamPagingStart();
		$numOrd = 1;
		if ($start > 0) {
			$numOrd += $start;
		}
		
		foreach ($items as &$item) {
			$item->_ord = $numOrd++;
		}
	}
	
	/**
	 * Adiciona una fila para sumatoria de la lista
	 *
	 * @param array $items
	 * @param string $nameFieldID Campo clave de la lista a este se lo pondrá un valor -1
	 * @param string $nameFieldTotal Nombre del campo donde se pondrá el texto Total
	 * @param string $strTOTAL Defecto TOTAL
	 * @param mixed $fieldsSummary Puede ser string campos separados por comas o array
	 * @return object La fila summary que se adicionó
	 */
	public static function AddRowSummary(&$items, $nameFieldID, $nameFieldTotal, $fieldsSummary, $strTOTAL='TOTAL')
	{
		if (count($items) == 0) {
			return null;
		}
		
		if (!is_array($fieldsSummary)) {
			$fieldsSummary = explode(',', $fieldsSummary);
		}
		
		$totals = array();
		foreach ($items as &$item) {
			$item->isRowSummary = false;
			
			foreach ($fieldsSummary as $fieldSummary) {
				$fieldSummary = trim($fieldSummary);
				if (!isset($totals[$fieldSummary])) {
					$totals[$fieldSummary] = 0;
				}
				
				$totals[$fieldSummary] += $item->$fieldSummary;
			}
		}
		
		$rowSummary = self::NewRowSummary($nameFieldID, $nameFieldTotal, $strTOTAL);
		// $rowSummary->num_bill_days = $total;
		
		foreach ($totals as $field => $total) {
			$rowSummary->$field = $total;
		}
		
		$items[] = $rowSummary;
		
		return $rowSummary;
	}
	
	static function NewRowSummary($nameFieldID='', $nameFieldTotal='', $strTOTAL='TOTAL'){
		$rowSummary = new stdClass();
		$rowSummary->isRowSummary = true;
		if ($nameFieldID) {
			$rowSummary->$nameFieldID = -1;
		}
		if ($nameFieldTotal) {
			$rowSummary->$nameFieldTotal = $strTOTAL;
		}
		
		return $rowSummary;
	}
	
	/**
	 * Obtiene el siguiente valor de una tabla
	 *
	 * @param string $nameTable
	 * @param string $fieldInt
	 * @return int Si ocurre error false
	 */
	public static function GetNextValueTable($nameTable, $fieldInt){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT Max(t.$fieldInt)+1 AS next_value FROM $nameTable t";
		
		$nextValue = $db->loadResult($query);
		if (!$db->isValid()) {
			return false;
		}
		
		if (!$nextValue) {
			$nextValue = 1;
		}
		
		return $nextValue;
	}
	
	public static function GetColumnsOfTable($nameTable){
		$db = Exj::InstanceDatabase();
		
		$query = "SELECT cols.COLUMN_NAME AS col_name,
		    cols.COLUMN_DEFAULT AS col_def, cols.IS_NULLABLE AS is_col_nullable, cols.DATA_TYPE,
		    cols.CHARACTER_MAXIMUM_LENGTH, cols.CHARACTER_OCTET_LENGTH,
		    cols.NUMERIC_PRECISION, cols.NUMERIC_SCALE,
		    cols.COLUMN_TYPE, cols.COLUMN_KEY, cols.EXTRA,
		    cols.COLUMN_COMMENT, refs.REFERENCED_TABLE_NAME, refs.REFERENCED_COLUMN_NAME,
		    ref_const1.UPDATE_RULE, ref_const1.DELETE_RULE,
		    col_usage.TABLE_NAME AS table_name_usage, col_usage.COLUMN_NAME AS column_name_usage,
		    ref_const.UPDATE_RULE AS update_rule_constrain, ref_const.DELETE_RULE AS delete_rule_constrain 
		FROM INFORMATION_SCHEMA.COLUMNS as cols
		LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS refs 
		    ON (refs.TABLE_SCHEMA=cols.TABLE_SCHEMA
		    AND refs.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
		    AND refs.TABLE_NAME=cols.TABLE_NAME
		    AND refs.COLUMN_NAME=cols.COLUMN_NAME)
		LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS ref_const1
		    ON (ref_const1.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
		    AND ref_const1.CONSTRAINT_NAME=refs.CONSTRAINT_NAME)
		LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE AS col_usage
		    ON (col_usage.TABLE_SCHEMA=cols.TABLE_SCHEMA
		    AND col_usage.REFERENCED_TABLE_SCHEMA=cols.TABLE_SCHEMA
		    AND col_usage.REFERENCED_TABLE_NAME=cols.TABLE_NAME
		    AND col_usage.REFERENCED_COLUMN_NAME=cols.COLUMN_NAME)
		LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS AS ref_const 
		    ON (ref_const.CONSTRAINT_SCHEMA=cols.TABLE_SCHEMA
		    AND ref_const.CONSTRAINT_NAME=col_usage.CONSTRAINT_NAME)";
		
		$query .= " WHERE (cols.TABLE_SCHEMA=DATABASE() AND cols.TABLE_NAME='$nameTable')";
		$query .= " ORDER BY cols.ORDINAL_POSITION";
		
		$itemsAll = $db->loadObjectList($query);
		if (!$db->isValid()) {
			return false;
		}
		
		$items = array();
		foreach ($itemsAll as $itemAll) {
			$col_name = $itemAll->col_name;
			
			if (isset($items[$col_name])) {
				$refCol = $items[$col_name];
				if (!isset($refCol->usages) || !$refCol->usages) {
					$refCol->usages = array();
				}
				
				$usage = new stdClass();
				$usage->tableName = $itemAll->table_name_usage;
				$usage->colName = $itemAll->column_name_usage;
				$usage->ruleUpdate = $itemAll->update_rule_constrain;
				$usage->ruleDelete = $itemAll->delete_rule_constrain;
				
				$refCol->usages[] = $usage;
			}
			else {
				$item = new stdClass();
				$item->colDefault = $itemAll->col_def;
				$item->isNullable = ($itemAll->is_col_nullable != 'NO' ? 1:0);
				$item->dataType = $itemAll->DATA_TYPE;
				$item->charMaxLen = $itemAll->CHARACTER_MAXIMUM_LENGTH;
				$item->charOctLen = $itemAll->CHARACTER_OCTET_LENGTH;
				$item->numPrecision = $itemAll->NUMERIC_PRECISION;
				$item->numScale = $itemAll->NUMERIC_SCALE;
				$item->colType = $itemAll->COLUMN_TYPE;
				$item->colKey = $itemAll->COLUMN_KEY;
				$item->extra = $itemAll->EXTRA;
				$item->colComment = $itemAll->COLUMN_COMMENT;
				
				$item->isPrimaryKey = (($item->colKey == 'PRI' && $item->extra == 'auto_increment') ? 1:0);
				
				$item->usages = null;
				
				$items[$col_name] = $item;
			}
		}
		
		return $items;
	}

	/**
	 * Obtiene info de acceso ACL
	 *
	 * @param string $component
	 * @param string $axo_value
	 * @param int $gid
	 * @return object Si ocurre un error false, null si no tiene acceso
	 */
	public static function GetInfoAccessACL($component, $axo_value, $gid=0){
		$db = Exj::InstanceDatabase();
		
		if (!$gid) {
			$gid = Exj::GetUserGID();
		}
		
		$query = "SELECT 
		  rul.id, rul.aro_value, rul.axo_section,
		  k2c.name AS mod_name, k2c.parent AS k2c_parent, k2c.access
		FROM 
		  jos_noixacl_rules rul 
		  INNER JOIN jos_k2_categories k2c ON rul.axo_section = k2c.id 
		  INNER JOIN jos_groups grps ON k2c.access = grps.id 
		  INNER JOIN jos_core_acl_aro_groups aro_grp ON rul.aro_value = aro_grp.value 
		WHERE 
		  rul.aco_section = 'com_k2' AND 
		  k2c.published = 1 AND aro_grp.id = $gid AND grps.name = '$component' AND rul.axo_value = '$axo_value'";
		
		$db->setQuery($query);
		$infoAccess = null;
		$db->loadObject($infoAccess);
		if (!$db->isValid()) {
			return false;
		}
		
		return $infoAccess;
	}

	public static function RendererData($data, $fieldsInt=null) {
		if (!$data || empty($data)) {
			return $data;
		}

		if (empty($fieldsInt)) {
			$fieldsInt = array('id_', 'is_');
		}
		elseif (is_string($fieldsInt)) {
			$fieldsInt = explode(',', $fieldsInt);
		}

		if (is_array($data)) {
			foreach ($data as $item) {
				self::RendererData($item, $fieldsInt);
			}

			return $data;
		}
		
		if (is_object($data)) {
			foreach ($data as $prop => $value) {
				if ($value === null || $value === '') {
					continue;
				}

				foreach ($fieldsInt as $fieldInt) {
					if (strpos($prop, $fieldInt) === 0) {
						$valueInt = Exj::ParseInt($value, null);
						if ($valueInt === null) {
							continue;
						}

						if ($valueInt !== $value) {
							$data->$prop = $valueInt;
						}
					}
				}
			}
		}
		
		return $data;
	}
}

class ExjDataResult{
	public $items=null, $total=0;
	
	public function __construct(){
		$this->items = array();
		$this->total = 0;
	}
	
	/**
	 * Envia items
	 *
	 * @param array $items
	 */
	public function setItems($items){
		$this->items = $items;
		$this->total = count($items);
	}
	
}

?>