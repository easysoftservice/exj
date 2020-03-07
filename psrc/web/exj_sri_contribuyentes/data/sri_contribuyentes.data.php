<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );
	
/**
 * Datos de Contribuyentes SRI
 *
 */
class AppSriContribuyentesData extends ExjData {
	
	/**
	 * Carga lista principal de Contribuyentes SRI
	 *
	 * @param ExjResponse $response
	 * @param array $items
	 * @param int $total
	 * @param object $paramsCriteria
	 * @return bool Si ocurre un error false sino true
	 */
	public static function CargarListaPrincipal(ExjResponse $response, &$items, &$total, $paramsCriteria=null){
        $dbQuery = new ExjDBQuery();
      //  $dbQuery->autoAddLastChange('sc');
        
        $dbQuery->setFields("sc.id_contribuyente, 
 				 sc.id_act_eco, sc.numero_ruc, 
 				 sc.razon_social, sc.nombre_comercial, 
 				 sc.estado_contribuyente, sc.clase_contribuyente, 
 				 sc.fecha_inicio_actividades, sc.fecha_actualizacion, 
 				 sc.fecha_suspension_definitiva, sc.fecha_reinicio_actividades, 
 				 sc.obligado, sc.tipo_contribuyente, 
 				 sc.numero_establecimiento, sc.nombre_fantasia_comercial, 
 				 sc.calle, sc.numero, 
 				 sc.interseccion, sc.estado_establecimiento, 
 				 sc.descripcion_provincia, sc.descripcion_canton, 
 				 sc.descripcion_parroquia");
        
        $dbQuery->setTables("repositorio.sri_contribuyentes sc");
        
        if ($paramsCriteria) {
			// Exj::IncludeClass('AppSriContribuyentesCriteriaModel');
			$criteria = new AppSriContribuyentesCriteriaModel(false);
			if ($criteria->bind($paramsCriteria)) {
				$criteria->addConditionsQuery($dbQuery);
			}
        }
        
  		/* -------LOAD PARAMS--------------------- */
		$total = $dbQuery->getCount("sc.id_contribuyente");
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
		// $dbQuery->writeQueryExecuted();
        
        return true;
	}
	
	/**
	 * Lookup de Contribuyentes SRI
	 *
	 * @param array $items
	 * @return bool Si ocurren errores false sino true
	 */
	public static function LoadLookupSriContribuyentes(&$items){
        $dbQuery = new ExjDBQuery();
        
        $dbQuery->setFields("sc.id_contribuyente AS value, sc.id_act_eco, sc.numero_ruc AS text, sc.razon_social, 
 				 sc.nombre_comercial, sc.estado_contribuyente, sc.clase_contribuyente, sc.fecha_inicio_actividades, 
 				 sc.fecha_actualizacion, sc.fecha_suspension_definitiva, sc.fecha_reinicio_actividades, sc.obligado, 
 				 sc.tipo_contribuyente, sc.numero_establecimiento, sc.nombre_fantasia_comercial, sc.calle, 
 				 sc.numero, sc.interseccion, sc.estado_establecimiento, sc.descripcion_provincia, 
 				 sc.descripcion_canton, sc.descripcion_parroquia");
        
        $dbQuery->setTables("repositorio.sri_contribuyentes sc");
        
        $dbQuery->addOrders("sc.numero_ruc");

  		$dbQuery->withOutPaging();
		$items = $dbQuery->getRows();
		
		if (!$dbQuery->isValid()) {
			return false;
		}
		
    //   $dbQuery->writeQueryExecuted();
        
        return true;
	}

    /**
     * @param string $numero_ruc Ruc ó Cédula
     * @return bool|null|object
     */
    public static function FindByNumDoc($numero_ruc) {
        $numero_ruc = trim($numero_ruc);
        if(!$numero_ruc){
            return null;
        }
        $nCars = strlen($numero_ruc);
        if($nCars > 13){
            return null;
        }

        $query = "SELECT c.* FROM repositorio.sri_contribuyentes c WHERE ";
        if($nCars == 13){
            $query .= "c.numero_ruc = '$numero_ruc'";
        }
        else{
            $query .= "c.numero_ruc LIKE '$numero_ruc%'";
        }

        return ExjDatabase::GetObjectFromQuery($query);
    }

    /**
     * @param array $numerosRucs
     * @return array
     */
    public static function GetInfoFromRUCS($numerosRucs) {
        $rucs = array();
        foreach ($numerosRucs as $numeroRuc) {
            $rucs[] = "'$numeroRuc'";
        }
        if(count($rucs) == 0){
            return array();
        }

        $rucs = implode(',', $rucs);
        $query = "SELECT 
  c.numero_ruc, 
  c.descripcion_parroquia,
  IF(c.nombre_comercial IS NULL, c.nombre_fantasia_comercial, c.nombre_comercial) AS nom_comercial 
 FROM repositorio.sri_contribuyentes c 
 WHERE c.numero_ruc IN ($rucs)";

        return ExjDatabase::GetObjectList($query);
    }
    
    public static function GetItemRUCSri($numero_ruc, $tipo_contribuyente=''){
    	$numero_ruc = trim(str_replace('-', '', $numero_ruc));
    	$nCars = strlen($numero_ruc);
    	
    	if ($nCars < 10 || $nCars > 13) {
    		return null;
    	}
    	
    	if ($nCars != 13) {
    		if ($nCars > 10) {
    			$numero_ruc = substr($numero_ruc, 0, 10);
    		}
    		
    		$numero_ruc .= '001';
    	}
    	
    	$filters = array();
    	$filters[] = "c.numero_ruc = '$numero_ruc'";
    	if ($tipo_contribuyente) {
    		$filters[] = "c.tipo_contribuyente = '$tipo_contribuyente'";
    	}
    	$filters = implode(' AND ', $filters);
    	
    	$query = "SELECT 
		  c.numero_ruc, c.razon_social, c.tipo_contribuyente, c.calle,
		  c.numero, c.interseccion, c.descripcion_provincia,
		  c.descripcion_canton, c.descripcion_parroquia 
		 FROM repositorio.sri_contribuyentes c 
		 WHERE ($filters)";
    	
    	return ExjDatabase::GetObjectFromQuery($query);
    }
    
    public static function GetStrDomicilio($calle, $numero, $interseccion, $descripcion_provincia, $descripcion_canton, $descripcion_parroquia){
    	$info = array();
    	
    	$calle = self::_TrimStr($calle);
    	if ($calle) {
    		$info[] = "$calle";
    		$numero = self::_TrimStr($numero);
    		if ($numero) {
    			if (is_nan(intval($numero))) {
    				$numero = str_replace('CASA', 'casa', $numero);
    				$numero = str_replace('LOTE', 'lore', $numero);
    			}
                /*
    			else{
    				$numero = 'casa número '.$numero;
    			}
                */
    			$info[] = "$numero";
    		}
    		
    		$interseccion = self::_TrimStr($interseccion);
    		if ($interseccion) {
    			$info[] = "Y $interseccion";
    		}
    	}

        if ($descripcion_canton != 'LOJA') {
            if (empty($info)) {
                $info[] = "PARROQUIA $descripcion_parroquia,";
                $info[] = "CANTON $descripcion_canton,";
                $info[] = "PROVINCIA $descripcion_provincia";
            }
            else {
                $info[] = "CANTON $descripcion_canton";
            }
        }
    	
    	$info = trim(implode(' ', $info));
    	return $info;
    }
    
    private static function _TrimStr($str){
    	if ($str == null) {
    		$str = '';
    	}
    	$str = trim($str);
    	if (!$str) {
    		return $str;
    	}
    	
    	if (stripos($str, 'S/N') !== false || $str == 'SN' || $str == '.SN') {
    		$str = '';
    	}
    	
    	return $str;
    }

}

?>