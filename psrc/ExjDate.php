<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Fecha
 * Gestiona formatos de fechas.
 */
class ExjDate extends ExjObject {
    const FORMAT_TIME_DISPLAY = 'H\hi';
    const FORMAT_DATETIME_FROM_UI = 'Y-m-d\TH:i:s';
    const FORMAT_DATE_DB = 'Y-m-d';
    const FORMAT_DATE_SERVER = 'd/m/Y';
    const FORMAT_DATETIME_DB = 'Y-m-d H:i:s';
    const DIAS = array('Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado');
    const MESES = array('enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre');

    
    /*
      public static function ParseFromFormat($stData, $stFormat) {
      $aDataRet = array();
      $aPieces = split('[:/.\ \-]', $stFormat);
      $aDatePart = split('[:/.\ \-]', $stData);

      foreach($aPieces as $key=>$chPiece)
      {
      switch ($chPiece)
      {
      case 'd':
      case 'j':
      $aDataRet['day'] = $aDatePart[$key];
      break;

      case 'F':
      case 'M':
      case 'm':
      case 'n':
      $aDataRet['month'] = $aDatePart[$key];
      break;

      case 'o':
      case 'Y':
      case 'y':
      $aDataRet['year'] = $aDatePart[$key];
      break;

      case 'g':
      case 'G':
      case 'h':
      case 'H':
      $aDataRet['hour'] = $aDatePart[$key];
      break;

      case 'i':
      $aDataRet['minute'] = $aDatePart[$key];
      break;

      case 's':
      $aDataRet['second'] = $aDatePart[$key];
      break;
      }

      }
      return $aDataRet;
      }
     */

    /*
      public static function ChangeDateFormat($stDate, $stFormatFrom, $stFormatTo) {
      if (!$stDate) {
      return $stDate;
      }

      $date = self::ParseFromFormat($stDate, $stFormatFrom);
      return date($stFormatTo, mktime($date['hour'],
      $date['minute'],
      $date['second'],
      $date['month'],
      $date['day'],
      $date['year']));
      }
     */

    
    
    public static function ValidateFormat(&$fieldUI, &$strExpresion){
    	$strExpresion = '';
    	if (!$fieldUI) {
    		return $fieldUI;
    	}
    	
    	if (isset($fieldUI->format) && $fieldUI->format) {
    		// echo "<br>fieldUI->format: $fieldUI->format";
    		$posMarca = strpos($fieldUI->format, '+');
    		if ($posMarca === false) {
    			$posMarca = strpos($fieldUI->format, '-');
    		}
    		
    		if ($posMarca !== false) {
    			$strExpresion = substr($fieldUI->format, $posMarca);
    			$fieldUI->format = trim(substr($fieldUI->format, 0, $posMarca));
    			// echo "<br>strExpresion: $strExpresion fieldUI->format: $fieldUI->format";
    		}
    	}
    	
    	return $fieldUI;
    }

    public static function ConvertHoursMinsToLetters12H($nHoras, $nMin, $addStrHorMin=false) {
        $nHorasTradicional = ($nHoras > 12 ? $nHoras-12 : $nHoras);

        $strHorMin = self::ConvertHoursMinsToLetters24H($nHorasTradicional, $nMin, false);

        $strHorMin .= " de la ";
        if($nHoras <= 12) {
          if ($nHoras == 12) {
            $strHorMin .= 'mediodía';
            $strHorMin = str_replace('de la medio', 'del medio', $strHorMin);
          }
          else {
            $strHorMin .= ($nHoras <= 3 ? 'madrugada':'mañana');
          }
        }
        else{
          $strHorMin .= ($nHoras >= 19 ? 'noche':'tarde');
        }

        if ($addStrHorMin) {
          $strInfoTime = ($nHoras >= 12 ? 'p.m.':'a.m.');
          $strHorMin .= ' (' . self::RendererHourMin($nHorasTradicional, $nMin, ':') . " $strInfoTime)";
        }

        return $strHorMin;
    }

    public static function ConvertHoursMinsToLetters24H($nHoras, $nMin, $addStrHorMin=false) {
        $srtHora = Exj::ConvertNumberIntToLetters($nHoras, true);
        $srtMin = Exj::ConvertNumberIntToLetters($nMin, true);

        $lblHoras = 'hora';
        if ($nHoras > 1 || $nHoras == 0) {
          $lblHoras .= 's';
        }

        $lblMinutos = 'minuto';
        if ($nMin > 1 || $nMin == 0) {
          $lblMinutos .= 's';
        }

        $strHorMin = "$srtHora $lblHoras $srtMin $lblMinutos";
        
        $strHorMin = str_replace('uno minuto', 'un minuto', $strHorMin);
        $strHorMin = str_replace('uno hora', 'una hora', $strHorMin);

        if ($addStrHorMin) {
          $strHorMin .= ' (' . self::RendererHourMin($nHoras, $nMin) . ')';
        }

        return $strHorMin;
    }

    public static function RendererHourMin($hor, $min, $separator='h'){
      return (sprintf("%02s", $hor) . $separator . sprintf("%02s", $min));
    }
    
    public static function ConvertHorasMinToLetras($nHoras, $nMin, $strHorMin='', $toUpper = false, $allToLetras = true) {

    	$nHorasTradicional = ($nHoras > 12 ? $nHoras-12 : $nHoras);
  		if (!$strHorMin) {
  			$strHorMin = sprintf("%02s", $nHorasTradicional).'h' . sprintf("%02s", $nMin);
  		}
    	
    	if ($allToLetras) {
  			$srtHora = Exj::ConvertNumberIntToLetters($nHorasTradicional, !$toUpper);
  			$srtMin = Exj::ConvertNumberIntToLetters($nMin, !$toUpper);

        $lblHoras = 'hora';
        if ($nHoras > 1) {
          $lblHoras .= 's';
        }

        $lblMinutos = 'minuto';
        if ($nMin > 1) {
          $lblMinutos .= 's';
        }

  			$strHorMin = "$srtHora $lblHoras $srtMin $lblMinutos ($strHorMin)";
  			
        $strHorMin = str_replace('uno minuto', 'un minuto', $strHorMin);
  			$strHorMin = str_replace('uno hora', 'una hora', $strHorMin);
    	}
    	else{
    		$strHorMin = "($strHorMin)";
    	}
		
		  $strHorMin .= " de la ";
    	if($nHoras <= 12) {
    		if ($nHoras == 12) {
    			$strHorMin .= 'mediodía';
    			$strHorMin = str_replace('de la medio', 'del medio', $strHorMin);
    		}
    		else {
    			$strHorMin .= ($nHoras <= 3 ? 'madrugada':'mañana');
    		}
    	}
    	else{
    		$strHorMin .= ($nHoras >= 19 ? 'noche':'tarde');
    	}
    	
    	if ($toUpper) {
    		$strHorMin = strtoupper($strHorMin);
    	}
	
    	return $strHorMin;
    }
    
    public static function ConvertToTimeLetras($stTime = '', $toUpper = true, $allToLetras = true) {
    	if (!$stTime) {
    		$stTime = date("Y-m-d H:i");
    	}
    	
    	$hora = date('H', strtotime($stTime));
    	$min = date('i', strtotime($stTime));
    	return self::ConvertHorasMinToLetras($hora, $min, '', $toUpper, $allToLetras);
    }

    /**
     * Convierte una fecha a letras
     *
     * @param string $stDate
     * @param bool $toUpper
     * @param bool $allToLetras
     * @return string
     */
    public static function ConvertToDateLetras($stDate = '', $toUpper = true, $allToLetras = true) {
        if (!$stDate) {
            $stDate = date("Y-m-d");
        }

       /* $stDate = "1976-02-10"; */
        // date('l jS \d\e F Y')
        $dia = self::ConvertToDateDia($stDate);
        $mes = self::ConvertToDateMes($stDate);
        $anio = date("Y", strtotime($stDate));
        $num = date("j", strtotime($stDate));
        
        if ($allToLetras) {
            $num = Exj::ConvertNumberIntToLetters($num, !$toUpper);
            $anio = Exj::ConvertNumberIntToLetters($anio, !$toUpper);
        }

        if (!$toUpper) {
            $mes = ucfirst($mes);
        }
        
        $dateLetras = "$dia, $num de $mes del año $anio";
       // echo "<br>".__FUNCTION__." $stDate  dateLetras: " . htmlentities($dateLetras);
        
        // echo "<br>$stDate --> $dateLetras";

        /*
        $STR_DIAS = ' ';
        if (strtolower($num) == 'un') {
            $STR_DIAS .= 'día';
        } else {
            $STR_DIAS .= 'días';
        }

        $STR_DIAS .= ' del mes de';
        $STR_DIAS .= ' ';

        $STR_ANIO = 'del año';
        if ($toUpper) {
            $STR_ANIO = 'DEL AÑO';
        }

        $dateLetras = $dia . ', ' . $num . $STR_DIAS . $mes . " $STR_ANIO " . $anio;
        */
        
        if ($toUpper) {
            $dateLetras = strtoupper($dateLetras);
        }

        $dateLetras = str_replace("  ", ' ', $dateLetras);

        return $dateLetras;
    }

    public static function ConvertToDateMes($stDate) {
        $meses = self::MESES;
        return $meses[date("m", strtotime($stDate) * 1) - 1];
    }

    public static function ConvertToDateDia($stDate) {
        $dias = self::DIAS;
        return $dias[date("w", strtotime($stDate))];
    }

    public static function ConvertDateBetweenDisplay_DB($stDate) {
        $stDate = preg_replace("/(\d+)\D+(\d+)\D+(\d+)/", "$3-$2-$1", $stDate);
        return $stDate;
    }

    private static function &_GetDateFormatFromDate($stDate) {
        $dateFormat = null;
        if (!$stDate) {
            return $dateFormat;
        }

        $dateFormat = DateTime::createFromFormat(
          Exj::GetValueCfg('uiFormatDateDef'), $stDate
        );
        
        if ($dateFormat) {
            return $dateFormat;
        }

        $dateFormat = DateTime::createFromFormat(self::FORMAT_DATE_DB, $stDate);
        if ($dateFormat) {
            return $dateFormat;
        }

        $dateFormat = DateTime::createFromFormat(self::FORMAT_DATETIME_FROM_UI, $stDate);
        if ($dateFormat) {
            return $dateFormat;
        }

        $dateFormat = DateTime::createFromFormat(self::FORMAT_DATE_SERVER, $stDate);
        if ($dateFormat) {
            return $dateFormat;
        }

        $dateFormat = DateTime::createFromFormat(self::FORMAT_DATETIME_DB, $stDate);
        if ($dateFormat) {
            return $dateFormat;
        }

        echo "<br/>" . __CLASS__ . " Advertencia: Formato desconocido con la fecha: <b>$stDate</b>";
        debug_print_backtrace();

        $dateFormat = new DateTime($stDate);
        return $dateFormat;
    }

    /**
     * Convierte la fecha en formato para la base de datos
     *
     * @param string $stDate
     * @return string Fecha con formato Y-m-d
     */
    public static function ConvertToDateDB($stDate) {
        if (!$stDate || $stDate == 'null') {
            return $stDate;
        }

        $dateTimeFormat = self::_GetDateFormatFromDate($stDate);

        $stDate = self::_FormatDateFromDateTime($dateTimeFormat, self::FORMAT_DATE_DB, $stDate);
        return $stDate;
    }

    /**
     * Convierte la fecha a un formato personalizado, Y=año m=mes d=dia
     * @param string $stDate
     * @param string $format
     * @return string
     */
    public static function ConvertToDateCustom($stDate, $format) {
        if (!$stDate || $stDate == 'null') {
            return $stDate;
        }

        $dateTimeFormat = self::_GetDateFormatFromDate($stDate);

        $stDate = self::_FormatDateFromDateTime($dateTimeFormat, $format, $stDate);
        return $stDate;
    }

    /**
     * Convierte la fecha a un formato para la presentación
     *
     * @param string $stDate
     * @return string
     */
    public static function ConvertToDateDisplay($stDate) {
        if (!$stDate || $stDate == 'null') {
            return $stDate;
        }

        $dateTimeFormat = DateTime::createFromFormat(self::FORMAT_DATE_DB, $stDate);
        if (!$dateTimeFormat) {
            $dateTimeFormat = self::_GetDateFormatFromDate($stDate);
        }

        $stDate = self::_FormatDateFromDateTime(
          $dateTimeFormat, Exj::GetValueCfg('uiFormatDateDef'), $stDate
        );

        return $stDate;
    }

    public static function ConvertToDateDisplay2($stDate) {
        if (!$stDate) {
            return $stDate;
        }

        $dateTimeFormat = DateTime::createFromFormat(self::FORMAT_DATE_DB, $stDate);
        if (!$dateTimeFormat) {
            $dateTimeFormat = self::_GetDateFormatFromDate($stDate);
        }


        $stDate = self::_FormatDateFromDateTime(
          $dateTimeFormat, Exj::GetValueCfg('uiFormatDateShort'), $stDate
        );

        return $stDate;
    }
    
    public static function ConvertToTimeDisplay($stDate) {
        if (!$stDate) {
            return $stDate;
        }

        $dateTimeFormat = DateTime::createFromFormat(self::FORMAT_DATE_DB, $stDate);
        if (!$dateTimeFormat) {
            $dateTimeFormat = self::_GetDateFormatFromDate($stDate);
        }

        $stDate = self::_FormatDateFromDateTime($dateTimeFormat, self::FORMAT_TIME_DISPLAY, $stDate);
        return $stDate;
    }
    

    /**
     * Convierte la fecha y hora a un formato para la presentación
     *
     * @param string $stDate
     * @return string
     */
    public static function ConvertToDateTimeDisplay($stDate) {
        //	echo '<br/>'.__METHOD__." stDate: $stDate";
        if (!$stDate) {
            return $stDate;
        }
        
        // ver si está ya convertido
        if (strlen($stDate) == 16 && strpos($stDate, '/') !== false) {
        	return $stDate;
        }

        $dateTimeFormat = DateTime::createFromFormat(self::FORMAT_DATETIME_DB, $stDate);
        if (!$dateTimeFormat) {
            $dateTimeFormat = self::_GetDateFormatFromDate($stDate);
        }

        $stDate = self::_FormatDateFromDateTime(
          $dateTimeFormat, Exj::GetValueCfg('uiFormatDatetimeDef'), $stDate
        );
        
        return $stDate;
    }

    public static function ConvertToDateTimeDisplay2($stDate) {
        //	echo '<br/>'.__METHOD__." stDate: $stDate";
        if (!$stDate) {
            return $stDate;
        }

        $dateTimeFormat = DateTime::createFromFormat(self::FORMAT_DATETIME_DB, $stDate);
        if (!$dateTimeFormat) {
            $dateTimeFormat = self::_GetDateFormatFromDate($stDate);
        }

        $stDate = self::_FormatDateFromDateTime(
          $dateTimeFormat, Exj::GetValueCfg('uiFormatDatetimeShort'), $stDate
        );

        return $stDate;
    }

    /**
     * Convierte la fecha y hora a un formato para la db
     *
     * @param string $stDate
     * @return string
     */
    public static function ConvertToDateTimeDB($stDate) {
        if (!$stDate) {
            return $stDate;
        }

        $dateTimeFormat = DateTime::createFromFormat(self::FORMAT_DATETIME_FROM_UI, $stDate);
        if (!$dateTimeFormat) {
            $dateTimeFormat = self::_GetDateFormatFromDate($stDate);
        }

        $stDate = self::_FormatDateFromDateTime($dateTimeFormat, self::FORMAT_DATETIME_DB, $stDate);
        return $stDate;
    }

    private static function _FormatDateFromDateTime(DateTime $dateTime, $FORMAT, $dateDefault = '') {
        $stDate = $dateDefault;

        if ($dateTime) {
            $stDate = $dateTime->format($FORMAT);
        } else {
            global $exj;
            $exj->setErrorValidating("Fecha: <b>$stDate</b> no tiene el formato esperado. Ref: " . __CLASS__);
            static $showDebugFromDate;
            if (!isset($showDebugFromDate)) {
                $showDebugFromDate = true;
                debug_print_backtrace();
            }
        }

        return $stDate;
    }

    public static function AddDays($fecha, $numDias = 1, $format = "d/m/Y") {
        return date($format, strtotime("$fecha +$numDias day"));
    }

    public static function RendererDateForDB($strFecha, $format='Y-m-d') {
      return self::RendererDateDB($strFecha, $format);
    }

    public static function RendererDateDB($strFecha, $format="d/m/Y") {
      if (!$strFecha) {
        // renderer tiene q retornar string
        return '';
      }

      return date($format, strtotime($strFecha));
    }

    public static function RendererDayMonDateDB($strFecha) {
      return self::RendererDateDB($strFecha, 'd-m');
    }

    public static function GetDateCurrent($format='Y-m-d') {
      return date($format);
    }

}

?>