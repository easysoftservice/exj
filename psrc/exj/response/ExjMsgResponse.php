<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjMsgResponse {
	public $title;
	public $text;
	public $type;

	public function __construct() {
		$this->setTitle('')->setText('')->setType(Exj::MSG_TIPO_NINGUNO);
	}

	public function setTitle($value) {
		if (strtoupper($value) == 'ERROR') {
			$value = ExjText::__($value);
		}
		else {
			$value = ExjText::_($value);
		}

		$this->title = $value;
		return $this;
	}

	public function setText($value) {
		if (is_array($value)) {
            $value = implode('<br>', $value);
        }

		$this->text = $value;
		return $this;
	}

	public function setType($value) {
		$this->type = $value;
		return $this;
	}

	public function addToText($msgExtra, $strConcat='<br>') {
		if (empty($msgExtra)) {
            return $this;
        }

        if (is_array($msgExtra)) {
            $msgExtra = implode($strConcat, $msgExtra);
        }

        if ($this->text) {
            $this->text .= $strConcat . $msgExtra;
        }
        else{
            $this->text = $msgExtra;
        }

		return $this;
	}

	public function isTypeError() {
		return ($this->type == Exj::MSG_TIPO_ERROR);
	}

	public function haveText() {
		return ($this->text ? true : false);
	}
}

?>