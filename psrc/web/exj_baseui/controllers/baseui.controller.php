<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppBaseuiController
 * A simple application controller extension
 */
class AppBaseuiController extends ExjController {
	
	
	public function getDataUI() {
		global $exj;
		
		$response = new ExjResponse();
		
		$idMenu = ExjRequest::GetParamInt('idMenu');
		$nameComponent = ExjRequest::GetParam('nameComponent');
		$nameListModel = ExjRequest::GetParam('nameListModel');
		$nameEditableModel = ExjRequest::GetParam('nameEditableModel');
		$nameEditableModelExtend = ExjRequest::GetParam('nameEditableModelExtend');
		$nameCriteriaModel = ExjRequest::GetParam('nameCriteriaModel');
		$nameFooterModel = ExjRequest::GetParam('nameFooterModel');
		$nameReadOnlyModel = ExjRequest::GetParam('nameReadOnlyModel');
		$namesListsModels = ExjRequest::GetParam('namesListsModels', null);
		$namePanelMainModel = ExjRequest::GetParam('namePanelMainModel', null);
		
		
		if (!$nameComponent) {
			$response->setMsgError("No se ha indicado nombre del componente.", 'Error Generando UI');
			return $response;
		}
		
		if ($namesListsModels && is_string($namesListsModels)) {
			$namesListsModels = Exj::JsonDecode($namesListsModels);
			if (count($namesListsModels) == 0) {
				$namesListsModels = null;
			}
		}

		
		// se lee desde sesion acceso al modulo y permisos
		$hMenu = new ExjHelperMenu();
		// $hMenu->bufferDebugEnable();
		
		if (!$hMenu->loadMenuAccess($idMenu)) {
			//$strObjMenu = print_r($hMenu, true);
			 // $hMenu->bufferDebugAdd(print_r($hMenu, true));
			// $response->setMsgError("Permiso denegado.<br/>No se encuentra el menú $idMenu" . "<br/>$strObjMenu");
			// $response->setMsgError("Permiso denegado.<br/>No se encuentra el menú $idMenu" .'<br/>'. print_r($_SESSION, true));
			return $response->setMsgError(
				"Permiso denegado.<br/>No se encuentra menú. Ref: $idMenu"
			);
		}
		
		/*
		$strObjMenu = print_r($hMenu, true);
		$response->setMsgInfo($strObjMenu);
		*/
		
		if (!$hMenu->moduleNameAccess) {
			$response->setMsgError("Error no se pudo obtener datos de acceso");
			return $response;
		}
		
		if ($hMenu->moduleNameAccess != $nameComponent) {
			$response->setMsgError("Permiso denegado. No se puede acceder al componente");
			return $response;
		}
		
		if (!$hMenu->isAccessModule) {
			$response->setMsgError("Usted no tiene acceso al módulo", "Permiso denegado...");
			return $response;
		}
		
		
		 
		$dataResponse = new stdClass();
		
		// TODO: obtener data para el idioma
		$dataResponse->dataIdioma = new stdClass();
		$dataResponse->list = null;
		$dataResponse->editable = null;
		$dataResponse->criteria = null;
		$dataResponse->footer = null;


	//	$response->setMsgError("test nameListModel: $nameListModel");
	//	return $response;
		
		if ($nameEditableModel) {
			$dataResponse->editable = AppBaseuiModel::getDataUIEditable($hMenu, $nameComponent, $nameEditableModel, $nameEditableModelExtend);
		}

	//	echo "<br/>modelo editable OK. Verificando modelo list...";
		
		if ($nameListModel) {
			$dataResponse->list = AppBaseuiModel::getDataUIList($hMenu, $nameComponent, $nameListModel);
		}
		if ($namesListsModels) {
			$dataResponse->lists = array();
			foreach ($namesListsModels as $itemNameListModel) {
				$newItemListModel = new stdClass();
				$newItemListModel->name = $itemNameListModel;
				$newItemListModel->list = AppBaseuiModel::getDataUIList($hMenu, $nameComponent, $itemNameListModel);
				
				$dataResponse->lists[] = $newItemListModel;
			}
		}

	//	echo "<br/>modelo list OK. Verificando modelo criteria...";
		
		if ($nameCriteriaModel) {
			$dataResponse->criteria = AppBaseuiModel::getDataUICriteria($hMenu, $nameComponent, $nameCriteriaModel);
		}
		
//		echo "<br/>modelo criteria OK. Verificando errores...";

		if ($nameFooterModel) {
			$dataResponse->footer = AppBaseuiModel::getDataUIFooter($hMenu, $nameComponent, $nameFooterModel);
		}
		
		if ($nameReadOnlyModel) {
			$dataResponse->readonly = AppBaseuiModel::getDataUIReadOnly($hMenu, $nameComponent, $nameReadOnlyModel);
		}
		
		if ($namePanelMainModel) {
			$dataResponse->panelmain = AppBaseuiModel::getDataUIPanelMain(
				$hMenu, $nameComponent, $namePanelMainModel
			);
		}
		
		if (Exj::GetError()->haveError()) {
			return $response;
		}
		
		$response->data = $dataResponse;
		
		return $response;
	}
	
}

?>