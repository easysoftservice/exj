<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );
	
/**
 * Helper UI para Component
 * Autor: Byron Córdova
 */
class AppComponentsUIHelper {
	const FILE_CONTROLLER = 'controller.php';
	const FILE_DATA = 'data.php';
	const FILE_HELPER_UI = 'helper_ui.php';
	const FILE_EDITABLE_MODEL = 'editable.model.php';
	const FILE_CRITERIA_MODEL = 'criteria.model.php';
	const FILE_LIST_MODEL = 'list.model.php';
	const FILE_MODEL = 'model.php';
	const FILE_REPORT_MODEL = 'report.model.php';
	const FILE_MAIN_JS = 'main.js';

	
	/**
	 * ComboBox de Componentes
	 *
	 * @param string $name. No es requerido, por defecto: id_loc
	 * @param string $fieldLabel. No es requerido, por defecto: Componente
	 * @return ExjUIComboBox
	 */
	public static function NewComboSimpleComponents($name='id', $fieldLabel = 'Componente'){
    	$data = null;
    	AppComponentsData::LoadLookupComponentes($data);
    	
    	$fieldExtras = array();
    	$fieldExtras[] = ExjUI::NewFieldInt('id_cat');
    	$fieldExtras[] = ExjUI::NewFieldString('name_cat');
    	$fieldExtras[] = ExjUI::NewFieldInt('published');
		
    	$combo = ExjUI::NewComboSimple($name, $fieldLabel, $data, $fieldExtras);
    	$combo->setAnchor('98%');
		$combo->forceSelection = true;
		
    	return $combo;
	}
	
	
	public static function NewComboSimpleAppTables($anchor='510px',$name='nombre_tabla_com'){
    	$data = null;
    	AppComponentsData::LoadLookupAppTables($data);
    	
    	$fieldExtras = array();
    	$fieldExtras[] = ExjUI::NewFieldString('engine');
    	$fieldExtras[] = ExjUI::NewFieldInt('table_rows');
    	$fieldExtras[] = ExjUI::NewFieldString('table_comment');
    	$fieldExtras[] = ExjUI::NewFieldString('nameComponent');
    	$fieldExtras[] = ExjUI::NewFieldString('pluralComp');
    	$fieldExtras[] = ExjUI::NewFieldString('singularComp');
		
    	$combo = ExjUI::NewComboSimple($name, 'Tabla', $data, $fieldExtras, '- Seleccione -', false);
    	$combo->setAnchor($anchor);
		$combo->forceSelection = true;
		
		$tplContent = array();
		$tplContent[] = '<h3>{text}<span style="width: 51px;">{engine}</span></h3>';
		$tplContent[] = "Filas {table_rows}";
		
		$combo->setTplContentItemSelector($tplContent);
    	return $combo;
	}
	
	/**
	 * Combo con paginación Columnas de una tabla
	 *
	 * @param string $fieldKey
	 * @param string $fieldLabel
	 * @return ExjUIComboBox
	 */
	public static function NewComboPagingColumnas($fieldKey='table_col', $fieldLabel='Columnas'){
		$fieldsExtras = array();
		
		$fieldsExtras[] = ExjUI::NewFieldInt("isNullable");
		$fieldsExtras[] = ExjUI::NewFieldString("dataType");
		$fieldsExtras[] = ExjUI::NewFieldString("colComment");
				
		$tplContenido = array();
		$tplContenido[] = '<h3>{text}<span>{dataType}</span></h3>';
		$tplContenido[] = '{colComment}';
		
		$url = Exj::BuildURLModel('components', 'viewTableCols', 'exj_components');
		
		$listWidth = null;
		
    	$cmb = ExjUI::NewComboPaging($fieldKey, $fieldLabel, $url, $fieldsExtras, $tplContenido, '- Seleccione -', $listWidth, false);
    	$cmb->forceSelection = true;
    	$cmb->setAnchor();
    	$cmb->setMinChars(2);
    	$cmb->setAutoBindLoad();
    	
    	return $cmb;
	}

	public static function NewComboSimpleTplFiles($name='tpl_file'){
    	$items = array();
    	$items[] = ExjUI::NewItemLookup(self::FILE_CONTROLLER);
    	$items[] = ExjUI::NewItemLookup(self::FILE_DATA);
    	$items[] = ExjUI::NewItemLookup(self::FILE_HELPER_UI);
    	$items[] = ExjUI::NewItemLookup(self::FILE_EDITABLE_MODEL);
    	$items[] = ExjUI::NewItemLookup(self::FILE_CRITERIA_MODEL);
    	$items[] = ExjUI::NewItemLookup(self::FILE_LIST_MODEL);
    	$items[] = ExjUI::NewItemLookup(self::FILE_MODEL);
    	$items[] = ExjUI::NewItemLookup(self::FILE_REPORT_MODEL);
    	$items[] = ExjUI::NewItemLookup(self::FILE_MAIN_JS);
	
    	$combo = ExjUI::NewComboSimple($name, 'Archivo', $items, null, '- Seleccione -', false);
    	$combo->setWidth(180);
		$combo->forceSelection = true;
		
    	return $combo;
	}
}

?>