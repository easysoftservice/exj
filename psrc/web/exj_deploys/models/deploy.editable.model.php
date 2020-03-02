<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDeployEditableModel
 */
class AppDeployEditableModel extends ExjEditableModel {
	public $id_deploy;
	public $version_dpy;
	public $path_dpy;
	public $file_bkdb;
	public $is_copied_preprod;
	
	public $num_filesphp;
	public $num_filesjs;
	public $num_filescss;
	public $num_filesimg;
	
	public $num_filesotros;
	public $num_filesjs_encoded;
	public $size_filesjs_encoded;
	
	public $url_dpy;
	public $obs_dpy;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_deploys';
		$fieldKey = 'id_deploy';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldString('version_dpy', 'Versión de la App');
		$this->registerFieldString('path_dpy', 'Path físico');
		$this->registerFieldString('file_bkdb', 'Nombre del archivo de la DB');
		$this->registerFieldInt('is_copied_preprod', 'Está copiado a preproduccion', false, true, true);
		
		$this->registerFieldInt('num_filesphp', 'Archivos PHP', false, true, true);
		$this->registerFieldInt('num_filesjs', 'Archivos JS', false, true, true);
		$this->registerFieldInt('num_filescss', 'Archivos CSS', false, true, true);
		$this->registerFieldInt('num_filesimg', 'Archivos Imagen', false, true, true);
		
		$this->registerFieldInt('num_filesotros', 'Archivos Otros', false, true, true);
		$this->registerFieldInt('num_filesjs_encoded', 'Archivos JS codificados', false, true, true);
		$this->registerFieldInt('size_filesjs_encoded', 'Tamaño de Archivos JS codificados', false, true, true);
		
		$this->registerFieldString('url_dpy', 'URL');
		$this->registerFieldString('obs_dpy', 'Observación', true, false);
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		$this->registerControlUI(ExjUI::NewTextArea('obs_dpy', 'Observación'));
		
		$txfVersion = ExjUI::NewTextField('version_dpy', 'Versión', '90%');
		$txfVersion->readOnly = true;
		$this->registerControlUI($txfVersion);
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
    	
		/*    	
    	if (!$this->canDestroyRelationTable($id, 'jos_tpg_staff_dest', 'Parámetros Generales')) {
    		return false;
    	}
    	*/
		
    	return true;
    }
    
    public function fixCopiedPreProduccion($is_copied_preprod = 1){
    	$this->is_copied_preprod = $is_copied_preprod;
    	if (!$this->is_copied_preprod) {
    		return true;
    	}
    	
    	if ($this->isNew()) {
    		return true;
    	}
    	
    	// hay q cambiar a los otros
    	$db = Exj::InstanceDatabase();
    	
    	$sql = "UPDATE jos_exj_deploys";
    	$sql .= " SET is_copied_preprod=0";
    	$sql .= " WHERE id_deploy <> $this->id";
    	
    	$db->setQuery($sql);
    	$db->query();
    	return $db->isValid();
    }
    
    public function buildDeployEditable(){
    	if (!$this->isNew()) {
    		// es edicion
    		return true;
    	}
    	
    	// echo "<br>" . __METHOD__;
    	
    	
    	$nroRegLoaded = 0;
    	$criteria = new stdClass();
    	$criteria->version_dpy = $this->version_dpy;
    	$deployLoad = new AppDeployEditableModel(false);
    	$deployLoad->loadDBFromCriteria($criteria, $nroRegLoaded);
    	if ($deployLoad->haveBrokenRules(true)) {
    		return false;
    	}
    	
    	if ($nroRegLoaded > 0) {
    		$this->setValueId($deployLoad->id);
    	}
    	
    	global $exj;
    	
    	$deployFiles = new AppDeployFilesModel($this->version_dpy);
    	$deployFiles->writeLogFile(__METHOD__. " SE CREO INSTANCIA DE LA CLASE: AppDeployFilesModel");
    	$deployFiles->buildDeployFiles();

    	if (Exj::GetError()->haveError()) {
            $this->addBrokenRuler($exj->getErrorMsg());
    		return false;
    	}
    	
    	$this->num_filescss = $deployFiles->getNumFilesCopiedCSS();
    	$this->num_filesphp = $deployFiles->getNumFilesCopiedPHP();
    	$this->num_filesimg = $deployFiles->getNumFilesCopiedIMG();
    	$this->num_filesjs = $deployFiles->getNumFilesCopiedJS();
    	
    	$this->num_filesotros = $deployFiles->getNumFilesCopiedOTROS();
    	$this->num_filesjs_encoded = $deployFiles->getNumFilesJsEncoded();
    	$this->size_filesjs_encoded = $deployFiles->getSizeTotalFilesJsEncoded();
    	
    	
    	$this->path_dpy = $deployFiles->getPathRelease();
    	$this->url_dpy = $deployFiles->getURIRelease();
    	$this->is_copied_preprod = 0;
    	$this->file_bkdb = $deployFiles->getNameFileBKDB();
    	
    	return true;
    }
    
    
}

?>