<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * Helper Parámetros del Sistema RIDE, esta clase ya está cargada por la App
 * Autor: Byron Córdova
 */
class AppSysParametersHelper {
	const TYPE_STRING = 'string';
	const TYPE_INT = 'int';
	const TYPE_FLOAT = 'float';
	const TYPE_DATE = 'date';
	const TYPE_DATETIME = 'datetime';
	const TYPE_OBJECT = 'object';
	
	const CODE_MONTO_BCO_SANGRE_DEFECTO = 'MONTO_BCO_SANGRE_DEFECTO';
	const CODE_FTP_GEN_DOCN = 'FTP_GEN_DOCN';
	const CODE_GEN_CON_TPLDOC = 'GEN_CON_TPLDOC';
	const CODE_PATH_PROG_BKDB = 'PATH_PROG_BKDB';
	const CODE_MAX_EXEC_REP_SEGS = 'MAX_EXEC_REP_SEGS';
	
	public static function GetValue_MONTO_BCO_SANGRE_DEFECTO(){
		return self::GetValueParameter(self::CODE_MONTO_BCO_SANGRE_DEFECTO, 0);
	}
	
	
	public static function GetValue_FTP_GEN_DOCN($defaultValue=''){
		return self::GetValueParameter(self::CODE_FTP_GEN_DOCN, $defaultValue);
	}

	public static function GetValue_MAX_EXEC_REP_SEGS($defaultValue=0){
		return self::GetValueParameter(self::CODE_MAX_EXEC_REP_SEGS, $defaultValue);
	}
	
	public static function GetValue_GEN_CON_TPLDOC($defaultValue=0){
		return self::GetValueParameter(self::CODE_GEN_CON_TPLDOC, $defaultValue);
	}

	public static function GetValue_CODE_PATH_PROG_BKDB($defaultValue=''){
		return self::GetValueParameter(self::CODE_PATH_PROG_BKDB, $defaultValue);
	}

	
	
	static function GetValueParameter($codeParam, $defaultValue=null, $filtrarEmpresa=true){
		global $exj;
		$db = Exj::InstanceDatabase();
		
		$id_empresa = ExjUser::GetIdEmpresa();
		
		$value = $defaultValue;
		
		$where = array();
		$where[] = "p.code_param = '$codeParam'";
		if ($filtrarEmpresa) {
			$where[] = "p.id_empresa = $id_empresa";
		}
		
		$where = implode(' AND ', $where);
		
		$db->setQuery("SELECT p.value_param, p.type_param, id_empresa 
		 FROM jos_app_sys_parameters p 
		 WHERE $where 
		 ORDER BY p.modificado_dt DESC");
		
		$param = null;
		$items = $db->loadObjectList();
		if (!$db->isValid()) {
			return $value;
		}
		
		if (count($items) > 0) {
			foreach ($items as $item) {
				if ($item->id_empresa == $id_empresa) {
					$param = $item;
					break;
				}
			}
			
			if (!$param) {
				$param = $items[0];
			}
		}
		
		if (!$param) {
			Exj::SetErrorValidating("Parámetro <b>$codeParam</b> no se encuenta en el Empresa: " . ExjUser::GetNombreEmpresa());
			return $value;
		}
		
		$value = $param->value_param;
		$value = self::ParseValue($value, $param->type_param);
		
		return $value;
	}
	
	public static function ParseValue($valueRaw, $type_param){
		if ($valueRaw === null) {
			return $valueRaw;
		}
		
		$value = $valueRaw;
		switch ($type_param) {
			case self::TYPE_INT:
				$value = intval($value);
			break;
			
			case self::TYPE_FLOAT:
				$value = floatval($value);
			break;

			case self::TYPE_OBJECT:
				$value = Exj::JsonDecode($value);
			break;
		}
		
		return $value;
	}
}

?>