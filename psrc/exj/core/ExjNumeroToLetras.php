<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjNumeroToLetras {
    private $_void = "";
    private $_sp = " ";
    private $_dot = ".";
    private $_zero = "0";
    private $_neg = "Menos";
    
    /**
     * Obtiene instancia de la clase ExjNumeroToLetras
     * @return ExjNumeroToLetras
     */
    public static function &GetInstance(){
        static $instanceNumLetras;
        if(!isset($instanceNumLetras)){
            $instanceNumLetras = new ExjNumeroToLetras();
        }
        
        return $instanceNumLetras;
    }
    
    public static function ConvertirIntPorValor($valor, $toLower = false) {
    	return Exj::ConvertNumberIntToLetters($valor, $toLower);
    }

    /**
     * Convierte a letra por cada cifra o número
     *
     * @param string $valor
     * @return string
     */
    public static function ConvertirPorCifra($valor) {
        if ($valor === '' || $valor === null) {
            return '';
        }

        if (is_numeric($valor)) {
            $valor .= '';
        }

        $letras = array();
        for ($i = 0; $i < strlen($valor); $i++) {
            $strNum = $valor[$i];
            if ($strNum === ' ') {
                continue;
            }

            switch ($strNum) {
                case '0':
                    $letras[] = 'cero';
                    break;

                case '1':
                    $letras[] = 'uno';
                    break;

                case '2':
                    $letras[] = 'dos';
                    break;

                case '3':
                    $letras[] = 'tres';
                    break;

                case '4':
                    $letras[] = 'cuatro';
                    break;

                case '5':
                    $letras[] = 'cinco';
                    break;

                case '6':
                    $letras[] = 'seis';
                    break;

                case '7':
                    $letras[] = 'siete';
                    break;

                case '8':
                    $letras[] = 'ocho';
                    break;

                case '9':
                    $letras[] = 'nueve';
                    break;
                case '-':
                case '_':
                    $letras[] = 'guion';
                    break;

                case '.':
                    $letras[] = 'punto';
                    break;
                case ',':
                    $letras[] = 'coma';
                    break;


                default:
                    $letras[] = $strNum;
                    break;
            }
        }

        $letras = implode(' ', $letras);
        return $letras;
    }

    public static function ParseNumber($strNumero) {
        if ($strNumero === null) {
            return $strNumero;
        }

        /* comprobar si tiene , */
        if ($strNumero && preg_match('/[\,]|[\.]/', $strNumero)) {
        //    echo "<br><b>ParseNumber</b>. TIENE COMA o PUNTO: $strNumero";
            
            $posPunto = strpos($strNumero, '.');
            $posComa = strpos($strNumero, ',');
            
            $tienePunto = ($posPunto !== false);
            $tieneComa = ($posComa !== false);
            
          	if ($tienePunto) {
            	$strNumero = str_replace('..', '.', $strNumero);
            }
            
            if ($tieneComa) {
            	$strNumero = str_replace(',,', ',', $strNumero);
            }
            
            if ($tienePunto && $tieneComa) {
                if ($posPunto < $posComa) {
                    $strNumero = str_replace('.', '', $strNumero);
                    $strNumero = str_replace(',', '.', $strNumero);
                    // echo " TIENE . LUEGO ,";
                } else {
                    $strNumero = str_replace(',', '', $strNumero);
                    // echo " TIENE , LUEGO .";
                }
            } else {
            	if ($tieneComa) {
            		$strNumero = str_replace(',', '.', $strNumero);
            	}
                // echo " TIENE SOLO ,";
            }
            
            $posPunto = strpos($strNumero, '.');
            $tienePunto = ($posPunto !== false);
            
            if ($tienePunto) {
            	$lastPunto = strrpos($strNumero, '.');
            	$nDec = substr($strNumero, $lastPunto+1);
	        	$carsDec = strlen($nDec);
	        	if ($posPunto != $lastPunto || $carsDec != 2) {
	        		if ($carsDec <= 2){
	        			// caso: 47.206.00 ó 47.206.3
		        		$parteEntera = substr($strNumero, 0, $lastPunto);
		   //     		echo " CASO ESPECIAL 2 ULTIMOS DEC. parteEntera: $parteEntera parteDec: $nDec";
		        		$parteEntera = str_replace('.', '', $parteEntera);
		        		$strNumero = $parteEntera . '.' . $nDec;
	        		}
	        		elseif ($carsDec == 3){
	        			$strNumero = str_replace('.', '', $strNumero);
	        //			echo " CASO ESPECIAL 3 ULTIMOS DEC";
	        		}
	        		else{
	        			// caso: 15.1562 ó 3.526.2632
	        			if ($posPunto != $lastPunto) {
	        				// caso: 3.526.2632
	        				$parteEntera = substr($strNumero, 0, $lastPunto);
			  //      		echo " CASO ESPECIAL MAS DE UN PUNTO. parteEntera: $parteEntera parteDec: $nDec";
			        		$parteEntera = str_replace('.', '', $parteEntera);
			        		$strNumero = $parteEntera . '.' . $nDec;
	        			}
	        		}
	        	}
	        	/* else es caso normal */
            }
            
       	//	echo " <b>NUMERO</b>: $strNumero";
        }

        return $strNumero;
    }

    /**
     * Convierte número en letras
     *
     * @param int $x
     * @param bool $conDecimales
     * @param string $moneda
     * @param int $formatoMoneda
     * @return string
     */
    public function convertir($x, $conDecimales = false, $moneda = '', $formatoMoneda = 0) {
        if ($x === '' || $x === null) {
            return '';
        }

        $s = "";
        $Ent = "";
        $Frc = "";
        $Signo = "";

        $x = self::ParseNumber($x);

        if (floatval($x) < 0) {
            $Signo = $this->_neg . " ";
        } else {
            $Signo = "";
        }

        if (intval(@number_format($x, 2, '.', '')) != $x) {
            $s = number_format($x, 2, '.', '');
            if ($s != $x) {
                echo "<br>POSIBLE ERROR DE CONVERSION DE $x RES: $s";
            }
        } else {
            $s = number_format($x, 2, '.', '');
        }
        
     //   echo "<br><b>convertir</b>: s: $s x: $x";

        $Pto = strpos($s, $this->_dot);
        $tienePunto = ($Pto !== false);

        if ($tienePunto) {
            $Ent = substr($s, 0, $Pto);
            $Frc = substr($s, $Pto + 1);
        	
        } else {
            $Ent = $s;
            $Frc = $this->_void;
        }

        if ($Ent == $this->_zero || $Ent == $this->_void) {
            $s = "Cero ";
        } elseif (strlen($Ent) > 7) {
            $s = $this->subValLetra(intval(substr($Ent, 0, strlen($Ent) - 6))) .
                    "Millones " . $this->subValLetra(intval(substr($Ent, -6, 6)));
        } else {
            $s = $this->subValLetra(intval($Ent));
        }

        if (substr($s, -9, 9) == "Millones " || substr($s, -7, 7) == "Millón ") {
            $s = $s . "de ";
        }

        /* add bco. 05/07/2016 */
        if (strlen($s) >= 6) {
            if (substr($s, 0, 6) == "Un Mil") {
                $s = substr($s, 3);
            }

            $s = str_replace(
                    array("idos", "itres", "iseis", "iun"), array("idós", "itrés", "iséis", "iún"), $s);
        }

      /*  echo "<br>letras: $s"; */

        $s = $s . $moneda;
        $s = trim($s);

        if ($conDecimales && ($Frc != $this->_void)) {
            $addLblCentavos = true;
            if ($moneda && $formatoMoneda == 1) {
                $s = $s . " con " . $this->convertir($Frc, false);
            } else {
                /* por defecto */
                if (strpos($x, '.') !== false) {
                	$s = $s . " con " . $Frc . "/100";
                }
                else{
                    $addLblCentavos = false;
                }
            }

            if ($addLblCentavos && $moneda) {
                $s .= ' '. ($Frc == 1 ? 'centavo':'centavos') ;
            }
        }

        $retLetras = $Signo . $s;

        if ($moneda && strlen($moneda) > 3 && $moneda === strtoupper($moneda)) {
            $retLetras = strtoupper($retLetras);
        }

        if (stripos($retLetras, 'un dolares') !== false) {
            $retLetras = str_replace('un dolares', 'un dolar', $retLetras);
            $retLetras = str_replace('UN DOLARES', 'UN DOLAR', $retLetras);
        }

        return $retLetras;
    }

    public function subValLetra($numero) {
        $Ptr = "";
        $n = 0;
        $i = 0;
        $x = "";
        $Rtn = "";
        $Tem = "";

        $x = trim("$numero");
        $n = strlen($x);

        $Tem = $this->_void;
        $i = $n;

        while ($i > 0) {
            $Tem = $this->parte(intval(substr($x, $n - $i, 1) .
                            str_repeat($this->_zero, $i - 1)));
            if ($Tem != "Cero")
                $Rtn .= $Tem . $this->_sp;
            $i = $i - 1;
        }


        //--------------------- GoSub FiltroMil ------------------------------
        $Rtn = str_replace(" Mil Mil", " Un Mil", $Rtn);
        while (1) {
            $Ptr = strpos($Rtn, "Mil ");
            if (!($Ptr === false)) {
                if (!(strpos($Rtn, "Mil ", $Ptr + 1) === false )) {
                    self::ReplaceStringFrom($Rtn, "Mil ", "", $Ptr);
                } else {
                    break;
                }
            } else {
                break;
            }
        }

        //--------------------- GoSub FiltroCiento ------------------------------
        $Ptr = -1;
        do {
            $Ptr = strpos($Rtn, "Cien ", $Ptr + 1);
            if (!($Ptr === false)) {
                $Tem = substr($Rtn, $Ptr + 5, 1);
                if ($Tem == "M" || $Tem == $this->_void)
                    ;
                else
                    self::ReplaceStringFrom($Rtn, "Cien", "Ciento", $Ptr);
            }
        } while (!($Ptr === false));

        //--------------------- FiltroEspeciales ------------------------------
        $Rtn = str_replace("Diez Un", "Once", $Rtn);
        $Rtn = str_replace("Diez Dos", "Doce", $Rtn);
        $Rtn = str_replace("Diez Tres", "Trece", $Rtn);
        $Rtn = str_replace("Diez Cuatro", "Catorce", $Rtn);
        $Rtn = str_replace("Diez Cinco", "Quince", $Rtn);
        $Rtn = str_replace("Diez Seis", "Dieciseis", $Rtn);
        $Rtn = str_replace("Diez Siete", "Diecisiete", $Rtn);
        $Rtn = str_replace("Diez Ocho", "Dieciocho", $Rtn);
        $Rtn = str_replace("Diez Nueve", "Diecinueve", $Rtn);
        $Rtn = str_replace("Veinte Un", "Veintiun", $Rtn);
        $Rtn = str_replace("Veinte Dos", "Veintidos", $Rtn);
        $Rtn = str_replace("Veinte Tres", "Veintitres", $Rtn);
        $Rtn = str_replace("Veinte Cuatro", "Veinticuatro", $Rtn);
        $Rtn = str_replace("Veinte Cinco", "Veinticinco", $Rtn);
        $Rtn = str_replace("Veinte Seis", "Veintiséis", $Rtn);
        $Rtn = str_replace("Veinte Siete", "Veintisiete", $Rtn);
        $Rtn = str_replace("Veinte Ocho", "Veintiocho", $Rtn);
        $Rtn = str_replace("Veinte Nueve", "Veintinueve", $Rtn);

        //--------------------- FiltroUn ------------------------------
        if (substr($Rtn, 0, 1) == "M") {
            $Rtn = "Un " . $Rtn;
        }

        //--------------------- Adicionar Y ------------------------------
        for ($i = 65; $i <= 88; $i++) {
            if ($i != 77)
                $Rtn = str_replace("a " . Chr($i), "* y " . Chr($i), $Rtn);
        }
        $Rtn = str_replace("*", "a", $Rtn);
        return($Rtn);
    }

    public static function ReplaceStringFrom(&$x, $OldWrd, $NewWrd, $Ptr) {
        $x = substr($x, 0, $Ptr) . $NewWrd . substr($x, strlen($OldWrd) + $Ptr);
    }

    public function parte($x) {
        $Rtn = '';
        $t = '';
        $i = '';
        do {
            switch ($x) {
                Case 0: $t = "Cero";
                    break;
                Case 1: $t = "Un";
                    break;
                Case 2: $t = "Dos";
                    break;
                Case 3: $t = "Tres";
                    break;
                Case 4: $t = "Cuatro";
                    break;
                Case 5: $t = "Cinco";
                    break;
                Case 6: $t = "Seis";
                    break;
                Case 7: $t = "Siete";
                    break;
                Case 8: $t = "Ocho";
                    break;
                Case 9: $t = "Nueve";
                    break;
                Case 10: $t = "Diez";
                    break;
                Case 20: $t = "Veinte";
                    break;
                Case 30: $t = "Treinta";
                    break;
                Case 40: $t = "Cuarenta";
                    break;
                Case 50: $t = "Cincuenta";
                    break;
                Case 60: $t = "Sesenta";
                    break;
                Case 70: $t = "Setenta";
                    break;
                Case 80: $t = "Ochenta";
                    break;
                Case 90: $t = "Noventa";
                    break;
                Case 100: $t = "Cien";
                    break;
                Case 200: $t = "Doscientos";
                    break;
                Case 300: $t = "Trescientos";
                    break;
                Case 400: $t = "Cuatrocientos";
                    break;
                Case 500: $t = "Quinientos";
                    break;
                Case 600: $t = "Seiscientos";
                    break;
                Case 700: $t = "Setecientos";
                    break;
                Case 800: $t = "Ochocientos";
                    break;
                Case 900: $t = "Novecientos";
                    break;
                Case 1000: $t = "Mil";
                    break;
                Case 1000000: $t = "Millón";
                    break;
            }

            if ($t == $this->_void) {
                $i = $i + 1;
                $x = $x / 1000;
                if ($x == 0)
                    $i = 0;
            } else
                break;
        } while ($i != 0);

        $Rtn = $t;
        Switch ($i) {
            Case 0: $t = $this->_void;
                break;
            Case 1: $t = " Mil";
                break;
            Case 2: $t = " Millones";
                break;
            Case 3: $t = " Billones";
                break;
        }

        return($Rtn . $t);
    }

}
?>