<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjUtilTableCol extends ExjObject {
    public $value = '';
    public $align = null;
    public $isfontBold = false;
    public $fontSize = 0;
    public $colspan;
    public $rowspan;
    public $color = '';
    public $width = null;
    public $unitSize = 'px';
    public $isTitle = false;
    public $isLabel = false;
    public $isHeader = false;
    public $isTypeFloat = false;

    public function __construct($value = '', $align = null, $isfontBold = false, $colspan = 0, $rowspan = 0, $fontSize = 0, $color = '') {
        $this->value = $value;
        $this->align = $align;
        $this->isfontBold = $isfontBold;
        $this->colspan = $colspan;
        $this->rowspan = $rowspan;
        $this->fontSize = $fontSize;
        $this->color = $color;
    }

}
?>