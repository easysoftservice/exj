<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailTplEditableModel
 */
class AppMailTplEditableModel extends ExjEditableModel {
	const MAIL_TIPO_INVITACION = 'INVITACION';
	const MAIL_TIPO_REPORTE = 'REPORTE';
	const MAIL_TIPO_NOTIFICACION = 'NOTIFICACION';
	
	public $id_mail_tpl;
	public $title_tpl;
	public $cnt_tpl;
	public $is_published=1;
	public $is_default_tpl=0;
	public $subject_default;
	public $type_tpl;
	public $id_company;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_app_mail_tpls';
		$fieldKey = 'id_mail_tpl';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('title_tpl', 'Título');
		$this->registerFieldString('cnt_tpl', 'Contenido');
		$this->registerFieldInt('is_published', 'Está plublicado', false, true, true);
		$this->registerFieldInt('is_default_tpl', 'Es por defecto', false, true, true);
		$this->registerFieldInt('id_company', 'Id Empresa');
		$this->registerFieldString('subject_default', 'Asunto');
		$this->registerFieldString('type_tpl', 'Tipo');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('title_tpl', 'Título'));
    	$this->registerControlUI(ExjUI::NewTextField('subject_default', 'Asunto por defecto'));
    	$this->registerControlUI(ExjUI::NewRadioGroupSiNo('is_published', 'Publicar'));
    	$this->registerControlUI(ExjUI::NewRadioGroupSiNo('is_default_tpl', 'Por defecto', false));

    	$this->registerControlUI(ExjUI::NewTextArea('cnt_tpl', 'Contenido', '99%', 300));
    	
    	
    	$this->registerControlUI(AppAdminMailUIHelper::NewComboSimpleTplTipos());
	}
	
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
    	$this->applyValidationClear('cnt_tpl', 60000);
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
    	
    	if (!$this->canDestroyRelationTable($id, 'jos_app_mails', 'Correos', 'id_mail_tpl')) {
    		return false;
    	}
    	
    	return true;
    }

    /**
     * overwrited. Inicio del Guardardo
     *
     * @return bool
     */
    protected function initSave(){
    	global $exj;
    	
    	if ($this->isNew()) {
    		$this->id_company = ExjUser::GetIdCompania();
    		
	    	if (!$this->isSettedField('type_tpl')) {
	    		$this->type_tpl = self::MAIL_TIPO_NOTIFICACION;
	    	}
	    	if (!$this->isSettedField('is_published')) {
	    		$this->is_published = 1;
	    	}
	    	if (!$this->isSettedField('is_default_tpl')) {
	    		$this->is_default_tpl = 0;
	    	}
    	}
    	
    	
    	if ($this->isSettedField('cnt_tpl')) {
    		
    		
    		$varsInvalid = array();
    		AppMailVarHelper::ValidateVarsInTextHTML($this->cnt_tpl, $varsInvalid);
    		if (count($varsInvalid) > 0) {
    			$msgError = "Existen variables no soportadas:<br/>";
    			$msgError .= implode(', ', $varsInvalid);
    			$msgError .= "<br/>Las variables soportadas son:<br/>";
    			$namesVars = AppMailVarHelper::GetNamesVars();
    			$msgError .= implode(', ', $namesVars);
    			
    			$this->addBrokenRuler($msgError);
    			return false;
    		}
    	}
    	
    	
    	
    	// comprobación de duplicados
//    	$whereExtra = "type_tpl='$this->type_tpl'";
    	if (!$this->canSaveCodeUnique('title_tpl', 'Título')) {
    		return false;
    	}
    	
    	return true;
    }
    
	
}

?>