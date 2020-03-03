<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Clase base para controladores. Los controladores deben heredar de esta clase.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: app_[componente]/controllers/[componente].controller.php
 * Nombrado de la clase: Debe tener el formato: class App[componente]Controller extends ExjController
 */
class ExjController {

    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DESTROY = 'destroy';

    public $request, $id, $params, $paramDataChanged, $paramData, $paramCriteria;
    private $_response = null;

    /**
     * Informa si es nuevo o no
     *
     * @var bool
     */
    public $isNew;

    /**
     * Constructor de la clase ExjController, el constructor llama al método protegido: initController, si se desea dar un comportamiento general al controlador, debe sobrescribir dicho método.
     *
     */
    public function __construct() {
        $this->initController();
    }

    /**
     * Inicio del Controlador
     *
     */
    protected function initController() {
        
    }

    public function getId() {
        return $this->id;
    }

    public function getDataChanged(){
        if (!$this->paramDataChanged) {
            return null;
        }
        
        return $this->paramDataChanged;
    }

    /**
     * Despacha request para controller-action apropiados por conversación de acuerdo a el método HTTP.
     */
    public function dispatch($request) {
        global $exj;
        Exj::SetBufferDebugMethod(__METHOD__, 'Se envió parámetro: request:');
        Exj::SetBufferDebug($request);


        $this->request = $request;
        $this->params = $request->params;
        $this->paramDataChanged = $request->paramDataChanged;
        $this->paramCriteria = $request->paramCriteria;
        $this->paramData = $request->paramData;

        if ($request->id === null) {
            $this->id = 0;
        } else {
            $this->id = intval($request->id);
        }

        $this->isNew = $this->getParamFromData('isNew', null);
        //    echo " this->isNew: $this->isNew";
        if ($this->isNew === null) {
            $this->isNew = ($this->id ? false : true);
            if (!$this->isNew && $this->id == -1) {
                $this->isNew = true;
            }
        } else {
            // convierte a bool
            $this->isNew = ($this->isNew ? true : false);
        }

        //    echo " this->isNew: $this->isNew";
        //	echo " test ID: $this->id ";
        // the client can pass 'fail=true' to force a server error response for testing CUD (not R) actions
        if ($request->fail && $request->method != 'GET') {
            $response = new ExjResponse();
            $response->setMsgError("Acción remota no puede ser completada.");

            return $response;
        }

        $responseRaw = '';

        try {
            if ($request->isRestful()) {
                $responseRaw = $this->dispatchRestful();
            } elseif ($request->action) {
                $nameMethodCall = $request->action;
                if (method_exists($this, $nameMethodCall)) {
                    $responseRaw = $this->{$nameMethodCall}();
                }
                else{
                    $responseRaw = new ExjResponse();
                    $responseRaw->setMsgError("Método: <b>$nameMethodCall</b> indefinido en la clase: " . get_class($this));
                }
                
                $this->_dispatchMsgResponse($responseRaw, $request);
            }
        } catch (Exception $ex) {
            Exj::SetErrorException($ex);
        }


        // se verifica si hay errores
        $responseError = $this->dispatchError($responseRaw);
        if ($responseError) {
            Exj::LogWriteDelayed();
            return $responseError;
        }

        Exj::LogWriteDelayed();

        return $responseRaw;
    }

// dispatch

    private function _dispatchMsgResponse(ExjResponse &$response, $request = null, $action = '') {
        // xxx
        if (!$response) {
            return $response;
        }

        if ($response->haveMsgText()) {
            return $response;
        }

        global $exj;
        if (Exj::GetError()->haveError()) {
            return $response;
        }

        if (!$request) {
            $request = $this->request;
        }
        if (!$action) {
            $action = $request->action;
        }

        if (!$action) {
            return $response;
        }

        switch ($action) {
            case self::ACTION_CREATE:
                $response->setMsgNotify("Se creó satisfatoriamente");
                break;

            case self::ACTION_UPDATE:
                $response->setMsgNotify("Actualización satisfactoria");
                break;

            case self::ACTION_DESTROY:
                $response->setMsgNotify("Se eliminó satisfactoriamente");
                break;
        }

        return $response;
    }

    private function _getParamFromObj($obj, $name, $defaulValue = '') {
        $name = trim($name);

        if (!$name || !$obj) {
            return $defaulValue;
        }

        if (is_object($obj)) {
            if (isset($obj->$name)) {
                return $obj->$name;
            }
        } elseif (is_array($obj)) {
            if (isset($obj[$name])) {
                return $obj[$name];
            }
        }

        return $defaulValue;
    }

    /**
     * Permite cargar archivos al servidor
     *
     * @param string $nameFileUI
     * @return Instancia de ExjResponse
     */
    public function uploadFile($nameFileUI = '') {
        $response = new ExjResponse();

        $subFolder = '';
        $msgError = '';
        $FILETYPE_MODULE = '';
        $addFolderUser = null;
        $renameNameFile = '';

        if ($this->uploadFileConfig($FILETYPE_MODULE, $subFolder, $addFolderUser, $msgError, $renameNameFile) === false) {
            if (!$msgError) {
                $msgError = 'Error desconocido al cargar definiciones';
            }
            return $response->setMsgError($msgError);
        }

        if (!$FILETYPE_MODULE) {
            return $response->setMsgError(
                "No se ha definido el tipo de módulo a cargar!",
                'ERROR DE IMPLEMENTACION'
            );
        }

        $id_file = $this->getParam('id_file', -1);
        // echo "id_file: $id_file";
        if ($id_file < 0) {
            $id_file = 0;
        }

        $hFileUpload = null;

        if (!$id_file || ExjHandlerFileUpload::IsLoadFileFromUI()) {
            $hFileUpload = new ExjHandlerFileUpload(0, $nameFileUI);
            $hFileUpload->uploadFile(
                $FILETYPE_MODULE, $subFolder, $addFolderUser, $renameNameFile
            );
            
            if ($hFileUpload->haveError()) {
                $response->setMsgError($hFileUpload->getErrorMsg());
                return $response;
            }

            $pathFileSaved = $hFileUpload->getPathFileSaved();
            $fileName = $hFileUpload->getFileName();
            $id_file = 0; // se crear, si la imagen ya existe, se asigna el id correspondiente
        }

        global $exj;

        $archivoEditableModel = new AppArchivoEditableModel(false);
        $archivoEditableModel->setValueId($id_file);
        if ($hFileUpload) {
            $archivoEditableModel->id_file_type = $hFileUpload->get_id_file_type();
            $archivoEditableModel->name_file = $fileName;
            $archivoEditableModel->nameext_file = $hFileUpload->getFileName(false);
            $archivoEditableModel->uri_file = $hFileUpload->getURIFile();
            $archivoEditableModel->sub_folder = $subFolder;
            $archivoEditableModel->path_file = $pathFileSaved;
            $archivoEditableModel->size_file = $hFileUpload->getSizeFile();

            $archivoEditableModel->verificarExiste($FILETYPE_MODULE);
        } else {
            $archivoEditableModel->load();
            if ($archivoEditableModel->haveBrokenRules()) {
                $response->setMsgError($archivoEditableModel->getBrokenRules(), 'ERROR GUARDANDO ARCHIVO');
                return $response;
            }

            $fileName = $archivoEditableModel->name_file;
            $pathFileSaved = $archivoEditableModel->path_file;
        }

        /*
          print_r($archivoEditableModel->toObject());
          $response->setMsgError("Pruebas. " . Exj::GetErrorMsgGlobal(), 'ERROR SAVING FILE');
          return $response;
         */

        try {
            ExjDBTrx::Start();

            if (!$id_file) {
                if (!$archivoEditableModel->save()) {
                    $response->setMsgError($archivoEditableModel->getBrokenRules(), 'ERROR GUARDANDO ARCHIVO');
                    if (ExjDBTrx::IsStartedTransaction()) {
                        ExjDBTrx::Rollback();
                    }

                    return $response;
                }

                ExjTransferCharacters::decodeUTF8ToISO($archivoEditableModel->path_file);
                $id_file = $archivoEditableModel->id;
            }

            $showMsgSuccess = true;
            $this->uploadFileAfter($id_file, $msgError, $showMsgSuccess, $archivoEditableModel->path_file, $nameFileUI);
            if (!$msgError) {
                if (Exj::GetError()->haveError()) {
                    $msgError = Exj::GetErrorMsgGlobal();
                }
            }
            if ($msgError) {
                $response->setMsgError($msgError);
                if (ExjDBTrx::IsStartedTransaction()) {
                    ExjDBTrx::Rollback();
                }
                return $response;
            }

            if ($showMsgSuccess) {
                $response->setMsgNotify("Se cargó satisfactoriamente el archivo: $fileName");
            }

            ExjDBTrx::Commit();
        } catch (Exception $ex) {
            Exj::SetErrorException($ex);
            $response->setMsgError($ex->getMessage());
            if (ExjDBTrx::IsStartedTransaction()) {
                ExjDBTrx::Rollback();
            }
        }

        return $response;
    }

// uploadFile

    /**
     * Configuración del archivo a cargar
     * @param string $FILETYPE_MODULE
     * @param string $subFolder
     * @param boolean $addFolderUser
     * @param string $msgError
     * @param string $renameNameFile Nombre del archivo a renombrar
     */
    protected function uploadFileConfig(&$FILETYPE_MODULE, &$subFolder, &$addFolderUser, &$msgError, &$renameNameFile) {
        $msgError = '';
        $subFolder = '';
        $addFolderUser = false;
    }

    /**
     * Después de haber subido el archivo al servidor. Soportado para transacciones, commit rollback
     * @param int $id_file
     * @param string $msgError
     * @param boolean $showMsgSuccess
     * @param string $path_file
     * @param string $nameFileUI
     */
    protected function uploadFileAfter($id_file, &$msgError, &$showMsgSuccess, $path_file, $nameFileUI) {
        $showMsgSuccess = true;
    }

    /**
     * Devuelve el valor del parametro pasado por parámetro, este valor es convertido a int, sino se puede convertir el valor a int se devuelve el valor por defecto
     *
     * @param string $name
     * @param int $defaulValue Por defecto 0
     * @return int
     */
    public function getParamId($name, $defaulValue = 0) {
        $id = $this->getParam($name, $defaulValue);
        if (!$id) {
            return $defaulValue;
        }

        $id = intval($id);
        if (is_nan($id)) {
            return $defaulValue;
        }

        return $id;
    }

    /**
     * Retorna los parámetros enviados desde la UI en forma de object
     *
     * @return object
     */
    public function getParamsToObject() {
        if (!$this->params) {
            return $this->params;
        }

        if (is_object($this->params)) {
            return $this->params;
        }

        $obj = ExjObject::ConvertArrayToObject($this->params);

        return $obj;
    }

    public function getParam($name, $defaulValue = '') {
        return $this->_getParamFromObj($this->params, $name, $defaulValue);
    }

    public function setParam($name, $value) {
        if (!$this->params) {
            $this->params = new stdClass();
        }

        if (is_array($this->params)) {
            $this->params[$name] = $value;
        } elseif (is_object($this->params)) {
            $this->params->$name = $value;
        }
    }

    /**
     * Obtiene los parámetros pasados a data en la UI
     *
     * @return object
     */
    public function getParamsDataToObject() {
        if (!$this->paramData) {
            return null;
        }

        if (is_object($this->paramData)) {
            return $this->paramData;
        }

        if (is_array($this->paramData)) {
            return Exj::ConvertArrayToObject($this->paramData);
        }

        return null;
    }

    public function getParamFromData($name, $defaulValue = '') {
        return $this->_getParamFromObj($this->paramData, $name, $defaulValue);
    }

    public function getParamIntFromData($name, $defaulValue = 0) {
        return intval($this->getParamFromData($name, $defaulValue));
    }

    public function getParamFromDataChanged($name, $defaulValue = '') {
        return $this->_getParamFromObj($this->paramDataChanged, $name, $defaulValue);
    }

    public function getParamFromCriteria($name, $defaulValue = '') {
        return $this->_getParamFromObj($this->paramCriteria, $name, $defaulValue);
    }

    public function getParamIntFromDataChanged($name, $defaulValue = 0) {
        return intval($this->getParamFromDataChanged($name, $defaulValue));
    }

    public function downloadEXCELXLSX() {
        $this->dispatchDocumentDownload('excelxlsx', 'Documento Excel');
    }

    public function downloadEXCELXLS() {
        $this->dispatchDocumentDownload('excelxls', 'Documento Excel');
    }

    public function downloadPDF() {
        $this->dispatchDocumentDownload('pdf', 'Documento PDF');
    }

    /**
     * Devuelve una instancia de AppDataReport
     *
     * @return Instancia de DataReport
     */
    public function getDataReport() {
        return false;
    }

    public function dispatchDocumentDownload($format, $title = 'Documento', $fileName = 'reporte')
    {
        global $exj;

        Exj::IncludePHPExcel();
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);

        $namesUser = ExjUser::GetNames();
        $pageOrientation = null;
        $paperSize = null;

        $dataReport = $this->getDataReport();
        if ($dataReport) {
            $activeSheet = $objPHPExcel->getActiveSheet();

            $columns = $dataReport->getColumns();
            $rows = $dataReport->getDataRows();
            $displayHeaders = $dataReport->displayHeaders;
            $fileName = $dataReport->getFileName();
            $title = $dataReport->getTitle();

            $pageOrientation = $dataReport->getPageOrientation();
            $paperSize = $dataReport->getPaperSize();

            $ordCharA = ord('A');
            $nRowCurrent = 1; // fila actual
            $nCols = count($columns);
            for ($indexRow = 0; $indexRow < count($rows); $indexRow++) {
                $row = $rows[$indexRow];

                for ($indexCol = 0; $indexCol < $nCols; $indexCol++) {
                    $col = $columns[$indexCol];
                    // $col->header
                    // $col->width
                    // $col->dataIndex
                    // $col->type
                    $charCol = chr($ordCharA + $indexCol); // ej: A, B, C

                    if ($displayHeaders) {
                        $posCol = $charCol . ($nRowCurrent);
                        $activeSheet->setCellValue($posCol, $col->header);
                    }
                    $dataIndex = $col->dataIndex;


                    $valueCell = '';
                    if (isset($row->$dataIndex)) {
                        $valueCell = $row->$dataIndex;
                    }

                    $posRow = $charCol;
                    if ($displayHeaders) {
                        $posRow .= ($nRowCurrent + 1);
                    } else {
                        $posRow .= ($nRowCurrent);
                    }

                    $activeSheet->setCellValue($posRow, $valueCell);
                }

                if ($displayHeaders) {
                    $rangeHeaders = 'A' . $nRowCurrent; // ej: A1:E1
                    $rangeHeaders .= ':' . chr($ordCharA + $nCols - 1) . $nRowCurrent;

                    $activeSheet->getStyle($rangeHeaders)->applyFromArray($this->_getStyleHeaders());
                    $nRowCurrent += 1;
                    $displayHeaders = false;
                }

                $nRowCurrent += 1; // siguiente
            }

            for ($indexCol = 0; $indexCol < count($columns); $indexCol++) {
                $col = $columns[$indexCol];
                $charCol = chr($ordCharA + $indexCol); // ej: A, B, C

                if ($col->width) {
                    $activeSheet->getColumnDimension($charCol)->setWidth($col->width);
                }
            }
        }

        $objPHPExcel->getProperties()->setCreator(Exj::GetTitleApp())
                ->setLastModifiedBy($namesUser)
                ->setTitle($title)
                ->setSubject(ExjUser::GetEmail())
                ->setDescription("Documento generado GYMCloud.")
                ->setKeywords("pdf GYMCloud")
                ->setCategory("reporte")
                ->setCompany(Exj::GetTitleCompanyApp());
        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle(ucfirst($title));

        // Set page orientation and size
        if ($pageOrientation !== null) {
            $objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation($pageOrientation);
        }
        if ($paperSize !== null) {
            $objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize($paperSize);
        }


        $format = strtolower($format);
        switch ($format) {
            case 'pdf':
                $objPHPExcel->getActiveSheet()->setShowGridLines(false);
                $this->_writeDocumentPDF($objPHPExcel, $fileName);
                break;
            case 'excelxls':
            case 'excel5':
                $this->_writeDocumentExcelXLS($objPHPExcel, $fileName);
                break;

            default:
                $this->_writeDocumentExcelXLSX($objPHPExcel, $fileName);
                break;
        }
    }

    private function _getStyleHeaders() {
        $styleHeaders = array(
            'borders' => array(
                'outline' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('argb' => 'FF000000'),
                ),
            ),
            'font' => array(
                'bold' => true
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 90,
                'startcolor' => array(
                    'argb' => 'FFA0A0A0'
                ),
                'endcolor' => array(
                    'argb' => 'FFFFFFFF'
                )
            )
        );

        return $styleHeaders;
    }

    private function _writeDocumentPDF($objPHPExcel, $fileName) {
        // Redirect output to a clientâ€™s web browser (PDF)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="' . $fileName . '.pdf"');
        header('Cache-Control: max-age=0');

        /*
        $className = \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class;
        PHPExcel_IOFactory::registerWriter('Pdf', $className);
        */

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'PDF');
        $objWriter->save('php://output');
        exit();
    }

    private function _writeDocumentExcelXLS($objPHPExcel, $fileName) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit();
    }

    private function _writeDocumentExcelXLSX($objPHPExcel, $fileName) {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * Despacha errores generados por la aplicación si existe algún error se registra en los logs de la app
     *
     * @param ExjResponse $response Por defecto null
     * @return bool si ha ocurrido algún error retorna la instancia de ExjResponse, sino devuelve false
     */
    public function dispatchError($response = null) {
        global $exj;

        if (Exj::GetError()->haveError()) {
            Exj::LogWriteError();

            if (!$response || !($response instanceof ExjResponse)) {
                $response = new ExjResponse();
            }

            $response->setMsgError(Exj::GetErrorText());

            return $response;
        }

        return false;
    }

    public function view() {
        $response = new ExjResponse();
        $response->setMsgError("No se ha implementado el método: view en el controlador: " . $this->request->controller, "ERROR DE IMPLEMENTACION");
        return $response;
    }

    /**
     * Crea
     *
     * @return ExjResponse
     */
    public function create() {
        return ExjModel::SaveDataChangedToEditableModel($this, 0);
    }

    public function update() {
        return ExjModel::SaveDataChangedToEditableModel($this, $this->id);
    }

    public function destroy() {
        $response = new ExjResponse();

        $component = $this->getComponent();

        if (!ExjModel::ValidateAccessDelete($response, $component)) {
            return $response;
        }

        $id = $this->id;
        if (!$id) {
            return $response->setMsgError("No se indicó referencia.", 'ERROR AL ELIMINAR');
        }

        $ClassModelEditable = ExjUtil::GetClassModelEditableOfComponent($msgError, $component);
        if ($msgError) {
            return $response->setMsgError($msgError, 'ERROR AL ELIMINAR');
        }

        // echo "<br>Eliminar. component: $component id: $id, ClassModelEditable: $ClassModelEditable";

        ExjModel::Destroy($id, $ClassModelEditable, $response, $component);

        return $response;
    }

    /**
     * Obtiene el componente enviado desde la UI
     *
     * @return string
     */
    public function getComponent() {
        $component = trim($this->getParam('option'));
        if (!$component) {
            $component = ExjRequest::GetParamOption();
        }

        return $component;
    }

    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
    protected function editableModelRead(&$nameEditableModel, &$params) {
        
    }

    /**
     * overwrited. Lectura de Modelo solo lectura
     *
     * @param string $nameModel
     * @param array $params
     */
    protected function readonlyModelRead(&$nameModel, &$params, &$fnCallBackLoad) {
        
    }

    /**
     * overwrited. Lectura del modelo de lista
     *
     * @param string $nameListModel
     * @param array $params
     * @param bool $addItemsTopbarExtras
     */
    protected function listModelRead(&$nameListModel, &$params, &$addItemsTopbarExtras = false) {
        
    }

    private function _converToParamsValue($params, &$msgError) {
        $paramsValue = array();

        if (!$params) {
            return $paramsValue;
        }

        if (count($params) == 0) {
            return $paramsValue;
        }

        $msgError = array();

        foreach ($params as $param) {
            $paramsValue[$param] = $this->getParam($param);
            if (!$paramsValue[$param]) {
                $msgError[] = "El parámetro $param es requerido";
            }
        }

        if (count($msgError) > 0) {
            $msgError = implode("<br/>", $msgError);
        } else {
            $msgError = '';
        }

        return $paramsValue;
    }

    protected function reportHTML() {
        // global $exj;
        $response = new ExjResponse();

        // print_r($this->request);
        //	print_r($this->getParamsToObject());

        $component = $this->getParam('option');
        $getItemsTopbarFromListModel = $this->getParam('getItemsTopbarFromListModel');

        // $x = new SoapServer()

        $classController = get_class($this);
        $className = str_replace('Controller', '', $classController);

        $ClassListModel = $className . 'ListModel';
        $ClassCriteriaModel = $className . 'CriteriaModel';


        if (!class_exists($ClassListModel)) {
            return $response->setMsgError("No está definida la clase ListModel: $ClassListModel", 'ERROR ' . __METHOD__);
        }
        if (!class_exists($ClassCriteriaModel)) {
            return $response->setMsgError("No está definida la clase CriteriaModel: $ClassCriteriaModel", 'ERROR ' . __METHOD__);
        }

        //	$objListModel = new $ClassListModel(null);
        $objCriteriaModel = new $ClassCriteriaModel();


        $dataReportHTML = new stdClass();
        $dataReportHTML->criteriaModel = self::_ToUICriteriaModelReportHTML($objCriteriaModel, $response);
        //	$dataReportHTML->listModel = self::_ToUIListModelReportHTML($objListModel, $response);

        if ($response->haveMsgError()) {
            return $response;
        }

        $response->setDataObject($dataReportHTML);

        //	$response->setMsgError("Prueba. classController: $classController component: $component");


        return $response;
    }

    private static function _ToUIListModelReportHTML(ExjListModel &$listModel, ExjResponse $response) 
    {
        // $listModel->addItemsTopbarExtras = false;
        $listModel->setResponse($response)->fixModeLocal()->readData();

        return $listModel->to_ui();
    }

    private static function _ToUICriteriaModelReportHTML(ExjCriteriaModel &$criteriaModel, ExjResponse $response) 
    {
        return $criteriaModel->to_ui();
    }

    /**
     * Modelo de lista
     *
     * @return ExjResponse
     */
    protected function listModel() {
        global $exj;
        $response = new ExjResponse();

        $nameListModel = '';
        $params = array();
        $addItemsTopbarExtras = false;
        $this->listModelRead($nameListModel, $params, $addItemsTopbarExtras);
        if (!$nameListModel) {
            $response->setMsgError("No se ha definido la función: listModelRead en el controlador: " . $this->_getNameController(), "ERROR DE IMPLEMENTACION");
            return $response;
        }

        $msgError = '';
        $paramsValue = $this->_converToParamsValue($params, $msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return $response;
        }

        
        $ClassModelList = ExjUtil::GetNameClassModelListFromName($nameListModel);
        $xListModel = new $ClassModelList(null);
        $xListModel->addItemsTopbarExtras = $addItemsTopbarExtras;
        $xListModel->setResponse($response);

        if (count($paramsValue) > 0) {
            foreach ($paramsValue as $param => $value) {
                $xListModel->setBaseParam($param, $value);
            }
        }

        $xListModel->readData();

        $response->data = $xListModel->to_ui();

        return $response;
    }

    private function _getNameController() {
        return $this->request->controller;
    }

    /**
     * Modelo Editable
     *
     * @return ExjResponse
     */
    protected function editableModel() {
        $response = new ExjResponse();

        $nameEditableModel = '';
        $params = array();
        $responseReadModel = $this->editableModelRead($nameEditableModel, $params);
        if ($responseReadModel && $responseReadModel instanceof ExjResponse) {
            if ($responseReadModel->haveMsgError()) {
                return $responseReadModel;
            }
            $response = $responseReadModel;
        }

        if (!$nameEditableModel) {
            $response->setMsgError("No se ha definido el método: editableModelRead en el controlador: " . $this->_getNameController(), "ERROR DE IMPLEMENTACION");
            return $response;
        }

        $msgError = '';
        $paramsValue = $this->_converToParamsValue($params, $msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return $response;
        }

        $ClassModelEditable = ExjUtil::GetNameClassModelEditableFromName(
            $nameEditableModel
        );

        $ClassModelChildEditable = '';
        if (!class_exists($ClassModelEditable)) {
            $ClassModelChildEditable = ExjUtil::GetNameClassModelChildEditableFromName($nameEditableModel);
            $ClassModelEditable = $ClassModelChildEditable;
        }

        if (!class_exists($ClassModelEditable)) {
            $ClassModelEditable = ExjUtil::GetNameClassModelEditableFromName($nameEditableModel);
            $response->setMsgError("No existe Modelo Editable.<br/>Clase: $ClassModelEditable o $ClassModelChildEditable");
            return $response;
        }

        //	echo __METHOD__ . " ClassModelEditable: $ClassModelEditable";

        $xEditableModel = new $ClassModelEditable(true, $response);
        $this->_setFieldsValuesEditable($xEditableModel, $paramsValue);
        if (isset($this->id) && $this->id > 0) {
            $xEditableModel->setValueId($this->id);
        }

        $fnCallBackLoad = '';
        if (!$xEditableModel->isNew()) {
            $fnCallBackLoad = 'load';
        }

        //	echo "<br/>Pruebas $ClassModelEditable ES NUEVO: " . ($xEditableModel->isNew() ? 'SI':'NO'). " paramsValue:<br/>";
//		print_r($paramsValue);

        $this->_dispatchModel(
            $xEditableModel, 
            $response,
            $paramsValue,
            $fnCallBackLoad
        );

        return $response;
    }

    private function _setFieldsValuesEditable(ExjEditableModel &$modelEdit, $paramsValue) {
        if (!$paramsValue || count($paramsValue) == 0) {
            return;
        }

        foreach ($paramsValue as $param => $value) {
            // echo '<br/>'.__METHOD__." param: $param value: $value";
            $modelEdit->setValueToField($param, $value);
        }
    }

    private function _dispatchModel(ExjModels &$model, ExjResponse &$response, $paramsValue, $fnCallBackLoad = 'load') {
        if ($response->haveError()) {
            return $response;
        }

        if ($paramsValue && count($paramsValue) > 0) {
            foreach ($paramsValue as $param => $value) {
               // echo '<br/>'.__METHOD__." setParam param: $param value: $value Clase: " . get_class($model);
                $model->setParam($param, $value);
            }
        }

        if ($model->haveBrokenRules()) {
            $response->setMsgError($model->getBrokenRules());
            return $response;
        }

        if ($fnCallBackLoad) {
            //	echo __METHOD__. " LLAMANDO A FN: $fnCallBackLoad";
            $model->$fnCallBackLoad();

            if ($model->haveBrokenRules()) {
                $response->setMsgError($model->getBrokenRules());
                return $response;
            }
        }

        $model->afterLoadRegisterControlsUI();
        $model->registerChildsListModel();
        //	$model->loadDataModelAfterSetterParams();

        $response->setDataObject($model->to_ui());
    }

    /**
     * Modelo para modelo de solo lectura
     *
     * @return ExjResponse
     */
    protected function viewModel() {
        global $exj;
        $response = new ExjResponse();

        $nameReadOnlyModel = $this->_getNameController();
        $params = array();
        $fnCallBackLoad = 'loadDataUI';
        $this->readonlyModelRead($nameReadOnlyModel, $params, $fnCallBackLoad);
        if (!$nameReadOnlyModel) {
            $response->setMsgError("No se ha definido el método: readonlyModelRead en el controlador: " . $this->_getNameController(), "ERROR DE IMPLEMENTACION");
            return $response;
        }

        $msgError = '';
        $paramsValue = $this->_converToParamsValue($params, $msgError);
        if ($msgError) {
            $response->setMsgError($msgError);
            return $response;
        }


        $ClassModelReadOnly = ExjUtil::GetNameClassModelReadOnlyFromName($nameReadOnlyModel);
        $xReadOnlyModel = new $ClassModelReadOnly(true, $response);

        $this->_dispatchModel($xReadOnlyModel, $response, $paramsValue, $fnCallBackLoad);

        return $response;
    }

    protected function deleteImportReadItems(&$nameTable, &$nameFieldFile, &$paramsQuery) {
        $response = new ExjResponse();

        $response->setMsgError("No se ha implementado el método: deleteImportReadItems en el controlador: " . $this->request->controller, "ERROR DE IMPLEMENTACION");

        return $response;
    }

    private function _validateReturnExjResponse(&$response, $nameMethod) {
        if (!$response) {
            $response = new ExjResponse();
            $response->setMsgError("El método: $nameMethod debe retornar una instancia de: ExjResponse.<br/>El método no retornó nada!", "ERROR DE IMPLEMENTACION");
            return false;
        }
        if (!($response instanceof ExjResponse)) {
            $response = new ExjResponse();
            $response->setMsgError("El método: $nameMethod debe retornar una instancia de: ExjResponse.<br/>El método retornó un tipo deferente!", "ERROR DE IMPLEMENTACION");
            return false;
        }
        if ($response->haveMsgError()) {
            return false;
        }

        return true;
    }

    /**
     * Eliminar importaciones
     *
     * @return ExjResponse
     */
    protected function deleteImportGetItems() {
        global $exj;

        $nameTable = '';
        $nameFieldFile = '';
        $paramsQuery = array();
        $response = $this->deleteImportReadItems($nameTable, $nameFieldFile, $paramsQuery);

        if (!$this->_validateReturnExjResponse($response, 'deleteImportGetItems')) {
            return $response;
        }

        if (!$nameTable) {
            $response->setMsgError("No se ha indicado el nombre de la tabla en el método: deleteImportReadItems", "ERROR DE IMPLEMENTACION");
            return $response;
        }


        if (!$nameFieldFile) {
            $nameFieldFile = 'id_file_import';
        }

        $whereSQL = array();
        if (count($paramsQuery) > 0) {
            foreach ($paramsQuery as $paramQuery) {
                $whereSQL[] = 'tx.' . $paramQuery;
            }
        }
        $whereSQL = count($whereSQL) > 0 ? implode(" AND ", $whereSQL) : '';
        if ($whereSQL) {
            $whereSQL = " WHERE " . $whereSQL;
        }

        $sql = "SELECT
  fls.id_file AS value, fls.name_file AS text, 
  Count(*) AS nro_del, fls.size_file, fty.name_type_file
FROM
  jos_app_files fls INNER JOIN
  $nameTable tx ON fls.id_file = tx.$nameFieldFile INNER JOIN
  jos_app_files_type fty ON fls.id_file_type = fty.id_file_type
 $whereSQL 
GROUP BY
  fls.id_file, fls.name_file, fls.size_file, fty.name_type_file";

        $db = Exj::InstanceDatabase();
        $items = $db->loadObjectList($sql);
        if (Exj::GetError()->haveError()) {
            return $response;
        }
        // echo $db->getQuery();

        $response->data = null;
        if (count($items) == 0) {
            $response->setMsgInfo("No hay datos para importar");
            return $response;
        }

        foreach ($items as &$item) {
            $item->size_file = ExjUtil::RenderSizeBytes($item->size_file);
        }

        $fieldExtras = array();
        $fieldExtras[] = ExjUI::NewFieldInt('nro_del');
        $fieldExtras[] = ExjUI::NewFieldString('name_type_file');
        $fieldExtras[] = ExjUI::NewFieldString('size_file');
        $cmbFiles = ExjUI::NewComboSimple('cmbFileImp', 'Archivo Importado', $items, $fieldExtras);
        $cmbFiles->anchor = '96%';
        $cmbFiles->allowBlank = false;
        $cmbFiles->blankText = 'Select the file to delete';


        $response->data = $cmbFiles;

        return $response;
    }

    /**
     * overwrited. Después de eliminar
     *
     * @param int $id_file
     */
    protected function deleteImportAfter($id_file, $itemsDeleted) {
        return false;
    }

    /**
     * Eliminar importaciones ya confirmado
     *
     * @return ExjResponse
     */
    protected function deleteImportConfirmed() {
        $response = new ExjResponse();

        $id_file = $this->getParamFromData('id', 0);
        // $id_file = $this->getParam('id', 0);
        if (!$id_file) {
            $response->setMsgError("ID del archivo es requerido", 'Confirmado eliminación');
            return $response;
        }


        $response = $this->deleteImportExecute($id_file);
        if (!$this->_validateReturnExjResponse($response, 'deleteImportConfirmed')) {
            return $response;
        }

        /*
          if (!$response->haveMsgText()) {
          $response->setMsgInfo("Se ha eliminado con éxito");
          }
         */

        return $response;
    }

    /**
     * Ejecución de eliminación de la Importación
     *
     * @param int $id_file
     * @return ExjResponse
     */
    protected function deleteImportExecute($id_file) {
        $response = new ExjResponse();

        $nameTable = '';
        $nameFieldFile = '';
        $paramsQuery = array();
        $response = $this->deleteImportReadItems($nameTable, $nameFieldFile, $paramsQuery);

        if (!$nameFieldFile) {
            $nameFieldFile = 'id_file_import';
        }

        $whereSQL = array();
        $whereSQL[] = "$nameFieldFile = $id_file";
        if (count($paramsQuery) > 0) {
            foreach ($paramsQuery as $paramQuery) {
                $whereSQL[] = $paramQuery;
            }
        }


        $whereSQL = implode(" AND ", $whereSQL);

        $db = Exj::InstanceDatabase();

        $sql = "SELECT * FROM $nameTable";
        $sql .= " WHERE $whereSQL";
        $itemsDeleted = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
            $response->setMsgError($db->getErrorMsg());
            return $response;
        }

        $nDeleted = count($itemsDeleted);

        if ($nDeleted == 0) {
            $response->setMsgError("No hay registros a eliminar!");
            return $response;
        }


        $sql = "DELETE FROM $nameTable";
        $sql .= " WHERE $whereSQL";

        $db->setQuery($sql)->query();
        if ($db->getErrorMsg()) {
            $response->setMsgError($db->getErrorMsg());
            return $response;
        }

        $nDeleted = $db->getAffectedRows();

        $responseAfter = $this->deleteImportAfter($id_file, $itemsDeleted);
        if ($responseAfter) {
            $response = $responseAfter;
        }
        if (!$this->_validateReturnExjResponse($response, 'deleteImportAfter')) {
            return $response;
        }

        if (!$response->haveMsgText()) {
            $response->setMsgInfo($nDeleted . ' ' . ($nDeleted == 1 ? 'registro' : 'registros') . " a sido eliminado");
        }

        return $response;
    }

    /**
     * overwrited. Configuración del Reporte
     *
     * @param string $nameReport
     * @param string $nameComponent
     * @param array $namesParamsExtras Adicionar nombres de parámetros pasado al reporte
     */
    public function reportConfig(&$nameReport, &$nameComponent, &$namesParamsExtras) {
        $nameReport = $this->request->controller;
        if (!$nameReport) {
            $nameReport = null;
        } else {
            $nameReport = strtolower($nameReport);
            /*
              if (substr($nameReport, -1, 1) == 's') {
              $nameReport = substr($nameReport, 0, strlen($nameReport)-1);
              }
             */
        }

        $nameComponent = '';
    }

    public function reportModel() {
        $response = new ExjResponse();

        $format = $this->getParam('format');
        if (!$format) {
            $response->setMsgError("El formato es requerido.");
            return $response;
        }
        
        $cols = $this->getParam('cols', null);
        if ($cols) {
        	$cols = Exj::JsonDecode($cols);
        	// print_r($cols);
        }

        global $exj;
        
        // rrr

        $nameReport = '';
        $nameComponent = '';
        $namesParamsExtras = array();
        $this->reportConfig($nameReport, $nameComponent, $namesParamsExtras);
        if ($nameReport) {
            $nameReport = trim($nameReport);
        }
        if ($nameReport === null) {
            $response->setMsgError("No se ha implementado el método: <b>reportConfig</b> en el controlador: " . $this->request->controller, "ERROR DE IMPLEMENTACION");
            return $response;
        }

        if (!$nameReport) {
            $response->setMsgError("No se ha definido el nombre del reporte, en el método: reportConfig");
            return $response;
        }

        $ClassReport = Exj::GetNameClassReport($nameReport);


        $report = new $ClassReport($format, $cols);
        if (!($report instanceof ExjReportModel)) {
            $response->setMsgError("La clase: $ClassReport debe ser heredado de la clase: ExjReportModel.<br/>Ref: Reporte: $nameReport", 'ERROR DE IMPLEMENTACION');
            return $response;
        }

        $id = $this->getParam('id', 0);
        if ($id) {
            $id = intval($id);
            $report->setParam('id', $id);
        }

        if ($namesParamsExtras && count($namesParamsExtras) > 0) {
            foreach ($namesParamsExtras as $nameParamExtra) {
                $valueParam = $this->getParam($nameParamExtra, '');
                if ($valueParam !== '') {
                    $report->setParam($nameParamExtra, $valueParam);
                }
            }
        }

        /*
          echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;
          echo '<br/>paramCriteria: ';
          print_r($this->paramCriteria);
         */
        
        // expandir tiempo máximo de espera
        $maxSegs = AppSysParametersHelper::GetValue_MAX_EXEC_REP_SEGS();
        if ($maxSegs && $maxSegs > 1) {
            ini_set('max_execution_time', $maxSegs);
        }

        // ini_set('max_execution_time', 240); //240 segundos = 4 minutos

        $report->bindCriterias($this->paramCriteria);


        // echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;
        $nameCriteria = $nameReport;
        $nameComponentCriteria = $nameComponent;
        $onlyRequired = false;
        if ($report->reportSetValuesCriteria($nameCriteria, $nameComponentCriteria, $onlyRequired) === false) {
            $report->fixValuesCriteria(
                '', $nameComponentCriteria, $onlyRequired
            );
        } else {
            $report->fixValuesCriteria(
                $nameCriteria, $nameComponentCriteria, $onlyRequired
            );
        }

        if ($report->beforeSave($this, $response) === false) {
            if (!$response->haveMsgError() && !Exj::GetError()->haveError()) {
                $response->setMsgError("Error inesperado, beforeSave no informó del error.<br/>No se pudo crear el archivo. Formato: $format");
            }

            return $response;
        }

        /*
        echo "<br>Controller. ParamsCriteria: ". print_r($report->getParamsCriteria(), true);
        */

        if (!$report->save()) {
            if (!Exj::GetError()->haveError()) {
                $response->setMsgError("Error inesperado.<br/>No se pudo crear el archivo. Formato: $format");
            }
            return $response;
        }

        // echo '<br/>TEST: ' . __METHOD__  . ' Ln: ' . __LINE__ ;

        $dataDownload = new ExjDTODataDownload(
            $report->getFullPathFileSaved(false),
            $report->canViewFileInUI(),
            'out',
            $report->getFileName(),
            $report->getExtensionFileSaved()
        );

        /*
          $dataDownload = new stdClass();
          $dataDownload->fileName = $report->getFileName();
          $dataDownload->idFile = $report->getFullPathFileSaved(true);
          $dataDownload->idFull = 1;
          $dataDownload->entry = base64_encode('out');
          $dataDownload->canViewFile = $report->canViewFileInUI();
          $dataDownload->fileSize = ExjUtil::RenderSizeBytes(filesize($report->getFullPathFileSaved()));
          $dataDownload->fileExt = $report->getExtensionFileSaved();
         */

        if ($report->isFormatPDF()) {
            // NOTE: Se da la url ya que al guardar el archivo no toma el nombre
            // del archivo
            $dataDownload->url = $report->getURIFileDownload();
        }

//    	print_r($dataDownload);

        $response->data = $dataDownload->toObject();

        return $response;
    }

    /**
     * Obtiene una instancia de ExjResponse
     *
     * @return ExjResponse
     */
    public function &getResponse() {
        if (!$this->_response) {
            $this->_response = new ExjResponse();
        }

        return $this->_response;
    }

    protected function dispatchRestful() {
        $response = null;

        switch ($this->request->method) {
            case 'GET':
                $response = $this->view();
                break;

            case 'POST':
                $response = $this->create();
                $this->_dispatchMsgResponse($response, null, self::ACTION_CREATE);
                break;

            case 'PUT':
                $response = $this->update();
                $this->_dispatchMsgResponse($response, null, self::ACTION_UPDATE);

                break;
            case 'DESTROY':
            case 'DELETE':
                $response = $this->destroy();
                $this->_dispatchMsgResponse($response, null, self::ACTION_DESTROY);

                break;
        }

        if ($response == null) {
            $response = new ExjResponse();
            $response->setMsgError("El método: " . $this->request->method . ' no está soportado!');
        }

        return $response;
    }

    /**
     * Verifica si son válidos los parametros para actualizar, se compruebas los parámetros:
     * id Valor numérico
     * paramDataChanged Object
     * @param ExjResponse $response Instancia de la clase: ExjResponse
     * @return bool
     */
    public function isValidParamsToUpdate(&$response) {
        return $this->_isValidParamsToSave($response, false);
    }

    /**
     * Verifica si son válidos los parametros para creación o nuevo, se compruebas los parámetros:
     * paramDataChanged Object
     * @param ExjResponse $response Instancia de la clase: ExjResponse
     * @return bool
     */
    public function isValidParamsToCreate(&$response) {
        return $this->_isValidParamsToSave($response, true);
    }

    private function _isValidParamsToSave(&$response, $isNew) {
        if ($isNew != $this->isNew) {
            //	echo " TEST: $isNew != $this->isNew ";
            // debug_print_backtrace();
            $response->setMsgError("Según la petición de lado del cliente, no corresponde a la tarea solicitada", "Error " . ($isNew ? 'Creando' : 'Actualizado') . '...');
            return false;
        }

        if (!$isNew) {
            if (!$this->id) {
                $response->setMsgError("No se recuperó ID", "Error " . ($isNew ? 'Creando' : 'Actualizando') . '...');
                return false;
            }
        }

        if (!$this->paramDataChanged) {
            /*
              if ($this->params) {
              print_r($this->params);
              }
              else {
              echo "NO HAY PARAMS. DESDE: " . __METHOD__;
              }

              $forceSaveDataChangedEmpty = $this->getParam('forceSaveDataChangedEmpty');
              if ($forceSaveDataChangedEmpty) {
              return true;
              }
             */

            $response->setMsgError("No se recuperó los datos modificados!", "Error " . ($isNew ? 'Creando' : 'Actualizando') . '...');
            return false;
        }
        return true;
    }

}

class ExjDTODataDownload extends ExjObject {

    public $fileName;
    public $idFile;
    public $idFull = 1;
    public $entry;
    public $canViewFile;
    public $fileSize;
    public $fileExt;
    public $url = '';
    private $_fileSizeRaw = 0;

    public function __construct($fullPathFile, $canViewFile = true, $entry = 'out', $fileName = '', $fileExt = '') {
        $this->entry = base64_encode($entry);
        $this->canViewFile = ($canViewFile ? 1 : 0);
        $this->_fileSizeRaw = filesize($fullPathFile);
        $this->fileSize = ExjUtil::RenderSizeBytes($this->_fileSizeRaw);

        if (!$fileExt || !$fileName) {
            $path_parts = pathinfo($fullPathFile);
            if (!$fileExt) {
                $fileExt = $path_parts['extension'];
            }
            if (!$fileName) {
                $fileName = $path_parts['filename'];
            }
        }

        $this->fileName = $fileName;
        $this->fileExt = $fileExt;
        $this->idFile = base64_encode($fullPathFile);
    }

    /**
     * Obtiene el tamaño del archivo en bytes
     *
     * @return int
     */
    public function getSizeFile() {
        return $this->_fileSizeRaw;
    }

    public function getBaseNameFile() {
        return $this->fileName . '.' . $this->fileExt;
    }

}

?>