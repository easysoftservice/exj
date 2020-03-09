<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjBuildHtml {
	private $_tag;
	private $_attrs=null;
	private $_content='';

	public static function Create($tag) {
		return (new ExjBuildHtml($tag));
	}

	public static function CreateImg($src) {
		return (new ExjBuildHtml('img'))->addAttr('src', $src);
	}

    public static function GetLabelValue($label, $value) {
        return "<b>$label</b>: " . $value;
    }

    public function __construct($tag) {
    	$this->_tag = $tag;
    }

    public function setWidth($value) {
    	return $this->addAttr('width', $value);
    }

    public function setId($value) {
    	return $this->addAttr('id', $value);
    }

    public function setClass($value) {
        return $this->addAttr('class', $value);
    }

    public function addAttr($key, $value) {
    	if (!$this->_attrs) {
    		$this->_attrs = array();
    	}

    	$this->_attrs[$key] = $value;
    	return $this;
    }

    public function isDefinedAttr($key){
    	if (empty($this->_attrs)) {
    		return false;
    	}

    	return array_key_exists($key, $this->_attrs);
    }

    public function applyIfAttrs($values) {
    	return $this->applyAttrs($values, false);
    }

    public function applyAttrs($values, $override = true) {
    	if (empty($values)) {
    		return $this;
    	}

    	foreach ($values as $key => $value) {
    		if (is_numeric($key)) {
    			if (is_array($value)) {
    				$this->applyAttrs($value, $override);
    			}
    		}
    		else {
    			if (!$override && $this->isDefinedAttr($key)) {
    				continue;
    			}

    			$this->addAttr($key, $value);
    		}
    	}

    	return $this;
    }

    public function getStrAttrs() {
    	$value = '';
    	if (empty($this->_attrs)) {
    		return $value;
    	}

    	$value = array();
    	foreach ($this->_attrs as $key => $valAttr) {
    		if ($valAttr === false) {
    			$valAttr = 'false';
    		}

    		$value[] = $key.'="'.$valAttr.'"';
    	}

    	$value = implode(' ', $value);

    	return $value;
    }

    public function setContent($value, $addCnt = false) {
    	if ($value && is_object($value)) {
    		if ($value instanceof ExjBuildHtml) {
    			$value = $value->toHtml();
    		}
    	}
        elseif ($value === null) {
            $value = '';
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($item) {
                    $this->setContent($item, true);
                }
            }

            return $this;
        }

        if ($addCnt && isset($this->_content) && $this->_content) {
            $this->_content .= $value;
        }
        else {
            $this->_content = $value;
        }
    	
    	return $this;
    }

    public function setContentLabelValue($label, $value) {
        return $this->setContent(self::GetLabelValue($label, $value));
    }

    public function toHtml() {
    	$html = '<' . $this->_tag;
    	$attrs = $this->getStrAttrs();

    	if ($attrs) {
    		$html .= ' '.$attrs;
    	}

    	if ($this->_content) {
    		$html .= '>' . $this->_content . '</'.$this->_tag;
    	}
    	else {
    		if ($attrs) {
    			$html .= '/';
    		}
    	}

    	$html .= '>';

    	return $html;
    }
}

?>