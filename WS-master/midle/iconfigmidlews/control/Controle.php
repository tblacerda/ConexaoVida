<?php
/******************************************************************************
 Nome do Arquivo   : controle.php
 Descrição         : Arquivo do controle do sistema
 Programador       : José Gabriel
 CRC               : 49682	
 Data              : 19/01/2015
 Diretório         : ./control/
 Alteração  : Nome - Data - numero crc
              # Descrição das alteração...
******************************************************************************/

include_once __DIR__.'../../model/CURL.php';

Class Controle{
    
    protected $objCURL = null;
    protected $strAssinatura  = "54WJUwm3c9K2Sv6ZEWpemfC1TZPlCJQH";
    protected $intSistema     = "2";

    /**
     * Construtor da classe
     */
    function __construct() {
        $this->objCURL = new CURL(300);
    }
    
    /**
     * Retorna o objeto cURL
     * @return object cURL
     */
    function getObjCURL() {
        return $this->objCURL;
    }

    /**
     * Caso tenha necessidade de criar um objeto com outros parametros
     * 
     * @param object cURL
     */
    function setObjCURL($objCURL) {
        $this->objCURL = $objCURL;
    }
    
    function getStrAssinatura() {
        return $this->strAssinatura;
    }

    function getIntSistema() {
        return $this->intSistema;
    }

    
    /**
     * Método que irá invocar / consumir o ws do imdlog atrávez de requisição GET podendo ou não passar paramentros pela url
     * mas como default sempre irá passar o sistema e a assinatura
     * 
     * @param string $strUrl
     * @param string $arrData
     * @param int $intRetries
     * @param boolean $bolIsAspX
     * @return array JSON
     * @throws Exception
     */
    public function get($strUrl, $arrData = array(), $intRetries = 1, $bolIsAspX = false){
        
        // Criando as entradas do sistema
        $arrData["assinatura"]  = $this->getStrAssinatura();
        $arrData["sistema"]     = $this->getIntSistema();
        
        try {            
            // realizando o get na url
            $strRetorno = $this->getObjCURL()->get($strUrl, $arrData, $intRetries, $bolIsAspX);
            
            // Caso algum erro aconteça lanço exceção
            if($strRetorno == false)
                throw new Exception($this->getObjCURL()->getErrorMessage());
            
        } catch (Exception $exc) {
            throw new Exception($exc);
        }
        
        // Caso não tenha erro retorno json com as informações
        return json_decode($strRetorno);
    }
    
    /**
     * Método que irá invocar / consumir o ws do imdlog atrávez de requisição POST precisando passar paramentros pela url
     * mas como default sempre irá passar o sistema e a assinatura
     * 
     * @param string $strUrl
     * @param string $arrData
     * @param int $intRetries
     * @param boolean $bolIsAspX
     * @return array JSON
     * @throws Exception
     */
    public function post($strUrl, $arrData = array(), $intRetries = 1, $bolIsAspX = false){
        
        // Criando as entradas do sistema
        $arrData["assinatura"]  = $this->getStrAssinatura();
        $arrData["sistema"]     = $this->getIntSistema();
        
        try {
            // realizando o get na url
            $strRetorno = $this->getObjCURL()->post($strUrl, $arrData, $intRetries, $bolIsAspX);
           
            // Caso algum erro aconteça lanço exceção
            if($strRetorno == false)
                throw new Exception($this->getObjCURL()->getErrorMessage());
        } catch (Exception $exc) {
            throw new Exception($exc);
        }
        
        // Caso não tenha erro retorno json com as informações
        return json_decode($strRetorno);
    }
    
    /**
     * Método que irá invocar / consumir o ws do imdlog atrávez de requisição POST precisando passar paramentros pela url
     * mas como default sempre irá passar o sistema e a assinatura
     * 
     * @param string $strUrl
     * @param string $arrData
     * @param int $intRetries
     * @param boolean $bolIsAspX
     * @return array JSON
     * @throws Exception
     */
    public function postJson($strUrl, $arrData = array(), $intRetries = 1, $bolIsAspX = false){
        
        // Criando as entradas do sistema
//        $arrData["assinatura"]  = $this->getStrAssinatura();
//        $arrData["sistema"]     = $this->getIntSistema();
        
        try {
            // Reailzando o parse pra json
            $strData = json_encode($arrData);
            // realizando o get na url
            $strRetorno = $this->getObjCURL()->post($strUrl, $strData, $intRetries, $bolIsAspX);
            
            // Caso algum erro aconteça lanço exceção
            if($strRetorno == false)
                throw new Exception($this->getObjCURL()->getErrorMessage());
        } catch (Exception $exc) {
            throw new Exception($exc);
        }
        
        // Caso não tenha erro retorno json com as informações
        return json_decode($strRetorno);
    }


    
}

?>