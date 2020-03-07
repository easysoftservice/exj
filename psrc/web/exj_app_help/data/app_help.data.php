<?php
defined( '_JEXEC' ) or die( 'Restricted access' );
	
/**
 * class. AppHelpsData
 *
 */
class AppHelpsData extends ExjData {
	
	/**
	 * Lista de Ayudas
	 *
	 * @return array de object
	 */
	static function loadListAyudas(&$items, $paramsCriteria=null){
        global $exj;

		$hMenu = new ExjHelperMenu();
		$menuData = $hMenu->getMenuData();
	//	print_r($menuData);
	
		$criteriaTema = '';
        if ($paramsCriteria) {
			// $exj->includeModelCriteria('helps');
			$criteriaHelps = new AppHelpsCriteriaModel(false);
			if ($criteriaHelps->bind($paramsCriteria)) {
				$criteriaTema = $criteriaHelps->name_help;
			}
        }
		
		$itemsHlp = array();
		$idsAccess = array();
		
		$itemsRegistered = self::getItemsHelpCntCat('1');
		// print_r($itemsRegistered);
		
		$addItemGeneral = ($itemsRegistered && count($itemsRegistered) > 0);
		if ($addItemGeneral) {
			if ($criteriaTema) {
				if (!ExjUtil::isEqualLikeAll('GENERAL', $criteriaTema)) {
					$addItemGeneral = false;
				}
			}
		}
		
		if ($addItemGeneral) {
			$itemHlpReg = new stdClass();
			
			$itemHlpReg->idMnu = 0;
			$itemHlpReg->nameMnu = 'GENERAL';
			$itemHlpReg->numCatHlp = 0;
			$itemHlpReg->iconCls = 'exj-icon-app';
			$itemHlpReg->moduleName = 'Registered';
			$itemHlpReg->idAccess = 1;
			
			$idsAccess[] = $itemHlpReg->idAccess;

			$idsCats = array();
			foreach ($itemsRegistered as $itemRegistered) {
				$itemRegistered->id_cat = intval($itemRegistered->id_cat);
				if (!in_array($itemRegistered->id_cat, $idsCats)) {
					$idsCats[] = $itemRegistered->id_cat;
					$itemHlpReg->idMnu += $itemRegistered->id_cat;
					$itemHlpReg->numCatHlp += 1;
				}
			}
			$itemHlpReg->idMnu *= -1;
			
			$itemsHlp[] = $itemHlpReg;
		}
		
		
		foreach ($menuData as $itemData) {
			foreach ($itemData as $itemMenu) {
				if (!isset($itemMenu->children)) {
					continue;
				}
				
				foreach ($itemMenu->children as $itemChild) {
					if (is_string($itemChild)) {
						continue;
					}
					
					/*
					if (!isset($itemChild->numCatHlp)) {
						echo "<br/>Ayuda item child no es un objeto, el valor es: ";
						print_r($itemChild);
						continue;
					}
					*/
					
					if (!$itemChild->numCatHlp) {
						continue;
					}
					
					$foundMnu = false;
					foreach ($itemsHlp as $itemTest) {
						if ($itemTest->idMnu == $itemChild->id) {
							$foundMnu = true;
							break;
						}
					}
					if ($foundMnu) {
						continue;
					}
					
					if ($criteriaTema) {
						if (!ExjUtil::isEqualLikeAll($itemChild->text, $criteriaTema)) {
							continue;
						}
					}
					
					$itemHlp = new stdClass();
					$itemHlp->idMnu = $itemChild->id;
					$itemHlp->nameMnu = $itemChild->text;
					$itemHlp->numCatHlp = $itemChild->numCatHlp;
					$itemHlp->iconCls = $itemChild->iconCls;
					$itemHlp->moduleName = $itemChild->access->moduleName;
					$itemHlp->idAccess = intval($itemChild->access->idAccess);
					if (!in_array($itemHlp->idAccess, $idsAccess)) {
						$idsAccess[] = $itemHlp->idAccess;
					}
					
					if ($itemHlp->idAccess == 1 && !$itemHlp->moduleName) {
						$itemHlp->moduleName = 'Registered';
					}
					
					$itemsHlp[] = $itemHlp;
				}
			}
		}

		
		
		$itemsHelpCntCat = self::getItemsHelpCntCat($idsAccess);
		if ($itemsHelpCntCat === false) {
			return false;
		}
		
		foreach ($itemsHlp as &$item) {
			$idAccess = $item->idAccess;
			
			$dataCats = array();
			foreach ($itemsHelpCntCat as $itemHelpCntCat) {
				if ($itemHelpCntCat->id_cnt && !$itemHelpCntCat->state_cnt) {
					continue;
				}
				if ($idAccess != intval($itemHelpCntCat->access)) {
					continue;
				}
				
				
				$foundCat = null;
				foreach ($dataCats as &$itemCat) {
					if ($itemCat->idCat == $itemHelpCntCat->id_cat) {
						$foundCat = $itemCat;
						break;
					}
				}
				if (!$foundCat) {
					$foundCat = new stdClass();
					$foundCat->idCat = $itemHelpCntCat->id_cat;
					$foundCat->titCat = $itemHelpCntCat->tit_cat;
					$foundCat->titsCnt = array();
					$dataCats[] = $foundCat;
				}
				
				if ($itemHelpCntCat->id_cnt) {
					$foundCat->titsCnt[] = $itemHelpCntCat->tit_cnt;
				}
			}
			
			$item->dataCats = $dataCats;
		}
		
	//	print_r($itemsHlp);
		$items = $itemsHlp;
		
		return true;
	}
	
	static function getLookupHelps(){
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  hlp.id_help AS value, hlp.name_help AS text, hlp.url_help,
  hlp.is_module, hlp.content_help, hlp.modificado_dt
FROM
  jos_app_help hlp
ORDER BY
  hlp.is_module DESC, hlp.name_help";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	global $exj;
        	
        	Exj::SetErrorDB($db->getErrorMsg());
        	return null;
        }
        
        return $items;
	}
	
	static function getItemsHelpCntCat($idsAccess=null){
        $db = Exj::InstanceDatabase();
        
        $whereSQL = array();
        $whereSQL[] = "grp_sec.name = 'exj_app_help'";
        $whereSQL[] = "sec.published = 1";
        $whereSQL[] = "cat.published = 1";
        
        if ($idsAccess) {
        	if (is_array($idsAccess)) {
        		$idsAccess = implode(",", $idsAccess);
        	}
    		$whereSQL[] = "cat.access IN ($idsAccess)";
        }
        
        $whereSQL = implode(" AND ", $whereSQL);
        
        $sql = "SELECT
  cnt.id AS id_cnt, cat.id AS id_cat, cat.title AS tit_cat,
  cat.access, cnt.state AS state_cnt, cnt.title AS tit_cnt
FROM
  jos_categories cat INNER JOIN
  jos_sections sec ON cat.section = sec.id INNER JOIN
  jos_groups grp_sec ON sec.access = grp_sec.id LEFT JOIN
  jos_content cnt ON cat.id = cnt.catid
WHERE 
  $whereSQL
ORDER BY
  cat.id, cat.ordering, cnt.ordering";
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	return false;
        }
		
        return $items;
	}
	
	/**
	 * Devuelve data de ayuda, dado el nombre del componente
	 *
	 * @param string $nameCmp
	 * @return mixed false si ha ocurrido algún error, sino object
	 */
	static function getDataHelpCmp($nameCmp){
        $db = Exj::InstanceDatabase();
        
        $sql = "SELECT
  cat.id, cat.title, cat.description, cat.image, cat.image_position,
  cnt_sum.nro_cnt
FROM
  jos_categories cat INNER JOIN
  jos_groups grp_cat ON cat.access = grp_cat.id INNER JOIN
  jos_sections sec ON cat.section = sec.id INNER JOIN
  jos_groups grp_sec ON sec.access = grp_sec.id LEFT JOIN
  (  
SELECT
  cnt.catid, Count(cnt.id) AS nro_cnt
FROM
  jos_content cnt
WHERE
  cnt.state = 1
GROUP BY
  cnt.catid
  ) cnt_sum ON cnt_sum.catid = cat.id
WHERE
  grp_cat.name = '$nameCmp' AND
  cat.published = 1 AND
  grp_sec.name = 'exj_app_help' AND
  sec.published = 1
GROUP BY
  grp_cat.name, cat.title, cat.description, cat.image,
  cat.image_position
ORDER BY
  cat.ordering";
        
        $itemsCats = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	return false;
        }
        
        $data = new stdClass();
        $data->numCnt = 0;
        $data->items = array();
        
        if (count($itemsCats) == 0) {
        	return $data;
        }
        
        $idsCats = array();
        foreach ($itemsCats as $itemCat) {
        	if (!$itemCat->nro_cnt) {
        		continue;
        	}
        	
        	$idsCats[] = $itemCat->id;
        	$data->numCnt += intval($itemCat->nro_cnt);
        }

        if (count($idsCats) == 0) {
        	$data->items = $itemsCats;
        	return $data;
        }
        
        $itemsArts = self::getItemsArtsFromCats($idsCats);
        if ($itemsArts === false) {
        	return false;
        }
        
        foreach ($itemsCats as $itemCat) {
        	$idCat = intval($itemCat->id);
        	$itemCat->arts = array();
        	foreach ($itemsArts as $itemArt) {
        		if ($idCat != intval($itemArt->catid)) {
        			continue;
        		}
        		
    			$itemCat->arts[] = $itemArt;
        	}
        	
        	$data->items[] = $itemCat;
        }
        
        return $data;
	}
	
	static function getItemsArtsFromCats($idsCats){
		if (is_array($idsCats)) {
        	$idsCats = implode(',', $idsCats);
		}
		if (!$idsCats) {
			$items = array();
			return $items;
		}
		
        $sql = "SELECT
  cnt.id, cnt.catid, cnt.title, cnt.introtext, cnt.fulltext,
  cnt.attribs, cnt.parentid
FROM
  jos_content cnt
WHERE
  cnt.state = 1 AND cnt.catid IN ($idsCats)
ORDER BY
  cnt.catid, cnt.parentid, cnt.ordering";
        
        $db = Exj::InstanceDatabase();
        
        $items = $db->loadObjectList($sql);
        if ($db->getErrorMsg()) {
        	return false;
        }
		
        return $items;
	}
}

?>