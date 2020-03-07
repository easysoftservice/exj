<?php

// no direct access
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Plugin para presentación en la UI
 *
 */
class ExjPluginDisplay extends ExjPlugin {
	private $_dataUI = null;
	private $_dataGlobal = null;
	
	public function __construct(&$scope){
		parent::__construct($scope);
	}

	public function setDataGlobal($value) {
		$this->_dataGlobal = $value;
		return $this;
	}

	public function getDataGlobal() {
		return $this->_dataGlobal;
	}

	public function getInfoUserDataGlobal() {
		return $this->getDataGlobal()->infoUser;
	}

	public function getValuePropInfoUser($prop) {
		$value = '';
		if ($data = $this->getInfoUserDataGlobal()) {
			$value = $data->$prop;
		}

		return $value;
	}

	public function getUriLogoFrontDataGlobal() {
		return $this->getValuePropInfoUser('uri_logo_frontal');
	}

	protected function getHtmlImgLogoFront($props = null) {
		$bImg = ExjBuildHtml::CreateImg($this->getUriLogoFrontDataGlobal());
		$bImg->addAttr('unselectable', 'on')
			->addAttr('class', 'vu-img-logo-app')
			->addAttr('width', '99%')
			->applyAttrs($props);

		return $bImg->toHtml();
	}

	protected function getHtmlLabelEmpresa() {
		return ExjBuildHtml::Create('span')
			->setId('exjInfoMain_prefixOfc')
			->setContent('EMPRESA')
			->toHtml();
	}
	
	protected function getHtmlNomEmpresa() {
		return ExjBuildHtml::Create('span')
			->setId('exjInfoMain_nom_empresa')
			->setContent(ExjUser::GetNombreEmpresa())
			->toHtml();
	}

	

	protected function getHtmlNameCiuCom() {
		// name_city_prs
		return ExjBuildHtml::Create('span')
			->setId('exjInfoMain_name_ciu_com')
			->setContent($this->getValuePropInfoUser('name_ciu_com'))
			->toHtml();
	}

	protected function getHtmlNameSitMain() {
		return ExjBuildHtml::Create('span')
			->setId('exjInfoMain_name_sit_main')
			->setContent($this->getValuePropInfoUser('name_sit_main'))
			->toHtml();
	}

	protected function getHtmlNameState() {
		return ExjBuildHtml::Create('span')
			->setId('exjInfoMain_name_state')
			->setContent($this->getValuePropInfoUser('name_state'))
			->toHtml();
	}

	protected function getHtmlNameLang() {
		return ExjBuildHtml::Create('span')
			->setContent($this->getValuePropInfoUser('name_lang'))
			->toHtml();
	}

	protected function getHtmlInfoDebug() {
		if (!ExjUser::IsModeDebug()) {
			return '';
		}

		return ExjBuildHtml::Create('div')
			->addAttr('style', 'color:red')
			->setContent('(<b>MODO DEBUG ESTA ACTIVO</b>)')
			->toHtml();
	}

	protected function getHtmlNameCompany() {
		return ExjBuildHtml::Create('div')
			->addAttr('class', 'exj-title-main')
			->setContent($this->getValuePropInfoUser('name_company'))
			->toHtml();
	}

	protected function getHtmlNamesPerNumDoc() {
		return ExjBuildHtml::Create('span')
			->setContent(
				$this->getValuePropInfoUser('apes_noms_persona').' - '.
				$this->getValuePropInfoUser('nro_doc_persona')
			)
			->toHtml();
	}
		
	public function setDataUI($value){		
		$this->_dataUI = $value;
		return $this;
	}
	
	public function getDataUI(){
		return $this->_dataUI;
	}
	
	/**
	 * Carga de items para presentación
	 *
	 */
	public function loadDataUI() {
		
	}

	protected function getInfoAppUI() {
		$pnl = new ExjUIPanelTableLabelValue();
		$pnl->setId('bubble-markup')->setCls('headerTitle')->setBorder(false);
		// $pnl->setTitle(Exj::GetTitleApp());
		$pnl->addRowHtml($this->getHtmlNameCompany())
			->addLabelValue(strtoupper(ExjUser::GetUserType()), ExjUser::GetNomsApes())
			->addLabelValue(
				'CIUDAD', $this->getHtmlNameCiuCom(). ' - '. $this->getHtmlNameState()
			)
			->addLabelValue('LENGUAJE', $this->getHtmlNameLang())

			->addLabelValue('AUTORIZADO A', $this->getHtmlNomEmpresa())
			->addRowHtml($this->getHtmlInfoDebug());

		return $pnl;
	}
}
?>