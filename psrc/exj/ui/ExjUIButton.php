<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIButton extends ExjUIComponent {

    public $disabled = false;
    public $enableToggle = false;
    public $text = '';

    public function __construct($text, $tooltip = '') {
        parent::__construct('button');

        $this->text = ExjText::__($text);
        if ($tooltip) {
            $this->tooltip = $tooltip;
        }
    }

    public function setIcon($icon, $iconAlign = '') {
        $this->icon = $icon;
        if ($iconAlign) {
            $this->iconAlign = $iconAlign;
        }

        return $this;
    }

    public function setIconCls($iconCls) {
        if ($iconCls) {
            $this->iconCls = $iconCls;
        }

        return $this;
    }

}
?>