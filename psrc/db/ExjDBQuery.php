<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjDBQuery extends ExjObject {

    const QUERY_LIKE_ALL = 1;
    const QUERY_LIKE_LEFT = 2;
    const QUERY_LIKE_RIGHT = 3;
    const QUERY_LIKE_EQUAL = 4;

    private $_fields = '*';
    private $_tables = '';
    private $_groups = '';
    private $_conditions = array();
    private $_conditionsOR = array();
    private $_havings = array();
    private $_orders = array(), $_ordersFirst = '';
    private $_start = 0;
    private $_limit = 0;
    private $_sort = '';
    private $_dir = 'ASC';
    private $_query = '';
    private $_value = '';
    private $_queryFields = null;
    private $_queryFieldValue = '';
    private $_query_like = 1;
    private $_msgError = '';
    private $_totalTopics = 0;
    private $_separationQueryChar = '';
    private $_separationQueryField = '';
    private $_fixPagingOnlyLimit = false;
    private $_aliasTableAutoAddLastChange = '';
    private $_aliasFieldLastChangeUsr = '';
    private $_aliasFieldLastChangeDateTime = '';

    public function __construct($separationQueryField = '', $separationQueryChar = '') {
        $this->_separationQueryField = $separationQueryField;
        $this->_separationQueryChar = $separationQueryChar;


        $this->_start = ExjRequest::GetParam('start', 0);
        $this->_limit = ExjRequest::GetParam('limit', 0);
        $this->_sort = ExjRequest::GetParam('sort', '');
        $this->_dir = ExjRequest::GetParam('dir', "ASC");

        $this->_query = ExjRequest::GetParam('query', '');
        $this->_value = ExjRequest::GetParam('value', '');

        /*
          if ($this->_query) {
          ExjTransferCharacters::decodeUTF8ToISO($this->_query);
          }
         */

        $this->changeQuery($this->_query);
        $this->_executeSeparationQuery();
    }

    public function clearParamSort() {
        $this->_sort = '';
        return $this;
    }

    /**
     * Automáticamente adicionará a la consulta información del ultimo cambio Usuario y Fecha y hora del cambio
     *
     * @param string $aliasTable
     * @param string $fieldSortFromUI
     */
    public function autoAddLastChange($aliasTable, $fieldSortFromUI = '_info_ultcambio') {
        $this->_aliasTableAutoAddLastChange = $aliasTable;

        if ($fieldSortFromUI && $this->_aliasTableAutoAddLastChange) {
            $this->addMappingSort($fieldSortFromUI, "$aliasTable.modificado_dt");
        }
    }

    public function reset() {
        $this->_fields = '*';
        $this->_tables = '';
        $this->_conditions = array();
        $this->_conditionsOR = array();
        $this->_orders = array();
        $this->_ordersFirst = '';
        $this->_havings = array();
        $this->_queryFields = null;
        $this->_queryFieldValue = '';
        $this->_query_like = 1;
        $this->_totalTopics = 0;
        $this->_msgError = '';
        $this->_withOutOrder = false;
        $this->_fixPagingOnlyLimit = false;
    }

    public function getParamQuery() {
        return $this->_query;
    }

    public function getParamValue() {
        return $this->_value;
    }

    /**
     * Setea el parámetro value enviado por request
     *
     * @param string $value Por los general es tipo int
     */
    public function setParamValue($value) {
        $this->_value = $value;
    }

    /**
     * overwrited. Permite cambiar la consulta que llega como parámetro
     *
     * @param string $query
     */
    protected function changeQuery(&$query) {
        
    }

    private function _executeSeparationQuery() {
        if (!$this->_query) {
            return;
        }

        if (!$this->_separationQueryChar || !$this->_separationQueryField) {
            return;
        }

        $posComa = strpos($this->_query, $this->_separationQueryChar);

        if ($posComa === false) {
            return;
        }

        $queryAll = $this->_query;

        $this->_query = substr($queryAll, 0, $posComa);
        if (!$this->_query) {
            return;
        }

        $queryCode = substr($queryAll, $posComa + 1);
        if (!$queryCode) {
            return;
        }

        //	$queryCode = ltrim($queryCode);
        $queryCode = trim($queryCode);
        if (!$queryCode) {
            return;
        }

        $this->addConditions($this->_separationQueryField . " LIKE '$queryCode%'");
    }

    public function getParamSortRaw() {
        return $this->_sort;
    }

    private function _getParamSort() {
        if (!$this->_sort || !$this->_mappingSort) {
            if ($this->_sort) {
                $this->_sort = trim($this->_sort);
            }

            if ($this->_sort) {
                return "$this->_sort $this->_dir";
            }

            return $this->_sort;
        }

        $paramSort = $this->_sort;

        foreach ($this->_mappingSort as $nameFieldParam => $nameFieldSQL) {
            if ($this->_sort == $nameFieldParam) {
                $paramSort = $nameFieldSQL;
                break;
            }
        }

        if ($paramSort && is_array($paramSort)) {
            if ($this->_dir) {
                $paramSort = implode(' ' . $this->_dir . ', ', $paramSort);
            } else {
                $paramSort = implode(', ', $paramSort);
            }
        }

        if ($this->_dir && $paramSort) {
            $paramSort .= ' ' . $this->_dir;
        }

        return $paramSort;
    }

    private $_mappingSort = null;

    public function addMappingSort($nameFieldParam, $nameFieldSQL) {
        if (!$this->_mappingSort) {
            $this->_mappingSort = array();
        }

        $this->_mappingSort[$nameFieldParam] = $nameFieldSQL;

        return $this;
    }

    /**
     * Establece que la consulta es sin paginación
     *
     */
    public function withOutPaging() {
        $this->_limit = 0;
        return $this;
    }

    public function isWithOutPaging() {
        return ($this->_limit == 0);
    }

    public function getParamLimit() {
        if ($this->_limit < 0) {
            return 0;
        }
        return $this->_limit;
    }

    public function getParamStart() {
        if ($this->_start < 0) {
            return 0;
        }
        return $this->_start;
    }

    /**
     * Seteo de paginación
     *
     * @param int $limit
     * @param int $start
     */
    public function setPaging($limit = 30, $start = 0) {
        $this->_start = $start;
        $this->_limit = $limit;
        return $this;
    }

    /**
     * Cuando se trata del parametro query, se debe especificar, alguno de estos
     * valores:
      QUERY_LIKE_ALL
      QUERY_LIKE_LEFT
      QUERY_LIKE_RIGHT
      QUERY_LIKE_EQUAL
     * 
     *
     * @param int $query_like
     */
    public function setQueryLike($query_like) {
        $this->_query_like = $query_like;
        return $this;
    }

    public function setFields($fields) {
        if ($this->_fields == '*') {
            $this->_fields = $fields;
        } else {
            if ($this->foundStr($this->_fields, $fields)) {
                return;
            }

            if ($this->_fields) {
                $this->_fields .= ', ';
            }

            $this->_fields .= $fields;
        }

        return $this;
    }

// setFields

    private function _getAliasTableFromString($nameField, $text) {
        $aliasTable = '';
        $nameField = trim($nameField);
        $text = trim($text);

        if (!$nameField || !$text) {
            return $aliasTable;
        }

        $this->delCharExcept($text);

        $posFinal = strpos($text, $nameField);
        if ($posFinal === false) {
            return $aliasTable;
        }
        $posFinal = $posFinal - 1;
        if ($posFinal <= 0) {
            return $aliasTable;
        }

//		$charsSeparators = array(" ", ",");

        $posIni = $posFinal;
        $offsetChar = 0;
        while (--$posIni > 0) {
            $char = substr($text, $posIni, 1);
            // in_array($char, $charsSeparators)
            if ($char == ' ' || ($char == ',')) {
                $offsetChar = 1;
                break;
            }
        }
        if ($posIni <= 0) {
            return $aliasTable;
        }

        $aliasTable = substr($text, $posIni + $offsetChar, $posFinal - $posIni - $offsetChar);
        $aliasTable = trim($aliasTable);

        if ($aliasTable) {
            $aliasTable = str_replace(array('(', ')', ','), '', $aliasTable);
        }

        // echo "<br>aliasTable: $aliasTable";

        return $aliasTable;
    }

    public function getAliasTableFromField($nameField) {
        $aliasTable = $this->_getAliasTableFromString($nameField, $this->_tables);
        if (!$aliasTable) {
            $aliasTable = $this->_getAliasTableFromString($nameField, $this->_fields);
        }

        return $aliasTable;
    }

    public function delCharExcept(&$str) {
        if (!$str) {
            return;
        }

        $charExcept = array("", "´");

        $str = str_replace($charExcept, "", $str);
    }

    public function foundStr($str, $strToAdd) {
        $this->delCharExcept($str);
        $this->delCharExcept($strToAdd);

        $num = substr_count($str, $strToAdd);

        if ($num >= 1) {
            return true;
        }

        return false;
    }

// foundStr

    /**
     * Setea o sobrescribe tablas a la consulta
     *
     * @param string $tables
     */
    public function setTables($tables) {
        $this->_tables = $tables;
        return $this;
    }

    public function addTableJoin($extraTables, $joinSQL = 'INNER JOIN') {
        if ($this->_tables) {
            $this->_tables .= ' ' . $joinSQL . ' ';
        }

        $this->_tables .= $extraTables;
    }

    public function setGroups($group) {
        if ($this->_groups) {
            if ($this->foundStr($this->_groups, $group)) {
                return $this;
            }
            $this->_groups .= ', ' . $group;
        } else {
            $this->_groups = $group;
        }

        return $this;
    }

    public function addHaving($fieldName, $complementCondition) {
        $having = "$fieldName $complementCondition";
        $this->delCharExcept($having);

        // se add al group sql
        $this->setGroups($fieldName);

        // se add a fields por compatibilidad de mysql 4.1
        $this->setFields($fieldName);

        $this->_havings[] = $having;
        return true;
    }

    /**
     * Adiciona una condición a la expresión HAVING, la condición debe ser una expresión
     *
     * @param string $condition
     */
    public function addHavingCondition($condition) {
        $this->_havings[] = $condition;
    }

    /**
     * Adiciona condiciones a la consulta
     *
     * @param string $condition
     * @return bool true si se adicionó la condición
     */
    public function addConditions($condition) {
        if (!$condition) {
            return $this;
        }

        $this->_conditions[] = $condition;
        return $this;
    }

    public function addConditionsDateFromUntil($fieldTable, $date_from, $date_until, $applyDateFormatToFieldTable = true) {
        if (!$date_from && !$date_until) {
            return $this;
        }

        $date_from = trim($date_from);
        $date_until = trim($date_until);
        if (!$date_from && !$date_until) {
            return $this;
        }


        if ($applyDateFormatToFieldTable) {
            $fieldTable = "DATE_FORMAT($fieldTable, '%Y-%m-%d')";
        }

        if ($date_from && $date_until) {
            if ($date_from == $date_until) {
                $this->addConditions("$fieldTable = '$date_from'");
            } else {
                $this->addConditions("$fieldTable BETWEEN '$date_from' AND '$date_until'");
            }
        } else {
            if ($date_from) {
                $this->addConditions("$fieldTable >= '$date_from'");
            }

            if ($date_until) {
                $this->addConditions("$fieldTable <= '$date_until'");
            }
        }

        return $this;
    }


    public function addConditionsOR($condition) {
        if (!$condition) {
            return $this;
        }

        if (is_array($condition)) {
            $condition = '(' . implode(" OR ", $condition) . ')';
        }

        $this->_conditionsOR[] = $condition;

        return $this;
    }

    public function setOrdersFirst($ordersFirst) {
        if (!$ordersFirst) {
            return $this;
        }

        if ($this->_sort && strpos($ordersFirst, $this->_sort) !== false) {
            return $this;
        }

        $this->_ordersFirst = $ordersFirst;

        return $this;
    }

    public function addOrders($fieldOrder) {
        if (!$fieldOrder) {
            return $this;
        }

        if ($fieldOrder == $this->_sort) {
            return $this;
        }

        $this->_orders[] = $fieldOrder;

        return $this;
    }

    public function resetOrders(){
        $this->_orders = array();
        $this->_ordersFirst = '';
        return $this;
    }

    /**
     * Envia el campo o campos para que filtre en la consulta
     *
     * @param mixed $queryfield Tipo texto o array de campos
     */
    public function setQueryField($queryFields) {
        $this->_queryFields = $queryFields;
        return $this;
    }

    /**
     * Seteo de parametros para filtro por id clave y valor
     *
     * @param string $queryFieldValue
     * @param mixed $value No requerido
     */
    public function setQueryFieldValue($queryFieldValue, $value = null) {
        $this->_queryFieldValue = $queryFieldValue;
        if ($value !== null) {
            $this->setParamValue($value);
        }

        return $this;
    }

    public function getWhere($operator = 'AND') {
        $where = ' ';

        $conditionExtra = '';
        if ($this->_queryFields && $this->_query) {
            $queryCustom = $this->_query;
            if (trim($queryCustom) != '') {
                $queryCustom = trim($queryCustom);
            }
            switch ($this->_query_like) {
                case self::QUERY_LIKE_ALL:
                    $queryCustom = "%$queryCustom%";
                    break;

                case self::QUERY_LIKE_LEFT:
                    $queryCustom = "%$queryCustom";
                    break;

                case self::QUERY_LIKE_RIGHT:
                    $queryCustom = "$queryCustom%";
                    break;

                case self::QUERY_LIKE_EQUAL:
                    $queryCustom = "$queryCustom";
                    break;

                default:
                    break;
            }
            if (is_array($this->_queryFields)) {
                $conditionExtra = array();
                foreach ($this->_queryFields as $f) {
                    $conditionExtra[] = "($f LIKE '$queryCustom')";
                }
                $conditionExtra = '(' . implode(" OR ", $conditionExtra) . ')';
            } else {
                $conditionExtra = "($this->_queryFields LIKE '$queryCustom')";
            }
        }

        if ($this->_queryFieldValue && $this->_value) {
            if ($conditionExtra) {
                $conditionExtra .= ' AND ';
            }
            if (is_numeric($this->_value)) {
                $conditionExtra .= "$this->_queryFieldValue = $this->_value";
            } else {
                $this->_value = trim($this->_value);
                $conditionExtra .= "$this->_queryFieldValue = '$this->_value'";
            }
        }

        $strConditionsOR = $this->getStrConditionsOR();
        if (count($this->_conditions) == 0) {
            if ($conditionExtra) {
                $where = " \n WHERE $conditionExtra $strConditionsOR";
            }
            return $where;
        }
        $operator = trim($operator);
        $where = "\n WHERE (" . implode(" $operator ", $this->_conditions) . ") $strConditionsOR";
        if ($conditionExtra) {
            $where .= " AND $conditionExtra";
        }

        return $where;
    }


    public function getStrConditions() {
        return implode(' AND ', $this->_conditions);
    }

    public function getStrConditionsOR($addConditionORFirst = true) {
        $str = '';

        if (count($this->_conditionsOR) > 0) {
            $str = '(' . implode(' OR ', $this->_conditionsOR) . ')';
            if ($addConditionORFirst) {
                $str = "OR $str";
            }
        }

        return $str;
    }

    public function getHavingSQL($operator = 'AND') {
        $havingSQL = " ";

        if (count($this->_havings) == 0) {
            return $havingSQL;
        }

        $havingSQL = "\n HAVING (" . implode(" $operator ", $this->_havings) . ") ";

        return $havingSQL;
    }

// getHavingSQL

    private $_withOutOrder = false;

    public function withOutOrder($withOutOrder = true) {
        $this->_withOutOrder = $withOutOrder;
    }

    public function existOrders() {
        if ($this->_withOutOrder) {
            return false;
        }

        if ($this->_sort || $this->_ordersFirst) {
            return true;
        }

        return (count($this->_orders) > 0 ? true : false);
    }

    public function getOrder() {
        $existOrders = $this->existOrders();

        if (!$existOrders) {
            return ' ';
        }

        $fieldsOrderSQL = array();

        if ($this->_ordersFirst) {
            $fieldsOrderSQL[] = $this->_ordersFirst;
        }

        $paramSort = $this->_getParamSort();
        if ($paramSort) {
            if (!in_array($paramSort, $fieldsOrderSQL)) {
                $fieldsOrderSQL[] = $paramSort;
            }
        }

        if ($this->_orders && count($this->_orders) > 0) {
            foreach ($this->_orders as $itemOrder) {
                if (!in_array($itemOrder, $fieldsOrderSQL)) {
                    $fieldsOrderSQL[] = $itemOrder;
                }
            }
        }

        if (count($fieldsOrderSQL) == 0) {
            return ' ';
        }

        $fieldsOrderSQL = implode(", ", $fieldsOrderSQL);
        $order = " \n ORDER BY $fieldsOrderSQL";

        return $order;
    }

    public function writeQueryExecuted() {
        $db = Exj::InstanceDatabase();

        $query = $db->getQuery();
        if (!$query) {
            $this->writeErrorClassLn($this, "No se ha ejecutado consulta");
            return;
        }

        $segundosDemoraLastQuery = $db->getSegundosDemoraLastQuery();
        if ($segundosDemoraLastQuery != -1) {
            $query .= "<br>Demora: $segundosDemoraLastQuery segundos.";
        }

        Exj::WriteLn($query);
    }

    public function getGroup() {
        $group = ' ';
        if ($this->_groups) {
            $group = " \n GROUP BY " . $this->_groups . ' ';
        }

        return $group;
    }

    /**
     * Fija el limite no con start, solo LIMIT
     *
     * @param bool $onlyLimit Defecto true
     */
    public function fixPagingOnlyLimit($onlyLimit = true) {
        $this->_fixPagingOnlyLimit = $onlyLimit;
    }

    public function getLimits() {
        $SQLlimits = '';

        if ($this->_limit == 0) {
            return $SQLlimits;
        }

        if ($this->_fixPagingOnlyLimit) {
            $SQLlimits = " \n LIMIT $this->_limit";
            return $SQLlimits;
        }

        if ($this->_start == -1 && $this->_totalTopics == 0) {
            $this->_start = 0;
        }
        if ($this->_start == -1) {
            // la ultima pagina
            $this->_start = $this->_totalTopics - intval($this->_limit);
            if ($this->_start < 0) {
                $this->_start = 0;
            }
        }

        $SQLlimits = " \n LIMIT $this->_start, $this->_limit";

        return $SQLlimits;
    }

    private function _validateAutoAddFields(&$fieldsSQL, &$tablesSQL) {
        if (!$this->_aliasTableAutoAddLastChange) {
            return false;
        }

        $aliasTable = $this->_aliasTableAutoAddLastChange;

        $this->_aliasFieldLastChangeUsr = 'info_ult_cambio_usr';
        $this->_aliasFieldLastChangeDateTime = 'info_utl_cambio_datet';

        $fieldsSQL .= ", usr_ult_cambio.username AS " . $this->_aliasFieldLastChangeUsr;
        $fieldsSQL .= ", $aliasTable.modificado_dt AS " . $this->_aliasFieldLastChangeDateTime;

        $tablesSQL .= " LEFT JOIN jos_users usr_ult_cambio ON $aliasTable.id_usuario_modifico = usr_ult_cambio.id";

        return true;
    }

    public function getSQL($operatorWhere = "AND") {
        $sql = '';

        if (!$this->_tables) {
            return $sql;
        }

        if (!$this->_fields) {
            $this->_fields = "*";
        }

        $fieldsSQL = $this->_fields;
        $tablesSQL = $this->_tables;

        $this->_validateAutoAddFields($fieldsSQL, $tablesSQL);

        $sql = "SELECT $fieldsSQL ";
        $sql .= "\n FROM $tablesSQL ";

        $sql .= $this->getWhere($operatorWhere);
        $sql .= $this->getGroup();
        $sql .= $this->getHavingSQL($operatorWhere);
        $sql .= $this->getOrder();
        $sql .= $this->getLimits();

        $this->delCharExcept($sql);

        return $sql;
    }

    public function setError($msgError) {
        //	echo '<br/>'. __METHOD__ . " msgError: $msgError";
        $this->_msgError = $msgError;
    }

    public function getError() {
        return $this->_msgError;
    }

    public function isValid() {
        return ($this->_msgError ? false : true);
    }

    // sobre carga
    public function getErrorMsg() {
        return $this->_msgError;
    }

    public function getRows($operatorWhere = "AND", $totalTopics = 0) {
        $db = Exj::InstanceDatabase();

        if ($totalTopics > 0) {
            $this->_totalTopics = $totalTopics;
        }

        $sql = $this->getSQL($operatorWhere);
        if (!$sql) {
            $this->setError("No se han enviado, datos para construir el SQL.");
            return null;
        }


        $rows = $db->loadObjectList($sql);
        $errMsgDB = $db->getErrorMsg();

        /*
          echo '<br/>'. __METHOD__. " sql: $sql" . '<br/>ERROR: '. $errMsgDB . '<br/>';
          print_r($db);
         */

        //	echo "<br/>EsValido: ". ($db->isValid() ? 'si': 'NO');

        if ($errMsgDB) {
            $this->setError($errMsgDB);
            //	echo "<br/>errMsgDB: $errMsgDB";
            return $rows;
        }

        $this->_renderRows($rows);

        //	echo __METHOD__;
        //	print_r($rows);

        return $rows;
    }

    private function _renderRows(&$rows) {
        if (!$rows || count($rows) == 0) {
            return;
        }

        if (!$this->_aliasFieldLastChangeUsr && !$this->_aliasFieldLastChangeDateTime) {
            return;
        }

        $fLastChangeUsr = $this->_aliasFieldLastChangeUsr;
        $fLastChaneDateTime = $this->_aliasFieldLastChangeDateTime;

        foreach ($rows as &$row) {
            $row->_info_ultcambio = '';
            if ($fLastChangeUsr) {
                if ($row->$fLastChangeUsr) {
                    $row->_info_ultcambio = $row->$fLastChangeUsr;
                }

                unset($row->$fLastChangeUsr);
            }

            if ($fLastChaneDateTime) {
                if ($row->$fLastChaneDateTime) {
                    $row->$fLastChaneDateTime = ExjDate::ConvertToDateTimeDisplay2($row->$fLastChaneDateTime);
                }

                if ($row->_info_ultcambio) {
                    $row->_info_ultcambio .= ' - ';
                }

                $row->_info_ultcambio .= $row->$fLastChaneDateTime;

                unset($row->$fLastChaneDateTime);
            }
        }
    }

    public function getDataArray($operatorWhere = " And ") {
        $db = Exj::InstanceDatabase();

        $sql = $this->getSQL($operatorWhere);
        if (!$sql) {
            $this->setError("No se han enviado, datos para construir el SQL.");
            return null;
        }

        $db->setQuery($sql);
        $rowList = $db->loadRowList();
        $this->setError($db->getErrorMsg());

        return $rowList;
    }

    public function getObject(&$object, $operatorWhere = " And ") {
        $db = Exj::InstanceDatabase();

        $sql = $this->getSQL($operatorWhere);
        if (!$sql) {
            $this->setError("No se han enviado, datos para construir el SQL.");
            return null;
        }

        $db->setQuery($sql);

        $objReturn = $db->loadObject($object);
        $this->setError($db->getErrorMsg());

        return $objReturn;
    }

    public function getCount($fieldKey = '', $operatorWhere = " AND ") {
        $total = 0;

        if (!$fieldKey) {
            $fieldKey = "*";
        }

        $sql = "SELECT count($fieldKey) ";
        $sql .= "\n FROM $this->_tables ";
        $sql .= $this->getWhere($operatorWhere);

        $db = Exj::InstanceDatabase();
        $db->setQuery($sql);

        $total = $db->loadResult();
        $this->setError($db->getErrorMsg());

        $this->_totalTopics = intval($total);
        return $this->_totalTopics;
    }

    public function loadRowsCount(&$rows, &$count, $fieldCount='*'){
        $rows = $this->getRows();
        $count = 0;

        // si array vacio o hay error sale
        if (empty($rows)){
            return $this;
        }

        $nRows = count($rows);
      //  echo "<br>ExjDBQuery. limit: ".$this->getParamLimit()." nRows: $nRows";

        if ($this->isWithOutPaging()) {
            // es sin paginación
            $count = $nRows;
        }
        else{
            if ($nRows < $this->getParamLimit()) {
                $count = $nRows;
            }
            else{
                $count = $this->getCount($fieldCount);
              //  echo "<br>ExjDBQuery. Ejecutado count($fieldCount) = $count";
            }
        }

        return $this;
    }

    public function addConditionsValidDatesFromUntil($dateCurrent=''){
        if (!$dateCurrent) {
            $dateCurrent = Exj::GetDate();
        }

        return $this->addConditions("valid_from_date <= '$dateCurrent'")
            ->addConditions("(valid_until_date >= '$dateCurrent' OR valid_until_date IS NULL)");
    }

    public function getNameFieldValue(){
        $nameFieldValue = trim($this->_queryFieldValue);
        if ($nameFieldValue) {
            return $nameFieldValue;
        }
        
        $strFields = $this->_fields;
        if ($strFields) {
            $posEnd = stripos($strFields, ' AS value');
            if ($posEnd !== false) {
                $nameFieldValue = substr($strFields, 0, $posEnd);

                $posIni = strrpos($nameFieldValue, ',');
                if ($posIni !== false) {
                    $nameFieldValue = substr($nameFieldValue, $posIni+1);
                }

                $nameFieldValue = trim($nameFieldValue);
            }
        }

        return $nameFieldValue;
    }

    public function testAddConditionIdValue($nameFieldValue=''){
        $idValue = trim($this->getParamValue());
        if (!$idValue) {
            return $this;
        }

        if (!$nameFieldValue) {
            $nameFieldValue = $this->getNameFieldValue();
        }

        if (!$nameFieldValue) {
            return $this;
        }
        
        if (!is_numeric($idValue)) {
            $idValue = "'" . $idValue . "'";
        }
        $strCondition = $nameFieldValue . '=' . $idValue;

        // $this->setQueryFieldValue($nameFieldValue);

        if (!empty($this->_conditions)) {
            $this->_conditions = array();
        }
        
        $this->addConditions($strCondition);
        

        // return $this->addConditionsOR($strCondition);
        return $this;
    }

}

?>