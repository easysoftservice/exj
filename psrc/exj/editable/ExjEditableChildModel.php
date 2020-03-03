<?php
// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Clase base para el modelo Editable Child. Los modelos editables hijos deben heredar de esta clase.
 * [drivers] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[drivers]/models/[driver].editable.model.php
 * Nombrado de la clase: Debe tener el formato: class App[driver]EditableChildModel extends ExjEditableChildModel
 * La clase editable child debe sobrescribir los métodos: readTable, registerFields, registerControlsUI, readNameEditableModelParent
 * Si la clase hija tiene otros hijos se debe sobrescribir el método: registerChildsNamesEditable.
 * Si se requiere leer datos extras al momento que se cargue el modelo child (al editar), se debe sobre-escribir el método: afterLoadRegisterControlsUI.
 */
class ExjEditableChildModel extends ExjEditableModel {
	private $_nameEditableModelParent='';
	private $_refEditableParent = null;

	/**
	 * Constructor de Modelo editable hijo
	 *
	 * @param bool $addControlesUI Si se envia null se toma en valor del editable Padre
	 * @param ExjResponse $response Por defecto null
	 * @param ExjEditableModel $editablePatent Por defecto null
	 */
	public function __construct($addControlesUI = true, $response = null, ExjEditableModel $editablePatent=null){
		$this->readNameEditableModelParent($this->_nameEditableModelParent);
		if (!$this->_validateBrokenRules()) {
			return ;
		}
		
		if (!$this->_nameEditableModelParent) {
			$this->addBrokenRuler("No se ha definido el nombre del modelo editable padre.<br/>Clase: " . get_class($this));
			$this->_validateBrokenRules();
			return ;
		}
		
		
		if (!$editablePatent) {
			$editablePatent = $this->createInstanceParent(
				$this->_nameEditableModelParent, 
				$addControlesUI
			);
			if (!$this->_validateBrokenRules()) {
				return ;
			}
		}
		
		$this->_refEditableParent = $editablePatent;
		
		if (!$response) {
			$response = $editablePatent->getResponse();
		}
		
		$this->setParams($editablePatent->getParams());
		if ($addControlesUI === null) {
			$addControlesUI = $editablePatent->isAddControlesUI();
		}
		
		if (!$response) {
			$response = new ExjResponse();
		}
		
		parent::__construct($addControlesUI, $response);
	}
	
	public function getNameFieldKeyParent(){
		return $this->_refEditableParent->getNameFieldKey();
	}

	/**
	 * Retorna el valor del campo clave del padre
	 *
	 * @param int $defaultValue Defecto -1
	 * @return int
	 */
	public function getValueFieldKeyParent($defaultValue = -1){
		$fkp = $this->getNameFieldKeyParent();
		return $this->_refEditableParent->getValueField($fkp, $defaultValue);
	}
	
	private function _validateBrokenRules(){
		if (!$this->haveBrokenRules()) {
			return true;
		}
		
		if ($this->_refEditableParent) {
			$this->_refEditableParent->addBrokenRuler($this->getBrokenRules());
		}
		else {
			Exj::SetErrorValidating($this->getBrokenRules());
		}
		
		return false;
	}

	/**
	 * overwrited. Lee el nombre del modelo editable padre
	 *
	 * @param string $nameEditableModelParent
	 */
	protected function readNameEditableModelParent(&$nameEditableModelParent){
		$this->addBrokenRuler("Se debe sobrecargar el método: ". __METHOD__."<br/>En la Clase: " . get_class($this));
	}
	
	/**
	 * Devuleve nombre del modelo editable padre
	 *
	 * @return string
	 */
	public function getNameEditableModelParent(){
		return $this->_nameEditableModelParent;
	}
	
	/**
	 * Retorna la referencia del modelo editable padre
	 *
	 * @return ExjEditableModel Puede retornar tambien una instancia de ExjEditableChildModel
	 */
	public function getEditableParent(){
		return $this->_refEditableParent;
	}
	
	public function loadFromEditableParent(ExjEditableModel &$objEditableParent, $idParent=0){
		if (!$idParent) {
			$idParent = $objEditableParent->getId();
		}
		
		if (!self::IsSettedValue($idParent)) {
			$idParent = 0;
		}
		
		if (!$idParent) {
			return false;
		}
		
		$fk = $objEditableParent->getFieldKey();
		
	//	echo '<br/>'.__METHOD__. " Cargando fk: $fk idParent: $idParent Clase Hija: ". get_class($this);
		
		$nroRegLoaded = 0;
		$criteria = new stdClass();
		$criteria->$fk = $idParent;
		
		$this->loadDBFromCriteria($criteria, $nroRegLoaded);
		if ($this->haveBrokenRules()) {
			// informar a la clase padre
			$objEditableParent->addBrokenRuler($this->getBrokenRules());
			return false;
		}
		
		if (!$nroRegLoaded) {
			return false;
		}
		
		// print_r($this->toObject());
		
		return true;
	}
	
	/**
	 * Carga los items hijos según la entidad padre
	 *
	 * @param array $items
	 * @param int $valueFKParent No Requerido
	 * @param bool $onlyFieldKeyToLoad No Requerido
	 * @return bool true si cargó los items
	 */
    public function loadItemsChilds(&$items, $valueFKParent=0, $onlyFieldKeyToLoad = true){
    	$items = array();
    	
    	if (!$valueFKParent) {
    		$valueFKParent = $this->getValueFieldKeyParent();
    	}
    	
    	if ($valueFKParent <= 0) {
    		$this->addBrokenRuler("No se pudo cargar items hijos.<br/>No se indicó ID del padre. " . $this->getClassStr('Clase Hija:'));
    		return false;
    	}
    	
    	$fkp = $this->getNameFieldKeyParent();
    	$where = "$fkp = $valueFKParent";
    	
    	$fields = '*';
    	if ($onlyFieldKeyToLoad) {
    		$fields = $this->getNameFieldKey();
    	}
    	
		$query = "SELECT $fields ";
    	$query .= " FROM " . $this->getNameTable();
    	$query .= " WHERE $where";
    	
    	$db = Exj::InstanceDatabase();
    	$items = $db->loadObjectList($query);
    	if (!$db->isValid()) {
    		$this->addBrokenRuler($db->getErrorMsg());
    		return false;
    	}
    	
    	// echo '<br/>' . __METHOD__. " => ";
    	// $db->writeLastQuery();
    	
    	return true;
    }
    
    public function cloneChildEditable($valueFKParentOriginal){
    	$editableParent = $this->_refEditableParent;
    	
    	$itemsChilds = array();
		if (!$this->loadItemsChilds($itemsChilds, $valueFKParentOriginal)) {
			return false;
		}
		
		if (count($itemsChilds) == 0) {
			// echo " ==> No tiene items hijos";
			return true;
		}
		
		$fkChild = $this->getNameFieldKey();
		
		$fkParentNew = $this->getNameFieldKeyParent();
		$valueFKParentNew = $this->getValueFieldKeyParent(-1);
		if ($valueFKParentNew <= 0) {
			$this->addBrokenRuler("ERROR CLONANDO CHILDS.<br/>No está seteado ID del padre, campo: $fkParentNew");
			return false;
		}
		
		// echo " ==> Si tiene items hijos: " . count($itemsChilds). " fkChild: $fkChild fkParentNew: $fkParentNew valueFKParentNew: $valueFKParentNew";

		$indexItemsChilds = -1;
		foreach ($itemsChilds as $itemChild) {
			$indexItemsChilds += 1;
			
			if (!isset($itemChild->$fkChild)) {
				$this->addBrokenRuler("ERROR CLONANDO CHILD EDITABLE.<br/>No está definido el valor id clave: $fkChild en items consultados." . $this->getClassStr());
				break;
			}
			
			$idChild = $itemChild->$fkChild;
			$this->setValueId($idChild);
			
			$objToSetter = new stdClass();
			$objToSetter->$fkParentNew = $valueFKParentNew;
			$this->setDataToSetterForClone($objToSetter);
			
			if (!$this->cloneEditable($idChild, $indexItemsChilds)) {
				break;
			}
		}
		
		if ($this->haveBrokenRules()) {
			return false;
		}
		
		return true;
    }
}

?>