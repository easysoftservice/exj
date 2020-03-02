<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncDocEditableModel
 */
class AppHldIncDocEditableModel extends ExjEditableModel {
	public $id_hld_inc_doc;
	public $id_hld_incident;
	public $id_file;
	public $tipo_doc;
	public $valor_doc;
	public $titulo_doc;
	public $desc_doc;

	const TIPO_ARCHIVO = 'ARCHIVO';
	const TIPO_LINK = 'LINK';
	
	/**
	 * overwrited. Inicio del modelo editable
	 *
	 */
	protected function initEditableModel(){
		$this->enableTransactionOnSave();
		$this->enableTransactionOnDestroy();
	}
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_helpdesk_incs_docs';
		$fieldKey = 'id_hld_inc_doc';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_hld_incident', 'Id Incidente');
		$this->registerFieldIntNullable('id_file', 'Id Archivo');
		$this->registerFieldString('tipo_doc', 'Tipo');
		$this->registerFieldStringNullable('valor_doc', 'Valor');
		$this->registerFieldString('titulo_doc', 'Título', true, true);
		$this->registerFieldStringNullable('desc_doc', 'Descripción', true, true);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){    	
    	$this->registerControlUI(ExjUI::NewTextField('titulo_doc'));
    	$this->registerControlUI(ExjUI::NewTextField('valor_doc'));
		
		$txaDesc = ExjUI::NewTextArea('desc_doc', 'Descripción');
    	$txaDesc->height = 150;
    	$this->registerControlUI($txaDesc);
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationTextMemo('valor_doc', 360);
    	$this->applyValidationTextMemo('titulo_doc', 30);
    	$this->applyValidationTextMemo('desc_doc', 60);
	}
	
	
	
	/**
     * overwrited. Inicio de Guardar
     *
     */
    protected function initSave(){
    	if ($this->isNew()) {
    		if (self::IsSettedValue($this->id_file)) {
    			$this->tipo_doc = self::TIPO_ARCHIVO;
    		}
    		elseif (self::IsSettedValue($this->valor_doc)){
    			$this->tipo_doc = self::TIPO_LINK;
    		}
    		else {
    			$this->addBrokenRuler("No se a cargado el archivo o no se ingresó la URL");
    			return false;
    		}
    	}
    	
    	return true;
    }

    /**
     * Antes Guardar
     *
     * @return bool
     */
    protected function beforeSave(){
    	if ($this->isNew()) {
    		switch ($this->tipo_doc) {
    			case self::TIPO_ARCHIVO:
    				if (!self::IsSettedValue($this->id_file) || !$this->id_file) {
    					$this->addBrokenRuler("Se requiere que se cargue el archivo");
    				}
    			break;
    			
    			case self::TIPO_LINK:
    				if (!self::IsSettedValue($this->valor_doc) || !$this->valor_doc) {
    					$this->addBrokenRuler("Se requiere el link o url");
    				}
    			break;
    		
    			default:
    				$this->addBrokenRuler("Tipo de documento no soportado: " . $this->tipo_doc);
    			break;
    		}
    		
    		if ($this->haveBrokenRules()) {
    			return false;
    		}
    	}
    	
    	return true;
    }
	
}

?>