<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppDeploysCriteriaModel
 */
class AppDeploysCriteriaModel extends ExjCriteriaModel {
	public $num_filesjs;
	public $num_filesphp;
	public $num_filescss;
	public $num_filesimg;
	public $version_dpy;
	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	protected function criteriaRegisterFields(){
		$this->registerFieldString('version_dpy', 'Versión');
		
		$this->registerFieldInt('num_filesjs', 'js');
		$this->registerFieldInt('num_filesphp', 'php');
		$this->registerFieldInt('num_filescss', 'css');
		$this->registerFieldInt('num_filesimg', 'imagen');
	}
	
	/**
	 * overwrite. Registro de Controles para la UI
	 *
	 */
	protected function criteriaRegisterControlsUI(){
    	$this->registerControlUI(ExjUI::NewTextField('version_dpy', 'Versión', '90%'));
    	
    	$this->registerControlUI(ExjUI::NewNumberField('num_filesjs', 'JavaScript', '60%', true));
    	$this->registerControlUI(ExjUI::NewNumberField('num_filesphp', 'PHP', '60%', true));
    	$this->registerControlUI(ExjUI::NewNumberField('num_filescss', 'CSS', '60%', true));
    	$this->registerControlUI(ExjUI::NewNumberField('num_filesimg', 'Imagen', '60%', true));
	}
}
?>