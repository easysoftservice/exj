<?php
defined('_JEXEC') or die('Acceso Restringido');

class ExjDataStore {
    const PATH_PROXY_ITEMS = 'DataTopics.topics';
    const PATH_PROXY_TOTAL ='DataTopics.total';

    public $xtype;
    public $autoDestroy = false;

    public function __construct($xtype = 'store') {
        $this->xtype = $xtype;
    }

    public function setAutoDestroy($value = true){
        $this->autoDestroy = ($value ? true:false);
        return $this;
    }

    /**
     * Envia datos al store
     *
     * @param array $data
     * @param string $root
     * @param bool $addItemToData
     * @return ExjDataStore
     */
    public function setData($data, $root = '', $addItemToData=false) {
        if (empty($data)) {
            $data = array();
        }

        if ($root) {
            $this->setRoot($root);
        }

        $this->rendererData($data);

        if ($addItemToData && count($data)==1) {
            $lastItems = $this->getItemsData();
            $newItem = $data[0];
            if (!empty($lastItems) && isset($newItem->value)) {
                foreach ($lastItems as $lastItem) {
                    if ($lastItem->value == $newItem->value) {
                        $addItemToData = false;
                        break;
                    }
                }

                if ($addItemToData) {
                    $data = array_merge($lastItems, $data);
                }
            }
        }

        if (isset($this->root) && $this->root && is_array($data)) {
            if ($this->root == self::PATH_PROXY_ITEMS) {
                $newData = new stdClass();
                $newData->DataTopics = new stdClass();
                $newData->DataTopics->topics = $data;
                if (isset($this->totalProperty)) {
                    $newData->DataTopics->total = count($data);
                }
                
                $this->data = $newData;
                return $this;
            }
        }

        $this->data = $data;

        return $this;
    }

    protected function rendererData($data) {
        $fields = $this->getFields();
        if (empty($fields) || empty($data) || !is_array($data)) {
            return $this;
        }

        $fieldsTypes = array();
        foreach ($fields as $field) {
            if (!is_object($field) || !isset($field->name) || !isset($field->type))
            {
                continue;
            }

            $name = $field->name;
            $fieldsTypes[$name] = $field;
        }

        if (empty($fieldsTypes)) {
            return $this;
        }

        foreach ($data as $item) {
            if (!is_object($item)) {
                continue;
            }

            foreach ($item as $prop => $value) {
                if (!isset($fieldsTypes[$prop])) {
                    continue;
                }

                $field = $fieldsTypes[$prop];
                $value = ExjDataField::RendererValue($value, $field->type);
                if ($value !== $item->$prop) {
                    $item->$prop = $value;
                    // echo "\n seteando $prop = $value";
                }
            }
        }

        return $this;
    }

    public function getData() {
        return (isset($this->data) ? $this->data : null);
    }

    public function getCount(){
        $items = $this->getItemsData();
        if (empty($items)) {
            return 0;
        }

        return count($items);
    }

    public function getItemsData() {
        $items = $this->getData();
        if (!$items) {
            return null;
        }

        if (is_array($items)) {
            return $items;
        }

        if (is_object($items)) {
            if (isset($items->DataTopics)) {
                if (isset($items->DataTopics->topics)) {
                    if (is_array($items->DataTopics->topics)) {
                        return $items->DataTopics->topics;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Envia campos al store
     *
     * @param array $fields
     * @return ExjDataStore
     */
    public function setFields($fields) {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Adiciona un campo al store
     *
     * @param ExjDataField|string $field
     * @return ExjDataStore
     */
    public function addField($field) {
        if (!$field) {
            return $this;
        }

        if (!is_object($field)) {
            if (!is_string($field)) {
                throw new Exception(
                    "Error addField. Parámetro debe ser ExjDataStore ó string", 1
                );
            }
        }
        elseif (!($field instanceof ExjDataField)) {
            throw new Exception(
                    "Error addField. Parámetro debe ser tipo ExjDataStore", 1
                );
        }

        if (!isset($this->fields)) {
            $this->fields = array();
            $this->fields[] = $field;
            return $this;
        }

        if ($fFound=$this->getField($name)) {
            if (!is_string($fFound)) {
                $fFound->apply($field);
            }
        }
        else{
            $this->fields[] = $field;
        }

        return $this;
    }

    public function getFields() {
        return (isset($this->fields) ? $this->fields : null);
    }

    public function getField($name, $defVal=null) {
        if (!isset($this->fields) || empty($this->fields)) {
            return $defVal;
        }

        foreach ($this->fields as $field) {
            if (is_string($field) && $field==$name) {
                $defVal = $field;
                break;
            }
            elseif($field->name == $name){
                $defVal = $field;
                break;
            }
        }

        return $defVal;
    }

    /**
     * Envia parámetros base al store
     *
     * @param object $baseParams
     * @return ExjDataStore
     */
    public function setBaseParams($baseParams) {
        if (is_array($baseParams)) {
            $baseParams = (object) $baseParams;
        }

        $this->baseParams = $baseParams;
        return $this;
    }

    public function applyBaseParams($params) {
        if (empty($params)) {
            return $this;
        }

        if (!isset($this->baseParams) || empty($this->baseParams)) {
            return $this->setBaseParams($params);
        }

        foreach ($params as $param => $value) {
            if (!$param) {
                continue;
            }

            $this->baseParams->$param = $value;
        }

        return $this;
    }

    /**
     * Envia un parámetro base al store
     *
     * @param string $name
     * @param mixed $value
     * @return ExjDataStore
     */
    public function setBaseParam($name, $value) {
        $name = trim($name);

        if (!isset($this->baseParams)) {
            $this->baseParams = new stdClass();
        }

        $this->baseParams->$name = $value;
        return $this;
    }

    public function getBaseParams() {
        return (isset($this->baseParams) ? $this->baseParams : null);
    }

    public function getBaseParam($param, $defVal=null) {
        if ($bp=$this->getBaseParams()) {
            if (isset($bp->$param)) {
                return $bp->$param;
            }
        }

        return $defVal;
    }

    

    /**
     * Envia url al store
     *
     * @param string $url
     * @return ExjDataStore
     */
    public function setUrl($url) {
        $this->url = trim($url);

        return $this;
    }

    public function getUrl(){
        return (isset($this->url) ? $this->url : '');
    }

    public function load($url=''){
        if(!$url){
            $url = $this->getUrl();
        }

        if (!$url) {
            return $this;
        }

        // echo "url: $url";
        $posParams = strpos($url, '?');
        $params = null;
        $uri = $url;
        if ($posParams !== false) {
            $uri = trim(substr($url, 0, $posParams));
            if (!$uri) {
                return $this;
            }

            $params = trim(substr($url, $posParams+1));
            $params = explode('&', $params);
           // echo "<br> uri: $uri params: " . print_r($params, true);
        }

        $partesUri = explode('/', $uri);
        if (count($partesUri) <= 2) {
            return $this;
        }

        $nameController = trim($partesUri[1]); 
        $nameMethod = trim($partesUri[2]);

        if (!$nameController || !$nameMethod) {
            return $this;
        }

        $ClassController = Exj::GetPrefixClassApp() . ucfirst($nameController).'Controller';
        // echo " ClassController: $ClassController nameMethod: $nameMethod";
        if (!class_exists($ClassController)) {
            throw new Exception("Cargando datos desde URL. No existe la clase: $ClassController", 1);
            
            return $this;
        }

        $instanceController = new $ClassController();
        if (!method_exists($instanceController, $nameMethod)) {
            throw new Exception("Cargando datos desde URL. Método: $nameMethod no implementado, en la clase: $ClassController", 1);
            return $this;
        }

        if (isset($this->baseParams) && $this->baseParams) {
            foreach ($this->baseParams as $key => $value) {
                if (!$params) {
                    $params = array();
                }

                if ($key) {
                    $params[] = $key."=".$value;
                }
            }
        }

        $lastValueRequest = false;
        $addItemToData = false;
        if (!empty($params)) {
            foreach ($params as $param) {
                $partesParam = explode('=', $param);
                if (count($partesParam) == 2) {
                    $keyParam = trim($partesParam[0]);
                    $valParam = $partesParam[1];
                    // echo "<br>param: $keyParam = $valParam";

                    if ($keyParam) {
                        if ($keyParam == 'value' && $valParam) {
                            $lastValueRequest = ExjRequest::GetParam('value', null);
                            ExjRequest::SetParam('value', $valParam);
                            $addItemToData = true;
                        }
                        else{
                            $instanceController->setParam($keyParam, $valParam);
                        }
                    }
                }
            }
        }

        $response = $instanceController->$nameMethod();

        if ($lastValueRequest !== false) {
            ExjRequest::SetParam('value', $lastValueRequest);
        }

        if (!$response || !($response instanceof ExjResponse)) {
            return $this;
        }

        if ($response->haveMsgError()) {
            throw new Exception('Carga desde URL. '. $response->getErrorMsg(), 1);
            return $this;   
        }

//        print_r($response);
        if ($response->haveDataTopics()) {
            $this->setData($response->getItemsDataTopics(), '', $addItemToData);
        }
        elseif ($response->haveData()) {
            $this->setData($response->data, '', $addItemToData);
        }

        return $this;
    }

    public function setIdProperty($value) {
        $this->idProperty = $value;
        return $this;
    }

    public function setRoot($root) {
        if ($root) {
            $root = trim($root);
        }

        $this->root = $root;
        return $this;
    }

    public function setTotalProperty($value) {
        $this->totalProperty = $value;
        return $this;
    }

    public function setterPathProxy() {
        return $this->setRoot(self::PATH_PROXY_ITEMS)
            ->setTotalProperty(self::PATH_PROXY_TOTAL);
    }

    public function setRemoteSort($value=true) {
        $this->remoteSort = $value;
        return $this;
    }

    public function isSetRemoteSort(){
        return (isset($this->remoteSort));
    }

    public function setUrlProxy($value){
        $this->urlProxy = $value;
        return $this;
    }

    public function findRow($fieldName, $value) {
        $row = null;
        if (!$fieldName) {
            return $row;
        }

        $items = $this->getItemsData();
        if (empty($items)) {
            return $row;
        }

        foreach ($items as $item) {
            /*
            if (!isset($item->$fieldName)) {
                continue;
            }
            */

            if ($item->$fieldName == $value) {
                $row = $item;
                break;
            }
        }

        return $row;
    }
}
?>