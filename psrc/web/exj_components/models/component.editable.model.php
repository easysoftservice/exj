<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComponentEditableModel
 */
class AppComponentEditableModel extends ExjEditableModel {
	public $id_componente;
	public $id_group_joomla;
	public $plural_com;
	public $singular_com;
	public $nombre_tabla_com;
	
	public $nombre_com;
	public $tpl_file;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'exj_components';
		$fieldKey = 'id_componente';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldId('id_group_joomla');
		$this->registerFieldString('plural_com', 'Plural');
		$this->registerFieldString('singular_com', 'Singular');
		$this->registerFieldString('nombre_tabla_com', 'Tabla');
		
		$this->registerFieldString('nombre_com', 'Componente');
		$this->registerFieldString('tpl_file', 'Archivo', false, false);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){		
		$this->registerControlUI(ExjUI::NewTextField('nombre_tabla_com'));
    	$this->registerControlUI(ExjUI::NewTextField('nombre_com'));
    	$this->registerControlUI(ExjUI::NewTextField('singular_com'));
    	$this->registerControlUI(ExjUI::NewTextField('plural_com'));
    	
    	/* nombre_tabla_com */
    	$this->registerControlUI(
            AppComponentsUIHelper::NewComboSimpleAppTables('99%')
        );
    	
    	/* tpl_file */
    	$this->registerControlUI(AppComponentsUIHelper::NewComboSimpleTplFiles());
    	
    	/* grid */
    	$this->registerChildListModel(
            'table_cols',
            'table_cols',
            'id_componente',
            null,
            ExjHelperMenu::CreateAccessReadOnly(),
            'com_campo',
            false
        );
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextNameExtendido('plural_com', false, 60, 3);
    	$this->applyValidationTextNameExtendido('singular_com', false, 60, 3);
	}	

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}

        if ($id <= 0) {
            $this->addBrokenRuler("Componente no existe. Ref: $id");
            return false;
        }
    	
    	/*
    	if (!$this->canDestroyRelationTable($id, 'jos_groups', 'Grupos Joomla', '')) {
    		return false;
    	}
    	*/
    	if (!$this->canDestroyRelationTable($id, 'exj_com_campos', 'Campos')) {
    		return false;
    	}
		
    	return true;
    }
    
    /**
     * overwrited. Inicio de Guardar
     *
     * @return bool
     */
    protected function initSave(){
    	$id_group_joomla = $this->getParam('id_group_joomla', 0);
    	if (!$id_group_joomla) {
	    	$this->addBrokenRuler("No se indicó Id group joomla!");
	    	return false;
    	}
    	
    	$objComp = null;
    	$this->loadToObjectCustom($objComp, "id_group_joomla=$id_group_joomla", 'id_componente');
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	if ($objComp) {
    		$this->setValueId($objComp->id_componente);
    	}
    	else {
    		$this->setValueId(0);
    		$this->id_group_joomla = $id_group_joomla;
    	}
    	
    	// print_r($this->toObjectOnlySetted());
    	
    	if ($this->isSettedField('tpl_file')) {
    		$this->resetField('tpl_file');
    	}

    	return true;
    }

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){

    	if ($this->isSettedField('nombre_com')) {
    		if (strpos($this->nombre_com, Exj::PREFIX_COMP_APP) !== 0) {
    			$this->addBrokenRuler("El nombre del componente debe tener el prefijo: " . Exj::PREFIX_COMP_APP);
    			return false;
    		}

    		if (strpos($this->nombre_com, ' ') !== false) {
    			$this->addBrokenRuler("El nombre del componente no debe tener espacios en blanco.<br>$this->nombre_com");
    			return false;
    		}
    		
	    	if ($this->isSettedField('id_group_joomla') && !$this->canSaveCodeUnique('id_group_joomla', 'Componente', null, 'nombre_tabla_com')) {
	    		return false;
	    	}
	    	
	    	$this->resetField('nombre_com');
    	}

    	return true;
    }
	
}

?>