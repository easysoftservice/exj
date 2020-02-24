<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjBuildSql {
	private $_select='';
	private $_from='';
	private $_where='';
	private $_groupBy='';
	private $_limit=0;
	private $_orderBy='';

	public static function Create() {
		return (new ExjBuildSql());
	}

	public function reset() {
		$this->_select = '';
		$this->_from = '';
		$this->_where = '';
		$this->_groupBy = '';
		$this->_orderBy = '';
		$this->_limit = 0;
		return $this;
	}

	public function select($value) {
		if (is_array($value)) {
			$value = implode(',', $value);
		}

		$this->_select = $value;
		return $this;
	}

	public function from($value) {
		if (is_array($value)) {
			$value = implode(' ', $value);
		}

		$this->_from = $value;
		return $this;
	}

	public function where($value, $operator='AND') {
		if (is_array($value)) {
			$value = implode(" $operator ", $value);
		}

		$this->_where = $value;
		return $this;
	}

	public function groupBy($value) {
		if (is_array($value)) {
			$value = implode(',', $value);
		}

		$this->_groupBy = $value;
		return $this;
	}

	public function orderBy($value) {
		if (is_array($value)) {
			$value = implode(',', $value);
		}

		$this->_orderBy = $value;
		return $this;
	}

	

	public function limit($value) {
		$this->_limit = $value;
		return $this;
	}

	public function toSql() {
		$value = array();
		$value[] = 'SELECT '. ($this->_select ? $this->_select : '*');
		$value[] = 'FROM '.$this->_from;

		if ($this->_where) {
			$value[] = 'WHERE '.$this->_where;
		}

		if ($this->_groupBy) {
			$value[] = 'GROUP BY '.$this->_groupBy;
		}

		if ($this->_orderBy) {
			$value[] = 'ORDER BY '.$this->_orderBy;
		}

		if ($this->_limit) {
			$value[] = 'LIMIT '.$this->_limit;
		}

		$value = implode(' ', $value);
		return $value;
	}


}

?>