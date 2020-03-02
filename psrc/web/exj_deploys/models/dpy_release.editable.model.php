<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDpyReleaseEditableModel
 */
class AppDpyReleaseEditableModel extends ExjEditableModel {
	public $id_deploy;
	
	public $id_company;
	public $url_release;
	public $path_release;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'jos_exj_dpy_releases';
		$fieldKey = 'id_dpy_release';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldInt('id_deploy', 'Id Deploy');
		$this->registerFieldInt('id_company', 'Id Institución');
		
		$this->registerFieldString('url_release', 'URL', false, false);
		$this->registerFieldString('path_release', 'PATH', false, false);
		
	}

	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
    	$txfPath = ExjUI::NewTextField('path_release', 'Ruta a copiar');
    	$txfPath->value = $this->path_release;
    	$txfPath->readOnly = true;
    
    	$this->registerControlUI($txfPath);
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
    
    public function copyDeployProduction($id_deploy=0){
    	if ($id_deploy) {
    		$this->id_deploy = $id_deploy;
    	}
    	else {
    		$id_deploy = $this->id_deploy;
    	}
    	
    	$this->path_release = '';
    	$this->url_release = '';
    	
    	global $exj;
    	
		$deploy = new AppDeployEditableModel(false);
		
		$deploy->load($id_deploy);
		if ($deploy->haveBrokenRules(true)) {
			return false;
		}
		
		$criteria = new stdClass();
		$criteria->id_company = ExjUser::GetIdCompania();
		
		$nroRegLoaded = 0;
		$this->loadDBFromCriteria($criteria, $nroRegLoaded);
		if (!$nroRegLoaded) {
			// hay q crear el registro
			$this->id_company = $criteria->id_company;
		}
		$this->id_deploy = $deploy->id;
    	
    	$deployFiles = new AppDeployFilesModel($deploy->version_dpy);
    	
    	if (Exj::GetError()->haveError()) {
    		return false;
    	}
    	
    	/*
    	if (!$this->path_release) {
    		$this->path_release = $deployFiles->getPathPreProduccion();
    	}
    	if (!$this->url_release) {
    		$this->url_release = $deployFiles->getURIPreProduccion();
    	}
    	*/
		$this->path_release = $deployFiles->getPathPreProduccion();
		$this->url_release = $deployFiles->getURIPreProduccion();
    	
    	
    	$this->path_release = str_replace("\\", "/", $this->path_release);
    	
    	/*
    	$this->addBrokenRuler("Origen: $deploy->path_dpy");
    	$this->addBrokenRuler("Destino: $this->path_release");
    	return false;
    	*/
    	
		$deployFiles->copyToPreProduction($deploy->path_dpy, $this->path_release);
		
    	if (Exj::GetError()->haveError()) {
    		return false;
    	}
    	
    	$deploy->url_dpy = $this->url_release;
    	if (!$deploy->fixCopiedPreProduccion()) {
    		return false;
    	}
    	
    	if (!$deploy->save()) {
    		return false;
    	}

    	ExjEvent::Fire('afterCopyDeploy', array($deploy), $this);
    	
    	return true;
    }
    
}

?>