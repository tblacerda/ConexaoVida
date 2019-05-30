<?php
/**
 * Controlador Notificação
 *
 * @author Alberto Medeiros
 */
class Notificacao {
    
    /**
     * Ir� conter o objeto  daoNotificacao
     *
     * @var daoNotificacao
     */
    private $objDaoNotificacao;
    
    /**
     * 
     * @return mixed
     */
    public function get_listaCancer(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // 
        return $this->objDaoNotificacao->listaCancer();
    }
    
    /**
     * 
     * @return ArrayObject
     */
    public function get_listaPerfis(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        
        return $this->objDaoNotificacao->listaPerfil();
    }
    
    /**
     * M�todo que ir� retornar o total de usu�rios do filtro 
     * 
     * @throws Exception
     * @return mixed
     */
    public function post_filtrarTotalUsuariosEnvio(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_POST["dadosNotificacao"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados da notificação
        $objNotificacao = json_decode($_POST["dadosNotificacao"]);
        // Recuperando o usu�rios que ser�o enviados
        $arrTotal = $this->objDaoNotificacao->getUsuariosEnviosFiltro((array) $objNotificacao, true);
        // Retornando o total de usu�rios
        return $arrTotal;
    }
    
    /**
     * M�todo que ir� realizar a valida��o e o cadastro das notificações
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarNotificacao(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_POST["dadosNotificacao"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados da notificação
        $objNotificacao = json_decode($_POST["dadosNotificacao"]);  
        // Validando os dados postados
        $this->validarCadastro($objNotificacao); 
        // Recuperando o usu�rios que ser�o enviados 
        $arrUsuarios = $this->objDaoNotificacao->getUsuariosEnviosFiltro((array) $objNotificacao, false);
        // Formatando o filtro que foi usado
        $objNotificacao->filtro = json_encode($objNotificacao);
        // // cadastrando de notificação na base
        $bolCadastro = $this->objDaoNotificacao->cadastrarNotificacao($objNotificacao, $arrUsuarios);
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar a notificação!");
        // Ids Usuario
        $arrIds = array();
        // Formatando os ids para envio em massa
        foreach($arrUsuarios as $arrValor){
            if(!empty($arrValor["codigo_onesignal"]) && $arrValor["codigo_onesignal"] != "" && $arrValor["codigo_onesignal"] != "undefined")
            $arrIds[] = $arrValor["codigo_onesignal"];
        }
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => $objNotificacao->titulo),
            'contents' => array("en" => substr($objNotificacao->corpo, 0, 250))
        );
        if(isset($objNotificacao->notificacao_criacao_id) && !empty($objNotificacao->notificacao_criacao_id)){
            $arrDadosNotificacao['data'] = array(
                                            "foo"=>"bar",
                                            "acao"=> ($objNotificacao->envio_paciente == 1) 
                                                     // Caso seja uma notificação de um paciente para equipemérica 
                                                     ? Constantes::$ULR_THREAD_NOTIFICAAO_EQUIPE_MEDICA 
                                                    //  Caso seja uma notificação da equipemérica para um paciente
                                                     : Constantes::$ULR_THREAD_NOTIFICAAO_PACIENTE,
                                            "parametros"=>array("notificacaoId"=>$objNotificacao->notificacao_criacao_id)
                                        );
        }else{
            $arrDadosNotificacao['data'] = array(
                "foo"=>"bar",
                "acao"=> Constantes::$ULR_THREAD_MINHAS_NOTIFICACOES
            );
        }
        
        if(isset($objNotificacao->envio_paciente) && $objNotificacao->envio_paciente == 1 &&
            (!isset($objNotificacao->notificacao_criacao_id) || empty($objNotificacao->notificacao_criacao_id))){
                $arrDadosNotificacao['data'] = array(
                    "foo"=>"bar",
                    "acao"=> ($objNotificacao->envio_paciente == 1)
                    // Caso seja uma notificação de um paciente para equipemérica
                    ? Constantes::$ULR_THREAD_NOTIFICAAO_EQUIPE_MEDICA
                    //  Caso seja uma notificação da equipemérica para um paciente
                    : Constantes::$ULR_THREAD_NOTIFICAAO_PACIENTE,
                    "parametros"=>array("notificacaoId"=>$objNotificacao->id)
                );
        }
        
        
        // Enviando a notificação
        $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Retornando sucesso
        return $objRerotno;
    }
    
    /**
     * M�todo que ir� validar os dados de cadastro do Exame
     * 
     * @param Object $objExame
     * @throws Exception
     */
    function validarCadastro(stdClass $objNotificacao){
        // Valida��o dos dados de exame
        if(!empty($objNotificacao->pep) && strlen($objNotificacao->pep) != 7)        throw new Exception("Número de pep Inválido!");
        if(empty($objNotificacao->titulo))      throw new Exception("Título da notificação não informado!");
        if(empty($objNotificacao->corpo))       throw new Exception("Corpo da notificação não informado!"); 
        if(strlen($objNotificacao->corpo) > 500)throw new Exception("Corpo da notificação maior que 250 caracteres!");
    } 
    
    /**
     * M�todo que ir� retornar os exames pelo id do paciente (usu�rio)
     * 
     * @throws Exception
     * @return mixed
     */
    public function get_notificacoesDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Valida��es
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrExames = $this->objDaoNotificacao->listarNotificacoesDoPaciente($intIdUsuario);
        if(empty($arrExames)) throw new Exception("Exames não foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrExames;
    }
    
    /**
     * M�todo que ir� retornar os exames pelo id do paciente (usu�rio)
     *
     * @throws Exception
     * @return mixed
     */
    public function post_notificacoesLidas(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        $intIdNotificacao = (int) @$_GET["intIdNotificacao"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        $this->objDaoNotificacao->notificacoesLidas($intIdUsuario, $intIdNotificacao);
        return true;
    }
    
    /**
     * Método que irá listar as notificações da base
     * 
     * @throws Exception
     */
    public function post_filtrarNotificacoes(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();        
         // Validando os filtros
         if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
         // Recuperando os filtros
        $arrFiltro = json_decode($_POST["filtroBusca"]);
        // Buscando as notificações com os filtros recuperados
        $arrNotificacoes = $this->objDaoNotificacao->filtrarNotificacoes((array) $arrFiltro);
        if(empty($arrNotificacoes)) throw new Exception("Nenhuma Notificação Encontrada!");
        // formatando as notificações
        foreach($arrNotificacoes as $inChave => $arrNotificacao){
            $arrNotificacao["filtro"] = (array) json_decode($arrNotificacao["filtro"]);
            // Formatando o retorno
            $arrNotificacao["titulo"]        = "<a class='links link '  href='".Constantes::$ULR_DETALHE_NOTIFICACAO.$arrNotificacao["id"]."'>".$arrNotificacao["titulo"]."</a>";
            $arrNotificacao["data_envio"]    = "<a class='links link '  href='".Constantes::$ULR_DETALHE_NOTIFICACAO.$arrNotificacao["id"]."'><span class='esconder-informacao'>".$arrNotificacao["data_envio"]."</span> " . Utilidades::formatarDataPraBr($arrNotificacao["data_envio"], "Y-m-d H:i:s")."</a>";
            $arrNotificacao["total"]         = "<a class='links link '  href='".Constantes::$ULR_DETALHE_NOTIFICACAO.$arrNotificacao["id"]."'>".@$arrNotificacao["filtro"]["total"]."</a>";
            $arrNotificacoes[$inChave] = $arrNotificacao;
        }
        // Retornando a lista de noticações filtradas
        return $arrNotificacoes;
    }
    
    public function post_filtrarRespostasNotificacao(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando os filtros
        if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
        // Recuperando os filtros
        $arrFiltro = json_decode($_POST["filtroBusca"]);
        // Buscando as notificações com os filtros recuperad
        $arrNotificacoes = $this->objDaoNotificacao->filtrarNotificacoesResposta((array) $arrFiltro);
        if(empty($arrNotificacoes)) throw new Exception("Nenhuma Notificação Encontrada!");
        // formatando as notificações
        foreach($arrNotificacoes as $inChave => $arrNotificacao){
            $arrNotificacao["filtro"] = (array) json_decode($arrNotificacao["filtro"]);
            // Formatando o retorno
            $arrNotificacao["paciente"]        = "<a class='links link '  href='".Constantes::$ULR_LISTA_NOTIFICAAO_EQUIPE_MEDICA.$arrNotificacao["id"]."'>".$arrNotificacao["nome"]."(".$arrNotificacao["numero_pep"].")"."</a>";
            $arrNotificacao["data_envio"]    = "<a class='links link '    href='".Constantes::$ULR_LISTA_NOTIFICAAO_EQUIPE_MEDICA.$arrNotificacao["id"]."'><span class='esconder-informacao'>".$arrNotificacao["data_envio"]."</span> " . Utilidades::formatarDataPraBr($arrNotificacao["data_envio"], "Y-m-d H:i:s")."</a>";
            $arrNotificacao["titulo"]         = "<a class='links link '   href='".Constantes::$ULR_LISTA_NOTIFICAAO_EQUIPE_MEDICA.$arrNotificacao["id"]."'>".$arrNotificacao["titulo"]."</a>";
            $arrNotificacoes[$inChave] = $arrNotificacao;
        }
        // Retornando a lista de noticações filtradas
        return $arrNotificacoes;
    }
    
    /**
     * M�todo que ir� retornar o exame pelo id
     *
     * @throws Exception
     * @return mixed
     */
    public function get_recuperarNotificacaoPorID(){
        // Criando o dao
        $this->objDaoNotificacao = new daoNotificacao();
        // Validando
        if(empty($_GET["intIdNotificacao"])) throw new Exception("Notificação Não Informados!");
        $intIdNotificacao = (int) $_GET["intIdNotificacao"];
        // Recuperando o Notifica��o da base
        $objNotificacao = (object) $this->objDaoNotificacao->getNotificacaoPorId($intIdNotificacao);
        if(!$objNotificacao) throw new Exception("Exame Não Encontrado!");
        
        $objNotificacao->filtro = json_decode($objNotificacao->filtro);
        
        if(isset($objNotificacao->filtro->perfil_id)){
            $arrListaPerfil = $this->objDaoNotificacao->listaPerfil();
            foreach($arrListaPerfil as $intChave => $arrValor){
                if($arrValor["id"] == $objNotificacao->filtro->perfil_id)
                    $objNotificacao->perfil = $arrValor["descricao"];
            }
        }
        
        if(isset($objNotificacao->filtro->cancer_id)){
            $arrListaCancer = $this->objDaoNotificacao->listaCancer();
            foreach($arrListaCancer as $intChave => $arrValor){
                if($arrValor["id"] == $objNotificacao->filtro->cancer_id)
                    $objNotificacao->cancer = $arrValor["descricao"];
            }
        }
        
        if(isset($objNotificacao->filtro->sexo)){
            $objNotificacao->sexo = ($objNotificacao->filtro->sexo == 1) ? "Homem" : "Mulher";
        }
        
        // Formatando as datas
        $this->formatarDataNotificação($objNotificacao);
        
        // Recuperando as notificações fihas
        $objNotificacao->filhos = $this->objDaoNotificacao->getNotificacaoPorIdPai($objNotificacao->id);
        
        if(!empty($objNotificacao->filhos)){
            foreach($objNotificacao->filhos as $intChave => $arrObjFilho){
                $objNotificacaoFilhio = (object) $arrObjFilho;
                $this->formatarDataNotificação($objNotificacaoFilhio);
                $objNotificacao->filhos[$intChave] =  $objNotificacaoFilhio;
            }
        }
        // Retornando a notificação
        return $objNotificacao;
    }
    
    public function formatarDataNotificação(&$objNotificacao){
        // Formatando as datas
        $objNotificacao->data_envio = Utilidades::formatarDataPraBr($objNotificacao->data_envio, 'Y-m-d H:i:s', 'd/m/Y H:i:s');
    }
    
}