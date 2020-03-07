<?php

defined('_JEXEC') or die('Restricted access');

/**
 * class AppSysParametersData
 *
 */
class AppSysParametersData extends ExjData {

    /**
     * Lista de System Parameters
     *
     * @return array de object
     */
    static function loadListSysParams(&$items, &$total, $paramsCriteria = null) {
        global $exj;

        $dbQuery = new ExjDBQuery();

        $dbQuery->setFields("p.id_sys_param, p.code_param, p.name_param, p.type_param,
  p.value_param, u.name AS name_usr, p.modificado_dt,
  p.id_empresa");

        $dbQuery->setTables("jos_app_sys_parameters p LEFT JOIN
  jos_users u ON p.id_usuario_modifico = u.id");

        $dbQuery->addConditions("p.id_empresa = " . ExjUser::GetIdEmpresa());

        if ($paramsCriteria) {
            $criteriaSysParam = new AppSysParametersCriteriaModel(false);
            if ($criteriaSysParam->bind($paramsCriteria)) {

                $criteriaSysParam->addConditionsQuery($dbQuery);
            }
        }

        // $dbQuery->setOrdersFirst("p.modificado_dt");
        // $dbQuery->addOrders("p.type_param");

        /* -------LOAD PARAMS--------------------- */
        $total = $dbQuery->getCount("p.id_sys_param");
        //	$dbQuery->writeQueryExecuted();
        $items = $dbQuery->getRows();

        //     $dbQuery->writeQueryExecuted();

        return $dbQuery->isValid();
    }

    public static function GetRowFromCode($code_param, $fields='*'){
        $query = "SELECT $fields FROM jos_app_sys_parameters p WHERE ";
        $query .= "p.code_param = '$code_param' AND ";
        $query .= "p.id_empresa = ".ExjUser::GetIdEmpresa();

        $result = ExjDatabase::GetObjectFromQuery($query);
        if (empty($result)) {
          return $result;
        }

        if (isset($result->value_param) && isset($result->type_param) && $result->type_param) {
          $result->value_param = AppSysParametersHelper::ParseValue(
            $result->value_param, $result->type_param
          );
        }

        return $result;
    }

}

?>