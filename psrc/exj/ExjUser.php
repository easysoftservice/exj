<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjUser {
	private static $_jUser=null;
    private static $_autoEncodeISO = false;

	public static function GetUserJoomla(){
		if (!self::$_jUser) {
			self::$_jUser = & JFactory::getUser();
		}
		
		return self::$_jUser;
	}

	protected static function GetPropjUser($prop, $valDef=null) {
		if ($usr=self::GetUserJoomla()) {
			if ($usr->id > 0) {
				return self::NormalizeValue($usr->$prop);
			}
		}

        return $valDef;
	}

    public static function GetId() {
    	return self::GetPropjUser('id', 0);
    }
	public static function GetGID() {
		return self::GetPropjUser('gid');
    }

    public static function GetNames() {
        return self::GetPropjUser('name','');
    }

    public static function GetUserName() {
    	return self::GetPropjUser('username','');
    }

    public static function GetEmail() {
    	return self::GetPropjUser('email','');
    }

    /**
     * Devuelve el nombre del tipo de usuario logueado
     *
     * @return string Ej: Administrador
     */
    public static function GetUserType() {
    	return self::GetPropjUser('usertype','');
    }

    /**
     * Indica si el usuario está logueado
     *
     * @return bool retorna false si se ha terminado el tiempo de sesión del usuario
     */
    public static function IsLogin() {
    	// $usr = & JFactory::getUser();
    	if ($usr=self::GetUserJoomla()) {
			return ($usr->id && $usr->id > 0);
		}
		return false;
    }

    public static function IsRolSuperAdmin() {
        return (self::GetGID() == Exj::GetValueCfg('ugidSuperAdmin'));
    }
    public static function IsRolAdministrador() {
    	return (self::GetGID() == Exj::GetValueCfg('ugidAdministrador'));
    }
    public static function IsRolContabilidad() {
    	return (self::GetGID() == Exj::GetValueCfg('ugidContabilidad'));
    }

    public static function IsRolPropietario() {
    	return (self::GetGID() == Exj::GetValueCfg('ugidPropietario'));
    }

    public static function IsRolRecaudador() {
    	return (self::GetGID() == Exj::GetValueCfg('ugidRecaudador'));
    }

    public static function IsRolCliente() {
    	return (self::GetGID() == Exj::GetValueCfg('ugidCliente'));
    }

    public static function IsRolProveedor() {
    	return (self::GetGID() == Exj::GetValueCfg('ugidProveedor'));
    }

    public static function IsRolSuperOAdmin() {
    	return (self::IsRolAdministrador() || self::IsRolSuperAdmin());
    }

    public static function IsRolSuperOAdminOContabilidad() {
    	return (self::IsRolSuperOAdmin() || self::IsRolContabilidad());
    }
    public static function IsRolSuperOAdminOPropietario() {
    	return (self::IsRolSuperOAdmin() || self::IsRolPropietario());
    }


    /**
     * Obtiene información del Usuario logueado
     *
     * @param bool $forceReload
     * @return ExjHelperInfoUser
     */
    public static function &GetUserSys($forceReload = false) {
        static $infoUser;

        //  $forceReload = true;

        if (!isset($infoUser) || ($infoUser === null) || $forceReload) {
            $infoUser = new ExjHelperInfoUser();
        }
        if (!$infoUser->id_company) {
            // echo '<br/>NO EXISTE ID COMPAÑIA';
            $infoUser = new ExjHelperInfoUser();
        }

        return $infoUser;
    }

    public static function IsModeDebug() {
        return (self::GetUserSys()->enable_debug ? true : false);
    }

    /**
     * Devuelve el id de la empresa actual del usuario logueado
     *
     * @return int Id de Compania
     */
    public static function GetIdCompania() {
        return self::GetUserSys()->id_company;
    }

    static function GetNombreCompania() {
        return self::NormalizeValue(self::GetUserSys()->name_company);
    }

    public static function GetIdPais() {
        return self::GetUserSys()->id_pais;
    }

    public static function GetIdCiudad() {
        return self::GetUserSys()->id_sit;
    }

    static function GetNombreLenguaje() {
        return self::NormalizeValue(self::GetUserSys()->name_lang);
    }

    public static function GetAcronimoLenguaje() {
        return self::GetUserSys()->acronym_lang;
    }

    public static function GetOffsetTime() {

        $offset_time = self::GetUserSys()->offset_time;
        if ($offset_time) {
            $offset_time = Exj::ParseInt($offset_time, 0);
        }
        else {
            $offset_time = 0;
        }

        return $offset_time;
    }

    static function GetNombrePais() {
        return self::NormalizeValue(self::GetUserSys()->nom_pais);
    }

    public static function GetEmailSys() {
        return self::NormalizeValue(self::GetUserSys()->email);
    }

    /**
     * Devuelve el ID del empresa del usuario logueado
     *
     * @return int 
     */
    public static function GetIdEmpresa() {
        return self::GetUserSys()->id_empresa;
    }

    public static function GetIdEmisor() {
        return self::GetUserSys()->id_emisor;
    }

    /**
     * Devuelve el código del cliente, relacionado con el usuario actual logueado.
     *
     * @return string
     */
    static function GetCodigoCliente() {
        return self::NormalizeValue(self::GetUserSys()->cod_cliente);
    }

    /**
     * Devuelve los ids de las empresas relacionado el usuario
     *
     * @return array Puede retornar * indica que tiene acceso a todas las empresas
     */
    public static function GetIdsEmpresasRels() {
        return self::GetUserSys()->idsOfcsRels;
    }

    /**
     * Devuelve el Nombre de la Empresa que pertenece el Usuario
     *
     * @return string
     */
    public static function GetNombreEmpresa() {
        return self::NormalizeValue(self::GetUserSys()->nom_empresa);
    }

    public static function GetCodigoEmpresa() {
        return self::NormalizeValue(self::GetUserSys()->cod_empresa);
    }
    
    public static function GetURILogoFrontalEmpresa() {
        return self::GetUserSys()->uri_logo_frontal;
    }

    public static function GetIdPersona() {
        return self::GetUserSys()->id_persona;
    }

    public static function GetAliasPersona() {
        return self::NormalizeValue(self::GetUserSys()->alias_persona);
    }

    public static function GetNumDocPersona() {
        return self::GetUserSys()->nro_doc_persona;
    }

    public static function SetAutoEncodeISO($value = true) {
        self::$_autoEncodeISO = $value;
    }

    protected static function NormalizeValue($value) {
        if (!empty($value) &&  self::$_autoEncodeISO && !is_numeric($value)) {
            Exj::TrasferCharsDecodeUTF8ToISO($value);
        }
        return $value;
    }

    public static function GetNombreCiudad() {
        return self::NormalizeValue(self::GetUserSys()->name_ciu_com);
    }

    public static function GetCodigoProvincia() {
        return self::GetUserSys()->codigo_prov;
    }
    
    public static function GetNombreProvincia() {
        return self::NormalizeValue(self::GetUserSys()->name_state);
    }
    

    public static function GetNacionalidad() {
        return self::NormalizeValue(self::GetUserSys()->nacionalidad_pais);
    }

    /**
     * Obtiene nombres y apellidos de la persona logueada
     *
     * @return string
     */
    public static function GetNomsApes() {
        $infoUsuario = self::GetUserSys();
        $value = $infoUsuario->nombres_persona;
        if (!$value) {
            $value = '';
        }

        if ($infoUsuario->apellidos_persona) {
            if ($value) {
                $value .= ' ';
            }
            $value .= $infoUsuario->apellidos_persona;
        }

        return self::NormalizeValue($value);
    }

    /**
     * Devuelve el ID del usuario del sistema: id_sys_user
     *
     * @return int
     */
    public static function GetIdSysUser() {
        return self::GetUserSys()->id_sys_user;
    }

    public static function SetOffsetTime($offset_time) {
        $infoUsr = self::GetUserSys();
        if (!$infoUsr) {
            return false;
        }

        $infoUsr->offset_time = $offset_time;
        return $infoUsr->saveOffsetTime($offset_time);
    }

    public static function GetIdAbogado($id_sys_user=0){
        $idAbogado = null;
        if (self::IsRolProveedor()) {
            $idAbogado = ExjSession::Get('idAbogadoFromUserAbogado', -1);
            if ($idAbogado < 0) {
                if (!$id_sys_user) {
                    $id_sys_user = self::GetIdSysUser();
                }
                $db     =& JFactory::getDBO();
                $query = 'SELECT id_abogado'
                    .' FROM app_abogados'
                    .' WHERE id_sys_user = ' . $id_sys_user;

                $db->setQuery($query);
                $idAbogado = $db->loadResult();
                $idAbogado = ($idAbogado ? intval($idAbogado): 0);
                ExjSession::Set('idAbogadoFromUserAbogado', $idAbogado);
            }
            
            if (!$idAbogado || $idAbogado=='0' || $idAbogado < 0) {
                $idAbogado = null;
            }
        }
        
        return $idAbogado;
    }
}

?>