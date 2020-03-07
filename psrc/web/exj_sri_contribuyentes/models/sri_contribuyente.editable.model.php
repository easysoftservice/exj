<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * Modelo Editable para Contribuyente SRI
 *
 */
class AppSriContribuyenteEditableModel extends ExjEditableModel {
	/* estado_contribuyente Estado Contribuyente */
	const ESTADO_CONTRIBUYENTE_ACTIVO='ACTIVO';
	const ESTADO_CONTRIBUYENTE_PASIVO='PASIVO';
	const ESTADO_CONTRIBUYENTE_SUSPENDIDO='SUSPENDIDO';
	
	/* clase_contribuyente Clase Contribuyente */
	const CLASE_CONTRIBUYENTE_RISE='RISE';
	const CLASE_CONTRIBUYENTE_ESPECIAL='ESPECIAL';
	const CLASE_CONTRIBUYENTE_OTROS='OTROS';
	
	/* tipo_contribuyente Tipo Contribuyente */
	const TIPO_CONTRIBUYENTE_PERSONASNATURALES='PERSONAS NATURALES';
	const TIPO_CONTRIBUYENTE_SOCIEDADES='SOCIEDADES';
	
	/* estado_establecimiento Estado Establecimiento */
	const ESTADO_ESTABLECIMIENTO_ABI='ABI';
	const ESTADO_ESTABLECIMIENTO_CER='CER';
	
	/**
	 * Id Contribuyente
	 *
	 * @var int
	 */
	public $id_contribuyente;
	
	/**
	 * Identificador de la Actividad Economica
	 *
	 * @var int
	 */
	public $id_act_eco;
	
	/**
	 * Número Ruc
	 *
	 * @var string
	 */
	public $numero_ruc;
	
	/**
	 * Razon Social
	 *
	 * @var string
	 */
	public $razon_social;
	
	/**
	 * Nombre Comercial
	 *
	 * @var string
	 */
	public $nombre_comercial;
	
	/**
	 * Estado Contribuyente
	 *
	 * @var string enum('ACTIVO','PASIVO','SUSPENDIDO')
	 */
	public $estado_contribuyente;
	
	/**
	 * Clase Contribuyente
	 *
	 * @var string enum('RISE','ESPECIAL','OTROS')
	 */
	public $clase_contribuyente;
	
	/**
	 * Fecha Inicio Actividades
	 *
	 * @var date
	 */
	public $fecha_inicio_actividades;
	
	/**
	 * Fecha Actualización
	 *
	 * @var date
	 */
	public $fecha_actualizacion;
	
	/**
	 * Fecha Suspension Definitiva
	 *
	 * @var date
	 */
	public $fecha_suspension_definitiva;
	
	/**
	 * Fecha Reinicio Actividades
	 *
	 * @var date
	 */
	public $fecha_reinicio_actividades;
	
	/**
	 * Obligado
	 *
	 * @var int
	 */
	public $obligado;
	
	/**
	 * Tipo Contribuyente
	 *
	 * @var string enum('PERSONAS NATURALES','SOCIEDADES')
	 */
	public $tipo_contribuyente;
	
	/**
	 * Numero Establecimiento
	 *
	 * @var int
	 */
	public $numero_establecimiento;
	
	/**
	 * Nombre Fantasia Comercial
	 *
	 * @var string
	 */
	public $nombre_fantasia_comercial;
	
	/**
	 * Calle
	 *
	 * @var string
	 */
	public $calle;
	
	/**
	 * Numero
	 *
	 * @var string
	 */
	public $numero;
	
	/**
	 * Intersección
	 *
	 * @var string
	 */
	public $interseccion;
	
	/**
	 * Estado Establecimiento
	 *
	 * @var string enum('ABI','CER')
	 */
	public $estado_establecimiento;
	
	/**
	 * Descripción Provincia
	 *
	 * @var string
	 */
	public $descripcion_provincia;
	
	/**
	 * Descripción Cantón
	 *
	 * @var string
	 */
	public $descripcion_canton;
	
	/**
	 * Descripción Parroquia
	 *
	 * @var string
	 */
	public $descripcion_parroquia;
	
	/**
	 * overwrited. Lectura de la tabla a editar
	 *
	 * @param string $nameTable Nombre de la tabla
	 * @param string $fieldKey  Nombre del campo principal de la tabla
	 */
	public function readTable(&$nameTable, &$fieldKey){
		$nameTable = 'repositorio.sri_contribuyentes';
		$fieldKey = 'id_contribuyente';
	}
	
	/**
	 * overwrited. Registro de Campos
	 *
	 */
	public function registerFields(){
		$this->registerFieldId('id_act_eco', 'Id Actividad Económica');
		$this->registerFieldString('numero_ruc', 'Número Ruc');
		$this->registerFieldString('razon_social', 'Razon Social');
		$this->registerFieldStringNullable('nombre_comercial', 'Nombre Comercial');
		$this->registerFieldString('estado_contribuyente', 'Estado Contribuyente');
		$this->registerFieldString('clase_contribuyente', 'Clase Contribuyente');
		$this->registerFieldDateNullable('fecha_inicio_actividades', 'Inicio Actividades');
		$this->registerFieldDateNullable('fecha_actualizacion', 'Actualización');
		$this->registerFieldDateNullable('fecha_suspension_definitiva', 'Suspensión Definitiva');
		$this->registerFieldDateNullable('fecha_reinicio_actividades', 'Reinicio Actividades');
		$this->registerFieldInt('obligado', 'Obligado');
		$this->registerFieldString('tipo_contribuyente', 'Tipo Contribuyente');
		$this->registerFieldInt('numero_establecimiento', 'Nro Establecimiento');
		$this->registerFieldStringNullable('nombre_fantasia_comercial', 'Nombre Fantasia Comercial');
		$this->registerFieldStringNullable('calle', 'Calle');
		$this->registerFieldStringNullable('numero', 'Número');
		$this->registerFieldStringNullable('interseccion', 'Intersección');
		$this->registerFieldString('estado_establecimiento', 'Estado Establecimiento');
		$this->registerFieldString('descripcion_provincia', 'Provincia');
		$this->registerFieldString('descripcion_canton', 'Cantón');
		$this->registerFieldString('descripcion_parroquia', 'Parroquia');
	}
	
	/**
	 * overwrited. Registro de controles UI
	 *
	 */
	public function registerControlsUI(){
		$this->registerControlUI(ExjUI::NewTextField('numero_ruc'));
		$this->registerControlUI(ExjUI::NewTextField('razon_social'));
		$this->registerControlUI(ExjUI::NewTextField('nombre_comercial'));
		/* estado_contribuyente enum('ACTIVO','PASIVO','SUSPENDIDO') */
		/* clase_contribuyente enum('RISE','ESPECIAL','OTROS') */
		$this->registerControlUI(ExjUI::NewDateField('fecha_inicio_actividades'));
		$this->registerControlUI(ExjUI::NewDateField('fecha_actualizacion'));
		$this->registerControlUI(ExjUI::NewDateField('fecha_suspension_definitiva'));
		$this->registerControlUI(ExjUI::NewDateField('fecha_reinicio_actividades'));
		$this->registerControlUI(ExjUI::NewCheckbox('obligado'));
		/* tipo_contribuyente enum('PERSONAS NATURALES','SOCIEDADES') */
		$this->registerControlUI(ExjUI::NewNumberField('numero_establecimiento'));
		$this->registerControlUI(ExjUI::NewTextField('nombre_fantasia_comercial'));
		$this->registerControlUI(ExjUI::NewTextField('calle'));
		$this->registerControlUI(ExjUI::NewTextField('numero'));
		$this->registerControlUI(ExjUI::NewTextField('interseccion'));
		/* estado_establecimiento enum('ABI','CER') */
		$this->registerControlUI(ExjUI::NewTextField('descripcion_provincia'));
		$this->registerControlUI(ExjUI::NewTextField('descripcion_canton'));
		$this->registerControlUI(ExjUI::NewTextField('descripcion_parroquia'));
	}
	
	/**
	 * overwrited. Registro de Reglas
	 *
	 */
	public function registerRules(){
		$this->applyValidationTextNameExtendido('numero_ruc', true, 13, 10);
		$this->applyValidationTextNameExtendido('razon_social', true, 90);
		$this->applyValidationTextNameExtendido('nombre_comercial', false, 120);
		$this->applyValidationTextNameExtendido('nombre_fantasia_comercial', false, 120);
		$this->applyValidationTextNameExtendido('calle', false, 45, 0);
		$this->applyValidationTextNameExtendido('numero', false, 20, 0);
		$this->applyValidationTextNameExtendido('interseccion', false, 45);
		$this->applyValidationTextNameExtendido('descripcion_provincia', false, 60);
		$this->applyValidationTextNameExtendido('descripcion_canton', false, 60);
		$this->applyValidationTextNameExtendido('descripcion_parroquia', false, 60);
	}	

	/**
	 * overwrited. Antes de Eliminar
	 *
	 * @param int $id
	 */
    public function beforeDestroy($id) {
    	if (!parent::beforeDestroy($id)) {
    		return false;
    	}
    	
    	
		
    	return true;
    }
    
    
    /**
     * overwrited. Antes Guardar
     *
     * @return bool
     */
    protected function beforeSave(){

    	$this->addBrokenRuler("No se permite crear o modificar datos del Contribuyente.");
        return false;
    	
    	// return true;
    }
	
}

?>