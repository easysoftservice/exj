<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Utilitario para generar estructura en forma de tabla
 *
 */
class ExjUtilTable extends ExjObject {

    private $_columns;
    private $_width;
    private $_rows = array();
    private $_widthsCols = array();
    private $_isPercentWidths = null;

    const ALIGN_CENTER = 'center';
    const ALIGN_LEFT = 'left';
    const ALIGN_RIGHT = 'right';

    /**
     * Constructor
     *
     * @param int $columns Nro de columnas total que tendrá la estructura
     * @param string $width ancho de la tabla, esto se suele usar para presentación de datos en la UI
     */
    public function __construct($columns = 2, $width = '100%') {
        $this->_columns = $columns;
        $this->_width = $width;
        $this->_rows = array();
    }

    private function _validate() {
        return true;
    }

    public function getDataTable() {
        $this->_validate();

        $dataTable = new stdClass();

        $dataTable->columns = $this->_columns;
        $dataTable->width = $this->_width;
        $dataTable->rows = $this->_rows;

        $dataTable->isPercentWidths = $this->_isPercentWidths;
        $dataTable->widthsCols = $this->_widthsCols;

        return $dataTable;
    }

    /**
     * Fija el ancho de las columnas
     *
     * @param array $widths un array de el ancho de cada columna, no es necesario fijar todas las columnas
     * @param bool $isPercentWidths Por defecto true
     */
    public function fixWidthCols($widths, $isPercentWidths = true) {
        for ($i = 0; $i < $this->_columns; $i++) {
            if (isset($widths[$i])) {
                $this->_widthsCols[] = $widths[$i];
            } else {
                $this->_widthsCols[] = 0;
            }
        }

        $this->_isPercentWidths = $isPercentWidths;
    }

    /**
     * Adiciona una fila a la estructura de tabla
     *
     * @param array $cols Arreglo de columnas, estas columnas deben crearse con el método: NewColXXX
     */
    public function addRow($cols) {
        $this->_rows[] = self::NewRow($cols);
    }

    public function addRowHeaders($cols) {
        foreach ($cols as &$col) {
            $col->isfontBold = true;
            $col->align = self::ALIGN_CENTER;
            $col->isHeader = true;
        }

        $this->addRow($cols);
    }

    public function addRowTitle($col, $fontSize = 12) {
        $col->colspan = $this->_columns;
        if (!$col->align) {
            $col->align = self::ALIGN_CENTER;
        }
        $col->isfontBold = true;
        if ($fontSize && !$col->fontSize) {
            $col->fontSize = $fontSize;
        }

        $col->isTitle = true;

        $cols = array();
        $cols[] = $col;

        $this->addRow($cols);
    }

    static function NewRow($objCols, $title = '') {
        $row = new stdClass();
        $row->title = $title;
        $row->cols = $objCols;
        return $row;
    }

    static function NewItemImage($srcImg, $height = null, $width = null, $alt = '') {
        $itemImage = new ExjUtilImage($srcImg, $height, $width, $alt);
        return $itemImage;
    }

    static function NewColSpanImage($srcImg, $heightImg = null, $widthImg = null, $colspan = 0, $rowspan = 0, $align = null) {
        $col = self::NewColSpan(self::NewItemImage($srcImg, $heightImg, $widthImg), $colspan, $rowspan, $align);

        return $col;
    }

    static function NewColSpan($value, $colspan = 0, $rowspan = 0, $align = null, $isfontBold = false, $fontSize = 0) {
        return self::NewCol($value, $align, $isfontBold, $colspan, $rowspan, $fontSize);
    }

    static function NewColSpanLabel($value, $colspan = 0, $rowspan = 0, $fontSize = 0, $align = null, $isfontBold = true) {
        $value = $value . ':';
        if ($align === null) {
            $align = self::ALIGN_RIGHT;
        }

        $col = self::NewColSpan($value, $colspan, $rowspan, $align, $isfontBold, $fontSize);
        $col->isLabel = true;

        return $col;
    }

    static function NewColColor($value, $color, $align = null, $colspan = 0, $rowspan = 0, $fontSize = 0, $isfontBold = true) {
        return self::NewCol($value, $align, $isfontBold, $colspan, $rowspan, $fontSize, $color);
    }

    static function NewColFloat($value, $align = null, $isfontBold = false, $colspan = 0, $rowspan = 0, $fontSize = 0, $color = '') {
        $col = self::NewCol($value, $align, $isfontBold, $colspan, $rowspan, $fontSize, $color);
        $col->isTypeFloat = true;
        return $col;
    }

    static function NewCol($value, $align = null, $isfontBold = false, $colspan = 0, $rowspan = 0, $fontSize = 0, $color = '') {
        $item = new ExjUtilTableCol($value, $align, $isfontBold, $colspan, $rowspan, $fontSize, $color);
        return $item;
    }

}

// ExjUtilTable
?>