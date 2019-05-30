<?php
/**
 * Includes necess�rios do Slim / MVC
 */
require_once(__DIR__ . '../../control/ChatBot.php');
require_once(__DIR__ . '/dao.php');


/**
 * Description of daoEmpreendimento
 *
 * @author Alberto Medeiros
 */
class daoChatBot extends Dao {

    /**
     * Contrutor padr�o da classe
     */
    function __construct() {
        parent::__construct();
    }
    
    /**
     * Irá recuperar as opções para o
     * 
     * @param String $strTexto
     * @throws Exception
     * @return boolean
     */
    function getOcorrencia($strTexto) {
        // Flag que ir� informar se existe uma ocorr�ncia igual a informada
        $arrRetorno = array();
        try {
            // Cast no valor para garantir a integridade
//             $this->sql = "SELECT titulo, opcao
//                          FROM OPCOES 
//                          WHERE nome = :strTexto";
//             // Realizando o bind da informação para que não haja SQL Inject
//             $this->bind("strTexto", $strTexto);
//             $this->prepare();
//             $this->executar();
//             $arrRetornoBanco = $this->buscarDoResultadoAssoc();
            $arrRetornoBanco = array();
            // Caso n�o tenha retorno
            if(empty($arrRetornoBanco)){
                // recuperando as opções padrões
                $arrRetornoBanco = $this->getOcorrenciasPadroes();
                // Setando a informação e as opções
                $arrRetorno["texto"] = "Olá {usuário}, não entendi o que pode você quer, favor selecionar uma das opções abaixo!";
                $arrRetorno["opcoes"] = $arrRetornoBanco;
            }else{
                foreach($arrRetornoBanco as $arrDados){
                    $arrRetorno[] = $arrDados;
                }
                $arrRetorno["texto"] = "Favor selecionar uma das opções abaixo!";
                $arrRetorno["opcoes"] = $arrRetornoBanco;
            }
        } catch (Exception $ex) { throw new Exception($ex->getMessage()); }
        // retornando o resultado
        return $arrRetorno;
    }
    
    /**
     * Ir� realizar a busca das ocorrencias padr�es
     * 
     * @return string[]
     */
    function getOcorrenciasPadroes(){
        // Array padr�o 
        $arrRetornoPadrao = array(
                                array(
                                    "arr" => array(
                                        array("titulo" => "Dúvidas", "opcao" => "duvida"), 
                                        array("titulo" => "Sintomas", "opcao" => "sintomas"), 
                                        array("titulo" => "Tratamentos", "opcao" => "tratamentos")
                                    )
                                ),
                                array(
                                    "arr" => array(
                                        array("titulo" => "Horários", "opcao" => "horario"), 
                                        array("titulo" => "Equipe Médica", "opcao" => "equipe_medica"), 
                                        array("titulo" => "Consultas" , "opcao" => "consultas")
                                    )
                                ),
                            );
        // Retornadno o array
        return $arrRetornoPadrao;
    }
    
}