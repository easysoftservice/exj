<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjColumnReport {
	const ALIGN_CENTER = 'center';
	const ALIGN_RIGHT = 'right';
	const ALIGN_LEFT = 'left';

	const FORMAT_STRING = 'string';
	const FORMAT_DATE = 'date';
	const FORMAT_DATETIME = 'datetime';
	const FORMAT_INT = 'int';
	const FORMAT_FLOAT = 'float';
	const FORMAT_BOOLYESNO = 'boolyesno';

	public $dataIndex;
	public $header='';
	public $posIndexCol=0;
	public $widthRaw=0;
	public $width=0;
	public $type;
	public $isCalc=false;
	public $align;
	private $_isWidthFixed=false;

	public function __construct($dataIndex){
		$this->dataIndex = $dataIndex;

		// por defecto
		return $this->setAlign(self::ALIGN_CENTER)
			->setTypeString()
			->setHeader($dataIndex);
	}

	public static function Create($dataIndex){
		return new self($dataIndex);
	}

	public function setHeader($value){
		$this->header = $value;
		return $this;
	}

	public function setIsCalc($value=true){
		$this->isCalc = $value;
		return $this;
	}

	public function setType($value){
		$value = trim($value);
		if ($value) {
			$value = strtolower($value);
		}

		$this->type = $value;
		return $this;
	}

	public function setPosIndexCol($value){
		$this->posIndexCol = $value;
		return $this;
	}

	public function setWidthRaw($value){
		$this->widthRaw = $value;
		if (!$this->width) {
			$this->width = $value;
		}

		return $this;
	}

	public function setWidth($value){
		$this->width = $value;
		return $this;
	}

	public function setAlign($value){
		$this->align = $value;
		return $this;
	}

	public function setAlignCenter(){
		return $this->setAlign(self::ALIGN_CENTER);
	}

	public function setAlignLeft(){
		return $this->setAlign(self::ALIGN_LEFT);
	}
	
	public function setAlignRight(){
		return $this->setAlign(self::ALIGN_RIGHT);
	}

	public function isAlignCenter(){
		return ($this->align == self::ALIGN_CENTER);
	}
	public function isAlignRight(){
		return ($this->align == self::ALIGN_RIGHT);
	}
	public function isAlignLeft(){
		return ($this->align == self::ALIGN_LEFT);
	}





	public function setTypeString(){
		return $this->setType(self::FORMAT_STRING);
	}
	public function setTypeDate(){
		return $this->setType(self::FORMAT_DATE);
	}
	public function setTypeDateTime(){
		return $this->setType(self::FORMAT_DATETIME);
	}
	public function setTypeInt(){
		return $this->setType(self::FORMAT_INT);
	}
	public function setTypeFloat(){
		return $this->setType(self::FORMAT_FLOAT);
	}
	public function setTypeBool(){
		return $this->setType(self::FORMAT_BOOLYESNO);
	}

	public function isTypeString(){
		return ($this->type == self::FORMAT_STRING);
	}
	public function isTypeBool(){
		return ($this->type == self::FORMAT_BOOLYESNO);
	}
	public function isTypeFloat(){
		return ($this->type == self::FORMAT_FLOAT);
	}
	public function isTypeInt(){
		return ($this->type == self::FORMAT_INT);
	}
	public function isTypeDateTime(){
		return ($this->type == self::FORMAT_DATETIME);
	}
	public function isTypeDate(){
		return ($this->type == self::FORMAT_DATE);
	}

	public function isWidthFixed(){
		return $this->_isWidthFixed;
	}

	public function setIsWidthFixed($value=true){
		$this->_isWidthFixed = $value;
		return $this;
	}

	public function setWidthFixed($value){
		return $this->setWidth($value)->setIsWidthFixed();
	}

}

?>