<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );
	
/**
 * class. AppComponentsData
 *
 */
class AppComponentsData extends ExjData {
	
	/**
	 * Carga lista principal de Componentes
	 *
	 * @param ExjResponse $response
	 * @param array $items
	 * @param int $total
	 * @param object $paramsCriteria
	 * @return bool Si ocurre un error false sino true
	 */
	public static function CargarListaPrincipal(ExjResponse $response, &$items, &$total, $paramsCriteria=null){
        $dbQuery = new ExjDBQuery();
        $dbQuery->autoAddLastChange('gro');
        
        $dbQuery->setFields("gro.id, com.id_componente, gro.name AS nombre_com,
  			cat.id AS id_cat, cat.published, cat.name AS name_cat,
  			com.plural_com, com.singular_com, com.nombre_tabla_com, gro.id AS id_group_joomla");
        
        $dbQuery->setTables("jos_groups gro 
        	LEFT JOIN jos_k2_categories cat ON gro.id = cat.access 
        	LEFT JOIN exj_components com ON gro.id = com.id_group_joomla");
        
        if ($paramsCriteria) {
			$criteria = new AppComponentsCriteriaModel(false);
			if ($criteria->bind($paramsCriteria)) {
				$criteria->addMappingNameFieldDB('name_cat', 'cat.name');
				$criteria->addConditionsQuery($dbQuery);
			}
        }
        
        $dbQuery->addConditions("gro.id >= 3");
        $dbQuery->addConditions("gro.name LIKE '" . Exj::PREFIX_COMP_APP ."%'");
        
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("gro.id");
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
	//	$dbQuery->writeQueryExecuted();
	
		$autoId = 0;
		$baseDirCmp = Exj::GetPathAppWeb() . '/';
		foreach ($items as &$item) {
			if (!$item->id_componente) {
				$item->id_componente = --$autoId;
			}
			
			if ($item->nombre_com) {
				$item->existDirCmp = (file_exists($baseDirCmp . $item->nombre_com) ? 1:0);
			}
			else {
				$item->existDirCmp = 0;
			}

			/*
			if ($item->existDirCmp && !$item->nombre_tabla_com) {
				$item->nombre_tabla_com = 'xxx';
			}
			*/
		}
        
        return true;
	}
	
	
	/**
	 * Lookup de Components
	 *
	 * @param array $items
	 * @return bool Si ocurren errores false sino true
	 */
	public static function LoadLookupComponentes(&$items){
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("gro.id AS value, gro.name AS text, cat.id AS id_cat,
 	 		cat.published, cat.name AS name_cat");
        
        $dbQuery->setTables("jos_groups gro 
         LEFT JOIN jos_k2_categories cat ON gro.id = cat.access");
        
        $dbQuery->addConditions("gro.id >= 3");
        $dbQuery->addConditions("gro.name LIKE '" . Exj::PREFIX_COMP_APP ."%'");
        $dbQuery->addOrders("gro.id");
        
  		/* -------LOAD PARAMS--------------------- */
  		$dbQuery->withOutPaging();
		$items = $dbQuery->getRows();
		
    //   $dbQuery->writeQueryExecuted();
        
        return $dbQuery->isValid();		
	}
	
	/**
	 * Lookup de Tablas de la Aplicación
	 *
	 * @param array $items
	 * @return bool Si ocurren errores false sino true
	 */
	public static function LoadLookupAppTables(&$items){
		$db = Exj::InstanceDatabase();
		
		$prefixTables = Exj::PREFIX_TABLES_APP;
		
		$query = "SELECT t.table_name AS value, 
			TRIM(CONCAT_WS(' ', t.table_name, IF(t.table_comment = '', '', CONCAT('(', t.table_comment, ')')))) AS text, 
			t.engine, t.table_rows, t.table_comment 
			FROM  information_schema.TABLES t 
			WHERE (t.TABLE_SCHEMA=DATABASE() AND t.TABLE_TYPE='BASE TABLE' AND t.table_name LIKE '$prefixTables%')";
		$query .= " ORDER BY t.table_rows, t.CREATE_TIME DESC";
		
		$items = $db->loadObjectList($query);
		if (!$db->isValid()) {
			return false;
		}
		
		// $db->writeLastQuery();
		
		$itemsComponents = $db->loadObjectList("SELECT com.nombre_tabla_com, com.plural_com, com.singular_com, com.id_group_joomla 
								FROM exj_components com");
		if (!$db->isValid()) {
			return false;
		}

		foreach ($items as &$item) {
			$table_name = $item->value;

			$nameComponent = '';
			if (strpos($table_name, self::PREFIX_COMP_APP) !== 0) {
				$nameComponent = self::PREFIX_COMP_APP;
				if (substr($nameComponent, -1) != '_') {
					$nameComponent .= '_';
				}
			}
			$nameComponent .= $table_name;
			

			$item->nameComponent = $nameComponent;
			
			$item->pluralComp = '';
			$item->singularComp = '';

			foreach ($itemsComponents as $itemComponent) {
				if ($table_name == $itemComponent->nombre_tabla_com) {
					$item->pluralComp = $itemComponent->plural_com;
					$item->singularComp = $itemComponent->singular_com;
					break;
				}
			}
			
			if (!$item->pluralComp || !$item->singularComp) {
				$pluralComp = trim($item->table_comment);
				if (!$pluralComp) {
					$pluralComp = str_replace($prefixTables, '', $item->value);
					$pluralComp = trim(str_replace('_', ' ', $pluralComp));
				}
				
				$pluralComp = ucfirst($pluralComp);
				
				if (!$item->pluralComp) {
					$item->pluralComp = $pluralComp;
				}
				
				if (!$item->singularComp) {
					$item->singularComp = substr($pluralComp, 0, strlen($pluralComp)-1);
				}
			}
		}
		
		return true;
	}
	
	public static function LoadLookupTableCols(&$items, $nameTable){
		$items = array();
		
		$cols = self::GetColumnsOfTable($nameTable);
		if ($cols === false) {
			return false;
		}
		
		foreach ($cols as $nameCol => $objCol) {
			$objCol->value = $nameCol;
			$objCol->text = $nameCol;
			
			$items[] = $objCol;
		}
        
        return true;
	}

	public static function GetFieldsValidateDatesFromUntil(){
		return [
			'valid_from_date',
			'valid_until_date'
		];
	}

	public static function GetFieldsPrivatesSys(){
		// $items = self::GetFieldsValidateDatesFromUntil();
		$items = array();
		$items[] = 'modificado_dt';
		$items[] = 'id_usuario_modifico';
		return $items;
	}

	public static function IsFieldPrivateSys($nameField){
		return in_array($nameField, self::GetFieldsPrivatesSys());
	}

	public static function IsFieldValidateDatesFromUntil($nameField){
		return in_array($nameField, self::GetFieldsValidateDatesFromUntil());
	}
	
	public static function LoadListTableCols(&$items, $nameTable){
		$items = array();
		
		$cols = self::GetColumnsOfTable($nameTable);
		if ($cols === false) {
			return false;
		}
		
		foreach ($cols as $nameCol => $objCol) {
			/* Columnas reservadas */
			if (self::IsFieldPrivateSys($nameCol)) {
				continue;
			}
			
			$objCol->nameCol = $nameCol;
			
			/* seteo de etiqueta de la columna */
			$words = explode('_', $nameCol);
			$labelCol = array();
			foreach ($words as $w) {
				if ($w == 'id' || strlen($w) > 3 || $w == 'doc' || $w == 'ruc' || $w == 'is' || $w == 'es' || $w == 'num') {
					$labelCol[] = ucfirst($w);
				}
			}
			
			$labelCol = trim(implode(' ', $labelCol));
			if (!$labelCol) {
				$labelCol = str_replace("_", ' ', $nameCol);
				$labelCol = ucwords($labelCol);
			}

			if (self::IsFieldValidateDatesFromUntil($nameCol)) {
				$labelCol = 'Vigente ';
				$labelCol .= ($nameCol == 'valid_from_date' ?'desde':'hasta');
			}
			
			$labelCol = str_replace('Codigo', 'Código', $labelCol);
			$labelCol = str_replace('cion', 'ción', $labelCol);
			$objCol->labelCol = $labelCol;
		
			$items[] = $objCol;
		}
		
	//	print_r($items[0]);
		
		$db = Exj::InstanceDatabase();
		$query = "SELECT cam.id_campo, cam.nombre_cam, cam.etiqueta_cam
			FROM
			  exj_com_campos cam 
			  INNER JOIN exj_components com ON cam.id_componente = com.id_componente
			WHERE com.nombre_tabla_com = '$nameTable'";
		$itemsCampos = $db->loadObjectList($query);
		if (!$db->isValid()) {
			return false;
		}
		
		$autoId = 0;
		foreach ($items as &$item) {
			$nameCol = $item->nameCol;
			$item->id_campo = 0;
			foreach ($itemsCampos as $itemCampo) {
				if ($nameCol == $itemCampo->nombre_cam) {
					$item->id_campo = $itemCampo->id_campo;
					$item->labelCol = $itemCampo->etiqueta_cam;
					break;
				}
			}
			
			if (!$item->id_campo) {
				$item->id_campo = --$autoId;
			}
		}
        
        return true;
	}
	
	public static function GetInfoComponentFromIdGroupJoomla($id_group_joomla, $fields='*'){
		return ExjDatabase::GetObjectFromQuery("SELECT $fields FROM exj_components WHERE id_group_joomla=$id_group_joomla");
	}
}

?>