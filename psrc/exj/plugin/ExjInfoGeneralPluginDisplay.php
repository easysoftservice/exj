<?php

defined('_JEXEC') or die('Acceso restringido');

class ExjInfoGeneralPluginDisplay extends ExjPluginDisplay {
	public function loadDataUI() {
		$html = array();

		$html[] = $this->getInfoHtmlAppUI();
		$html[] = $this->getHtmlImgLogoFront(['width' => '300px']);


		$html = implode('', $html);

		$dataUI = new ExjUIPanel();
		$dataUI->setLayout('anchor')
			->setBorder(false)
			->setHtml($html);

		$this->setDataUI($dataUI);
	}

}

?>