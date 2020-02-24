<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjClass extends ExjObject {

    public function haveError() {
        global $exj;
        return $exj->haveError();
    }


}
?>