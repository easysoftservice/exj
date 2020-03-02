<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComCampoEditableChildModel
 */
class AppComCampoEditableChildModel extends ExjEditableChildModel {
	public $id_campo;
	public $id_componente;
	public $nombre_cam;
	public $etiqueta_cam;
	
	public $nameCol;
	public $labelCol;
	
	/**
	 * overwrited. Lee el nombre del modelo editable padre
	 *
	 * @param string $nameEditableModelParent
	 * @param string &$nameComponent
	 */
	protected function readNameEditableModelParent(&$nameEditableModelParent){
		$nameEditableModelParent = 'component';
	}	

	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'exj_com_campos';
		$fieldKey = 'id_campo';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldId('id_componente', 'Identificador de Componente');
		$this->registerFieldString('etiqueta_cam', 'Etiqueta');
		$this->registerFieldString('nombre_cam', 'Nombre');
		
		$this->registerFieldStringNullable('nameCol');
		$this->registerFieldStringNullable('labelCol');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
 
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		
	}
	
    /**
     * overwrited. Inicio de Guardar
     *
     * @return bool
     */
    protected function initSave(){
    	
    	if ($this->isSettedField('nameCol')) {
    		$this->nombre_cam = $this->nameCol;
    		$this->resetField('nameCol');
    	}
    	
    	if ($this->isSettedField('labelCol')) {
    		$this->etiqueta_cam = $this->labelCol;
    		$this->resetField('labelCol');
    	}
    	
    	if (!$this->isNew()) {
    		$this->resetField('id_componente');
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
    	return true;
    }
    
	
}

?>