<?php

// includes necess�rios
include_once("./util/Conexao.php");
/**
 * Configura��es do BOT
 * 
 * @var unknown
 */

define('BOT_TOKEN', '757419476:AAGjRv3QhGYM08ma_lpQb6SyBAF7epuJ320');
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');
// Op��es de intera��es
$keyboard = array(array("Logs","Usuarios"),array("Agendamento", "Exames"), array("Notificacoes"));

$arrIds = array("-295555739", "187603566");

/**
 * M�todo que ir� realizar as instera��es com o bot do telegram
 * 
 * @param array $message
 * @param array $arrResultadoView
 * @param array $keyboard
 */
function processMessage($message, $arrResultadoView, $keyboard, $arrIds)
{
    // processa a mensagem recebida
    $message_id = $message['message_id'];
    $chat_id = $message['chat']['id'];
    
    // Caso n�o seja um dos ids permitidos
    if(!in_array($chat_id, $arrIds)) {
        $strMensagem = 'Ol�,  galera da Infinity! Esse cara ->' . $message['from']['first_name'] . ', t� querendo usar o BOT, voc�s querem permitir? Seu canal �: ' . $chat_id;
        // envia a mensagem ao GRUPO
        sendMessage("sendMessage", array(
            'chat_id' => "-295555739",
            "text" => $strMensagem
        ));
        
        // envia a mensagem p/ Alberto
        sendMessage("sendMessage", array(
            'chat_id' => "187603566",
            "text" => $strMensagem
        ));
        die;
    }
    // Caso o texto tenha sido definido
    if (isset($message['text'])) {

        $text = $message['text']; // texto recebido na mensagem
        if (strpos($text, "/start") === 0) {
            // envia a mensagem ao usu�rio
            sendMessage("sendMessage", array(
                'chat_id' => $chat_id,
                "text" => 'Ol�, ' . $message['from']['first_name'] . '! Escolha uma das op��es a seguir! '
            ));
            
            $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
            $reply = json_encode($resp);
            // Criando as op��es
            $obj = new stdClass();
            $obj->keyboard = $keyboard;
            $obj->resize_keyboard = true;
            $obj->one_time_keyboard = true;

            sendMessage("sendMessage",
                array(
                    'chat_id' => $chat_id,
                    "text" => 'Selecione:',
                    'reply_markup' => $obj
                )
                );

        } else if(in_array($message['text'], array(
            'Logs',
            'Usuarios',
            'Agendamento',
            'Notificacoes',
            'Exames'))){
            
            $strTexto = "";
            switch ($message['text']){
                case 'Logs':
                    $strTexto = 'Logs do sistema: https://conexaovidaimip1.websiteseguro.com/prod/log.txt';
                    break;
                case 'Usuarios' :
                    $strTexto = 'Usuarios do Sistema: ' . getUsuarios();
                    break;
                case  'Agendamento':
                    $strTexto = 'Total De Agendamentos Cadastrados: ' . getAgendamentos();
                    break;
                case 'Exames':
                    $strTexto = 'Total De Exames Cadastrados: ' . getTotalExames();
                    break;
                case 'Notificacoes':
                    $strTexto = 'Total De Notifica��es Cadastradas: ' . getTotalNotificacoes();
                    break;
            }
            // enviando a mensagem
            sendMessage("sendMessage",
                array(
                    'chat_id' => $chat_id,
                    "text" => $strTexto
                )
                );

        } else {
            // Op��es de intera��es
            $resp = array("keyboard" => $keyboard,"resize_keyboard" => true,"one_time_keyboard" => true);
            $reply = json_encode($resp);
            // Criando as op��es
            $obj = new stdClass();
            $obj->keyboard = $keyboard;
            $obj->resize_keyboard = true;
            $obj->one_time_keyboard = true;
            sendMessage("sendMessage",
                array(
                    'chat_id' => $chat_id,
                    "text" => 'Ol�, '. $message['from']['first_name']. '! Desculpe, n�o entendi, escolha uma das op��es abaixo e nos informe o que voc� deseja?',
                    'reply_markup' => $obj
                )
                );
        }
    }else{
        // envia a mensagem ao usu�rio
        sendMessage("sendMessage", array(
            'chat_id' => $chat_id,
            "text" => 'Ol�, ' . json_encode($message)
        ));
    }
}

// Envio da mensagem
function sendMessage($method, $parameters)
{
    foreach($parameters as $strChave => $strConteudo){
        if($strChave == "reply_markup") {
            $parameters[$strChave] = json_encode($strConteudo);
            continue;
        }
        $parameters[$strChave] = (!is_numeric($strConteudo)) ? utf8_encode($strConteudo) : $strConteudo;
    }
    
    // Recuperando as mensagens
    $curl = curl_init(API_URL . $method);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $update_response = curl_exec($curl);
    curl_close($curl);

}

// M�todo que ir� retornar a conex�o 
function getExecuteQuery($strQuery){
    
    $objConexao = Conexao::getInstanceMysql();
    $objSTTM = $objConexao->prepare($strQuery);
    $objSTTM->execute();
    return $objSTTM->fetch(PDO::FETCH_ASSOC);
}



function getAgendamentos(){
    $strSql = "SELECT count(id) total FROM usuario";
    
    $arrRetorno = getExecuteQuery($strSql);
    
//     return $arrRetorno["total"];
    return " (N�o implementado)";
}

function getTotalExames(){
    $strSql = "SELECT count(id) total FROM exame";
    
    $arrRetorno = getExecuteQuery($strSql);
    
    return $arrRetorno["total"];
}

function getTotalNotificacoes(){
    $strSql = "SELECT count(id) total FROM notificacao";
    
    $arrRetorno = getExecuteQuery($strSql);
    
    return $arrRetorno["total"];
}

function getErros(){
    return " (N�o Implementado)";
}

function getUsuarios(){
    $strSql = "SELECT count(id) total FROM usuario";
    
    $arrRetorno = getExecuteQuery($strSql);
    
    return $arrRetorno["total"];
}

/*Com o webhook setado, n�o precisamos mais obter as mensagens atrav�s do m�todo getUpdates.Em vez disso,
 * como o este arquivo ser� chamado automaticamente quando o bot receber uma mensagem, utilizamos "php://input"
 * para obter o conte�do da �ltima mensagem enviada ao bot.
 */
$update_response = file_get_contents("php://input");
$update = json_decode($update_response, true);

// Caso tenha mensagem
if (isset($update["message"])) {
    processMessage($update["message"], array(), $keyboard, $arrIds);
}