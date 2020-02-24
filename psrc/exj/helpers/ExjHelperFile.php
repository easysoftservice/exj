<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Helper para archivos
 *
 */
class ExjHelperFile {

    const DS = DIRECTORY_SEPARATOR;
    const ENDLINE = PHP_EOL;
    const ARCHIVOTIPO_MODULO_LINKS = 'LINKS';
    const ARCHIVOTIPO_MODULO_UPGRADES = 'UPGRADES';

    /**
     * Crea un archivo, e inserta contenido en el mismo
     *
     * @param string $pathFile Path y nombre del archivo a crear
     * @param mixed $contentFile Puede ser string o un array de string
     * @param string $msgError Si ocurre algún error, en este parámetros se setea el error
     * @param bool $canCreateDir Indica si se puede crear el path del directorio, defecto false
     * @return bool true si se pudo crear el archivo, sino false
     */
    public static function CreateFile($pathFile, $contentFile, &$msgError, $canCreateDir = false) {
        if (!$pathFile) {
            $msgError = "No se ha indicado el path del archio a crear. Ref: " . __METHOD__;
            return false;
        }

        //	echo '<br/>'. __METHOD__. " pathFile: $pathFile realpath: ".realpath($pathFile) . " dirname: ". dirname($pathFile);

        $pathDir = dirname($pathFile);
        if (!file_exists($pathDir)) {
            // echo "<bt/>No existe el dir: $pathDir";
            if ($canCreateDir) {
                if (!ExjFile::MkDirRecursive($pathDir)) {
                    $msgError = "Could not create directory<br/>$pathDir";
                    return false;
                }
            } else {
                $msgError = "Can not access directory<br/>$pathDir";
                return false;
            }
        }

        $hFile = fopen($pathFile, 'wb+');

        if ($hFile === false) {
            $msgError = "Could not open the <br/>archivo: $pathFile to write.";
            return false;
        }

        if ($contentFile && is_array($contentFile)) {
            $contentFile = implode(self::ENDLINE, $contentFile);
        }

        fwrite($hFile, $contentFile);

        fclose($hFile);
        return true;
    }

    /**
     * Información del path, por ejemplo si en el 1er parámetro se pasa: test.model.php
     *
     * @param string $pathFile  Path del archivo, puede ser solo el archivo
     * @param string $dirName   Ruta o nombre del directorio: Ej: .
     * @param string $baseName  Nombre del archivo Ej: test.model.php
     * @param string $extension Extensión del archivo Ej: php
     * @param string $filename  Solo nombre del archivo Ej: test.model
     */
    static function pathInfo($pathFile, &$dirName, &$baseName, &$extension, &$filename) {

        $partes = pathinfo($pathFile);

        $dirName = $partes['dirname'];
        $baseName = $partes['basename'];
        $extension = $partes['extension'];
        $filename = $partes['filename'];
    }

    /**
     * Información del path del archivo
     *
     * @param string $pathFile
     * @param string $dirName
     * @param string $onlyName
     * @param string $extension
     * @param bool $extToLower
     */
    static function pathInfoFileDir($pathFile, &$dirName, &$onlyName, &$extension, $extToLower = true) {
        $baseName = '';

        self::pathInfo($pathFile, $dirName, $baseName, $extension, $onlyName);

        if ($extToLower) {
            $extension = strtolower($extension);
        }
    }

    /**
     * Información solo del nombre o ruta del archivo
     *
     * @param string $pathFile
     * @param string $fileName Solo el nombre del archivo, sin extension
     * @param string $extension
     * @param bool $extToLower Por defecto true
     */
    static function pathInfoFileExtension($pathFile, &$onlyName, &$extension, $extToLower = true) {
        $dirName = '';

        self::pathInfoFileDir($pathFile, $dirName, $onlyName, $extension, $extToLower);
    }

    public static function ConcatPaths($path1, $path2, $separatorDirs = '') {
        if (!$path1 && !$path2) {
            return '';
        }

        if (!$separatorDirs) {
            $separatorDirs = self::DS;
        }

        $pathComplete = $path1;
        if ($pathComplete && $path2) {
            $pathComplete .= $separatorDirs . $path2;
        } elseif ($path2) {
            $pathComplete = $path2;
        }

        $posRoot = -1;
        $lenPath = strlen($pathComplete);
        for ($i = 0; $i < $lenPath; $i++) {
            $charPath = $pathComplete{$i};
            if ($charPath == '/' || $charPath == '\\') {
                $posRoot = $i;
            } elseif ($posRoot >= 0) {
                $posRoot = $i - 1;
                break;
            }
        }

        $pathRoot = substr($pathComplete, 0, $posRoot + 1);
        $pathFile = substr($pathComplete, $posRoot + 1);
        if ($separatorDirs == '/') {
            $pathFile = str_replace(array('\\', '\\\\', '\\/', '//'), $separatorDirs, $pathFile);
        } else {
            $pathFile = str_replace(array('/', '//', '/\\', '\\\\'), $separatorDirs, $pathFile);
        }

        $pathComplete = $pathRoot . $pathFile;

        // 	echo "<br/>$separatorDirs: $separatorDirs | pathRoot: $pathRoot pathFile: $pathFile | pathComplete: $pathComplete";

        return $pathComplete;
    }

    /**
     * Copiar todo el directorio y sus sub directorios y archivos, del origen al destino
     *
     * @param string $source Ruta del directorio Origen
     * @param string $target Ruta del directorio Destino
     */
    public static function copyAllDir($source, $target) {
        if (is_dir($source)) {
            if (!file_exists($target)) {
                ExjFile::MkDir($target);
            }
            
            $d = dir($source);
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $Entry = $source . '/' . $entry;
                if (is_dir($Entry)) {
                    self::copyAllDir($Entry, $target . '/' . $entry);
                    continue;
                }
                copy($Entry, $target . '/' . $entry);
            }

            $d->close();
        } else {
            copy($source, $target);
        }
    }

    public static function SearchFileInDir($nameFile, &$founds, $dirToSearch='', $maxSeg=0, $exepts=null) {
        $founds = null;

        if (!preg_match('/^([a-z0-9\._\-])+$/i', $nameFile)) {
            throw new Exception("Buscar archivo. No válido: $nameFile", 1);
        }

        if (!$dirToSearch) {
            $dirToSearch = realpath('/');
        }

        if (!is_dir($dirToSearch)) {
            throw new Exception("Buscar archivo. Directorio no existe: $dirToSearch", 1);
        }
        $dirToSearch = str_replace('\\', '/', $dirToSearch);
        $dirToSearch = rtrim($dirToSearch, '/');

        if ($maxSeg < 0) {
            $maxSeg = 0;
        }
        $timeIni = ($maxSeg ? microtime(true) : 0);
        
        $res = self::_searchFileInDir(
            $nameFile, $dirToSearch, $founds, $maxSeg, $timeIni, $exepts
        );

        $segDemora = round(microtime(true) - $timeIni, 3);
        // echo "<br>maxSeg: $maxSeg demora: $segDemora seg. res: $res";
        
        return $res;
    }

    private static function _searchFileInDir($nameFile, $dirToSearch, &$founds, $maxSeg=0, $timeIni=0, $exepts=null)
    {
        if ($timeIni && $maxSeg) {
            if (round(microtime(true) - $timeIni, 2) >= $maxSeg) {
                // echo "<br>tiempo termino ln: ". __LINE__;
                return $dirToSearch;
            }
        }

        if (!empty($exepts)) {
            $readDir = true;
            foreach ($exepts as $exept) {
                if (substr($exept, 0, 1)=='/' && substr($exept, -1)=='/') {
                    if (preg_match($exept, $dirToSearch)) {
                        $readDir = false;
                        break;
                    }
                }

                if (stripos($dirToSearch, $exept)!==false) {
                    $readDir = false;
                    break;
                }
            }

            if (!$readDir) {
              //  echo "<br>excluyendo: $dirToSearch";
                return true;
            }
        }

        $d = dir($dirToSearch);
        $resSearch = true;
        // $filesReades = 0;
        while (false !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            // $filesReades += 1;

            $pathx = $dirToSearch . '/' . $entry;
            if ($entry==$nameFile && is_file($pathx)) {
                if (empty($founds)) {
                    $founds = array();
                }
                $founds[] = $pathx;
                continue;
            }

            if (is_dir($pathx)) {
                if ($timeIni && $maxSeg) {
                    if (round(microtime(true) - $timeIni, 2) >= $maxSeg) {
                        $resSearch = $pathx;
                        break;
                    }
                }

                $res = self::_searchFileInDir(
                    $nameFile, $pathx, $founds, $maxSeg, $timeIni, $exepts
                );

                if (is_string($res)) {
                    break;
                }

                continue;
            }
        }

        $d->close();

        // echo "<br>dirToSearch: $dirToSearch Leidos: " . $filesReades;

        return $resSearch;
    }

}
?>