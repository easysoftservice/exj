<?php

defined('_JEXEC') or die('Restricted access');

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

    public function __construct($fullPathFile)
    {
        $this->setEntry('out')->setCanViewFile(true)->setUrl('');

        if (file_exists($fullPathFile)) {
            $this->_fileSizeRaw = filesize($fullPathFile);

            $path_parts = pathinfo($fullPathFile);
            $this->setFileExt($path_parts['extension'])
                ->setFileName($path_parts['filename']);
        }
        else {
            $this->_fileSizeRaw = 0;
        }

        $this->fileSize = ExjUtil::RenderSizeBytes($this->_fileSizeRaw);

        $this->idFile = base64_encode($fullPathFile);
    }

    public function setCanViewFile($value = true) {
        $this->canViewFile = ($value ? 1 : 0);
        return $this;
    }

    
    public function setEntry($value) {
        $this->entry = base64_encode($value);
        return $this;
    }

    public function setUrl($value) {
        $this->url = $value;
        return $this;
    }

    public function setFileExt($value) {
        $this->fileExt = $value;
        return $this;
    }

    public function setFileName($value) {
        $this->fileName = $value;
        return $this;
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