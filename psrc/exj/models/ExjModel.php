<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase base para modelo del componente. Los modelos de componente deben heredar de esta clase.
 * [componente] es el nombre del componente.
 * Nombrado del archivo: Debe tener el formato y ruta: com_sfi_[componente]/models/[componente].model.php
 * Nombrado de la clase: Debe tener el formato: class App[Componente]Model extends ExjModel
 * Nota: El archivo modelo del componente no es necesario incluirlo, este ya lo inclye el framewoRk EXJ.
 */
class ExjModel {

    public $id, $attributes;

    static function create($params) {
        $obj = new self(get_object_vars($params));
        $obj->save();
        return $obj;
    }

    static function find($id) {
        $db = Exj::InstanceDatabase();
        $found = null;
        /*
          foreach ($dbh->rs() as $rec) {
          if ($rec['id'] == $id) {
          $found = new self($rec);
          break;
          }
          }
         */
        return $found;
    }

    static function update($id, $params) {
        global $dbh;
        $rec = self::find($id);

        if ($rec == null) {
            return $rec;
        }
        $rs = $dbh->rs();

        foreach ($rs as $idx => $row) {
            if ($row['id'] == $id) {
                $rec->attributes = array_merge($rec->attributes, get_object_vars($params));
                $dbh->update($idx, $rec->attributes);
                break;
            }
        }
        return $rec;
    }

    public static function DestroyFromNameModelEditable($id, $nameModelEditable, &$response, $nameComponentEditable = '') {
        return self::destroy($id, '', $response, $nameComponentEditable, $nameModelEditable);
    }

    /**
     * Destruye o elimina un registro desde el modelo editable
     *
     * @param int $id
     * @param string $nameClassModelEditable
     * @param ExjResponse $response
     * @param string $nameComponentEditable
     * @param string $nameModelEditable
     * @return bool
     */
    public static function Destroy($id, $nameClassModelEditable, &$response, $nameComponentEditable = '', $nameModelEditable = '') {
        global $exj;
        if (!($response instanceof ExjResponse)) {
            Exj::SetErrorValidating("En el método " . __METHOD__ . " debe pasarse una instancia response de tipo ExjResponse");
            return false;
        }

        if (!$id) {
            $response->setMsgError("No se ha indicado Id", 'ERROR DELETING');
            return false;
        }

        if (!$nameModelEditable) {
            $nameModelEditable = ExjUtil::GetNameModelEditableFromNameClass($nameClassModelEditable);
        }
        if (!$nameClassModelEditable) {
            $nameClassModelEditable = ExjUtil::GetNameClassModelEditableFromName($nameModelEditable);
        }

        if (!$nameModelEditable) {
            $response->setMsgError('No se pudo determinar el nombre del modelo editable desde la clase: ' . $nameClassModelEditable);
            return false;
        }

        $editableModel = new $nameClassModelEditable(false);
        if (!($editableModel instanceof ExjEditableModel)) {
            $response->setMsgError("La clase: $nameClassModelEditable debe ser una instancia de: ExjEditableModel");
            return false;
        }

        return self::_destroyFromEditable($id, $editableModel, $response);
    }

    private static function _destroyFromEditable($id, ExjEditableModel &$modelEditable, ExjResponse &$response) {
        global $exj;

        $numDel = $modelEditable->destroy($id);
        if (!$modelEditable->isValid()) {
            Exj::SetErrorValidating($modelEditable->getBrokenRules());
            $response->setMsgError($modelEditable->getBrokenRules());
            return false;
        }

        $response->data = new stdClass();
        $response->data->nDeleted = $numDel;
        if ($numDel == 0) {
            $response->setMsgWarning("Eliminado.<br/>Ya a sido eliminado por otro usuario.", "ELIMINANDO REGISTRO...");
            return true;
        }

        $msgInfo = "<b>$numDel</b> registro a sido eliminado.";

        if ($modelEditable->haveChildsDeletes()) {
            $totalRowsChildsDeletes = $modelEditable->getTotalRowsChildsDeletes();
            if ($totalRowsChildsDeletes > 0) {
                $msgInfo .= "<br/><b>$totalRowsChildsDeletes</b> Registros relacionados han sido eliminados.";
            }

            $response->data->totalRowsChildsDeletes = $totalRowsChildsDeletes;
        }

        $response->setMsgInfo($msgInfo, 'REGISTRO ELIMINANDO...');

        return true;
    }

    static function all() {
        global $dbh;
        return $dbh->rs();
    }

    public function __construct($params) {
        $this->id = isset($params['id']) ? $params['id'] : null;
        $this->attributes = $params;
    }

    public function save() {
        global $dbh;
        $this->attributes['id'] = $dbh->pk();
        $dbh->insert($this->attributes);
    }

    public function to_hash() {
        return $this->attributes;
    }

    /**
     * Guarda los datos modificados al modelo editable
     *
     * @param ExjController $controller
     * @param int $id
     * @return ExjResponse
     */
    public static function SaveDataChangedToEditableModel(ExjController $controller, $id) {
        $response = new ExjResponse();

        if ($id <= 0) {
            if (!$controller->isValidParamsToCreate($response)) {
                return $response;
            }
        } else {
            if (!$controller->isValidParamsToUpdate($response)) {
                return $response;
            }
        }

        $component = $controller->getComponent();

        $ClassEditableModel = ExjUtil::GetClassModelEditableOfComponent(
            $msgError, $component
        );

        if ($msgError) {
            return $response->setMsgError($msgError, 'ERROR AL GUARDAR DATOS');
        }

        $dataChanged = $controller->getDataChanged();
        $paramsData = $controller->getParamsDataToObject();

        //print_r($dataChanged);
        // echo "<br>$ClassEditableModel id: $id";

        $instanceEditable = new $ClassEditableModel(false, $response);

        return self::_ProcessSaveEditable(
            $instanceEditable, $id, $dataChanged, $paramsData
        );
    }

    private static function _ProcessSaveEditable(ExjEditableModel $editable, $id, $dataChanged, $paramsData = null) {
        
        $editable->bind($dataChanged);

        // if ($editable->bind($dataChanged)) {
            if ($paramsData) {
                // print_r($paramsData);
                $editable->setParams($paramsData);
            }

            $editable->setValueId($id);
            $editable->save();
       // }

        return $editable->validateResponse();
    }

    /**
     * Valida si tiene acceso de eliminar
     *
     * @param ExjResponse $response
     * @param string $component
     * @param int $gid
     * @return mixed Si tiene acceso un object, sino false
     */
    public static function ValidateAccessDelete(ExjResponse $response, $component, $gid = 0) {
        $infoAccess = ExjData::GetInfoAccessACL($component, Exj::ACL_AXO_VALUE_TRASH, $gid);
        if ($infoAccess === false) {
            $response->setMsgError("Error al obtener información de acceso ACL");
            return false;
        }

        if ($infoAccess) {
            return $infoAccess;
        }

        $response->setMsgError("No tiene permiso de eliminar.");
        return false;
    }

}

?>