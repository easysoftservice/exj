<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

class ExjEditableConstraint {

    public $nameField;
    public $isPrimaryKey = false;
    public $isForeignKey = false;
    public $isUnique = false;
    public $isActionUpdate = false;
    public $autoSeteerPrimaryKey = true;

    public function __construct($nameField, $isUnique = false, $isActionUpdate = false, $isForeignKey = true, $autoSeteerPrimaryKey = true) {
        if (is_array($nameField)) {
            $nameField = implode(',', $nameField);
        } else {
            $nameField = str_replace("  ", ' ', $nameField);
            $nameField = str_replace(array(", ", " ,", "+"), ',', $nameField);
        }
        $nameField = trim($nameField);

        $this->nameField = $nameField;
        $this->isUnique = $isUnique;
        $this->isActionUpdate = $isActionUpdate;
        $this->autoSeteerPrimaryKey = $autoSeteerPrimaryKey;

        $this->isForeignKey = $isForeignKey;
        $this->isPrimaryKey = (!$isForeignKey);
    }

    /**
     * Pasea a tipo ExjEditableConstraint
     *
     * @param object $obj
     * @return ExjEditableConstraint
     */
    public static function Parse($obj) {
        if (!is_object($obj)) {
            return null;
        }

        return $obj;
    }
}

?>