<?php
/**
 * Class respons�vel por gerar log de acesso
 * 
 * @author alberto
 */
class LogAcesso{
    function logAcesso($msg){
        // Abre ou cria o arquivo bloco1.txt
        // "a" representa que o arquivo � aberto para ser escrito
        $fp = fopen("./log.txt", "a");
        // Escreve a mensagem passada atrav�s da vari�vel $msg
        $escreve = fwrite($fp, $msg);
        // Fecha o arquivo
        fclose($fp);
    }
}