<?php


class RfProduto {

    function get_listall() {
        $releaseDao = new DaoRelease();
        
        return $releaseDao->listarTodos();
    }

}

?>