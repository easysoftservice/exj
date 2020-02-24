<?php

class ExjSimpleXMLElement extends SimpleXMLElement {

	public function addCDATA($cData) {
		$node = dom_import_simplexml($this);
		$no = $node->ownerDocument;
		$node->appendChild($no->createCDATASection($cData));
	}

	public function getValueAttribute($nameAttr){
		return ((string) $this->attributes()->$nameAttr);
	}

	public function toString(){
		return ((string) $this);
	}

	public function getValue($prop, $applyTrim=true){
		$val = ((string) $this->$prop);
		if ($applyTrim && $val) {
			$val = trim($val);
		}
		
		return $val;
	}

	public function setValue($prop, $value){

		if (is_array($value) || 
			(is_object($value) && !($value instanceof SimpleXMLElement))) {

			$node = null;
			if ($prop !== '@attributes') {
				$node  = $this->addChild($prop);
			}
			
			foreach ($value as $key => $val) {
				if ($node !== null) {
					$nameNode = $key;
					if (is_numeric($key)) {
						$nameNode = substr($prop, 0, -1);
						if (!$nameNode) {
							$nameNode = 'item';
						}
					}

					$node->setValue($nameNode, $val);
				}
				else{
					$this->addAttribute($key, $val);
				}				
			}
		}
		else{
			$this->$prop = $value;
		}
		
		return $this;
	}

	public function getValueFloat($prop, $valDefault=0){
		$val = $this->getValue($prop);
		if ($val === '') {
			return $valDefault;
		}

		$valueFloat = floatval($val);
		if (is_nan($valueFloat)) {
			return $valDefault;
		}

		return $valueFloat;
	}

	public function getValueInt($prop, $valDefault=0){
		$val = $this->getValue($prop);
		if ($val === '') {
			return $valDefault;
		}

		$valueNum = intval($val);
		if (is_nan($valueNum)) {
			return $valDefault;
		}

		return $valueNum;
	}

	public function getValueFloatNullable($prop){
		return $this->getValueFloat($prop, null);
	}

	public function getValueMoneyNullable($prop, $decimals=2){
		$val = $this->getValueFloatNullable($prop);
		if ($val) {
			$val = round($val, $decimals);
		}

		return $val;
	}

	public function getObject($prop){
		$val = $this->$prop;
		if (!$val || $val === '') {
			return null;
		}

		return $val;
	}

	public static function RendererMoney($val, $decimals=2){
		if ($val === null || $val === '') {
			return '';
		}

		if (!is_numeric($val)) {
			return $val;
		}

		return sprintf("%.".$decimals."f", $val);
	}
	
}

?>