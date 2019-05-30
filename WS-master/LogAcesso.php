<?php
/**
 * Class responsável por gerar log de acesso
 * 
 * @author alberto
 */
class LogAcesso{
    function logAcesso($msg){
        // Abre ou cria o arquivo bloco1.txt
        // "a" representa que o arquivo é aberto para ser escrito
        $fp = fopen("./log.txt", "a");
        // Escreve a mensagem passada através da variável $msg
        $escreve = fwrite($fp, $msg);
        // Fecha o arquivo
        fclose($fp);
    }
}