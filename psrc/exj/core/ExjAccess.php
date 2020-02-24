<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjAccess extends ExjObject {

	public static function GetInfoGroupFromId($id_group_joomla, $fields='*'){
		return ExjDatabase::GetObjectFromQuery("SELECT $fields FROM jos_groups WHERE id=$id_group_joomla");
	}

	public static function IsPrivateIdGroup($id_group_joomla){
		return ($id_group_joomla <= 3);
	}

	public static function GetIdFromCategories($idAccess){
        $db = Exj::InstanceDatabase();
        
        $query = "SELECT k.id FROM jos_k2_categories k WHERE k.access = $idAccess";
        
        $id = $db->loadResult($query);
        if (!$db->isValid()) {
        	return false;
        }
        
        return $id;
	}

	public static function GetIdFromGroups($nameComponent){
        $db = Exj::InstanceDatabase();
        
        $query = "SELECT 
		  gro.id 
		FROM 
		  jos_groups gro 
		WHERE gro.name = '$nameComponent'";
        
        $id = $db->loadResult($query);
        if (!$db->isValid()) {
        	return false;
        }
        
        return $id;
	}

	public static function DeleteGroupFromId($id){
		return ExjDatabase::ExecuteQuery("DELETE FROM jos_groups WHERE id=$id");
	}

	public static function DeleteCategoriesFromIdAccess($idAccess){
		return ExjDatabase::ExecuteQuery("DELETE FROM jos_k2_categories WHERE access=$idAccess");
	}

	public static function DeleteRulesACLFromAxoSection($axo_section){
		$where = array();
		$where[] = "aco_section='com_k2'";
		$where[] = "aco_value='category'";

		$where[] = "axo_section='$axo_section'";

		$where = implode(' AND ', $where);

		$query = "DELETE FROM jos_noixacl_rules WHERE ($where)";
		return ExjDatabase::ExecuteQuery($query);
	}

}
?>