<?php
/**
 * @class AppGlobalsController
 * A simple application controller extension
 */
class AppGlobalsController extends ExjController {
	/**
	 * view
	 * Recupera filas desde la base de datos.
	 */
	public function view() {
		$res = new ExjResponse();
		$res->success = true;
		$res->message = "Data Cargada";
		//var_dump($this->request);
        if (isset($_REQUEST['start'])) {
            $this->startDate = $_REQUEST['start'];
            $this->endDate = $_REQUEST['end'];
            $res->data = AppGlobalModel::range($this->startDate, $this->endDate);
        } else {
        	$res->data = AppGlobalModel::all();
        }
        
        // print_r($res);
        
		return $res;
	}
	
	/**
	 * Retorna datos globales para la aplicación, la primera vez que se inicia
	 *
	 * @return ExjResponse
	 */
	public function getDataGlobal() {
		$response = new ExjResponse();
		
		$dataGlobal = new stdClass();
		
		
		$dataGlobal->Const = AppGlobalModel::getDataConstantesUI();
		
		$dataGlobal->infoUser = AppGlobalModel::GetDataInfoUser();

		if (Exj::GetError()->haveError()) {
			$response->forceExit();
			return $response;
		}
                
        if(!$dataGlobal->infoUser->uri_logo_frontal) {
            $dataGlobal->infoUser->uri_logo_frontal = ExjResource::GetUriLogoFrontEndDefault();
        }

        $dataGlobal->nameTplSys = ExjResource::GetNameTemplateSys();
		
		$hInfoUser = new ExjHelperInfoUser();
		$hInfoUser->bindToSession($dataGlobal->infoUser);
	//	print_r($dataGlobal->infoUser);
	
		$dataGlobal->infoUser->isRolSuperAdmin = ExjUser::IsRolSuperAdmin();

		$dataGlobal->emisor = null;
		$dataEmisor = null;
		if ($hInfoUser->id_emisor) {
			$dataEmisor = AppEmisorsData::GetInfo($hInfoUser->id_emisor, [
				'ruc_emisor',
				'cod_establecimieto',
				'cod_punto_emision'
			]);
		}

		if ($dataEmisor) {
			$dataGlobal->emisor = new stdClass();
			$dataGlobal->emisor->ruc = $dataEmisor->ruc_emisor;
			$dataGlobal->emisor->cod_establecimieto = $dataEmisor->cod_establecimieto;
			$dataGlobal->emisor->cod_punto_emision = $dataEmisor->cod_punto_emision;
		}
		
		
		$dataGlobal->infoFile = AppGlobalModel::getDataInfoFile(); 
		if (Exj::GetError()->haveError()) {
			return $response;
		}

		$dataGlobal->segTimeoutRep = 0;
		$maxSegs = AppSysParametersHelper::GetValue_MAX_EXEC_REP_SEGS();
		if ($maxSegs && $maxSegs > 1) {
            $dataGlobal->segTimeoutRep = $maxSegs;
            // para lado del cliente adicionamos 3 segundos
            $dataGlobal->segTimeoutRep += 3;
        }
		
		$dataGlobal->dataAccess = new stdClass();
		$dataGlobal->dataAccess->modules = array();
		
		
		$dataGlobal->pgValuesMap = '';
		$dataGlobal->dataListLangGlobal = '';
		
		$dataGlobal->dataIdioma = '';
		
		$dataGlobal->dataMenusMain = AppGlobalModel::getDataMenusMain();
		$dataGlobal->dataMenusOpcGen = AppGlobalModel::getDataMenusOpcGen();
		
		// print_r($dataGlobal);
		/* cargar helpers a sesion */
		$hMenu = new ExjHelperMenu();
		$hMenu->clearMenuData();
		$hMenu->addMenuData($dataGlobal->dataMenusMain->items);	// add a sesion
		$hMenu->addMenuData($dataGlobal->dataMenusOpcGen->items); // add a sesion
		 // print_r($dataGlobal->dataMenusMain->items);
		
		
		$dataGlobal->dataBrowsers = AppGlobalModel::getDataBrowsers();
		
		// Datos para los modulos principales
		$dataGlobal->itemsModulesMains = AppGlobalModel::GetItemsModulesMains();
		$dataGlobal->itemsCmpAutoLoad = AppGlobalModel::GetItemsCmpAutoLoad();

		// Información de terceros
		if (class_exists('AppInfoGeneralPluginDisplay')) {
			$plgGlobal = new AppInfoGeneralPluginDisplay($this);			
		}
		else {
			$plgGlobal = new ExjInfoGeneralPluginDisplay($this);
		}

		$plgGlobal->setDataGlobal($dataGlobal)->loadDataUI();
		$dataGlobal->infoGeneral = $plgGlobal->getDataUI();
		 
		$response->data = $dataGlobal;
		return $response;
	}
	
	public function changeEmpresa(){
		$response = $this->getResponse();
		
		$id_empresa = $this->getParamId('id_empresa');
		if (!$id_empresa) {
			return $response->setMsgError("No se indicó ID de la Empresa");
		}
		
		$idEmpresaActual = ExjUser::GetIdEmpresa();
		if ($id_empresa == $idEmpresaActual) {
			return $response->setMsgInfo(ExjText::_('Seleccionado la misma Empresa asignada'));
		}
		
		AppGlobalModel::ChangeOffice($response, $id_empresa);
		
		return $response;
	}
	
	public function loginUser(){
		$response = new ExjResponse();
		
		$username = $this->getParamFromDataChanged('username');
		$passwd = $this->getParamFromDataChanged('passwd');
		
		$response->setMsgInfo("En construcción.<br/>Usuario: $username Contraseña: ***", "Login de usuario");
		
		$data = new stdClass();
		$data->forceExit = true;
		
		$response->data = $data;
		
		return $response;
	}
	
	
	/**
	 * Crear
	 */
	public function create() {
//		global $model;
		
		$res = new ExjResponse();

		// Ugh, php...check if !hash
		if (is_array($this->params) && !empty($this->params) && preg_match('/^\d+$/', implode('', array_keys($this->params)))) {
			foreach ($this->params as $data) {
				array_push($res->data, AppGlobalModel::create($data)->to_hash());
			}
			$res->success = true;
			$res->message = "Created " . count($res->data) . ' records';
		} else {
			// $model->create($this->params)
			
			if ($rec = AppGlobalModel::create($this->params)) {
				$res->data = $rec->to_hash();
                $res->success = true;
                $res->message = "Record created";
			} else {
				$res->success = false;
				$res->message = "Failed to create record";
			}
		}
		return $res;
	}

	/**
	 * Actualizar
	 */
	public function update() {
		$res = new ExjResponse();

		if (!get_class($this->params)) {
			$res->data = array();
			foreach ($this->params as $data) {
				if ($rec = AppGlobalModel::update($data->id, $data)) {
					array_push($res->data, $rec->to_hash());
				}
			}
			$res->success = true;
			$res->message = "Updated " . count($res->data) . " records";
		} else {
			if ($rec = AppGlobalModel::update($this->params->id, $this->params)) {
				$res->data = $rec->to_hash();
				$res->success = true;
				$res->message = "Updated record";
			} else {
				$res->message = "Failed to updated record " . $this->params->id;
				$res->success = false;
			}

		}
		return $res;
	}

	/**
	 * Destruír o Eliminar
	 */
	public function destroy() {
		$res = new ExjResponse();

		if (is_array($this->params)) {
			$destroyed = array();
			foreach ($this->params as $id) {
				if ($rec = AppGlobalModel::destroy($id)) {
					array_push($destroyed, $rec);
				}
			}
			$res->success = true;
			$res->message = 'Destroyed ' . count($destroyed) . ' records';
		} else {
			if ($rec = AppGlobalModel::destroy($this->id)) {
                $res->success = true;
                $res->message = "Destroyed record";
			} else {
				$res->message = "Failed to Destroy event";
			}
		}
		return $res;
	}
	
	public function getGeocode() {
		$response = new ExjResponse();
		
		$address = $this->getParam('address');
		if (!$address) {
			$response->setMsgError("No se indicó la dirección");
		}
		$address = trim($address);
		if (!$address) {
			$response->setMsgError("Dirección esta vacia");
		}
		
		
		AppGlobalModel::GetGeocode($response, $address);
		
		return $response;
	}
}

?>