<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComponentsListModel
 * Modelo de lista para: Components
 */
class AppComponentsListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('components', 'components');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Componentes', 'id_componente');
		$this->nameTopics = 'Componentes';
		$this->nameTopic = 'Componente';
		$this->defaultSort = 'gro.id';
		$this->fixSortDesc();
		
		$this->autoAddColInfoUltimoCambio();
		$this->getView()->setForceFit(false);
		$this->fixColAutoActionEdit('nombre_com');
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldId('id_cat');
		$this->registerFieldId('id_group_joomla');

		$this->registerFieldString('nombre_com', 'Componente');
		$this->registerFieldString('name_cat', 'Categoría');
		$this->registerFieldString('nombre_tabla_com', 'Tabla');
		$this->registerFieldString('plural_com', 'Plural');
		$this->registerFieldString('singular_com', 'Singular');
		$this->registerFieldInt('published', 'Publicado');
		$this->registerFieldInt('existDirCmp', 'Existe');
		
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('nombre_com', self::COL_ANCHO_DETALLE);
		$this->registerCol('name_cat', self::COL_ANCHO_DETALLE);
		$this->registerColTextSino('published', self::COL_ANCHO_NOMBRE);
		$this->registerCol('nombre_tabla_com', self::COL_ANCHO_NOMBRE);
		$this->registerCol('plural_com', self::COL_ANCHO_NOMBRE);
		$this->registerCol('singular_com', self::COL_ANCHO_NOMBRE);
		
		$this->registerColTextSino('existDirCmp', self::COL_ANCHO_NOMBRE, '', false);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppComponentsModel::CargarListaPrincipal($this->getResponse(), $items, $total, $this->getBaseParamsCriteriaClone());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Izquierda
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		$items = array();
		
		$items[] = ExjUI::NewButton('Generar...', 'Genera un componente en base a una tabla', 'exj-btn-new-win', 'generateCmp');
		
		return $items;
	}

	/**
     * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Derecha
     *
     * @return array
     */
    public function getItemsTopbarExtrasRight() {
        $items = array();
		
		$items[] = ExjUI::NewButton(
			'Eliminar Grupo & AXO...', 
			'Eliminar Grupo AXO, permisos y menú', 
			'exj-btn-delete', 
			'delAllGrpAXO'
		);
		
		return $items;
    }
}

?>