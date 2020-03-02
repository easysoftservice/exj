<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );
	
/**
 * Datos de {labelComponents}
 *
 */
class AppComponentTplsData extends ExjData {
	
	/**
	 * Carga lista principal de {labelComponents}
	 *
	 * @param ExjResponse $response
	 * @param array $items
	 * @param int $total
	 * @param object $paramsCriteria
	 * @return bool Si ocurre un error false sino true
	 */
	public static function CargarListaPrincipal(ExjResponse $response, &$items, &$total, $paramsCriteria=null){
        $dbQuery = new ExjDBQuery();
        $dbQuery->autoAddLastChange('{alias_table}');
        
        $dbQuery->setFields("{fields_table_sql}");
        
        $dbQuery->setTables("{name_table} {alias_table}");
        
        if ($paramsCriteria) {
			$criteria = new AppComponentTplsCriteriaModel(false);
			if ($criteria->bind($paramsCriteria)) {
				$criteria->addConditionsQuery($dbQuery);
			}
        }
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("{alias_table}.id_field_key");
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		// $dbQuery->writeQueryExecuted();
        
        return true;
	}
	
	/**
	 * Lookup de {labelComponents}
	 *
	 * @return array|bool Resultado de la consulta, false si ocurre un error
	 */
	public static function GetLookupComponentTpls(){
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("{fields_valuetext_table_sql}");
        
        $dbQuery->setTables("{name_table} {alias_table}");

        $dbQuery->testAddConditionIdValue();
        
        $dbQuery->addOrders("{alias_table}.{field_sort_sql}");

        
  		$dbQuery->withOutPaging();
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
    //   $dbQuery->writeQueryExecuted();
        
        return $items;
	}
}

?>