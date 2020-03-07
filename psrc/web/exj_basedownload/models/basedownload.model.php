<?php

defined('_JEXEC') or die('Acceso Restringido');

/**
 * @class AppBasedownloadModel
 */
class AppBasedownloadModel extends ExjModel {

    const CONTENT_TYPE_PDF = 'application/pdf';
    const CONTENT_TYPE_EXCEL_XLS = 'application/vnd.ms-excel';
    const CONTENT_TYPE_EXCEL_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const CONTENT_TYPE_WORD_DOC = 'application/msword';
    const CONTENT_TYPE_WORD_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const CONTENT_TYPE_FORCE_DOWNLOAD = 'application/force-download';
    const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';
    const CONTENT_TYPE_XML = 'text/xml';
    const CONTENT_TYPE_IMG_PNG = 'image/png';

    const CONTENT_TYPE_XML_UTF8 = 'application/xml; charset=utf-8';
    const CONTENT_DISPOSITION_ATTACHMENT = 'attachment'; // descarga
    const CONTENT_DISPOSITION_INLINE = 'inline'; // visualización
    const TimeCacheSegDefault = 120; // 2 minutos

    /**
     * Devuelve data de modelo de lista
     *
     * @param ExjHelperMenu $hMenu Instancia de la clase ExjHelperMenu
     * @param string $nameComponent
     * @param string $nameListModel
     * @return object
     */

    static function getDataUIList($hMenu, $nameComponent, $nameListModel) {
        global $exj;

        $nameClaseModel = Exj::GetNameClassList($nameListModel);

        $instaceModel = new $nameClaseModel($hMenu);
        // $instaceModel->setDataAccess($hMenu);
        $instaceModel->readData($nameComponent);

        return $instaceModel->to_ui();
    }

    /**
     * Decodifica el path del archivo de descarga
     *
     * @param string $idFile
     * @param string $entry
     * @param int $idFull
     * @return string Path completo del archivo
     */
    static function DecodePathFile($idFile, $entry, $idFull) {
        $pathFile = base64_decode($idFile);
        if ($entry) {
            $entry = base64_decode($entry);
        }

        if (!$idFull) {
            // build
            $pathFile = ExjHandlerFile::GetPathBaseFiles($entry . '/' . $pathFile);
        }

        return $pathFile;
    }

    /**
     * Descarga o visualiza un archivo de disco
     * @param string $pathFile
     * @param string $fileName
     * @param bool $canViewFile
     * @param bool $forceExit
     */
    public static function DownloadFile($pathFile, $fileName = '', $canViewFile = false, $forceExit = true) {
        $path_parts = pathinfo($pathFile);

        header('Cache-Control: maxage=0');

        header('Expires: ' . date(DATE_COOKIE, time() + self::TimeCacheSegDefault)); // Cache for 2 mins
        header('Pragma: public');

        $extFile = $path_parts['extension'];
        $extFile = strtolower($extFile);

        if (!$fileName) {
            $fileName = $path_parts['basename'];
        } else {
            $fileName .= '.' . $extFile;
        }

        $contentTypes = array();
        switch ($extFile) {
            case 'pdf':
                $contentTypes[] = self::CONTENT_TYPE_PDF;
                break;

            case 'xlsx':
                $contentTypes[] = self::CONTENT_TYPE_EXCEL_XLSX;
                header("Content-Transfer-Encoding: Binary");
                break;

            case 'xls':
                $contentTypes[] = self::CONTENT_TYPE_EXCEL_XLS;
                header("Content-Transfer-Encoding: Binary");
                break;

            case 'xml':
                $contentTypes[] = self::CONTENT_TYPE_XML_UTF8;
                //	$contentTypes[] = self::CONTENT_TYPE_XML;
                header("Content-Transfer-Encoding: Binary");
                break;

            case 'doc':
                $contentTypes[] = self::CONTENT_TYPE_WORD_DOC;
                header("Content-Transfer-Encoding: Binary");
                break;

            case 'docx':
                $contentTypes[] = self::CONTENT_TYPE_WORD_DOCX;
                header("Content-Transfer-Encoding: Binary");
                break;

            case 'zip':
                $contentTypes[] = self::CONTENT_TYPE_OCTET_STREAM;
                header("Content-Transfer-Encoding: Binary");
                break;

            case 'png':
                $contentTypes[] = self::CONTENT_TYPE_IMG_PNG;
                // header("Content-Transfer-Encoding: Binary");
                break;

            default:
                $contentTypes[] = self::CONTENT_TYPE_FORCE_DOWNLOAD;
                //	$contentTypes[] = self::CONTENT_TYPE_OCTET_STREAM;
                header("Content-Transfer-Encoding: Binary");
                break;
        }

        foreach ($contentTypes as $contentType) {
            header('Content-Type: ' . $contentType);
        }

        $contentDisposition = self::CONTENT_DISPOSITION_ATTACHMENT;
        if ($canViewFile) {
            $contentDisposition = self::CONTENT_DISPOSITION_INLINE;
        } else {
            header('Content-Length: ' . filesize($pathFile));
        }

        header('Content-Disposition: ' . $contentDisposition . ';filename="' . $fileName . '"');

        //	echo "<hola>666</hola>";
        readfile($pathFile);
        if ($forceExit) {
            exit();
        }
    }

}

?>