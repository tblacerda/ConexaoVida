<?php

/******************************************************************************
 Nome do Arquivo   : modelMonitoraPedidos.php
 Descrição         : Classe modelo do sistema de monitoração de pedidos
 Programador       : José Gabriel
 CRC               : 49682	
 Data              : 19/01/2015
 Diretório         : ./model/
 Alteração  : Nome - Data - numero crc
              # Descrição das alteração...
******************************************************************************/

class ModelRelease {
    
    private $id_release;
    private $id_cliente;
    private $id_sistema;
    private $tipo;
    private $branch;
    private $dat_pla_exp;

    function getId_release() {
        return $this->id_release;
    }

    function getId_cliente() {
        return $this->id_cliente;
    }

    function getId_sistema() {
        return $this->id_sistema;
    }

    function getTipo() {
        return $this->tipo;
    }

    function getBranch() {
        return $this->branch;
    }

    function getDat_pla_exp() {
        return $this->dat_pla_exp;
    }

    function setId_release($id_release) {
        $this->id_release = $id_release;
    }

    function setId_cliente($id_cliente) {
        $this->id_cliente = $id_cliente;
    }

    function setId_sistema($id_sistema) {
        $this->id_sistema = $id_sistema;
    }

    function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    function setBranch($branch) {
        $this->branch = $branch;
    }

    function setDat_pla_exp($dat_pla_exp) {
        $this->dat_pla_exp = $dat_pla_exp;
    }


}
