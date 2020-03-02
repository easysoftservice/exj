<?php

defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Información Global del Sistema
 *
 */
class AppGlobalData{
	
	
	public static function GetDataUser() {
    	global $exj;
    	$db = Exj::InstanceDatabase();
    	
    	$id_user = ExjUser::GetId();
    	if (!$id_user) {
    		$exj->setErrorValidating('Sesión de usuario expirada');
    		return null;
    	}
    	
    	$subqCliente = "SELECT 
      c.id_cliente, c.cod_cliente, c.id_empresa, c_usr.id_sys_user 
    FROM 
      jos_app_clientes c INNER JOIN jos_app_cli_usrs c_usr ON c.id_cliente = c_usr.id_cliente";
		
    	$query = "SELECT 
  syu.id_sys_user, syu.sys_type_theme, syu.enable_debug,
  p.nombres_persona, p.apellidos_persona, p.alias_persona, 
  CONCAT_WS(' ', p.apellidos_persona, p.nombres_persona) AS apes_noms_persona,
  p.nro_doc_persona,
  o.nom_empresa, o.cod_empresa, o.uri_logo_frontal,
  o.id_emisor,
  i.name_company, i.img_logo_company, u.usertype, 
  u.block AS user_block, city.id_sit, city.name_sit AS name_ciu_com, syl.name_lang,
  syl.acronym_lang, syu.id_sys_lang, o.is_main AS is_main_empresa,
  syu.id_persona, syu.id_empresa, city.id_pais, i.id_company,
  cou.offset_time, cou.nom_pais, cou.name_sit_main, cou.nacionalidad_pais,
  provincia.name_sit AS name_state, city.id_sit_parent,
  If(p.email_person IS NULL, u.email, p.email_person) AS email,
  o.id_sit, o.id_loc_zip, city.offset_time_sit,
  city.loc_latitude, city.loc_longitude,
  city.cod_sit AS codigo_ciu,
  provincia.cod_sit AS codigo_prov,
  subq_cust.id_cliente, subq_cust.cod_cliente,
  citprs.name_sit AS name_city_prs,
  statprs.name_sit AS name_state_prs 
 FROM 
  jos_exj_sys_users syu 
  INNER JOIN  jos_app_personas p ON syu.id_persona = p.id_persona 
  INNER JOIN app_loc_empresas o ON syu.id_empresa = o.id_empresa 
  INNER JOIN jos_exj_companies i ON o.id_company = i.id_company 
  INNER JOIN jos_users u ON syu.id_user = u.id 
  INNER JOIN jos_exj_sys_lang syl ON syu.id_sys_lang = syl.id_sys_lang 
  INNER JOIN jos_app_loc_sites city ON o.id_sit = city.id_sit 
  INNER JOIN app_loc_paises cou ON city.id_pais = cou.id_pais 
  LEFT JOIN jos_app_loc_sites provincia ON city.id_sit_parent = provincia.id_sit 
  LEFT JOIN jos_app_loc_sites citprs ON p.id_sit = citprs.id_sit 
  LEFT JOIN jos_app_loc_sites statprs ON citprs.id_sit_parent = statprs.id_sit 
  LEFT JOIN ($subqCliente) subq_cust ON (syu.id_sys_user = subq_cust.id_sys_user AND syu.id_empresa = subq_cust.id_empresa) 
 WHERE 
  syu.id_user = $id_user";
    	
    	$dataUser = null;
    	$db->setQuery($query);
    	$db->loadObject($dataUser);
    	if (!$db->isValid()) {
    		return null;
    	}    	
    	
    	if (!$dataUser) {
    		$email = ExjUser::GetEmail();
    		$userName = Exj::GetUserUserName();
    		$userType = Exj::GetUserUserType();
    		$exj->setErrorValidating("Estimado Usuario <b>$userName</b> $userType, sú cuenta de usuario no está autorizada por la Empresa.<br/>Cuando se verifiquen sus datos se le notificará a sú Correo electrónico: <b>$email</b> para el acceso al Sistema " . Exj::GetTitleApp());
    		return null;
    	}

    	// format data
    	$propsInts = array(
    		'user_block'
    	);

    	foreach ($dataUser as $prop => $value) {
    		if (strlen($prop) <= 3 || $value === null) {
    			continue;
    		}

    		$prefijoProp = substr($prop, 0, 3);
    		if ($prefijoProp == 'id_' || $prefijoProp == 'is_') {
    			$dataUser->$prop = Exj::ParseInt($value, 0);
    		}
    		elseif (in_array($prop, $propsInts)) {
    			$dataUser->$prop = Exj::ParseInt($value, 0);
    		}
    	}
    	
    	if ($dataUser->user_block) {
    		$exj->setErrorValidating("Ha sido bloqueado, acceso denegado al sistema.");
    		return null;
    	}
    	
    	if (ExjUser::IsRolProveedor() && !ExjUser::GetIdAbogado($dataUser->id_sys_user))
    	{
    		$userName = Exj::GetUserUserName();
    		$exj->setErrorValidating("Estimado usuario $dataUser->apes_noms_persona, tiene el rol de PROVEEDOR, pero aún su usuario: <b>$userName</b> no está enlazado con nuestra base de datos de Abogados.<br>Por favor, notifique esto a: $dataUser->nom_empresa");
    		return null;
    	}
    	
    //	print_r($dataUser);
    	
    	$id_sys_user = $dataUser->id_sys_user;
    	
    	// determinar las empresas que tiene acceso el usuario
    	$dataUser->itemsOfcsRels = null;
    	$dataUser->idsOfcsRels = null;
    	if (ExjUser::IsRolSuperOAdminOContabilidad()) {
    		$dataUser->itemsOfcsRels = '*';
    		$dataUser->idsOfcsRels = '*';
    	}
    	else {
	    	$queryOfcsRel = "SELECT
			  us_ofc.id_empresa, ofc.cod_empresa, ofc.is_main
			FROM
			  jos_exj_sys_user_empresas us_ofc INNER JOIN app_loc_empresas ofc ON us_ofc.id_empresa = ofc.id_empresa
			WHERE 
			  us_ofc.id_sys_user = $id_sys_user
		    ORDER BY 
			  ofc.is_main DESC, ofc.cod_empresa";
	    	$db->setQuery($queryOfcsRel);
	    	$dataUser->itemsOfcsRels = $db->loadObjectList();
	    	if (!$db->isValid()) {
	    		return null;
	    	}
	    	
	    	if (!$dataUser->itemsOfcsRels || count($dataUser->itemsOfcsRels) == 0) {
	    		$exj->setErrorValidating("Estimado Usuario, su cuenta de usuario no está relacionado con alguna empresa.<br/>Consulte al Administrador para sú Registro.");
    			return null;
	    	}
	    	
	    	if (ExjUser::IsRolCliente()) {
	    		if (!$dataUser->id_cliente) {
	    			$exj->setErrorValidating("Estimado usuario, su cuenta es ROL Cliente, en la empresa: $dataUser->nom_empresa no está relacionado con un Cliente en el Sistema.<br/>Consulte al Administrador para su registro.");
    				return null;
	    		}
	    	}
	    	
	    	$dataUser->idsOfcsRels = array();
	    	foreach ($dataUser->itemsOfcsRels as $itemOfcRel) {
	    		$dataUser->idsOfcsRels[] = $itemOfcRel->id_empresa;
	    	}

	    	if (count($dataUser->idsOfcsRels) == 0) {
	    		$exj->setErrorValidating("Estimado usuario. No tiene acceso a ninguna empresa en el sistema.<br/>Consulte con el Administrador para su registro.");
    			return null;
	    	}
	    	
	    	if ($dataUser->id_empresa) {
	    		if (!in_array($dataUser->id_empresa, $dataUser->idsOfcsRels)) {
	    			$dataUser->idsOfcsRels[] = $dataUser->id_empresa;
	    		}
	    	}
    	}
    	
    	$dataUser->enable_debug = intval($dataUser->enable_debug);
    	
    	
    	if ($dataUser->loc_latitude !== null && $dataUser->loc_longitude !== null) {
    		if ($dataUser->offset_time_sit == null) {
    			$newOffsetTime = AppLocCiudadesHelper::CalcOffsetTime($dataUser->loc_latitude, $dataUser->loc_longitude);
    			if ($newOffsetTime != null) {
    				$dataUser->offset_time_sit = $newOffsetTime;
    				
    				$city = new AppCiudadEditableModel(false);
    				$city->setValueId($dataUser->id_sit);
    				$city->offset_time_sit = $newOffsetTime;
    				$city->save();
    				if ($city->haveBrokenRules()) {
    					// $exj->setErrorDB($city->getBrokenRules());
    					echo 'ERROR.<br/>'. $city->getBrokenRules();
    				}
    			}
    		}
    	}
    	
    	if ($dataUser->offset_time_sit !== null) {
    		$dataUser->offset_time = $dataUser->offset_time_sit;
    	}
    	
    	$dataUser->paramsGen = new stdClass();
    	
    	$dataUser->canEditAllUsr = (ExjUser::IsRolSuperOAdminOContabilidad() ? 1:0);
    	
    	// xxx
    //	print_r($dataUser);
    	
		return $dataUser;
	} // GetDataUser
	
	public static function validateItemMenu(&$v, $menuList, $accessUser) {
		$v->access = $accessUser->getAccess($v->access);
		$nameMnuItem = trim(strtolower($v->text), " .");
		
		if ($nameMnuItem == 'salir' || $nameMnuItem == 'exit') {
			return true;
		}
		
		if ($v->parent == 0) {
			$numChilds = 0;
			foreach ($menuList as $itemMnu) {
				if (intval($itemMnu->parent) != $v->id){
					continue;
				}
				if ($itemMnu->type == 'separator') {
					continue;
				}
				
				$access = $accessUser->getAccess($itemMnu->access);
				if (count($access->actions) == 0) {
					continue;
				}
				$numChilds += 1;
			}
			if ($numChilds == 0) {
				return false;
			}
		}
		else {
			if (count($v->access->actions) == 0) {
		//		echo "<br/>menu $v->text: NO TIENE PERMISOS. ";
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Obtiene datos para menus
	 *
	 * @param string $menutype
	 * @param string $idPanelToRender
	 * @return object
	 */
	public static function getDataMenus($menutype = '', $idPanelToRender=''){
    	global $exj;
    	$db = Exj::InstanceDatabase();
    	
    	if (!$menutype) {
    		$menutype = ExjMenu::MENU_TYPE_APP;
    	}
		
		
		$subqueryHelps = "SELECT 
  cat.access, Count(cat.id) AS nro_cat_hlp
 FROM 
  jos_categories cat 
  INNER JOIN jos_sections sec ON sec.id = cat.section 
  INNER JOIN jos_groups grp ON sec.access = grp.id
 WHERE
  sec.published = 1 AND cat.published = 1 AND grp.name = 'exj_app_help'
 GROUP BY 
  cat.access";
		
    	$query = "SELECT 
  mnu.id, mnu.name AS text, mnu.parent, mnu.access, grp.name AS
  grp_name, mnu.params, mnu.type, mnu.sublevel, hlp.nro_cat_hlp
 FROM 
  jos_menu mnu INNER JOIN 
  jos_groups grp ON mnu.access = grp.id LEFT JOIN 
  ($subqueryHelps) AS hlp ON mnu.access = hlp.access
 WHERE 
  mnu.menutype = '$menutype' AND mnu.published = 1 
 ORDER BY 
  mnu.parent, mnu.ordering;";
    	
    	$menuList = $db->loadObjectList($query);
    	if (!$db->isValid()) {
    		return null;
    	}
    	
    //	echo "<br/>query menuList:<br/>";
    //	 echo $db->getQuery();
    //	echo '<br/> Tipo Usr: '. ExjApi->GetUserUserType();
    	
		$accessUser = new AppGlobalDataAccessUser();
		if (!$accessUser->isValid()) {
			return null;
		}
		
	//	print_r($accessUser);
    	
		$itemsMenus = array();
		foreach ($menuList as &$v)
		{
			$v->id = intval($v->id);
			$v->parent = intval($v->parent);
			
			$v->idPanelToRenderVU = ($idPanelToRender ? $idPanelToRender : null);

			if (!self::validateItemMenu($v, $menuList, $accessUser)) {
		//		echo "<br/><b>Menú invalido: </b>: $v->text ";
				continue;
			}
			
	//		echo "<br/><b>Menú OK</b>: $v->text ";
			/*
			print_r($v);
			*/
			if (strtolower($v->grp_name) == 'registered') {
				$v->grp_name = '';
			}
			if (!$v->nro_cat_hlp) {
				$v->nro_cat_hlp = 0;
			}
			$v->numCatHlp = $v->nro_cat_hlp;
			
			$v->iconCls = '';
			$v->nameModule = '';
			$v->page_title = $v->text;
			
			if ($v->type == 'separator') {
				$v->text = '-';
			}
			elseif ($v->params){
				$params = new JParameter($v->params);
				$v->url = $params->get('url');
				$v->menu_image = $params->get('menu_image');
				$v->iconCls = $params->get('pageclass_sfx');
				
				$v->nameModule = ExjHelper::GetNamModUIFromGroupName($v->grp_name);
				if (!$v->nameModule) {
					$v->nameModule = ExjHelper::getNamModUIFromURL($v->url);
				//	echo " <b>Obteniendo desde url</b>: $v->url";
				}
				 // echo " <b>Módulo</b>: $v->nameModule";
				
				$show_page_title = $params->get('show_page_title');
				if ($show_page_title) {
					$v->page_title = $params->get('page_title');
				}
			}
			$v->params = null;
			if (!$v->page_title) {
				$v->page_title = $v->text;
			}
			$v->page_title = ExjText::__($v->page_title);
			$v->text = ExjText::__($v->text);
			
			$pt = $v->parent;
			$list = @$itemsMenus[$pt] ? $itemsMenus[$pt] : array();
			array_push( $list, $v );
			$itemsMenus[$pt] = $list;
		}
		
		
		$dataMenus = new stdClass();
		$dataMenus->items = AppGlobalDataMenu::parseData($itemsMenus);
		
	//	print_r($dataMenus->items);
		
	
		if ($dataMenus->items && count($dataMenus->items) >= 3) {
			if ($menutype == ExjMenu::MENU_TYPE_APP) {
				$tbHeaderMain = self::GetToolbarHeaderMain();
			//	echo "<b>menutype</b>: $menutype<br/>";
				// print_r($tbHeaderMain);
				
				$dataMenus->items[] = '->';
				$dataMenus->items[] = $tbHeaderMain;
			}
		}
		
		
		return $dataMenus;
	}
	
	public static function GetLabelUIInfoEmpresa(){
		$infoEmpresa = ExjUI::NewLabelUI(
			'lblInfoMainHeaderNameMunic', null, ExjUser::GetNombreEmpresa()
		);

    	$infoEmpresa->style = 'text-transform: uppercase;';
    	$infoEmpresa->cls = 'vu-tb-header-main';
    	
    	return $infoEmpresa;
	}
	
	public static function GetLabelUIInfoBienvenido(){
		$infoWelcome = ExjUI::NewLabelUI(
			null, null, 'Bienvenido ' . ExjUser::GetNames()
		);

    	$infoWelcome->cls = 'vu-tb-header-main';
    	
    	return $infoWelcome;
	}
	
	public static function GetToolbarHeaderMain(){
		$itemsToolbar = array();
    	
		// $itemsToolbar[] = self::GetLabelUIInfoEmpresa();
		// $itemsToolbar[] = '-';
    	
    	$itemsToolbar[] = self::GetLabelUIInfoBienvenido();
		
	//	if (ExjUser::IsRolSuperOAdmin() || ExjUser::IsRolContabilidad() || ExjUser::IsRolRecaudador() || ExjUser::IsRolCliente()) {
		if (ExjUser::IsRolSuperAdmin()) {
	    	$cmbEmpresa = AppLocEmpresasUIHelper::NewComboSimpleEmpresas(
	    		'id_empresa',
	    		'Empresa',
	    		true
	    	);

	    	$storeEmp = $cmbEmpresa->getStore();
	    	if ($storeEmp) {
	    		if ($storeEmp->getCount() <= 1) {
	    			$cmbEmpresa->setHidden();
	    		}
	    	}

	    	$cmbEmpresa->setValue(ExjUser::GetIdEmpresa())
	    		->setEditable(false)
	    		->setSelectOnFocus()
	    		->setAutoSelect()
	    		->setAllowBlank(false);

	    	$cmbEmpresa->setAction('selectEmpresa');

	    	// $cmbEmpresa->cls = 'vu-tb-header-main';
	    //	$cmbEmpresa->labelStyle = 'color: #FFFFFF;font: bold medium verdana,arial,helvetica,sans-serif;';
	    	
			$pnl = ExjUI::NewPanel('', $cmbEmpresa, 'form');
			$pnl->labelWidth = 66;
			$pnl->setBorder(false)
				->setFrame(false)
				->setBodyBorder(false)
				->setBodyStyle('padding:0px;')
				->setAnchor('100%');
			
			$itemsToolbar[] = '-';
			$itemsToolbar[] = $pnl;
		}
		
		$tb = ExjUI::NewToolbarUI($itemsToolbar, 'vu-tb-header-mainx');
	//	$tb->setLayout('fit');
		$tb->setHideBorders();

		return $tb;
	}
	
	public static function ChangeOffice(ExjResponse &$response, $idOfficeNew, $id_sys_user=0){
		global $exj;
		/*
		$db = new ExjDatabase();
		
		$sql = "UPDATE";
		$db->query($sql);
		*/

		/*
		echo "<br/>_SESSION antes:<br/>";
		print_r($_SESSION);
		echo '<br/>';
		*/
		
		if (!$id_sys_user) {
			$id_sys_user = ExjUser::GetIdSysUser();
		}
		
		if (!$id_sys_user) {
			// $testInfoUser = ExjUser::GetUserSys();
			$msgSessionOut = 'Finalizó el tiempo de sesión.<br/>Testing reload Aplication...';
		//	$msgSessionOut .= "ExjUser::IsLogin: ". ExjUser::IsLogin();
			
		//	session_save_path('D:\Temp');
			
		//	echo "<br/>Pruebas de obj InfoUser";
			
		/*
			if ($testInfoUser) {
				$msgSessionOut .= " Retorna infouser. testInfoUser->id_sys_user: $testInfoUser->id_sys_user";
			}
			else {
				$msgSessionOut .= " No se retorna nada de infouser";
			}
			*/
			
		/*
			echo "<br/>SESSION<br/>";
			print_r($_SESSION);
			*/
			
			$response->setMsgInfo($msgSessionOut);
			
			// $exj->setErrorValidating("Sesion terminada");
			return false;
		}
		
		$sysUser = new AppSysUserEditableModel(false, $response);
		$sysUser->setValueId($id_sys_user);
		$sysUser->id_empresa = $idOfficeNew;
		$sysUser->id_user = ExjUser::GetId();
		if ($sysUser->id_user) {
			$sysUser->save();
		}
		else {
			$exj->setErrorValidating("Finalizó el tiempo de sesión de usuario.");
			return false;
		}

		
		$sysUser->validateResponse();
		if (!$sysUser->isValid()) {
		//	echo "error " . __METHOD__;
			return false;
		}
		

		$infoUser = self::GetDataUser();
		if (Exj::GetError()->haveError()) {
			return false;
		}
		
	//	print_r($infoUser);

		// reseteo data de sesion y objetos estaticos
		
		 $hInfoUser = new ExjHelperInfoUser();
		
		$hInfoUser->saveValueToSession('id_empresa', $infoUser->id_empresa);
		$hInfoUser->saveValueToSession('cod_empresa', $infoUser->cod_empresa);
		$hInfoUser->saveValueToSession('nom_empresa', $infoUser->nom_empresa);
		$hInfoUser->saveValueToSession('is_main_empresa', $infoUser->is_main_empresa, true);
		$hInfoUser->saveValueToSession('name_sit_main', $infoUser->name_sit_main);
		$hInfoUser->saveValueToSession('name_state', $infoUser->name_state);
		$hInfoUser->saveValueToSession('codigo_prov', $infoUser->codigo_prov);
		$hInfoUser->saveValueToSession('name_ciu_com', $infoUser->name_ciu_com);
		$hInfoUser->saveValueToSession('id_pais', $infoUser->id_pais);
		$hInfoUser->saveValueToSession('nom_pais', $infoUser->nom_pais);
		$hInfoUser->saveValueToSession('id_sit', $infoUser->id_sit);
		$hInfoUser->saveValueToSession('offset_time', $infoUser->offset_time, true);
		$hInfoUser->saveValueToSession('id_cliente', $infoUser->id_cliente, true);
		$hInfoUser->saveValueToSession('cod_cliente', $infoUser->cod_cliente, true);
		
		/*
		$varsObj = get_object_vars($infoUser);
		foreach ($varsObj as $name => $value) {
			if ($name == '_bufferDebug' || $name == '_enableDebug') {
				continue;
			}
			
			$hInfoUser->saveValueToSession($name, $value);
		}
		*/
		
		// offset_time
		// $hInfoUser->setterObjToSession($infoUser);
		// $hInfoUser->fixDataOffice($infoUser->id_empresa, $infoUser->nom_empresa);
		
		/*
		echo "<br/>infoUser:<br/>";
		print_r($infoUser);
		*/
		
		/*
		echo "<br/>_SESSION:<br/>";
		print_r($_SESSION);
		*/
		
		$dataEmpresa = new stdClass();
		$dataEmpresa->id_empresa = $infoUser->id_empresa;
		$dataEmpresa->nom_empresa = $infoUser->nom_empresa;
		$dataEmpresa->is_main_empresa = $infoUser->is_main_empresa;
		$dataEmpresa->name_sit_main = $infoUser->name_sit_main;
		$dataEmpresa->name_state = $infoUser->name_state;
		$dataEmpresa->codigo_prov = $infoUser->codigo_prov;
		$dataEmpresa->name_ciu_com = $infoUser->name_ciu_com;
		
	//	print_r($infoUser);
	//	$id_sys_user = ExjUser::GetIdSysUser();
//		echo "<br/>id_sys_user: $id_sys_user";
		
		// $msgOk = ExjText::_('<b>%s</b> Cambio de empresa satisfactorio.<br/>Be updated the application to apply the changes.');
		$msgOk = ExjText::_('Cambio satisfactorio a <b>%s</b>');
		// $dataEmpresa->msgUI = sprintf($msgOk, ExjUser::GetNombreEmpresa());
		$dataEmpresa->msgUI = sprintf($msgOk, $infoUser->nom_empresa);
		
		$response->setDataObject($dataEmpresa);
		
		return true;
	}
}
	
?>