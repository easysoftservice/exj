<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppAdminMailsData
 *
 */
class AppAdminMailsData extends ExjData {
	
	/**
	 * Lista de Correos
	 *
	 * @return array de object
	 */
	static function loadListCorreos(&$items, &$total, $paramsCriteria=null){
        global $exj;
        $id_company = ExjUser::GetIdCompania();
        
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("mai.id_mail, mai.id_mail_tpl, mai.from_email, mai.sender_mail,
  mai.to_email, mai.subject_mail, mai.body_mail, mai.is_html,
  mai.cc_mail, mai.bcc_mail, mai.state_mail, mai.modificado_dt,
  usr.name AS name_usr, tpl.title_tpl, tpl.type_tpl,    
  IF(ISNULL(mai.subject_mail), tpl.subject_default, mai.subject_mail) AS subject_mail");
        
        $dbQuery->setTables("jos_app_mails mai INNER JOIN
  jos_app_mail_tpls tpl ON mai.id_mail_tpl = tpl.id_mail_tpl INNER JOIN
  jos_users usr ON mai.id_usuario_modifico = usr.id");
        
        if ($paramsCriteria) {
			$criteriaCorreos = new AppMailsCriteriaModel(false);
			if ($criteriaCorreos->bind($paramsCriteria)) {
				$criteriaCorreos->addConditionsQuery($dbQuery);
			}
        }
        
        $dbQuery->addConditions("tpl.id_company = $id_company");
        $dbQuery->addConditions("tpl.is_published = 1");
        
        $dbQuery->addOrders("mai.modificado_dt");
        
  		/* -------LOAD PARAMS--------------------- */
  		$dbQuery->loadRowsCount($items, $total, "mai.id_mail");
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		if (count($items) > 0) {
			$ids = array();
			foreach ($items as &$item) {
				$item->nro_attachs = 0;
				$item->names_attachs = '';
				
				$ids[] = intval($item->id_mail);
			}
			
			$ids = implode(",", $ids);
			
			$sql = "SELECT
  matt.id_mail_attach, matt.id_mail, matt.file_path
FROM
  jos_app_mails_attachs matt
WHERE
  matt.id_mail IN ($ids)";
			$db = Exj::InstanceDatabase();
			$itemsAttachs = $db->loadObjectList($sql);
			if (!$db->isValid()) {
				return false;
			}
			if (count($itemsAttachs) > 0) {
				foreach ($items as &$itemMail) {
					$idMail = $itemMail->id_mail;
					foreach ($itemsAttachs as $itemAttach) {
						if ($itemAttach->id_mail == $idMail) {
							$itemMail->nro_attachs += 1;
							// $itemAttachFound = $itemAttach;
							if ($itemMail->names_attachs) {
								$itemMail->names_attachs .= ', ';
							}
							$itemMail->names_attachs .= basename($itemAttach->file_path);
						}
					}
				}
			}
		}
		
		
       // $dbQuery->writeQueryExecuted();
        
        return true;
	}
	
	
	static function getInfoCorreo($id_mail){
		static $info;
		
		if (Exj::IsDefinedObj($info, 'id_mail', $id_mail)) {
			return $info;
		}
		
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  m.id_mail, m.from_email, m.sender_mail, m.to_email,
  m.subject_mail, m.body_mail, m.is_html, m.cc_mail,
  m.bcc_mail, m.state_mail, m.id_mail_tpl, tpl.title_tpl AS title_mail, 
  tpl.cnt_tpl AS tpl_mail
FROM
  jos_app_mails m INNER JOIN
  jos_app_mail_tpls tpl ON m.id_mail_tpl = tpl.id_mail_tpl
WHERE
  m.id_mail = $id_mail";
        
        $db->setQuery($sql);
        $info = null;
        $db->loadObject($info);
        if (!$db->isValid()) {
        	Exj::SetErrorValidating($db->getErrorMsg());
        	return null;
        }
        
        if (!$info) {
        	Exj::SetErrorValidating("No se obtuvo información del correo");
        }
        
        $info->attachment = self::getInfoAttachCorreo($id_mail);
        
        
        return $info;
	}
	
	static function getInfoAttachCorreo($id_mail){
        global $exj;
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  att.id_mail_attach, att.file_path, att.file_name_custom
FROM
  jos_app_mails_attachs att
WHERE
  att.id_mail = $id_mail
ORDER BY
  att.file_name_custom";
        
        $db->setQuery($sql);
        $items = $db->loadObjectList($sql);
        
        if (!$db->isValid()) {
        	Exj::SetErrorValidating($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}	
	
	static function getInfoUserFromEmail($email, $validateNoFound=false, $separator=',<br/>'){
		static $info;
		
		if (Exj::IsDefinedObj($info, 'p_email', $email)) {
			return $info;
		}
		
		
		// echo __METHOD__.' email: '. $email;
		$itemsEmails = AppMailData::TransformEmailsToItems($email);
		if (!$itemsEmails) {
			return $itemsEmails;
		}
		
		$emailsEmpties = array();
		$nameEmails = array();
		foreach ($itemsEmails as $itemEmail) {
			if (!$itemEmail->name && $itemEmail->email) {
				$emailsEmpties[] = $itemEmail->email;
			}
			if ($itemEmail->name) {
				$nameEmails[] = $itemEmail->name;
			}
		}
		
		if (count($emailsEmpties) == 0) {
			$info = new stdClass();
			
        	$info->haveInfoPerson = false;
        	$info->p_email = $email;
        	$info->usuario_nombres = implode($separator, $nameEmails);
			
			return $info;
		}
		
		$emailTest = $emailsEmpties[0];
		
		
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  peo.id_persona, usr.email, peo.email_person, peo.nombres_persona,
  peo.apellidos_persona, usr.name, usr.username, usr.usertype,
  ofc.nom_empresa
FROM
  jos_users usr LEFT JOIN
  jos_exj_sys_users syu ON usr.id = syu.id_user LEFT JOIN
  jos_app_personas peo ON syu.id_persona = peo.id_persona LEFT JOIN
  app_loc_empresas ofc ON syu.id_empresa = ofc.id_empresa
WHERE
  (usr.email = '$emailTest') OR (peo.email_person = '$emailTest')
ORDER BY
  peo.id_persona DESC
LIMIT 1";
        
        $db->setQuery($sql);
        $info = null;
        $db->loadObject($info);
        if (!$db->isValid()) {
        	Exj::SetErrorValidating($db->getErrorMsg());
        	return null;
        }
        
        if (!$info && $validateNoFound) {
        	Exj::SetErrorValidating("No se obtuvieron datos del usuario.<br/>Correo: $email");
        	return $info;
        }
        
        if ($info) {
        	$info->p_email = $email;
        	$info->haveInfoPerson = true;
        	
        	$info->usuario_nombres = $info->name;
        	if ($info->id_persona) {
        		$info->usuario_nombres = $info->nombres_persona . ' '. $info->apellidos_persona;
        	}
        }
        
        return $info;
	}
	
}

?>