<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjUtilImage extends ExjObject {
    public $isImage = true;
    public $src;
    public $height;
    public $width;
    public $alt = '';
    public $unitSize = 'px';
    public $offsetX = null;
    public $offsetY = null;

    public function __construct($src, $height = null, $width = null, $alt = '') {
        $this->src = $src;
        $this->height = $height;
        $this->width = $width;
        $this->alt = $alt;
    }

}
?>