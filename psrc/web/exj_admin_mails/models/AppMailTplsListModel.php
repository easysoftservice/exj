<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppMailTplsListModel
 * Modelo de lista para: Plantillas
 */
class AppMailTplsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('tpls');
		
		$this->setReportDownload(true, true, true);
		
		$this->forceShowReports = true;
		$this->requiereSelectionReport = false;
		
		$this->setConfig('Plantillas', 'id_mail_tpl');
		$this->nameTopics = 'Plantillas';
		$this->nameTopic = 'Plantilla';
		$this->defaultSort = 'modificado_dt';
		$this->fixSortDesc();
		
		$this->autoAddColsNameUserDateRegister();
	}
	
	/**
	 * overwrited. Devuelve el manejador del menú
	 *
	 * @return ExjHelperMenu
	 */
	protected function getHandlerMenu() {
		if (!ExjUser::IsRolSuperAdmin()) {
			return null;
		}
		
		$hMenu = new ExjHelperMenu();
		$hMenu->fixFullAccess();
		
		return $hMenu;
	}
	
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('title_tpl', 'Título');
		$this->registerFieldString('type_tpl', 'Tipo');
		$this->registerFieldString('cnt_tpl', 'Contenido');
		$this->registerFieldInt('is_default_tpl', 'Es por defecto');
		$this->registerFieldString('subject_default', 'Asunto por defecto');
		$this->registerFieldString('is_published', 'Es publicado');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('title_tpl', 33);
		$this->registerCol('subject_default', 30);
		$this->registerCol('type_tpl', 30);
//		$this->registerColHidden('cnt_tpl', 45);
		$this->registerColCustom('is_published', 'Publicado', 'Exj.rendererTextSiNo', 18);
		$this->registerColCustom('is_default_tpl', 'Por defecto', 'Exj.rendererTextSiNo', 18);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){		
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppMailTplsData::loadListPlantillas($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
}

?>