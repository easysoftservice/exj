<?php

/**
 * Plantilla para correos
 *
 */
class AppMailTemplate {
    private $_variables = array();
    private $_pathToFile= array();
    
    public function __construct($pathToFile) {
         if(!file_exists($pathToFile)) {
             trigger_error('No existe el archivo', E_USER_ERROR);
             return;
         }
         $this->_pathToFile = $pathToFile;
    }

    public function setVar($key, $val) {
        $this->_variables[$key] = $val;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function compile() {
        ob_start();

        extract($this->_variables);
        include $this->_pathToFile;


        $content = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

?>