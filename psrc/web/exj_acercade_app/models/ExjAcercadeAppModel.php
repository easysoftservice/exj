<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjAcercadeAppModel
 * Modelo para AcercadeApp
 */
class ExjAcercadeAppModel extends ExjModel {
	public static function GetDataInfo(ExjResponse $response) {
		ExjUser::SetAutoEncodeISO(true);

		if (class_exists('AppAcercadeAppModel')) {
			if (method_exists(AppAcercadeAppModel::class, 'GetDataInfo')) {
				AppAcercadeAppModel::GetDataInfo($response);
				if (!$response->haveData()) {
					return;
				}
			}
		}

		$win = new ExjUIWindow();

		$html = array();

		$html[] = '<h1>'.Exj::GetTitleApp().'</h1>'.
		          '<h2>Versi�n: '. Exj::GetVersionApp(). '</h2>';

		$html[] = Exj::GetNameApp().'.';
		$html[] = '<b>Creado por</b>: EasySoft Service. RUC: 1103222715001';
		$html[] = '';
		$html[] = '<b>Soporte</b>: Byron C�rdova. <b>Tel�fono</b>: (593) 0991277547';
		$html[] = '<b>correo</b>: byron.cordova.mora@gmail.com <b>skype</b>: bvcordova';
		$html[] = '';
		$html[] = '<b>Autorizado a </b>: ' . ExjUser::GetNombreEmpresa();
		$html[] = '';
		$html[] = Exj::GetNameApp().'. '.Exj::GetDescApp().'.';
		$html[] = '';
		$html[] = 'Navegador: {navigator.userAgent}';
		// $html[] = 'Tama�o de Pantalla (w*h): {Exj.calcWidth()} * {Exj.calcHeight()}';
		$html[] = '';
		$html[] = Exj::GetNameApp().'. Todos los derechos reservados.';

		$html = implode('<br/>', $html);
		$win->setHtml($html)->setMaximizable(false);

		$response->setDataObject($win);
	}
}

?>