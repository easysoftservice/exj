<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjReloadAppResponse {
	public $titleToShow;
	public $msgToShow='';

	public function __construct(){
		$this->setTitleToShow(Exj::GetTitleApp());
	}

	public function setTitleToShow($value){
		$this->titleToShow = $value;
		return $this;
	}

	public function setMsgToShow($value){
		$this->msgToShow = $value;
		return $this;
	}

	public function addMsgToShow($value){
		if ($this->msgToShow) {
			$this->msgToShow .= '<br/>';
		}
		$this->msgToShow .= $value;
		return $this;
	}

}


?>