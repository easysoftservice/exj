<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base para soporte de archivos
 *
 */
class ExjHandlerFile extends ExjObject {
    const FOLDER_FILES = 'files';

    private $_nameFile = '';
    private $_subFolder = '';
    private $_addFolderUser = true;

    public function __construct($nameFile, $subFolder = '', $addFolderUser = true)
    {
        $this->_nameFile = $nameFile;
        $this->_subFolder = $subFolder;
        if ($this->_subFolder && is_numeric($this->_subFolder)) {
            $this->_subFolder .= '';
        }

        $this->_addFolderUser = $addFolderUser;

//        $this->_buildPath('in');
 //       $this->_buildPath('out');
    }

    public function setSubFolder($value) {
        $this->_subFolder = $value;
        return $this;
    }

    public function setAddFolderUser($value = true) {
        $this->_addFolderUser = $value;
        return $this;
    }

    public static function GetPathDirFilesRelative($addSubDir = '') {
        $path = Exj::DIR_STORAGE_APP . '/' . self::FOLDER_FILES;
        if ($addSubDir) {
            $path .= '/' . $addSubDir;
        }

        return $path;
    }

    /**
     * Devuelve el path base donde se encunetran los archivos in out
     *
     * @param string $addSubDir
     * @return string
     */
    public static function GetPathBaseFiles($addSubDir = '') {
        $path = Exj::GetPathBase() . '/' . self::GetPathDirFilesRelative($addSubDir);

        return $path;
    }

    public static function GetCharsMaxNameFile() {
        return 66;
    }

    public function haveError() {
        global $exj;
        return $exj->haveError();
    }

    public function getPathFileIn() {
        return $this->getDirFileIn() . '/' . $this->_nameFile;
    }

    public function getPathFileOut() {
        return $this->getDirFileOut() . '/' . $this->_nameFile;
    }

    public function getDirFileIn() {
        return $this->getDirValidate('in');
    }

    public function getDirFileOut() {
        return $this->getDirValidate('out');
    }

    public function getURIFileIn() {
        return $this->_getURIFile('in');
    }

    public function getURIFileOut() {
        return $this->_getURIFile('out');
    }

    public function existFileOut() {
        return file_exists($this->getPathFileOut());
    }

    public function existFileIn() {
        return file_exists($this->getPathFileIn());
    }

    public function getPathDirRelative($subDir = '') {
        $path = self::GetPathDirFilesRelative();

        self::AddToPath($path, $subDir);
        self::AddToPath($path, $this->_subFolder);
        self::AddToPath($path, $this->getFolderUser());

        return $path;
    }

    public function getPathFileRelative($subDir = '') {
        $path = $this->getPathDirRelative($subDir);
        self::AddToPath($path, $this->_nameFile);

        return $path;
    }

    protected function getDirValidate($subDir) {
        $pathDir = Exj::GetPathBase();
        self::AddToPath($pathDir, $this->getPathDirRelative($subDir));

        if (!ExjFile::ValidateDir($pathDir)) {
            global $exj;
            $exj->setError(
                "No se pudo crear el directorio:<br/>$pathDir", Exj::TIPO_ERROR_FILE
            );
        }

        return $pathDir;
    }

    protected function getFolderUser() {
        $folderUser = '';
        if ($this->_addFolderUser) {
            $folderUser = self::GetFolderUserEncode();
        }

        return $folderUser;
    }

    public static function GetFolderUserEncode() {
        return ('u/' . dechex((ExjUser::GetId()+1)*2));
    }

    public static function AddToPath(&$path, $folder) {
        if (!$folder) {
            return $path;
        }

        $path .= '/' . $folder;
        return $path;
    }

    // ExjHandlerFile::GetURIRoot()
    public static function GetURIRoot() {
        return JURI::root();
    }

    private function _getURIFile($subDir) {
        $uRIFile = self::GetURIRoot();
        $uRIFile .= $this->getPathFileRelative($subDir);

        return $uRIFile;
    }


    public static function GetDirectoryTemp() {
        $dirTmp = Exj::GetPathDirStorageFiles() . "/tmp/" . self::GetFolderUserEncode();

        if (!ExjFile::ValidateDir($dirTmp)) {
            global $exj;
            $exj->setErrorValidating("No se pudo crear el directorio temporal en el servidor.<br/>dirTmp: $dirTmp");
        }

        return $dirTmp . '/';
    }
}

?>