<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjUIBoxComponent extends ExjUIComponent {

	public function __construct() {
        parent::__construct(self::XTYPE_BoxComponent);
    }

    /**
     * Envia el anchor en porcentaje, si se envia con px, se setea como width fijo
     *
     * @param string $anchor
     * @return ExjUIComponent
     */
    public function setAnchor($anchor = '99%') {
        if ($anchor) {
            if (strpos($anchor, 'px') !== false) {
                $anchor = str_replace('px', '', $anchor);
                if ($anchor) {
                    $anchor = intval($anchor);
                } else {
                    $anchor = 'auto';
                }

                $this->setWidth($anchor);

                return $this;
            }
            elseif (is_numeric($anchor)) {
                $anchor = floatval($anchor);
                if ($anchor > 100) {
                    $anchor = 100;
                }

                $anchor .= '%';
            }

            $this->anchor = $anchor;
        }

        return $this;
    }

    /**
     * Envia el ancho fijo al componente
     *
     * @param int $width
     * @param bool $clearAnchor
     * @return ExjUIComponent
     */
    public function setWidth($width, $clearAnchor = true) {
        $this->width = $width;
        if ($clearAnchor) {
            if (isset($this->anchor)) {
                unset($this->anchor);
            }
        }

        return $this;
    }

    public function setAutoHeight($value=true){
    	$this->autoHeight = $value;
    	return $this;
    }

    public function setFlex($value){
    	$this->flex = $value;
    	return $this;
    }

    public function setHeight($value){
    	$this->height = $value;
    	return $this;
    }
}

?>