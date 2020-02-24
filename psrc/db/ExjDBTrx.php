<?php

// no direct access
defined('_JEXEC') or die('Acceso Restringido');

/**
 * Clase para manejo de Transacciones en la db
 *
 */
class ExjDBTrx {

    public static function Start() {
        return Exj::InstanceDatabase()->transactionStart();
    }

    public static function Commit() {
        return Exj::InstanceDatabase()->transactionCommit();
    }

    public static function Rollback($validateStartTransaction = false) {
        if ($validateStartTransaction) {
            if (!self::IsStartedTransaction()) {
                return;
            }
        }

        return Exj::InstanceDatabase()->transactionRollback();
    }

    public static function IsStartedTransaction() {
        return Exj::InstanceDatabase()->isStartedTransaction();
    }

}
?>