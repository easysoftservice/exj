<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHldIncidentsListModel
 * Modelo de lista para: Incidentes Mesa de Ayuda
 */
class AppHldIncidentsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('hld_incidents');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Incidentes Mesa de Ayuda', 'id_hld_incident');
		$this->nameTopics = 'Incidentes';
		$this->nameTopic = 'Incidente';
		$this->defaultSort = 'start_incident';
		$this->fixSortDesc();
		$this->getView()->setForceFit(false);
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldInt('id_helpdesk', 'Id HelpDesk');
		$this->registerFieldInt('id_hld_catalog_state', 'Id Estado');
		$this->registerFieldInt('id_hld_catalog_priority', 'Id Prioridad');
		$this->registerFieldInt('id_sys_user_asignado', 'Id Usr Asignado', true);
		$this->registerFieldString('name_pri', 'Prioridad');
		$this->registerFieldString('name_state', 'Estado');
		$this->registerFieldString('name_hld', 'Mesa de Ayuda');
		$this->registerFieldString('title_incident', 'Asunto');
		$this->registerFieldString('desc_incident', 'Descripción');
		
		$this->registerFieldString('color_pri', 'Color Prioridad');
		$this->registerFieldString('color_state', 'Color Estado');
		$this->registerFieldString('color_hld', 'Color Mesa de Ayuda');
		
		$this->registerFieldDateTime('start_incident', 'Inicio');
		$this->registerFieldDateTime('end_incident', 'Final');

		$this->registerFieldString('typ_usr_chg', 'Tipo de Usr Cambio');
		$this->registerFieldString('typ_usr_cre', 'Tipo de Usr Creó');
		$this->registerFieldString('comp_mun', 'Compañia - Empresa');
		
		
		$this->registerFieldString('name_usr_cre', 'Creado por');
		$this->registerFieldString('name_usr_asign', 'Asignado a', true);
		$this->registerFieldString('name_usr_chg', 'Modificado por');
		$this->registerFieldDateTime('modificado_dt', 'Cambio');
		
		$this->registerFieldInt('canDel', 'Se puede eliminar');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		if (ExjUser::IsRolSuperOAdmin()) {
			$this->registerCol('comp_mun', self::COL_ANCHO_NOMBRE);
		}
		
		$this->registerColCustom('name_hld', "Mesa de Ayuda", "renderHelpDesk", self::COL_ANCHO_NOMBRE+45);
		$this->registerCol('title_incident', self::COL_ANCHO_DETALLE);
		$this->registerColCustom('name_state', "Estado", "renderState", self::COL_ANCHO_NOMBRE);
		$this->registerColCustom('name_pri', "Prioridad", "renderPriority", self::COL_ANCHO_CODIGO);
		
		$this->registerColDateTime('start_incident');
		$this->registerColDateTime('end_incident');
		
		$this->registerColCustom('name_usr_cre', "Creado Por", "renderUsrCre", self::COL_ANCHO_NOMBRE);
		$this->registerCol('name_usr_asign', self::COL_ANCHO_NOMBRE);
		$this->registerColCustom('name_usr_chg', "Modificado por", "renderUsrChg", self::COL_ANCHO_NOMBRE, true, 'Usuario que hizo el último cambio', true);
		
		// $this->registerColHidden('name_usr_chg', self::COL_ANCHO_NOMBRE);
		$this->registerColDateTime('modificado_dt', self::COL_ANCHO_FECHAHORA);
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	
	public function onGetData(&$items, &$total){
		$params = $this->getBaseParams();
		/*
		if (!$params) {
			return parent::onGetData($items, $total);
		}
		*/
		
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppHldIncidentModel::LoadListMain($items, $total, $params);
		
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
		
		// if (ExjUser::IsRolSuperAdmin()) {
		if (ExjUser::IsRolSuperOAdmin() ) {
			$btnMnuAcciones = self::_GetButtonMnuAcciones();
			if ($btnMnuAcciones) {
				$items[] = $btnMnuAcciones;
			}
		}
		
		/* --------------- */
		/*
		$verSubItems = array();
		$mnuItem = ExjUI::NewMenuItem('Incidente...', 'exj-btn-detalles');
		$mnuItem->setAction('viewIncident');
		$mnuItem->isInfoInc = true;
		$mnuItem->tooltip = 'Muestra Información del Incidente';
		
		$verSubItems[] = $mnuItem;
		
		$verSubItems[] = '-';
		*/

		$mnuItem = ExjUI::NewMenuItem('Respuestas del Incidente...', 'exj-btn-detalles');
		$mnuItem->setAction('respuestas');
		$mnuItem->isResponses = true;
		$mnuItem->tooltip = ExjText::_('Muestra las respuestas registrados del Incidente seleccionado');
		
		$verSubItems[] = $mnuItem;
		
		$btnMnuVer = ExjUI::NewBotonMenu('Ver', 'app-btn-view', $verSubItems, ExjText::_('Muestra un listado del Incidente seleccionado'));
		ExjUI::ApplyAction($btnMnuVer, 'vistasHldInc', true);
		$items[] = $btnMnuVer;
		
		
		$btnDoc = ExjUI::NewButton('Documentos...', ExjText::_('Permite adicionar o eliminar documentos relacionados al incidente seleccionado'), 'app-btn-links', 'docInc');
		$btnDoc->isDocs = true;
	//	$btnDoc->disabled = true;
		// $btnDoc->idStateAllowed = AppHldIncidentsData::ESTADO_TRABAJO_PROG;
		
		$items[] = $btnDoc;
		
		
		
		return $items;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Derecha
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasRight(){
		/*
		if (!ExjUser::IsRolSuperOAdmin()) {
			return null;
		}
		*/
		
		$items = array();
		
		// Exj::IncludeClass('AppHelpdesksData', 'exj_helpdesks');
		
		$estados = AppHelpdesksData::getLookupEstados(AppHldIncidentsData::ESTADO_CERRADO);
		
		if (!$estados || count($estados) == 0) {
			return null;
		}
		
		$estadoCerrado = $estados[0];
		
		$btnCerrar = ExjUI::NewButton('Cerrar Incidente...', ExjText::_('Permite <b>Cerrar</b> el incidente seleccionado, si el incidente está en estado <b>Resuelto</b>'), $estadoCerrado->css, 'closeInc');
		self::_ApplyItemUIEstado($btnCerrar, $estadoCerrado);
	//	$btnCerrar->disabled = true;
		$btnCerrar->idStateAllowed = AppHldIncidentsData::ESTADO_RESUELTO;
		
		$items[] = $btnCerrar;
		
		// print_r($items);
		
		return $items;
	}
	
	private static function _ApplyItemUIEstado(&$itemUI, $estado){
		$itemUI->idState = intval($estado->value);
		$itemUI->descState = $estado->description;
		if (!$itemUI->descState) {
			$itemUI->descState = $estado->text;
		}
		
		ExjUI::applyStyleColor($itemUI, $estado->color);
	}
	
	private static function _GetButtonMnuAcciones(){
		// Exj::IncludeClass('AppHldIncidentsData', 'exj_hld_incidents');
		// Exj::IncludeClass('AppHelpdesksData', 'exj_helpdesks');
		
		$estados = AppHelpdesksData::getLookupEstados();
		if (!$estados) {
			return null;
		}
		
		$accionesSubItems = array();
		
		$indexEstado = -1;
		foreach ($estados as $estado) {
			if (++$indexEstado == 0) {
				continue;
			}
			
			$mnuItem = ExjUI::NewMenuItem($estado->text, $estado->css);
			$mnuItem->setAction('accionHldInc');
			
			self::_ApplyItemUIEstado($mnuItem, $estado);
			
			$itemsAllowed = array();
			// seteo de items permitidos, según el estado anterior
			switch ($mnuItem->idState) {
				case AppHldIncidentsData::ESTADO_NUEVO:
					// $itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_CERRADO);
				break;

				case AppHldIncidentsData::ESTADO_ASIGNADO:
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_NUEVO);
				break;

				case AppHldIncidentsData::ESTADO_TRABAJO_PROG:
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_NUEVO);
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_ASIGNADO);
					
					// REAPERTURA DEL INCINDENTE
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_CERRADO);
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_RESUELTO);
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_PENDIENTE);
				break;

				case AppHldIncidentsData::ESTADO_RESUELTO:
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_TRABAJO_PROG);
				break;

				case AppHldIncidentsData::ESTADO_PENDIENTE:
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_TRABAJO_PROG);
				break;
				
				case AppHldIncidentsData::ESTADO_CERRADO:
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_RESUELTO);
					$itemsAllowed[] = self::_GetItemEstado($estados, AppHldIncidentsData::ESTADO_PENDIENTE);
				break;
				
				default:
					$itemAllowed = new stdClass();
					$itemAllowed->value = $mnuItem->idState;
					$itemAllowed->text = "ERROR ESTADO $mnuItem->idState DESCONOCIDO";
					$itemsAllowed[] = $itemAllowed;
				break;
			}
			
			$mnuItem->itemsAllowed = $itemsAllowed;
			
			$accionesSubItems[] = $mnuItem;
		}
		
		$btnMnuAcciones = ExjUI::NewBotonMenu('Acciones del Incidente', 'app-btn-acciones', $accionesSubItems, 'Aplica alguna acción al Incidente seleccionado');
		ExjUI::ApplyAction($btnMnuAcciones, 'accionesHldInc', true);
		
		return $btnMnuAcciones;
	}
	
	private static function _GetItemEstado($estados, $idState){
		$itemState = null;
		foreach ($estados as $item) {
			if ($item->value == $idState) {
				$itemState = $item;
			}
		}
		
		if (!$itemState) {
			$itemState = new stdClass();
		}
		
		return $itemState;
	}
	
}

?>