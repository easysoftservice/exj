<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo de lista Contribuyentes SRI
 *
 */
class AppSriContribuyentesListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('sri_contribuyentes');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Contribuyentes SRI', 'id_contribuyente');
		$this->nameTopics = 'Contribuyentes SRI';
		$this->nameTopic = 'Contribuyente SRI';
		$this->defaultSort = 'numero_ruc';
		
		$this->autoAddColInfoUltimoCambio();
		$this->getView()->setForceFit(false);
		$this->fixColAutoActionEdit('numero_ruc');
	}
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldId('id_act_eco', 'Id Actividad Económica');
		$this->registerFieldString('numero_ruc', 'RUC');
		$this->registerFieldString('razon_social', 'Razón Social');
		$this->registerFieldString('nombre_comercial', 'Nombre Comercial');
		$this->registerFieldString('estado_contribuyente', 'Estado Contrib.');
		$this->registerFieldString('clase_contribuyente', 'Clase Contrib.');
		$this->registerFieldDate('fecha_inicio_actividades', 'F. Inicio Actividades');
		$this->registerFieldDate('fecha_actualizacion', 'F. Actualización');
		$this->registerFieldDate('fecha_suspension_definitiva', 'F. Suspension Definitiva');
		$this->registerFieldDate('fecha_reinicio_actividades', 'F. Reinicio Actividades');
		$this->registerFieldInt('obligado', 'Obligado');
		$this->registerFieldString('tipo_contribuyente', 'Tipo Contribuyente');
		$this->registerFieldInt('numero_establecimiento', 'Nro Estab.');
		$this->registerFieldString('nombre_fantasia_comercial', 'Nombre Fantasia Comercial');
		$this->registerFieldString('calle', 'Calle');
		$this->registerFieldString('numero', 'Número');
		$this->registerFieldString('interseccion', 'Intersección');
		$this->registerFieldString('estado_establecimiento', 'Estado Establecimiento');
		$this->registerFieldString('descripcion_provincia', 'Provincia');
		$this->registerFieldString('descripcion_canton', 'Cantón');
		$this->registerFieldString('descripcion_parroquia', 'Parroquia');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('numero_ruc', self::COL_ANCHO_RUC);
		$this->registerCol('razon_social', self::COL_ANCHO_DETALLE);
		$this->registerCol('nombre_comercial', self::COL_ANCHO_NOMBRE);
		$this->registerCol('estado_contribuyente', self::COL_ANCHO_NOMBRE);
		$this->registerCol('clase_contribuyente', self::COL_ANCHO_NOMBRE);
		$this->registerColDate('fecha_inicio_actividades', self::COL_ANCHO_FECHA);
		$this->registerColDate('fecha_actualizacion', self::COL_ANCHO_FECHA);
		$this->registerColDate('fecha_suspension_definitiva', self::COL_ANCHO_FECHA);
		$this->registerColDate('fecha_reinicio_actividades', self::COL_ANCHO_FECHA);
		$this->registerColTextSino('obligado', self::COL_ANCHO_ESTADO);
		$this->registerCol('tipo_contribuyente', self::COL_ANCHO_NOMBRE);
		$this->registerColInt2('numero_establecimiento', self::COL_ANCHO_CANTIDAD,'',true,'Número de Establecimientos');
		$this->registerCol('nombre_fantasia_comercial', self::COL_ANCHO_NOMBRE);
		// $this->registerCol('calle', self::COL_ANCHO_NOMBRE);
		// $this->registerCol('numero', self::COL_ANCHO_NOMBRE);
		// $this->registerCol('interseccion', self::COL_ANCHO_NOMBRE);
		$this->registerCol('estado_establecimiento', self::COL_ANCHO_NOMBRE);
		$this->registerCol('descripcion_provincia', self::COL_ANCHO_NOMBRE);
		$this->registerCol('descripcion_canton', self::COL_ANCHO_NOMBRE);
		$this->registerCol('descripcion_parroquia', self::COL_ANCHO_NOMBRE);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppSriContribuyentesModel::CargarListaPrincipal($this->getResponse(), $items, $total, $this->getBaseParamsCriteriaClone());
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
}

?>