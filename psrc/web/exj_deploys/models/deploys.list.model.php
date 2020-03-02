<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDeploysListModel
 * Modelo de lista para: Deploys
 */
class AppDeploysListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('deploys', 'deploys');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Deploys', 'id_deploy');
		$this->nameTopics = 'Deploys';
		$this->nameTopic = 'Deploy';
		$this->defaultSort = 'modificado_dt';
		$this->fixSortDesc();
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('version_dpy', 'Versión');
		$this->registerFieldString('obs_dpy', 'Observación');
		$this->registerFieldInt('num_filesphp', 'PHP');
		$this->registerFieldInt('num_filesjs', 'JS');
		$this->registerFieldInt('num_filescss', 'CSS');
		$this->registerFieldInt('num_filesimg', 'IMG');
		
		$this->registerFieldInt('num_filesotros', 'Otros');
		$this->registerFieldInt('num_filesjs_encoded', 'js Pack');
		$this->registerFieldString('size_filesjs_encoded', 'Tamaño jsPacks');
		
		$this->registerFieldInt('num_totalfiles', 'Total de Archivos');
		$this->registerFieldInt('is_copied_preprod', 'Pre Producción');
		
		$this->registerFieldString('url_dpy', 'URL');
		$this->registerFieldString('path_dpy', 'Path Físico');
		$this->registerFieldString('file_bkdb', 'BK DB');
		$this->registerFieldString('isCopiedPreProd', 'PreProd');
		
		$this->registerFieldString('name_usr', 'Modificado por');
		$this->registerFieldDateTime('modificado_dt', 'Cambio');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('version_dpy', 9);
	//	$this->registerCol('url_dpy', 24);
//		$this->registerCol('isCopiedPreProd', 9);
		$this->registerColCustom('is_copied_preprod', 'PreProd', 'renderIsCopiedPreProd', 9);
		$this->registerCol('path_dpy', 15);
		$this->registerColHidden('file_bkdb', 12);
		
		$widthColNums = 5;
		$this->registerCol('num_filesjs', $widthColNums, true, false);
		$this->registerCol('num_filesphp', $widthColNums, true, false);
		$this->registerCol('num_filescss', $widthColNums, true, false);
		$this->registerCol('num_filesimg', $widthColNums, true, false);
		$this->registerCol('num_filesotros', $widthColNums+2, true, false);
		
		$this->registerColCustom('num_totalfiles', 'Total', 'renderNumFilesTot', $widthColNums+3);

		$this->registerCol('num_filesjs_encoded', $widthColNums+3, true, false);
		$this->registerCol('size_filesjs_encoded', $widthColNums+9);
		
		$this->registerCol('obs_dpy', 21);
		
		$this->registerCol('name_usr', 15);
		$this->registerColDateTime('modificado_dt');
	}	
	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppDeployModel::loadListDeploys($items, $total);
		
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

		$items[] = ExjUI::NewButton('Ver Componentes...', 'Lista los componentes actuales de la App', 'exj-btn-view', 'view_comps');
		$items[] = '-';
	//	$items[] = ExjUI::NewButton('Ofuscar PHP...', 'Ofusca el código php del Deploy seleccionado', 'exj-btn-view', 'ofs_php');
		
		$items[] = ExjUI::NewButton('Copiar a PreProducción...', 'Copia el deploy selecionado a pre-producción', 'exj-btn-view', 'copy_prod_local');
		$items[] = ExjUI::NewButton('BK db', 'Backup de la base de datos', 'exj-btn-view', 'bk_db');
		
		
		return $items;
	}
	
}

?>