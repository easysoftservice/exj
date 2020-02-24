<?php

defined('_JEXEC') or die('Restricted access');

global $exj;

// 1: upper, 0: lower, 2: no convierte, deja como vienen del ADODB
// if (!defined('ADODB_ASSOC_CASE')) define('ADODB_ASSOC_CASE', 2);

/**
 * Clase base del framework
 *
 */
class Exj extends ExjObject {

    const NAME_SPACE = 'Exj';

    const DIR_STORAGE_APP = 'storage/app';
    const DIR_STORAGE_LOGS = 'storage/logs';

    const FILE_INDEX_AJAX = 'index3.php';
    const MSG_ERROR_DISPLAY = 'ERROR 1001. An error occurred try again later.';
    const ACL_AXO_VALUE_ADD = 'add';
    const ACL_AXO_VALUE_SAVE = 'save';
    const ACL_AXO_VALUE_EDIT = 'edit';
    const ACL_AXO_VALUE_TRASH = 'trash';
    const ACL_AXO_VALUE_READONLY = 'readOnly';
    const ACL_AXO_VALUE_VIEW = 'view';

    const DIR_STORAGE_FILES = 'storage/app/files';

    const ESTADO_OK = 0;
    const ESTADO_ERROR = 1;

    // NAMESPACE DE LA UI. MODULOS
    const NS_UI_MODULES = 'Exj.ui.modules';



    // ESTADOS DE LOS TIPO DE MENSAJES SERVIDOS AL CLIENTE
    const MSG_TIPO_NINGUNO = 0;
    const MSG_TIPO_INFO = 1;
    const MSG_TIPO_ERROR = 2;
    const MSG_TIPO_WARNING = 3;
    const MSG_TIPO_NOTIFY = 4; // SOLO PRESENTA EL MSG Y DESAPARECE DESPUES DE 3 SEG.
    const MSG_TIPO_HTML = 6; // SE PRESENTA EL HTML EN UNA VENTANA


    // Manejo de errores
    const TIPO_ERROR_NINGUNO = 0;
    const TIPO_ERROR_DESCONOCIDO = 99;
    const TIPO_ERROR_DATABASE=1;
    const TIPO_ERROR_FILE=3;
    const TIPO_ERROR_USERACCESS=4;
    const TIPO_ERROR_SERVICIOFTP=5;
    const TIPO_ERROR_VALIDINGDATA=6;
    const TIPO_ERROR_BUFFER=15;
    const TIPO_ERROR_EXCEPTION=16;
    const TIPO_ERROR_DELAYED=17;

    
    /**
     * (UTC-05:00) Bogotá, Lima, Quito
     * - 5 HORAS
     */
    const OFFSET_TIME_ZONE = -5;
    // hora universal
    // const OFFSET_TIME_ZONE = 0;

    const ND_COMPONENT_BUZONENTRADA = 'RepFacturas';
    const ND_COMPONENT_PREDIOSURBANOS = 'vu_mod_prediosurbanos';
    const ND_COMPONENT_PATENTESMUNICIPALES = 'vu_mod_patentesmunici';
    const ND_COMPONENT_CONTRUBUCIONMEJORAS = 'vu_mod_contrubucionme';
    const ND_COMPONENT_TITULOSCREDITO = 'vu_mod_tituloscredito';

    protected static $pathDirBase='';

    private $_verAppClient = ''; // esto es leido al iniciar
    private $_id_empresaUI = 0; // esto es leido al iniciar
    private $_sendedClient; // informa si se ha enviado en Json al cliente
    private $_consume_time_start = 0, $_includeDataErrors = false, $_error;
    private $_request = null;
    // manejo de debug trace ---
    private $_bufferDebug = array();
    private $_enableDebug = false;
    private $_lastDemoraSeconds = 0;
    private $_controllerRaw = '';
    public $returnHTML = true;
    private $_hLogData = null;
    private $_mainframe=null;
    private $_lastInstanceRequest = null, $_lastInstanceDatabase = null;
    private static $_cfgExj = null;
    
    public function __construct(JSite  $mainframe) {
        // setlocale(LC_CTYPE, 'es_ES');
        $this->_mainframe = $mainframe;

        $this->_verAppClient = $this->getParamRequest('verApp');
        $this->_id_empresaUI = $this->getParamRequest('id_empresa', 0, false);


        $this->_error = new stdClass();

        $this->_error->msgError = '';
        $this->_error->typeError = Exj::TIPO_ERROR_NINGUNO;
        $this->_sendedClient = false;


        //	print_r($this->lastInstanceRequest());

        $this->_controllerRaw = $this->lastInstanceRequest()->controller;
    }

    public static function GetVersionApp() {
        return self::GetValueCfg('versionApp', '');
    }


    public static function GetTitleApp() {
        return self::GetValueCfg('titleApp', '');
    }

    public static function GetTitleCompanyApp() {
        return self::GetValueCfg('companyApp', '');
    }

    public static function GetNameApp() {
        return self::GetValueCfg('nameApp', '');
    }

    public static function GetValueCfg($prop, $defValue=null)
    {
        $cfg = self::GetCfg();
        return (isset($cfg->$prop) ? $cfg->$prop : $defValue);
    }

    public static function GetCfg(){
        if (!self::$_cfgExj) {
            if (!class_exists('CfgExj')) {
                require_once(self::GetPathBase()."/CfgExj.php");                
            }

            self::$_cfgExj = new CfgExj();
        }

        return self::$_cfgExj;
    }

    public function lastInstanceRequest(){
        if (!$this->_lastInstanceRequest) {
            $this->_lastInstanceRequest = new ExjRequest();
        }

        return $this->_lastInstanceRequest;
    }

    /**
    * @return JSite|JApplication
    */
    public function getMainframe(){
        return $this->_mainframe;
    }

    /**
    * @return ExjRequest
    */
    public static function InstanceRequest(){
        global $exj;
        return $exj->lastInstanceRequest();
    }

    public function lastInstanceDatabase(){
        if (!$this->_lastInstanceDatabase) {
            $this->_lastInstanceDatabase = new ExjDatabase();
        }

        return $this->_lastInstanceDatabase;
    }

    /**
    * @return ExjDatabase
    */
    public static function InstanceDatabase(){
        global $exj;
        return $exj->lastInstanceDatabase();
    }

    public static function MemoryUsage($rendering = false){
        $bytes = memory_get_usage();
        if ($rendering) {
            $bytes = ExjUtil::RenderSizeBytes($bytes, 4);
        }

        return $bytes;
    }


    static function GetPathComponents() {
        return self::GetPathBase() . "/components";
    }

    public static function GetPathLibraries() {
        return self::GetPathBase() . "/libraries";
    }

    public static function GetPathAppWeb() {
        return self::GetPathBase() . "/app/web";
    }    

    public function getControllerRaw() {
        return $this->_controllerRaw;
    }

    static function GetNameFormToken() {
        $htmlToken = JHTML::_('form.token');
        $htmlToken = str_replace("<input", "", $htmlToken);
        $htmlToken = trim(str_replace("/>", "", $htmlToken));

        $partes = explode(" ", $htmlToken);
        $nameFormToken = '';
        foreach ($partes as $parte) {
            $propiedades = explode("=", $parte);
            if (count($propiedades) <= 1) {
                continue;
            }
            if ($propiedades[0] == 'name') {
                $nameFormToken = $propiedades[1];
                $nameFormToken = trim(str_replace('"', '', $nameFormToken));
                break;
            }
        }

        return $nameFormToken;
    }
    
    public static function PrintBackTrace($message='', $maxTrace=15){
    	ExjObject::PrintBackTrace($message, $maxTrace);
    }

    

    /**
     * Devuelve el href para la UI
     *
     * @param string $nameAction Nombre de la acción del controller actual
     * @param array $paramsUrl arreglo de clave, valor
     * @param string $nameController Nombre del controlador
     * @param string $optionGlobal Nombre del componente
     */
    public function getHrefForUI($nameAction, $paramsUrl = null, $nameController = '', $optionGlobal = '') {
        $href = self::FILE_INDEX_AJAX . "/";
        if ($nameController) {
            $href .= $nameController;
        } else {
            $href .= $this->_controllerRaw;
        }
        $href .= "/$nameAction";
        if ($optionGlobal) {
            $href .= "?option=" . $optionGlobal;
        } else {
            global $option;
            $href .= "?option=" . $option;
        }

        $href .= "&verApp=" . self::GetVersionApp();
        $href .= "&no_html=1";

        if ($paramsUrl) {
            if (count($paramsUrl)) {
                foreach ($paramsUrl as $nameParam => $value) {
                    $href .= "&$nameParam=$value";
                }
            }
        }

        return $href;
    }

    private static $_pathDirSrc='';
    public static function GetPathDirSrc() {
        if (!self::$_pathDirSrc) {
            self::$_pathDirSrc = realpath(__DIR__.'/../..');
            self::$_pathDirSrc = str_replace('\\', '/', self::$_pathDirSrc);
        }

        return self::$_pathDirSrc;
    }

    public static function GetPathDirSrcExj() {
        return self::GetPathDirSrc() . '/exj';
    }

    public static function GetPathDirSrcWeb() {
        return self::GetPathDirSrc() . '/web';
    }

    public static function GetPathResources() {
        $path = trim(self::GetValueCfg('pathResources', ''));
        
        if ($path) {
            $pathReal = ExjString::ConvertBathSlash(realpath($path));
            if ($pathReal) {
                $path = $pathReal;
            }
            else {
                ExjLog::error("CfgExj. pathResources. No existe ruta: $path");
                $path = '';
            }
        }

        if (!$path) {
            $path = self::GetPathDirSrcExj().'/resources';
        }

        return $path;
    }

    static function GetURIBase() {
        return JURI::base();
    }

    /**
     * Incluye la lib de excel para el uso de: PHPExcel_IOFactory
     *
     */
    public static function IncludePHPExcel() {
        if (!class_exists('PHPExcel')) {
            $path = self::GetPathLibraries();
            $path .= "/codeplex/phpexcel/";

            require($path . 'PHPExcel.php'); 
        }

        return;

        
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            class_alias("PhpOffice\PhpSpreadsheet\Spreadsheet", "PHPExcel");
            class_alias("PhpOffice\PhpSpreadsheet\Worksheet\PageSetup", "PHPExcel_Worksheet_PageSetup");

            class_alias("PhpOffice\PhpSpreadsheet\Worksheet\PageMargins", "PHPExcel_Worksheet_PageMargins");

            class_alias("PhpOffice\PhpSpreadsheet\Document\Security", "PHPExcel_DocumentSecurity");
            
            
            class_alias("PhpOffice\PhpSpreadsheet\Worksheet\Protection", "PHPExcel_Worksheet_Protection");

            
            class_alias("PhpOffice\PhpSpreadsheet\Style\Alignment", "PHPExcel_Style_Alignment");

            
            class_alias("PhpOffice\PhpSpreadsheet\Style\Border", "PHPExcel_Style_Border");
            class_alias("PhpOffice\PhpSpreadsheet\Style\Fill", "PHPExcel_Style_Fill");
            class_alias("PhpOffice\PhpSpreadsheet\Style\NumberFormat", "PHPExcel_Style_NumberFormat");
            
            class_alias("PhpOffice\PhpSpreadsheet\IOFactory", "PHPExcel_IOFactory");


            class_alias("PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooterDrawing", "PHPExcel_Worksheet_HeaderFooterDrawing");

            class_alias("PhpOffice\PhpSpreadsheet\Worksheet\HeaderFooter", "PHPExcel_Worksheet_HeaderFooter");

            class_alias("PhpOffice\PhpSpreadsheet\Worksheet\Drawing", "PHPExcel_Worksheet_Drawing");
            
        }
    }

    /**
     * Incluye ib de word para el uso de 
     *
     */
    public static function IncludePHPWord() {
        if (!class_exists('PhpWord')) {
            $path = self::GetPathLibraries();
            $path .= "/phpoffice/phpword/PhpWord3_0/";

            require($path.'PhpWord.php');
            require_once($path . 'Autoloader.php');
        }

        //   echo '1. clase JResponse existe: '. (class_exists('JResponse') ? "si":"no");
        if (class_exists('\PhpOffice\PhpWord\PhpWord')) {
            if (!class_exists('PHPWord')) {
                /* NOTE: Incluir esta linea, hay conflictos con las clases de joomla */
                class_exists('JResponse');

                \PhpOffice\PhpWord\Autoloader::register();

                /* desde libs de joomla */
                /* jimport('phpword.library.autoload'); */

                /* se crean alias de clases por compatibilidad del phpword anterior */
                class_alias("PhpOffice\PhpWord\PhpWord", "PHPWord");
                class_alias("PhpOffice\PhpWord\Style\Font", "PHPWord_Style_Font");
                class_alias("PhpOffice\PhpWord\Element\Section", "PHPWord_Section");
                class_alias("PhpOffice\PhpWord\Element\TextRun", "PHPWord_Section_TextRun");
                class_alias("PhpOffice\PhpWord\Shared\Html", "PHPWord_Shared_Html");
                // class_alias("PhpOffice\PhpWord\IOFactory", "PHPWord_IOFactory");
            }

            return true;
        }
        else{
            echo 'ERROR. No se encontró clase: \PhpOffice\PhpWord\PhpWord';
        }

        /*
          echo "<br>clases declaradas:<br>";
          print_r(get_declared_classes());
         */
    }

    public function getTimeDemora($title = '') {
        $timeApp = $this->getSecondsDemora();
        return "$title $timeApp seg.";
    }

    public function getSecondsDemora() {
        if (!defined('_TIME_INI_APP')) {
            define( '_TIME_INI_APP', microtime(true));
        }

        $time_end = microtime(true);
        $timeApp = $time_end - _TIME_INI_APP;
        $timeApp = round($timeApp, 4);
        return $timeApp;
    }

    function setBufferDebugTimeDemora($nameMethod, $line) {
        $title = "$nameMethod linea: $line";

        $segDemora = $this->getSecondsDemora();
        $ultimaDemora = round($segDemora - $this->_lastDemoraSeconds, 4);
        $this->_lastDemoraSeconds = $segDemora;

        return $this->setBufferDebug("$title. Demora: $segDemora seg. Ultima demora: $ultimaDemora seg.");
    }

    public function setBufferDebugMethod($nameMethod, $textExtra = '') {
        $textHtml = '<div style="color:blue;">--- METODO: ' . $nameMethod . '</div>';
        if ($textExtra) {
            $textHtml .= ' --- ' . $textExtra;
        }
        $this->setBufferDebug($textHtml);
    }

    public function setBufferDebugError($strDebugError, $textExtra = '') {
        $textHtml = '<div style="color:red;">--- ERROR: ' . $strDebugError . '</div>';
        if ($textExtra) {
            $textHtml .= ' --- ' . $textExtra;
        }
        $this->setBufferDebug($textHtml);
    }

    public function setBufferDebug($strDebug) {
        if ($this->_enableDebug) {
            $this->_bufferDebug[] = $strDebug;
        }
        return $strDebug;
    }

    public function setEnabledDebugTrace($enable = true) {
        $this->_enableDebug = $enable;
        $this->setBufferDebugTimeDemora(__METHOD__, __LINE__);
    }

    function getBufferDebugTrace($charSeparatorLine = '<br />') {
        $buffers = $this->_bufferDebug;
        foreach ($buffers as &$buffer) {
            // if (is_array($buffer) || is_object($buffer)) {
            $buffer = var_export($buffer, true);
            // }
        }

        return implode($charSeparatorLine, $buffers);
    }

    public function writeBufferDebugTrace($charSeparatorLine = '<br />') {
        $buffer = $this->getBufferDebugTrace($charSeparatorLine);
        if (!$buffer) {
            return;
        }
        echo $buffer;
    }

    public static function GetPathBase(){
        if (!self::$pathDirBase) {
            // self::$pathDirBase = dirname(dirname(__DIR__));
            self::$pathDirBase = JPATH_BASE;
            self::$pathDirBase = str_replace('\\', '/', self::$pathDirBase);
        }

        return self::$pathDirBase;
    }

    public static function GetPathDirStorageFiles() {
        return (self::GetPathBase() . '/'.self::DIR_STORAGE_FILES);
    }

    public static function GetPathDirStorageApp() {
        return (self::GetPathBase() . '/'.self::DIR_STORAGE_APP);
    }

    public static function GetPathDirStorageLogs($nameFile='') {
        $path = (self::GetPathBase() . '/'.self::DIR_STORAGE_LOGS);
        if ($nameFile) {
            $path .= '/'.$nameFile;
        }

        return $path;
    }

    public static function GetPathDirStorageFilesIn() {
        return self::GetPathDirStorageFiles().'/in';
    }

    public static function GetDirectoryDownload() {
        return self::GetPathDirStorageFiles().'/out/';
    }

    public static function GetDirectoryDownloadFTP() {
        return self::GetDirectoryDownload() . "ftp/";
    }

    // manejo de debug trace ---
    public function consumeTimeStart() {
        $this->_consume_time_start = $this->microtime_float();
    }

    public function consumeSecondsServer() {
        if ($this->_consume_time_start == 0) {
            return 0;
        }
        return round($this->microtime_float() - $this->_consume_time_start, 2);
    }

    public static function TrasferCharsEncodeISOToUTF8(&$obj) {
        ExjTransferCharacters::encodeISOToUTF8($obj);
    }

    public static function TrasferCharsDecodeUTF8ToISO(&$obj) {
        ExjTransferCharacters::decodeUTF8ToISO($obj);
    }

    static function GetFieldsVarsFromObject($obj) {
        $objVars = null;
        if (is_object($obj)) {
            $objVars = get_object_vars($obj);
        } else {
            $objVars = get_class_vars($obj);
        }

        if (is_null($objVars)) {
            global $exj;
            $exj->setError("El parámetro enviado no es object o clase parametro: $obj");
            return null;
        }

        $names = array();
        foreach ($objVars as $name => $value) {
            $names[] = $name;
        }

        return $names;
    }

    static function AssingValuesToObject($objOrigen, &$objDestino) {
        $objVars = get_object_vars($objOrigen);
        foreach ($objVars as $name => $value) {
            $objDestino->$name = $value;
        }
    }

    /**
     * Agrupa un array de objectos
     *
     * @param array $rows
     * @param string $fieldsGroup
     * @param string $fieldsSum
     * @return array de objetos agrupagos
     */
    static function GetGroupRows($rows, $fieldsGroup, $fieldsSum) {
        if (!is_array($fieldsGroup)) {
            $fieldsGroup = explode(',', $fieldsGroup);
        }
        if (!is_array($fieldsSum)) {
            $fieldsSum = explode(',', $fieldsSum);
        }

        $fieldsGroupStr = implode(',', $fieldsGroup);

        $rowsSummary = array();
        foreach ($rows as $row) {

            $valuesGroup = array();
            foreach ($fieldsGroup as $fieldGroup) {
                $fieldGroup = trim($fieldGroup);
                $valuesGroup[] = $row->$fieldGroup;
            }
            $valuesGroupStr = implode(',', $valuesGroup);

            foreach ($fieldsSum as $fieldSum) {
                $fieldSum = trim($fieldSum);
                if (!isset($rowsSummary[$valuesGroupStr][$fieldSum])) {
                    $rowsSummary[$valuesGroupStr][$fieldSum] = 0;
                }
                $rowsSummary[$valuesGroupStr][$fieldSum] += floatval($row->$fieldSum);
            }
        }

        $rowsGroup = array();
        foreach ($rowsSummary as $valuesGroupStr => $fieldsSum) {
            $valuesGroup = explode(',', $valuesGroupStr);
            $rowGroup = new stdClass();

            foreach ($fieldsSum as $fieldSum => $valueSum) {
                $index = -1;
                foreach ($fieldsGroup as $fieldGroup) {
                    $rowGroup->$fieldGroup = $valuesGroup[++$index];
                }
                $rowGroup->$fieldSum = $valueSum;
            }

            $rowsGroup[] = $rowGroup;
        }

        return $rowsGroup;
    }

// GetGroupRows

    /**
     * Se asignan los valores del Objeto Origen hacia el Destino. Los 2 objetos deben tener los mismo campos
     *
     * @param object $objOrigen
     * @param object $objDestino
     * @param string $methodRef
     */
    function setValuesObjects($objOrigen, $objDestino, $methodRef = '') {
        $fields = self::GetFieldsVarsFromObject($objDestino);
        if ($this->getErrorExist()) {
            return;
        }

        foreach ($fields as $field) {
            $field = trim($field);
            if (!$field) {
                continue;
            }

            if (!isset($objOrigen->$field)) {
                if ($methodRef) {
                    $methodRef = " Método: $methodRef";
                }
                $this->setError("No está definido el campo: $field en el Objeto Origen $methodRef");
                break;
            }

            $objDestino->$field = $objOrigen->$field;
        }
    }

// setValuesObjects

    static function LoadPropertyFromMixed($dataMixed, $nameProperty, &$value) {
        if (!$dataMixed) {
            return false;
        }

        if (is_array($dataMixed)) {
            if (isset($dataMixed[$nameProperty])) {
                $value = $dataMixed[$nameProperty];
                return true;
            }
        } else if (is_object($dataMixed)) {
            if (isset($dataMixed->$nameProperty)) {
                $value = $dataMixed->$nameProperty;
                return true;
            }
        }

        return false;
    }

    public function getParamRequestInt($name, $def = 0) {
        return intval($this->getParamRequest($name, $def, false));
    }

    public function getParamRequest($name, $def = null, $decodeUTFtoISO = true) {
        $params = $this->getParamsRequest();
        if (!$params) {
            return $def;
        }

        $param = $def;

        if (!self::LoadPropertyFromMixed($params, $name, $param)) {
            $param = JRequest::getVar($name, $def);
        }

        if (!$param) {
            return $param;
        }

        if ($decodeUTFtoISO) {
            self::TrasferCharsDecodeUTF8ToISO($param);
        }

        return $param;
    }

    function getParamRequestDecode($name, $def = null, $decodeUTFtoISO = true) {
        $p = $this->getParamRequest($name, $def, $decodeUTFtoISO);
        if (!$p) {
            return $p;
        }

        $p = $this->JsonDeconde($p);

        return $p;
    }
    

    function renderPorcent($valuePorc, $addSimbolPorc = false, $nDecimales = 2, $remplacePointToComa = false) {
        $this->renderFixValueComaToPoint($valuePorc);
        $valuePorc = floatval($valuePorc);

        $nDecCalc = -1;
        $nValCalc = $valuePorc;
        while (++$nDecCalc < $nDecimales) {
            $nValCalc = round($valuePorc, $nDecCalc);
            if ($valuePorc == $nValCalc) {
                break;
            }
        }

        $porcentRet = $nValCalc;

        //$porcentRet = sprintf("%.$nDecCalc".'f', $valuePorc);

        /*
          if ($valuePorc == $nVal2Desc) {
          $porcentRet = sprintf("%.0".'f', $valuePorc);
          }
         */

        if ($addSimbolPorc) {
            $porcentRet .= '%';
        }
        if ($remplacePointToComa) {
            $porcentRet = str_replace('.', ',', $porcentRet);
        }

        return $porcentRet;
    }

// renderPorcent

    /**
     * Renderiza un valor a letras de moneda
     *
     * @param float $valor
     * @param bool $toUpper
     * @return string
     */
    public static function RenderValorALetrasMoneda($valor, $toUpper = false) {
        $letras = self::RenderNumToLetras($valor, $toUpper, true);

        $nomMoneda = 'Dolares';
        if ($toUpper) {
            $nomMoneda = strtoupper($nomMoneda);
        } else {
            $nomMoneda = 'Dólares';
        }

        $letras .= " $nomMoneda";

        return $letras;
    }

    public static function RenderNumToLetras($num, $toUpper = false, $withDecimal = true) {
        $letras = 'Cero';
        if ($toUpper) {
            $letras = strtoupper($letras);
        }

        if (!$num) {
            return $letras;
        }

        $num = floatval($num);

        // constantes de numeros
        $NUMS = array();

        $NUMS['1'] = 'Un';
        $NUMS['2'] = 'Dos';
        $NUMS['3'] = 'Tres';
        $NUMS['4'] = 'Cuatro';
        $NUMS['5'] = 'Cinco';
        $NUMS['6'] = 'Seis';
        $NUMS['7'] = 'Siete';
        $NUMS['8'] = 'Ocho';
        $NUMS['9'] = 'Nueve';

        $NUMS['10'] = 'Diez';
        $NUMS['11'] = 'Once';
        $NUMS['12'] = 'Doce';
        $NUMS['13'] = 'Trece';
        $NUMS['14'] = 'Catorce';
        $NUMS['15'] = 'Quince';
        $NUMS['16'] = 'Dieciséis';
        $NUMS['17'] = 'Diecisiete';
        $NUMS['18'] = 'Dieciocho';
        $NUMS['19'] = 'Diecinueve';

        $NUMS['20'] = 'Veinte';
        $NUMS['30'] = 'Treinta';
        $NUMS['40'] = 'Cuarenta';
        $NUMS['50'] = 'Sincuenta';
        $NUMS['60'] = 'Sesenta';
        $NUMS['70'] = 'Setenta';
        $NUMS['80'] = 'Ochenta';
        $NUMS['90'] = 'Noventa';

        $NUMS['100'] = 'Cien';

        $NUMS['500'] = 'Quinientos';
        $NUMS['700'] = 'Setecientos';
        $NUMS['900'] = 'Novecientos';

        $NUMS['CENTENA'] = 'Ciento';
        $NUMS['CENTENAS'] = 'Cientos';

        $NUMS['MILLAR'] = 'Mil';

        $NUMS['MILLON'] = 'Millón';
        $NUMS['MILLONES'] = 'Millones';

        $nInt = intval($num);
        $txtNum = $nInt . '';

        $letras = '';

        $numMaxDigitos = strlen($txtNum);
        $numDigitos = $numMaxDigitos + 1;
        $foundAndExit = false;
        while (--$numDigitos > 0) {
            $nDigito = substr($txtNum, $numMaxDigitos - $numDigitos, 1);
            $numFix = intval($nDigito);
            if ($numFix == 0) {
                continue;
            }

            if ($numDigitos > 1) {
                $numFix = $numFix * pow(10, $numDigitos - 1);
            }

            $subNum = intval(substr($txtNum, $numMaxDigitos - $numDigitos));
            if ($subNum < 1000) {
                foreach ($NUMS as $key => $valNumLetras) {
                    if ($subNum == $key) {
                        if (trim($letras) != '') {
                            if ($subNum >= 1 && $subNum <= 9) {
                                $letras .= " y ";
                            } else {
                                // $letras .= ", ";
                                $letras .= " ";
                            }
                        }

                        $letras .= "$valNumLetras";

                        $foundAndExit = true;
                        break;
                    }
                }
            }
            if ($foundAndExit) {
                break;
            }


            if ($numFix < 1000) {
                $foundVal = false;
                foreach ($NUMS as $key => $valNumLetras) {
                    if ($key == $numFix) {
                        $foundVal = true;

                        if (trim($letras) != '') {
                            if ($numFix >= 1 && $numFix <= 9) {
                                $letras .= ' y ';
                            } else {
                                // $letras .= ', ';
                                $letras .= ' ';
                            }
                        }

                        // caso especial con el 100
                        $letrasAdd = $valNumLetras;
                        if ($numFix == 100) {
                            $letrasAdd = $NUMS['CENTENA'];
                        } elseif ($numFix > 100 && $numFix < 900 && $numFix != 500 && ($numFix != 700)) {
                            $letrasAdd = $NUMS['CENTENAS'];
                        }

                        $letras .= "$letrasAdd";
                        break;
                    }
                }

                if ($foundVal) {
                    continue;
                }
            }


            switch ($numDigitos) {
                case 1: // unidades
                    $letras .= ' y ' . $NUMS[$nDigito];
                    break;

                case 2: // decenas

                    break;

                case 3: // centenas
                    $letras .= $NUMS[$nDigito];
                    if (intval($nDigito) == 1) {
                        $letras .= ' ' . $NUMS['CENTENA'];
                    } else {
                        $letras .= ' ' . $NUMS['CENTENAS'];
                    }
                    break;

                case 4: // miles 3000
                    $letras .= $NUMS[$nDigito];
                    $letras .= ' ' . $NUMS['MILLAR'] . ' ';
                    break;
                case 5: // miles 30,000
                    // cojemos las 2 letras
                    $parteMillar = intval(substr($subNum + '', 0, 2));
                    $letras .= ' ' . self::RenderNumToLetras($parteMillar, $toUpper, false);
                    $letras .= ' ' . $NUMS['MILLAR'] . ' ';
                    $numDigitos -= 1;
                    break;
                case 6: // miles 300,000
                    // cojemos las 3 letras
                    $parteMillar = intval(substr($subNum + '', 0, 3));
                    $letras .= ' ' . self::RenderNumToLetras($parteMillar, $toUpper, false);

                    $letras .= ' ' . $NUMS['MILLAR'] . ' ';
                    $numDigitos -= 2;
                    break;
                    break;

                // para millones
                case 7: // millones
                    $letras .= $NUMS[$nDigito];

                    if ($nDigito == 1) {
                        $letras .= ' ' . $NUMS['MILLON'] . ' ';
                    } else {
                        $letras .= ' ' . $NUMS['MILLONES'] . ' ';
                    }
                    break;
                case 8: // millones
                    $parteMillon = intval(substr($subNum + '', 0, 2));
                    $letras .= ' ' . self::RenderNumToLetras($parteMillon, $toUpper, false);

                    $letras .= ' ' . $NUMS['MILLONES'];
                    $numDigitos -= 1;
                    break;
                case 9: // millones
                    $parteMillon = intval(substr($subNum + '', 0, 3));
                    $letras .= ' ' . self::RenderNumToLetras($parteMillon, $toUpper, false);

                    $letras .= ' ' . $NUMS['MILLONES'];
                    $numDigitos -= 2;
                    break;
            }
        }; // while

        $letras = trim($letras);


        // -----------------------------------------------
        if ($withDecimal) {
            // $nDec = intval(($num - $nInt) * 100);
            $txtNumFixDecimals = sprintf("%01.2f", $num);
            $posStr = strpos($txtNumFixDecimals, '.');
            $nDec = '00';
            if ($posStr > 0) {
                $nDec = substr($txtNumFixDecimals, $posStr + 1);
            }

            $letras .= " , $nDec/100";
        }

        if ($toUpper) {
            $letras = strtoupper($letras);
        }
        return $letras;
    }

// RenderNumToLetras

    function renderFieldsMoney(&$row, $fields, $nameFieldSimMoneda = 'sim_moneda') {
        if (!is_array($fields)) {
            $fields = explode(",", $fields);
        }
        foreach ($fields as $field) {
            $field = trim($field);
            $sim_moneda = '';
            if (isset($row->$nameFieldSimMoneda)) {
                $sim_moneda = $row->$nameFieldSimMoneda;
            }
            $valueMoney = 0;
            if (isset($row->$field)) {
                $valueMoney = $row->$field;
            } else {
                // si existe el campo y el valor es null, entra tambien aqui
                //			echo "Campo no definido: $field ";
                $valueMoney = null; // para q no presente el valor
            }
            $row->$field = $this->renderMoney($valueMoney, false, $sim_moneda);
        }
    }

// renderFieldsMoney

    function renderFloat($valueFloat, $nDecimals = 2, $charSeparator = '.') {
        // "%01.2f"
        $formatFloat = "%01.$nDecimals" . 'f';

        $formatted = sprintf($formatFloat, $valueFloat);
        if ($charSeparator == '.') {
            return $formatted;
        }

        $formatted = str_replace(".", $charSeparator, $formatted);

        return $formatted;
    }

    function renderMoney($value, $withHTML = false, $sim_moneda = '') {
        $html = '';
        if (is_null($value)) {
            return $html;
        }

        if ($withHTML) {
            $html .= '<div align="right" style="width:60px">';
        }

        $nDecimales = 2;
        $this->renderFixValueComaToPoint($value);

        $html .= sprintf("%.$nDecimales" . 'f', $value);

        if ($withHTML) {
            $html .= '</div>';
        }

        if ($sim_moneda) {
            $html .= " $sim_moneda";
        }

        return $html;
    }

    function renderTimeFromSeconds($nSeconds) {
        $nSeconds = round($nSeconds, 2);
        if ($nSeconds < 60) {
            return "$nSeconds segundos";
        }

        // min
        $nSeconds = round($nSeconds / 60, 2);
        if ($nSeconds < 60) {
            return "$nSeconds minutos";
        }

        // horas
        $nSeconds = round($nSeconds / 60, 2);
        return "$nSeconds horas";
    }

    function renderDate($valueData, $format = "%d-%m-%Y") {
        if (!$format) {
            $format = "%d-%m-%Y";
        }
        return strftime($format, strtotime($valueData));
    }

// renderDate

    function renderDateTime($valueData, $format = "%d-%m-%Y %H:%M:%S") {
        if (!$format) {
            $format = "%d-%m-%Y %H:%M:%S";
        }
        return strftime($format, strtotime($valueData));
    }

// renderDateTime

    function renderValuePointToComa($value) {
        $valueStr = $value . '';

        $valueStr = str_replace('.', ',', $valueStr);

        return $valueStr;
    }

// renderValuePointToComa

    function renderFixValuePointToComa(&$value) {
        $value = $this->renderValuePointToComa($value);
    }

// renderFixValuePointToComa

    function renderFixValuesPointToComa(&$rows, $fields) {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
        }

        foreach ($rows as &$row) {
            foreach ($fields as $field) {
                $field = trim($field);
                $this->renderFixValuePointToComa($row->$field);
            }
        }
    }

// renderFixValuesPointToComa

    /**
     * Por lo general para formateo de datos para la exportacion y/o impresion
     *
     * @param object $obj
     * @param array $rows
     * @param string $fields o array de campos
     * @param string $paramConditional Nombre del parametro
     */
    function renderFixValuesPointToComaConditional($obj, &$rows, $fields, $paramConditional = 'convertPointToComa') {
        if (!isset($obj->$paramConditional)) {
            return;
        }

        if (!$obj->$paramConditional) {
            return;
        }

        $this->renderFixValuesPointToComa($rows, $fields);
    }

// renderFixValuesPointToComaConditional

    function renderValueComaToPoint($value) {
        if ($value == '') {
            return $value;
        }
        $valueStr = $value . '';
        $valueStr = str_replace(',', '.', $valueStr);
        return $valueStr;
    }

// renderValueComaToPoint

    function renderFixValueComaToPoint(&$value) {
        $value = $this->renderValueComaToPoint($value);
    }

// renderFixValuePointToComa

    function numberIsValid(&$txtNumber, $isFloat = true) {
        if (!$txtNumber) {
            $txtNumber = 0;
            return true;
        }
        $txtNumber = trim($txtNumber);
        if (!$txtNumber) {
            $txtNumber = 0;
            return true;
        }

        $txtNumber = str_replace(',', '.', $txtNumber);
        if (!is_numeric($txtNumber)) {
            return false;
        }

        if (is_float($txtNumber)) {
            $txtNumber = floatval($txtNumber);
        } else if (is_int($txtNumber)) {
            $txtNumber = intval($txtNumber);
        } else {
            if ($isFloat) {
                $txtNumber = floatval($txtNumber);
            } else {
                $txtNumber = intval($txtNumber);
            }
        }

        if (is_nan($txtNumber)) {
            return false;
        }

        return true;
    }

// numberIsValid

    function autoCompleteNames(&$apellidos, &$nombres, &$msgError) {
        $apellidos = trim($apellidos);
        $nombres = trim($nombres);
        if ($apellidos && $nombres) {
            return true;
        }

        if (!$apellidos && !$nombres) {
            $msgError = "Apellidos o Nombres son requeridos";
            return false;
        }

        $completeApellidos = true;
        if ($apellidos) {
            $completeApellidos = false;
        }


        // vemos si hay la coma como separador
        $partesComaApe = explode(",", $apellidos);
        if (count($partesComaApe) == 2) {
            if ($completeApellidos) {
                $apellidos = trim($partesComaApe[1]);
                $nombres = trim($partesComaApe[0]);
            } else {
                $apellidos = trim($partesComaApe[0]);
                $nombres = trim($partesComaApe[1]);
            }
            return true;
        }

        $charsTrash = array(",", ";", ".", "\r", "\n", "  ");
        if ($apellidos) {
            $apellidos = str_replace($charsTrash, " ", $apellidos);
        }
        if ($nombres) {
            $nombres = str_replace($charsTrash, " ", $nombres);
        }

        $testNames = trim("$apellidos $nombres");
        $posSpace = strpos($testNames, " ");
        if ($posSpace === false) {
            $msgError = "Solo se ha ingresado un Apellido o Nombre";
            return false;
        }

        $words = explode(" ", $testNames);
        $nWords = count($words);

        $mitad = round($nWords / 2, 0);
        $i = -1;
        $apellidos = array();
        $nombres = array();
        while (++$i < $nWords) {
            if ($i < $mitad) {
                if (!$completeApellidos) {
                    $apellidos[] = $words[$i];
                } else {
                    $nombres[] = $words[$i];
                }
            } else {
                if (!$completeApellidos) {
                    $nombres[] = $words[$i];
                } else {
                    $apellidos[] = $words[$i];
                }
            }
        }

        $apellidos = implode(" ", $apellidos);
        $nombres = implode(" ", $nombres);

        return true;
    }

// autoCompleteNames

    function parseStringToDateSQL($strDate, &$msgError, $formatInput = '') {
        $dateSQL = trim($strDate . '');

        if (!$dateSQL) {
            return self::GetDate();
        }

        $dateSQL = str_replace("  ", " ", $dateSQL);
        $isDateTime = false;
        // 2010-06-02
        // 2010-06-02 00:00:00
        if (strlen($dateSQL) < 10) {
            $msgError = "Fecha: $dateSQL no válida. Longuitud mínima 10";
            return false;
        }

        if (strlen($dateSQL) != 10) {
            if (strlen($dateSQL) < 19) {
                $msgError = "Fecha y Hora: $dateSQL no válido. Longuitud mínima 19";
                return false;
            }
            if (strlen($dateSQL) > 19) {
                $dateSQL = substr($dateSQL, 0, 19);
            }
            $isDateTime = true;
        }

        $dateSQL = str_replace(array("/", "\\", ".", "|", "#", "_", "'", "\t"), "-", $dateSQL);

        $timeStamp = strtotime($dateSQL);
        if ($timeStamp === false) {
            $msgError = "Fecha: $strDate no reconocida";
            return false;
        }

        $format = "%Y-%m-%d";
        if ($isDateTime) {
            $format = "%Y-%m-%d %H:%M:%S";
        }

        // $dateSQL = strftime($format, $timeStamp);
        if (!$formatInput) {
            $formatInput = $format;
        }

        if (!$isDateTime) {
            if (!$this->dateIsValid($dateSQL)) {
                $msgError = "Fecha: $strDate no reconocida";
                return false;
            }
            return $dateSQL;
        }

        // es fecha y hora
        $partesDateTime = explode(" ", $dateSQL);
        if (count($partesDateTime) != 2) {
            $msgError = "Fecha y Hora: $dateSQL ($strDate) no reconocida";
            return false;
        }


        $dateStr = $partesDateTime[0];
        $timeStr = $partesDateTime[1];

        if (!$this->dateIsValid($dateStr)) {
            $msgError = "Fecha y Hora: $dateSQL no reconocida";
            return false;
        }

        $timeStr = str_replace("-", ":", $timeStr);

        $dateSQL = "$dateStr $timeStr";

        return $dateSQL;
    }


    public function dateIsValid(&$txtDate) {
        if (!$txtDate) {
            return false;
        }

        $txtDate = trim($txtDate);
        $nDate = strlen($txtDate);
        if ($nDate < 8) {
            return false;
        }

        $strTime = '';
        if ($nDate > 10) {
            // 2010-06-02 12:12:12
            if ($nDate >= 19) {
                $strTime = trim(substr($txtDate, 11, 8));
                if (strlen($strTime) < 8) {
                    // echo "<p>tiempo menos de 8: $strTime</p>";
                    $strTime = '';
                }
            }
            $txtDate = substr($txtDate, 0, 10);
        }

        // $month, $day, $year
        $partes = split('[/.-]', $txtDate);
        if ($partes == false) {
            return false;
        }

        $nPartes = count($partes);
        if ($nPartes < 3) {
            return false;
        }

        $year = $partes[0];
        $month = $partes[1];
        $day = $partes[2];

        if (!is_numeric($year) || !is_numeric($month) || !is_numeric($day)) {
            return false;
        }

        $isYYYYmmdd = true;
        if (strlen($year) != 4) {
            $year = $partes[2];
            $isYYYYmmdd = false;
        }
        if (strlen($year) != 4) {
            return false;
        }

        if (!$isYYYYmmdd) {
            $day = $partes[0];
            $month = $partes[1];
        }

        if ($month > 12) {
            return false;
        }
        if ($day > 31) {
            return false;
        }
        if ($year <= 1800) {
            return false;
        }

        $txtDate = "$year-$month-$day";
        if ($strTime) {
            $txtDate .= " $strTime";
        }

        return true;
    }


    function convertObjectListToArray($rows, $fields) {
        $retArray = array();
        if (!is_array($rows)) {
            return $retArray;
        }

        foreach ($rows as $row) {
            $rowArray = array();
            foreach ($fields as $field) {
                $rowArray[] = $row->$field;
            }
            $retArray[] = $rowArray;
        }

        return $retArray;
    }


    function convertArrayObjToArray($arrayObj) {
        $data = array();

        foreach ($arrayObj as $obj) {
            $varsObj = get_object_vars($obj);
            $itemsArray = array();
            foreach ($varsObj as $name => $value) {
                $itemsArray[] = $value;
            }
            $data[] = $itemsArray;
        }

        return $data;
    }


    function convertObjectListToArrayWithIndex($rows, $fields, $field_key) {
        $retArray = array();
        if (!is_array($rows)) {
            return $retArray;
        }

        foreach ($rows as $row) {
            $rowArray = array();
            foreach ($fields as $field) {
                $rowArray[] = $row->$field;
            }

            $valueKey = $row->$field_key;

            $retArray[$valueKey][] = $rowArray; // agrupa por indice
        }

        $retArrayIndex = array();
        $index = -1;
        foreach ($retArray as $valueKey => $dataArray) {
            $data = new stdClass();
            $data->$field_key = $valueKey;
            $data->data = $dataArray;

            $retArrayIndex[++$index] = $data;
        }

        return $retArrayIndex;
    }


    function loadResourceFront($option, $path_file) {
        $path = self::GetPathbaseFront($option);

        require_once("$path/$path_file");
    }

    function loadPrinter() {
        $this->loadResourceFront('com_eprinter', 'cnegocios/report.neg.php');
        // $this->loadResourceFront('com_eprinter', 'cdata/eprinter.class.php');
    }

    function JsonEncodeSimple($topics, $printClient = true) {
        $response = new ExjResponse();

        $dataBuffer = ob_get_contents();
        if (self::IsModeDebug()) {
            ///retornar todo la salida para realizar debug
            $response->dataBuffer = $dataBuffer;
        }

        /////////// para listados
        $response->setDataTopics($topics);

        //////// para objetos, datacustom
        $response->data = $obj;
        $response->setMsg($msg, $msgTitle, $msgType);

        if ($topics == '') {
            $response->setMsgError("No listings have served or object data. Ask the Administrator.");
        }

        // ExjTransferCharacters::encodeISOToUTF8($response);
        ExjTransferCharacters::encodeISOToUTF8($topics);

        if ($printClient) {
            $callback = $this->getParamRequest("callback");
            if ($callback) {
                $response->writeWithCallback($callback);
                return;
            }

            $response->writeOnlyTopics();
            return;
        }

        return $response->to_json_onlyTopics();
    }

// JsonEncodeSimple

    public function setError($msgError, $typeError = Exj::TIPO_ERROR_DESCONOCIDO) {
        $this->_error->msgError = $msgError;
        $this->_error->typeError = $typeError;

        $this->setBufferDebug($msgError);
        ExjEvent::Fire(__FUNCTION__, array($msgError, $typeError), $this);

        return $msgError;
    }

    function addArrayFromObject(&$dataArray, $obj, $fieldObj, $valueIsInt = true) {
        $fieldObj = trim($fieldObj);
        if (!$fieldObj) {
            return;
        }
        if (isset($obj->$fieldObj)) {
            $item = $obj->$fieldObj;
            if ($valueIsInt) {
                $item = intval($item);
            }
            if (!in_array($item, $dataArray)) {
                $dataArray[] = $item;
            }
        }
    }

    function implodeSmart($partes, $glue = ",") {
        $result = "";
        if (!$partes) {
            return $result;
        }
        if (is_array($partes)) {
            if (count($partes) == 0) {
                return $result;
            }
            $result = implode($glue, $partes);
        } else {
            $result = trim($partes);
        }

        return $result;
    }

// implodeSmart

    private $_isModeConsole = false;

    public function fixModeConsole($enable = true) {
        $this->_isModeConsole = $enable;
    }

    public function isModeConsole() {
        return $this->_isModeConsole;
    }

    public static function IsAppModeConsole() {
        global $exj;
        return $exj->isModeConsole();
    }

    public static function LogWriteStr($str) {
        global $exj;
        return $exj->logWrite($str);
    }

    public function setErrorValidating($msgError) {
        return $this->setError($msgError, Exj::TIPO_ERROR_VALIDINGDATA);
    }

    /**
     * Envia un error de una excepción
     *
     * @param Exception $ex
     * @return bool
     */
    public function setErrorException($ex) {
        if (is_string($ex)) {
            return $this->setError($ex, Exj::TIPO_ERROR_EXCEPTION);
        }

        return $this->setError($ex->getMessage(), Exj::TIPO_ERROR_EXCEPTION);
    }

    /**
     * Devuelve un objeto ExjResponse con el error que esta en el objeto base
     *
     * @return ExjResponse instancia de la clase ExjResponse
     */
    public function getResponseError() {
        $response = new ExjResponse();

        if ($this->getErrorExist()) {
            $response->setMsgError($this->getErrorMsg());
        }

        return $response;
    }

    public function setErrorDB($msgError = '') {
        if (!$msgError) {
            $msgError = $this->lastInstanceDatabase()->getErrorMsg();
        }
        if (!$msgError) {
            return false;
        }
        $this->setError($msgError, Exj::TIPO_ERROR_DATABASE);

        return $msgError;
    }

    public function getError() {
        return $this->_error;
    }

    public function getErrorExist() {
        return ($this->_error->typeError != Exj::TIPO_ERROR_NINGUNO);
    }

    public function haveError() {
        return $this->getErrorExist();
    }

    /**
     * Devuelve el tamaño máximo permitido para subir archivos
     *
     * @return int bytes
     */
    public function getSizeMaxUpload() {
        return 4096000;
    }

    public function setErrorMsg($msgError) {

        if (self::IsModeDebug()) {
            $this->setError($msgError, Exj::TIPO_ERROR_VALIDINGDATA);
        } else {
            $this->setError($msgError, Exj::MSG_TIPO_ERROR);
        }

        return $this;
    }

    /**
     * Devuelve solo el Mensaje de error registrado
     *
     * @return string
     */
    public function getErrorMsg() {
        return $this->_error->msgError;
    }

    public function getArrayInts($arrayObjs, $field) {
        $ints = array();
        foreach ($arrayObjs as $item) {
            $ints[] = intval($item->$field);
        }

        return $ints;
    }

    public function getErrorText($returnErrorRaw = false) {
        if (!$this->getErrorExist()) {
            return '';
        }

        $textError = self::GetTextTypeError($this->_error->typeError, true, true);

        switch ($this->_error->typeError) {
            case Exj::TIPO_ERROR_DESCONOCIDO:
                $textError .= ".<br/>Referencia: ";
                break;

            case Exj::TIPO_ERROR_VALIDINGDATA:
                $textError .= "<br/>";
                $returnErrorRaw = true;
                break;
        }

        $textError .= '<br/>';

        if ($returnErrorRaw) {
            $textError .= $this->_error->msgError;
        } else {
            $textError .= "Ocurrieron errores internos en el sistema.<br/>Ha sido notificado a soporte sobre el error.";
            if (ExjUser::IsRolSuperAdmin()) {
                // echo $this->_error->msgError;
                $textError .= '<br/>Referencia:<br/>' . $this->_error->msgError;
            }
        }

        $this->parseTextResult($textError);

        return $textError;
    }

    /**
     * Determina si un objeto ya está definido. Usado por lo general para variables estáticas.
     *
     * @param object $obj
     * @param string $keyObj
     * @param mixed $valueKeyObj
     * @return bool
     */
    static function IsDefinedObj($obj, $keyObj = null, $valueKeyObj = null) {
        if (!is_object($obj)) {
            return false;
        }
        if ($keyObj === null) {
            return true;
        }

        if (isset($obj->$keyObj) && $obj->$keyObj == $valueKeyObj) {
            return true;
        }

        return false;
    }

    /**
     * Devuelve el tipo de error
     *
     * @param int $valueTypeError
     */
    public static function GetTextTypeError($valueTypeError, $toUpper = false, $addBold = false) {
        if (!$valueTypeError) {
            $valueTypeError = 0;
        }
        $valueTypeError = intval($valueTypeError);

        $textTypeError = $valueTypeError;
        $color = '';
        switch ($valueTypeError) {
            case Exj::TIPO_ERROR_BUFFER:
                $textTypeError = "Buffer";
                break;
            case Exj::TIPO_ERROR_DATABASE:
                $textTypeError = "Base de datos";
                $color = 'red';
                break;

            case Exj::TIPO_ERROR_NINGUNO:
                $textTypeError = "Ninguno";
                break;

            case Exj::TIPO_ERROR_VALIDINGDATA:
                $textTypeError = "Validando datos";
                $color = 'green';
                break;

            case Exj::TIPO_ERROR_FILE:
                $textTypeError = "Procesando Archivo";
                break;

            case Exj::TIPO_ERROR_EXCEPTION:
                $textTypeError = "Exception";
                $color = 'red';
                break;

            case Exj::TIPO_ERROR_DELAYED:
                $textTypeError = "Demora";
                $color = 'blue';
                break;


            case Exj::TIPO_ERROR_DESCONOCIDO:
                $textTypeError = "Desconocido";
                break;

            case Exj::TIPO_ERROR_USERACCESS:
                $textTypeError = "Acceso de Usuario";
                break;

            case Exj::TIPO_ERROR_SERVICIOFTP:
                $textTypeError = "Servicio FTP";
                break;


            case Exj::MSG_TIPO_ERROR:
                $textTypeError = "Error";
                $color = 'red';
                break;

            /*
              case Exj::MSG_TIPO_WARNING:
              $textTypeError = "Advertencia";
              break;
             */

            default:
                $textTypeError = "Error tipo $valueTypeError Desconocido";
                break;
        }

        if ($toUpper) {
            $textTypeError = strtoupper($textTypeError);
        }
        if ($addBold) {
            $textTypeError = '<b>' . $textTypeError . '</b>';
        }
        if ($color) {
            $textTypeError = '<span style="color:' . $color . '">' . $textTypeError . '</span>';
        }

        return $textTypeError;
    }

    static function GetLookupTypeError($toUpper = false) {
        $item = array();

        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_DATABASE, $toUpper);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_EXCEPTION);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_BUFFER);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_VALIDINGDATA);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_DELAYED);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_FILE);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_SERVICIOFTP);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_USERACCESS);
        $item[] = self::GetDataTypeError(Exj::TIPO_ERROR_NINGUNO);

        return $item;
    }

    static function GetDataTypeError($typeError, $toUpper = false, $isTextHTML = false, $addBold = false) {
        $dataTypeError = new stdClass();
        $dataTypeError->value = intval($typeError);
        $dataTypeError->text = $dataTypeError->value;
        $dataTypeError->color = '';
        $dataTypeError->isCritical = false;
        $dataTypeError->showMsgRaw = false;

        switch ($dataTypeError->value) {
            case Exj::TIPO_ERROR_BUFFER:
                $dataTypeError->text = "Buffer";
                break;
            case Exj::TIPO_ERROR_DATABASE:
                $dataTypeError->text = "Base de datos";
                $dataTypeError->isCritical = true;
                break;

            case Exj::TIPO_ERROR_NINGUNO:
                $dataTypeError->text = "Ninguno";
                break;

            case Exj::TIPO_ERROR_VALIDINGDATA:
                $dataTypeError->text = "Validando datos";
                $dataTypeError->color = 'green';
                $dataTypeError->showMsgRaw = true;
                break;

            case Exj::TIPO_ERROR_FILE:
                $dataTypeError->text = "Procesando Archivo";
                break;

            case Exj::TIPO_ERROR_EXCEPTION:
                $dataTypeError->text = "Exception";
                $dataTypeError->isCritical = true;
                break;

            case Exj::TIPO_ERROR_DELAYED:
                $dataTypeError->text = "Demora";
                $dataTypeError->color = 'blue';
                $dataTypeError->showMsgRaw = true;
                break;

            case Exj::TIPO_ERROR_DESCONOCIDO:
                $dataTypeError->text = "Desconocido";
                break;

            case Exj::TIPO_ERROR_USERACCESS:
                $dataTypeError->text = "Acceso de Usuario";
                break;

            case Exj::TIPO_ERROR_SERVICIOFTP:
                $dataTypeError->text = "Servicio FTP";
                break;


            case Exj::MSG_TIPO_ERROR:
                $dataTypeError->text = "Error";
                $dataTypeError->isCritical = true;
                break;


            default:
                $dataTypeError->text = "Error desconocido tipo $dataTypeError->value";
                break;
        }

        if ($dataTypeError->isCritical) {
            $dataTypeError->color = 'red';
        }
        if ($dataTypeError->showMsgRaw && !$dataTypeError->color) {
            $dataTypeError->color = 'green';
        }

        if ($toUpper) {
            $dataTypeError->text = strtoupper($dataTypeError->text);
        }
        if ($addBold && $isTextHTML) {
            $dataTypeError->text = "<b>$dataTypeError->text</b>";
        }

        if ($isTextHTML && $dataTypeError->color) {
            $dataTypeError->text = "<span style='color:" . $dataTypeError->color . "'>$dataTypeError->text</span>";
        }

        return $dataTypeError;
    }

    static function GetServerInfo($varServer, $valueDefault = null) {
        if (!isset($_SERVER[$varServer])) {
            return $valueDefault;
        }

        return $_SERVER[$varServer];
    }

    static function GetServerPathInfo() {
        return self::GetServerInfo('PATH_INFO', '');
    }

    static function GetServerQuery($decode = false) {
        if (!$decode) {
            return self::GetServerInfo('QUERY_STRING', '');
        }

        return urldecode(self::GetServerInfo('QUERY_STRING', ''));
    }

    static function GetServerRequestMethod() {
        return self::GetServerInfo('REQUEST_METHOD', '');
    }

    static function GetServerUserAgent() {
        return self::GetServerInfo('HTTP_USER_AGENT', '');
    }

    /**
     * Devuelve la url del cliente, ej: http://localhost/svum/
     *
     * @return string
     */
    static function GetServerURLClient() {
        return self::GetServerInfo('HTTP_REFERER', '');
    }

    public static function GetServerDelayed() {
        $requestTime = self::GetServerInfo('REQUEST_TIME', 0);
        if (!$requestTime) {
            return -1;
        }

        return round(microtime(true) - $requestTime, 3);
    }

    public function parseTextResult(&$text) {
        if (!$text) {
            return $text;
        }
        if ($this->returnHTML) {
            return $text;
        }

        $text = str_replace("<br/>", "\n", $text);

        /*
          $text = str_replace("<b>", "", $text);
          $text = str_replace("</b>", "", $text);
          $text = str_replace("<p>", "", $text);
          $text = str_replace("</p>", "", $text);
         */

        $text = strip_tags($text);

        return $text;
    }

    function isValidTypeDep($type_dep) {
        if (!$type_dep) {
            return false;
        }
        $isValid = false;
        switch ($type_dep) {
            case _T_AGC_TYPE_EPAGOS:
            case _T_AGC_TYPE_AGENTE:
            case _T_AGC_TYPE_CORRESPONSAL:
                $isValid = true;
                break;
        }

        return $isValid;
    }

    function hubieronErrores($line, $method) {
        $hayError = false;

        if ($this->lastInstanceDatabase()->getErrorMsg()) {
            $this->JsonEncode('', 0, '', "There are SQL errors", "<b>Method</b>: $method <b>Line</b>: $line<br /><b>Reference</b>: " . $this->lastInstanceDatabase()->getErrorMsg(), Exj::MSG_TIPO_ERROR);
            $hayError = true;
        }

        return $hayError;
    }

// hubieronErrores

    function JsonEncodeError() {
        return $this->JsonEncodeMsgError('Errors occurred on the server', $this->getErrorText());
    }

// JsonEncodeError

    function JsonEncodeMsgError($msgTile, $msg) {
        return $this->JsonEncode('', 0, '', $msgTile, $msg, Exj::MSG_TIPO_ERROR);
    }

// JsonEncodeMsg

    function JsonEncodeMsgNotify($msgTile, $msg) {
        return $this->JsonEncode('', 0, '', $msgTile, $msg, Exj::MSG_TIPO_NOTIFY);
    }

// JsonEncodeMsgNotify

    function JsonEncodeMsgInfo($msgTile, $msg) {
        return $this->JsonEncode('', 0, '', $msgTile, $msg, Exj::MSG_TIPO_INFO);
    }

// JsonEncodeMsgInfo

    function JsonEncodeMsgWarning($msgTile, $msg) {
        return $this->JsonEncode('', 0, '', $msgTile, $msg, Exj::MSG_TIPO_WARNING);
    }

// JsonEncodeMsgWarning

    function JsonEncodeMsgSaved($saved, $msgTile, $msg = 'Data have been successfully saved') {
        $obj = $saved;
        if ($saved) {
            return $this->JsonEncode('', 0, $obj, $msgTile, $msg, Exj::MSG_TIPO_NOTIFY);
        }
        return $this->JsonEncode('', 0, $obj, $msgTile, $msg, Exj::MSG_TIPO_INFO);
    }

// JsonEncodeMsgSaved

    function JsonEncodeObjMsgNotify($obj, $msgTile, $msg) {
        return $this->JsonEncode('', 0, $obj, $msgTile, $msg, Exj::MSG_TIPO_NOTIFY);
    }

// JsonEncodeObjMsgNotify

    function JsonEncodeObjMsgInfo($obj, $msgTile, $msg) {
        return $this->JsonEncode('', 0, $obj, $msgTile, $msg, Exj::MSG_TIPO_INFO);
    }

// JsonEncodeObjMsgInfo

    function JsonEncodeObjMsgWarning($obj, $msgTile, $msg) {
        return $this->JsonEncode('', 0, $obj, $msgTile, $msg, Exj::MSG_TIPO_WARNING);
    }

// JsonEncodeObjMsgWarning

    function JsonEncodeObjMsgError($obj, $msgTile, $msg) {
        return $this->JsonEncode('', 0, $obj, $msgTile, $msg, Exj::MSG_TIPO_ERROR);
    }

// JsonEncodeObjMsgError

    function JsonEncodeObj($obj) {
        return $this->JsonEncode('', 0, $obj);
    }

// JsonEncodeObj

    /*
      function isModeDebug(){
      return true;

      $cfg = new JConfig();
      return ($cfg->debug ? true: false);
      }
     */

    function JsonEncode($topics, $totalTopics, $obj = '', $msgTitle = '', $msg = '', $msgType = Exj::MSG_TIPO_NINGUNO, $printClient = true) {
        $response = new ExjResponse();

        /////////// para listados
        $response->setDataTopics($topics, $totalTopics);

        //////// para objetos, datacustom
        $response->data = $obj;


        if (!$msg) {
            $msgType = Exj::MSG_TIPO_NINGUNO;
        }

        $sendErrorToFile = false;
        if ($msgType == Exj::MSG_TIPO_ERROR) {
            $sendErrorToFile = true;
            $this->logWrite($msg, Exj::MSG_TIPO_ERROR);
        }

        $dataBuffer = ob_get_contents();
        if (self::IsModeDebug()) {
            ///retornar todo la salida para realizar debug
            $response->dataBuffer = $dataBuffer;
        }

        if (!$sendErrorToFile) {
            if (strlen($dataBuffer) > 15) {
                $sendErrorToFile = true;
                $this->logWrite($dataBuffer, Exj::TIPO_ERROR_BUFFER);
            }
        }

        $response->setMsg($msg, $msgTitle, $msgType);

        ExjTransferCharacters::encodeISOToUTF8($response);

        if ($printClient) {
            $callback = $this->getParamRequest("callback");
            if ($callback) {
                $response->writeWithCallback($callback);
                $this->_sendedClient = true;
                return false;
            }

            $response->write();
            $this->_sendedClient = true;
            return true;
        }

        return $response->to_json();
    }

// JsonEncode

    function JsonEncodeSendedClient() {
        return $this->_sendedClient;
    }

    
    /**
     * Convirte número entero a letras
     * @param int $num Valor a convertir, si el valor es decimal no se toman en cuenta la parte decimal
     * @param bool $toLower Predeterminado false
     * @return string Retorna en letras, el valor 1 lo transforma como Uno
     */
    public static function ConvertNumberIntToLetters($num, $toLower = false){
        $valueLetters = ExjNumeroToLetras::GetInstance()->convertir($num, false);

        $valueLetters = ' ' . $valueLetters . ' ';
        $valueLetters = str_replace(" Un ", ' Uno ', $valueLetters);



        if($toLower){
            $valueLetters = strtolower($valueLetters);
        }
        $valueLetters = trim($valueLetters);
        
       // echo "<br>ConvertNumberIntToLetters $num = $valueLetters";
        
        return $valueLetters;
    }

    /**
     * Escribe en el archivo de logs
     *
     * @param string $text
     * @param int $typeError Por defecto Exj::TIPO_ERROR_NINGUNO
     */
    public function logWrite($text, $typeError = null) {
        if (!$this->_hLogData) {
            $this->_hLogData = new ExjHandlerLogData();
        }

        if (!$typeError) {
            $typeError = Exj::TIPO_ERROR_NINGUNO;
        }

        $this->_hLogData->writeLogLn($text, $typeError, true);
        return $this;
    }

    public function logWriteError() {
        if (!$this->haveError()) {
            return false;
        }

        $this->logWrite($this->_error->msgError, $this->_error->typeError);
    }

    public function logWriteDelayed($msgExtra = '') {
        $delayed = self::GetServerDelayed();
        if ($delayed > self::GetValueCfg('maxDelayedLog', 3)) {
            if ($msgExtra === null) {
                $msgExtra = '';
            }
            if ($msgExtra) {
                $msgExtra .= "<br/>";
            }
            $msgExtra .= "El proceso demoró $delayed segundos";

            return $this->logWrite($msgExtra, Exj::TIPO_ERROR_DELAYED);
        }

        return false;
    }

    private static function _GetInstanceAccessUser() {
        static $accessUser;

        if (isset($accessUser)) {
            return $accessUser;
        }
        

        $accessUser = new AppGlobalDataAccessUser();
        if (!$accessUser->isValid()) {
            return null;
        }

        return $accessUser;
    }

    private static function _IsAccessNew(AppGlobalDataAccessUser $accessUser, $grp_name = '') {
        if (!$accessUser)
            return false;
        return $accessUser->isAccessNew(null, $grp_name);
    }

    private static function _IsAccessSave(AppGlobalDataAccessUser $accessUser, $grp_name = '') {
        if (!$accessUser)
            return false;
        return $accessUser->isAccessSave(null, $grp_name);
    }

    private static function _IsAccessTrash(AppGlobalDataAccessUser $accessUser, $grp_name = '') {
        if (!$accessUser)
            return false;
        return $accessUser->isAccessTrash(null, $grp_name);
    }

    private static function _IsAccessView(AppGlobalDataAccessUser $accessUser, $grp_name = '') {
        if (!$accessUser)
            return false;
        return $accessUser->isAccessView(null, $grp_name);
    }

    public static function IsUserAccessNew($component = '') {
        if (ExjUser::IsRolSuperAdmin())
            return true;
        return self::_IsAccessNew(self::_GetInstanceAccessUser(), $component);
    }

    public static function IsUserAccessSave($component = '') {
        if (ExjUser::IsRolSuperAdmin())
            return true;
        return self::_IsAccessSave(self::_GetInstanceAccessUser(), $component);
    }

    public static function IsUserAccessTrash($component = '') {
        if (ExjUser::IsRolSuperAdmin())
            return true;
        return self::_IsAccessTrash(self::_GetInstanceAccessUser(), $component);
    }

    public static function IsUserAccessView($component = '') {
        if (ExjUser::IsRolSuperAdmin())
            return true;
        return self::_IsAccessView(self::_GetInstanceAccessUser(), $component);
    }

    /**
     * Indica si el usuario tiene acceso a editar algún control, si es super usuario siempre tiene acceso
     *
     * @param string $component Si no se indica el componente es el componente que llamó al server
     * @return bool
     */
    public static function IsUserAccessEdit($component = '') {
        if (ExjUser::IsRolSuperAdmin()) {
            return true;
        }

        if (self::IsUserAccessSave($component)) {
            return true;
        }

        if (self::IsUserAccessNew($component)) {
            return true;
        }

        return false;
    }

    /**
     * Retorna el ID del grupo de usuario de joomla
     *
     * @return mixed Si el usuario no está louado retorna false, sino el valor int
     */
    static function GetUserGID() {
        $jUser = & JFactory::getUser();
        if (!$jUser->id) {
            return false;
        }

        return $jUser->gid;
    }

    /**
     * Devuelve el nombre de usuario
     *
     * @return string Ej: bvcordova
     */
    static function GetUserUserName() {
        $jUser = & JFactory::getUser();
        return $jUser->username;
    }

    /**
     * Devuelve el nombre del tipo de usuario logueado
     *
     * @return string Ej: Administrador
     */
    public static function GetUserUserType() {
        $jUser = & JFactory::getUser();
        return $jUser->usertype;
    }

    private function _validateActionGlobal(){
        if ($this->_controllerRaw == 'globals') {
            $actionReq = $this->getActionRequest();
            if ($actionReq == 'loginUser' || $actionReq == 'getDataGlobal' || $actionReq == 'changeEmpresa') {
                return true;
            }

        }

        return false;
    }

    public function validateAccess($msg = '') {
        if ($this->_validateActionGlobal()) {
            return true;
        }

        if (!$this->isResquestApi() && 
            (!ExjUser::IsLogin() || !ExjUser::GetIdCompania()))
        {
            $response = new ExjResponse();
            $response->setDataIniSession("Finalizó el tiempo de sesión.<br/>Debe ingresar de nuevo usuario y contraseña.")
               ->writeExit();
        }

        $config = new JConfig();
        if ($config->offline == 1) {
            // fuera de linea
            $response = new ExjResponse();

            $response->setDataOffline($config->offline_message)->writeExit();
        }

        if ($this->isResquestApi()) {
            return true;
        }

        // verificamos la versión del cliente con la del servidor
        if ($this->_verAppClient != self::GetVersionApp()) {
            // echo "verApp: $this->_verAppClient = ".self::GetVersionApp();
            ExjResponse::NewResponseOffline()->writeExit();
        }

        if ($this->_id_empresaUI && $this->_id_empresaUI > 0) {
            if ($this->_id_empresaUI != ExjUser::GetIdEmpresa()) {
                // se cambio en la UI en el mismo navegador
                // "_id_empresaUI: $this->_id_empresaUI idEmpresa: ".ExjUser::GetIdEmpresa()
                ExjResponse::NewResponseReloadApp()->writeExit();
            }
        }

        return true;
    }

    public static function AddParamToPOST($varName, $value) {
        global $_POST;

        if (!is_array($_POST)) {
            $_POST = array();
        }

        $_POST[$varName] = $value;
    }

    /**
     * Devuelve el path del componente, de acuerdo con el parametro
     * $option
     *
     * @param string $option, nombre del componente
     * @return string
     */
    static function GetPathbaseFront($option = '') {
        if ($option) {
            $option = "/$option";
        }

        return self::GetPathComponents() . $option;
    }

    public function getFullPathTemplateComponent($component, $nameFile = '') {
        return $this->_getPathFront('tmpl', $component, $nameFile);
    }


    public static function GetComponentCurrent($readComponentFromRequest = false) {
        if ($readComponentFromRequest) {
            $nameComponent = ExjRequest::GetParam('nameComponent');
            if ($nameComponent) {
                return $nameComponent;
            }
        }

        global $option;

        return $option;
    }

    public static function TrimPrefixComponent($nameCmp) {
        if (!$nameCmp) {
            return $nameCmp;
        }
        $nameCmp = trim($nameCmp);
        $pos = strpos($nameCmp, self::PREFIX_COMP_APP);
        if ($pos === false) {
            $pos = strpos($nameCmp, self::PREFIX_COMP_FRAMEWORK);
            if ($pos !== false) {
                $pos = strlen(self::PREFIX_COMP_FRAMEWORK);
            }
        } else {
            $pos = strlen(self::PREFIX_COMP_APP);
        }

        if ($pos !== false) {
            $pos += 1;
            $nameCmp = substr($nameCmp, $pos);
        }

        return $nameCmp;
    }

    private function _getPathFront($varname, $_option = '', $nameCustom = '') {
        // echo '<br/>'.__METHOD__ . " option: $_option";

        $result = null;
        if (!$_option) {
            global $option;
            $_option = $option;
        }
        if (!$_option) {
            return '';
        }

        $path = self::GetPathbaseFront($_option);
        if ($nameCustom) {
            $name = $nameCustom;
        } else {
            $name = self::TrimPrefixComponent($_option);
        }

        switch ($varname) {
            case 'view':
                $result = "$path/views/$name.$varname";
                break;


            case 'xml':
                $result = "$path/xml/$name.$varname";
                break;

            case 'rep':
            case 'rpt':
                $result = "$path/reports/$name.rpt";
                break;

            case 'tmpl.handler':
            case 'tmpl':
                $result = "$path/views/tmpl/$name.$varname";
                break;


            case 'include':
                $result = "$path/$name.$varname";
                break;


            default:
                $result = "$path/$name";
                break;
        }

        $result .= ".php";
        // echo "<br />result: $result "; 
        if (!file_exists($result)) {
            echo "No existe: $result";
            self::PrintBackTrace(__METHOD__." NO EXISTE ARCHIVO");
            // $result = $path;
        }
        return $result;
    }

    static function GetDS() {
        return DIRECTORY_SEPARATOR;
    }

    /**
     * Devuelve fecha de estampado en segundos con precision de milisegundos
     *
     * @return float
     */
    function microtime_float() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }

    /**
     * Devuelve fecha desde el server
     *
     * @param int $offset_time
     * @param string $format
     * @param int $timeStamp Defecto fecha y hora actual del server
     * @return string
     */
    static function GetDateTimeFromServer($offset_time = null, $format = '%Y-%m-%d %H:%M:%S', $timeStamp = null) {
        if ($offset_time === null) {
            $offset_time = ExjUser::GetOffsetTime();
        }

        if ($timeStamp === null) {
            $timeStamp = time();
        }


        $date = strftime($format, $timeStamp + ($offset_time));
        return $date;
    }

    static function RenderDateTimeOffset($dateTime, $offset_time = null, $format = '%Y-%m-%d %H:%M:%S') {
        if ($dateTime && ($dateTime == '0000-00-00 00:00:00' || $dateTime == '0000-00-00')) {
            $dateTime = '';
        }

        if (!$dateTime) {
            return $dateTime;
        }

        $timeStamp = $dateTime;
        if (is_string($dateTime)) {
            $timeStamp = strtotime($dateTime);
        }

        return self::GetDateTime($format, $offset_time, $timeStamp);
    }

    /**
     * Obtiene el año actual
     *
     * @return int
     */
    public static function GetDateYear() {
        return intval(self::GetDateTime('%Y'));
    }

    /**
     * Devuelve la fecha y hora actual del sistema
     *
     * @return string
     */
    public static function GetDateTime($format = '%Y-%m-%d %H:%M:%S', $offset_time = null, $timeStamp = null) {
        if ($offset_time === null) {
            $offset_time = ExjUser::GetOffsetTime();
            //	echo "<br/>1. offset_time: $offset_time Ofc: " . ExjUser::GetNombreEmpresa();

            if ($offset_time != 0) {
                $offset_time *= -1;
                $offset_time -= 3600 * 3;
            } elseif (!$offset_time) {
                $offset_time = self::GetOffsetTimeFromCountry();
                //		echo "<br/>Tomando desde el pais offset_time: $offset_time";
            }
        }

        //	echo "<br/>2. offset_time: $offset_time Ofc: " . ExjUser::GetNombreEmpresa();

        return self::GetDateTimeFromServer($offset_time, $format, $timeStamp);
    }

    /**
     * Devuelve la fecha actual del primer dia del mes
     *
     * @return string Fecha en formato año-mes-01
     */
    public static function GetDateFirstDayOfMonth() {
        return self::GetDateTime("%Y-%m-01");
    }

    /**
     * Devuelve el año actual
     *
     * @return int
     */
    static function GetAnioActual() {
        return self::GetDateTime("%Y");
    }

    /**
     * Devuelve el mes actual
     *
     * @return int
     */
    static function GetMesActual() {
        return self::GetDateTime("%m");
    }

    static function GetDiaActual() {
        return self::GetDateTime("%d");
    }

    /**
     * Obtiene el año y mes actual en formato: aaaa-mm
     *
     * @return string
     */
    public static function GetAnioMesActual() {
        return self::GetDateTime("%Y-%m");
    }

    function getTime($offset_time = null) {
        return self::GetDateTime('%H:%M:%S', $offset_time);
    }

    public static function WriteLnConsole($str, $trimBoldHTML=true){
        if ($str) {
            if ($trimBoldHTML) {
                $str = str_ireplace(array('<b>', '</b>'), '', $str);
            }
        }

        self::Write("\n".$str);
    }

    public static function WriteRawLnConsole($str){
        self::WriteLnConsole($str, false);
    }

    public static function WriteLn($data=''){
        self::Write($data."\n");
    }

    public static function Write($data){
        if ($data === null || $data === '') {
            return;
        }

        if ($data && (is_object($data) || is_array($data))) {
            $data = print_r($data, true);
        }

        if (ExjRequest::IsConsole()) {
            $data = str_replace(array("<br>", "<br/>"), "\n", $data);
            if (strpos($data, '<hr>') !== false) {
                $data = str_replace('<hr>', "\n".str_repeat('-', 80)."\n", $data);
            }
        }
        else{
            $data = str_replace("\n", "<br>", $data);
        }

        echo $data;
    }

    /**
     * Devuelve la fecha actual en formato Y-m-d
     *
     * @param int $offset_time No es necesaro
     * @return string
     */
    public static function GetDate($offset_time = null) {
        return self::GetDateTime('%Y-%m-%d', $offset_time);
    }

    public static function BuildURLProxy($controller, $model = 'view', $isRestFul = false, $params = null) {

        return self::BuildURLModel($controller, $model, '', $isRestFul, $params);
    }

    /**
     * Construye una url para llamada del modelo MVC
     *
     * @param string $controller
     * @param string $model
     * @param string $nameComponent
     * @param bool $isRestFul Por defacto false
     * @param array $params Por defacto null, debe darse clave y valor
     * @return string url construida
     */
    public static function BuildURLModel($controller, $model = 'view', $nameComponent = '', $isRestFul = false, $params = null)
    {
        /*
        if (!$nameComponent) {
            global $option;
            $nameComponent = $option;
        }
        */

        // $url = self::FILE_INDEX_AJAX. "/$controller/$model?option=$nameComponent";
        $url = self::FILE_INDEX_AJAX. "/$controller/$model?option=";
        $url .= '&verApp=' . self::GetVersionApp();
        $url .= '&no_html=1';
        if ($isRestFul) {
            $url .= '&isRestFul=true';
        } else {
            $url .= '&isRestFul=false';
        }

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_numeric($key) && $key >= 0) {
                    continue;
                }

                $url .= '&' . "$key=$value";
            }
        }

        return $url;
    }

    function session_ini() {
        if (!session_id()) {
            global $mosConfig_live_site;
            session_name(md5($mosConfig_live_site));
            session_start(); // not even sure if really needed here...
        }
    }

    function str_decrypt($key, $data) {
        return $data;
    }

    function str_encrypt($key, $data) {
        return $data;
    }

    static function FormatMoney($value) {
        $value = floatval($value);

        $formatted = sprintf("$ %01.2f", $value);

        return $formatted;
    }

    static function GetPathComponentCurrent($optionCustom = '') {
        if ($optionCustom) {
            return self::GetPathComponents() . "/$optionCustom";
        }

        global $option;

        return self::GetPathComponents() . "/$option";
    }

    public function getActionRequest() {
        return $this->lastInstanceRequest()->action;
    }

    /**
     * Devuelve todos los parámetros enviados desde el cliente
     *
     * @return object
     */
    public function getParamsRequest() {
        if (empty($this->lastInstanceRequest()->params)) {
            return $_REQUEST;
        }

        return $this->lastInstanceRequest()->params;
    }

    /**
     * Devuelve los parámetros desde el cliente, usado para edición de entidades
     *
     * @return object
     */
    public function getDataChangedRequest() {
        return $this->lastInstanceRequest()->paramDataChanged;
    }

    public function setRestFulToRequest($isRestful) {
        $this->lastInstanceRequest()->setRestFul($isRestful);
        return $this;
    }

    /**
     * Despacha el requerimiento de la UI
     * @param bool $autoLoadModel
     * @param bool $restful true para comportamiento de crud por default
     */
    public function dispatchRestful($autoLoadModel = true, $restful = true) {
        $this->setBufferDebugTimeDemora(__METHOD__, __LINE__);

        /*
          Ejemplo:

          A partir de controller: globals
          Controlador:
          Nombre del archivo: globals.controller.php
          Nombre de la Clase: ExjGlobalsController

          Modelo:
          Nombre del archivo: global.model.php
          Nombre de la Clase: ExjGlobalModel
         */

        $nameFileController = $this->getNameFileController();

        // Get Controller
        $ClassController = $this->getNameClassController();
        $controller = new $ClassController();

        $this->setBufferDebugTimeDemora("-> Instanciado controller: $ClassController", __LINE__);

        // Dispatch request
        $this->setRestFulToRequest($restful);

        $this->setBufferDebugMethod(__METHOD__, "Se ha instanciado ExjRequest");
        $this->setBufferDebug('this->lastInstanceRequest()->paramDataChanged');
        $this->setBufferDebug($this->lastInstanceRequest()->paramDataChanged);

        $responseRaw = $controller->dispatch($this->lastInstanceRequest());

        $this->setBufferDebugTimeDemora("-> Despachado controller: $ClassController accion: " . $this->getActionRequest(), __LINE__);


        $responseApi = $responseRaw;
        if (!$responseApi) {
            $responseApi = new ExjResponse();
            $responseApi->setMsgError("No se ha retornado nada desde el controlador: $nameFileController");
        } elseif (!is_object($responseApi)) {
            $responseApi = new ExjResponse();
            if (strlen($responseRaw) > 99) {
                $responseRaw = substr($responseRaw, 0, 99) . ' ...';
            }
            $responseApi->setMsgError("El controlador: $nameFileController, debe retornar una instancia de ExjResponse.<br/>Se esta retornando: $responseRaw");
        } elseif (!($responseApi instanceof ExjResponse)) {
            $responseApi = new ExjResponse();
            $responseApi->setMsgError("El controlador: $nameFileController, debe retornar una instancia de ExjResponse.");
        }

        $this->setBufferDebugTimeDemora(__METHOD__, __LINE__);

        if ($this->lastInstanceRequest()->callback) {
            $this->writeBufferDebugTrace();
            $responseApi->writeWithCallback($this->lastInstanceRequest()->callback);
            return;
        }

        $this->writeBufferDebugTrace();
        $responseApi->write();
    }

    public function getNameFileController() {
        $nameFileController = $this->_controllerRaw;
        if (!$nameFileController) {
            $nameFileController = 'NoSePudoDeterminarControlador';
        }
        $nameFileController .= '.controller.php';
        return $nameFileController;
    }

    /**
     * Devuelve el prefijo de las clases de la aplicación
     *
     * @return string
     */
    public static function GetPrefixClassApp() {
        return 'App';
    }

    /**
     * Convierte a nombre de clase dado un nombre
     *
     * @param string $name
     * @return string
     */
    public static function CovertToNameClass($name) {
        if (!$name) {
            return '';
        }

        $nameClass = ucfirst($name);
        if (strpos($nameClass, '_') !== false) {
            $nameClass = str_replace('_', ' ', $nameClass);
            $nameClass = ucwords($nameClass);
            $nameClass = str_replace(' ', '', $nameClass);
        }

        return $nameClass;
    }

    /**
     * Devuelve nombre de una clase tipo criteria
     *
     * @param string $nameCriteriaModel
     * @return string
     */
    static function GetNameClassCriteria($nameCriteriaModel) {
        $nameClassCriteria = self::CovertToNameClass($nameCriteriaModel);

        $nameClassCriteria = self::GetPrefixClassApp() . $nameClassCriteria . 'CriteriaModel';
        return $nameClassCriteria;
    }

    public static function GetNameClassFooter($nameFooterModel) {
        $nameClass = self::CovertToNameClass($nameFooterModel);

        $nameClass = self::GetPrefixClassApp() . $nameClass . 'FooterModel';
        return $nameClass;
    }

    public static function GetNameClassReadOnly($nameReadOnlyModel) {
        $nameClass = self::CovertToNameClass($nameReadOnlyModel);

        $nameClass = self::GetPrefixClassApp() . $nameClass . 'ReadOnlyModel';
        return $nameClass;
    }

    public static function GetNameClassEditable($nameEditableModel) {
        $nameClassEditable = self::CovertToNameClass($nameEditableModel);
        $nameClassEditable = self::GetPrefixClassApp() . $nameClassEditable;
        if (strpos($nameClassEditable, 'Editable')===false) {
            $nameClassEditable .= 'EditableModel';
        }

        return $nameClassEditable;
    }

    public static function GetNameClassList($nameListModel) {
        $nameClassList = self::CovertToNameClass($nameListModel);
        $nameClassList = self::GetPrefixClassApp() . $nameClassList . 'ListModel';
        return $nameClassList;
    }

    public static function GetNameClassPanelMain($nameContainerModel) {
        $nameClass = self::CovertToNameClass($nameContainerModel);
        $nameClass = self::GetPrefixClassApp() . $nameClass . 'PanelMainModel';
        return $nameClass;
    }

    public function getNameClassReport($nameReportModel) {
        $nameClass = self::CovertToNameClass($nameReportModel);
        $nameClass = self::GetPrefixClassApp() . $nameClass . 'ReportModel';
        return $nameClass;
    }

    public function getNameClassController() {
        $nameClassController = self::CovertToNameClass($this->_controllerRaw);
        $nameClassController = self::GetPrefixClassApp() . $nameClassController . 'Controller';
        return $nameClassController;
    }

    function getModelRaw() {
        $modelRaw = $this->_controllerRaw;
        if (substr($modelRaw, -1, 1) == "s") {
            $modelRaw = substr($modelRaw, 0, -1);
        }

        return $modelRaw;
    }

    function getNameFileModel() {
        $nameFileModel = $this->getModelRaw();
        $nameFileModel .= '.model.php';
        return $nameFileModel;
    }

    function getNameClassModel() {
        $nameClassModel = ucfirst($this->getModelRaw());
        $nameClassModel = self::GetPrefixClassApp() . $nameClassModel . 'Model';
        return $nameClassModel;
    }

    public static function GetOffsetTimeFromCountry($codeIsoCou = 'ECU') {
        $offset_time = ExjSession::Get('offsetTimeCou' . $codeIsoCou, null, Exj::NAME_SPACE);
        if ($offset_time !== null) {
            return $offset_time;
        }

        $db = Exj::InstanceDatabase();

        $offset_time = $db->loadResult("SELECT offset_time FROM app_loc_paises WHERE cod_iso_cou_alfa3='$codeIsoCou'");
        if ($db->isValid()) {
            ExjSession::Set('offsetTimeCou' . $codeIsoCou, $offset_time, Exj::NAME_SPACE);
        } else {
            $offset_time = 0;
            ExjSession::Set('offsetTimeCou' . $codeIsoCou, $offset_time, Exj::NAME_SPACE);
        }

        return $offset_time;
    }


    /**
     * Escapa caracteres slashes y deja el fin de linea
     *
     * @param string $text
     * @return string
     */
    static function StripslashesWithEndLine($text) {
        if (!$text) {
            return $text;
        }

        if (!is_string($text)) {
            return $text;
        }

        //	$text = addslashes($text);

        $text = addcslashes($text, "\\\'\"&\n\r<>");
        $text = stripslashes($text);


        return $text;
    }

    static function StripslashesToObject(&$object) {
        if (is_array($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::StripslashesToObject($value);
                } elseif (is_string($value)) {
                    $value = ($value ? stripslashes($value) : $value);
                } elseif (is_object($value)) {
                    self::StripslashesToObject($value);
                }
            }
        } elseif (is_object($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::StripslashesToObject($value);
                } elseif (is_string($value)) {
                    //	echo "<br/>value antes: $value";
                    $value = ($value ? stripslashes($value) : $value);
                    //	echo " despues: $value";
                } elseif (is_object($value)) {
                    self::StripslashesToObject($value);
                }
            }
        } else {
            if ($object && is_string($object)) {
                $object = stripslashes($object);
            }
        }
    }

    static function AddSlashesToObject(&$object) {
        if (is_array($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::AddSlashesToObject($value);
                } elseif (is_string($value)) {
                    $value = ($value ? addslashes($value) : $value);
                } elseif (is_object($value)) {
                    self::AddSlashesToObject($value);
                }
            }
        } elseif (is_object($object)) {
            foreach ($object AS $key => &$value) {
                if (is_array($value)) {
                    self::AddSlashesToObject($value);
                } elseif (is_string($value)) {
                    $value = ($value ? addslashes($value) : $value);
                } elseif (is_object($value)) {
                    self::AddSlashesToObject($value);
                }
            }
        } else {
            if ($object && is_string($object)) {
                $object = addslashes($object);
            }
        }
    }

    static function &JsonDecodeSlashes($strJson, $addSlashes = true) {
        $obj = self::JsonDecode($strJson, false);

        if ($addSlashes && $obj) {
            Exj::AddSlashesToObject($obj);
        }

        return $obj;
    }

    static function JsonDecode($strJson, $encodeISOToUT8 = true) {
        if ($encodeISOToUT8) {
            ExjTransferCharacters::encodeISOToUTF8($strJson);
        }

        return json_decode($strJson);
    }

    static function CheckTokenExit() {
        JRequest::checkToken() or exit('Invalid Token');
    }

    static function ValidateFieldEmptyExit(&$obj, $field, $aliasField = '', $msgInvalidate = '') {
        $field = trim($field);
        if (!$field) {
            exit(__METHOD__ . ". No field name indicated");
        }

        $isValid = true;

        if (!isset($obj->$field)) {
            $isValid = false;
        } else {
            if ($obj->$field) {
                $obj->$field = trim($obj->$field);
            }

            if (!$obj->$field) {
                $isValid = false;
            }
        }

        if ($isValid) {
            return true;
        }

        if (!$aliasField) {
            $aliasField = $field;
        }

        if (!$msgInvalidate) {
            $msgInvalidate = "Campo requerido. $aliasField.";
        }

        exit($msgInvalidate);
    }

    private $_isResquestApi=false;
    public function setIsResquestApi($value=true){
        $this->_isResquestApi = $value;
        return $this;
    }

    public function isResquestApi(){
        return $this->_isResquestApi;
    }

    public static function GetPathFileConfiguration() {
        return (JPATH_CONFIGURATION.DS.'configuration.php');
    }

    public static function ParseInt($value, $defVal=null) {
        if ($value === null) {
            return $value;
        }

        $valNum = intval($value);
        if (is_nan($valNum)) {
            $valNum = $defVal;
            echo "<br>ERROR ParseInt: $value no es valor numérico";
        }

        return $valNum;
    }

}

?>