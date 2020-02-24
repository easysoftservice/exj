<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIGridView {
	const CLS_ROW_ALERT = 'exj-rep-row-date-equal';
	const CLS_ROW_DANGER = 'exj-rep-row-date-minor';
	const CLS_ROW_DISABLED = 'exj-grid-row-inactivo';
	const CLS_ROW_ASSIGNED = 'exj-rep-row-date-mayor-assigned';

	public function setAutoFill($value = true){
    	$this->autoFill = $value;
    	return $this;
    }

    public function setEmptyText($value){
    	$this->emptyText = $value;
    	return $this;
    }

    public function setEnableRowBody($value = true){
    	$this->enableRowBody = $value;
    	return $this;
    }

    public function setForceFit($value = true){
    	$this->forceFit = $value;
    	return $this;
    }

    public function setMarkDirty($value = true){
    	$this->markDirty = $value;
    	return $this;
    }

    public function isDefinedProp($prop){
    	return (isset($this->$prop) ? true : false);
    }

    public function getForceFit() {
    	return (isset($this->forceFit) ? $this->forceFit : false);
    }
}

?>