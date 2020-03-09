<?php

/**
 * @class AppGlobalModel
 * 
 */
class AppGlobalModel extends ExjModel {

    function AppGlobalModel() {
        // $this->setTitleDefault("DATOS GLOBALES");	
    }

    public static function GetDataInfoUser() {
        return AppGlobalData::GetDataUser();
    }

    public static function getDataInfoFile() {
        $infoFile = new stdClass();

        $infoFile->charsMaxNameFile = ExjHandlerFile::GetCharsMaxNameFile();
        $infoFile->maxSizeUpload = ExjHandlerFileUpload::getMaxSizeFileUpload();

        return $infoFile;
    }

    /**
     * Constantes globales del sistema, para la UI
     *
     */
    public static function getDataConstantesUI() {
        $constantes = new stdClass();

        // NOMBRE DE LA APP
        $constantes->_EXJ_APP_TITULO = Exj::GetTitleApp();
        $constantes->_EXJ_APP_COMPANIA = Exj::GetTitleCompanyApp();

        // Manejo de errores
        $constantes->_EXJ_TIPO_ERROR_NINGUNO = ExjError::TIPO_ERROR_NINGUNO;
        $constantes->_EXJ_TIPO_ERROR_DESCONOCIDO = ExjError::TIPO_ERROR_DESCONOCIDO;
        $constantes->_EXJ_TIPO_ERROR_DATABASE = ExjError::TIPO_ERROR_DATABASE;
        $constantes->_EXJ_TIPO_ERROR_FILE = ExjError::TIPO_ERROR_FILE;
        $constantes->_EXJ_TIPO_ERROR_USERACCESS = ExjError::TIPO_ERROR_USERACCESS;
        $constantes->_EXJ_TIPO_ERROR_SERVICIOFTP = ExjError::TIPO_ERROR_SERVICIOFTP;
        $constantes->_EXJ_TIPO_ERROR_VALIDINGDATA = ExjError::TIPO_ERROR_VALIDINGDATA;

        // ESTADOS DE LOS TIPO DE MENSAJES SERVIDOS AL CLIENTE
        $constantes->_EXJ_MSG_TIPO_NINGUNO = Exj::MSG_TIPO_NINGUNO;
        $constantes->_EXJ_MSG_TIPO_INFO = Exj::MSG_TIPO_INFO;
        $constantes->_EXJ_MSG_TIPO_ERROR = Exj::MSG_TIPO_ERROR;
        $constantes->_EXJ_MSG_TIPO_WARNING = Exj::MSG_TIPO_WARNING;
        $constantes->_EXJ_MSG_TIPO_NOTIFY = Exj::MSG_TIPO_NOTIFY;
        $constantes->_EXJ_MSG_TIPO_HTML = Exj::MSG_TIPO_HTML;

        $constantes->_EXJ_ESTADO_OK = Exj::ESTADO_OK;
        $constantes->_EXJ_ESTADO_ERROR = Exj::ESTADO_ERROR;

        $constantes->uriBase = Exj::GetURIBase();

        return $constantes;
    }

    public static function getDataMenusMain() {
        return self::getDataMenus(ExjMenu::MENU_TYPE_APP);
    }

    public static function getDataMenusOpcGen() {
        return self::getDataMenus(ExjMenu::MENU_TYPE_OPCGEN_APP);
    }

    /**
     * Obtiene items para los módulos principales de la aplicación
     *
     * @return array
     */
    static function GetItemsModulesMains() {
        $itemsModulesMains = array();

        // 	$menusAlls = AppGlobalModel::getDataMenus(ExjMenu::MENU_TYPE_APP);
        //	$modBuzonEntrada = new ModuleMainUI(Exj::ND_COMPONENT_BUZONENTRADA, $menusAlls);
        //	$modBuzonEntrada->isActiveTabDefault = true;

        /*
          $modPrediosUrbanos = new ModuleMainUI(Exj::ND_COMPONENT_PREDIOSURBANOS);
          $modPatentesMunicipales = new ModuleMainUI(Exj::ND_COMPONENT_PATENTESMUNICIPALES);
          $modContribucionMejoras = new ModuleMainUI(Exj::ND_COMPONENT_CONTRUBUCIONMEJORAS);
          $modTitulosCredito = new ModuleMainUI(Exj::ND_COMPONENT_TITULOSCREDITO);

          $itemsModulesMains[] = $modPrediosUrbanos->toUI();
          $itemsModulesMains[] = $modPatentesMunicipales->toUI();
          $itemsModulesMains[] = $modContribucionMejoras->toUI();
          $itemsModulesMains[] = $modTitulosCredito->toUI();
         */

        //	$itemsModulesMains[] = $modBuzonEntrada->toUI();

        return $itemsModulesMains;
    }

    public static function GetItemsCmpAutoLoad() {
        $itemsCmpAutoLoad = array();

        $menusAlls = AppGlobalModel::getDataMenus(ExjMenu::MENU_TYPE_APP);

        $modulesUI = array();
//        $modulesUI[] = "Exj.ui.modules.RepFacturas";

        foreach ($modulesUI as $moduleUI) {
            $objModule = null;
            ModuleMainUI::LoadModuleInMenus($menusAlls->items, $moduleUI, $objModule);
            if ($objModule) {
                $itemsCmpAutoLoad[] = $objModule;
            }
        }

        //	print_r($itemsCmpAutoLoad);

        return $itemsCmpAutoLoad;
    }

    static function getDataMenus($menutype, $idPanelToRender = '') {
        return AppGlobalData::getDataMenus($menutype, $idPanelToRender);
    }

    static function getDataBrowsers() {
        return AppGlobalDataBrowsers::Get();
    }

    public static function ChangeOffice(ExjResponse &$response, $idOfficeNew, $id_sys_user = 0) {
        return AppGlobalData::ChangeOffice($response, $idOfficeNew, $id_sys_user);
    }

    static function GetGeocode(ExjResponse &$response, $address) {
        $response->setMsgError("GetGeocode. address: $address. En construcción!");
    }

}

class ModuleMainUI extends ExjObject {
    public $iconCls = 'exj-icon-app';
    public $layout = 'fit';
    public $title = '';
    public $closable = false;
    public $items = null;
    public $dataModule = null;
    public $isActiveTabDefault = false;
    private $_nameComponent = '';
    private $_menusAlls = null;

    public function __construct($moduleApp, $menusAlls = null) {
        $this->_nameComponent = $moduleApp;
        $this->_menusAlls = $menusAlls;

        switch ($moduleApp) {
            case Exj::ND_COMPONENT_BUZONENTRADA:
                $this->title = 'Buzón de Entrada';
                // $this->iconCls = 'app-btn-view';
                break;

            case Exj::ND_COMPONENT_CONTRUBUCIONMEJORAS:
                $this->title = 'Contribución de Mejoras';
                $this->iconCls = 'app-btn-contmejoras';
                break;

            case Exj::ND_COMPONENT_PATENTESMUNICIPALES:
                $this->title = 'Patentes Municipales';
                $this->iconCls = 'app-btn-patentes';
                break;

            case Exj::ND_COMPONENT_PREDIOSURBANOS:
                $this->title = 'Predios Urbanos';
                $this->iconCls = 'app-btn-urban';
                break;

            case Exj::ND_COMPONENT_TITULOSCREDITO:
                $this->title = 'Títulos de Crédito';
                $this->iconCls = 'app-btn-titcred';
                break;

            default:
                $this->title = 'No Implementado';
                break;
        }

        $this->_buildContentModules();
    }

    public function getIdPanelMain() {
        return 'pnlMain_' . $this->_nameComponent;
    }

    private function _buildContentModules() {
        $this->items = array();

        $itemMain = new stdClass();
        $itemMain->id = $this->getIdPanelMain();
        $itemMain->isVUContentMain = true;
        $itemMain->layout = 'fit';
        $itemMain->bodyCfg = new stdClass();
        $itemMain->bodyCfg->align = 'center';


        $this->_loadMenuMain($itemMain);
        // $this->_addPaginaInicio($itemMain);


        $this->items[] = $itemMain;
    }

    private function _addPaginaInicio(&$itemMain) {
        //	$pnlInicio = ExjUI::NewPanel($this->title, null, 'fit');

        $html = array();
        $html[] = "<div class='vu-mod-main-cnt-title'>$this->title</div>";

        $html[] = ExjBuildHtml::CreateImg(
            ExjResource::GetUriLogoFrontEndDefault()
        )->addAttr('unselectable', 'on')->toHtml();

        $html = implode('<br/>', $html);
        $html = '<div align="center">' . $html . "</div>";

        $pnlInicio = ExjUI::NewLabelUI(null, $html);

        $itemMain->items = $pnlInicio;
    }

    public static function LoadModuleInMenus($itemsMenus, $nameModule, &$objModule) {
        foreach ($itemsMenus as $itemMenu) {
            if (isset($itemMenu->nameModule) && $itemMenu->nameModule == $nameModule) {
                $objModule = $itemMenu;
                break;
            }

            if (isset($itemMenu->children) && $itemMenu->children && is_array($itemMenu->children)) {
                self::LoadModuleInMenus($itemMenu->children, $nameModule, $objModule);
            }
        }

        return ($objModule ? true : false);
    }

    private function _loadMenuMain(&$itemMain) {
        if (!$this->_menusAlls) {
            $this->_menusAlls = AppGlobalModel::getDataMenus(ExjMenu::MENU_TYPE_APP);
        }

        // Exj.ui.modules.RepFacturas
        $nameModule = 'Exj.ui.modules.' . $this->_nameComponent;

        // echo $this->getIdPanelMain();
        // print_r($this->_menusAlls);

        $objModule = null;
        self::LoadModuleInMenus($this->_menusAlls->items, $nameModule, $objModule);

        if (!$objModule) {
            return;
        }

        // print_r($objModule);
        $this->iconCls = $objModule->iconCls;
        if (!$this->title) {
            $this->title = $objModule->page_title;
        }

        if (isset($objModule->access)) {
            if (!isset($objModule->access->isAccessModule) || !$objModule->access->isAccessModule) {
                return;
            }
        }

//		print_r($objModule->access);

        $this->dataModule = new stdClass();
        $this->dataModule->id = $objModule->id;
        $this->dataModule->nameModule = $objModule->nameModule;
        $this->dataModule->nameComponent = $objModule->access->moduleName;
        $this->dataModule->access = $objModule->access;
        $this->dataModule->page_title = $objModule->page_title;
        $this->dataModule->iconCls = $this->iconCls;
    }

    public function toUI() {
        $ui = $this->toObject();

        $ui->isVUModuleMain = true;
        $ui->id = 'tabMain_' . $this->_nameComponent;

        return $ui;
    }

}

?>