<?php

defined('_JEXEC') or die('Restricted access');

class ExjError {
    const TIPO_ERROR_NINGUNO = 0;
    const TIPO_ERROR_DESCONOCIDO = 99;
    const TIPO_ERROR_DATABASE=1;
    const TIPO_ERROR_FILE=3;
    const TIPO_ERROR_USERACCESS=4;
    const TIPO_ERROR_SERVICIOFTP=5;
    const TIPO_ERROR_VALIDINGDATA=6;
    const TIPO_ERROR_BUFFER=15;
    const TIPO_ERROR_EXCEPTION=16;
    const TIPO_ERROR_DELAYED=17;

	public $msgError;
	public $typeError;

	public function __construct($msg='') {
		$this->msgError = trim($msg);
		$this->typeError = (
			$this->msgError ? self::TIPO_ERROR_DESCONOCIDO : self::TIPO_ERROR_NINGUNO
		);
	}

	public function setMsg($value) {
		$this->msgError = $value;
		return $this;
	}

	public function setType($value) {
		$this->typeError = $value;
		return $this;
	}

	public function haveError() {
		return ($this->typeError != self::TIPO_ERROR_NINGUNO);
	}

	public function setMsgDatabase($value) {
		return $this->setMsg($value)->setType(self::TIPO_ERROR_DATABASE);
	}

	public function setMsgFile($msg) {
		return $this->setMsg($msg)->setType(self::TIPO_ERROR_FILE);
	}

	public function setMsgInvalidData($msg) {
		return $this->setMsg($msg)->setType(self::TIPO_ERROR_VALIDINGDATA);
	}

	public function setMsgException($value) {
		if ($value && !is_string($value)) {
			$value = $value->getMessage();
		}
		return $this->setMsg($value)->setType(self::TIPO_ERROR_EXCEPTION);
	}

	

	

	public function rendererMsg($returnErrorRaw = false) {
		$msg = '';
		if (!$this->haveError()) {
			return $msg;
		}

		$msg = self::GetTextTypeError($this->typeError, true, true);

        switch ($this->typeError) {
            case self::TIPO_ERROR_DESCONOCIDO:
                $msg .= ".<br/>Referencia: ";
                break;

            case self::TIPO_ERROR_VALIDINGDATA:
                $msg .= "<br/>";
                $returnErrorRaw = true;
                break;
        }

        $msg .= '<br/>';

        if ($returnErrorRaw) {
            $msg .= $this->msgError;
        } else {
            $msg .= "Ocurrieron errores internos en el sistema.<br/>Ha sido notificado a soporte sobre el error.";
            if (ExjUser::IsRolSuperAdmin()) {
                // echo $this->msgError;
                $msg .= '<br/>Referencia:<br/>' . $this->msgError;
            }
        }

		return $msg;
	}

	/**
     * Devuelve el tipo de error
     *
     * @param int $type
     */
    public static function GetTextTypeError($type, $toUpper = false, $addBold = false)
    {
        if ($type) {
            $type = intval($type);
        }
        else {
			$type = 0;
        }

        $textTypeError = $type;
        $color = '';
        switch ($type) {
            case self::TIPO_ERROR_BUFFER:
                $textTypeError = "Buffer";
                break;
            case self::TIPO_ERROR_DATABASE:
                $textTypeError = "Base de datos";
                $color = 'red';
                break;

            case self::TIPO_ERROR_NINGUNO:
                $textTypeError = "Ninguno";
                break;

            case self::TIPO_ERROR_VALIDINGDATA:
                $textTypeError = "Validando datos";
                $color = 'green';
                break;

            case self::TIPO_ERROR_FILE:
                $textTypeError = "Procesando Archivo";
                break;

            case self::TIPO_ERROR_EXCEPTION:
                $textTypeError = "Exception";
                $color = 'red';
                break;

            case self::TIPO_ERROR_DELAYED:
                $textTypeError = "Demora";
                $color = 'blue';
                break;


            case self::TIPO_ERROR_DESCONOCIDO:
                $textTypeError = "Desconocido";
                break;

            case self::TIPO_ERROR_USERACCESS:
                $textTypeError = "Acceso de Usuario";
                break;

            case self::TIPO_ERROR_SERVICIOFTP:
                $textTypeError = "Servicio FTP";
                break;


            case Exj::MSG_TIPO_ERROR:
                $textTypeError = "Error";
                $color = 'red';
                break;

            /*
              case self::MSG_TIPO_WARNING:
              $textTypeError = "Advertencia";
              break;
             */

            default:
                $textTypeError = "Error tipo $type Desconocido";
                break;
        }

        if ($toUpper) {
            $textTypeError = strtoupper($textTypeError);
        }
        if ($addBold) {
            $textTypeError = '<b>' . $textTypeError . '</b>';
        }
        if ($color) {
            $textTypeError = '<span style="color:' . $color . '">' . $textTypeError . '</span>';
        }

        return $textTypeError;
    }

    public static function GetLookupTypeError($toUpper = false)
    {
        $item = array();

        $item[] = self::GetDataTypeError(self::TIPO_ERROR_DATABASE, $toUpper);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_EXCEPTION);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_BUFFER);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_VALIDINGDATA);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_DELAYED);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_FILE);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_SERVICIOFTP);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_USERACCESS);
        $item[] = self::GetDataTypeError(self::TIPO_ERROR_NINGUNO);

        return $item;
    }

    public static function GetDataTypeError($typeError, $toUpper = false, $isTextHTML = false, $addBold = false)
    {
        $dataTypeError = new stdClass();
        $dataTypeError->value = intval($typeError);
        $dataTypeError->text = $dataTypeError->value;
        $dataTypeError->color = '';
        $dataTypeError->isCritical = false;
        $dataTypeError->showMsgRaw = false;

        switch ($dataTypeError->value) {
            case self::TIPO_ERROR_BUFFER:
                $dataTypeError->text = "Buffer";
                break;
            case self::TIPO_ERROR_DATABASE:
                $dataTypeError->text = "Base de datos";
                $dataTypeError->isCritical = true;
                break;

            case self::TIPO_ERROR_NINGUNO:
                $dataTypeError->text = "Ninguno";
                break;

            case self::TIPO_ERROR_VALIDINGDATA:
                $dataTypeError->text = "Validando datos";
                $dataTypeError->color = 'green';
                $dataTypeError->showMsgRaw = true;
                break;

            case self::TIPO_ERROR_FILE:
                $dataTypeError->text = "Procesando Archivo";
                break;

            case self::TIPO_ERROR_EXCEPTION:
                $dataTypeError->text = "Exception";
                $dataTypeError->isCritical = true;
                break;

            case self::TIPO_ERROR_DELAYED:
                $dataTypeError->text = "Demora";
                $dataTypeError->color = 'blue';
                $dataTypeError->showMsgRaw = true;
                break;

            case self::TIPO_ERROR_DESCONOCIDO:
                $dataTypeError->text = "Desconocido";
                break;

            case self::TIPO_ERROR_USERACCESS:
                $dataTypeError->text = "Acceso de Usuario";
                break;

            case self::TIPO_ERROR_SERVICIOFTP:
                $dataTypeError->text = "Servicio FTP";
                break;


            case Exj::MSG_TIPO_ERROR:
                $dataTypeError->text = "Error";
                $dataTypeError->isCritical = true;
                break;


            default:
                $dataTypeError->text = "Error desconocido tipo $dataTypeError->value";
                break;
        }

        if ($dataTypeError->isCritical) {
            $dataTypeError->color = 'red';
        }
        if ($dataTypeError->showMsgRaw && !$dataTypeError->color) {
            $dataTypeError->color = 'green';
        }

        if ($toUpper) {
            $dataTypeError->text = strtoupper($dataTypeError->text);
        }
        if ($addBold && $isTextHTML) {
            $dataTypeError->text = "<b>$dataTypeError->text</b>";
        }

        if ($isTextHTML && $dataTypeError->color) {
            $dataTypeError->text = "<span style='color:" . $dataTypeError->color . "'>$dataTypeError->text</span>";
        }

        return $dataTypeError;
    }

}

?>