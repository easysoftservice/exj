<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

/**
 * @class AppSysUpgradesListModel
 * Modelo de lista para: Actualizaciones del Sistema
 */
class AppSysUpgradesListModel extends ExjListModel {
	
	/**
	 * overwrite. Inicio del Modelo de Lista
	 *
	 */
	public function listInit(){
		$this->setNameModelController('sys_upgrades', 'sys_upgrades');
		
		$this->setReportDownload(true, true, true);
		
		$this->setConfig('Actualizaciones', 'id_sys_upg');
		$this->nameTopics = 'Actualizaciones';
		$this->nameTopic = 'Actualización';
		$this->defaultSort = 'version_upg';
		$this->autoAddColsNameUserDateRegister();
	}

	
	/**
	 * overwrite. Registro de Campos
	 *
	 */
	public function listRegisterFields(){
		$this->registerFieldString('version_upg', 'Versión');
		$this->registerFieldInt('state_upg', 'Id Estado');
		$this->registerFieldString('state_text', 'Estado');
		$this->registerFieldString('color', 'Color del Estado');
		
		$this->registerFieldString('version_upg', 'Versión');
		$this->registerFieldString('desc_upg', 'Descripción');
		
		
		$this->registerFieldString('file_zip_sql', 'DB');
		$this->registerFieldString('file_zip_code', 'Código');
		$this->registerFieldInt('id_file_code', 'Id File code');
		$this->registerFieldInt('id_file_sql', 'Id File sql');
	}
	
	/**
	 * overwrite. Registro de Columnas
	 *
	 */
	public function listRegisterCols(){
		$this->registerCol('file_zip_code', 24);
		$this->registerCol('file_zip_sql', 24);
		$this->registerCol('version_upg', 15);
		$this->registerColCustom('state_text', 'Estado', 'Exj.rendererTextColor', 21, false);
	}	

	/**
	 * override. Devuelve data al cliente
	 *
	 * @param array $items
	 * @param int $total
	 */
	public function onGetData(&$items, &$total){
		ExjRequest::SetParamsQueryFromModelList($this);
		
		$isLoad =  AppSysUpgradeModel::loadListSysUpgrades($items, $total);
		
		ExjRequest::ClearParamsQuery();
		
		return $isLoad;
	}
	
	/**
	 * overwrited. Devuelve los items q se adicionaran al toolbar del grid en la parte superior Derecha
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasRight(){
		if (!ExjUser::IsRolSuperOAdmin()) {
			return null;
		}
		
		$items = array();
		
		$btnExecuteCode = ExjUI::NewButton(
			'Ejecutar Código...',
			'Permite descomprimir el código hacia el sistema',
			'exj-btn-execute',
			'executeCode'
		);
		
		$btnExecuteCode->isCode = true;
		
		$btnExecuteSQL = ExjUI::NewButton('Ejecutar DB...', 'Permite descomprimir el archivo sql y ejecutarlo en la db', 'exj-btn-execute', 'executeSql');
		$btnExecuteSQL->isSql = true;
		
		$items[] = $btnExecuteCode;
		$items[] = $btnExecuteSQL;
		
		// print_r($items);
		
		return $items;
	}

	/**
	 * overwrited. Devuelve los items q se adicionarán al toolbar del grid 
	 * en la parte superior Izquierda
	 *
	 * @return array
	 */
	public function getItemsTopbarExtrasLeft(){
		$items = array();

		$mnuItem = ExjUI::NewMenuItem('Código...', 'exj-btn-execute');
		$mnuItem->setAction('filesCode');
		$mnuItem->isCode = true;
		$mnuItem->tooltip = 'Lista los archivos comprimidos del Código';
		
		$verSubItems[] = $mnuItem;
		
		
		$mnuItem = ExjUI::NewMenuItem('DB...', 'exj-btn-execute');
		$mnuItem->setAction('filesSql');
		$mnuItem->isSql = true;
		$mnuItem->tooltip = 'Lista los archivos comprimidos para la db';
		
		$verSubItems[] = $mnuItem;
		
		
		$btnMnuVer = ExjUI::NewBotonMenu(
			'Ver Archivos', 'app-btn-view', $verSubItems, 'Listado del archivos'
		)->setAction('viewFiles');
		// ExjUI::ApplyAction($btnMnuVer, 'viewFiles', true);
		$items[] = $btnMnuVer;

		$items[] = ExjUI::NewButton(
			'Ejecutar script sql...',
			'Ejecuta script SQL',
			'exj-btn-exec-script',
			'execScriptSql'
		);

		$btnBackupDB = ExjUI::NewButton(
			'Backup DB...',
			'Realiza respaldo de la base de datos',
			'exj-btn-seguridad',
			'backupDB'
		);

		$rowParamSys = AppSysParametersData::GetRowFromCode(
            AppSysParametersHelper::CODE_PATH_PROG_BKDB,
            'type_param,value_param'
        );

        if ($rowParamSys) {
        	$btnBackupDB->path_mysqldump = $rowParamSys->value_param;
        }
        else{
        	$btnBackupDB->path_mysqldump = 'mysqldump.exe';
        }

		$items[] = $btnBackupDB;

		$items[] = ExjUI::NewButton(
			'Rebuild Js...',
			'Reconstruye archivos js en el public',
			'app-btn-admin',
			'rebuildJs'
		);
		
		return $items;		
	}	
	
}

?>