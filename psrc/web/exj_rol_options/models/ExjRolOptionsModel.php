<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class ExjRolOptionsModel
 * Modelo para Opciones del Rol del Usuario
 */
class ExjRolOptionsModel extends ExjModel {
    const RULER_READONLY = 'readOnly';
    const RULER_ADD = 'add';
    const RULER_SAVE = 'save';
    const RULER_EDIT = 'edit';
    const RULER_TRASH = 'trash';
    const RULER_VIEW = 'view';
    const RULER_CANCEL = 'cancel';

	/**
	 * Guarda o Elimina reglas para el rol de usuario
	 *
	 * @param ExjResponse $response
	 * @param int $gid
	 * @param array $news
	 * @param array $removes
	 * @return bool true si fué satisfactorio
	 */
    public static function UpdateOptionsRol(ExjResponse &$response, $gid, $news, $removes) 
    {
    	
    	if ($news && !is_array($news)) {
    		$response->setMsgError("Items nuevos deben ser tipo array.");
    		return ;
    	}
    	if ($removes && !is_array($removes)) {
    		$response->setMsgError("Items removes deben ser tipo array.");
    		return ;
    	}
    	
    	$gid = intval($gid);
    	if (!$gid || is_nan($gid)) {
    		$response->setMsgError("Parámetro grupo id, es un valor inválido.");
    		return ;
    	}
    	    	
    	$aro_value = AppRolsData::GetValueGroupACL_ARO($gid);
    	if ($aro_value === false) {
    		$response->setMsgError(
                "Ocurrió un error al obtener el valor del grupo acl aro."
            );
    		return ;
    	}
    	
    	if (!$aro_value) {
    		$response->setMsgError("El rol no existe.");
    		return ;
    	}
    	
    	
    	$nroRulesDeleteds = 0;
    	if (!empty($removes)) {
    		$idsAXOsSections = self::_GetIdsAXOSections($removes);
    		
    		if (count($idsAXOsSections) > 0) {
    			$msgError = '';
    			$nroRulesDeleteds = AppRolOptionsData::DeleteACL_Rules($aro_value, $idsAXOsSections, $msgError);
    			if ($msgError) {
    				$response->setMsgError($msgError);
    				return false;
    			}
    			if ($nroRulesDeleteds === false) {
    				$response->setMsgError("No se pudo eliminar, motivo desconocido!");
    				return false;
    			}
    		}
    	}
    	
    	if (!empty($news)) {
    		foreach ($news as $itemNew) {
    			$axo_section = $itemNew->axo_section;
    			
    			$rules = self::GetAXO_ValuesRulesDef($itemNew->name_comp, $gid);
    			$msgError = '';
    			AppRolOptionsData::CreateACL_Rules(
                    $aro_value, $axo_section, $rules, $msgError
                );

    			if ($msgError) {
    				$response->setMsgError($msgError);
    				return false;
    			}
    			
    		}
    	}
    	
    	/*
		$response->setMsgError("Test save. aro_value: $aro_value ");
		return false;
		*/
		
		
		return true;
    }
    
    public static function GetAXO_ValuesRulesDef($name_comp, $gid)
    {
    	$rules = array();
    	$isSuperAdmin = ($gid == Exj::GetValueCfg('ugidSuperAdmin'));
    	$isAdmin = ($gid == Exj::GetValueCfg('ugidAdministrador'));
    	$isContador = ($gid == Exj::GetValueCfg('ugidContabilidad'));
    	$isCliente = ($gid == Exj::GetValueCfg('ugidCliente'));
    	$isRecaudador = ($gid == Exj::GetValueCfg('ugidRecaudador'));
    	$isAdminOContador = ($isAdmin || $isContador);
    	
    	switch ($name_comp) {
    		case 'Public':
    		case 'Registered':
    		case 'Special':
    		case 'exj_app_help':
    		case 'exj_acercade_app':
    			$rules[] = self::RULER_READONLY;
    		break;
    		    		
    		case 'exj_sys_parameters':
    			if ($isAdminOContador || $isSuperAdmin) {
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    			}
    		break;
    		
    		
    		/* trash,cancel */
    		case 'exj_logs':
    			if ($isSuperAdmin) {
    				$rules[] = self::RULER_TRASH;
    				$rules[] = self::RULER_CANCEL;
    			}
    			
    		break;
    		
    		/* add,save,edit,trash */
    		case 'exj_sys_upgrades':
    			if ($isSuperAdmin) {
    				$rules[] = self::RULER_ADD;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_TRASH;
    			}
    			
    		break;
    		
    		/* edit,readOnly */
    		case 'exj_deploys':
    			$rules[] = self::RULER_READONLY;
    			
    			if ($isSuperAdmin) {
    				$rules[] = self::RULER_EDIT;
    			}
    			
    		break;
    		
    		/* Correos  */
    		case 'exj_admin_mails':
    			if ($isSuperAdmin) {
    				$rules[] = self::RULER_TRASH;
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_ADD;
    			}
    			else {
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_ADD;
    			}
    			
    		break;
    		
    		/* HelpDesk */
    		case 'exj_hld_incidents':
    			if ($isSuperAdmin) {
    				$rules[] = self::RULER_TRASH;
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_ADD;
    			}
    			else {
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_ADD;
    			}
    			
    		break;
    		
    		/* cancel,save */
    		case 'exj_rol_options':
    			if ($isSuperAdmin || $isAdminOContador) {
    				$rules[] = self::RULER_CANCEL;
    				$rules[] = self::RULER_SAVE;
    			}
    			else {
    				$rules[] = self::RULER_READONLY;
    			}
    			
    		break;
    		    		    		
    		/* trash,edit,save,add  */
            case 'exj_rols':
            case 'exj_rol_users':
            case 'exj_sys_users':
            case 'exj_helpdesks':
            case 'exj_files':
    		
    			if ($isAdminOContador || $isSuperAdmin) {
    				$rules[] = self::RULER_TRASH;
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_ADD;
    			}
    			elseif ($isCliente){
    				 $rules[] = self::RULER_READONLY;
    			}
    			else {
    				// edit,save,add
    				$rules[] = self::RULER_EDIT;
    				$rules[] = self::RULER_SAVE;
    				$rules[] = self::RULER_ADD;
    			}
    		break;
    	}

        if (empty($rules)) {
            if (class_exists('AppRolOptionsPlugin')) {
                $rulesExtras = AppRolOptionsPlugin::GetRulesDefault($name_comp, $gid);
                if (!empty($rulesExtras)) {
                    $rules = $rulesExtras;
                }
            }

            // reportes: edit,save
            if (empty($rules) && strpos($name_comp, 'app_rep_') === 0)
            {
                if ($isAdminOContador || $isSuperAdmin || $isRecaudador) {
                    $rules[] = self::RULER_EDIT;
                    $rules[] = self::RULER_SAVE;
                }
                else {
                    $rules[] = self::RULER_READONLY;
                    $rules[] = self::RULER_VIEW;
                }
            }
        }
    	
    	if (empty($rules)) {
    		$rules[] = self::RULER_READONLY;
    	}
    	
    	return $rules;
    }
    
    private static function _GetIdsAXOSections($items){
    	$idsAXOsSections = array();
		foreach ($items as $item) {
			if (!$item->axo_section) {
				continue;
			}
			$idsAXOsSections[] = $item->axo_section;
		}
		
		return $idsAXOsSections;
    }

    static function LoadDataRolOptions(ExjResponse &$response, &$items, $paramsCriteria=null) {
    		
    	return AppRolOptionsData::LoadDataRolOptions($response, $items, $paramsCriteria);
    }   
}

?>