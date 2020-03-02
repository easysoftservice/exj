<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Class AppGlobalDataBrowsers
 *
 */
class AppGlobalDataBrowsers extends ExjObject {
	private $_browsers;
	
    public function __construct() {
    	$this->_browsers = array();
    	
    	$dataVers = new AppGlobalDataBrowsersVersions();
    	
    	// $desc = "Tiene una interfaz intuitiva, puede bloquear los virus, spywares y las ventanas emergentes. Descarga las páginas más rápido que nunca. Es muy fácil de instalar e importar tus favoritos";
    	$desc = "It has an intuitive interface, you can block viruses, spyware and popups. Download the pages faster than ever. It is very easy to install and will import your favorites";
    	$urlDownload = "http://firefox.uptodown.com/descargar";
    	$dataVers->reset();
    	$dataVers->add('2.x', 'Ext.isGecko2');
    	$dataVers->add('3.x', 'Ext.isGecko3', true);
    	$this->_add('Mozilla FireFox', 'Ext.isGecko', 'exj-nav-mozilla', $desc, $urlDownload, true, true, true, $dataVers, true);

//    	$desc = "Chrome tiene un gran número de funciones útiles integradas, entre las que se incluyen la traducción automática de páginas completas y el acceso a cientos de aplicaciones, extensiones y temas desde Chrome Web Store";
    	$desc = "Chrome has many useful features built, including the automatic translation of full pages and access to hundreds of apps, extensions and themes from the Chrome Web Store are included";
    	$urlDownload = "http://google-chrome.uptodown.com/descargar";
    	$dataVers->reset();
    	$this->_add('Chrome', 'Ext.isChrome', 'exj-nav-chrome', $desc, $urlDownload, true, true, true, $dataVers, true);

//    	$desc = "La visión de Opera es entregar la mejor experiencia de Internet en cualquier dispositivo. El objetivo de negocios principal de Opera es ganar el liderázgo global en el mercado para PCs/desktops y productos integrados";
    	$desc = "Opera's vision is to deliver the best Internet experience on any device. The main objective is to win business operates global leadership in the market for PC / desktops and embedded products";
    	$urlDownload = "http://www.opera.com/download/";
    	$dataVers->reset();
    	$this->_add('Opera', 'Ext.isOpera', 'exj-nav-opera', $desc, $urlDownload, true, true, true, $dataVers);

    	// $desc = "Incorpora compatibilidad mejorada con sitios y aplicaciones web, soporte para la autenticación mediante certificados personales, acceso al teclado estándar para la navegación y posibilidad de reanudar las descargas interrumpidas";
    	$desc = "Incorporates enhanced Web sites and applications, support for personal certificate authentication, access to the standard keyboard for navigation and ability to resume interrupted downloads compatibility";
    	$urlDownload = "http://safari.uptodown.com/descargar";
    	$dataVers->reset();
    	$dataVers->add('2.x', 'Ext.isSafari2');
    	$dataVers->add('3.x', 'Ext.isSafari3', true);
    	$dataVers->add('4.x', 'Ext.isSafari4');
    	$this->_add('Safari', 'Ext.isSafari', 'exj-nav-safari', $desc, $urlDownload, true, true, true, $dataVers);

//    	$desc = "WebKit es una plataforma para aplicaciones que funciona como base para el navegador web Safari, Google Chrome, Epiphany, Maxthon, Midori, Qupzilla entre otros";
    	$desc = "WebKit is an application platform that serves as the basis for web Safari, Google Chrome, Epiphany, Maxthon, Midori, among others QupZilla browser";
    	$urlDownload = "http://personalwebkit.uptodown.com/descargar";
    	$dataVers->reset();
    	$this->_add('WebKit', 'Ext.isWebKit', 'exj-nav-webkit', $desc, $urlDownload, false, false, false, $dataVers);

//    	$desc = "Es un navegador web desarrollado por Microsoft para el sistema operativo Microsoft Windows";
    	$desc = "It is a web browser developed by Microsoft for the Microsoft Windows operating system.";
//    	$desc .= "<br/>This browser is not recommended for low performance and security flaws can see the following article:<br/>";
//    	$desc .= "<a target='_blank' href='http://www.cronica.com.mx/notas/2004/131713.html' >New flaw in Internet Explorer</a>";
    	$urlDownload = "";
    	$dataVers->reset();
    	$dataVers->add('6.x', 'Ext.isIE6');
    	$dataVers->add('7.x', 'Ext.isIE7');
    	$dataVers->add('8.x', 'Ext.isIE8', true);
    	// $this->_add('Internet Explorer', 'Ext.isIE', 'exj-nav-ie', $desc, $urlDownload, false, false, false, $dataVers);
    	$this->_add('Internet Explorer', 'Ext.isIE', 'exj-nav-ie', $desc, $urlDownload, true, true, true, $dataVers);
    }
    
    private function _add($name, $js, $iconCls, $desc, $urlDownload, $isSupported=false, $canDownload=false, $canUpload=false, AppGlobalDataBrowsersVersions $dataVers=null, $isRecommend=false){
    	$item = new AppGlobalDataBrowser($dataVers);
    	
    	if (!$isSupported) {
    		$isRecommend = false;
    	}
    	
    	$item->name = $name;
    	$item->iconCls = $iconCls;
    	$item->isSupported = $isSupported;
    	$item->canDownload = $canDownload;
    	$item->canUpload = $canUpload;
    	$item->desc = $desc;
    	$item->js = $js;
    	$item->urlDownload = $urlDownload;
    	$item->isRecommend = $isRecommend;
    	
    	$this->_browsers[] = $item->toObject();
    }

    public function getData(){
    	$data = new stdClass();
    	
    	$data->items = $this->_browsers;
    	$data->total = count($this->_browsers);
    	
    	
    	return $data;
    }
    
    static function Get(){
    	$dataBrowsers = new AppGlobalDataBrowsers();
    	
    	return $dataBrowsers->getData();
    }
}

class AppGlobalDataBrowser extends ExjObject {
	static $_ID=0;
	protected $id=0;
	
	public $name='';
	public $iconCls='';
	public $isSupported=false;
	public $desc='';
	public $urlDownload='';
	public $canUpload=false;
	public $canDownload= false;
	public $js= '';
	public $isRecommend= false;
	
	public $dataVersions= null;
	
    public function __construct(AppGlobalDataBrowsersVersions $dataVersions=null) {
    	self::$_ID += 1;
    	$this->id = self::$_ID;
    	
    	if ($dataVersions) {
    		$this->dataVersions = $dataVersions->getData();
    	}
    }
}

class AppGlobalDataBrowserVersion extends ExjObject {
	public $id=0;
	protected $ver='';
	protected $js='';
	protected $isVerMin= false;
	
    public function __construct($ver, $js, $isVerMin=false) {
    	$this->ver = $ver;
    	$this->js = $js;
    	$this->isVerMin = $isVerMin;
    }
}

class AppGlobalDataBrowsersVersions extends ExjObject {
	private $_browsersVersions;
	private $_verMin = null;
	
    public function __construct() {
    	$this->_browsersVersions = array();
    }
    
    public function add($ver, $js, $isVerMin=false){
    	$dataVer = new AppGlobalDataBrowserVersion($ver, $js, $isVerMin);
    	$dataVer->id = count($this->_browsersVersions)+1;
    	
    	if ($isVerMin) {
    		$this->_verMin = $dataVer->toObject();
    	}
    	
    	$this->_browsersVersions[] = $dataVer->toObject();
    }
    
    public function reset(){
    	$this->_browsersVersions = array();
    	$this->_verMin = null;
    }
    
    public function getData(){
    	$data = new stdClass();
    	
    	$data->verMin = $this->_verMin;
    	$data->items = $this->_browsersVersions;
    	$data->total = count($this->_browsersVersions);
    	
    	return $data;
    }
    
}

?>