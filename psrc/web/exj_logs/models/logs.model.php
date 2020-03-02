<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppLogModel
 * Modelo para Logs
 */
class AppLogModel extends ExjModel {
	
    public static function loadListLogs(&$items, &$total, $paramsCriteria=null) {
    	return AppLogsData::loadListLogs($items, $total, $paramsCriteria);
    }

    public static function LoadLogsInternals(ExjResponse $response){
    	$pathLogsPhp = ini_get('error_log');
    	if (!$pathLogsPhp) {
    		return $response->setMsgError("No definido ruta de logs de php");
    	}

    	if (!file_exists($pathLogsPhp)) {
    		return $response->setMsgError("No existe archivo: $pathLogsPhp");
    	}

    	$contFile = file_get_contents($pathLogsPhp);
    	if (!$contFile) {
    		return $response->setMsgWarning(
    			"Archivo de logs está vacio"
    		);
    	}

    	$info = new stdClass();
    	$info->nameFile = basename($pathLogsPhp);
    	$info->content = $contFile;

    	$response->setDataObject($info);
    }

    public static function LoadContentIniInternals(ExjResponse $response){
        $dirPhp = '';
        if (isset($_SERVER['PHP_PEAR_BIN_DIR'])) {
            $dirPhp = $_SERVER['PHP_PEAR_BIN_DIR'];
        }

        if (!$dirPhp) {
            $pathLogsPhp = ini_get('error_log');
            $pathLogsPhp = str_replace('\\', '/', $pathLogsPhp);
            $partesDir = explode('/', $pathLogsPhp);
            if (count($partesDir) >= 2) {
                $dirPhp = $partesDir[0];
                $dirPhp .= '/'. $partesDir[1];
                // echo "<br>dirPhp: $dirPhp";
            }
        }

        // print_r($_SERVER);

        if (!$dirPhp) {
            return $response->setMsgError("No se encontró dir php");
        }

        $dirPhp = str_replace('\\', '/', $dirPhp);
        $pathPhpini = $dirPhp. '/php.ini';
        if (!file_exists($dirPhp)) {
            return $response->setMsgError("No existe archivo: $dirPhp");
        }

        $contFile = file_get_contents($pathPhpini);
        if (!$contFile) {
            return $response->setMsgWarning(
                "Archivo de php.ini está vacio"
            );
        }

        ExjSession::Set('pathPhpini' , $pathPhpini);

        $info = new stdClass();
        $info->nameFile = basename($pathPhpini);
        $info->content = $contFile;

        $response->setDataObject($info);
    }

    public static function SaveCntIniInt(ExjResponse $response, $txtInter){
        if (!trim($txtInter)) {
            return $response->setMsgError("Contenido vacio!");
        }

        $pathPhpini = ExjSession::Get('pathPhpini');
        if (!$pathPhpini) {
            return $response->setMsgError("path no defindo!");
        }

        // test
      //  $pathPhpini .= 'xxx';

        $bytesWrited = file_put_contents($pathPhpini, $txtInter);
        if ($bytesWrited === false) {
            return $response->setMsgError(
                "No se pudo esribir en el archivo: ".basename($pathPhpini)
            );
        }

        $response->setMsgInfo("Se guardó con éxito $bytesWrited bytes");

        self::ResetServiceApache($response);
        if (!$response->haveMsgError()) {
            $response->setMsgInfo(
                "Se guardó con éxito $bytesWrited bytes y se reinicio apache"
            );
        }
    }

    public static function ResetServiceApache(ExjResponse $response){
        // print_r($_SERVER);
        $dirApache = (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT']:'');
        if (!$dirApache) {
            return $response->setMsgError(
                "No se encontró dir apache!"
            );
        }

        $dirApache = str_replace('\\', '/', $dirApache);
        $partesDir = explode('/', $dirApache);
        if (count($partesDir) <= 2) {
            return $response->setMsgError(
                "No se encontró dir apache, path erroneo!"
            );
        }
        // C:/Apache241w64vc11/htdocs

        $pathHttp = array(
            $partesDir[0],
            $partesDir[1],
            'bin',
            'httpd.exe',
        );
        $pathHttp = implode('\\', $pathHttp);
        if (!file_exists($pathHttp)) {
            return $response->setMsgError(
                "No se encontró: $pathHttp"
            );
        }

        // $strCmd = '"'.$pathHttp.'"'." -k restart";
        $strCmd = $pathHttp." -k restart";
        exec($strCmd, $outputCmd, $returnCmd);
        // pclose(popen("start /B ". $strCmd, "r"));
        // exec("c:\\windows\\system32\\cmd.exe /c $strCmd"); 

        /*
        session_write_close();
        exec($strCmd);
        session_start();
        */

       // $pathBat = dirname($pathHttp);
       // $pathBat .= "\\resethttpd.bat";
       // if (file_put_contents($pathBat, $strCmd)) {
            // shell_exec($pathBat);
            // pclose(popen("start /B ". $pathBat, "r"));
       //     echo "<br>Ejecutado: $pathBat";
        // }

        // echo "<br>Ejecutado: ".$strCmd;
    }

    public static function LoadDataVarServer(ExjResponse $response) {

    	$tableHtml = '<table cellspacing="1"><tbody>';

    	$content = array();
    	foreach ($_SERVER as $key => $value) {
    		$valHtml = '';
    		if (is_object($value) || is_array($value)) {
    			$valHtml = print_r($value, true);
    		}
    		else {
    			$valHtml = $value;
    		}

    		$tableHtml .= '<tr>';

    		$tableHtml .= '<td>' . $key . '</td>';
    		$tableHtml .= '<td>' . $valHtml . '</td>';
    		
    		$tableHtml .= '</tr>';
    	}

    	$tableHtml .= '</tbody></table>';

    	$info = new stdClass();
    	$info->title = '$_SERVER';
    	$info->content = $tableHtml;

    	$response->setDataObject($info);
    }
}

?>