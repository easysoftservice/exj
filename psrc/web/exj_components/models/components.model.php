<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppComponentsModel
 * Modelo para Componente
 */
class AppComponentsModel extends ExjModel {
	
    public static function CargarListaPrincipal(ExjResponse $response, &$items, &$total, $paramsCriteria=null) {
    	
    	return AppComponentsData::CargarListaPrincipal($response, $items, $total, $paramsCriteria);
    }
    
    public static function LoadLookupTableCols(&$items, $table_name){
    	return AppComponentsData::LoadLookupTableCols($items, $table_name);
    }
    
    public static function LoadListTableCols(&$items, $table_name){
    	return AppComponentsData::LoadListTableCols($items, $table_name);
    }
    
    private static function _ValidateDirComponent($pathDir, &$msgError){
    	$msgError = '';
    	
    	if (file_exists($pathDir)) {
    		return true;
    	}
    	
		if (!ExjFile::MkDirRecursive($pathDir)) {
			$msgError = "No se creó el directorio:<br>$pathDir";
			return false;
		}
		
		// copiar archio index.html
		$pathFileIndexHTMLOrig = Exj::GetPathAppWeb() . "/exj_component_tpls/index.html";;
		$pathFileIndexHTMLDest = $pathDir . "/index.html";;
		if (!@copy($pathFileIndexHTMLOrig, $pathFileIndexHTMLDest)) {
			$msgError = "No se copió archivo index.html";
			return false;
		}
		
		return true;
    }
    
    public static function GenerateFilesOfComponent($tpl_files, $nombreTabla, $nombreComponente, $etiquetaCmpPlural, $etiquetaCmpSingular, $itemsColsTable){
    	if (!is_array($tpl_files)) {
    		$tpl_files = explode(',', $tpl_files);
    	}
    	
    	$resultGeneted = new stdClass();
    	$resultGeneted->msgError = '';
    	$resultGeneted->filesExists = array();
    	$resultGeneted->filesGenerateds = array();
    	
    	foreach ($tpl_files as $tpl_file) {
			$fileGenerated = self::GetFileGenerated($nombreTabla, $tpl_file, $nombreComponente, $etiquetaCmpPlural, $etiquetaCmpSingular, $itemsColsTable);
			if ($fileGenerated->msgError) {
				$resultGeneted->msgError = $fileGenerated->msgError;
				break;
			}
			
			$pathFileCompGenerated = $fileGenerated->pathFileComp;
			
			$pathFullFile = Exj::GetPathAppWeb() . "/$pathFileCompGenerated";
			if (file_exists($pathFullFile)) {
				$resultGeneted->filesExists[] = $pathFileCompGenerated;
				continue;
			}
			else {
				/* Verificar si está creado el dir del componente */
				$dirCmp = Exj::GetPathAppWeb() . "/$nombreComponente";
				if (!self::_ValidateDirComponent($dirCmp, $resultGeneted->msgError)) {
					break;
				}
			}
			
			$pathDir = dirname($pathFullFile);
			if (self::_ValidateDirComponent($pathDir, $resultGeneted->msgError)) {
				file_put_contents($pathFullFile, $fileGenerated->content);
				$resultGeneted->filesGenerateds[] = $pathFileCompGenerated;
			}
			else {
				break;
			}
    	}
    	
    	return $resultGeneted;
    }
    
    public static function GenerarComponent(ExjResponse $response, $paramDataChanged){
    	$component = new AppComponentEditableModel(false, $response);
		
    	if (!$component->bind($paramDataChanged)) {
    		return $response->setMsgError("No se pudo bindear campos del componente.");
    	}
    	$component->setValueId(0);
    	
		if ($component->haveBrokenRules()) {
			return $response->setMsgError($component->getBrokenRules());
		}
		
		$nombreComponente = $component->nombre_com;
		if (!$component->IsSettedValue($nombreComponente) || !$nombreComponente) {
			return $response->setMsgError("No se indicó nombre del componente.");
		}
		$nameTable = $component->nombre_tabla_com;
		if (!$component->IsSettedValue($nameTable) || !$nameTable) {
			return $response->setMsgError("No se indicó nombre de tabla.");
		}
		
		$dirComp = Exj::GetPathAppWeb() . "/$nombreComponente";
	//	echo "<br>dirComp: $dirComp<br>";
		$tpl_file = $component->getValueField('tpl_file');
		if ($tpl_file) {
			$tpl_file = trim($tpl_file);
		}

		/*
		if (!$tpl_file && file_exists($dirComp)) {
			return $response->setMsgError("Ya está creado el componente o directorio: $nombreComponente");
		}
		*/
		
		if (!self::InstallComponent($response, $nombreComponente, $component->plural_com, $id_group_joomla, $id_cat)) {
			return $response;
		}
		$component->setParam('id_group_joomla', $id_group_joomla);
		$component->id_group_joomla = $id_group_joomla;
		$component->save();
		if ($component->haveBrokenRules()) {
			return $response->setMsgError($component->getBrokenRules());
		}
		
		$itemsColsTable = self::GetColsOfTable($nameTable);
		
		if (!$tpl_file) {
			/* adicionar todos para generar */
			
			$tpl_file = array();
			$tpl_file[] = AppComponentsUIHelper::FILE_CONTROLLER;
			$tpl_file[] = AppComponentsUIHelper::FILE_DATA;
			$tpl_file[] = AppComponentsUIHelper::FILE_HELPER_UI;
			$tpl_file[] = AppComponentsUIHelper::FILE_MODEL;
			$tpl_file[] = AppComponentsUIHelper::FILE_CRITERIA_MODEL;
			$tpl_file[] = AppComponentsUIHelper::FILE_EDITABLE_MODEL;
			$tpl_file[] = AppComponentsUIHelper::FILE_REPORT_MODEL;
			$tpl_file[] = AppComponentsUIHelper::FILE_LIST_MODEL;
			$tpl_file[] = AppComponentsUIHelper::FILE_MAIN_JS;
		}
		
		$resultGeneted = self::GenerateFilesOfComponent($tpl_file, $nameTable, $nombreComponente, $component->plural_com, $component->singular_com, $itemsColsTable);
		if ($resultGeneted->msgError) {
			return $response->setMsgError($resultGeneted->msgError);
		}
		
		$filesExists = implode('<br>', $resultGeneted->filesExists);
		$filesGenerateds = implode('<br>', $resultGeneted->filesGenerateds);
		
		$msgInfo = '<h3>Resultados de la Generación</h3><br>';
		if ($filesExists) {
			$msgInfo .= "No se generó ya existe:<br>$filesExists";
		}
		if ($filesGenerateds) {
			$msgInfo .= "Generados:<br>$filesGenerateds";
		}
		
		$response->setMsgInfo($msgInfo);

		// print_r($paramDataChanged);
//		print_r($component->toObjectOnlySetted());
		
		return $response;
    }
    
    public static function InstallComponent(ExjResponse $response, $nameComponent, $labelComponent, &$id_group_joomla, &$id_cat){
    	$nameComponent = trim($nameComponent);
    	$labelComponent = trim($labelComponent);
    	if (!$nameComponent) {
    		$response->setMsgError("No se indicó nombre del componente!");
    		return false;
    	}
    	if (!$labelComponent) {
    		$response->setMsgError("No se indicó etiqueta del componente:<br>$nameComponent");
    		return false;
    	}

		$id_group_joomla = ExjAccess::GetIdFromGroups($nameComponent);
		if ($id_group_joomla === false) {
			$response->setMsgError("Error al obtener id de groups");
			return false;
		}
		
		$db = Exj::InstanceDatabase();
		
		if (!$id_group_joomla) {
			$id_group_joomla = ExjData::GetNextValueTable('jos_groups', 'id');
			if ($id_group_joomla === false){
				$response->setMsgError("Error obteniendo next id de groups");
				return false;
			}
			
			$queryInsert = "INSERT INTO jos_groups(id, name)";
			$queryInsert .= " VALUES($id_group_joomla, '$nameComponent')";
			$db->query($queryInsert);
			if (!$db->isValid()) {
				$response->setMsgError("Error insertando group Componente: $nameComponent");
				return false;
			}
		}
		
		/* Categorias */
		$id_cat = ExjAccess::GetIdFromCategories($id_group_joomla);
		if ($id_cat === false) {
			$response->setMsgError("Error al obtener id de categories");
			return false;
		}
    	if (!$id_cat) {
			$queryInsert = "INSERT INTO jos_k2_categories(access, name)";
			$queryInsert .= " VALUES($id_group_joomla, '$labelComponent')";
			$db->query($queryInsert);
			if (!$db->isValid()) {
				$response->setMsgError("Error insertando group Componente: $nameComponent");
				return false;
			}
			$id_cat = $db->insertid();
    	}
		
    	
    	return true;
    }
    
    public static function GetColsOfTable($nameTable, $itemsModifiedCols=null){
    	if (!AppComponentsData::LoadListTableCols($items, $nameTable)) {
    		return false;
    	}
    	
    	if ($itemsModifiedCols) {
	    	foreach ($items as &$item) {
	    		$nameCol = $item->nameCol;
	    		foreach ($itemsModifiedCols as $itemModifiedCol) {
	    			if ($nameCol == $itemModifiedCol->nameCol) {
	    				$item->labelCol = $itemModifiedCol->labelCol;
	    			}
	    		}
	    	}
    	}
    	
    	return $items;
    }
    
    public static function GetFileGeneratedHTML($nameTable, $nameFileTpl, $nameComp, $plural_com, $singular_com, $colsTable){
    	$fileGenerated = self::GetFileGenerated($nameTable, $nameFileTpl, $nameComp, $plural_com, $singular_com, $colsTable);
    	if ($fileGenerated->msgError) {
    		return $fileGenerated;
    	}
    	
    	$fileGenerated->content = htmlentities($fileGenerated->content);
    	
    	$fileGenerated->content = str_replace("\n", '<br>', $fileGenerated->content);
    	$fileGenerated->content = str_replace("\t", '&nbsp;&nbsp;&nbsp;', $fileGenerated->content);
    	$fileGenerated->content = str_replace(" ", '&nbsp;', $fileGenerated->content);
    	
    	$fileGenerated->content = str_replace('class&nbsp;', '<span style="color:blue;">class&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('extends&nbsp;', '<span style="color:blue;">extends&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('public&nbsp;', '<span style="color:blue;">public&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('protected&nbsp;', '<span style="color:blue;">protected&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('function', '<span style="color:blue;">function</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('static&nbsp;', '<span style="color:blue;">static&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('const&nbsp;', '<span style="color:blue;">const&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('new&nbsp;', '<span style="color:blue;">new&nbsp;</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('self::', '<span style="color:blue;">self::</span>', $fileGenerated->content);
    	$fileGenerated->content = str_replace('Ext.', '<span style="color:brown;">Ext.</span>', $fileGenerated->content);
    	
    	$fileGenerated->content = str_replace('/*', '<span style="color:gray;">/*', $fileGenerated->content);
    	$fileGenerated->content = str_replace('*/', '*/</span>', $fileGenerated->content);

    	return $fileGenerated;
    }
    
    
    public static function GetFileGenerated($nameTable, $nameFileTpl, $nameComp, $plural_com, $singular_com, $itemsColsTable)
    {
    	$fileGenerated = new stdClass();
    	$fileGenerated->msgError = '';
    	$fileGenerated->pathFileComp = '';
    	$fileGenerated->content = '';
    	
    	
    	$pathRelativeFile = '';
    	switch ($nameFileTpl) {
    		case AppComponentsUIHelper::FILE_CONTROLLER:
    			$pathRelativeFile = "controllers/component_tpls.controller.php";
    		break;
    		
    		case AppComponentsUIHelper::FILE_DATA:
    			$pathRelativeFile = "data/component_tpls.data.php";
    		break;
    		
    		case AppComponentsUIHelper::FILE_HELPER_UI:
    			$pathRelativeFile = "helpers/component_tpls_ui.helper.php";
    		break;
    		
    		case AppComponentsUIHelper::FILE_MODEL:
    			$pathRelativeFile = "models/component_tpls.model.php";
    		break;

    		case AppComponentsUIHelper::FILE_EDITABLE_MODEL:
    			$pathRelativeFile = "models/component_tpl.editable.model.php";
    		break;
    		
    		case AppComponentsUIHelper::FILE_LIST_MODEL:
    			$pathRelativeFile = "models/component_tpls.list.model.php";
    		break;
    		
    		case AppComponentsUIHelper::FILE_CRITERIA_MODEL:
    			$pathRelativeFile = "models/component_tpls.criteria.model.php";
    		break;

    		case AppComponentsUIHelper::FILE_REPORT_MODEL:
    			$pathRelativeFile = "models/component_tpls.report.model.php";
    		break;
    		
    		case AppComponentsUIHelper::FILE_MAIN_JS:
    			$pathRelativeFile = "views/component_tpls.main.js";
    		break;    		
    	}
    	
    	if (!$pathRelativeFile) {
    		$fileGenerated->msgError = "No disponible: $nameFileTpl";
    		return $fileGenerated;
    	}
    	
    	$pathFile = Exj::GetPathDirSrcWeb() . "/exj_component_tpls/" . $pathRelativeFile;
    	if (!file_exists($pathFile)) {
    		$fileGenerated->msgError = "No existe archivo: " . $pathRelativeFile;
            Exj::PrintBackTrace(__METHOD__." NO EXISTE ARCHIVO");
    		return $fileGenerated;
    	}
    	
    	Exj::TrasferCharsDecodeUTF8ToISO($plural_com);
    	Exj::TrasferCharsDecodeUTF8ToISO($singular_com);

    	$fileGenerated->content = file_get_contents($pathFile);
    	$fileGenerated->content = self::ReplaceMarcksTpl($fileGenerated->content, $nameComp, $plural_com, $singular_com, $nameTable, $itemsColsTable);
    	
		self::LoadPrefixFilesOfComponent($nameComp, $component_tpls, $component_tpl, $exj_component_tpls);
		$pathRelComp = str_replace('exj_component_tpls', $exj_component_tpls, $pathRelativeFile);
		$pathRelComp = str_replace('component_tpl', $component_tpl, $pathRelComp);
    	$fileGenerated->pathFileComp = $nameComp . '/' . $pathRelComp;

        $nameFilePHP = basename($fileGenerated->pathFileComp);
        if (substr($nameFilePHP, -4) == '.php') {
            $nameClassGen = self::_GetNameClassFromString($fileGenerated->content);
            if ($nameClassGen) {
                $fileGenerated->pathFileComp = dirname($fileGenerated->pathFileComp).'/'.$nameClassGen;
                $fileGenerated->pathFileComp .= '.php';
            }
        }

        // echo "<br>fileGenerated->pathFileComp: $fileGenerated->pathFileComp";
    	
    	return $fileGenerated;
    }

    private static function _GetNameClassFromString($content){
        $nameClass = '';

        $contents = explode("\n", $content);
        
        $iniComment = false;
        foreach ($contents as $strLine) {
            $strLine = trim($strLine);
            if (!$strLine) {
                continue;
            }

            $firstLetters = substr($strLine, 0, 2);
            if ($firstLetters == '//') {
                continue;
            }

            if ($firstLetters == '/*') {
                $iniComment = true;
                continue;
            }

            if ($iniComment) {
                if (substr($strLine, -2) == '*/') {
                    $iniComment = false;
                    continue;
                }
            }

            if ($iniComment) {
                continue;
            }

            if (strlen($strLine) <= 9 || $firstLetters != 'cl') {
                continue;
            }

            if (strpos($strLine, 'class ') === false) {
                continue;
            }

            $posEnd = strpos($strLine, ' ', 7);
            $nameClass = $strLine;
            if ($posEnd === false) {
                $nameClass = trim($nameClass, '{');
            }
            else{
                $nameClass = trim(substr($nameClass, 6, $posEnd-6));
            }
            
            // echo "<br>strLine: $strLine posEnd: $posEnd nameClass: $nameClass";

            break;
        }

        return $nameClass;
    }
    
    public static function LoadPrefixFilesOfComponent($nameComp, &$component_tpls, &$component_tpl, &$exj_component_tpls){
    	$exj_component_tpls = substr($nameComp, 4);
    	
    	$component_tpls = str_replace(Exj::PREFIX_COMP_APP, '', $nameComp);
    	$component_tpls = trim($component_tpls, '_');
    	$component_tpl = substr($component_tpls, 0, strlen($component_tpls)-1);
    }
    
    public static function ConvertLabelTable($nameTable){
    	$aliasTable = str_replace(Exj::PREFIX_TABLES_APP, '', $nameTable);
    	$aliasTable = str_replace('_', ' ', $aliasTable);
    	$aliasTable = trim(ucwords($aliasTable));
    	return $aliasTable;
    }
    
    public static function ReplaceMarcksTpl($contentFile, $nameComp, $plural_com, $singular_com, $nameTable, $itemsColsTable){
    	if (!$contentFile) {
    		return $contentFile;
    	}
    	
    	$contentFile = str_replace(
            '{date_current}', date(Exj::GetValueCfg('uiFormatDateDef')), $contentFile
        );

    	$nameAuthor = ExjUser::GetNomsApes();
        Exj::TrasferCharsDecodeUTF8ToISO($nameAuthor);
        $contentFile = str_replace('{name_author}', $nameAuthor, $contentFile);

    	$contentFile = str_replace('exj_component_tpls', $nameComp, $contentFile);
    	$contentFile = str_replace('{labelComponents}', $plural_com, $contentFile);
    	$contentFile = str_replace('{labelComponent}', $singular_com, $contentFile);
    	
    	self::LoadPrefixFilesOfComponent($nameComp, $component_tpls, $component_tpl, $exj_component_tpls);
    	$contentFile = str_replace('exj_component_tpls', $exj_component_tpls, $contentFile);
    	$contentFile = str_replace('component_tpls', $component_tpls, $contentFile);
    	$contentFile = str_replace('component_tpl', $component_tpl, $contentFile);
    	
    	$ComponentTpls = ucwords(str_replace("_", ' ', $component_tpls));
    	$ComponentTpls = str_replace(" ", '', $ComponentTpls);
    	
    	$ComponentTpl = substr($ComponentTpls, 0, strlen($ComponentTpls)-1);
    	
    	$contentFile = str_replace('ComponentTpls', $ComponentTpls, $contentFile);
    	$contentFile = str_replace('ComponentTpl', $ComponentTpl, $contentFile);
    	
    	$contentFile = str_replace('{name_table}', $nameTable, $contentFile);
    	$aliasTable = trim(str_replace(Exj::PREFIX_TABLES_APP, '', $nameTable), '_');
    	if ($aliasTable) {
    		if (strpos($aliasTable, '_') !== false) {
    			$words = explode('_', $aliasTable);
    			$aliasTable = array();
    			foreach ($words as $w) {
    				$aliasTable[] = substr($w, 0, 1);
    			}
    			$aliasTable = implode('', $aliasTable);
    		}
    		else {
    			if (strlen($aliasTable) > 3) {
    				$aliasTable = substr($aliasTable, 0, 3);
    			}
    		}
    	}
    	else{
    		$aliasTable = 't';
    	}
    	
    	$contentFile = str_replace('{alias_table}', $aliasTable, $contentFile);
    	
    	/* reemplazo de campos de la tabla */
    	$id_field_key = '';
    	$editablePublicFields = array();
    	$editableConstFields = array();
        $editableProtectedFields = array();
    	$criteriaPublicFields = array();
    	$criteriaRegisterFields = array();
    	$criteriaRegisterControlUIs = array();
    	$comboboxSimpleFieldExtras = array();
    	$editableRegisterFields = array();
    	$editableRegisterControlUIs = array();
    	$jsWinSubmitAddToForm = array();
    	$jsWinSubmitFieldFocus = '';
    	$editableApplyValidations = array();
    	$editableCanDestroyRelationTable = array();
    	$listRegisterFields = array();
    	$listRegisterCol = array();
    	$field_sort_sql = '';
    	$listFieldActionEditSql = '';
    	$fields_table_sql = array();
    	$fields_valuetext_table_sql = array();
    	$fieldText = '';
    	$fieldsCriteria=array();
    	$jsItemsCriteria=array();
    	foreach ($itemsColsTable as $itemColTable) {
    		$nameCol = $itemColTable->nameCol;
    		$codeComment = $itemColTable->colComment;
    		$isNullable = $itemColTable->isNullable;
    		$dataType = $itemColTable->dataType;
    		$labelCol = $itemColTable->labelCol;
    		$isColId = (strpos($nameCol, 'id_') === 0);
    		
    		$isFieldGlobal = ($nameCol == 'id_empresa' && !$itemColTable->isPrimaryKey);
            /*
            if (!$isFieldGlobal) {
                if (AppComponentsData::IsFieldValidateDatesFromUntil($nameCol)) {
                    $isFieldGlobal = true;
                }
            }
            */
    		
    		if (!$isFieldGlobal) {
	    		$fieldTableSql = "$aliasTable.$nameCol";
	    		if (count($fields_table_sql) % 2) {
	    			$fieldTableSql = "\n \t\t\t\t " . $fieldTableSql;
	    		}
	    		$fields_table_sql[] = $fieldTableSql;
    		}
    		
    		if (!$field_sort_sql && !$isNullable && $dataType == 'varchar') {
    			$field_sort_sql = $nameCol;
    		}
    		
    		if (!$listFieldActionEditSql && !$isNullable && !$isColId && $dataType == 'varchar') {
    			$listFieldActionEditSql = $nameCol;
    		}
    		
    		$fieldValueTextTableSql = "$aliasTable.$nameCol";
    		$isFieldValueText = false;
    		if ($itemColTable->isPrimaryKey) {
    			$fieldValueTextTableSql .= ' AS value';
    			$isFieldValueText = true;
    		}
    		elseif ($listFieldActionEditSql && !$fieldText) {
    			$fieldText = $listFieldActionEditSql;
    			$fieldValueTextTableSql .= ' AS text';
    			$isFieldValueText = true;
    		}
    		
    		if ((count($fields_valuetext_table_sql) > 0) && count($fields_valuetext_table_sql) % 4 == 0) {
    			$fieldValueTextTableSql = "\n \t\t\t\t " . $fieldValueTextTableSql;
    		}
    		$fields_valuetext_table_sql[] = $fieldValueTextTableSql;

    		
    		if ($codeComment) {
    			$codeComment = ucfirst($codeComment);
    		}
    		else {
    			$codeComment = $labelCol;
    		}
    		
    		if ($itemColTable->isPrimaryKey) {
    			$id_field_key = $nameCol;
    		}
    		
    		$typeVarComment = $dataType;
    		if ($typeVarComment == 'varchar') {
    			$typeVarComment = 'string';
    		}
    		elseif ($typeVarComment == 'enum'){
    			$typeVarComment = 'string ' . $itemColTable->colType;
    			
    			$prefixNameCol = $nameCol;
    			$posGuion = strpos($prefixNameCol, '_');
    			if ($posGuion !== false) {
    				$palabras = explode('_', $prefixNameCol);
    				$prefixNameCol = array();
    				foreach ($palabras as $palabra) {
    					if (strlen($palabra) > 3) {
    						$prefixNameCol[] = $palabra;
    					}
    				}
    				$prefixNameCol = implode('_', $prefixNameCol);
    				if (strlen($prefixNameCol) <= 3) {
    					$prefixNameCol = $nameCol;
    				}
    			}
    			$prefixNameCol = trim(strtoupper($prefixNameCol));
    			$valuesEnum = str_replace(array('enum(', ')', "'"), '', $itemColTable->colType);
    			$valuesEnum = explode(',', $valuesEnum);
    			
    			$editableConstFields[] = '/* '."$nameCol $codeComment".' */';
    			foreach ($valuesEnum as $valueEnum) {
    				$nameConstEnum = $prefixNameCol . '_' . strtoupper($valueEnum);
    				$editableConstFields[] = 'const '.$nameConstEnum.'=' . "'$valueEnum'" . ';';
    			}
    			$editableConstFields[] = '';
    		}
    		elseif ($typeVarComment == 'tinyint'){
    			$typeVarComment = 'int';
    		}

            // $editableProtectedFields
            $addPropEditable = true;
            if (AppComponentsData::IsFieldValidateDatesFromUntil($nameCol)) {
                $strFieldCode = 'protected $useValidDatesFromUntil = true;';
                if (!in_array($strFieldCode, $editableProtectedFields)) {
                    $editableProtectedFields[] = $strFieldCode;
                    $editableProtectedFields[] = '';
                }
                
                $addPropEditable = false;
            }

            if ($addPropEditable) {
                if (count($editablePublicFields) > 0) {
                    $editablePublicFields[] = '';
                }

                $editablePublicFields[] = '/**';
                $editablePublicFields[] = " * $codeComment";
                $editablePublicFields[] = " *";
                $editablePublicFields[] = " * @var $typeVarComment";
                $editablePublicFields[] = ' */';
                $editablePublicFields[] = 'public $' . $nameCol . ';';
            }
    		
    		if ($addPropEditable && count($fieldsCriteria) < 2 && !$isColId && $dataType != 'enum') {
    			$fieldsCriteria[] = $nameCol;
	    		$criteriaPublicFields[] = '';
	    		$criteriaPublicFields[] = '/**';
	    		$criteriaPublicFields[] = " * $codeComment";
	    		$criteriaPublicFields[] = " *";
	    		$criteriaPublicFields[] = " * @var $typeVarComment";
	    		$criteriaPublicFields[] = ' */';
	    		$criteriaPublicFields[] = 'public $' . $nameCol . ';';
    		}
    		
    		
    		if (!$itemColTable->isPrimaryKey) {
    			
    			/* editable.registerFields */
	    		$fnRegisterField = ($isNullable ? "registerFieldStringNullable" : "registerFieldString");
	    		$fnListRegisterField = "registerFieldString";
	    		$fnFieldUI = 'NewFieldString';
	    		switch ($dataType) {
	    			case 'tinyint':
	    			case 'int':
	    				if ($isColId) {
	    					$fnRegisterField = ($isNullable ? "registerFieldIdNullable" : "registerFieldId");
	    					$fnListRegisterField = "registerFieldId";
	    				}
	    				else {
	    					$fnRegisterField = ($isNullable ? "registerFieldIntNullable" : "registerFieldInt");
	    					$fnListRegisterField = "registerFieldInt";
	    				}
	    				
	    				$fnFieldUI = 'NewFieldInt';
	    			break;
	    			
	    			case 'decimal':
	    			case 'double':
	    				$fnRegisterField = ($isNullable ? "registerFieldFloatNullable" : "registerFieldFloat");
	    				$fnListRegisterField = "registerFieldFloat";
	    				$fnFieldUI = 'NewFieldFloat';
	    			break;
	    			
	    			case 'date':
	    				$fnRegisterField = ($isNullable ? "registerFieldDateNullable" : "registerFieldDate");
	    				$fnListRegisterField = "registerFieldDate";
	    				$fnFieldUI = 'NewFieldDate';
	    			break;
	    			
	    			case 'datetime':
	    				$fnRegisterField = ($isNullable ? "registerFieldDateTimeNullable" : "registerFieldDateTime");
	    				$fnListRegisterField = "registerFieldDateTime";
	    				$fnFieldUI = 'NewFieldDateTime';
	    			break;
	    		}

                if ($addPropEditable) {
                    $editableRegisterFields[] = '$this->' . $fnRegisterField . "('$nameCol', '$labelCol');";
                }
	    		
	    		$listRegisterFields[] = '$this->' . $fnListRegisterField . "('$nameCol', '$labelCol');";
	    		
	    		if (in_array($nameCol, $fieldsCriteria)) {
	    			$criteriaRegisterFields[] = '$this->' . $fnListRegisterField . "('$nameCol', '$labelCol');";
	    		}
	    		
	    		if (!$isFieldValueText) {
	    			$comboboxSimpleFieldExtras[] = '$fieldExtras[] = ExjUI::' . "$fnFieldUI('$nameCol')" . ';';
	    		}
	    		
	    		
	    		/* editable.registerControlUIs */
	    		$fnControlUI = "NewTextField";
	    		$fnJsControlUI = "getTextField";
	    		switch ($dataType) {
	    			case 'tinyint':
	    			case 'int':
	    				if ($isColId) {
	    					$fnControlUI = '';
	    					$fnJsControlUI = '';
	    				}
	    				else {
	    					if ($dataType == 'tinyint' && $itemColTable->numPrecision <= 3) {
	    						$fnControlUI = "NewCheckbox";
	    						$fnJsControlUI = 'getCheckbox';
	    					}
	    					else {
	    						$fnControlUI = "NewNumberFieldForInt";
	    						$fnJsControlUI = 'getNumberField';
	    					}
	    					
	    				}
	    			break;
	    			
	    			case 'decimal':
	    			case 'double':
	    				$fnControlUI = "NewNumberField";
	    				$fnJsControlUI = 'getNumberField';
	    			break;
	    			
	    			case 'date':
	    				$fnControlUI = "NewDateField";
	    				$fnJsControlUI = 'getDateField';
	    			break;
	    			
	    			case 'datetime':
	    				$fnControlUI = "NewFieldDateTime";
	    				$fnJsControlUI = 'getDateField';
	    			break;
	    			
	    			case 'text':
	    				$fnControlUI = "NewTextArea";
	    				$fnJsControlUI = 'getTextArea';
	    			break;
	    			
	    			case 'varchar':
	    				if ($itemColTable->charMaxLen > 210) {
	    					$fnControlUI = "NewTextArea";
	    					$fnJsControlUI = 'getTextArea';
	    				}
	    			break;
	    		}
	    		
	    		if ($fnControlUI) {
	    			if ($dataType == 'enum') {
	    				$editableRegisterControlUIs[] = '/* '. "$nameCol $itemColTable->colType" .' */';
	    				$jsWinSubmitAddToForm[] = '/* winSubmit.addToForm(editable.getComboBox'. "('$nameCol')" . '); */';
	    			}
	    			else {
                        if ($addPropEditable) {
                            $editableRegisterControlUIs[] = '$this->registerControlUI(ExjUI::' . $fnControlUI . "('$nameCol'));";
                        }
	    				
	    				$jsWinSubmitAddToForm[] = 'winSubmit.addToForm(editable.'. "$fnJsControlUI('$nameCol')" . ');';
	    				if (!$jsWinSubmitFieldFocus) {
	    					$jsWinSubmitFieldFocus = $nameCol;
	    				}
	    				
						if (in_array($nameCol, $fieldsCriteria)) {
							$criteriaRegisterControlUIs[] = '$this->registerControlUI(ExjUI::' . $fnControlUI . "('$nameCol'));";
							$jsItemsCriteria[] = "criteria.$fnJsControlUI('$nameCol')";
						}
	    			}
	    		}
	    		
	    		
	    		/* editable.applyValidations */
	    		if ($dataType == 'varchar' && $addPropEditable) {
	    			$editableApplyValidations[] = '$this->applyValidationTextNameExtendido(' . "'$nameCol', false, $itemColTable->charMaxLen". ');';
	    		}
	    		
	    		/* list.registerCol */
	    		if (!$isColId && !$isFieldGlobal) {
	    			$fnRegisterCol = '';
	    			
	    			if ($fnControlUI == "NewCheckbox") {
	    				$fnRegisterCol = 'registerColTextSino(%s, self::COL_ANCHO_NOMBRE)';
	    			}
	    			elseif ($fnControlUI == "NewFieldFloat"){
	    				$fnRegisterCol = 'registerColDecimal2(%s, self::COL_ANCHO_VALOR)';
	    			}
	    			elseif ($fnControlUI == "NewFieldDateTime"){
	    				$fnRegisterCol = 'registerColDateTime(%s, self::COL_ANCHO_FECHAHORA)';
	    			}
	    			elseif ($fnControlUI == "NewDateField"){
	    				$fnRegisterCol = 'registerColDate(%s, self::COL_ANCHO_FECHA)';
	    			}
	    			elseif ($dataType == "int"){
	    				$fnRegisterCol = 'registerColInt2(%s, self::COL_ANCHO_NOMBRE)';
	    			}
	    			else{
	    				if (strpos($nameCol, 'codigo') === 0) {
	    					$fnRegisterCol = 'registerCol(%s, self::COL_ANCHO_CODIGO)';
	    				}
	    				else {
	    					$fnRegisterCol = 'registerCol(%s, self::COL_ANCHO_NOMBRE)';
	    				}
	    			}
	    			
		    		if ($fnRegisterCol) {
		    			$listRegisterCol[] = '$this->' . sprintf($fnRegisterCol, "'$nameCol'") . ';';
		    		}
	    		}
    		}
    		
    		/* editable.canDestroyRelationTable */
    		if ($itemColTable->usages && count($itemColTable->usages) > 0) {
    			foreach ($itemColTable->usages as $usageTable) {
    				if ($usageTable->ruleDelete == 'RESTRICT') {
	    				$lblTable = self::ConvertLabelTable($usageTable->tableName);
    				
	    				$editableCanDestroyRelationTable[] = 'if (!$this->canDestroyRelationTable($id, ' . "'$usageTable->tableName', '$lblTable', '$usageTable->colName'" . ')) {';
	    				$editableCanDestroyRelationTable[] = "\t".'return false;';
	    				$editableCanDestroyRelationTable[] = '}';
    				}
    			}
    			
    		}
    	}
    	
    	if (!$id_field_key) {
    		$id_field_key = $itemsColsTable[0]->nameCol;
    	}
    	if (!$field_sort_sql) {
    		$field_sort_sql = $id_field_key;
    	}
    	if (!$listFieldActionEditSql) {
    		$listFieldActionEditSql = $field_sort_sql;
    	}
    	
    	$jsCriteriaFieldFocus = '';
    	if (count($fieldsCriteria) > 0) {
    		$jsCriteriaFieldFocus = $fieldsCriteria[0];
    	}
    	else {
    		$jsCriteriaFieldFocus = $listFieldActionEditSql;
    	}
    	
    	// print_r($itemsColsTable);
    	
    	$editablePublicFields = implode("\n\t", $editablePublicFields);
    	$editableConstFields = implode("\n\t", $editableConstFields);
        $editableProtectedFields = implode("\n\t", $editableProtectedFields);
    	$criteriaPublicFields = implode("\n\t", $criteriaPublicFields);
    	$criteriaRegisterFields = implode("\n\t\t", $criteriaRegisterFields);
    	$criteriaRegisterControlUIs = implode("\n\t\t", $criteriaRegisterControlUIs);
    	$editableRegisterFields = implode("\n\t\t", $editableRegisterFields);
    	$editableRegisterControlUIs = implode("\n\t\t", $editableRegisterControlUIs);
    	$jsWinSubmitAddToForm = implode("\n\t\t ", $jsWinSubmitAddToForm);
    	$editableApplyValidations = implode("\n\t\t", $editableApplyValidations);
    	$editableCanDestroyRelationTable = implode("\n\t\t", $editableCanDestroyRelationTable);
    	$listRegisterFields = implode("\n\t\t", $listRegisterFields);
    	$listRegisterCol = implode("\n\t\t", $listRegisterCol);
    	
    	$fields_table_sql = implode(', ', $fields_table_sql);
    	$fields_valuetext_table_sql = implode(', ', $fields_valuetext_table_sql);
    	
    	$comboboxSimpleFieldExtras = implode("\n\t\t ", $comboboxSimpleFieldExtras);
    	if (!$comboboxSimpleFieldExtras) {
    		$comboboxSimpleFieldExtras = '$fieldExtras = null;';
    	}
    	else {
    		$comboboxSimpleFieldExtras = '$fieldExtras = array();' . "\n\t\t " . $comboboxSimpleFieldExtras;
    	}

    	$contentFile = str_replace('id_field_key', $id_field_key, $contentFile);
    	$contentFile = str_replace('/*editable.public.fields*/', $editablePublicFields, $contentFile);
    	$contentFile = str_replace('/*editable.registerFields*/', $editableRegisterFields, $contentFile);
    	$contentFile = str_replace('/*editable.registerControlUIs*/', $editableRegisterControlUIs, $contentFile);
    	$contentFile = str_replace('/*js.winSubmit.addToForm*/', $jsWinSubmitAddToForm, $contentFile);
    	$contentFile = str_replace('{js.winSubmit.field.focus}', $jsWinSubmitFieldFocus, $contentFile);
    	$contentFile = str_replace('/*editable.applyValidations*/', $editableApplyValidations, $contentFile);
    	$contentFile = str_replace('/*editable.canDestroyRelationTable*/', $editableCanDestroyRelationTable, $contentFile);
    	$contentFile = str_replace('/*editable.canSaveCodeUnique*/', '', $contentFile);
    	
    	
    	$contentFile = str_replace('{field_sort_sql}', $field_sort_sql, $contentFile);
    	$contentFile = str_replace('{list.field_action_edit_sql}', $listFieldActionEditSql, $contentFile);
    	$contentFile = str_replace('/*list.registerFields*/', $listRegisterFields, $contentFile);
    	$contentFile = str_replace('/*list.registerCol*/', $listRegisterCol, $contentFile);
    	$contentFile = str_replace('{fields_table_sql}', $fields_table_sql, $contentFile);
    	$contentFile = str_replace('{fields_valuetext_table_sql}', $fields_valuetext_table_sql, $contentFile);
    	$contentFile = str_replace('/*criteria.public.fields*/', $criteriaPublicFields, $contentFile);
    	$contentFile = str_replace('/*criteria.registerFields*/', $criteriaRegisterFields, $contentFile);
    	$contentFile = str_replace('/*criteria.registerControlUIs*/', $criteriaRegisterControlUIs, $contentFile);
    	$contentFile = str_replace('{js.criteria.field.focus}', $jsCriteriaFieldFocus, $contentFile);
    	$contentFile = str_replace('/*combobox.simple.fieldExtras*/', $comboboxSimpleFieldExtras, $contentFile);
    	$contentFile = str_replace('/*editable.const.fields*/', $editableConstFields, $contentFile);

        $contentFile = str_replace('/*editable.protected.fields*/', $editableProtectedFields, $contentFile);
    	
    	$jsCriteriaItems=array();
    	$totalItemsCriteria = count($jsItemsCriteria);
    	if ($totalItemsCriteria > 0) {
    		$columnWidth = round((1 / $totalItemsCriteria), 2);
    		$nCriteriaItem = 0;
    		
    		$jsCriteriaItems[] = '{';
    		foreach ($jsItemsCriteria as $jsItemCriteria) {
    			$nCriteriaItem += 1;
    			
    			$jsCriteriaItems[] = "\t" . "columnWidth: $columnWidth,";
    			$jsCriteriaItems[] = "\t\t". 'items: [';
    			$jsCriteriaItems[] = "\t\t\t" . $jsItemCriteria;
    			$jsCriteriaItems[] = "\t\t" . ']';
    			
    			if ($nCriteriaItem < $totalItemsCriteria) {
    				$jsCriteriaItems[] = '}, {';
    			}
    			else {
    				$jsCriteriaItems[] = '}';
    			}
    		}
    		
    		$jsCriteriaItems = implode("\n\t\t\t\t", $jsCriteriaItems);
    	}
    	else {
    		$jsCriteriaItems = '{ /* TODO: Adicionar items */ }';
    	}
    	
    	$contentFile = str_replace('{js.criteria.items}', $jsCriteriaItems, $contentFile);
    	
    	
    	return $contentFile;
    }

    public static function DeleteAllFromGrupo($id_group_joomla, ExjResponse $response){
       $infoGroup = ExjAccess::GetInfoGroupFromId($id_group_joomla, 'id,name');
       if ($infoGroup === false) {
           return $response->setMsgError("Error consultar grupo. Ref: $id_group_joomla");
       }

       if (!$infoGroup) {
           return $response->setMsgError("No se encontró grupo. Ref: $id_group_joomla");
       }

       $nameGroup = trim($infoGroup->name);

       if (ExjAccess::IsPrivateIdGroup($id_group_joomla)) {
           return $response->setMsgError("Grupo $nameGroup es privado, no se puede eliminar");
       }

       // comprobar si existe en disco
       if ($nameGroup) {
           $pathDirComp = Exj::GetPathAppWeb() . '/' . $nameGroup;
           if (file_exists($pathDirComp)) {
               return $response->setMsgError("Grupo $nameGroup no se puede eliminar.")
                    ->addToMsg("Primero eliminar el directorio:")
                    ->addToMsg($pathDirComp);
           }
       }


        $infoCmp = AppComponentsData::GetInfoComponentFromIdGroupJoomla($id_group_joomla, 'singular_com');
        if ($infoCmp === false) {
            return $response->setMsgError("Error consultar componente. Ref: $id_group_joomla");
        }

        if ($infoCmp) {
            return $response->setMsgError("Existe componente $infoCmp->singular_com, primero Elimine, antes de eliminar todas las relaciones con el grupo: $nameGroup");
        }

       $infoProcess = array();

        $idK2 = ExjAccess::GetIdFromCategories($id_group_joomla);
        if ($idK2 === false) {
            return $response->setMsgError("Error consultar cat k2. Grupo: $nameGroup");
        }

        $itemsMenus = ExjMenu::GetRowsFromIdAccess($id_group_joomla, 'id,menutype,name');
        if ($itemsMenus === false) {
            return $response->setMsgError("Error consultar menus desde idAccess. Grupo: $nameGroup");
        }

        if (!empty($itemsMenus)) {
            foreach ($itemsMenus as $itemMenu) {
                if (!ExjMenu::IsTypeApp($itemMenu->menutype)) {
                    $response->setMsgError("Menú $itemMenu->name tipo: $itemMenu->menutype está relacionado y no pertenece a la Aplicación, elimine el menú para proceder.");
                    break;
                }
            }
        }

        if ($response->haveMsgError()) {
            return $response;
        }

        // eliminar menu
        if (!empty($itemsMenus)) {
            foreach ($itemsMenus as $itemMenuDel) {
                if (ExjMenu::DeleteFromId($itemMenuDel->id) === false) {
                    $response->setMsgError("Error al eliminar menú $itemMenuDel->name Grupo: $nameGroup");
                    break;
                }
                else{
                    $infoProcess[] = "Eliminado menú: $itemMenuDel->name";
                }
            }
        }

        if ($idK2 && !$response->haveMsgError()) {
           if (ExjAccess::DeleteRulesACLFromAxoSection($idK2) === false) {
               $response->setMsgError("Error al eliminar reglas ACL. Grupo: $nameGroup");
           }
           else{
                $nRowsDeleteds = ExjDatabase::GetAffectedRowsOfLastQuery();
                $infoProcess[] = "Eliminado $nRowsDeleteds reglas ACL";
           }
        }

        // eliminar k2 categories
        if ($idK2 && !$response->haveMsgError()) {
            if (ExjAccess::DeleteCategoriesFromIdAccess($id_group_joomla) === false) {
                $response->setMsgError("Error al eliminar categories k2. Ref: $id_group_joomla, Grupo: $nameGroup");
            }
            else{
                $nRowsDeleteds = ExjDatabase::GetAffectedRowsOfLastQuery();
                $infoProcess[] = "Eliminado $nRowsDeleteds k2_categories";
            }
        }

        // eliminar grupo
        if (!$response->haveMsgError()) {
            if (ExjAccess::DeleteGroupFromId($id_group_joomla) === false) {
                $response->setMsgError("Error al eliminar group. Ref: $id_group_joomla, Grupo: $nameGroup");
            }
            else{
                $infoProcess[] = "Eliminado Grupo: $nameGroup";
            }
        }
        
        if (!$response->haveMsgError()) {
            $response->setMsgInfo("Se eliminó satisfactoriamente.")
                    ->addToMsg("Procesos ejecutados: ". count($infoProcess));
        }
        
        return $response->addToMsg($infoProcess);
    }
}

?>