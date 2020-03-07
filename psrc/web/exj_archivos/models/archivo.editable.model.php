<?php

defined('_JEXEC') or die('Acceso Restringido');

/**
 * @class AppArchivoEditableModel
 */
class AppArchivoEditableModel extends ExjEditableModel {

    public $id_file;
    public $name_file;
    public $nameext_file;
    public $path_file;
    public $sub_folder;
    public $uri_file;
    public $size_file = 0;
    public $id_file_type;

    /**
     * overwrited. Lectura de la tabla a editar
     *
     * @param string $nameTable Nombre de la tabla
     * @param string $fieldKey  Nombre del campo principal de la tabla
     */
    public function readTable(&$nameTable, &$fieldKey) {
        $nameTable = 'jos_app_files';
        $fieldKey = 'id_file';
    }

    /**
     * overwrited. Registro de Campos
     *
     */
    public function registerFields() {
        $this->registerFieldString('name_file', 'Nombre del Archivo');
        $this->registerFieldString('nameext_file', 'Nombre y extension del Archivo');
        $this->registerFieldString('path_file', 'Ruta');
        $this->registerFieldString('sub_folder', 'Sub Carpeta');
        $this->registerFieldString('uri_file', 'URI del Archivo');
        $this->registerFieldInt('size_file', 'Tamaño del Archivo');
        $this->registerFieldInt('id_file_type', 'Id tipo de archivo');
    }

    /**
     * overwrited. Registro de controles UI
     *
     */
    public function registerControlsUI() {
        $this->registerControlUI(ExjUI::NewTextField('name_file', 'Archivo'));
        $this->registerControlUI(ExjUI::NewTextField('path_file', 'Ruta'));
    }

    public function verificarExiste($module_allow) {
        global $exj;
        $db = Exj::InstanceDatabase();

        $nameext_file = $this->getValueFieldSetted('nameext_file');
        $sub_folder = $this->getValueFieldSetted('sub_folder', false);

        if ($this->haveBrokenRules()) {
            return false;
        }
        $id_company = ExjUser::GetIdCompania();

        $sql = "SELECT 
  fil.id_file 
FROM 
  jos_app_files fil INNER JOIN 
  jos_app_files_type fty ON fil.id_file_type = fty.id_file_type 
WHERE
  fty.id_company = $id_company AND 
  fty.module_allow = '$module_allow' AND 
  fil.sub_folder = '$sub_folder' AND 
  fil.nameext_file = '$nameext_file'";

        $db->setQuery($sql);
        $archivo = null;
        $db->loadObject($archivo);
        if (Exj::GetError()->haveError()) {
            $this->addBrokenRuler(Exj::GetErrorMsgGlobal());
            return false;
        }

        if (!$archivo) {
            return false;
        }

        $this->id = $this->id_file = $archivo->id_file;

        return $this->id;
    }

    /**
     * overwrited. Antes de Eliminar
     *
     * @param int $id
     */
    public function beforeDestroy($id) {
        if (!parent::beforeDestroy($id)) {
            return false;
        }

        /*
          if (!$this->canDestroyRelationTable($id, 'jos_app_xxx_files', 'Archivos de XXX')) {
          return false;
          }
         */


        return $this->_deleteFile($id);
    }

    private function _deleteFile($id) {

        $this->load($id);
        if ($this->haveBrokenRules()) {
            return false;
        }

        $pathCompleteFile = $this->path_file;


        $pathFileToDelete = $pathCompleteFile;
        ExjTransferCharacters::decodeUTF8ToISO($pathFileToDelete);
        if (!file_exists($pathFileToDelete)) {
            $folderContent = '/files/';
            $pos = strrpos($this->path_file, $folderContent);
            if ($pos !== false) {
                $pos += strlen($folderContent);
                if ($pos < strlen($pathCompleteFile)) {
                    $hFile = new ExjHandlerFile($this->nameext_file, $this->sub_folder, false);

                    $pathCompleteFile = substr($pathCompleteFile, $pos);
                    $newPathCompleteFile = ExjHandlerFile::GetPathBaseFiles();

                    ExjHandlerFile::AddToPath($newPathCompleteFile, $pathCompleteFile);
                    $pathCompleteFile = $newPathCompleteFile;
                }
            }

            $pathFileToDelete = $pathCompleteFile;
            ExjTransferCharacters::decodeUTF8ToISO($pathFileToDelete);

            if (!file_exists($pathFileToDelete)) {
                //	echo "El archivo: $this->nameext_file<br/>No existe en la ruta: $pathCompleteFile";
                return true;
            }
        }

        if (!unlink($pathFileToDelete)) {
            echo "El archivo: $this->nameext_file<br/>No se pudo eliminar.<br/>Ruta: $pathCompleteFile";
        }

        return true;
    }

    /**
     * overwrited. Antes de Guardar
     *
     * @return bool
     */
    public function beforeSave() {

        // comprobación de duplicados
        /*
          if (!$this->canSaveCodeUnique('nameext_file', 'Nombre de Archivo')) {
          return false;
          }
         */

        return true;
    }

    public function importData($module_allow = '') {
        if (!$this->isSettedField('nameext_file') || !$this->nameext_file) {
            $this->addBrokenRuler("No se a seteado baseNameFile para importar");
            return false;
        }
        if (!$this->isSettedField('sub_folder') || !$this->sub_folder) {
            $this->addBrokenRuler("No se a seteado SUB CARPETA para importar");
            return false;
        }

        $id_file_type = $this->getParam('id_file_type', 0, false);
        $path_parts = null;
        if (!$id_file_type) {

            $path_parts = pathinfo($this->nameext_file);
            $extFile = $path_parts['extension'];
            if (!$extFile) {
                $this->addBrokenRuler("El archivo: $this->nameext_file no tiene extensión");
                return false;
            }

            
            $dataType = null;
            if (!AppArchivosData::LoadRowFileType($dataType, $extFile, $module_allow)) {
                $this->addBrokenRuler("Ocurrió un error al obtener información de tipos de archivos");
                return false;
            }

            if (!$dataType) {
                $this->addBrokenRuler("El tipo de archivo: $extFile no está permitido cargar archivos al sistema");
                return false;
            }

            $id_file_type = $dataType->id_file_type;
        }

        $this->id_file_type = $id_file_type;

        // consultar si ya esta registrado
        $criteria = new stdClass();
        $criteria->id_file_type = $id_file_type;
        $criteria->nameext_file = $this->nameext_file;
        $criteria->sub_folder = $this->sub_folder;

        $objSelf = null;
        if (!$this->loadDBFromCriteriaToObject($objSelf, $criteria, 'id_file')) {
            $this->addBrokenRuler("Problemas al consultar en: " . get_class($this));
            return false;
        }

        if ($objSelf) {
            $this->setValueId($objSelf->id_file);
            return true;
        }

        // no existe hay que crearlo
        $this->setValueId(0);
        if (!$this->isSettedField('size_file') || !$this->size_file) {
            if (!$this->isEmptyField('path_file')) {
                $this->size_file = filesize($this->path_file);
            } else {
                $this->size_file = 0;
            }
        }

        if (!$this->isSettedField('name_file') || !$this->name_file) {
            if (!$path_parts) {
                $path_parts = pathinfo($this->nameext_file);
            }

            $this->name_file = $path_parts['filename'];
        }

        $this->save();
        if ($this->haveBrokenRules()) {
            return false;
        }

        return true;
    }

}

?>