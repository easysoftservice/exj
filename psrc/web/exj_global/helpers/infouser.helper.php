<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Información General del usuario logueado
 *
 */
class ExjHelperInfoUser extends ExjClassSession {

    /**
     * Id de la persona logueada
     *
     * @var int
     */
    public $id_persona = 0;

    /**
     * Primer Nombre de la persona
     *
     * @var string
     */
    public $nombres_persona;

    /**
     * Apellidos de la persona
     *
     * @var string
     */
    public $apellidos_persona;
//	public $noms_apes_per;

    public $alias_persona;

    public $nro_doc_persona;

    /**
     * Código de la Provincia
     *
     * @var string
     */
    public $codigo_prov;
    
    /**
     * Nombre de la Provincia
     *
     * @var unknown_type
     */
    public $name_state;

    /**
     * Nacionalidad
     *
     * @var string
     */
    public $nacionalidad_pais;


    /* Localización */

    /**
     * Id de la empresa q pertenece el usuario
     *
     * @var int
     */
    public $id_empresa = 0;

    /**
     * Id de la provincia que pertenece el usuario
     *
     * @var int
     */
    public $id_sit = 0;

    /**
     * Id del país de la Empresa que pertenece el usuario
     *
     * @var int
     */
    public $id_pais = 0;

    /**
     * Id de la Empresa que pertenece al usuario
     *
     * @var int
     */
    public $id_company = 0;

    /**
     * Nombre de la Empresa a la que pertenece
     *
     * @var string
     */
    public $name_company = '';

    /**
     * Nombre de la Ciudad de la empresa
     *
     * @var string
     */
    public $name_ciu_com;
    
    
    

    /**
     * Nombre de la empresa
     *
     * @var string
     */
    public $nom_empresa = '';

    /**
     * Indica si es la empresa principal o no
     *
     * @var string
     */
    public $is_main_empresa = 0;

    /* Datos de Usuario */

    /**
     * Indica si el usuario está bloqueado o no
     *
     * @var int (bool)
     */
    public $user_block = 1;

    /**
     * Tema de la aplicación que se presentará para el usuario. Ejemplo: PROFESIONAL
     *
     * @var string
     */
    public $sys_type_theme = '';

    /* Datos de Lenguaje */

    /**
     * Id del lenguaje del usuario
     *
     * @var int
     */
    public $id_sys_lang = 0;

    /**
     * Nombre del lenguaje
     *
     * @var string
     */
    public $name_lang = '';

    /**
     * Acronimo del lenguaje del usuario
     *
     * @var unknown_type
     */
    public $acronym_lang = '';

    /**
     * Nombre del País
     *
     * @var string
     */
    public $nom_pais = '';

    /**
     * Compesación de Tiempo
     *
     * @var int
     */
    public $offset_time = 0;

    /**
     * Indica si está activo el modo debug
     *
     * @var int
     */
    public $enable_debug = 0;

    /**
     * Correo de la persona, sino tiene este, lo toma del usuario
     *
     * @var string
     */
    public $email = '';

    /**
     * Id del usuario del sistema
     *
     * @var int
     */
    public $id_sys_user = 0;

    /**
     * Id del Cliente, si el usuario no esta asginado como cliente devuelve 0
     *
     * @var int
     */
    public $id_cliente = 0;

    /**
     * Código del Cliente
     *
     * @var string
     */
    public $cod_cliente = '';

    /**
     * Ids de las empresas que está relacionado el usuario, si es null no hay acceso si tiene * acceso a todas las empresas
     *
     * @var array
     */
    public $idsOfcsRels = null;

    /**
     * Código de la Empresa
     *
     * @var string
     */
    public $cod_empresa = '';

    /**
     * URI de Logo Frontal
     * @var string
     */
    public $uri_logo_frontal = null;

    public $id_emisor = null;

    public function saveOffsetTime($value) {
        $this->offset_time = $value;
        return $this->saveValueToSession('offset_time', $value);
    }

    public function fixDataOffice($id_empresa, $nom_empresa) {
        $this->id_empresa = $id_empresa;
        $this->nom_empresa = $nom_empresa;
        return $this;
    }
}

?>