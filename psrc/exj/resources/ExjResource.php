<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Recursos de la Aplicación
 *
 */
class ExjResource {

    const PREFIX_FILEJS_PACK = 'pack_';
    const DIR_ROOT_APP = 'app';
    const DIR_FILES_JS = 'app/public/js';
    const DIR_FILES_JS_ALL = 'app/public/js/all';

    // valor numérico
    // const SEC_DEPLOY_JS = 201;

    protected static $pathDirBase='';
    private static $_mapPrefixesCmp = null;

    const PREFIXES_COMPS = array(
        'exj_',
        'app_',
    );

    /**
     * Devuelve un array de los componentes que tienen helpers
     *
     * @return array
     */
    protected static function GetComponentsHelpers() {
        $componentsHelpers = array();

        $componentsHelpers[] = 'app_personas';
        $componentsHelpers[] = 'app_loc_cities';
        $componentsHelpers[] = 'app_companias';
        $componentsHelpers[] = 'app_clientes';
        $componentsHelpers[] = 'app_proveedors';
        $componentsHelpers[] = 'app_inventarios';
        $componentsHelpers[] = 'app_identificacion_tipos';
        $componentsHelpers[] = 'app_ventas';
        $componentsHelpers[] = 'app_ven_cuotas';

        // pack_ventas.helper.js
        
        return $componentsHelpers;
    }

    public static function GetPathHelperPublicJs(){
        return self::GetPathBase().'/app/' . self::GetPathRelativeHelperPublicJs();
    }

    public static function GetPathRelativeHelperPublicJs(){
        return 'public/js/' . self::PREFIX_FILEJS_PACK . 'web.helper.js';
    }

    static function getTemplateJavaScript($src) {
        $script = '<script language="javascript" type="text/javascript" src="' . $src . '">';
        $script .= '</script>';
        $script .= "\n";
        return $script;
    }

    protected static function GetURIFilesJs() {
        $value = JURI::base();
        $value .= self::DIR_FILES_JS;

        return $value;
    }

    public static function GetPathBase() {
        if (!self::$pathDirBase) {
            self::$pathDirBase = JPATH_BASE;
            self::$pathDirBase = str_replace('\\', '/', self::$pathDirBase);
        }

        return self::$pathDirBase;
    }

    protected static function GetPathDirFilesJs() {
        $value = self::GetPathBase();
        $value .= '/' . self::DIR_FILES_JS;

        return $value;
    }

    public static function GetURIBase() {
        $uri = JURI::base();
        return $uri;
    }

    public static function GetURIBaseApp() {
        $uri = self::GetURIBase();
        $uri .= self::DIR_ROOT_APP;

        return $uri;
    }

    public static function GetURIBaseExj() {
        $uri = self::GetURIBase();
        $uri .= self::GetDirRelativeProvider();

        return $uri;
    }

    public static function GetUriExtJs() {
        $uri = JURI::base();
        $uri .= "libraries/extjs/extjs340";

        return $uri;
    }

    static function buildSrcs($path, $namesFiles, $isModeDebugUI) {
        $srcs = array();
        foreach ($namesFiles as $nameFile) {
            // $nameFile = str_replace("//", '/', $nameFile);
            $src = '';
            if ($path) {
                $src .= $path . '/';
                ;
            }

            $src .= self::buildFileJs($nameFile);
            $srcs[] = $src;
        }

        return $srcs;
    }

    static function getSrcs_ExtJs($isModeDebugUI) {
        $namesFiles = array();
        $namesFiles[] = "adapter/ext/ext-base.js";
        $namesFiles[] = "ext-all.js";

        // note: fijar esto segun el lenguaje del usuario
        $namesFiles[] = "src/locale/ext-lang-es.js";

        return self::buildSrcs(self::GetUriExtJs(), $namesFiles, $isModeDebugUI);
    }

    static function getSrcs_CompBase($isModeDebugUI) {
        if (!$isModeDebugUI) {
            return array();
        }

        $namesFiles = array();

        $uriBase = self::GetURIBase();
        $uriBase .= self::GetDirRelativeResourcesJs();

        /*
          if ($isModeDebugUI) {
          $uriBase .= '/src';
          $namesFiles[] = "extend/TabCloseMenu";
          $namesFiles[] = "extend/Portal";
          $namesFiles[] = "extend/BubblePanel";
          $namesFiles[] = "extend/LinkButton";
          $namesFiles[] = "extend/ValidationTypes";
          $namesFiles[] = "extend/FileUploadField";
          }
         */
        $namesFiles[] = "extend/exj_extend-all";

        $namesFiles[] = "exj_base";
        $namesFiles[] = "exj_extends";
        $namesFiles[] = "exj_extend_layouts";
        $namesFiles[] = "exj_action_grid";
        $namesFiles[] = "exj_files";
        $namesFiles[] = "exj_mail";
        $namesFiles[] = "exj_override";
        $namesFiles[] = "exj_util";
        $namesFiles[] = "exj_main";

        $filesJs = self::buildSrcs($uriBase, $namesFiles, $isModeDebugUI);

        // adicionar config de la app
        $uriBase = self::GetURIBase() . 'app/web/common/js';
        $namesFiles = [
            "app_cfg"
        ];

        $filesAppJs = self::buildSrcs($uriBase, $namesFiles, $isModeDebugUI);
        if (!empty($filesAppJs)) {
            $filesJs = array_merge($filesJs, $filesAppJs);
        }

        return $filesJs;
    }

    // ExjResource::GetNameTemplateSys()
    private static $_NAMETMP='';
    public static function GetNameTemplateSys() {
        if (self::$_NAMETMP) {
            return self::$_NAMETMP;
        }

        $query = "SELECT tm.template FROM jos_templates_menu tm ORDER BY IF(tm.template LIKE 'sy%', 0, 1) LIMIT 1";

        $db = & JFactory::getDBO();
        $db->setQuery($query);
        self::$_NAMETMP = trim($db->loadResult());
        if (!self::$_NAMETMP) {
            self::$_NAMETMP = 'sy_nofound';
        }

        return self::$_NAMETMP;
    }

    // componentes desde menu, que son excluidos
    public static function GetArrayModulesMenuExcludes() {
        $query = "SELECT g.name 
 FROM jos_menu m INNER JOIN jos_groups g ON m.access = g.id 
 WHERE (m.menutype = 'mnu_app_main') and g.id >= 3 and m.published <= 0";

        $db = & JFactory::getDBO();
        $db->setQuery($query);
        return $db->loadResultArray();
    }

    public static function GetGroupsUsers() {
        $query = "SELECT aclg.id AS gid, aclg.name AS name_gid, Count(u.id) AS nro_usrs 
 FROM jos_core_acl_aro_groups aclg INNER JOIN jos_users u ON aclg.id = u.gid 
 WHERE (aclg.parent_id > 0) 
 GROUP BY aclg.id, aclg.name";

        $db = & JFactory::getDBO();
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function GetAliasGroupsUsers() {
        $items = self::GetGroupsUsers();
        if (empty($items)) {
            return $items;
        }

        foreach ($items as &$item) {
            $item->alias = self::GetAliasFromUserType($item->name_gid);
        }

        return $items;
    }

    public static function GetSecDeployJs() {
        $ver = trim(self::GetCfgExj()->versionApp);
        if (!$ver) {
            return 3;
        }

        $ver = intval(str_replace('.', '', $ver));
        if (is_nan($ver) || $ver <= 0) {
            $ver = 6;
        }

        return $ver;
    }

    public static function GetNameFileJsAppPack($usertype=''){
        if (!$usertype) {
            if ($usr = JFactory::getUser()) {
                $usertype = $usr->usertype;
            }
        }

        $value = self::PREFIX_FILEJS_PACK;
        // $value .= 'app_'.dechex(self::SEC_DEPLOY_JS).'_';
        $value .= 'app_'.dechex(self::GetSecDeployJs()).'_';
        $value .= self::GetAliasFromUserType($usertype);

        $value .= '.js';

        return $value;
    }

    public static function GetAliasFromUserType($usertype){
        $alias = trim(strtolower($usertype));
        if ($alias) {
            if (strpos($alias, ' ') > 0) {
                $partes = explode(' ', $alias);
                $alias = '';
                foreach ($partes as $parte) {
                    $alias .= substr($parte, 0, 1);
                }
            }
        }
        else {
            $alias = 'sa';
        }

        return $alias;
    }

    private static $_dirRelRes='';
    // ExjResource::GetDirRelativeResources()
    public static function GetDirRelativeResources() {
        if (self::$_dirRelRes) {
            return self::$_dirRelRes;
        }

        $dir = __DIR__;
        $dir = str_replace('\\', '/', $dir);
        $dir = substr($dir, strlen(self::GetPathBase())+1);
        self::$_dirRelRes = $dir;

        return self::$_dirRelRes;
    }

    private static $_pathDirProvider='';
    public static function GetPathDirProvider() {
        if (!self::$_pathDirProvider) {
            self::$_pathDirProvider = realpath(__DIR__ . '/../..');
            self::$_pathDirProvider = str_replace('\\', '/', self::$_pathDirProvider);
        }

        return self::$_pathDirProvider;
    }

    public static function GetDirRelativeProvider() {
        $dir = self::GetPathDirProvider();
        $dir = substr($dir, strlen(self::GetPathBase())+1);

        return $dir;
    }

    public static function GetDirRelativeResourcesJs() {
        return self::GetDirRelativeResources().'/js';
    }

    // xxxx
    public static function GetCfgMinify() {
        $itemsGids = self::GetAliasGroupsUsers();

        $cfgPack = new stdClass();
        $cfgPack->directoriesRoots = array(
            'app',
            self::GetDirRelativeResourcesJs(),
            self::GetDirRelativeProvider() . '/web'
        );

        $cfgPack->dirOutput = self::DIR_FILES_JS_ALL;
        $cfgPack->prefixPack = self::PREFIX_FILEJS_PACK;
        
        $cfgPack->dirsExclude = array(
            'exj_deploys', 'exj_components' , 'exj_component_tpls',
            'com_sfac_enquiry'
        );
        
        $cfgPack->modulesMainExcludesGen = self::GetArrayModulesMenuExcludes();
        foreach ($cfgPack->modulesMainExcludesGen as $moduleMainExcludeGen) {
            if (in_array($moduleMainExcludeGen, $cfgPack->dirsExclude)) {
                $key = array_search(
                    $moduleMainExcludeGen, $cfgPack->modulesMainExcludesGen
                );

                array_splice($cfgPack->modulesMainExcludesGen, $key, 1);
            }
        }

        /*
        $values = array();
        
        foreach ($itemsGids as $itemGid) {
            unset($itemGid->nro_usrs);
         
            $itemGid->outputFilePack = self::GetNameFileJsPackModules(
                $itemGid->name_gid
            );

            $itemGid->modulesMainExcludes = self::GetArrayModulesGidExcludes($itemGid->gid);

            foreach ($itemGid->modulesMainExcludes as $moduleMainExclude) {
                if (in_array($moduleMainExclude, $cfgPack->modulesMainExcludesGen)) {
                    $key = array_search($moduleMainExclude, $itemGid->modulesMainExcludes);
                    array_splice($itemGid->modulesMainExcludes, $key, 1);
                }
                elseif(in_array($moduleMainExclude, $cfgPack->dirsExclude)){
                    $key = array_search($moduleMainExclude, $itemGid->modulesMainExcludes);
                    array_splice($itemGid->modulesMainExcludes, $key, 1);
                }
            }

            $values[] = $itemGid;
        }

        $cfgPack->packs = $values;
        */

        return $cfgPack;
    }

    public static function GetDirAppWeb(){
        $dirProd = self::GetPathBase();
        $dirProd .= '/app/web';
        
        return $dirProd;
    }

    public static function GetDirExjWeb() {        
        return (self::GetPathDirProvider() . '/web');
    }


    // ExjResource::WriteFileCfgMinify();
    public static function WriteFileCfgMinify(){
        $pathFile = self::GetPathFileCfgMinify();
        $content = self::GetCfgMinify();

        $strJson = json_encode($content, JSON_PRETTY_PRINT);
        file_put_contents($pathFile, $strJson);
    }

    public static function GetPathFileCfgMinify(){
        return (self::GetPathBase().'/app-minify-cfg.json');
    }


    public static function GetArrayModulesGidExcludes($gid=0) {
        if (!$gid) {
            $user = & JFactory::getUser();
            if (!$user->id) {
                return $componetsApp;
            }

            $gid = $user->gid;
        }

        $sq_access = "SELECT grps.id AS gid
 FROM 
  jos_noixacl_rules rul INNER JOIN 
  jos_k2_categories k2c ON rul.axo_section = k2c.id INNER JOIN 
  jos_groups grps ON k2c.access = grps.id INNER JOIN 
  jos_core_acl_aro_groups aro_grp ON rul.aro_value = aro_grp.value 
 WHERE 
  (rul.aco_section = 'com_k2' AND k2c.published = 1 AND aro_grp.id = $gid) 
 GROUP BY grps.id";

        $query = "SELECT g.name AS mod_name 
 FROM jos_groups g LEFT JOIN ($sq_access) sq_access ON g.id = sq_access.gid 
 WHERE g.id >= 3 AND sq_access.gid IS NULL";

        $db = & JFactory::getDBO();
        $db->setQuery($query);
        return $db->loadResultArray();
    }

    /**
     * Obtiene los componentes de la aplicacion
     *
     * @return unknown
     */
    public static function GetComponetsApp($isModeDebugUI, $gid=0) {
        $componetsApp = array();

        if (!$gid) {
            $user = & JFactory::getUser();
            if (!$user->id) {
                return $componetsApp;
            }

            $gid = $user->gid;
        }

        $query = "SELECT 
  Count(rul.axo_value) AS num_rul, k2c.name AS mod_title, k2c.access, grps.name AS mod_name 
 FROM 
  jos_noixacl_rules rul INNER JOIN 
  jos_k2_categories k2c ON rul.axo_section = k2c.id INNER JOIN 
  jos_groups grps ON k2c.access = grps.id INNER JOIN 
  jos_core_acl_aro_groups aro_grp ON rul.aro_value = aro_grp.value 
 WHERE 
  rul.aco_section = 'com_k2' AND k2c.published = 1 AND aro_grp.id = $gid 
 GROUP BY k2c.name, k2c.access, grps.name
 ORDER BY k2c.access";

        $db = & JFactory::getDBO();
        $db->setQuery($query);
        $moduleList = $db->loadObjectList();

        foreach ($moduleList as $module) {
            if (strtolower($module->mod_name) == 'registered') {
                continue;
            }

            $componetApp = new stdClass();
            $componetApp->nameComponent = self::getNameComponent($module->mod_name);
            $componetApp->titleComponent = $module->mod_title;
            $componetApp->nameFileJs = self::GetNameFileJsFromComp(
                $componetApp->nameComponent, $isModeDebugUI
            );

            if (!$componetApp->nameFileJs) {
                continue;
            }

            $componetsApp[] = $componetApp;
        }

        return $componetsApp;
    }

    static function getNameComponent($nameComp) {
        $nameComp = trim($nameComp);
        return str_replace("mod_t", "com_app", $nameComp);
    }

    static function pathInfoFileDir($pathFile, &$dirName, &$filename, &$extension) {
        $partes = pathinfo($pathFile);

        $dirName = $partes['dirname'];
//		$baseName = $partes['basename'];
        $extension = '';
        if (isset($partes['extension'])) {
            $extension = $partes['extension'];
        }

        $filename = $partes['filename'];

        // $extension = strtolower($extension);
    }

    static function buildFileJs($nameFile, $isModeDebugUI = null) {
        $extFile = '';
        $dirName = '';
        $onlyName = '';

        if ($isModeDebugUI === null) {
            $isModeDebugUI = self::IsModeDebugUI();
        }

        //	echo "<br/>nameFile: $nameFile";
        self::pathInfoFileDir($nameFile, $dirName, $onlyName, $extFile);

        //	echo " dirName: $dirName onlyName: $onlyName";

        if (!$extFile) {
            $extFile = 'js';
        }

        if (strlen($onlyName) >= 4) {
            $prefix = substr($onlyName, 0, 4);
            switch ($prefix) {
                case 'ext-':
                    if ($isModeDebugUI) {
                        $onlyName .= '-debug';
                    }
                    break;

                case 'exj_':
                case 'exj-':
                    if (!$isModeDebugUI) {
                        $onlyName = self::PREFIX_FILEJS_PACK . $onlyName;
                    }
                    break;

                default:
                    $pos = strpos($onlyName, '.main');
                    if ($pos !== false) {
                        if (!$isModeDebugUI) {
                            if ($onlyName != 'deploys.main') {
                                $onlyName = self::PREFIX_FILEJS_PACK . $onlyName;
                            }
                        }
                    } else {
                        $pos = strpos($onlyName, '.helper');
                        if ($pos !== false) {
                            if (!$isModeDebugUI) {
                                if ($onlyName != 'deploys.helper') {
                                    $onlyName = self::PREFIX_FILEJS_PACK . $onlyName;
                                }
                            }
                        }
                    }

                    break;
            }
        }

        if ($dirName == '.' || $dirName == '..') {
            $dirName = '';
        }
        // xxx
        $pathFileJs = $dirName;
        if ($pathFileJs) {
            $pathFileJs .= '/';
        }

        $pathFileJs .= $onlyName . '.' . $extFile;
        // echo " <b>pathFileJs</b>: $pathFileJs";
        return $pathFileJs;
    }

    private static function _GetNameFileFromNameGroup($nameGrp){
        $nameFileJs = '';
        if (strlen($nameGrp) > 3) {
            $posIni = false;

            if (strpos($nameGrp, ExjObject::PREFIX_COMP_FRAMEWORK) === 0) {
                $posIni = strlen(ExjObject::PREFIX_COMP_FRAMEWORK);
            }
            elseif(strpos($nameGrp, ExjObject::PREFIX_COMP_APP) === 0){
                $posIni = strlen(ExjObject::PREFIX_COMP_APP);
            }

            if ($posIni === false) {
                $posIni = strrpos($nameGrp, '_');
                if ($posIni !== false) {
                    $posIni += 1;
                }
            }

            if ($posIni !== false) {
                $nameFileJs = substr($nameGrp, $posIni);
                $nameFileJs = ltrim($nameFileJs, '_');
            }
        }

        if (!$nameFileJs) {
            $nameFileJs = $nameGrp;
        }

        return $nameFileJs;
    }

    // ExjResource::GetNameFileJsFromComp()
    public static function GetNameFileJsFromComp($nameComp, $isModeDebugUI) {
        $nameFileJs = '';
        if (strlen($nameComp) < 6) {
            $nameFileJs = $nameComp;
        } else {
            $startOffset = 0;

            $mapPrefixes = self::GetMapPrefixesComps();
            foreach ($mapPrefixes as $prefix => $len) {
                if (strpos($nameComp, $prefix) === 0) {
                    $startOffset = $len;
                    break;
                }
            }

            $nameFileJs = substr($nameComp, $startOffset);
        }
        
        $nameFileJs = strtolower($nameFileJs);
        $nameFileJs .= '.main.js';

        $nameFileJs = self::buildFileJs($nameFileJs, $isModeDebugUI);

        return $nameFileJs;
    }

    /*
    public static function GetNameFileHelperJs($nameComp) {
        $nameFileJs = '';
        if (strlen($nameComp) < 6) {
            $nameFileJs = $nameComp;
        } else {
            $startOffset = 9;

            $subfijo = substr($nameComp, 0, 7);
            if ($subfijo == 'com_exj') {
                $startOffset = 8;
            }

            $nameFileJs = substr($nameComp, $startOffset);
        }
        $nameFileJs = strtolower($nameFileJs);
        $nameFileJs .= '.helper.js';

        $nameFileJs = self::buildFileJs($nameFileJs);

        return $nameFileJs;
    }
    */

    // ExjResource::ReBuildAllFilesJsAppPack()
    public static function ReBuildAllFilesJsAppPack(){
        $items = self::GetGroupsUsers();
        $nameFilesOut = array();

        foreach ($items as $item) {
            $nameFile = self::GetNameFileJsAppPack($item->name_gid);
            $fullPath = self::GetPathDirFilesJs().'/'. $nameFile;
            self::BuildFileJsAppPack($fullPath, $item->gid);

            $nameFilesOut[] = $nameFile;
        }

        return $nameFilesOut;
    }

    private static function BuildFileJsAppPack($fullPathOut, $gid=0){
        // echo "<br>BuildFileJsAppPack. $fullPathOut";

        $isModeDebugUI = false;
        // echo "construir file: $nameFile";
        // archivos js desde permisos
        // bbbbb
        $componetsApp = self::GetComponetsApp($isModeDebugUI, $gid);
        // print_r($componetsApp);

        $filesJoins = array(
            self::PREFIX_FILEJS_PACK.'exj_extend-all.js',
            self::PREFIX_FILEJS_PACK.'exj_base.js',
            self::PREFIX_FILEJS_PACK.'exj_extends.js',
            self::PREFIX_FILEJS_PACK.'exj_extend_layouts.js',
            self::PREFIX_FILEJS_PACK.'exj_action_grid.js',
            self::PREFIX_FILEJS_PACK.'exj_files.js',
            self::PREFIX_FILEJS_PACK.'exj_mail.js',
            self::PREFIX_FILEJS_PACK.'exj_override.js',
            self::PREFIX_FILEJS_PACK.'exj_util.js',            
            self::PREFIX_FILEJS_PACK.'exj_main.js',
            self::PREFIX_FILEJS_PACK.'app_cfg.js'
        );

        foreach ($componetsApp as $componetApp) {
            $nameComponent = $componetApp->nameComponent;
            $fileJsPack = $componetApp->nameFileJs;
            $pathFile = self::DIR_FILES_JS_ALL . '/' . $fileJsPack;
            if (!file_exists($pathFile)) {
                // echo "\nNO EXISTE: ". $pathFile;
                continue;
            }

            if (!in_array($fileJsPack, $filesJoins)) {
                $filesJoins[] = $fileJsPack;
            }

            // echo "\npathFile: ". $pathFile;
        }

        // leer desde disco los helpers, tenga o no permiso
        $filesDir = self::GetFilesJsFromDir(self::DIR_FILES_JS_ALL, $filesJoins);
        foreach ($filesDir as $fileDir) {
            if (stripos($fileDir, '.helper.') > 0) {
                $filesJoins[] = $fileDir;
                // echo "<br>ADD FILE HELPER: $fileDir";
                continue;
            }

            $isMain = strpos($fileDir, '.main.') > 0;
            if ($isMain) {
                continue;
            }

            if (in_array($fileDir, $filesJoins)) {
                continue;
            }

            // echo "<br>INCLUYENDO extra: $fileDir";
            $filesJoins[] = $fileDir;
        }

        // echo "\nfilesJoins: ". print_r($filesJoins, true);
        $codesJs = "/* GymCloud.- Autor: Byron V. Córdova Mora */\n";
        foreach ($filesJoins as $fileJoin) {
            $pathFile = self::DIR_FILES_JS_ALL . '/' . $fileJoin;

            $codeJs = file_get_contents($pathFile);
            if (!$codeJs) {
                continue;
            }

            $codesJs .= $codeJs;
        }

        if (file_put_contents($fullPathOut, $codesJs)) {
            self::DeleteFileSimilar($fullPathOut);
        }
    }

    public static function GetFilesJsDevFromDir($pathDir, $excludesDirBase=null){
        $out = array();
        if (is_array($pathDir)) {
            foreach ($pathDir as $itemPathDir) {
                $partes = self::GetFilesJsDevFromDir($itemPathDir, $excludesDirBase);
                if (!empty($partes)) {
                    $out = array_merge($out, $partes);
                }
            }

            return $out;
        }


        $files = scandir($pathDir);
        $out = array();
        if (empty($files)) {
            return $out;
        }

        foreach ($files as $nameFile) {
            if ($nameFile == '.' || $nameFile == '..') {
                continue;
            }

            $pathFile = $pathDir.'/'.$nameFile;
            if (is_file($pathFile)) {
                if (strpos($nameFile, self::PREFIX_FILEJS_PACK)===0) {
                    continue;
                }

                if (preg_match('/\.js$/', $nameFile)) {
                    $out[] = $nameFile;
                }

                continue;
            }

            if (is_dir($pathFile)) {
                $isDirView = (strpos($pathDir, '/views') > 0);
                if (!$isDirView && !empty($excludesDirBase)) {
                    if (in_array($nameFile, $excludesDirBase)) {
                   //     echo "\nExclude: $pathFile";
                        continue;
                    }
                }

                $filesJsSubDir = self::GetFilesJsDevFromDir(
                    $pathFile, $excludesDirBase
                );

                if (!empty($filesJsSubDir)) {
                    foreach ($filesJsSubDir as $fileJsSubDir) {
                        $out[] = $nameFile . '/' . $fileJsSubDir;
                    }
                }

                continue;
            }
        }

        return $out;
    }

    protected static function GetFilesJsFromDir($pathDir, $excepts=null){
        $files = scandir($pathDir);
        $out = array();
        if (empty($files)) {
            return $out;
        }

        foreach ($files as $nameFile) {
            if ($nameFile == '.' || $nameFile == '..' || is_dir($nameFile)) {
                continue;
            }

            if (!empty($excepts) && in_array($nameFile, $excepts)) {
                continue;
            }

            if (preg_match('/\.js$/', $nameFile)) {
                $out[] = $nameFile;
            }
        }

        return $out;
    }

    protected static function DeleteFileSimilar($pathFileOk){
        $nameFileOk = basename($pathFileOk);
        if (!preg_match('/(_[a-z]+.js)$/', $nameFileOk, $matches)) {
            return;
        }

        $endName = $matches[0];
        if (!$endName) {
            return;
        }

        $pathDir = dirname($pathFileOk);

        $files = self::GetFilesJsFromDir($pathDir, array($nameFileOk));
        if (empty($files)) {
            return;
        }

        // echo "\nnameFileOk: $nameFileOk";

        // print_r($files);
        foreach ($files as $nameFile) {
            if (strpos($nameFile, $endName) > 0) {
                $pathFileDel = $pathDir . '/'. $nameFile;
                // echo "\ndelete: $pathFileDel";
                unlink($pathFileDel);
            }
        }

    }

    protected static function ValidateNameFileJsAppPack(){
        $nameFile = self::GetNameFileJsAppPack();
        $fullPath = self::GetPathDirFilesJs().'/'. $nameFile;
        if (file_exists($fullPath)) {
            return $nameFile;
        }

        self::BuildFileJsAppPack($fullPath);
        return $nameFile;
    }


    // ExjResource::GetSrcScripts()
    public static function GetSrcScripts() {
        $isModeDebugUI = self::IsModeDebugUI();
        $scripts = array();

        $scripts = array_merge($scripts, self::getSrcs_ExtJs($isModeDebugUI));
        $scripts = array_merge($scripts, self::getSrcs_CompBase($isModeDebugUI));

        if (!$isModeDebugUI) {
            $nameFileMinify = self::ValidateNameFileJsAppPack();

            $scripts[] = self::GetURIFilesJs(). '/'. $nameFileMinify;

            return $scripts; 
        }

        /* Modo debug */
        
        $filesJsExj = self::GetFilesJsDevFromDir(
            self::GetDirExjWeb(),
            array(
                'common', 'controllers','data','helpers','models'
            )
        );

        $filesJsApp = self::GetFilesJsDevFromDir(
            self::GetDirAppWeb(),
            array(
                'common', 'controllers','data','helpers','models'
            )
        );

        $itemsScripts = array();
        $itemsScripts[] = array(
            'uri' => self::GetURIBaseExj()."/web",
            'filesJs' => $filesJsExj
        );

        $itemsScripts[] = array(
            'uri' => self::GetURIBaseApp()."/web",
            'filesJs' => $filesJsApp
        );
        
        $filesHelpersJs = array();
        $mapFilesJs = array();

        foreach ($itemsScripts as $itemScript) {
            $uri = $itemScript['uri'];
            $filesJs = $itemScript['filesJs'];

            foreach ($filesJs as $fileJs) {
                $posSepDir = strpos($fileJs, '/');
                if (!$posSepDir) {
                    continue;
                }

                $uriFileJs = $uri . '/' . $fileJs;

                if (strpos($fileJs, '.helper.js') > 0) {
                    $filesHelpersJs[] = $uriFileJs;
                    continue;
                }

                $nameDirBase = substr($fileJs, 0, $posSepDir);

                if (!isset($mapFilesJs[$nameDirBase])) {
                    $mapFilesJs[$nameDirBase] = array();
                }

                $mapFilesJs[$nameDirBase][] = $uriFileJs;
            }
        }

        $componetsApp = self::GetComponetsApp($isModeDebugUI);
        
        foreach ($componetsApp as $componetApp) {
            $nameComponent = $componetApp->nameComponent;

            if (!isset($mapFilesJs[$nameComponent])) {
                continue;
            }

            $filesJs = $mapFilesJs[$nameComponent];

            foreach ($filesJs as $uriFileJs) {
                // echo "\nuriFileJs: $uriFileJs";
                $scripts[] = $uriFileJs;
            }
        }

        // helpers ui de archivos .js
        foreach ($filesHelpersJs as $uriFileJs) {
            $scripts[] = $uriFileJs;
        }

        return $scripts;
    }

    // Inicio
    public static function WriteJavaScript() {
        $srcScripts = self::GetSrcScripts();
        foreach ($srcScripts as $srcScript) {
            echo self::getTemplateJavaScript($srcScript);
        }
    }

    // ExjResource::GetUriLogoFrontEndDefault()
    public static function GetUriLogoFrontEndDefault() {
        return 'templates/'.self::GetNameTemplateSys().'/images/logo_font_end.png';
    }

    public static function GetPathLogoFrontEndDefault(){
        return (self::GetPathBase().'/'. self::GetUriLogoFrontEndDefault());
    }

    public static function GetMapPrefixesComps() {
        if (!empty(self::$_mapPrefixesCmp)) {
            return self::$_mapPrefixesCmp;
        }

        self::$_mapPrefixesCmp = array();
        $prefixes = self::PREFIXES_COMPS;
        foreach ($prefixes as $prefix) {
            self::$_mapPrefixesCmp[$prefix] = strlen($prefix);
        }

        return self::$_mapPrefixesCmp;
    }

    public static function InPrefixesComps($prefix) {
        return ($prefix && in_array($prefix, self::PREFIXES_COMPS));
    }

    public static function IsModeDebugUI(){
        return (!self::GetCfgExj()->isReleased);        
    }

    private static $_cfgExj = null;
    public static function GetCfgExj(){
        if (!self::$_cfgExj) {
            if (!class_exists('CfgExj')) {
                require_once(self::GetPathBase()."/CfgExj.php");
            }

            self::$_cfgExj = new CfgExj();
        }

        return self::$_cfgExj;
    }
}

?>