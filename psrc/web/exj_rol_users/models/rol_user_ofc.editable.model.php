<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolUserOfcEditableModel
 */
class AppRolUserOfcEditableModel extends ExjEditableModel {
	public $id_sys_user_empresa;
	public $id_empresa;
	public $id_sys_user;

	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_sys_user_empresas';
		$fieldKey = 'id_sys_user_empresa';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldId('id_sys_user', 'Id User System');
		$this->registerFieldId('id_empresa', 'Id Empresa');
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
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}
    	
    	$this->load($id);
    	if ($this->haveBrokenRules()) {
    		return false;
    	}
    	
    	/*
    	$this->addBrokenRuler("test xxxssss");
    	return false;
    	*/

    	/*
    	if (!$this->_canDestroyTableUserChange('jos_app_xxx', 'Pxxx')) {
    		return false;
    	}
    	*/

    	/*
    	$this->addBrokenRuler("test de delete usr sys");
    	return false;
    	*/
		
    	return true;
    }
    
    /*
    private function _canDestroyTableUserChange($nameTable, $nameEntity){
    	 return $this->canDestroyRelationTable($this->id_user, $nameTable, $nameEntity, 'id_usuario_modifico', "No se puede eliminar.<br/>El Usuario a creado o editado registros.");
    }
    */
    

    /**
     * overwrited. Antes de Guardar
     *
     * @param int $id
     * @return bool
     */
    public function beforeSave(){
    	if (!$this->_canSave()) {
    		return false;
    	}
    	
    	
    	return true;
    }
    private function _canSave(){
    	return true;
    	
    	
    	$paramCriteria = new stdClass();
    	
    	$paramCriteria->id_sys_user = $this->id_sys_user;

    	/*
    	if ($this->haveBrokenRules()) {
    		echo " aqui xxxxx";
    		return false;
    	}
    	*/
    	
    	global $exj;
    	// $exj->includeModel('exj_rol_users');
    	
    	$topics=null;
    	$total=0;
		if (!AppRolUserModel::LoadListRolUsers($this->getResponse(), $topics, $total, $paramCriteria)) {
			return false;
		}
		
		// $db = Exj::InstanceDatabase();
		// $db->writeLastQuery();
		
		if (!$total) {
			return true;
		}
		
		$item = $topics[0];
		if ($item->id_sys_user_empresa == $this->id) {
			return true;
		}
		
		$this->addBrokenRuler("It is registered.<br/>User: $item->name_usr <br/>Empresa: $item->nom_empresa");
		return false;
    }
	
    /**
     * Indica si existe un registros usuario oficina
     *
     * @param int $id_sys_user
     * @param int $id_empresa
     * @return mixed false si no existe, si existe retorna el ID id_sys_user_empresa
     */
    public function existeUserOfc($id_sys_user, $id_empresa){
    	
    	$criteria = new stdClass();
    	$criteria->id_sys_user = $id_sys_user;
    	$criteria->id_empresa = $id_empresa;
    	
    	$objSelf = null;
    	$this->loadDBFromCriteriaToObject($objSelf, $criteria, 'id_sys_user_empresa');
    	if ($objSelf) {
    		return $objSelf->id_sys_user_empresa;
    	}
    	
    	return false;
    }
}

?>