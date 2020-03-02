<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class ExjClass extends ExjObject {

    public function haveError() {
        return Exj::GetError()->haveError();
    }

}
?>