<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Crc
 *
 * @author gabriel.novais
 */



class Crc extends Controle{
    
    
    function get_crcRelease($release){
        try{

            //Acesso ao ws
            $objJsonRetorno = $this->get("Crc/crcRelease/$release");

            //valida se a lista esta preenchida
            if(!empty($objJsonRetorno))
                return $objJsonRetorno;
            else
                return json_encode ("{Nenhuma CRC econtrada.}");
            } catch (Exception $ex) {
                return json_encode ("{$ex->getMessage()}");
        }
    }   
    
    
    function get_resumoChamados(){
         try{

            //Acesso ao ws
            $objJsonRetorno = $this->get("Crc/resumoChamados");

            //valida se a lista esta preenchida
            if(!empty($objJsonRetorno))
                return $objJsonRetorno;
            else
                return json_encode ("{Nenhuma CRC econtrada.}");
            } catch (Exception $ex) {
                return json_encode ("{$ex->getMessage()}");
        }
    }
}
