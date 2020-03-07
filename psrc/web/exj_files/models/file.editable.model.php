<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppFileEditableModel
 */
class AppFileEditableModel extends ExjEditableModel {
	public $id_file;
	public $name_file;
	public $nameext_file;
	public $path_file;
	public $uri_file;
	public $size_file;
	public $sub_folder;
	
	public $id_file_type;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_app_files';
		$fieldKey = 'id_file';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('name_file', 'Nombre');
		$this->registerFieldString('sub_folder', 'Sub Carpeta');
		$this->registerFieldString('nameext_file', 'Archivo');
		$this->registerFieldString('path_file', 'parth del archivo');
		$this->registerFieldString('uri_file', 'URI del archivo');
		$this->registerFieldInt('id_file_type', 'Id tipo de archivo');
		$this->registerFieldInt('size_file', 'Tamaño');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('nameext_file', 'Archivo', '96%'));
    	$this->registerControlUI(ExjUI::NewTextField('size_file', 'Tamaño'));
    	$this->registerControlUI(ExjUI::NewTextField('sub_folder', 'Sub Carpeta'));
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
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
    	

    	if (!$this->canDestroyRelationTable($id, 'jos_exj_sys_upgrades', 'Actualizaciones del Sistema - Código', 'id_file_code')) {
    		return false;
    	}
    	if (!$this->canDestroyRelationTable($id, 'jos_exj_sys_upgrades', 'Actualizaciones del Sistema - SQL', 'id_file_sql')) {
    		return false;
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
    	
    	if ($this->isNew()) {
    		return true;
    	}
    	
    	if ($this->isSettedField('size_file')) {
    		$this->addBrokenRuler("No se puede cambiar el tamaño del archivo");
    		return false;
    	}
    	
    	return true;
    }
	
}

?>