<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppGenerateComponentsController
 * Controlador para Generar Componentes
 */
class AppGenerateComponentsController extends ExjController {
	
    /**
     * overwrited. Lectura del Modelo editable
     *
     * @param string $nameEditableModel
     * @param array $params
     */
	protected function editableModelRead(&$nameEditableModel, &$params) {
		$nameEditableModel = 'component';
	}
	
	public function viewTableCols(){
		$response = $this->getResponse();
		
		$table_name = $this->getParam('table_name');
		if (!$table_name) {
			return $response->setMsgError("Se requiere nombre de tabla!");
		}
		
		$topics=null;
		if (!AppComponentsModel::LoadLookupTableCols($topics, $table_name)) {
			return $response;
		}
		
		$response->setDataTopics($topics, count($topics));
		return $response;
	}
	
	public function getContentHTMLFileGenerated(){
		$response = $this->getResponse();
		
		$nameFileTpl = $this->getParam('nameFileTpl');
		if (!$nameFileTpl) {
			return $response->setMsgError("No se indicó nombre del archivo tpl!");
		}
		
		$nameTable = $this->getParam('nameTable');
		if (!$nameTable) {
			return $response->setMsgError("No se indicó nombre tabla!");
		}
		
		$nameComp = $this->getParam('nameComp');
		$plural_com = $this->getParam('plural_com');
		$singular_com = $this->getParam('singular_com');
		
		
		$itemsModifiedCols = $this->getParam('itemsModifiedCols');
		if ($itemsModifiedCols) {
			$itemsModifiedCols = json_decode($itemsModifiedCols);
			
			if (count($itemsModifiedCols) == 0) {
				$itemsModifiedCols = null;
			}
			else {
				Exj::TrasferCharsDecodeUTF8ToISO($itemsModifiedCols);
			}
		}
		
		$colsTable = AppComponentsModel::GetColsOfTable($nameTable, $itemsModifiedCols);
		if ($colsTable === false) {
			return $response->setMsgError("No se pudo obtener columnas de la tabla: $nameTable");
		}
		
		$fileGenerated = AppComponentsModel::GetFileGeneratedHTML($nameTable, $nameFileTpl, $nameComp, $plural_com, $singular_com, $colsTable);
		if ($fileGenerated->msgError) {
			return $response->setDataObject("<div style='color:red;'>$fileGenerated->msgError<div>")->setMsgWarning($fileGenerated->msgError);
		}
		
		$fileGenExist = file_exists(Exj::GetPathAppWeb(). '/'. $fileGenerated->pathFileComp) ? 'EXISTE':'NO EXISTE';
		
		$htmlContent = "<h3>$fileGenerated->pathFileComp ($fileGenExist)</h3><br>";
		$htmlContent .= "<div style='background-color: lightyellow;'>$fileGenerated->content</div>";
		
		$response->setDataObject($htmlContent);
				
		return $response;
	}

	/**
     * Crea un registro en modelo editable
     *
     * @return ExjResponse
     */
    public function create(){
    	$response = $this->getResponse();
    	
    	if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		AppComponentsModel::GenerarComponent($response, $this->paramDataChanged);
		
		return $response;
    }
	
}

?>