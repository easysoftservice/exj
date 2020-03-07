<?php

defined('_JEXEC') or die('Acceso restringido');

class ExjInfoGeneralPluginDisplay extends ExjPluginDisplay {
	public function loadDataUI() {
		$colInfoApp = ExjUIItemColumn::Create()
			->setWidth(600)
			->setItems($this->getInfoAppUI());

		$colImg = ExjUIItemColumn::Create()
			->setColumnWidth(1)
			->setHtml($this->getHtmlImgLogoFront());
	//	$colImg->style = 'color:red';

		$dataUI = new ExjUIPanel();
		$dataUI->setLayout('column')
			->setBorder(false)
			->setDefaults([
				'border' => false
			])
			->setItems([
				$colInfoApp,
				$colImg
			]);

		$this->setDataUI($dataUI);
	}

}

?>