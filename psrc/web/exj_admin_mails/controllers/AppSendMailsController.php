<?php
/**
 * @class AppSendMailsController
 * Controlador para Correos
 */
class AppSendMailsController extends ExjController {
	
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
		
		$response->setMsgError("No soportado");
		
		return $response;
	}

	/**
	 * override. Actualizar
	 */
	public function update() {
		$response = new ExjResponse();
		
		$response->setMsgError("No soportado");
		
		return $response;
	}

	/**
	 * override. Destruír o Eliminar
	 */
	public function destroy() {
		$response = new ExjResponse();
		
		$response->setMsgError("No soportado");
		
		return $response;
	}
	
	/**
	 * Envia el correo
	 *
	 * @return unknown
	 */
	public function send() {
		$response = new ExjResponse();
		
		$idMail = $this->getParamId('id');
		if (!$idMail) {
			$response->setMsgError("No se indicó parámetros para envio de correo!");
			return $response;
		}
		
		
		if (AppMailHelper::send($idMail)) {
			$response->setMsgNotify("Correo ha sido enviado");
		}
		else {
			$msgError = 'No se pudo enviar correo, error desconocido';
			if (Exj::GetError()->haveError()) {
				$msgError = Exj::GetErrorText();
			}
			$response->setMsgError($msgError);
		}
		
		return $response;
	}
	
	public function preview(){
		$response = new ExjResponse();
		
		$idMail = $this->getParamId('id');
		if (!$idMail) {
			$response->setMsgError("No se indicó parámetros para envio de correo!");
			return $response;
		}

		
		$dataMail = AppMailHelper::send($idMail, true);
		if ($dataMail === false) {
			$msgError = 'No se pudo presentar la vista previa del correo. Error desconocido';
			if (Exj::GetError()->haveError()) {
				$msgError = Exj::GetErrorText();
			}
			$response->setMsgError($msgError);
			return $response;
		}
		
		$response->setDataObject($dataMail);
		
		return $response;
	}

}

?>