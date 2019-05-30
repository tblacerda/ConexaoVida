<?php

/******************************************************************************
 Nome do Arquivo   : ctrlMonitoraPedidos.php
 Descrição         : Classe especialista do controle do sistema
 Programador       : José Gabriel
 CRC               : 49682	
 Data              : 19/01/2015
 Diretório         : ./control/
 Alteração  : Nome - Data - numero crc
              # Descrição das alteração...
******************************************************************************/

class Release extends Controle{
    
    function get_releasesAbertos(){
        try{
            
        //Acesso ao ws
        $objJsonRetorno = $this->get("Release/releasesAbertos");
        
        //valida se a lista esta preenchida
        if(!empty($objJsonRetorno))
            return $objJsonRetorno;
        else
            return json_encode ("{Nenhum release econtrado.}");
        } catch (Exception $ex) {
            return json_encode ("{$ex->getMessage()}");
        }
    }
            
    function get_releasesEnviados(){
       try{
        //Acesso ao ws
        $objJsonRetorno = $this->get("Release/releasesEnviados");
        //valida se a lista esta preenchida
        if(!empty($objJsonRetorno))
            return $objJsonRetorno;
        else
            return json_encode ("{Nenhum release econtrado.}");
        } catch (Exception $ex) {
            return json_encode ("{Nenhum release econtrado.}");
        }
       
    }
    
    function get_releasesHoje(){
//        $this->releaseDao = new DaoRelease();
//        return $this->releaseDao->listarTodos();
        return null;
    }

    function post_novoRelease($request){
       try{
            
            //Acesso ao ws
            $objJsonRetorno = $this->postJson("Release/novoRelease", $request);
           
            //valida se a lista esta preenchida
            if(!empty($objJsonRetorno) && trim(strtolower($objJsonRetorno->result)) == true)
                return json_encode('{"post":true}');
            else
                return json_encode('{"post":false}');
        } catch (Exception $ex) {
            return json_encode("{$ex->getMessage()}");
        }
        
    }
        
}

?>