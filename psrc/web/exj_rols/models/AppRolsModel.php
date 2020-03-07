<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppRolsModel
 * Modelo para Roles del Usuario
 */
class AppRolsModel extends ExjModel {
	const ID_PublicFrontend = 29;
	
    static function SaveRol($id, $dataChanged, $is_internal_rol=null, $id_group_acl_aro=0) {
    	global $exj;
    	
		// $exj->includeModelEditable('rol');
		$rol = new AppRolEditableModel(false);
		if ($rol->bind($dataChanged)) {
			$rol->setValueId($id);
			if ($is_internal_rol !== null) {
				$rol->is_internal_rol = $is_internal_rol;
			}
			
			if (!$rol->isSettedField('id_group_acl_aro') && $id_group_acl_aro) {
				$rol->id_group_acl_aro = $id_group_acl_aro;
			}
			
			$rol->save();
		}
		
		return $rol->validateResponse();
    }

    static function LoadListRoles(&$items, &$total, $paramsCriteria=null) {
    	self::IncludeClassData();
    	
    	return AppRolsData::LoadListRoles($items, $total, $paramsCriteria);
    }
    
    static function IncludeClassData(){
    	if (!class_exists('AppRolsData')) {
    		global $exj;
    		// $exj->includeDataCustom('rols', 'exj_rols');
    	}
    }
    
    /**
     * Valida si el código o nombre son únicos
     *
     * @param int $id_rol
     * @param string $codeRol
     * @param string $nameRol
     * @param string $msgInvalidate
     * @return bool true si son unicos y false si ya existe algún rol ya regsitrado
     */
    static function ValidateCodeName($id_rol, $codeRol, $nameRol, &$msgInvalidate){
    	self::IncludeClassData();
    	
    	$msgError = $msgInvalidate = '';
    	$itemsRoles = AppRolsData::LoadRolesCodeName($id_rol, $codeRol, $nameRol, $msgError);
    	if ($msgError) {
    		$msgInvalidate = $msgError;
    		return false;
    	}
    	
    	if (!$itemsRoles || count($itemsRoles) == 0) {
    		return true;
    	}
    	
    	$itemRolInvalid = $itemsRoles[0];
    	
    	$msgInvalidate = array();
    	if ($codeRol) {
    		$msgInvalidate[] = "Cóigo: $codeRol ya existe.";
    	}
    	
    	if ($nameRol) {
    		$msgInvalidate[] = "Nombre: $nameRol ya existe.";
    	}
    	
    	$itemRolInvalid->modificado_dt = ExjDate::ConvertToDateTimeDisplay($itemRolInvalid->modificado_dt);
    	
    	$msgInvalidate[] = "Ha sido modificado por: $itemRolInvalid->username el: $itemRolInvalid->modificado_dt";
    	
    	$msgInvalidate = implode('<br/>', $msgInvalidate);
    	
    	/*
    	$itemRolInvalid->code_rol
    	$itemRolInvalid->name_rol
    	*/
    	
    	return false;
    }
    
    static function ValidateGroupToCreate(&$id_group_acl_aro, $codeRol, $nameRol, &$msgInvalid){
    	self::IncludeClassData();
    	$msgInvalid = '';
    	
    	$itemsGroups = AppRolsData::GetGroupsNotRelated($codeRol, $nameRol);
    	if ($itemsGroups === false) {
    		$msgInvalid = 'Error al Recuperar datos no relacionados.';
    		return false;
    	}
    	
    	if (!$itemsGroups || count($itemsGroups) == 0) {
    		return true;
    	}
    	
    	$itemFound = null;
    	foreach ($itemsGroups as $itemGroup) {
    		if ($itemGroup->parent_id == self::ID_PublicFrontend) {
    			$itemFound = $itemGroup;
    			break;
    		}
    	}
    	
    	if (!$itemFound) {
    		$msgInvalid = "El Rol: $nameRol es revervado y no se puede asignar este rol.";
    		return false;
    	}
    	
    	$id_group_acl_aro = $itemFound->id;
    	
    	return true;
    }
    
    static function CreateGroupACL_ARO($codeRol, $nameRol, &$id_group_acl_aro, &$msgInvalid){
    	self::IncludeClassData();
    	
    	$msgInvalid = '';
    	$rgt = 0;
    	
    	if (!AppRolsData::LoadRgtFromGroups(self::ID_PublicFrontend , $rgt)) {
    		$msgInvalid = "No se pudo cargar la secuencia del último grupo";
    		return false;
    	}
    	
    	if (!$rgt) {
    		$msgInvalid = "No existe el grupo: Public Frontend.";
    		// este caso no pasaria, solo pasa si se elimina el group public front end
    		return false;
    	}
    	
    	$valueLFT = $rgt;
    	$valueRGT = $valueLFT + 1;
    	
    	if (!AppRolsData::InsertGroupACL_ARO(self::ID_PublicFrontend, $nameRol, $valueLFT, $valueRGT, $codeRol, $id_group_acl_aro, $msgInvalid)) {
    		if (!$msgInvalid) {
    			$msgInvalid = "No se pudo insertar group acl aro!";
    		}
    		return false;
    	}
    	
    	return true;
    }
    
    static function UpdateGroupACL_ARO($id_group_acl_aro, $codeRol, $nameRol, &$msgInvalid){
    	self::IncludeClassData();
    	
    	$msgInvalid = '';
    	if (!$codeRol && !$nameRol) {
    		return true;
    	}
    	if ($id_group_acl_aro <= 0) {
    		$msgInvalid = "No se pudo actualizar group acl aro!<br/>Ref: No se indicó ID del grupo acl aro.";
    		return false;
    	}
    	
    	if (!AppRolsData::UpdateGroupACL_ARO($id_group_acl_aro, $codeRol, $nameRol, $msgInvalid)) {
    		if (!$msgInvalid) {
    			$msgInvalid = "No se pudo actualizar group acl aro! ID: $id_group_acl_aro";
    		}
    		return false;
    	}
    	
    	return true;
    }
}

?>