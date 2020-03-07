<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpEditableModel
 */
class AppHelpEditableModel extends ExjEditableModel {
	public $id_help;
	public $name_help;
	public $url_help=null;
	public $is_module=0;
	public $content_help;
	// public $id_company;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_app_help';
		$fieldKey = 'id_help';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('name_help', 'Nombre de la Ayuda');
		$this->registerFieldString('url_help', 'URL');
		$this->registerFieldString('content_help', 'Contenido');
		$this->registerFieldInt('is_module', 'es nacional', false, false, true);
		// $this->registerFieldInt('id_company', 'Id Empresa');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('name_help', 'Ayuda'));
    	$this->registerControlUI(ExjUI::NewTextArea('content_help', 'Contenido', '99%', 210));
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
    	
    	return true;
    }

    /**
     * overwrited. Antes de Guardar
     *
     * @return bool
     */
    public function beforeSave(){
    	
    	// comprobación de duplicados
    	if (!$this->canSaveCodeUnique('name_help', 'Nombre de Ayuda')) {
    		return false;
    	}
    	
    	return true;
    }
	
}

?>