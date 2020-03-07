<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppHelpContentPanelMainModel
 * Modelo de Panel Principal para: Help Contents
 */
class AppHelpContentPanelMainModel extends ExjPanelMainModel {
	const PATH_HELP_PDF = 'storage/app/files/help/Ayuda_APP.pdf';

	public static function GetUrlHelpPdf(){
		$url = self::PATH_HELP_PDF;
		return $url;
	}
	
	/**
	 * overwrite. Inicio del Modelo Panel Principal
	 *
	 */
	public function panelInit(){
		$this->setNameModelController('help_contents');
		$this->removeTopToolbar();
		$this->setTitlePage('');
		$this->id = 'help_contents';
		$this->autoHeight = true;
		
		$this->setNameTopic('Content');
		$this->setOffsetHeight(-3);		
		
		$this->forceEnableViewLogPers(false);
	}
	
	/**
	 * overwrited. Carga los items que se presentarán en la UI
	 *
	 * @param array $itemsUI Pasado por referencia
	 * @param array $items
	 * @param int $total
	 */
	protected function loadItemsUI(&$itemsUI, $items, $total){
		// print_r($items);
		
		$itemsUI[] = $this->_getLinkOpenPDF($items);
		$itemsUI[] = $this->_getFramePDF($items);
	}
	
	private function _getLinkOpenPDF($items){
		$linkOpenPDF = new ExjUIPanel();
		$linkOpenPDF->setAutoHeight()
			->setBorder(false);

		$href = self::GetUrlHelpPdf();
		
		$html = '<div>';
		$html .= "<a id='lnkOpenHelpPDF' target='_blank' class='exj-help-pdf' href='$href'>Abrir PDF en una Nueva Página</a>";
		$html .= '</div>';
		
		$linkOpenPDF->setHtml($html);
		
	//	echo $linkOpenPDF->html;
		
		return $linkOpenPDF;
	}
	
	private function _getFramePDF($items){
		$framePDF = new ExjUIPanel();
		
		$framePDF->setHeight(452)->setWidth(956);

	//	$framePDF->width = '100%';
		
		$framePDF->setCls('exj-help-pdf');
		
		$framePDF->bodyCfg = new stdClass();
		$framePDF->bodyCfg->tag = 'iframe';
		$framePDF->bodyCfg->src = self::GetUrlHelpPdf();
		$framePDF->bodyCfg->style = 'border-style: inset;border-width: 2px;';
		
	//	$framePDF->bodyCfg->width = $framePDF->width;
	//	$framePDF->bodyCfg->height = '100%';
		
		return $framePDF;
	}
	
	/**
	 * overwrited. Devuelve el manejador del menú
	 *
	 * @return ExjHelperMenu
	 */
	protected function getHandlerMenu(){
		$hMenu = ExjHelperMenu::CreateAccessReadOnly();
		return $hMenu;
	}
	
	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	/*
	public function onGetData(&$items, &$total){
		// ExjRequest::setParamsQueryFromModelList($this);
		
		$isLoad =  AppHelpContentsModel::LoadDataHelpContents($this->getResponse(), $items, $this->getBaseParams());
		if ($items) {
			$total = count($items);
		}
		
	//	ExjRequest::clearParamsQuery();
		
		return $isLoad;
	}
	*/
	
}

?>