<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

class ExjChildListModel {
	public $nameModel;
	public $nameController;
	public $fieldKey;
    public $nameEditableModel;
    private $_instanceListModel = null;
    private $_refInstParent;

	public function __construct(ExjModels $refInstParent, $name, $fieldKey) {
        $this->_refInstParent = $refInstParent;

		$this->setNameModel($name)
			->setNameController($name)
			->setFieldKey($fieldKey)
            ->setNameEditableModel(rtrim($name, 's'))
            ->setCanLoadData(false);
    }

    public function getModelParent() {
        return $this->_refInstParent;
    }

    public function getValueFromParent($prop, $defValue = null) {
        if (!$prop) {
            return $defValue;
        }

        $p = $this->getModelParent();
        return (isset($p->$prop) ? $p->$prop : $defValue);
    }

    public function setNameModel($value) {
    	$this->nameModel = $value;
        $this->_instanceListModel = null;
    	return $this;
    }

    public function setNameController($value) {
    	$this->nameController = $value;
    	return $this;
    }

    public function setFieldKey($value) {
    	$this->fieldKey = $value;
    	return $this;
    }

    public function setParams($value) {
    	$this->params = $value;
    	return $this;
    }

    public function getParams() {
        return (isset($this->params) ? $this->params : null);
    }

    public function setHelperMenu(ExjHelperMenu $value) {
    	$this->hMenu = $value;
    	return $this;
    }

    public function getHelperMenu() {
        return (isset($this->hMenu) ? $this->hMenu : null);
    }

    public function setNameEditableModel($value) {
    	$this->nameEditableModel = $value;
    	return $this;
    }

    public function setCanLoadData($value = true) {
    	$this->canLoadData = $value;
    	return $this;
    }

    public function getNameClassListModel() {
        return ExjUtil::GetNameClassModelListFromName($this->nameModel);
    }
  
    public function getInstanceListModel() {
        if (!$this->_instanceListModel) {
            $ClassListModel = $this->getNameClassListModel();
            $hMenu = $this->getHelperMenu();
            if (!$hMenu) {
                $hMenu = new ExjHelperMenu();
                $hMenu->fixFullAccess();
                $hMenu->isReports = false; // No está soportado
            }

            if (!class_exists($ClassListModel)) {
                $nameClassParent = get_class($this->getModelParent());
                throw new Exception(
                    "Error definiendo ExjChildListModel.<br/>Clase $ClassListModel no existe<br/>Definido en: $nameClassParent",
                    1
                );
            }

            $this->_instanceListModel = new $ClassListModel($hMenu, $this->nameModel);

            if (!($this->_instanceListModel instanceof ExjListModel)) {
                $nameClassParent = get_class($this->getModelParent());
                throw new Exception(
                    "Error definiendo ExjChildListModel.<br/>Clase $ClassListModel debe ser instancia de ExjListModel<br/>Definido en: $nameClassParent",
                    1
                );
            }
        }

        $this->_instanceListModel->setChildNameEditable($this->nameEditableModel)
                ->setChildNameList($this->nameModel);

        $params = $this->getParams();
        if (!empty($params)) {
            foreach ($params as $name => $value) {
                $this->_instanceListModel->setBaseParam($name, $value);
            }
        }

        $fk = $this->fieldKey;
        $valueKey = $this->getValueFromParent($fk);
        if ($valueKey !== null) {
            $this->_instanceListModel->setBaseParam($fk, $valueKey);
        }

        return $this->_instanceListModel;
    }
}

?>