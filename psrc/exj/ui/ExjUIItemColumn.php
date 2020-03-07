<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIItemColumn {

    public static function Create() {
        return new ExjUIItemColumn();
    }

    public function setColumnWidth($value) {
        $this->columnWidth = $value;
        return $this;
    }

    public function setWidth($value) {
        $this->width = $value;
        return $this;
    }

    public function setTitle($value) {
        $this->title = $value;
        return $this;
    }

    public function setItems($value) {
        $this->items = $value;
        return $this;
    }

    public function setHtml($value) {
        $this->html = $value;
        return $this;
    }

    


}

?>