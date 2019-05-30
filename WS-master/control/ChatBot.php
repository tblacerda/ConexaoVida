<?php

/**
 * Controlador que irá realizar as ações necessárias paras as mensagem do BOT
 *
 * @author Alberto Medeiros
 */
class ChatBot {
    
    /**
     * Irá conter o objeto  daoChatBot
     * 
     * @var daoChatBot  
     */
    private $daoChatBot;
    
    
    public function get_enviarMensagem(){
        return $this->getResposta();
    }
    
    public function post_enviarMensagem(){
        return $this->getResposta();
    }
    
    public function getResposta(){
        $arrRetorno = array();
        
        // Pegando a mensagem do usu�rio
        $strMensagem = (string) @$_POST["mensagem"];
        
        // Criando o dao
        $this->daoChatBot = new daoChatBot();
        
        // Irá recuperar as informa��es
        $arrRetorno = $this->daoChatBot->getOcorrencia($strMensagem);
        
//         echo '<pre>';
//         print_r($arrRetorno);
//         echo '</pre>';
//         die();
        // Realizando os
//         foreach($arrRetorno as $intChave => &$arrArrValor){
//             foreach($arrArrValor["arr"] as $intChaveInterno => &$arrValor){
//                 foreach($arrValor as $intChave => &$arrDados){
//                     $arrDados["titulo"] = utf8_encode($arrDados["titulo"]);
//                 }
//             }
//         }
        // retornando as opções de escolha do usuário
        return $arrRetorno;
    }
}