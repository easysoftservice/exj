<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base para soporte de cargar archivos
 *
 */
class ExjHandlerFileUpload extends ExjObject {

    const NAME_FILE_FROM_UI = 'file_upload';

    private $_nameFileUI = 'file_upload';
    private $_file_name = '';
    private $_file_type = '';
    private $_file_tmp_name = '';
    private $_file_error = 0;
    private $_file_size = 0;
    private $_sizeMaxBytes = 0;
    private $_pathFileSaved = '';
    private $_id_file_type = 0;
    private $_hFile = null;

    public function __construct($sizeMaxBytes = 0, $nameFileUI = '') {
        global $exj;
        $exj->returnHTML = false;
        if ($nameFileUI) {
            $this->_nameFileUI = $nameFileUI;
        }

        if (!isset($_FILES[$this->_nameFileUI])) {
            $this->_setError("No se cargó el archivo.");
            return $this;
        }
        $this->_sizeMaxBytes = $sizeMaxBytes;
        if (!$this->_sizeMaxBytes) {
            $this->_sizeMaxBytes = Exj::GetSizeMaxUpload();
        }

        $fileFromUI = $_FILES[$this->_nameFileUI];

        $this->_file_name = $fileFromUI['name'];
        $this->_file_type = $fileFromUI['type'];
        $this->_file_tmp_name = $fileFromUI['tmp_name'];
        $this->_file_error = $fileFromUI['error'];
        $this->_file_size = $fileFromUI['size'];

        if ($this->_file_error) {
            $this->_setError(self::GetMsgErrorUpload($this->_file_error));
            return $this;
        }

        if (!$this->_file_size) {
            $this->_setError("El archivo: $this->_file_name está vacio.");
        }
    }

    public static function GetMsgErrorUpload($codeError) {
        $msg = '';
        if (!$codeError) {
            return $msg;
        }

        $codeErrorInt = $codeError;
        if (is_numeric($codeError)) {
            $codeErrorInt = intval($codeError);
        }

        if (!$codeErrorInt) {
            return $codeError;
        }

        switch ($codeErrorInt) {
            case UPLOAD_ERR_INI_SIZE:
                $msg = "El fichero subido excede la directiva upload_max_filesize de php.ini";
                
                $msg .= ' VALOR: '.ini_get('upload_max_filesize');

            break;

            case UPLOAD_ERR_FORM_SIZE:
                $msg = "Valor: 2; El fichero subido excede la directiva MAX_FILE_SIZE especificada en el formulario HTML";
            break;

            case UPLOAD_ERR_PARTIAL:
                $msg = "Valor: 3; El fichero fue sólo parcialmente subido.";
            break;

            case UPLOAD_ERR_NO_FILE:
                $msg = "Valor: 4; No se subió ningún fichero";
            break;

            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = "Valor: 6; Falta la carpeta temporal";
            break;

            case UPLOAD_ERR_CANT_WRITE:
                $msg = "Valor: 7; No se pudo escribir el fichero en el disco";
            break;

            case UPLOAD_ERR_EXTENSION:
                $msg = "Valor: 8; Una extensión de PHP detuvo la subida de ficheros. PHP no proporciona una forma de determinar la extensión que causó la parada de la subida de ficheros";
            break;
            
            default:
                $msg = "Código error desconocido: $codeError";
            break;
        }

        return $msg;
    }

    public function haveError() {
        return Exj::GetError()->haveError();
    }

    public function getErrorMsg() {
        global $exj;
        return $exj->getErrorMsg();
    }

    public static function getMaxSizeFileUpload() {
        return (1024 * 1024 * 4); // 4 MB
    }

    /**
     * Indica si está cargado al servidor un archivo
     *
     * @param string $nameFileUI No requerido
     * @return bool
     */
    public static function IsLoadFileFromUI($nameFileUI = '') {
        if (!$nameFileUI) {
            $nameFileUI = self::NAME_FILE_FROM_UI;
        }

        if (!isset($_FILES[$nameFileUI])) {
            return false;
        }

        $fileFromUI = $_FILES[$nameFileUI];

        if (!$fileFromUI['name'] && $fileFromUI['error']) {
            return false;
        }

        // print_r($fileFromUI);

        return true;
    }

    /**
     * Devuelve el path físico que se guardó en el servidor, previo a la llamada a: uploadFile
     *
     * @return string
     */
    public function getPathFileSaved() {
        return $this->_pathFileSaved;
    }

    public function get_id_file_type() {
        return $this->_id_file_type;
    }

    public function getFileName($sinExtension = true) {
        $fileName = $this->_file_name;
        if (!$sinExtension) {
            return $fileName;
        }

        $pos = strrpos($fileName, ".");
        if ($pos === false) {
            return $fileName;
        }

        return substr($fileName, 0, $pos);
    }

    /**
     * Carga un archivo al servidor
     * @param string $ARCHIVOTIPO_MODULO
     * @param string $subDir
     * @param bool $addFolderUser
     * @param string $renameNameFile No requerido
     * @return boolean
     */
    public function uploadFile($ARCHIVOTIPO_MODULO, $subDir = '', $addFolderUser = null, $renameNameFile='')
    {
        if (!$ARCHIVOTIPO_MODULO) {
            $ARCHIVOTIPO_MODULO = ExjHelperFile::ARCHIVOTIPO_MODULO_LINKS;
        }

        if (!$this->_canUploadFile($ARCHIVOTIPO_MODULO)) {
            return false;
        }

        if ($addFolderUser === null) {
            $addFolderUser = ($subDir ? false : true);
        }
        
        if($renameNameFile){
            $this->_file_name = $renameNameFile;
        }


        $this->_hFile = new ExjHandlerFile($this->_file_name, $subDir, $addFolderUser);
        $pathFileIn = $this->_hFile->getPathFileIn();
        if ($this->_hFile->haveError()) {
            return false;
        }

        if (!is_uploaded_file($this->_file_tmp_name)) {
            $this->_setError("Posible ataque de carga de archivo:<br/>$this->_file_tmp_name");
            return false;
        }

        ExjTransferCharacters::decodeUTF8ToISO($pathFileIn);
        $pathFileSaveTo = $pathFileIn;

        if (!move_uploaded_file($this->_file_tmp_name, $pathFileSaveTo)) {
            $this->_setError(
                "No se pudo mover el archivo a cargar. Archivo:<br/>$this->_file_name"
            );
            return false;
        }
        

        $this->_pathFileSaved = $pathFileIn;

        // readfile($this->_file_tmp_name);

        return true;
    }

    public function getURIFile() {
        if (!$this->_hFile) {
            return '';
        }

        return $this->_hFile->getURIFileIn();
    }

    public function getSizeFile() {
        return $this->_file_size;
    }

    /* FUNCIONES PRIVADAS */

    private function _setError($msg) {
        if (is_numeric($msg)) {
            $msg = "Código de error: $msg";
        }

        global $exj;
        $msg = "Error Cargando archivo.<br/>$msg";
        $exj->setErrorValidating($msg);
        return $this;
    }

    private function _canUploadSizeFile() {
        if ($this->_sizeMaxBytes) {
            if ($this->_file_size > $this->_sizeMaxBytes) {
                $sizeMax = ExjUtil::RenderSizeBytes($this->_sizeMaxBytes);
                $sizeFile = ExjUtil::RenderSizeBytes($this->_file_size);
                $this->_setError("El archivo: $this->_file_name tiene un tamaño de:$sizeFile<br/>. El tamaño máximo permitido es: $sizeMax");
                return false;
            }
        }

        return true;
    }

    private function _canUploadFile($ARCHIVOTIPO_MODULO) {
        if ($this->haveError()) {
            return false;
        }
        if (!$this->_file_name) {
            $this->_setError("No se ha cargado el archivo!");
            return false;
        }
        if (!$this->_file_tmp_name) {
            $this->_setError("El archivo: $this->_file_name no se copió en carpeta temporal.");
            return false;
        }


        if (!$this->_canUploadSizeFile()) {
            return false;
        }

        $partes_file = explode(".", $this->_file_name);
        if (count($partes_file) <= 1) {
            $this->_setError("El archivo: $this->_file_name no tiene extensión");
            return false;
        }

        $ext_file = $partes_file[count($partes_file) - 1];
        $ext_file = strtolower($ext_file);

        $tipo = AppFilesData::GetRowFileType($ext_file, $ARCHIVOTIPO_MODULO);
        if ($tipo === false) {
            return false;
        }

        if (!$tipo) {
            $this->_setError(
                "Archivo: $this->_file_name <br/>Tipo: $this->_file_type <br/>No está permitido cargar.<br/>Módulo: $ARCHIVOTIPO_MODULO"
            );
            return false;
        }

        $this->_id_file_type = $tipo->id_file_type;

        if ($tipo->size_max_bytes) {
            $this->_sizeMaxBytes = $tipo->size_max_bytes;
            if (!$this->_canUploadSizeFile()) {
                return false;
            }
        }

        return true;
    }

}

?>