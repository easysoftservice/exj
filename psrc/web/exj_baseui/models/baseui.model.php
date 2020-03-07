<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppBaseuiModel
 */
class AppBaseuiModel extends ExjModel {
    
	/**
	 * Devuelve data de modelo de lista
	 *
	 * @param ExjHelperMenu $hMenu Instancia de la clase ExjHelperMenu
	 * @param string $nameComponent
	 * @param string $nameListModel
	 * @return object
	 */
    public static function getDataUIList($hMenu, $nameComponent, $nameListModel) {
   // 	echo "testx OKKK";	
  //  	exit();
    	
    	$nameClaseModel = Exj::GetNameClassList($nameListModel);

    	
    	$instaceModel = new $nameClaseModel($hMenu);
    	
    	// $instaceModel->setDataAccess($hMenu);
    	$instaceModel->readData($nameComponent);
    	
    	return $instaceModel->to_ui();
    }
    
    public static function getDataUIPanelMain($hMenu, $nameComponent, $namePanelMainModel) {

   // 	echo "testx OKKK";	
  //  	exit();
    	
		$NameClaseModel = Exj::GetNameClassPanelMain($namePanelMainModel);
    	
    	$instaceModel = new $NameClaseModel($hMenu);
    	
    	// $instaceModel->setDataAccess($hMenu);
    	$instaceModel->readData($nameComponent);
    	
    	return $instaceModel->to_ui();
    }
    
	/**
	 * Devuelve data de modelo editable
	 *
	 * @param ExjHelperMenu $hMenu Instancia de la clase ExjHelperMenu
	 * @param string $nameComponent
	 * @param string $nameEditableModel
	 * @param string $nameEditableModelExtend No es requerido
	 * @return object
	 */
    public static function getDataUIEditable($hMenu, $nameComponent, $nameEditableModel, $nameEditableModelExtend=''){
		
    	$nameClaseModel = Exj::GetNameClassEditable($nameEditableModel);
    	
        // $instaceModel = new $nameClaseModel($hMenu);
    	$instaceModel = new $nameClaseModel(true);
//    	$instaceModel->readData($nameComponent);

//    	echo " nameEditableModel: $nameEditableModel nameComponent: $nameComponent <br/>nameClaseModel: $nameClaseModel";

    	return $instaceModel->to_ui();
    }
    
    static function getDataUICriteria($hMenu, $nameComponent, $nameCriteriaModel){
    	$nameClaseModel = Exj::GetNameClassCriteria($nameCriteriaModel);
    	if (!class_exists($nameClaseModel)) {
    		trigger_error("La clase del modelo criteria: $nameClaseModel no existe", E_USER_ERROR);
    		return null;
    	}
    	
    	$instaceModel = new $nameClaseModel(true, $hMenu);
    	
    	return $instaceModel->to_ui();
    }
    
    static function getDataUIFooter($hMenu, $nameComponent, $nameFooterModel){
    	
    	$nameClaseModel = Exj::GetNameClassFooter($nameFooterModel);
    	
    	if (!class_exists($nameClaseModel)) {
    		trigger_error("La clase del modelo footer: $nameClaseModel no existe", E_USER_ERROR);
    		return null;
    	}
    	
    	$instaceModel = new $nameClaseModel(true, $hMenu);
    	
    	return $instaceModel->to_ui();
    }

    static function getDataUIReadOnly($hMenu, $nameComponent, $nameReadOnlyModel){
    	
    	$nameClaseModel = Exj::GetNameClassReadOnly($nameReadOnlyModel);
    	
    	
    	if (!class_exists($nameClaseModel)) {
    		trigger_error("La clase del modelo readonly: $nameClaseModel no existe", E_USER_ERROR);
    		return null;
    	}
    	
    	$response = new ExjResponse();
    	$instaceModel = new $nameClaseModel(true, $response);
    	
    	return $instaceModel->to_ui();
    }
}

?>