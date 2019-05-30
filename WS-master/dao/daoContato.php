<?php
/**
 * Includes necess�rios do Slim / MVC
 */
require_once(__DIR__ . '../../control/Contato.php');
require_once(__DIR__ . '/dao.php');

class daoContato extends Dao {

    /**
     * Contrutor padr�o da classe
     */
    function __construct() {
        parent::__construct();
    }
}