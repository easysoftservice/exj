<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo Criteria para Contribuyentes SRI
 *
 */
class AppSriContribuyentesCriteriaModel extends ExjCriteriaModel {
	
	/**
	 * Número Ruc
	 *
	 * @var string
	 */
	public $numero_ruc;
	
	/**
	 * Razon Social
	 *
	 * @var string
	 */
	public $razon_social;
	public $nombre_comercial;
	public $nombre_fantasia_comercial;

	public $descripcion_provincia;
	public $descripcion_canton;
	public $descripcion_parroquia;

	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('numero_ruc', 'RUC');
		$this->registerFieldString('razon_social', 'Razón Social');
		$this->registerFieldString('nombre_comercial', 'Nombre Comercial');
		$this->registerFieldString('nombre_fantasia_comercial', 'Nombre Fanstasía');
		$this->registerFieldString('descripcion_provincia', 'Provincia');
		$this->registerFieldString('descripcion_canton', 'Cantón');
		$this->registerFieldString('descripcion_parroquia', 'Parroquia');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
		$this->registerControlUI(ExjUI::NewTextField('numero_ruc','', '102px'));
		$this->registerControlUI(ExjUI::NewTextField('razon_social'));
		$this->registerControlUI(ExjUI::NewTextField('nombre_comercial'));
		$this->registerControlUI(ExjUI::NewTextField('nombre_fantasia_comercial'));
        
		$this->registerControlUI(ExjUI::NewTextField('descripcion_provincia'));
		$this->registerControlUI(ExjUI::NewTextField('descripcion_canton'));
		$this->registerControlUI(ExjUI::NewTextField('descripcion_parroquia'));
	}
}
?>