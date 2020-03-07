<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjMenu extends ExjObject {
	const MENU_TYPE_APP = 'mnu_app_main';
	const MENU_TYPE_OPCGEN_APP = 'mnu_app_opc_gen';

	public static function GetRowsFromIdAccess($idAccess, $fields='*'){
		if (is_array($fields)) {
			$fields = implode(',', $fields);
		}

		$query = "SELECT $fields from jos_menu WHERE access=$idAccess";
		return ExjDatabase::GetObjectList($query);
	}

	public static function IsTypeApp($menutype){
		return ($menutype == self::MENU_TYPE_APP || $menutype == self::MENU_TYPE_OPCGEN_APP);
	}

	public static function DeleteFromId($id){
		return ExjDatabase::ExecuteQuery("DELETE FROM jos_menu WHERE id=$id");
	}

}
?>