<?php
session_start();
ini_set("session.cookie_lifetime","3600");

/**
 * Verificação de acesso
 */
if(!isset($_SESSION["usuario"])){
    try {
        if(isset($_GET["usuario"]) && isset($_GET["senha"])){
            if($_GET["usuario"] == "utilizador" && $_GET["senha"] = "pwdws$123xy"){
                $_SESSION["usuario"]    = "utilizador";
                $_SESSION["acesso"]     = true;
            }else throw new Exception("Erro na autenticação, favor verificar usuário e senha!");
        }else{
            throw new Exception("Você não tem permissão de acesso de acesso!");
        }
    } catch (Exception $e) {
        // this session has worn out its welcome; kill it and start a brand new one
        session_unset();
        session_destroy();
        $arrMensagem = array("result" => array("mensagem"=>htmlentities($e->getMessage())));
        echo json_encode($arrMensagem);
        die;
    }
}

ini_set('memory_limit', '1024M');
date_default_timezone_set('America/Sao_Paulo');
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
//     include_once "control/{$controller}.php";
//     $classe = new $controller();
//     $retorno = call_user_func_array(array($classe, "get_" . $action), array($parameter));
//     echo '{"result":' . json_encode($retorno) . '}';
    // Executando o retorno GET
    getExecucao(false, $controller, $action, $parameter);
});

//POST nÃ£o possui parÃ¢metros na URL, e sim na requisiÃ§Ã£o

$app->post('/:controller/:action', function ($controller, $action) use ($app) {
//             $request = json_decode(\Slim\Slim::getInstance()->request()->getBody());           
//             include_once "control/{$controller}.php";
//             $classe = new $controller();
//             $retorno = call_user_func_array(array($classe, "post_" . $action), array($request));
//              echo '{"result":' . json_encode($retorno) . '}';       
     // Executando o retorno POST
     getExecucao(true, $controller, $action);
});

$app->run();

/**
 * Método de padronização do retorno 
 */
function getExecucao($bolPost = false, $controller, $action, $parameter = null){
    $arrDadosRetorno = array();
    $bolRetorno = false;
    $strMensagem = "";
    try {
        // Caso seja um post
        if($bolPost){
            // Instanciando as libs
            $request = json_decode(\Slim\Slim::getInstance()->request()->getBody());
            // instanciando o controlador
            include_once "control/{$controller}.php";
            $classe = new $controller();
            // Executando o método
            $arrDadosRetorno = call_user_func_array(array($classe, "post_" . $action), array($request));
        }else{
            // Instanciando as libs
            include_once "control/{$controller}.php";
            $classe = new $controller();
            // Executando o método
            $arrDadosRetorno = call_user_func_array(array($classe, "get_" . $action), array($parameter));
        }
        $bolRetorno = true;
    } catch (Exception $e) {
        // Retornando a mensagem de erro
        $strMensagem = $e->getMessage();
    }
    // Criando o retorno
    $arrRetorno = array();
    $arrRetorno["result"] = $arrDadosRetorno;
    $arrRetorno["bolRetorno"] = $bolRetorno;
    $arrRetorno["strMensagem"] = $strMensagem;
    
    echo json_encode($arrRetorno);
}