<?php


/**
 * Helper de Variables de correo
 *
 */
class AppMailVarHelper {
	/* Variables */
	const VAR_FROMNAME = '{fromname}';
	const VAR_LINK_SITE = '{link_site}';
	const VAR_TITULO = '{titulo}';
	const VAR_USUARIONOMBRE = '{usuarionombre}';
	const VAR_USUARIOEMAIL = '{usuarioemail}';
	const VAR_MENSAJE = '{mensaje}';
	const VAR_SITENAME = '{sitename}';
	const VAR_MAILFROM = '{mailfrom}';
	const VAR_USUARIO_NOM_FROM_EMAIL = '{usuario_nom_from_email}';
	
    static function GetVarsTpl($text){
    	$tplVars = array();
    	
		preg_match_all('/{[a-z_]+}/', $text, $matches);
		if (!$matches || count($matches) == 0) {
			return $tplVars;
		}
		// print_r($matches);
		
		$vars = $matches[0];
//		print_r($vars);
		
		foreach ($vars as $varx) {
			if (in_array($varx, $tplVars)) {
				continue;
			}
			
			$tplVars[] = $varx;
		}
		
    	
    	return $tplVars;
    }
	
    static function ValidateVarsInTextHTML($textHTML, &$varsInvalid, $vars=null){
    	if (!$vars) {
    		$vars = self::GetVarsTpl($textHTML);
    	}
    	
    	if (count($vars) == 0) {
    		return true;
    	}
    	
    	$varsInvalid = array();
    	foreach ($vars as $varx) {
    		if (!self::IsValidVar($varx)) {
    			$varsInvalid[] = $varx;
    		}
    	}
    	
    	return (count($varsInvalid) == 0 ? true:false);
    }

 	static function GetInfoVar($var, $titulo=null, $mensaje=null, $email_to=null){
    	
 		$info = new stdClass();
 		$info->id = $var;
 		$info->var = $var;
 		
		$info->desc = 'No está soportada la variable: ' . $var;
		$info->sample = '';
		$info->isValid = true;
		$info->isSampleValue = false;
 		
 		switch ($var) {
 			case self::VAR_FROMNAME:
 				$info->sample = self::GetValueVarFromName();
 				$info->desc = 'Nombre de la aplicación. Este valor se puede cambiar desde el backend';
 				$info->isSampleValue = true;
 			break;

 			case self::VAR_LINK_SITE:
 				$info->sample = self::GetValueVarLinkSite();
 				$info->desc = 'Link HTML de la aplicación actual';
 				$info->isSampleValue = true;
 			break;

 			case self::VAR_MAILFROM:
 				$info->sample = self::GetValueVarMailFrom();
 				$info->desc = 'Correo desde el cual se va a eniar el correo. Este valor se puede cambiar desde el backend';
 				$info->isSampleValue = true;
 			break;

 			case self::VAR_SITENAME:
 				$info->sample = self::GetValueVarSiteName();
 				$info->desc = 'Nombre de la aplicación actual. Este valor está en el backend';
 				$info->isSampleValue = true;
 			break;

 			case self::VAR_USUARIOEMAIL:
 				$info->sample = self::GetValueVarUsuarioEmail();
 				$info->desc = 'Correo del usuario actual que está logueado';
 				$info->isSampleValue = true;
 			break;

 			case self::VAR_USUARIONOMBRE:
 				$info->sample = self::GetValueVarUsuarioNombre();
 				$info->desc = 'Nombres del usuario actual que está logueado';
 				$info->isSampleValue = true;
 			break;

 			case self::VAR_USUARIO_NOM_FROM_EMAIL:
 				if ($email_to === null) {
 					$email_to = 'bvcordova@utpl.edu.ec';
 				}
 				else {
 					$info->isSampleValue = true;
 				}
 				$info->sample = self::GetValueVarUsuarioNombreFromEmail($email_to);
 				$info->desc = 'Nombres de la persona a quien se le envia el correo';
 			break;

 			case self::VAR_TITULO:
 				if ($titulo === null) {
 					$titulo = 'Este es ejemplo de Título de la plantilla';
 				}
 				else {
 					$info->isSampleValue = true;
 				}
 				$info->sample = $titulo;
 				$info->desc = 'Título de la plantilla';
 			break;

 			case self::VAR_MENSAJE:
 				if ($mensaje === null) {
 					$mensaje = 'Este es un mensaje de ejemplo';
 				}
 				else {
 					$info->isSampleValue = true;
 				}
 				
 				$info->sample = $mensaje;
 				$info->desc = 'Mensaje del correo';
 			break;
 			
 			default:
 				$info->isValid = false;
 				$info->isSampleValue = false;
 			break;
 			
 		}
 		
 		return $info;
    }
    
    static function GetNamesVars(){
    	$namesVars = array();
    	
    	$namesVars[] = self::VAR_FROMNAME;
    	$namesVars[] = self::VAR_LINK_SITE;
    	$namesVars[] = self::VAR_MAILFROM;
    	$namesVars[] = self::VAR_MENSAJE;
    	$namesVars[] = self::VAR_SITENAME;
    	$namesVars[] = self::VAR_TITULO;
    	$namesVars[] = self::VAR_USUARIO_NOM_FROM_EMAIL;
    	$namesVars[] = self::VAR_USUARIOEMAIL;
    	$namesVars[] = self::VAR_USUARIONOMBRE;
    	
    	return $namesVars;
    }

 	static function GetVarsList($addFieldValue = false){
 		$list = array();
 		
 		$vars = self::GetNamesVars();
 		foreach ($vars as $varx) {
 			$itemVar = self::GetInfoVar($varx);
 			
 			if ($addFieldValue) {
	 			$itemVar->value = '(El valor es calculado)';
	 			if ($itemVar->isSampleValue) {
	 				$itemVar->value = $itemVar->sample;
	 			}
 			}
 			
 			$list[] = $itemVar;
 		}
 		
 		return $list;
    }
    
    static function IsValidVar($nameVar){
    	if (!$nameVar) {
    		return false;
    	}
    	
    	$vars = self::GetNamesVars();
    	return in_array($nameVar, $vars);
    }
    
    
	
 	static function GetValueVarFromName(){
    	global $mainframe;
    	return $mainframe->getCfg('fromname');
    }
    static function GetValueVarMailFrom(){
    	global $mainframe;
    	return $mainframe->getCfg('mailfrom');
    }
    static function GetValueVarSiteName(){
    	global $mainframe;
    	return $mainframe->getCfg('sitename');
    }

    static function BuildSubject($subject){
    	$text = '['.self::GetValueVarSiteName().'] ' . $subject;
    	
    	return $text;
    }
    
    static function GetValueVarUsuarioNombre(){
    	return ExjUser::GetNames();
    }
    static function GetValueVarUsuarioEmail(){
    	return ExjUser::GetEmail();
    }
    
    static function GetValueVarUsuarioNombreFromEmail($email_to){
    	$infoUser = AppAdminMailsData::getInfoUserFromEmail($email_to);
    	
    	if ($infoUser) {
    		return $infoUser->usuario_nombres;
    	}
    	
    	$pos = strpos($email_to, '@');
		if ($pos === false) {
			return $email_to;
		}
		return substr($email_to, 0, $pos);
    }
    
    static function GetValueVarLinkSite(){
    	
    	$urlSite = Exj::GetServerURLClient();
    	
    	$siteName = self::GetValueVarSiteName();

    	$linkSite  = '<span>';
		$linkSite .= "<a href='$urlSite'>$siteName</a>";
		$linkSite .= '</span>';
		
    	return $linkSite;
    }	
	
    /**
     * Remplaza las variables contenidas en el priemr parámetro
     *
     * @param string $textHTML
     * @param string $msgError
     * @param string $mensaje No es requerido
     * @param string $titulo No es requerido
     * @return string Retornas el texto modificado
     */
    static function Render($textHTML, &$msgError, $mensaje=null, $titulo=null, $email_to=null){
    	$msgError = '';
    	if (!$textHTML) {
    		return $textHTML;
    	}
    	
    	if ($mensaje) {
    		$mensaje = self::Render($mensaje, $msgError);
    	}
    	if ($msgError) {
    		return false;
    	}
    	
    	if ($titulo) {
    		$titulo = self::Render($titulo, $msgError);
    	}
    	if ($msgError) {
    		return false;
    	}
    	
    	$vars = self::GetVarsTpl($textHTML);
    	$varsInvalid = array();
    	if (!self::ValidateVarsInTextHTML($textHTML, $varsInvalid, $vars)) {
    		$msgError = 'Las siguientes variables no son soportadas:<br/>';
    		$msgError .= implode(', ', $varsInvalid);
    		return false;
    	}
    	
    	$textChanged = $textHTML;
    	
    	foreach ($vars as $varx) {
    		$valueVar = null;
    		
    		$infoVar = self::GetInfoVar($varx, $titulo, $mensaje, $email_to);
    		if (!$infoVar->isValid) {
    			$msgError .= '<br/>'.$infoVar->desc;
    			continue;
    		}
    		
    		if ($infoVar->isSampleValue) {
    			$valueVar = $infoVar->sample;
    		}
    		
    		if ($valueVar === null) {
    			continue;
    		}
    		
    		$textChanged = str_replace($varx, $valueVar, $textChanged);
    	}
    	
    	self::ChangeCharsHTML($textChanged);
    	
    	return $textChanged;
    }   
    
    static function ChangeCharsHTML(&$textHTML){
		$textHTML = str_replace('<p> </p>', '<br/>', $textHTML);
		ExjHelper::convertCharsTildeToHTML($textHTML);
    }

}

?>