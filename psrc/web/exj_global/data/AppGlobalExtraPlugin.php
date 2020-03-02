<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class AppGlobalExtraPlugin extends ExjPluginDisplay {
	/**
	 * Carga de items para presentación
	 *
	 */
	protected function loadInfoExtraDisplay(){
		$this->addItemDisplay(
			strtoupper(ExjUser::GetUserType()), ExjUser::GetNomsApes()
		)
			->addItemDisplay('Autorizado a', ExjUser::GetNombreEmpresa());
	}
}
