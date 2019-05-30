<?php
session_name('SESSAO_PHP');
session_start();
error_reporting(E_ALL);
ini_set("session.cookie_lifetime","3600");
ini_set('display_errors', 'On');

header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");

// Incluindo as libs de emails
include './util/mailer/PHPMailerAutoload.php';

ini_set('memory_limit', '1024M');
date_default_timezone_set('America/Recife');
require __DIR__.'/control/controle.php';


require 'Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim(array(
    'debug' => true
        ));

$app->contentType("application/json");

$app->error(function ( Exception $e = null) use ($app) {
         echo '{"error":{"text":"'. $e->getMessage() .'"}}';
        });

//GET pode possuir um parametro na URL
$app->get('/:controller/:action(/:parameter)', function ($controller, $action, $parameter = null) use($app) {
    getExecucao(false, $controller, $action, $parameter);
});

//POST n�o possui par�metros na URL, e sim na requisi��o

$app->post('/:controller/:action', function ($controller, $action) use ($app) {
    getExecucao(true, $controller, $action);
});

$app->run();

/**
 * M�todo de padroniza��o do retorno
 */
function getExecucao($bolPost = false, $controller, $action, $parameter = null){
    $arrDadosRetorno    = array();
    $bolRetorno         = false;
    $strMensagem        = "";
    $parametros         = $parameter;
    
    try {
        // Caso seja um post
        if($bolPost){
            // Instanciando as libs
            $request = json_decode(\Slim\Slim::getInstance()->request()->getBody());
            // instanciando o controlador
            include_once "./control/".ucfirst($controller).".php";
            $classe = new $controller();
            // Executando o m�todo
            $arrDadosRetorno = call_user_func_array(array($classe, "post_" . $action), array($request));
        }else{
            // Instanciando as libs
            include_once "./control/".ucfirst($controller).".php";
            $classe = new $controller();
            // Executando o m�todo
            $arrDadosRetorno = call_user_func_array(array($classe, "get_" . $action), array($parameter));
        }
        $bolRetorno = true;
    } catch (Exception $e) {
        // Se o erro for severo ou que deve ser enviado para analise
        if(in_array($e->getCode(), array(9999, 8, 2))){
            // Criando o retorno
            $strMensagem = "Controlador: {$controller} - Ação: {$action} ";
            if($bolPost) $strMensagem .= " Dados POST: " . json_encode($_POST);
            else $strMensagem .= " Dados GET: " . json_encode($_GET);
            $strMensagem .= " Dados Retorno: " . json_encode($arrDadosRetorno);
            $strMensagem .= " Mensagem: " . $e->getMessage();
            $strMensagem .= " Caminho: " . $e->getTraceAsString();
            // Incluindo o bot
            include_once './bot.php';
            // enviando a mensagem
            sendMessage("sendMessage",
                array(
                    'chat_id' => '-295555739',
                    "text" => $strMensagem
                )
            );
        }
        // Retornando a mensagem de erro
        $strMensagem = $e->getMessage();// . $e->getTraceAsString();
    }
    // Criando o retorno
    $arrRetorno = array();
    $arrRetorno["result"] = $arrDadosRetorno;
    $arrRetorno["bolRetorno"] = $bolRetorno;
    $arrRetorno["strMensagem"] = $strMensagem;
    // Gerando Log
    gerarLog($arrRetorno, $parametros);
    // Retornando 
    echo json_encode($arrRetorno);
}
/**
 * Gerando log
 * @param unknown $arrRetorno
 * @param unknown $parametros
 */
function gerarLog($arrRetorno, $parametros){
    // Logando parametros
    $arrPost        = $_POST;
    $strParametros = "";
    $strData        = date("d/m/Y H:i:s");
    $strParametros     .= " - Dados: " . json_encode($arrPost);
    $strParametros     .= " - Retorno: " . json_encode($arrRetorno);
    $strMensagem    = "Data: {$strData} - {$strParametros} \r\n";
    // Abre ou cria o arquivo bloco1.txt
    // "a" representa que o arquivo � aberto para ser escrito
    $fp = @fopen("./log.txt", "a+");
    // Escreve a mensagem passada atrav�s da vari�vel $msg
    $escreve = @fwrite($fp, $strMensagem);
    // Fecha o arquivo
    @fclose($fp);
}