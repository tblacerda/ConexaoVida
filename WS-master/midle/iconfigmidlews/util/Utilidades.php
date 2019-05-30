<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Util
 *
 * @author gabriel.novais
 */
class Utilidades {
    
    function tiraMoeda($valor) {
        $pontos = array("_", ".");
        $result = str_replace($pontos, "", $valor);

        return $result;
    }

}
