<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpdeskEditableModel
 */
class AppHelpdeskEditableModel extends ExjEditableModel {
	public $id_helpdesk;
	public $id_hld_catalog_hld;
	public $esta_activo_hld;
	public $is_default_hld;
	
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_helpdesks';
		$fieldKey = 'id_helpdesk';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_hld_catalog_hld', 'Tipo');
		$this->registerFieldInt('is_default_hld', 'Defecto', false, true, true);
		$this->registerFieldInt('esta_activo_hld', 'Está activo', false, false, true);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('id_hld_catalog_hld', 'Tipo'));
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	// $this->applyValidationTextName('id_hld_catalog_hld', false, 60, 3);
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
    	
    	if (!$this->canDestroyRelationTable($id, 'jos_exj_helpdesk_incidents', 'Incidentes')) {
    		return false;
    	}
    	
    	if (!ExjUser::IsRolSuperOAdmin()) {
    		$this->addBrokenRuler("Solo el <b>Super Administrador</b> puede eliminar");
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * overwrited. Inicio de Guardar
     *
     */
    protected function initSave(){
    	if ($this->isNew()) {
    		$this->esta_activo_hld = 1;
    	}
    	
    	return true;
    }

    /**
     * overwrited. Antes de Guardar
     *
     * @return bool
     */
    public function beforeSave(){
    	
    	// comprobación de duplicados
    	if (!$this->canSaveCodeUnique('id_hld_catalog_hld', 'Tipo Mesa de Ayuda')) {
    		return false;
    	}
    	
    	return true;
    }
	
}

?>