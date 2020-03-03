<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base para modelos de lista (lista de datos en un Grid). Los modelos de lista deben heredar de esta clase.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[componente]/models/[componente].list.model.php
 * Nombrado de la clase: Debe tener el formato: class App[Componente]ListModel extends ExjListModel
 */
class ExjListModel {

    const CLASSUI_EDITORGRIDPANEL = 'EditorGridPanel';
    const CLASSUI_GRIDHTMLPANEL = 'Exj.ui.GridHTMLPanel';
    const FN_RENDER_TEXT_SINO = 'Exj.rendererTextSiNo';
    const FN_RENDER_TEXT_MEMO = 'Exj.rendererTextMemo';
    const FN_RENDER_TEXT = 'Exj.rendererText';
    const FN_RENDER_TEXT_LASTCHANGE = 'Exj.rendererTextLastChange';
    const ALIGN_RIGHT = 'right';
    const ALIGN_CENTER = 'center';
    const ALIGN_LEFT = 'left';

    public $id;

    /**
     * Tamaño de la página de la lista por defecto
     *
     */
    const PAGESIZE_DEFAULT = 30;

    /**
     * Ancho de Columna por defecto
     *
     */
    const COL_ANCHO_DEFECTO = 108;

    /**
     * Ancho de Columna para Fecha
     *
     */
    const COL_ANCHO_FECHA = 67;
    const COL_ANCHO_FECHA_MAX = 78;
    const COL_ANCHO_FECHAHORA = 105;
    const COL_ANCHO_TOTAL = 60;
    const COL_ANCHO_PRECIOS = 72;
    const COL_ANCHO_ULTCAMBIO = 201;
    const COL_ANCHO_NUMDOC = 117;
    const COL_ANCHO_CEDULA = 75;
    const COL_ANCHO_RUC = 99;
    const COL_ANCHO_EMAIL = 171;
    const COL_ANCHO_TELEFONO = 111;
    const COL_ANCHO_ESTADO = 66;
    const COL_ANCHO_VALOR = 81;
    const COL_ANCHO_ANIO = 78;
    const COL_ANCHO_ORDEN = 72;
    const COL_ANCHO_CODIGO = 99;
    const COL_ANCHO_NOMBRE = 126;
    const COL_ANCHO_NOMBRE2 = 180;
    const COL_ANCHO_DETALLE = 231;
    const COL_ANCHO_CORREO = 135;
    const COL_ANCHO_EDAD = 66;
    const COL_ANCHO_CANTIDAD = 84;
    const COL_ANCHO_ICON = 24;
    const COL_ANCHO_ACCION = 60;

    protected $titleList = '';
    protected $fieldKey = null;
    protected $withPagination = true;
    private $_cols = array(), $_fields = array(), $_data = null;
    private $_sortDir = 'ASC';
    private $_SORT_DIR_ASC = 'ASC';
    private $_SORT_DIR_DESC = 'DESC';
    private $_MODE_LOCAL = 'local';
    private $_MODE_REMOTE = 'remote';
    private $_mode = 'remote';
    private $_childNameList = '', $_childNameEditable = '', $_childNameComponent = '', $_childKeyEditable = '';
    private $_childParentEditable = '';
    private $_cfgGrid, $_cfgSelModel, $_nameClassGrid;
    private $_hideHeadersToGrid = null;
    private $_colExtrasHidden = false;
    private $_colExtraNameUser = '';
    private $_colExtraDateRegister = '';
    private $_forceEnableViewLogPers = null;
    private $_isUserAccessReadOnly = null;
    private $_addColOrder = false;
    private $_colsAutoAction = null;
    private $_textButtonDelete = 'Eliminar';
    private $_gridView = null;

    /**
     * Nombre de topicos, usado en toolbar
     *
     * @var string
     */
    public $nameTopics = 'items';
    public $nameTopic = 'item';

    /**
     * Indica si se adiciona o no botones extras al topbar
     *
     * @var bool Por defecto true
     */
    public $addItemsTopbarExtras = true;

    /**
     * Tamaño de página cuando se trata de paginación
     *
     * @var int
     */
    public $pageSize = 30;

    /**
     * Campo de defecto a ordenar en la lista
     *
     * @var unknown_type
     */
    public $defaultSort = '';

    /**
     * Alto de la lista, esta altura es temporal ya que se autoajusta el alto
     *
     * @var int
     */
    public $height = 333;
    public $bodyCssClass = '';

    public $requiereSelectionReport = false;
    public $titleButtonReport = '';
    public $forceShowReports = false;
    private $_hMenu = null;
    private $_nameListModel = '';
    private $_nameController = '';
    private $_isReportPDF = false;
    private $_isReportExcelXLS = false;
    private $_isReportExcelXLSX = false;
    private $_isReportHTML = false;
    private $_isReportXML = false;
    private $_reportXMLNameOption = '';
    private $_width = null;
    private $_useLockingGrid = false;
    private $_reportSendToMail = false;
    private $_nameFieldId = '';
    private $_nameComponentDefault = '';
    private $_fixTitleToToolbar = true;
    private $_listsModelsSecundaries = null;
    private $_store=null;

    /**
     * Constructor del modelo de lista
     *
     * @param ExjHelperMenu $hMenu Instancia de ExjHelperMenu
     * @param string $nameListModel
     * @param string $nameController
     */
    public function __construct($hMenu, $nameListModel = '', $nameController = '') {
        $this->_cfgGrid = new stdClass();
        $this->_cfgSelModel = new stdClass();
        $this->_nameClassGrid = 'GridPanel';
        $this->_isUserAccessReadOnly = null;
        $this->pageSize = self::PAGESIZE_DEFAULT;

        $this->_store = new ExjDataJsonStore();
        $this->_store->setterPathProxy()
            ->setBaseParam('start', 0)
            ->setBaseParam('limit', $this->pageSize);


        $newHMenu = $this->getHandlerMenu();
        if ($newHMenu !== null) {
            if ($newHMenu instanceof ExjHelperMenu) {
                $hMenu = $newHMenu;
            } else {
                Exj::SetErrorValidating("El método: getHandlerMenu() ha devuelto un objeto que no es instancia de ExjHelperMenu");
            }
        }

        $this->_hMenu = $hMenu;
        $this->fixSelModelRowSelectionModel(); // por defecto


        $this->setNameModelController($nameListModel, $nameController);
        $this->fixSortAsc();
        $this->fixModeRemote();

        $this->listInit();
        $this->_addFirstCols();
        $this->listRegisterFields();

        $this->_addExtrasFields();

        $this->listRegisterCols();

        if ($this->_cols && count($this->_cols) > 0) {
            $indexCol = 0;
            foreach ($this->_cols as &$refCol) {
                $this->afterColRegistered($refCol, $refCol->dataIndex, $indexCol++);
            }
        }

        $this->_addExtrasCols();
    }

    public function setTextButtonDelete($textButtonDelete) {
        $this->_textButtonDelete = $textButtonDelete;
    }

    /**
     * Setea desde clase Criteria hacia base params
     *
     * @param string $classCriteria No es requerido
     * @return bool
     */
    public function setterBaseParamsFromCriteria($classCriteria = '') {
        if (!$classCriteria) {
            $classCriteria = str_replace('ListModel', 'CriteriaModel', get_class($this));
        }

        //	echo "<br/>setterBaseParamsFromCriteria. classCriteria: $classCriteria";
        // yyyyyy

        $criteria = new $classCriteria(false);

        $itemCriteria = null;
        $fields = $criteria->getFields(true);
        if ($fields && count($fields) > 0) {
            foreach ($fields as $field) {
                $nf = $field->getName();

                // print_r($field);
                $value = $field->rendererValue($criteria->getValueField($nf, null));
                if ($value !== null) {
                    if (!$itemCriteria) {
                        $itemCriteria = new stdClass();
                    }
                    $itemCriteria->$nf = $value;
                }
            }
        }

        if ($itemCriteria) {
            $this->setBaseParams($itemCriteria);
        }

        return $this;
    }

    public function addListModelSecundary($nameModel, $nameControllerSec, $nameComponent = '', $hMenu = null) {
        if (!$this->_listsModelsSecundaries) {
            $this->_listsModelsSecundaries = array();
        }

        $itemNew = null;
        if (isset($this->_listsModelsSecundaries[$nameModel])) {
            $itemNew = $this->_listsModelsSecundaries[$nameModel];
        } else {
            $itemNew = new stdClass();
        }

        $itemNew->nameController = $nameControllerSec;
        $itemNew->hMenu = $hMenu;

        if (!$nameComponent) {
            $nameComponent = ExjRequest::GetParam('nameComponent');
        }
        $itemNew->nameComponent = $nameComponent;


        $this->_listsModelsSecundaries[$nameModel] = $itemNew;
    }

    private function _buildListsSecundaries(&$selfUI) {
        if (!$this->_listsModelsSecundaries) {
            return false;
        }


        $hMenuSec = null;

        foreach ($this->_listsModelsSecundaries as $nameModelSec => $itemSec) {
            $itemSecModelList = new stdClass();
            $itemSecModelList->nameModel = $nameModelSec;

            $ClassListModelSec = ExjUtil::GetNameClassModelListFromName($nameModelSec);

            if (!class_exists($ClassListModelSec)) {
                $this->getResponse()->setMsgError("No se encontró la clase: $ClassListModelSec<br/>Desde Modelo de Lista: " . get_class($this));
                continue;
            }

            if ($itemSec->hMenu) {
                $hMenuSec = $itemSec->hMenu;
            } else {
                $hMenuSec = $this->_hMenu;
            }

            $intanceSecModelList = new $ClassListModelSec($hMenuSec, $nameModelSec, $itemSec->nameController);

            $this->renderSecundaryListModel($intanceSecModelList, $nameModelSec);

            $itemSecModelList->nameController = $itemSec->nameController;
            // esta propiedad esta ligada con la UI
            $itemSecModelList->listModel = $intanceSecModelList->to_ui();

            if (!isset($selfUI->listsSecModels)) {
                $selfUI->listsSecModels = array();
            }

            $selfUI->listsSecModels[] = $itemSecModelList;
        }

        return true;
    }

    /**
     * Renderiza las listas secundarias
     *
     * @param ExjListModel $listSecModel
     * @param string $nameModelSec
     */
    protected function renderSecundaryListModel(ExjListModel $listSecModel, $nameModelSec) {
        
    }

    public function fixTitleToToolbar($fixed = false) {
        $this->_fixTitleToToolbar = $fixed;
    }

    /**
     * Fija el nombre del componente para incluir los modelos
     *
     * @param string $nameComponent
     */
    public function fixNameComponentDefault($nameComponent) {
        $this->_nameComponentDefault = $nameComponent;
    }

    public function hideHeadersToGrid($hideHeaders = true) {
        $this->_hideHeadersToGrid = $hideHeaders;
    }

    /**
     * overwrited. Después de cada columna registrada
     *
     * @param object $col Por referencia
     * @param string $dataIndex
     * @param int $indexCol La primera columna inicia en 0
     */
    protected function afterColRegistered(&$col, $dataIndex, $indexCol) {
        
    }

    /**
     * Indica si el usuario actual tiene acceso de solo lectura
     *
     * @return bool
     */
    public function isUserAccessReadOnly() {
        if ($this->_isUserAccessReadOnly !== null) {
            return $this->_isUserAccessReadOnly;
        }

        $this->_isUserAccessReadOnly = ExjRequest::GetParam('isReadOnlyAccess', null);

        if ($this->_isUserAccessReadOnly !== null) {
            return $this->_isUserAccessReadOnly;
        }

        $this->_isUserAccessReadOnly = !Exj::IsUserAccessEdit();

        return $this->_isUserAccessReadOnly;
    }

    /**
     * Forza a activar el boton de ver logs de persistencia
     *
     * @param bool $enable por defecto true
     */
    public function forceEnableViewLogPers($enable = true) {
        $this->_forceEnableViewLogPers = $enable;
        return $this;
    }

    public function readNameComponent(&$nameComponent) {
        if (!$nameComponent) {
            $nameComponent = $this->_nameComponentDefault;
        }

        return $nameComponent;
    }

    private function _addExtrasFields() {
        if ($this->_colExtraNameUser) {
            $this->registerFieldString($this->_colExtraNameUser, 'Modificado por');
        }

        if ($this->_colExtraDateRegister) {
            $this->registerFieldDateTime($this->_colExtraDateRegister, 'Cambio');
        }

        if ($this->_colExtrasInfoUltCambio) {
            $this->registerFieldString($this->_colExtrasInfoUltCambio, 'Ultimo Cambio');
        }
    }

    private function _addFirstCols() {
        if ($this->_addColOrder) {
            $this->registerFieldId('_ord', 'Num');
            // $this->registerColInt('_ord', 12, '', false);
            $this->registerColOrder('_ord', 'Exj.renderNumOrder');
        }
    }

    private function _addExtrasCols() {
        if ($this->_colExtrasInfoUltCambio) {
            $width = 60;
            if (!$this->getView()->getForceFit()) {
                $width = self::COL_ANCHO_DETALLE;
            }

            $this->registerColCustom($this->_colExtrasInfoUltCambio, '', self::FN_RENDER_TEXT_LASTCHANGE, $width, true, 'Muesta información del último cambio. Usuario - Fecha y hora', $this->_colExtrasHidden);
        }


        if ($this->_colExtraNameUser) {
            $width = 21;
            if (!$this->getView()->getForceFit()) {
                $width = self::COL_ANCHO_DEFECTO;
            }

            $this->registerCol($this->_colExtraNameUser, $width, true, true, 'Usuario que realizó el último cambio');
            if ($this->_colExtrasHidden) {
                $this->getColFromDataIndex($this->_colExtraNameUser)->setHidden();
            }
        }

        if ($this->_colExtraDateRegister) {
            $width = 15;
            if (!$this->getView()->getForceFit()) {
                $width = self::COL_ANCHO_FECHAHORA;
            }

            $this->registerColCustom($this->_colExtraDateRegister, '', 'Exj.rendererFormatDateTime', $width, true, 'Fecha y hora del último cambio');
            if ($this->_colExtrasHidden) {
                $this->getColFromDataIndex($this->_colExtraDateRegister)->hidden = true;
            }
        }
    }

    public function fixAnchor($anchor) {
        $this->_cfgGrid->anchor = $anchor;
    }

    /**
     * overwrited. Devuelve el manejador del menú
     *
     * @return ExjHelperMenu
     */
    protected function getHandlerMenu() {
        return null;
    }

    public function fixModeLocal() {
        $this->_mode = $this->_MODE_LOCAL;
        return $this;
    }

    public function fixModeRemote() {
        $this->_mode = $this->_MODE_REMOTE;
        return $this;
    }

    public function fixSortAsc() {
        $this->_sortDir = $this->_SORT_DIR_ASC;
        return $this;
    }

    public function fixSortDesc() {
        $this->_sortDir = $this->_SORT_DIR_DESC;
        return $this;
    }

    public function isSortAsc() {
        return ($this->_sortDir == $this->_SORT_DIR_ASC);
    }

    public function isSortDesc() {
        return ($this->_sortDir == $this->_SORT_DIR_DESC);
    }

    public function autoAddColsNameUserDateRegister($hiddenColExtras = true, $nameColNameUser = 'username', $nameColDateRegister = 'modificado_dt') {
        if ($this->isUserAccessReadOnly()) {
            return false;
        }

        $this->_colExtrasHidden = $hiddenColExtras;
        $this->_colExtraNameUser = $nameColNameUser;
        $this->_colExtraDateRegister = $nameColDateRegister;

        return true;
    }

    private $_colExtrasInfoUltCambio = '';

    public function autoAddColInfoUltimoCambio($hiddenColExtras = true) {
        if ($this->isUserAccessReadOnly()) {
            return $this;
        }

        $this->_colExtrasHidden = $hiddenColExtras;
        $this->_colExtrasInfoUltCambio = '_info_ultcambio';
        return $this;
    }

    /**
     * Adiociona columna ORD para indicar la secuencia
     *
     * @param bool $addColOrder Defecto true
     */
    public function autoAddColOrder($addColOrder = true) {
        $this->_addColOrder = $addColOrder;
    }

    /**
     * Por implementar
     *
     * @param string $nameListModel
     */
    static function getInstanceHMenu($nameListModel) {
        
    }

    public function setNameModelController($nameListModel, $nameController = '') {
        $this->_nameListModel = $nameListModel;
        if (!$nameController) {
            $nameController = $this->_nameListModel;
        }
        $this->_nameController = $nameController;

        return $this;
    }

    public function setterUrlProxy($method='view') {
        if (!$this->_nameController) {
            return $this;
        }

        $this->getStore()->setUrlProxy(
            Exj::BuildURLProxy(
                $this->_nameController, $method
            )
        );

        return $this;
    }

    /**
     * Envia si se soporta download
     *
     * @param bool $pdf
     * @param bool $excelXLSX
     * @param bool $excelXLS
     */
    public function setReportDownload($pdf, $excelXLSX, $excelXLS, $isReportHTML = false) {
        // NOTE: Se dehabilita pdf
        //	$pdf = false;
        $this->_isReportPDF = $pdf;
        $this->_isReportExcelXLS = $excelXLS;
        $this->_isReportExcelXLSX = $excelXLSX;
        $this->_isReportHTML = $isReportHTML;
    }

    public function setReportXMLDownload($nameOption, $nameFile) {
        $this->_isReportXML = true;
        $this->_reportXMLNameOption = $nameOption;
        $this->_reportXMLNameFile = $nameFile;
    }

    public function getModuleName() {
        $moduleName = '';
        if (!$this->_hMenu) {
            return $moduleName;
        }

        if (isset($this->_hMenu->moduleNameAccess)) {
            $moduleName = $this->_hMenu->moduleNameAccess;
        }

        return $moduleName;
    }

    /**
     * Indica si tiene acceso para crear Nuevo
     *
     * @return bool
     */
    public function isAccessNew() {
        if (!$this->_hMenu) {
            return false;
        }

        return $this->_hMenu->isNew;
    }

    /**
     * Indica si es posible guardar
     *
     * @return unknown
     */
    public function isAccessSave() {
        if (!$this->_hMenu) {
            return false;
        }

        return $this->_hMenu->isSave;
    }

    /**
     * Indica si tiene acceso para editar
     *
     * @return bool
     */
    public function isAccessEdit() {
        return $this->isAccessSave();
    }

    /**
     * Indica si tiene acceso para crear Eliminar
     *
     * @return bool
     */
    public function isAccessTrash() {
        if (!$this->_hMenu) {
            return false;
        }

        return $this->_hMenu->isTrash;
    }

    /**
     * Indica si tiene acceso para ver modelo solo lectura
     *
     * @return bool
     */
    public function isAccessView() {
        if (!$this->_hMenu) {
            return false;
        }

        return $this->_hMenu->isView;
    }

    /**
     * Indica si tiene acceso para ver el historial de los logs de persistencia
     *
     * @return bool
     */
    public function isAccessViewLogPersistence() {
        if (!ExjUser::IsRolSuperOAdminOContabilidad()) {
            return false;
        }

        if ($this->_forceEnableViewLogPers !== null) {
            return $this->_forceEnableViewLogPers;
        }

        if (!$this->_nameController) {
            return false;
        }

        if ($this->_nameController == 'sys_log_pers') {
            return false;
        }

        if (substr($this->_nameController, 0, 4) == 'rep_') {
            return false;
        }

        $lengthCtrl = strlen($this->_nameController);

        $nameEditable = $this->_nameController;
        if (substr($this->_nameController, $lengthCtrl - 1) == 's') {
            $nameEditable = substr($this->_nameController, 0, $lengthCtrl - 1);
        }

//		echo "<br/>nameEditable: $nameEditable";
        $ClassEditable = Exj::GetNameClassEditable($nameEditable);
        if (class_exists($ClassEditable)) {
            return true;
        }

        if (class_exists($ClassEditable)) {
            return true;
        }

        //	echo "<br/>$ClassEditable NO existe";


        return false;
    }

    /**
     * Indica si tiene acceso para los Reportes
     *
     * @return bool
     */
    public function isAccessReports() {
        if ($this->forceShowReports) {
            return true;
        }

        if (!$this->_hMenu) {
            return false;
        }

        if (!$this->_isReportExcelXLS && !$this->_isReportExcelXLSX && !$this->_isReportPDF && !$this->_isReportHTML) {
            return false;
        }

        return $this->_hMenu->isReports;
    }

    public function isAccessHelp() {
        if (!$this->_hMenu) {
            return false;
        }

        return $this->_hMenu->isAccessHelp();
    }

    /**
     * Seteo de configuración básica del modelo de listas
     *
     * @param string $title
     * @param string $fieldKey
     * @param bool $withPagination
     */
    public function setConfig($title, $fieldKey, $withPagination = true) {
        $this->titleList = $title;
        $this->fieldKey = $fieldKey;
        $this->withPagination = $withPagination;

        $this->registerFieldId($fieldKey, 'idList');
        /*
          if (!$this->defaultSort) {
          $this->defaultSort = $fieldKey;
          }
         */
        if (!$this->withPagination) {
            $this->pageSize = 0;
        }
    }

    public function setDefaultSort($nameField){
        $this->defaultSort = $nameField;
        return $this;
    }

    /**
     * Se setea la propiedad exjNameFieldId al grid hacia la UI, donde este campo sirve para que se envie el valor ID del registro al server
     *
     * @param string $nameFieldId Este campo debe estar registrado en el modelo de lista
     */
    public function setNameFieldId($nameFieldId) {
        $this->_nameFieldId = $nameFieldId;
    }

    public function setTitle($title) {
        $this->titleList = $title;
    }

    public function setChildNameList($nameList) {
        $this->_childNameList = $nameList;
    }

    public function setChildNameEditable($nameEditable, $keyEditable = '') {
        // echo "<br/>setChildNameEditable nameEditable: $nameEditable Clase: " . get_class($this);

        $this->_childNameEditable = $nameEditable;
        if (!$keyEditable) {
            $keyEditable = $nameEditable;
        }
        $this->_childKeyEditable = $keyEditable;

        return $this;
    }

    public function setChildNameComponent($nameComponent) {
        $this->_childNameComponent = $nameComponent;
    }

    public function setChildNameParentEditable($nameParentEditable) {
        $this->_childParentEditable = $nameParentEditable;
    }

    /**
     * Cuando se devuelve data, esta función debe ser sobrescrita
     *
     * @return object
     */
    public function onGetData(&$items, &$total) {
        $items = array();
        $total = 0;

        return false;
    }

    public function readData() {
        $items = null;
        $total = 0;

        $this->onGetData($items, $total);
        if ($items && ($total <= 0)) {
            $total = count($items);
        }

        $this->setData($items, $total);
    }

    /* modelo de campos */

    public function &registerFieldString($name, $alias = '', $useNull = false) {
        return $this->_registerField($name, ExjField::TYPE_STRING, $alias, '', $useNull);
    }

    public function &registerFieldId($name, $alias = '', $useNull = false) {
        return $this->_registerField($name, ExjField::TYPE_INT, $alias, '', $useNull);
    }

    public function &registerFieldIdNullable($name, $alias = '') {
        return $this->registerFieldId($name, $alias, true);
    }

    public function &registerFieldInt($name, $alias = '', $useNull = false) {
        return $this->_registerField($name, ExjField::TYPE_INT, $alias, '', $useNull);
    }

    public function &registerFieldIntNullable($name, $alias = '') {
        return $this->registerFieldId($name, $alias, true);
    }

    public function &registerFieldBool($name, $alias = '', $useNull = false) {
        return $this->_registerField($name, ExjField::TYPE_BOOL, $alias, '', $useNull);
    }

    public function &registerFieldFloat($name, $alias = '', $useNull = false) {
        return $this->_registerField($name, ExjField::TYPE_FLOAT, $alias, '', $useNull);
    }

    public function &registerFieldFloatNullable($name, $alias = '') {
        return $this->registerFieldFloat($name, $alias, true);
    }

    public function &registerFieldRaw($name, $alias = '') {
        return $this->_registerField($name, '', $alias);
    }

    public function registerFieldDate($name, $alias = '', $useNull = false) {
        $this->_registerField($name, ExjField::TYPE_DATE, $alias, 'Y-m-d', $useNull);
    }

    public function &registerFieldDateTime($name, $alias = '', $useNull = false) {
        // $this->_registerField($name, ExjField::TYPE_DATE, $alias, 'timestamp');
        return $this->_registerField($name, ExjField::TYPE_DATE, $alias, 'Y-m-d H:i:s', $useNull);
    }

    public function registerColEditorNumberField($dataIndex, $width = 12, $maxLength = 8, $maxValue = 99999, $renderNumber = 'Exj.renderEmptyDecimal2', $alignRight = true, $sortable = true) {
        $this->registerColCustom($dataIndex, '', $renderNumber, $width, $sortable);
        if ($alignRight) {
            $this->colAlignRight($dataIndex);
        }

        $nuf = ExjUI::NewNumberField('');
        $nuf->allowNegative = true;
        $nuf->maxLength = $maxLength;
        $nuf->maxValue = $maxValue;

        return $this->registerColEditor($dataIndex, $nuf);
    }

    public function registerColEditorNumberFloatField($dataIndex, $width = 12, $maxLength = 8, $maxValue = 99999, $renderNumber = 'Exj.renderEmptyDecimal2', $alignRight = true, $sortable = true) {
        $this->registerColCustom($dataIndex, '', $renderNumber, $width, $sortable);
        if ($alignRight) {
            $this->colAlignRight($dataIndex);
        }

        $nuf = ExjUI::NewNumberField('');
        $nuf->setAllowNegative(false)
            ->setMaxLength($maxLength)
            ->setMaxValue($maxValue);

        return $this->registerColEditor($dataIndex, $nuf);
    }

    public function registerColEditorNumberIntField($dataIndex, $width = 12, $maxLength = 9, $maxValue = 999999999, $fnRenderer = 'Exj.renderNumberBlue2')
    {
        $this->registerColCustom($dataIndex, '', $fnRenderer, $width);
        $this->colAlignRight($dataIndex);

        $nuf = ExjUI::NewNumberField('', '', '99%', true);
        
        $nuf->setAllowNegative(false)
            ->setMaxLength($maxLength)
            ->setMaxValue($maxValue)
            ->setSelectOnFocus();

        return $this->registerColEditor($dataIndex, $nuf);
    }

    public function registerColEditorTextField($dataIndex, $width = 15, $maxLength = 30, $minLength = null, $fnRenderer = SELF::FN_RENDER_TEXT, $sortable = true, $toUpper = false) {
        $this->registerColCustom($dataIndex, '', $fnRenderer, $width, $sortable);

        $txf = ExjUI::NewTextField('');
        $txf->maxLength = $maxLength;
        if ($minLength !== null) {
            $txf->minLength = $minLength;
        }

        if ($toUpper) {
            $txf->setClsTextUpper();
        }

        return $this->registerColEditor($dataIndex, $txf);
    }

    public function registerColEditorTextFieldRequired($dataIndex, $width = 15, $maxLength = 30, $minLength = 1, $fnRenderer = SELF::FN_RENDER_TEXT, $toUpper = false) {
        $this->registerColCustom($dataIndex, '', $fnRenderer, $width);

        $txf = new ExjUITextField();

        $txf->setMaxLength($maxLength)
            ->setMinLength($minLength)
            ->setAllowBlank(false);

        if ($toUpper) {
            $txf->setClsTextUpper();
        }

        return $this->registerColEditor($dataIndex, $txf);
    }

    public function registerColEditorDateTimeField($dataIndex, $width = 15, $disabled = false, $useRenderDate = true)
    {
        $col = $this->registerColDateTime($dataIndex, $width);
        // xxx
        if ($useRenderDate) {
            self::ApplyRenderToCol($col, 'Exj.rendererFormatDate');
        }

        $daft = ExjUI::NewDateTimeField('');
        if ($useRenderDate) {
            $daft->addAltFormat(Exj::GetValueCfg('uiFormatDateDef'));
        }

        if ($disabled) {
            $daft->setDisabled();
        }

        return $this->registerColEditor($dataIndex, $daft);
    }

    public function registerColEditorDateField($dataIndex, $width = 15, $isDateTime = false, $disabled = false) {
        if ($isDateTime) {
            $this->registerColDateTime($dataIndex, $width);
        } else {
            $this->registerColDate($dataIndex, $width);
        }

        $daf = ExjUI::NewDateField('');
        if ($disabled) {
            $daf->setDisabled();
        }

        /*
          $daf->maxLength = $maxLength;
          if ($minLength !== null) {
          $daf->minLength = $minLength;
          }
         */

        return $this->registerColEditor($dataIndex, $daf);
    }

    // $this->registerFieldDateTime('modificado_dt', 'Cambio');

    public function registerFieldComplex($name, $fnUIConvert, $alias = '', $defaultValue = array()) {
        $this->_registerField($name, '', $alias);
        $f = &$this->_fields[$name];

        $f->complex();

        $f->defaultValue = $defaultValue;
        $f->convert = $fnUIConvert;
        return $f;
    }

    private function &_registerField($name, $type, $alias = '', $dateFormat = '', $useNull = null)
    {

        $name = trim($name);
        
        if ($dateFormat && !$type) {
            $type = ExjField::TYPE_DATE;
        }

        if (isset($this->_fields[$name])) {
            $field = &$this->_fields[$name];
            $field->setType($type)
                ->setAlias($alias)
                ->setDateFormat($dateFormat);
        } else {
            $field = new ExjField($name, $type, $alias);
            $field->setDateFormat($dateFormat);

            $this->_fields[$name] = $field;
        }

        if ($useNull !== null) {
            $this->_fields[$name]->useNull($useNull);
        }

        return $this->_fields[$name];
    }

    public function findField($name) {
        if (empty($this->_fields)) {
            return null;
        }

        return (isset($this->_fields[$name]) ? $this->_fields[$name] : null);
    }

    /* modelo de columnas */

    /**
     * Registro de Columna
     *
     * @param string $dataIndex
     * @param int $width
     * @param bool $sortable
     * @param bool $isRenderText
     * @param string $tooltip
     * @param bool $menuDisabled Defecto null sino se aplica esta prop no se setea
     * @return object Instancia de la Clase
     */
    public function &registerCol($dataIndex, $width = null, $sortable = true, $isRenderText = true, $tooltip = '', $menuDisabled = null) {
        $renderer = '';
        if ($isRenderText) {
            $renderer = self::FN_RENDER_TEXT;
        }

        return $this->registerColCustom(
            $dataIndex, '', $renderer, $width, $sortable, $tooltip, false, $menuDisabled
        );
    }

    /**
     * Aplica a las columnas especificadas como ocultas o no
     *
     * @param mixed $datasIndexs String separados por coma o Array
     * @param bool $hidden Defecto true
     */
    public function applyColHidden($datasIndexs, $hidden = true) {
        if (!$datasIndexs) {
            return;
        }
        if (!is_array($datasIndexs)) {
            $datasIndexs = explode(',', $datasIndexs);
        }

        if (count($datasIndexs) == 0) {
            return;
        }

        foreach ($datasIndexs as $dataIndex) {
            $dataIndex = trim($dataIndex);
            if (!$dataIndex) {
                continue;
            }

            $colx = $this->getColFromDataIndex($dataIndex);
            if (!$colx) {
                continue;
            }

            $colx->setHidden($hidden);
        }
    }

    public function registerColHidden($dataIndex, $width = null, $sortable = true, $isRenderText = true) {
        $this->registerCol($dataIndex, $width, $sortable, $isRenderText);
        $this->getColFromDataIndex($dataIndex)->setHidden();

        return $this;
    }

    public function registerColDateTimeHidden($dataIndex, $width = 15, $alias = '', $sortable = true, $tooltip = '') {
        $this->registerColDateTime($dataIndex, $width, $alias, $sortable, $tooltip);
        $this->getColFromDataIndex($dataIndex)->hidden = true;

        return $this;
    }

    public function registerColTextSino($dataIndex, $width = null, $alias = '', $sortable = true, $tooltip = '') {
        if (!$width) {
            $width = self::COL_ANCHO_ESTADO + 21;
        }

        $this->registerColCustom($dataIndex, $alias, self::FN_RENDER_TEXT_SINO, $width, $sortable);

        return $this;
    }

    public function &registerColMemo($dataIndex, $width = 30, $hidden = true, $alias = '', $sortable = true, $tooltip = '') {
        return $this->registerColCustom($dataIndex, $alias, self::FN_RENDER_TEXT_MEMO, $width, $sortable, $tooltip, $hidden);
    }

    public function registerColAlias($dataIndex, $alias, $width = null, $sortable = true, $isRenderText = true, $tooltip = '') {
        $renderer = '';
        if ($isRenderText) {
            $renderer = self::FN_RENDER_TEXT;
        }

        return $this->registerColCustom($dataIndex, $alias, $renderer, $width, $sortable, $tooltip);
    }

    /**
     * overwrited. Valida la columna, cuando sea acceso de solo lectura
     *
     * @param string $dataIndex
     * @param object $col Por referencia
     * @return bool Retornar false si no se desea que aparezca la columna, sino true
     */
    protected function validateColInReadOnly($dataIndex, &$col) {
        return true;
    }

    /**
     * overwrited. Valida la columna, cuando sea acceso edit, o no sea de solo lectura
     *
     * @param string $dataIndex
     * @param object $col Por referencia
     * @return bool Retornar false si no se desea que aparezca la columna, sino true
     */
    protected function validateColInEdit($dataIndex, &$col) {
        return true;
    }

    /**
     * Establece el ancho fijo a la columna 
     *
     * @param string $dataIndex
     * @param bool $fixed Defecto true
     * @param int $newWidth Defecto null No lo setea si es valor vacio
     * @param string $id Defecto null No lo setea si es valor vacio
     * @return mixed false si no está registrada la columna, sino object column
     */
    public function fixColWidthFixed($dataIndex, $fixed = true, $newWidth = null, $id = null) {
        $colx = $this->getColFromDataIndex($dataIndex);
        if (!$colx) {
            return false;
        }

        $colx->setResizable(!$fixed)->setFixed($fixed);

        if ($newWidth) {
            $colx->setWidth($newWidth)->setMenuDisabled();
        }

        if ($id) {
            $colx->setId($id);
        }

        return $colx;
    }

    public function fixColCSS($dataIndex, $css) {
        $colx = $this->getColFromDataIndex($dataIndex);
        if (!$colx) {
            return false;
        }

        if (!isset($colx->css)) {
            $colx->css = '';
        }

        $colx->css .= $css;

        //	echo "<br/>Fijando a columna: $dataIndex css: $css";

        return $colx;
    }

    public function &registerColSeparator($width = 12) {
        $dataIndex = 'separator_blank';

        $this->registerFieldString($dataIndex, ' ');

        $col = $this->registerColCustom($dataIndex, '', '', $width, false, '', false, true);
        $this->fixColWidthFixed($dataIndex, true, $width, $dataIndex);

        return $col;
    }

    public function &registerColOrder($dataIndex = '_ord', $renderer = 'renderColsContainers') {
        $col = $this->registerColCustom($dataIndex, '', $renderer, 39, false, 'Order', false, true);
        $this->fixColWidthFixed($dataIndex);
        // $this->colAlignRight($dataIndex);
        $this->colAlignCenter($dataIndex);
        return $col;
    }

    public function fixColAutoActionEdit($dataIndexCol, $css = '') {
        $this->_fixColAutoAction($dataIndexCol, 'edit', $css);
        return $this;
    }

    public function fixColAutoActionView($dataIndexCol, $css = '') {
        $this->_fixColAutoAction($dataIndexCol, 'view', $css);
        return $this;
    }

    public function fixColAutoActionsEditView($dataIndexCol, $css = '') {
        $this->fixColAutoActionEdit($dataIndexCol, $css);
        $this->fixColAutoActionView($dataIndexCol, $css);
        return $this;
    }

    private function _fixColAutoAction($dataIndexCol, $exjAction, $css = '') {
        if (!$this->_colsAutoAction) {
            $this->_colsAutoAction = array();
        }

        $item = null;

        if (isset($this->_colsAutoAction[$dataIndexCol])) {
            $item = $this->_colsAutoAction[$dataIndexCol];
        } else {
            $item = new stdClass();
            $item->exjActions = array();
            $item->css = '';
        }

        if (!in_array($exjAction, $item->exjActions)) {
            $item->exjActions[] = $exjAction;
        }

        if ($css) {
            if ($item->css) {
                $item->css .= ' ';
            }
            $item->css .= $css;
        }

        $this->_colsAutoAction[$dataIndexCol] = $item;
    }

    public function &registerColCustom($dataIndex, $alias, $renderer, $width = null, $sortable = true, $tooltip = '', $hidden = false, $menuDisabled = null)
    {
        $col = $this->getColFromDataIndex($dataIndex, true);

        $col->setDataIndex($dataIndex)->setHeader($alias);

        self::ApplyRenderToCol($col, $renderer);
        $col->setWidth($width)->setSortable($sortable);

        if ($tooltip) {
            $col->setTooltip(ExjText::_($tooltip));
        }
        if ($hidden) {
            $col->setHidden($hidden);
        }
        if ($menuDisabled !== null) {
            $col->setMenuDisabled($menuDisabled ? true : false);
        }

        if (!$this->defaultSort) {
            $this->defaultSort = $col->dataIndex;
        }

        if (!isset($col->align)) {
            $col->setAlign('center');
        } elseif (!$col->align) {
            $col->setAlign('center');
        }

        $this->_cols[] = $col;

        return $col;
    }

    public function getCols() {
        return $this->_cols;
    }

    public static function ApplyRenderToCol(&$col, $fnRenderer) {
        if (!is_object($col)) {
            return false;
        }

        $col->setRenderer($fnRenderer);

        return true;
    }

    public function registerActionColumn($dataIndex, $width = 0) {
        if (!$width) {
            $width = self::COL_ANCHO_ACCION;
        }
        
        $col = $this->registerColAction($dataIndex, '', $width);
        $col->setMenuDisabled();

        return $col;
    }

    public function registerColAction($dataIndex, $nameFnRenderer, $width = 15) {
        $col = null;
        if ($dataIndex) {
            if ($width == 15 || $width < 0) {
                $width = 18;
                if (!$this->getView()->getForceFit()) {
                    $width = self::COL_ANCHO_DEFECTO;
                }
            }

            $col = $this->registerColCustom(
                $dataIndex, '', $nameFnRenderer, $width, false
            );
        }

        if (!$col) {
            $col = $this->getColFromDataIndex('actioncolumn' . count($this->_cols), true);
            $this->_cols[] = $col;
        }

        $col->align = 'center';
        // print_r($col);

        $col->xtype = 'actioncolumn';

        // no activar, es otra estructura con items
        // $col->items = array();
        
        $col->isActionColumn = true;

        return $col;
    }

    public function registerColEditor($dataIndex, $objUI, $registerCol = false, $width = null)
    {
        if ($registerCol) {
            $this->registerCol($dataIndex, $width);
        }

        $col = $this->getColFromDataIndex($dataIndex, true);

        // add prop custom
        $objUI->exjName = $dataIndex;

        $col->editor = $objUI;

        return $this;
    }

    public function registerColEditorComboBoxYesNo($dataIndex, $alias = '', $width = 18, $tooltip = '') {
        $this->registerColCustom($dataIndex, $alias, self::FN_RENDER_TEXT_SINO, $width, true, $tooltip);

        $itemsYesNo = array();
        $itemsYesNo[] = ExjUI::NewItemLookup(1, 'Yes');
        $itemsYesNo[] = ExjUI::NewItemLookup(0, 'No');

        $cmbYesNo = ExjUI::NewComboSimple('', '', $itemsYesNo);
        $cmbYesNo->forceSelection = true;
        $cmbYesNo->setEditable();
        return $this->registerColEditor($dataIndex, $cmbYesNo);
    }

    public function registerColEditorCheckbox($dataIndex, $alias = '', $tooltip = '', $width = 18, $renderer = SELF::FN_RENDER_TEXT_SINO) {
        $this->registerColCustom($dataIndex, $alias, $renderer, $width, true, $tooltip);

        $chb = ExjUI::NewCheckbox('', '');
        return $this->registerColEditor($dataIndex, $chb);
    }

    public function registerColCheck($dataIndex, $alias = '', $width = 54, $tooltip = '', $handler = null, $beforeCheckColumn = null) 
    {
        $col = $this->registerColCustom($dataIndex, $alias, '', $width, true, $tooltip);
        // sss
        $col->setXtype('checkcolumn');
        if ($handler) {
            $col->handler = $handler;
        }
        if ($beforeCheckColumn) {
            $col->beforeCheckColumn = $beforeCheckColumn;
        }

        return $col;
    }

    public function &registerColDateTime($dataIndex, $width = 15, $alias = '', $sortable = true, $tooltip = '') {
        if (!$this->getView()->getForceFit() && $width <= 15) {
            $width = self::COL_ANCHO_FECHAHORA;
        }

        return $this->registerColCustom(
            $dataIndex,
            $alias,
            'Exj.rendererFormatDateTime',
            $width,
            $sortable,
            $tooltip
        );
    }

    public function &registerColDate($dataIndex, $width = 12, $alias = '', $sortable = true)
    {
        if (!$this->getView()->getForceFit() && $width <= 12) {
            $width = self::COL_ANCHO_FECHA;
        }

        return $this->registerColCustom($dataIndex, $alias, 'Exj.rendererFormatDate', $width, $sortable);
    }

    private $_alignColsDecimal2Default = '';

    public function fixAlignColsDecimal2Default($align) {
        $this->_alignColsDecimal2Default = $align;
    }

    
    public function &registerColDecimal2ZeroRed($dataIndex, $width = null, $alias = '', $sortable = false, $tooltip = '')
    {
        if ($width == null) {
            $width = self::COL_ANCHO_VALOR;
        }

        $result = $this->registerColCustom(
            $dataIndex, $alias, 'Exj.renderDecimal2ZeroRed', $width, $sortable, $tooltip
        );
        
        if ($this->_alignColsDecimal2Default) {
            $this->colAlignCustom($dataIndex, $this->_alignColsDecimal2Default);
        } else {
            $this->colAlignRight($dataIndex);
        }

        return $result;
    }

    public function &registerColDecimal2($dataIndex, $width = null, $alias = '', $sortable = false, $tooltip = '')
    {
        if ($width == null) {
            $width = self::COL_ANCHO_VALOR;
        }

        $result = $this->registerColCustom(
            $dataIndex, $alias, 'Exj.renderDecimal2', $width, $sortable, $tooltip
        );
        
        if ($this->_alignColsDecimal2Default) {
            $this->colAlignCustom($dataIndex, $this->_alignColsDecimal2Default);
        } else {
            $this->colAlignRight($dataIndex);
        }

        return $result;
    }

    public function &registerColDecimal3($dataIndex, $width = null, $alias = '', $sortable = false, $tooltip = '') {
        if ($width == null) {
            $width = self::COL_ANCHO_VALOR;
        }

        $result = $this->registerColCustom($dataIndex, $alias, 'Exj.renderDecimal3', $width, $sortable, $tooltip);
        if ($this->_alignColsDecimal2Default) {
            $this->colAlignCustom($dataIndex, $this->_alignColsDecimal2Default);
        } else {
            $this->colAlignRight($dataIndex);
        }

        return $result;
    }

    public function &registerColDecimal2Zero($dataIndex, $width = null, $alias = '', $sortable = false) {
        if ($width == null) {
            $width = self::COL_ANCHO_VALOR;
        }

        $result = $this->registerColCustom($dataIndex, $alias, 'Exj.renderDecimal2Zero', $width, $sortable);
        if ($this->_alignColsDecimal2Default) {
            $this->colAlignCustom($dataIndex, $this->_alignColsDecimal2Default);
        } else {
            $this->colAlignRight($dataIndex);
        }

        return $result;
    }

    public function registerColDecimal2Hidden($dataIndex, $width = null, $sortable = true) {
        if ($width === null) {
            $width = self::COL_ANCHO_VALOR;
        }

        $this->registerColCustom($dataIndex, '', 'Exj.renderDecimal2', $width, $sortable, '', true);

        if ($this->_alignColsDecimal2Default) {
            $this->colAlignCustom($dataIndex, $this->_alignColsDecimal2Default);
        } else {
            $this->colAlignRight($dataIndex);
        }
    }

    public function &registerColInt($dataIndex, $width = 15, $alias = '', $sortable = true) {
        $result = $this->registerColCustom(
            $dataIndex, $alias, 'Exj.renderNumberBlue', $width, $sortable
        );
        
        $this->colAlignRight($dataIndex);
        return $result;
    }

    public function &registerColInt2($dataIndex, $width = 15, $alias = '', $sortable = true) {
        $result = $this->registerColCustom($dataIndex, $alias, 'Exj.renderNumberBlue2', $width, $sortable);
        $this->colAlignRight($dataIndex);
        return $result;
    }

    public function &getColFromDataIndex($dataIndex, $noFoundCreate = false) {
        $colFound = null;
        foreach ($this->_cols as &$col) {
            if ($col->dataIndex == $dataIndex) {
                $colFound = $col;
                break;
            }
        }

        if ($noFoundCreate && $colFound == null) {
            $colFound = new ExjUIColumn($dataIndex);
        }

        return $colFound;
    }

    public function colAlignCustom($dataIndex, $align) {
        $c = $this->getColFromDataIndex($dataIndex);
        if (!$c) {
            return false;
        }
        
        return $c->setAlign($align);;
    }

    public function colAlignRight($dataIndex) {
        $c = $this->getColFromDataIndex($dataIndex);
        if (!$c) {
            return false;
        }

        $c->setAlign(self::ALIGN_RIGHT);
        return $c;
    }

    public function colAlignLeft($dataIndex) {
        $c = $this->getColFromDataIndex($dataIndex);
        if (!$c) {
            return false;
        }

        $c->setAlign(self::ALIGN_LEFT);
        return $c;
    }

    public function colAlignCenter($dataIndex) {
        $c = $this->getColFromDataIndex($dataIndex);
        if (!$c) {
            return false;
        }

        $c->setAlign(self::ALIGN_CENTER);
        return $c;
    }

    /**
     * overwrite. Init del Modelo de Lista
     *
     */
    protected function listInit() {
        
    }

    /**
     * overwrite. Registro de Campos
     *
     */
    protected function listRegisterFields() {
        
    }

    /**
     * overwrite. Registro de Columnas
     *
     */
    protected function listRegisterCols() {
        
    }

    /**
     * Devuelve modelo de fields para UI, por lo general usado en Store
     *
     * @return array
     */
    public function to_ui_fields() {
        $ui_fields = array();

        foreach ($this->_fields as $name => $f) {
            $ui_field = $name;
            if ($f->type) {
                $ui_field = new stdClass();
                $ui_field->name = $name;
                $ui_field->type = $f->type;
                if ($f->dateFormat) {
                    $ui_field->dateFormat = $f->dateFormat;
                }
            }

            if ($f->isComplex) {
                $ui_field = new stdClass();
                $ui_field->name = $name;
                if (isset($f->defaultValue)) {
                    $ui_field->defaultValue = $f->defaultValue;
                }
                $ui_field->convert = $f->convert;
            }

            if (is_object($ui_field)) {
                if (isset($f->isUseNull)) {
                    $ui_field->useNull = $f->isUseNull;
                }
            }

            $ui_fields[] = $ui_field;
        }

        // print_r($ui_fields);

        return $ui_fields;
    }

    private function _validateColumnModel(ExjUIColumn $col, $itemsParams){
        if (empty($itemsParams)) {
            return $this;
        }

        foreach ($itemsParams as $itemParam) {
            if ($itemParam->name_prop != $col->dataIndex) {
                continue;
            }

            if ($itemParam->is_hidden) {
                $col->setHidden();
            }

            if ($itemParam->label_prop && strlen($itemParam->label_prop) <= 15) {
                $col->setHeader($itemParam->label_prop);
            }
        }

        return $this;
    }

    public function getParamsModel(){
        $paramsModel = array();

        $items = AppModelsParamsData::GetItemsFromSession();
        if (!$items || empty($items)) {
            return $paramsModel;
        }

        $selfNameClass = get_class($this);
        foreach ($items as $item) {
            if ($item->name_class == $selfNameClass) {
                $paramsModel[] = $item;
            }
        }

        return $paramsModel;
    }

    /**
     * Devuelve modelo de columnas para la UI
     *
     * @return array
     */
    public function to_ui_columns() {
        $ui_columns = array();
        $isReadOnly = $this->isUserAccessReadOnly();
        $itemsParams = $this->getParamsModel();
        foreach ($this->_cols as $col) {
            if ($isReadOnly) {
                if ($this->validateColInReadOnly($col->dataIndex, $col) === false) {
                    continue;
                }
            } else {
                if ($this->validateColInEdit($col->dataIndex, $col) === false) {
                    continue;
                }
            }


            if (!$col) {
                continue;
            }

            // mapeo Modelo list hacia UI
            if (!$col->getHeader()) {
                $col->setHeader($this->_getAliasFromFields($col->dataIndex));
            }

            $this->_validateColumnModel($col, $itemsParams);

            $ui_column = $col->toObject();
            // $ui_column = $col;

            // adicion de auto action
            if ($this->_colsAutoAction) {
                foreach ($this->_colsAutoAction as $dataIndexCol => $dataAutoAction) {
                    if ($dataIndexCol != $ui_column->dataIndex) {
                        continue;
                    }

                    $ui_column->dataAction = $dataAutoAction;
                }
            }

            $ui_columns[] = $ui_column;
        }

        // print_r($ui_columns);

        return $ui_columns;
    }

    public function to_ui_cfgPagingToolbar() {
        $cfg = new stdClass();
        if (!$this->withPagination) {
            return $cfg;
        }

        if ($this->isModeLocal()) {
            $this->withPagination = false;
            return $cfg;
        }

        $cfg->xtype = 'paging';
        if ($this->pageSize) {
            $cfg->pageSize = $this->pageSize;
        }

        $cfg->displayInfo = true;
        $strTopics = ExjText::_($this->nameTopics);
        $cfg->displayMsg = ExjText::__('Presentando') . " $strTopics {0} - {1} " . ExjText::__('de') . " {2}";
        $cfg->emptyMsg = ExjText::__('No se encontraron') . " $strTopics";

        return $cfg;
    }

    /**
     * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Izquierda
     *
     * @return array
     */
    public function getItemsTopbarExtrasLeft() {
        return null;
    }

    /**
     * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Derecha
     *
     * @return array
     */
    public function getItemsTopbarExtrasRight() {
        return null;
    }

    /**
     * overweited. Personalida el botón New
     *
     * @param string $text
     * @param string $iconCls
     * @param string $tooltip Si no se fija este valor se fija con el tooltip genérico
     */
    protected function listButtonAddCustom(&$text, &$iconCls, &$tooltip) {
        
    }

    /**
     * overwrited. Retorna un array de botones que se adicionarán en el toolbar, alado del botón New
     *
     * @param string $strNameTopic
     * @return array Arreglo de botones
     */
    protected function getButtonsAddExtras($strNameTopic) {
        return null;
    }

    public function to_ui_cfgTopToolbar() {
        $cfg = new stdClass();

        $cfg->xtype = 'toolbar';
        $cfg->items = array();
        $strNameTopic = ExjText::_($this->nameTopic);

        if ($this->isAccessNew()) {
            $textButtonNew = ExjText::__('Nuevo');
            $tooltipButtonNew = '';
            $iconClsButtonNew = '';
            $this->listButtonAddCustom($textButtonNew, $iconClsButtonNew, $tooltipButtonNew);

            if (!$tooltipButtonNew) {
                $tooltipButtonNew = "$textButtonNew " . $strNameTopic;
            }

            $btnNew = ExjUI::NewButtonAdd($textButtonNew, $tooltipButtonNew);
            if ($iconClsButtonNew) {
                $btnNew->iconCls = $iconClsButtonNew;
            }

            $cfg->items[] = $btnNew;

            $buttonsExtrasAdd = $this->getButtonsAddExtras($strNameTopic);
            if ($buttonsExtrasAdd && is_array($buttonsExtrasAdd)) {
                foreach ($buttonsExtrasAdd as $buttonExtraNew) {
                    if (!is_object($buttonExtraNew)) {
                        continue;
                    }
                    if (!isset($buttonExtraNew->iconCls)) {
                        $buttonExtraNew->iconCls = 'exj-btn-new';
                    }
                    if (!$buttonExtraNew->iconCls) {
                        $buttonExtraNew->iconCls = 'exj-btn-new';
                    }

                    $cfg->items[] = $buttonExtraNew;
                }

                $cfg->items[] = '-';
            }
        }
        if ($this->isAccessEdit()) {
            $cfg->items[] = ExjUI::NewButtonEdit('', ExjText::__('Editar') . ' ' . $strNameTopic);
        }

        if ($this->isAccessView()) {
            if (count($cfg->items) > 0) {
                $cfg->items[] = '-';
            }
            $strVer = ExjText::__('Ver');
            $cfg->items[] = ExjUI::NewButtonView("$strVer $strNameTopic" . '...', "$strVer $strNameTopic");
        }

        if ($this->addItemsTopbarExtras) {
            $itemsTopbarExtrasLeft = $this->getItemsTopbarExtrasLeft();
            if ($itemsTopbarExtrasLeft && count($itemsTopbarExtrasLeft) > 0) {
                if (count($cfg->items) > 0) {
                    $cfg->items[] = '-';
                }

                foreach ($itemsTopbarExtrasLeft as $item) {
                    $cfg->items[] = $item;
                }
            }
        }

        if ($this->_fixTitleToToolbar && $this->titleList) {
            $tbTitleCenter = new stdClass();
            // $tbTitleCenter->xtype = 'tbtext';
            // $tbTitleCenter->xtype = 'panel';
            $tbTitleCenter->xtype = 'container';

            $tbTitleCenter->cls = 'exj-toolbar-title';
            //	$tbTitleCenter->itemCls = 'exj-toolbar-title';
            $tbTitleCenter->layout = 'exj_centertb'; // layout personalizado
            //	$tbTitleCenter->text = $this->getTitleList();
            $tbTitleCenter->html = '<div class="exj-toolbar-title">' . $this->getTitleList() . '</div>';

            $cfg->items[] = $tbTitleCenter;

            // $this->setTitle('');
        }

        if (count($cfg->items) > 0) {
            $cfg->items[] = '->';
        }

        if ($this->addItemsTopbarExtras) {
            $itemsTopbarExtrasRight = $this->getItemsTopbarExtrasRight();
            if ($itemsTopbarExtrasRight && count($itemsTopbarExtrasRight) > 0) {
                foreach ($itemsTopbarExtrasRight as $item) {
                    $cfg->items[] = $item;
                }
            }
        }

        if ($this->isAccessViewLogPersistence()) {
            $cfg->items[] = ExjUI::NewButtonViewLogPersistence('', ExjText::__('Historial de Cambios') . " $strNameTopic");
        }

        if ($this->isAccessTrash()) {
            $cfg->items[] = ExjUI::NewButtonDelete($this->_textButtonDelete . '...', ExjText::__($this->_textButtonDelete) . " $strNameTopic");
        }

        if ($this->isAccessReports()) {
            if (count($cfg->items) > 0) {
                $cfg->items[] = '-';
            } else {
                $cfg->items[] = '->';
            }

            // se crea en forma de menú
            $itemsMenuReporte = array();

            if ($this->_isReportXML) {
                $itemsMenuReporte[] = ExjUI::NewMenuItemXML($this->_reportXMLNameOption);
                if ($this->_isReportPDF || $this->_isReportExcelXLSX) {
                    $itemsMenuReporte[] = '-';
                }
            }

            if ($this->_isReportHTML) {
                $itemsMenuReporte[] = ExjUI::NewMenuItemHTML();
                if ($this->_isReportPDF || $this->_isReportExcelXLSX) {
                    $itemsMenuReporte[] = '-';
                }
            }


            //	$itemsMenuReporte[] = ExjUI::NewMenuCheckItem('Enviar a Correo');
            //	$itemsMenuReporte[] = '-';


            if ($this->_isReportPDF) {
                $itemsMenuReporte[] = ExjUI::NewMenuItemPDF();
            }
            if ($this->_isReportExcelXLSX) {
                $itemsMenuReporte[] = ExjUI::NewMenuItemExcelXLSX();
            }
            if ($this->_isReportExcelXLS) {
                $itemsMenuReporte[] = ExjUI::NewMenuItemExcelXLS();
            }

            $nameBtnReporte = "Reporte";
            if ($this->requiereSelectionReport) {
                if ($this->titleButtonReport) {
                    $nameBtnReporte = $this->titleButtonReport;
                } else {
                    $nameBtnReporte = ExjText::__('Report selected') . " $strNameTopic";
                }
            }

            $menuReporte = ExjUI::NewBotonMenu($nameBtnReporte, 'exj-btn-rep', $itemsMenuReporte, 'Opciones Reporte');
            $menuReporte->setAction('rep_mnu', true);
            $menuReporte->requiereSelection = $this->requiereSelectionReport;

            $cfg->items[] = $menuReporte;
        }

        if ($this->isAccessHelp()) {
            $cfg->items[] = ExjUI::NewButtonHelp(ExjText::__('Ayuda de') . " " . $strNameTopic);
        }

        return $cfg;
    }

    /**
     * overwrited. Aplica configuración del grid
     *
     * @param object $cfg Config del Grid pasado por referencia
     */
    protected function applyUICfgGrid(&$cfg) {
        
    }

    /**
     * Fija el ancho del Grid
     *
     * @param int $width
     */
    public function fixGridWidth($width, $forceFit = false) {
        $this->_width = $width;
        $this->getView()->setForceFit($forceFit);
        return $this;
    }

    /**
     * Fija el alto del Grid
     *
     * @param int $height
     */
    public function fixGridHeight($height) {
        $this->height = $height;
        return $this;
    }

    public function fixUseLockingGrid($useLockingGrid = true) {
        $this->_useLockingGrid = $useLockingGrid;
        return $this;
    }

    public function fixGridViewForceFit($forceFit = false) {
        $this->getView()->setForceFit($forceFit);
        return $this;
    }

    
    public function getView() {
        if (!$this->_gridView) {
            $this->_gridView = new ExjUIGridView();
        }

        return $this->_gridView;
    }

    public function getTitleList() {
        return ExjText::_($this->titleList);
    }

    private function _buildCfgGrid($forceFit = true) {
        if (!$this->_cfgGrid) {
            $this->_cfgGrid = new stdClass();
        }

        if ($this->_fixTitleToToolbar) {
            // prop oculta
            $this->_cfgGrid->exjTitleHidden = $this->getTitleList();
        } else {
            $this->_cfgGrid->title = $this->getTitleList();
        }

        $this->_cfgGrid->trackMouseOver = true;
        $this->_cfgGrid->stripeRows = false;
        $this->_cfgGrid->height = $this->height;

        if ($this->bodyCssClass) {
            $this->_cfgGrid->bodyCssClass = $this->bodyCssClass;
        }


        if ($this->_width) {
            $this->_cfgGrid->width = $this->_width;
        }

        if ($this->_childNameComponent) {
            $this->_cfgGrid->childOption = $this->_childNameComponent;
        }

        //	echo "<br/>this->_childNameEditable: $this->_childNameEditable Clase: " . get_class($this);
        if ($this->_childNameEditable) {
            $this->_cfgGrid->childEditable = $this->_childNameEditable;
        }
        if ($this->_childKeyEditable) {
            $this->_cfgGrid->childKey = $this->_childKeyEditable;
        }
        if ($this->_childNameList) {
            $this->_cfgGrid->childList = $this->_childNameList;
        }
        if ($this->_childParentEditable) {
            $this->_cfgGrid->parentEditable = $this->_childParentEditable;
        } elseif (isset($this->_cfgGrid->childEditable) && $this->_cfgGrid->childEditable) {
            // registramos el padre de la lista hija xxx
            $nameComponent = $this->_childNameComponent;
            if (!$nameComponent) {
                $nameComponent = '';
            }
            $this->readNameComponent($nameComponent);

            // echo '<br/>xxx 1. OK '. __METHOD__ . " childEditable: ". $this->_cfgGrid->childEditable." nameComponent: $nameComponent";
            
            // echo '<br/>2. OK '. __METHOD__;
            $ClassEditableChild = ExjUtil::GetNameClassModelChildEditableFromName(
                $this->_cfgGrid->childEditable
            );

            $objEditableChildModel = new $ClassEditableChild(false);
            $this->_cfgGrid->parentEditable = $objEditableChildModel->getNameEditableModelParent();
        }

        $this->_cfgGrid->loadMask = new stdClass();
        $this->_cfgGrid->loadMask->msg = ExjText::__('Cargando') . ' ' . ExjText::_($this->nameTopics) . "...";

        $this->_cfgGrid->columns = $this->to_ui_columns();

        /* view */
        $this->_cfgGrid->viewConfig = $this->getView();
        if (!$this->_cfgGrid->viewConfig->isDefinedProp('forceFit')) {
            $this->_cfgGrid->viewConfig->setForceFit($forceFit);
        }        

        $bbar = $this->to_ui_cfgPagingToolbar();
        if ($bbar) {
            $this->_cfgGrid->bbar = $bbar;
        }

        $tbar = $this->to_ui_cfgTopToolbar();
        if ($tbar) {
            $this->_cfgGrid->tbar = $tbar;
        }

        if ($this->_nameFieldId) {
            $this->_cfgGrid->exjNameFieldId = $this->_nameFieldId;
        }

        if ($this->_hideHeadersToGrid !== null) {
            $this->_cfgGrid->hideHeaders = $this->_hideHeadersToGrid;
            /*
              if ($this->_hideHeadersToGrid){
              $this->_cfgGrid->columnLines = true;
              }
             */
        }

        /*
          $widthGrid = 0;
          foreach ($this->_cfgGrid->columns as $col) {
          if (isset($col->hidden) && $col->hidden) {
          continue;
          }

          if (!isset($col->width)) {
          echo "columna no tiene width<br/>";
          print_r($col);
          continue;
          }

          $widthGrid += $col->width;
          }

          if ($widthGrid > 33) {
          $widthGrid += 1;
          $this->_cfgGrid->width = $widthGrid;
          }
          else {
          echo "<br/>Grid tiene poco ancho: $widthGrid";
          }
         */


        //	print_r($this->_cfgGrid);	


        $this->applyUICfgGrid($this->_cfgGrid);

        return $this->_cfgGrid;
    }

    public function setBaseParams($objParams) {
        $this->getStore()->applyBaseParams($objParams);
        return $this;
    }

    public function setBaseParamCriteria($param, $value) {
        if (!$param) {
            return false;
        }

        $criteria = $this->getStore()->getBaseParam('criteria');
        if (empty($criteria) || !is_object($criteria)) {
            $criteria = new stdClass();
            $this->getStore()->setBaseParam('criteria', $criteria);
        }

        $criteria->$param = $value;

        return $this;
    }

    public function getBaseParamsCriteriaClone() {
        $params=$this->getBaseParamsCriteria();
        if (!$params) {
            return $params;
        }

        return (clone $params);
    }

    public function getBaseParamsCriteria() {
        return $this->getStore()->getBaseParam('criteria', null);
    }

    public function setBaseParam($param, $value) {
        if (!$param) {
            return $this;
        }

        if ($param == 'criteria' && is_string($value)) {
            $paramCriteria = json_decode(stripslashes($value));
            $vars = get_object_vars($paramCriteria);
            foreach ($vars as $nameCriteria => $valueCriteria) {
                $this->getStore()->setBaseParam($nameCriteria, $valueCriteria);
            }
        } else {
            $this->getStore()->setBaseParam($param, $value);
        }

        return $this;
    }

    /**
     * Obtiene parámetros del modelo de lista
     *
     * @return object. Si no se han seteado parámetros retorna null
     */
    public function getBaseParams() {
        return $this->getStore()->getBaseParams();
    }

    /**
     * Devuelve parametro desde baseParams
     *
     * @param string $nameParam
     * @param mixed $default
     * @return mixed
     */
    public function getParamFromBaseParams($nameParam, $default = '') {
        return $this->getStore()->getBaseParam($nameParam, $default);
    }

    public function getBaseParam($nameParam, $default = '') {
        return $this->getParamFromBaseParams($nameParam, $default);
    }

    public function isModeLocal() {
        return ($this->_mode == $this->_MODE_LOCAL);
    }

    public function isModeRemote() {
        if (!$this->_mode) {
            return true;
        }
        return ($this->_mode == $this->_MODE_REMOTE);
    }

    public function getStore() {
        return $this->_store;
    }

    public function to_ui_cfgStore() {
        $store = $this->getStore();

        if ($this->isModeLocal()) {
            $store->setRemoteSort(false);
        }
        elseif (!$store->isSetRemoteSort()) {
            $store->setRemoteSort(true);
        }

        $store->setIdProperty($this->fieldKey);
        $store->setFields($this->to_ui_fields());

        if ($bp = $this->getBaseParams()) {
            $store->applyBaseParams($this->rendererBaseParamsToUI($bp));
        }

      //  print_r($store);

        return $store;
    }

    protected function rendererBaseParamsToUI($data){
        if (empty($data)) {
            return new stdClass();
        }

        $bpUI = new stdClass();
        foreach ($data as $param => $value) {
            if (!$param) {
                continue;
            }

            if ($value && (is_object($value) || is_array($value))) {
                $bpUI->$param = json_encode($value);
            }
            else{
                $bpUI->$param = $value;
            }
        }

        return $bpUI;
    }

    public function setData($items, $total = -1) {
        if ($total < 0) {
            $total = count($items);
        }

        $this->getResponse()->setDataTopics($items, $total);
        return $this;
    }

    public function setResponse(ExjResponse $response) {
        $this->_data = $response;
        return $this;
    }

    /**
     * Retorna instancia de clase ExjResponse
     *
     * @return ExjResponse
     */
    public function &getResponse() {
        if (!$this->_data) {
            $this->_data = new ExjResponse();
        }

        return $this->_data;
    }

    public function getData() {
        $response = $this->_data;
        if (!$response) {
            return $response;
        }

        return $response->toObject();
    }

    /**
     * Fija el modo de selección del grid, en forma simple slección, este modo de selección es por defecto
     *
     * @param bool $singleSelect
     * @param object $paramsExtras No es requerido
     */
    public function fixSelModelRowSelectionModel($singleSelect = true, $paramsExtras = null) {
        if (!$paramsExtras) {
            $paramsExtras = new stdClass();
        }
        $paramsExtras->singleSelect = $singleSelect;

        $this->_fixSelModel('RowSelectionModel', $paramsExtras);
    }

    /**
     * Fija el modo de selección del grid, para multiple selección de filas
     *
     * @param bool $checkOnly
     * @param bool $singleSelect
     * @param object $paramsExtras
     */
    public function fixSelModelCheckboxSelectionModel($checkOnly = false, $singleSelect = false, $paramsExtras = null) {
        if (!$paramsExtras) {
            $paramsExtras = new stdClass();
        }
        $paramsExtras->checkOnly = $checkOnly;
        $paramsExtras->singleSelect = $singleSelect;

        $this->_fixSelModel('CheckboxSelectionModel', $paramsExtras);
    }

    public function fixSelModelCellSelectionModel($paramsExtras = null) {
        if (!$paramsExtras) {
            $paramsExtras = new stdClass();
        }

        $this->_fixSelModel('CellSelectionModel', $paramsExtras);
    }

    private function _fixSelModel($type, $params) {
        if (!$this->_cfgSelModel) {
            $this->_cfgSelModel = new stdClass();
        }

        $this->_cfgSelModel->type = $type;
        $this->_cfgSelModel->params = $params;
    }

    public function getNameClassGrid() {
        return $this->_nameClassGrid;
    }

    public function fixGridEditorGridPanel($clicksToEdit = 1, $autoEncode = false, $forceValidation = false) {
        $this->_nameClassGrid = self::CLASSUI_EDITORGRIDPANEL;

        $this->_cfgGrid->clicksToEdit = $clicksToEdit;
        $this->_cfgGrid->autoEncode = $autoEncode;
        $this->_cfgGrid->forceValidation = $forceValidation;

        return $this;
    }

    public function isGridEditor() {
        return ($this->_nameClassGrid == self::CLASSUI_EDITORGRIDPANEL);
    }

    /**
     * Fija al grid para presentar como HTML
     *
     * @param bool $forceIfEditor Defecto false
     * @return bool
     */
    public function fixGridHTMLGridPanel($forceIfEditor = false) {
        if (!$forceIfEditor && $this->isGridEditor()) {
            return false;
        }

        $this->_nameClassGrid = self::CLASSUI_GRIDHTMLPANEL;

        return true;
    }

    /**
     * overwrite. Registro de Columnas bloqueadas.
     *
     * @param array $dataIndexes Por referencia
     */
    protected function listFixColsLocking(&$dataIndexes) {
        
    }

    /**
     * Devuelve un objeto para ser usado en la UI.
     * Para store y grid
     *
     * @return object
     */
    public function to_ui() {
        $ui = new stdClass();

        $namesIndexesLocked = array();
        $this->listFixColsLocking($namesIndexesLocked);
        if (count($namesIndexesLocked) > 0) {
            $this->fixUseLockingGrid();
            foreach ($namesIndexesLocked as $nameIndexeLocked) {
                $colToLocked = $this->getColFromDataIndex($nameIndexeLocked);
                if ($colToLocked) {
                    $colToLocked->locked = true;
                    // $colToLocked->id = $nameIndexeLocked;
                }
            }
        }

        if ($this->_useLockingGrid) {
            $ui->useLockingGrid = true;
        }


//		$ui->mode = $this->_mode;
        $ui->isModeRemote = $this->isModeRemote();
        $ui->isModeLocal = $this->isModeLocal();

        $ui->pageSize = $this->pageSize;
        $ui->sortField = $this->defaultSort;
        $ui->sortDir = $this->_sortDir;
        $ui->cfgStore = $this->to_ui_cfgStore();
        // print_r($ui->cfgStore);
        $ui->cfgSelModel = $this->_cfgSelModel;
        $ui->nameClassGrid = $this->getNameClassGrid();

        $this->_buildCfgGrid();
        $ui->cfgGrid = $this->_cfgGrid;

        if ($ui->nameClassGrid) {
            $ui->canCloneColumns = true;
        }



        $ui->data = $this->getData();

        // adicion de modelos de listas secundarios
        $this->_buildListsSecundaries($ui);

        $this->renderUIListModel($ui);
        // print_r($ui);
        return $ui;
    }

    /**
     * overwrite. Renderiza el objeto pasado a la UI
     *
     * @param object $ui
     */
    protected function renderUIListModel(&$ui) {
        
    }

    /**
     * Devuelve el alias del campo pasado por parametro
     *
     * @param string $nameField
     * @return string
     */
    private function _getAliasFromFields($nameField) {
        $alias = '';

        if (isset($this->_fields[$nameField])) {
            $f = $this->_fields[$nameField];
            $alias = trim($f->alias);
        }

        return $alias;
    }

}

?>