<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base de utilizades, solo acceso estático
 *
 */
class ExjUtil {
    const TYPECLASS_EDITABLE = 'editable';
    const TYPECLASS_CRITERIA = 'criteria';
    const TYPECLASS_LIST = 'list';
    const TYPECLASS_REPORT = 'report';
    const TYPECLASS_MODEL = 'model';
    const TYPECLASS_DATA = 'data';
    const TYPECLASS_HELPER = 'helper';
    const TYPECLASS_CONTROLLER = 'controller';

    /**
     * Borra el prefijo del componente
     *
     * @param string $component
     * @return string
     */
    public static function ClearPrefixApp($component) {
        if (strpos($component, ExjObject::PREFIX_COMP_APP) === 0) {
            $component = substr($component, strlen(ExjObject::PREFIX_COMP_APP));
        } elseif (strpos($component, ExjObject::PREFIX_COMP_FRAMEWORK) === 0) {
            $component = substr($component, strlen(ExjObject::PREFIX_COMP_FRAMEWORK));
        }
        $component = trim($component);

        if ($component && strpos($component, '_') === 0) {
            $component = substr($component, 1);
        }

        return $component;
    }

    public static function GetClassModelEditableOfComponent(&$msgError, $component) {
        return self::GetClassModelOfComponent($msgError, $component, self::TYPECLASS_EDITABLE);
    }

    public static function GetClassModelCriteriaOfComponent(&$msgError, $component) {
        return self::GetClassModelOfComponent($msgError, $component, self::TYPECLASS_CRITERIA);
    }

    public static function GetClassModelReportOfComponent(&$msgError, $component) {
        return self::GetClassModelOfComponent($msgError, $component, self::TYPECLASS_REPORT);
    }

    public static function GetClassDataOfComponent(&$msgError, $component) {
        return self::_GetClassOfComponent($msgError, $component, self::TYPECLASS_DATA);
    }

    public static function GetClassHelperOfComponent(&$msgError, $component) {
        return self::_GetClassOfComponent($msgError, $component, self::TYPECLASS_HELPER);
    }

    public static function GetClassControllerOfComponent(&$msgError, $component) {
        return self::_GetClassOfComponent($msgError, $component, self::TYPECLASS_CONTROLLER);
    }

    public static function GetClassModelOfComponent(&$msgError, $component, $typeModel = '') {
        return self::_GetClassOfComponent($msgError, $component, $typeModel);
    }

    private static function _IsModel($typeModel){
        $isModel = false;
        if ($typeModel) {
            switch ($typeModel) {
                case self::TYPECLASS_EDITABLE:
                case self::TYPECLASS_CRITERIA:
                case self::TYPECLASS_LIST:
                case self::TYPECLASS_REPORT:
                case self::TYPECLASS_MODEL:
                    $isModel = true;
                break;
            }
        }

        return $isModel;
    }

    /**
     * Obtiene Clase de un componente
     *
     * @param string $msgError
     * @param string $component
     * @param string $typeModel
     * @param bool $isSubstrNameRaw
     * @return string
     */
    private static function _GetClassOfComponent(&$msgError, $component, $typeModel = '', $isSubstrNameRaw = false) 
    {
        $ClassOfComponent = '';
        $msgError = '';

        $component = trim($component);
        if (!$component) {
            $msgError = "No se indicó el componente para obtener Clase desde componente!";
            Exj::PrintBackTrace();
            return $ClassOfComponent;
        }

        $nameRaw = ExjUtil::ClearPrefixApp($component);

        // typeModel: editable component: app_comparecientes
        // nameRaw: comparecientes
        // AppComparecienteEditableModel        

        $ClassOfComponent = array();
        $ClassOfComponent[] = Exj::GetPrefixClassApp();
        $isModel = self::_IsModel($typeModel);

        if (!$isSubstrNameRaw) {
            $isSubstrNameRaw = ($typeModel == self::TYPECLASS_EDITABLE);
        }

        if ($isSubstrNameRaw) {
            $nameClassPrefix = substr($nameRaw, 0, strlen($nameRaw) - 1);
        }
        else{
            $nameClassPrefix = $nameRaw;
        }

        $ClassOfComponent = array_merge($ClassOfComponent, explode('_', $nameClassPrefix));

        if ($typeModel) {
            if ($typeModel == self::TYPECLASS_HELPER) {
                $ClassOfComponent[] = 'UI';
            }

            $ClassOfComponent[] = $typeModel;
        }

        if ($isModel) {
            $ClassOfComponent[] = 'model';
        }

        foreach ($ClassOfComponent as &$ClassPartes) {
            if ($ClassPartes) {
                $ClassPartes = ucfirst($ClassPartes);
            }
        }

        $ClassOfComponent = trim(implode('', $ClassOfComponent));

        if (class_exists($ClassOfComponent)) {
            return $ClassOfComponent;
        }

        if (!$isSubstrNameRaw) {
            $ClassOfComponent = self::_GetClassOfComponent(
                $msgError, $component, $typeModel, true
            );
            return $ClassOfComponent;
        }

        $msgError = "No existe la clase: $ClassOfComponent";
        echo "$msgError Ref: component: $component nameRaw: $nameRaw";
        $ClassOfComponent = '';

        return $ClassOfComponent;
    }

    public static function GetNameModelEditableFromNameClass($nameClassModel) {
        $name = '';
        $nameClassModel = trim($nameClassModel);
        if (!$nameClassModel) {
            return $name;
        }

        if (strlen($nameClassModel) <= 3) {
            return $nameClassModel;
        }

        // Ej: AppParticipanteEditableModel
        $name = substr($nameClassModel, 3);
        $name = str_replace("EditableModel", "", $name);
        $name = strtolower($name);

        return $name;
    }

    public static function GetNameClassModelEditableFromName($nameEditableModel) {
        $nameEditableModel = trim($nameEditableModel);
        if (!$nameEditableModel) {
            return $nameEditableModel;
        }

        if (strpos($nameEditableModel, Exj::GetPrefixClassApp()) === 0) {
            if (class_exists($nameEditableModel)) {
                return $nameEditableModel;
            }
        }        

        if (strpos($nameEditableModel, '_') !== false) {
            $nameEditableModel = self::ConvertirGionesToUcfirst($nameEditableModel);
        }
        else{
            $nameEditableModel = ucfirst($nameEditableModel);
        }

        $nameClass = Exj::GetPrefixClassApp();
        $nameClass .= $nameEditableModel;
        $nameClass .= 'EditableModel';

        return $nameClass;
    }

    public static function GetNameClassModelChildEditableFromName($nameEditableModel) {
        $nameEditableModel = trim($nameEditableModel);
        if (!$nameEditableModel) {
            return $nameEditableModel;
        }

        if (strpos($nameEditableModel, Exj::GetPrefixClassApp()) === 0) {
            if (class_exists($nameEditableModel)) {
                return $nameEditableModel;
            }
        }

        $nameEditableModel = self::ConvertirGionesToUcfirst($nameEditableModel);

        $nameClass = Exj::GetPrefixClassApp();
        $nameClass .= $nameEditableModel;
        if (strpos($nameClass, 'EditableChildModel')===false) {
            $nameClass .= 'EditableChildModel';
        }

        return $nameClass;
    }

    static function GetNameClassModelReadOnlyFromName($nameEditableModel) {
        $nameEditableModel = trim($nameEditableModel);
        if (!$nameEditableModel) {
            return $nameEditableModel;
        }

        $nameEditableModel = self::ConvertirGionesToUcfirst($nameEditableModel);



        $nameClass = Exj::GetPrefixClassApp();
        $nameClass .= $nameEditableModel;
        $nameClass .= 'ReadOnlyModel';

        return $nameClass;
    }

    public static function ConvertStrIdsToArray(&$strItemsIds) {
        if (!$strItemsIds) {
            return array();
        }

        if (is_array($strItemsIds)) {
            return $strItemsIds;
        }

        $strItemsIds = trim(str_replace("  ", ' ', $strItemsIds));
        if (!$strItemsIds) {
            return array();
        }

        $strItemsIds = str_replace(array(", ", " ,"), ',', $strItemsIds);
        $itemsIds = explode(',', $strItemsIds);

        return $itemsIds;
    }

    public static function ConvertirGionesToUcfirst($text, $gion = '_') {
        if (!$text) {
            return $text;
        }

        $posGion = strpos($text, $gion);
        if ($posGion === false) {
            $text = ucfirst($text);
            return $text;
        }

        $text = strtolower($text);

        $text = str_replace($gion, " ", $text);
        $text = ucwords($text);
        $text = str_replace(' ', '', $text);

        return $text;
    }

    public static function GetNameClassModelListFromName($nameListModel) {
        $nameListModel = trim($nameListModel);
        if (!$nameListModel) {
            return $nameListModel;
        }

        $nameListModel = self::ConvertirGionesToUcfirst($nameListModel);

        $nameClass = Exj::GetPrefixClassApp();
        $nameClass .= ucfirst($nameListModel);
        $nameClass .= 'ListModel';

        return $nameClass;
    }

    static function CalcularEdad($fechaNacimiento) {
        if (!$fechaNacimiento) {
            return '';
        }
        list($ano, $mes, $dia) = explode("-", $fechaNacimiento);
        $ano_diferencia = date("Y") - $ano;
        $mes_diferencia = date("m") - $mes;
        $dia_diferencia = date("d") - $dia;
        if ($dia_diferencia < 0 && $mes_diferencia < 0) {
            $ano_diferencia--;
        }

        return $ano_diferencia;
    }

    /**
     * Renderiza una lista de objetos para adicionar un campo de estado a la lista
     *
     * @param array $items
     * @param string $nameField
     * @param string $fieldAddToList
     * @return array de items
     */
    static function RenderListStatus(&$items, $nameField, $fieldAddToList = 'status_name') {
        if (!$items || count($items) == 0) {
            return $items;
        }

        foreach ($items as &$item) {
            $item->$fieldAddToList = '';
            if (!isset($item->$nameField)) {
                continue;
            }
            $value = $item->$nameField;
            if (is_numeric($value)) {
                $value = intval($value);
            }

            $item->$fieldAddToList = ($value ? ExjText::__('Activo') : ExjText::__('Inactivo'));
        }

        return $items;
    }

    public static function RenderTextSiNo($value) {
        if ($value == '0') {
            $value = 0;
        }

        if ($value) {
            return ExjText::__('Yes');
        }

        return ExjText::__('No');
    }

    public static function RenderPercent($valuePercent, $decimales = 2) {
        if (!$valuePercent) {
            $valuePercent = 0;
        }
        if ($valuePercent > 100) {
            $valuePercent = 100;
        }

        $valuePercent = round($valuePercent, $decimales);
        return "$valuePercent %";
    }

    /**
     * Renderiza el valor a el nro de decimales fijado
     *
     * @param float $valueMoney Si el valor es null o texto vacio retorna texto vacio
     * @param int $decimals opcional por defecto 2
     * @return string
     */
    public static function RenderMoney($valueMoney, $decimals = 2) {
        if ($valueMoney === null || $valueMoney === '') {
            return '';
        }

        return sprintf("%01." . $decimals . "f", $valueMoney);
    }

    public static function RenderDecimal($value, $decimals = 2) {
        if ($value === null || $value === '') {
            return '';
        }

        return sprintf("%01." . $decimals . "f", $value);
    }

    public static function RenderIntRellenoCeros($value, $nroRelleno = 6) {
        if ($value === null || $value === '') {
            return '';
        }

        return sprintf("%'.0" . $nroRelleno . 'd', $value);
    }

    static function isEqual($value1, $value2, $strict = false, $applyTrim = true) {
        if ($strict) {
            return ($value1 === $value2);
        }

        if (is_string($value1) && is_string($value2)) {
            if ($applyTrim) {
                $value1 = trim($value1);
                $value2 = trim($value2);
            }
            return (strtoupper($value1) == strtoupper($value2));
        }

        return ($value1 == $value2);
    }

    static function isEqualLikeAll($texto1, $txtFind) {
        if (self::isEqualLike($texto1, $txtFind, false)) {
            return true;
        }

        $pos = strpos(strtolower($texto1), strtolower($txtFind));
        if ($pos === false) {
            return false;
        }

        return true;
    }

    static function isEqualLike($texto1, $texto2, $compareLen = true) {
        //	echo "|<br/> $texto1 IGUAL A $texto2 ";

        if ($texto1 == $texto2) {
            //		echo " SI ";
            return true;
        }

        $texto1 = trim($texto1);
        $texto2 = trim($texto2);
        if ($compareLen) {
            if (strlen($texto1) != strlen($texto2)) {
                //	echo " NO longdif " . strlen($texto1) .' y '. strlen($texto2);
                return false;
            }
        }

        /*
          $texto1 = str_replace('ñ', 'n', $texto1);
          $texto1 = str_replace('Ñ', 'N', $texto1);

          $texto2 = str_replace('ñ', 'n', $texto2);
          $texto2 = str_replace('Ñ', 'N', $texto2);

          $texto1 = str_replace("\n", '', $texto1);
          $texto2 = str_replace("\n", '', $texto2);
          $texto2 = str_replace("\r", '', $texto2);
         */

        $codeCmp = strcasecmp($texto1, $texto2);
        if ($codeCmp == 0) {
            // echo " SI ";
            return true;
        }

        if ($codeCmp < 0) {
            $codeCmp *= -1;
        }

        // vemos si se trata de tildes
        if ($codeCmp >= 128 && $codeCmp <= 133) {
            // echo " SI ";
            return true;
        }

        /*
          if ($codeCmp == 32) {
          echo " SI (issue cod 32) ";
          return true;
          }
         */

        //	echo " NO (codigo: $codeCmp) ";
        return false;
    }

    static function isValidDateChars($dateRaw) {
        if (!$dateRaw) {
            return true;
        }
        $dateRaw = trim($dateRaw);
        if (!$dateRaw) {
            return true;
        }

        $dateRaw .= '';

        $numChars = strlen($dateRaw);
        if ($numChars <= 6) {
            return false;
        }

        $charsValid = array('-', '/', '\\', '.', '|', ':');
        $isValid = true;
        for ($i = 0; $i < $numChars; $i++) {
            $char = substr($dateRaw, $i, 1);
            if (is_numeric($char)) {
                continue;
            }
            if (!in_array($char, $charsValid)) {
                $isValid = false;
                break;
            }
        }

        return $isValid;
    }
    
    public static function PrintDebugBacktrace($limitBacktrace=3){
    	 if (!function_exists('debug_backtrace')) {
    	 	return ;
    	 }
    	 
    	 $debugTraces = debug_backtrace();
    	 $nItem = 0;
    	 foreach ($debugTraces as $itemDebug) {
    	 	$nItem += 1;
    	 	if ($nItem == 1) {
    	 		continue;
    	 	}
    	 	
    	 	if ($limitBacktrace && $nItem > $limitBacktrace+1) {
    	 		break;
    	 	}
    	 	
    	 	$info = (isset($itemDebug['class']) ? $itemDebug['class']:'');
    	 	$info .= (isset($itemDebug['type']) ? $itemDebug['type']:'');
    	 	$info .= (isset($itemDebug['function']) ? $itemDebug['function']:'');
    	 	$args = (isset($itemDebug['args']) ? $itemDebug['args'] : null);
    	 	$info .= '(';
    	 	if (!empty($args)) {
    	 		$info .= '<'. count($args).' parámetros>';
    	 	}    	 	
    	 	$info .= ')';
    	 	
    	 	echo '<br>['. ($nItem-1). "] file: ". $itemDebug['file'].': '. $itemDebug['line']. " $info";
    	 }
    	 
    }

    /**
     * Busca strings en un string
     *
     * @param string $str
     * @param mixed $valuesToSearch Puede ser string o array de string
     * @param bool $isCaseInsensitive
     * @return bool
     */
    public static function StrSearchStr($str, $valuesToSearch, $isCaseInsensitive = false) {
        if ($valuesToSearch === null) {
            return false;
        }
        
        if (empty($valuesToSearch)) {
        	// echo "<br>".__METHOD__." str: $str 2do parámetro vacio debug:";
        	// self::PrintDebugBacktrace();
        	
        	return false;
        }

        if (!$str) {
            if (is_string($valuesToSearch)) {
                if ($str === $valuesToSearch) {
                    return true;
                }
                if ($isCaseInsensitive && strtolower($str) == strtolower($valuesToSearch)) {
                    return true;
                }
            }

            return false;
        }
        
        $posFound = false;
        if (is_array($valuesToSearch)) {
            foreach ($valuesToSearch as $valueToSearch) {
                if (empty($valueToSearch)) {
		        	// echo "<br>".__METHOD__." str: $str valueToSearch vacio valuesToSearch: ". print_r($valuesToSearch, true);
		        	//self::PrintDebugBacktrace();
		        	
		      		continue;
		        }
		        
            	if ($isCaseInsensitive) {
                    $posFound = stripos($str, $valueToSearch);
                } else {
                    $posFound = strpos($str, $valueToSearch);
                }

                if ($posFound !== false) {
                    break;
                }
            }
        } else {
            if ($isCaseInsensitive) {
                $posFound = stripos($str, $valuesToSearch);
            } else {
                $posFound = strpos($str, $valuesToSearch);
            }
        }

        return ($posFound === false ? false : true);
    }

    public static function GetMesesNombres() {
        $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");

        return $meses;
    }

    /**
     * Retorna el nombre del mes
     *
     * @param int $mesNum
     * @param string $default
     * @return string
     */
    public static function RenderMonthStr($mesNum, $default = '') {
        $indexMes = intval($mesNum) - 1;
        if ($indexMes < 0) {
            return $default;
        }

        $mesesArray = self::GetMesesNombres();

        if (isset($mesesArray[$indexMes])) {
            return $mesesArray[$indexMes];
        }

        return $default;
    }

    /**
     * Retorna una fecha para presentacion. ej: 13 de Marzo 2015
     *
     * @param string $fechaStr
     * @return string
     */
    public static function RenderDateDisplayStr($fechaStr) {
        $timeEmision = strtotime($fechaStr);

        $mes = self::RenderMonthStr(date("m", $timeEmision));

        $dateStr = date("d", $timeEmision) . " de $mes ";
        $dateStr .= date("Y", $timeEmision);

        return $dateStr;
    }

    static function RenderDatesRange($date1, $date2) {
        $text = '';
        if ($date1 == $date2) {
            $date2 = new DateTime($date2);
            $text .= $date2->format('j \d\e F \d\e\l Y');
        } else {
            $date1 = new DateTime($date1);
            $date2 = new DateTime($date2);

            $text .= $date1->format('j \d\e F');
            $text .= ' al ';
            $text .= $date2->format('j \d\e F \d\e\l Y');
        }

        $mesesIng = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        $meseEsp = self::GetMesesNombres();

        $text = str_replace($mesesIng, $meseEsp, $text);

        return $text;
    }

    /**
     * Calcula la direncia entre fechas
     *
     * @param string $date1 Fecha mayor
     * @param string $date2 Fecha menor
     * @param bool $addOneDay Indica si se suma un dia o no
     * @return int Número de dias de diferencia
     */
    public static function DatesDif($date1, $date2, $addOneDay = true) {
        if (!is_integer($date1))
            $date1 = strtotime($date1);
        if (!is_integer($date2))
            $date2 = strtotime($date2);

        return floor(abs($date1 - $date2) / 60 / 60 / 24) + ($addOneDay ? 1 : 0);
    }

    /**
     * Compara la fecha del primer parámetro entre la fecha inicial y final
     *
     * @param string $dateRaw
     * @param string $dateInitial
     * @param string $dateEnd
     * @return bool
     */
    static function IsBetweenDates($dateRaw, $dateInitial = '', $dateEnd = '') {
        if ((!$dateInitial && !$dateEnd) && $dateRaw) {
            return true;
        }
        if (!$dateRaw) {
            return false;
        }

        $dateRaw = strtotime($dateRaw);

        if ($dateInitial) {
            $dateInitial = strtotime($dateInitial);
        }

        if ($dateEnd) {
            $dateEnd = strtotime($dateEnd);
            $dateEnd = date('Y-m-d 23:59:59', $dateEnd);
            $dateEnd = strtotime($dateEnd);
        }

        if ($dateInitial && $dateEnd) {
            return ($dateRaw >= $dateInitial && $dateRaw <= $dateEnd);
        }

        if ($dateInitial) {
            return ($dateRaw >= $dateInitial);
        }

        if ($dateEnd) {
            return ($dateRaw <= $dateEnd);
        }

        return false;
    }

    static function render_SINO($value) {
        if (!$value || $value == "0") {
            return ExjText::__('NO');
        }

        return ($value ? ExjText::__('YES') : ExjText::__('NO'));
    }

    public static function RenderSizeKBFile($sizeBytes) {
        $sizeKB = $sizeBytes / 1024;
        $sizeKB = round($sizeKB, 2);

        return "$sizeKB KB";
    }

    /* renombrado: Render_sizeBytes --> RenderSizeBytes */
    public static function RenderSizeBytes($size, $decimales = 2) {
        if (!$size) {
            return "0 Bytes";
        }

        $nameSize = array(" Bytes", " KB", " MB", " GB", " TB");

        return round($size / pow(1024, ($i = floor(log($size, 1024)))), $decimales) . $nameSize[$i];
    }
    
    public static function AddMonthToDate($fecha, $numMeses = 1, $format = "d/m/Y") {
        return date($format, strtotime("$fecha +$numMeses month"));
    }

    public static function AddDayToDate($fecha, $numDias = 1, $format = "d/m/Y") {
        return date($format, strtotime("$fecha +$numDias day"));
    }

    static function AddWeekToDate($fecha, $numSemanas = 1, $format = "d/m/Y") {
        return date($format, strtotime("$fecha +$numSemanas week"));
    }

    /**
     * Ordena un arreglo de objetos
     *
     * @param array $items Pasado por referencia
     * @param string $nameField Nombre del campo de la prop del objeto que se desea oredenar.
     * @param bool $isNumeric Defecto false
     * @param bool $isOrderAsc Defecto true
     * @return array de posiciones del orden
     */
    static function SortArrayOfObjects(&$items, $nameField, $isNumeric = null, $isOrderAsc = true) {
        if (count($items) <= 1) {
            return $items;
        }

        $nameField = trim($nameField);
        if (!$nameField) {
            return false;
        }

        $itemFirst = $items[0];

        if ($isNumeric === null && $itemFirst->$nameField) {
            $isNumeric = is_numeric($itemFirst->$nameField);
        }

        $sortFlag = SORT_STRING;

        if ($isNumeric) {
            $sortFlag = SORT_NUMERIC;
        }

        // -------------------
        if (!$isNumeric) {
            $hash = array();

            foreach ($items as $key => $record) {
                $hash[$record->$nameField . $key] = $record;
            }

            (!$isOrderAsc) ? krsort($hash, $sortFlag) : ksort($hash, $sortFlag);

            $items = array();

            foreach ($hash as $record) {
                $items[] = $record;
            }

            return $items;
        }
        // -------------------


        $position = array();
        $newRow = array();

        foreach ($items as $i => $obj) {
            $position[$i] = $obj->$nameField;
            $newRow[$i] = $obj;
        }



        if ($isOrderAsc) {
            asort($position, $sortFlag);
        } else {
            arsort($position, $sortFlag);
        }


        $arraySorted = array();

        //	print_r($position);

        foreach ($position as $i => $pos) {
            $arraySorted[] = $newRow[$i];
        }

        $items = $arraySorted;

        return $position;
    }

    static function GetNameMonth($fecha = null) {
        if (!$fecha) {
            $fecha = date("d/m/Y");
        }

        $numMes = date("m", strtotime($fecha));
        $numMes = intval($numMes);

        $nameMes = '';
        switch ($numMes) {
            case 1:
                $nameMes = 'ENERO';
                break;
            case 2:
                $nameMes = 'FEBRERO';
                break;
            case 3:
                $nameMes = 'MARZO';
                break;
            case 4:
                $nameMes = 'ABRIL';
                break;
            case 5:
                $nameMes = 'MAYO';
                break;
            case 6:
                $nameMes = 'JUNIO';
                break;
            case 7:
                $nameMes = 'JULIO';
                break;
            case 8:
                $nameMes = 'AGOSTO';
                break;
            case 9:
                $nameMes = 'SEPTIEMBRE';
                break;
            case 10:
                $nameMes = 'AGOSTO';
                break;
            case 11:
                $nameMes = 'NOVIEMBRE';
                break;
            case 12:
                $nameMes = 'DICIEMBRE';
                break;
        }

        $nameMes = ExjText::__($nameMes);

        return $nameMes;
    }

    /**
     * Devuelve un array según la paginación dada, si start y limit son vacios retorna el mismo array pasado por parámetro
     *
     * @param array $items Arreglo de objetos
     * @param int $start
     * @param int $limit
     * @return array
     */
    static function GetArrayPaging($items, $start, $limit) {
        if (!$items || count($items) == 0) {
            return $items;
        }
        if (!$start && !$limit) {
            return $items;
        }

        $itemsReturn = array();

        $posItem = 0;
        foreach ($items as $item) {
            $posItem += 1;

            if ($start >= $posItem) {
                continue;
            }

            $itemsReturn[] = $item;
            if (count($itemsReturn) >= $limit) {
                break;
            }
        }

        return $itemsReturn;
    }

}

?>