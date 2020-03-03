<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjDataTopicsResponse {
	public $topics;
	public $total;

	public function __construct() {
		$this->setTopics(array())->setTotal(-1);
	}

	public function setTopics($value) {
		$this->topics = $value;
		return $this;
	}

	public function setTotal($value) {
		$this->total = $value;
		return $this;
	}

	public function setItems($items, $total = -1) {
		if ($total < 0) {
			if (empty($items)) {
				$total = 0;
			}
			else {
				$total = count($items);
			}
		}

		return $this->setTopics($items)->setTotal($total);
	}

	public function getTotalNormalized() {
		return ($this->total < 0 ? 0 : $this->total);
	}

	public function addPropOrd() {
		if (empty($this->topics) || !is_array($this->topics)) {
			return $this;
		}

		$numOrd = 1;
    	foreach ($this->topics as &$item) {
    		if (isset($item->isData) || isset($item->isHeader)) {
    			if (isset($item->isData) && !$item->isData) {
    				$numOrd = 0;
    			}
    			elseif (isset($item->isHeader) && $item->isHeader) {
    				$numOrd = 0;
    			}
    		}
    		
    		$item->_ord = $numOrd;
    		$numOrd += 1;
    	}

		return $this;
	}
}

?>