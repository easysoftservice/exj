<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailsListModel
 * Modelo de lista para: Correos
 */
class AppMailsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('mails');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Correos', 'id_mail');
		$this->nameTopics = 'Correos';
		$this->nameTopic = 'E-mail';
		$this->defaultSort = 'modificado_dt';
		$this->fixSortDesc();
		
		$this->autoAddColsNameUserDateRegister();
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('from_email', 'Desde');
		$this->registerFieldString('to_email', 'E-mail');
		$this->registerFieldString('cc_mail', 'Copia');
		$this->registerFieldString('bcc_mail', 'Copia oculta');
		$this->registerFieldInt('is_html', 'Es HTML');
		$this->registerFieldInt('id_mail_tpl', 'Id plantilla del correo');
		$this->registerFieldInt('nro_attachs', 'Nro de archivos adjuntos');
		$this->registerFieldString('names_attachs', 'Adjuntos');
		$this->registerFieldString('subject_mail', 'Asunto');
		$this->registerFieldString('body_mail', 'Descripción');
		$this->registerFieldString('state_mail', 'State');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('to_email', 30);
		$this->registerCol('subject_mail', 33);
		$this->registerCol('cc_mail', 30);
		$this->registerCol('bcc_mail', 21);
		$this->registerCol('names_attachs', 21);
		$this->registerCol('state_mail', 21);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppAdminMailModel::loadListCorreos($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionarán al toolbar del grid 
	 * en la parte superior Izquierda
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		$items = array();

		$btnSendMail = ExjUI::NewButton('Renviar correo...', 'Renvía el correo seleccionado, previa a una confirmación', 'app-btn-mail', 'mail_resend');
		$items[] = $btnSendMail;

		$btnPreviewMail = ExjUI::NewButton('Vista previa correo...', 'Muesta una vista previa del correo seleccionado', 'app-btn-view', 'mail_preview');
		$items[] = $btnPreviewMail;
		
		$items[] = '-';
		
		$btnMailTpls = ExjUI::NewButton('Plantillas', 'Gestiona las plantillas para correos', 'app-btn-view', 'mail_tpls');
		$items[] = $btnMailTpls;
		
		return $items;
	}		
	
}

?>