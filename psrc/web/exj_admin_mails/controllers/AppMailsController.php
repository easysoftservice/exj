<?php
/**
 * @class AppMailsController
 * Controlador para Correos
 */
class AppMailsController extends ExjController {
	
	/**
	 * override. Vista de datos
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		global $exj;
		$response = new ExjResponse();
		
		$topics=null;
		$total=0;
		
		if (!AppAdminMailModel::loadListCorreos($topics, $total, $this->paramCriteria)) {
			return $response;
		}
		
		$response->setDataTopics($topics, $total);
		return $response;
	}
	
	/**
	 * override. Creación
	 */
	public function create() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToCreate($response)) {
			return $response;
		}
		
		$is_html = $this->getParamIntFromData('is_html');
		
		$response = AppAdminMailModel::saveCorreo(0, $this->paramDataChanged, $is_html);
		$response->setDataObject($this->paramDataChanged->id_mail);
		
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		if (!$this->isValidParamsToUpdate($response)) {
			return $response;
		}
		
		$is_html = $this->getParamIntFromData('is_html');
		
		$response = AppAdminMailModel::saveCorreo($this->id, $this->paramDataChanged, $is_html);
		$response->setDataObject($this->paramDataChanged->id_mail);
		
		return $response;
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		AppAdminMailModel::destroy($this->id, 'AppMailEditableModel', $response);
		
		return $response;
	}
	

	/**
	 * overrride. Devuelve datos para el reporte
	 *
	 * @return AppCorreosReportModel
	 */
	public function getDataReport(){
		$dataReport = new AppCorreosReportModel();
		
		return $dataReport;
	}
	
	public function getVarsList(){
		$response = new ExjResponse();

		$varsList = AppMailVarHelper::GetVarsList();
		
		$response->setDataObject($varsList);
		
		
		return $response;
	}

}

?>