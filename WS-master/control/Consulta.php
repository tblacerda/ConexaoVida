<?php

/**
 * Controlador Exame
 *
 * @author Régis Perez
 */
class Consulta {
    
    /**
     * Irá conter o objeto  daoConsulta
     *
     * @var daoConsulta
     */
    private $objDaoConsulta;
      
    /**
     * Método que irá realizar a validação e o cadastro dos exames do paciente
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarAgendamento(){
        // Criando o dao
        $this->objDaoConsulta   = new daoConsulta();
        // Validando os dados postados
        if(empty($_POST["dadosAgendamento"])) throw new Exception("Dados Não Informados!");
        print_r($_POST["dadosAgendamento"]);
        die;
        // Recuperando os dados do paciente
        $objAgendamento = json_decode($_POST["dadosAgendamento"]);  
        $objAgendamento->usuario_id = (int) $_POST["usuario_id"];
        // Validando os dados postados
        $this->validarCadastroAgendamento($objAgendamento); 
        // Cadastrando o exame
        $bolCadastro = $this->objDaoConsulta->cadastrarAgendamento($objAgendamento);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o Agendamento!");
        
        return $this->notificarEquipe($objAgendamento);
    }
    
    /**
     * Método que irá disparar notificações para equipe médica
     * 
     * @param agendamento cadastrado na base $objAgendamento
     */
    function notificarEquipe($objAgendamento){
        $this->objDaoUsuario    = new daoUsuario();
        $objPaciente = (object) $this->objDaoUsuario->getUsuarioPorId($objAgendamento->usuario_id);
        // Recuperando todos os usuários admin
        $arrIDsOnesinal = $this->objDaoUsuario->getIdsOnesignalPorPefil(2);
        $arrIds = array();
        // Formatando os ids para envio em massa
        foreach($arrIDsOnesinal as $arrValor){
            $arrIds[] = $arrValor["codigo_onesignal"];
        }
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Solicititação de Agendamento"),
            'contents' => array("en" => "Paciente com o nº do PEP: $objPaciente->numero_pep, Solicitou um agendamento para o dia $objAgendamento->data_solicitada"),
            'data' => array(
                                "foo"=>"bar",
                                "acao"=>Constantes::$ULR_AGENDAMENTO_CONSULTA,
                                "parametros"=>array("agendamentoId"=>$objAgendamento->id)
                            )
        );
        // enviando a notificação e retornando o resultado
        return Utilidades::enviarNotificacao($arrDadosNotificacao);
    }
    /**
     * Método que irá realiza a confirmação de recebimento do exame do paciente
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_confirmarAgendamento(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando os dados postados
        if(empty($_POST["dadosAgendamento"])) throw new Exception("Dados Não Informados!");
        if(empty($_POST["intIdUsuario"])) throw new Exception("Usuário Não Informados!");
        if(empty($_POST["intIdConsulta"])) throw new Exception("Agendamento Não Informados!");
        // Recuperando os dados do paciente
        $objAgendamento = json_decode($_POST["dadosAgendamento"]);
        $objAgendamento->usuario_id = (int) $_POST["intIdUsuario"];
        $objAgendamento->id = (int) $_POST["intIdConsulta"];
        // recuperando o agendamento do banco
        $objAgendamentoBanco = (object) $this->objDaoConsulta->getAgendamentoPorId($objAgendamento->id);
        // Validando os dados postados
        $this->validarconfirmarAgendamento($objAgendamento, $objAgendamentoBanco);
        // Cadastrando o exame
        $bolCadastro = $this->objDaoConsulta->confirmarAgendamento($objAgendamento);// cadastrando o exame na base
        if(!$bolCadastro) throw new Exception("Não foi possível confirmar a consulta!");
        // notificando o paciente a data dos seus agendamentos
        return $this->notificarConfirmarAgendamento($objAgendamento->id);
    }
    
    /**
     * Irá informar ao paciente que sua solicitação foi aceita e marcada
     *
     * @param unknown $intIdUsuario
     * @return mixed
     */
    function notificarConfirmarAgendamento($intIdAgendamento){
        $this->objDaoUsuario    = new daoUsuario();
        $objAgendamento = $this->recuperarConsultaPorID($intIdAgendamento);
        $objPaciente = (object) $this->objDaoUsuario->getUsuarioPorId($objAgendamento->usuario_id);
        // Recuperando todos os usuários admin
        $arrIDsOnesinal = $this->objDaoUsuario->getIdsOnesignalPorPefil(2);
        $arrIds = array($objPaciente->codigo_onesignal);
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Consulta Marcada"),
            'contents' => array("en" => "Olá, {$objPaciente->nome}! Sua solicitação de agendamento foi aceita e a sua consulta ficou marcada para o dia {$objAgendamento->data_confirmada}, desejamos boa sorte em sua consulta!"),
            'data' => array(
                "foo"=>"bar",
                "acao"=>Constantes::$ULR_MEUS_EXAMES_AGENDAMENTOS
            )
            );
        // enviando a notificação e retornando o resultado
        $objNotificacaoConsulta = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Enviando os lembretes da consulta para o paciente
        $strDataLembrete1 = date('Y-m-d', strtotime(Utilidades::formatarDataPraBanco($objAgendamento->data_confirmada) . ' - 5 days'));
        $strDataLembrete2 = date('Y-m-d', strtotime(Utilidades::formatarDataPraBanco($objAgendamento->data_confirmada) . ' - 1 days'));
        // Criando os dados de notificação de lembrete
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Consulta Marcada"),
            'contents' => array("en" => "Olá, {$objPaciente->nome}! Este é um  lembrete para avisar que faltam dois dias para sua consulta marcada para a data {$objAgendamento->data_confirmada}!"),
            'send_after' => "{$strDataLembrete1} 12:00:00 GMT-3",
            'data' => array(
                "foo"=>"bar",
                "acao"=>Constantes::$ULR_MEUS_EXAMES_AGENDAMENTOS
            )
            );
        // enviando a notificação e retornando o resultado
        $objLembreteConsulta1 = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Criando os dados de notificação de lembrete
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Consulta Marcada"),
            'contents' => array("en" => "Olá, {$objPaciente->nome}! Este é um  lembrete para avisar que faltam cinco dias para sua consulta marcada para a data {$objAgendamento->data_confirmada}!"),
            'send_after' => "{$strDataLembrete2} 12:00:00 GMT-3",
            'data' => array(
                "foo"=>"bar",
                "acao"=>Constantes::$ULR_MEUS_EXAMES_AGENDAMENTOS
            )
            );
        // enviando a notificação e retornando o resultado
        $objLembreteConsulta1 = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Criando os dados de notificação de lembrete
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Consulta Marcada"),
            'contents' => array("en" => "Olá, {$objPaciente->nome}! Este é um  lembrete para avisar que sua consulta é amanhã dia {$objAgendamento->data_confirmada}. Boa sorte!"),
            'send_after' => "{$strDataLembrete2} 12:00:00 GMT-3",
            'data' => array(
                "foo"=>"bar",
                "acao"=>Constantes::$ULR_MEUS_EXAMES_AGENDAMENTOS
            )
            );
        // enviando a notificação e retornando o resultado
        $objLembreteConsulta2 = Utilidades::enviarNotificacao($arrDadosNotificacao);
        // Retornando as notificações
        return array($objNotificacaoConsulta, $objLembreteConsulta1, $objLembreteConsulta2);
    }
    
    /**
     * Método que irá validar o confirmação da consulta
     * 
     * @param stdClass $objAgendamento
     * @throws Exception
     */
    function validarconfirmarAgendamento(stdClass $objAgendamento, stdClass $objAgendamentoBanco){
        // Validação dos dados de exame
        if(!isset($objAgendamento->data_confirmada))                         throw new Exception("Data Consulta Não Informada!");
        if(empty($objAgendamento->usuario_id))                               throw new Exception("Usuário Não Informado!");
        if(!Utilidades::validarData($objAgendamento->data_confirmada))      throw new Exception("Data Consulta Inválida!");
        if(!Utilidades::diffData(date("Y-m-d"),
                                Utilidades::formatarDataPraBanco($objAgendamento->data_confirmada)))         
            throw new Exception("Data da Confirmação Tem que Ser Maior que a Data de Hoje!");
    }
    
    /**
     * Método que irá validar os dados de cadastro do Agendamento
     * 
     * @param Object $objAgendamento
     * @throws Exception
     */
    function validarCadastroAgendamento(stdClass $objAgendamento){
        // Validação dos dados de exame
        if(empty($objAgendamento->data_solicitada))   throw new Exception("Data da Consulta Não Informada!");
        if(empty($objAgendamento->usuario_id))        throw new Exception("Paciente Não Informado!");        
        if(empty($objAgendamento->area_id))           throw new Exception("Área Não Informada!");
      
        if(!Utilidades::validarData($objAgendamento->data_solicitada))       throw new Exception("Data da Consulta Inválida!");
        // Validando as datas
        if(!Utilidades::diffData(Utilidades::formatarDataPraBanco($objAgendamento->data_solicitada), 
            Utilidades::formatarDataPraBanco(date('d/m/Y', strtotime('+2 months')))))         
                throw new Exception("Data de consulta fora do limite, favor selecionar um período de 2 (dois) meses!");
            
        if(!Utilidades::diffData(
            Utilidades::formatarDataPraBanco(date('d/m/Y', strtotime('+20 day'))),
            Utilidades::formatarDataPraBanco($objAgendamento->data_solicitada)))
            throw new Exception("Data da Consulta tem que ser maior ou igual a ".date('d/m/Y', strtotime('+20 day'))."!");
    } 
    
    /**
     * Método que irá retornar os Agendamentos pelo id do paciente (usuário)
     * @throws Exception
     * @return mixed
     */
    public function get_listarAgendamentosDoUsuarioPorId(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        // Recuperando os dados do paciente
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Listando os exames do paciente
        $arrAgendamentos = $this->objDaoConsulta->listarAgendamentosDoPaciente($intIdUsuario);
        if(empty($arrAgendamentos)) throw new Exception("Agendamentos não foram Encontrados!"); 
        // Retornando a lista de exames do paciente
        return $arrAgendamentos;
    }
    
    
    public function post_filtrarAgendamento(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();        
         // Validando os filtros
         if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
         // Recuperando os filtros
        $objFiltro = json_decode($_POST["filtroBusca"]);
        // Validando o filtro
        $this->validarFiltroAgendamento($objFiltro);
        // Buscando os exames com os filtros recuperados
        $arrAgendamentos = $this->objDaoConsulta->filtrarAgendamento((array) $objFiltro);
        if(empty($arrAgendamentos)) throw new Exception("Nenhum Exame Encontrado!");
        // formatando os exames
        foreach($arrAgendamentos as $inChave => $arrExame){
            $bolResponsável = $arrExame["usuario_responsavel"] != null ? "<i class='fa fa-user'></i> " : "";
            // Formatando o nome do paciente
            $arrExame["nome"]           = "<a class='links link ' href='".Constantes::$ULR_AGENDAMENTO_CONSULTA_DETALHE.$arrExame["id"]."'>".$bolResponsável.$arrExame["nome"]."</a>";
            $arrExame["situacao"]    = "<a class='links link' href='".Constantes::$ULR_AGENDAMENTO_CONSULTA_DETALHE.$arrExame["id"]."'>".$arrExame["situacao"]."</a>";
            $arrExame["data_solicitada_banco"]    = "<a class='links link' href='".Constantes::$ULR_AGENDAMENTO_CONSULTA_DETALHE.$arrExame["id"]."'><span class='esconder-informacao'>".$arrExame["data_solicitada_banco"]."</span> ".Utilidades::formatarDataPraBr($arrExame["data_solicitada_banco"])."</a>";
            $arrAgendamentos[$inChave] = $arrExame;
        }
        // Retornando a lista de exames filtrados
        return $arrAgendamentos;
    }
    
    /**
     * Método que irá ser responsável por informar que um usuário é responsável pelo agendamento
     * @throws Exception
     * @return boolean
     */
    public function post_responsavelAgendamento(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando os dados
        if(empty($_POST["intIdUsuario"]))       throw new Exception("Usuario Não Informado!");
        if(empty($_POST["intIdAgendamento"]))   throw new Exception("Agendamento Não Informado!");
        // Recuperando os ids
        $intIdUsuario = (int) $_POST["intIdUsuario"];
        $intIdAgendamento = (int) $_POST["intIdAgendamento"];
        // Atualizando o agendamento na base
        $bolRetorno = $this->objDaoConsulta->responsavelAgendamento($intIdUsuario, $intIdAgendamento);
        if(!$bolRetorno) throw new Exception("Nenhum Agendamento Encontrado!");
        // Retornando a lista de exames filtrados
        return true;
    }
    
    /**
     * Método que irá realizar a recusa do agendamento
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_recusarAgendamento(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando os dados
        if(empty($_POST["intIdUsuario"]))       throw new Exception("Usuario Não Informado!");
        if(empty($_POST["intIdAgendamento"]))   throw new Exception("Agendamento Não Informado!");
        // Recuperando os ids
        $intIdUsuario = (int) $_POST["intIdUsuario"];
        $intIdAgendamento = (int) $_POST["intIdAgendamento"];
        // Atualizando o agendamento na base
        $bolRetorno = $this->objDaoConsulta->recusarAgendamento($intIdUsuario, $intIdAgendamento);
        if(!$bolRetorno) throw new Exception("Nenhum Agendamento Encontrado!");
        // Retornando 
        return $this->notificarPacienteRecusa($intIdAgendamento);;
    }
    
    /**
     * Irá informar ao paciente que sua solicitação foi recusada
     * 
     * @param unknown $intIdUsuario
     * @param unknown $intIdAgendamento
     * @return mixed
     */
    function notificarPacienteRecusa($intIdAgendamento){
        $this->objDaoUsuario    = new daoUsuario();
        $objAgendamento = $this->recuperarConsultaPorID($intIdAgendamento);
        $objPaciente = (object) $this->objDaoUsuario->getUsuarioPorId($objAgendamento->usuario_id);
        // Recuperando todos os usuários admin
        $arrIDsOnesinal = $this->objDaoUsuario->getIdsOnesignalPorPefil(2);
        $arrIds = array($objPaciente->codigo_onesignal);
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Agendamento Recusado"),
            'contents' => array("en" => "Olá, {$objPaciente->nome}! Sua solicitação de agendamento para a data {$objAgendamento->data_solicitada} foi recusada, por favor, selecione outra data para que possa ser avaliada!"),
            'data' => array(
                "foo"=>"bar",
                "acao"=>Constantes::$ULR_MEUS_EXAMES_AGENDAMENTOS
            )
        );
        // enviando a notificação e retornando o resultado
        return Utilidades::enviarNotificacao($arrDadosNotificacao);
    }
    
    /**
     * Método que irá retornar a lista de agendamentos agrupados por data, area e usuário
     * @throws Exception
     * @return unknown[]|mixed[]
     */
    public function get_filtrarAgendamentoGrafico(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Buscando os exames com os filtros recuperados
        $arrAgendamentos = $this->objDaoConsulta->filtrarAgendamentoGrafico();
        foreach($arrAgendamentos["arrAgendamentoPorAera"] as $intChave => $arrValor){
            $arrAgendamentos["arrAgendamentoPorAera"][$intChave] = array();
            $arrAgendamentos["arrAgendamentoPorAera"][$intChave][0] = $arrValor["descricao"];
            $arrAgendamentos["arrAgendamentoPorAera"][$intChave][1] = $arrValor["total"];
        }
        foreach($arrAgendamentos["arrAgendamentoPorData"] as $intChave => $arrValor){
            $arrAgendamentos["arrAgendamentoPorData"][$intChave] = array();
            $arrAgendamentos["arrAgendamentoPorData"][$intChave][0] = $arrValor["mes"];
            $arrAgendamentos["arrAgendamentoPorData"][$intChave][1] = $arrValor["total"];
        }
        foreach($arrAgendamentos["arrAgendamentoPorUsuario"] as $intChave => $arrValor){
            $arrAgendamentos["arrAgendamentoPorUsuario"][$intChave] = array();
            $arrAgendamentos["arrAgendamentoPorUsuario"][$intChave][0] = $arrValor["nome"];
            $arrAgendamentos["arrAgendamentoPorUsuario"][$intChave][1] = $arrValor["total"];
        }
        if(empty($arrAgendamentos)) throw new Exception("Nenhum Agendamento Encontrado!");
        // Retornando a lista de exames filtrados
        return $arrAgendamentos;
    }
    
    /**
     * Validando o filtro apresentado
     * @param object $objFiltro
     * @throws Exception
     */
    function validarFiltroAgendamento($objFiltro){
        // Caso a data inicio tenha sido selecionada e a data fim não
        if(isset($objFiltro->data_solicitada_inicio) && !empty($objFiltro->data_solicitada_inicio) 
            && (!isset($objFiltro->data_solicitada_fim) || empty($objFiltro->data_solicitada_fim))){
            throw new Exception("Favor selecionar a data fim!");
        }
        // Caso a data fim tenha sido selecionada e a data inicio não
        if(isset($objFiltro->data_solicitada_fim) && !empty($objFiltro->data_solicitada_fim)
            && (!isset($objFiltro->data_solicitada_inicio) || empty($objFiltro->data_solicitada_inicio))){
                throw new Exception("Favor selecionar a data Inicio!");
        }
        // Caso a data inicio tenha sido selecionada
        if(isset($objFiltro->data_solicitada_inicio) && !empty($objFiltro->data_solicitada_inicio)){
            if(!Utilidades::diffData(Utilidades::formatarDataPraBanco($objFiltro->data_solicitada_fim),
                Utilidades::formatarDataPraBanco($objFiltro->data_solicitada_fim)))
                throw new Exception("Favor selecionar a data Inicio!");
        }
    }
    
    /**
     * Método que irá retornar a consulta pelo id
     *
     * @throws Exception
     * @return mixed
     */
    public function get_recuperarConsultaPorID(){
        // Criando o dao
        $this->objDaoConsulta = new daoConsulta();
        // Validando
        if(empty($_GET["intIdConsulta"])) throw new Exception("Consulta Não Informados!");
        $intIdConsulta = (int) $_GET["intIdConsulta"];
        // Recuperando o agendament da base
        $objAgendamento = $this->recuperarConsultaPorID($intIdConsulta);
        
        return $objAgendamento;
    }
    
    public function recuperarConsultaPorID($intIdConsulta){
        $objAgendamento = (object) $this->objDaoConsulta->getAgendamentoPorId($intIdConsulta);
        if(!$objAgendamento) throw new Exception("Consulta Não Encontrada!");
        // Formatando as tadas
        $objAgendamento->data_solicitada = Utilidades::formatarDataPraBr($objAgendamento->data_solicitada);
        $objAgendamento->data_agendamento = Utilidades::formatarDataPraBr($objAgendamento->data_agendamento);
        if(!empty($objAgendamento->data_confirmada))
            $objAgendamento->data_confirmada = Utilidades::formatarDataPraBr($objAgendamento->data_confirmada);
        // Caso o exame esteja entregue
        if($objAgendamento->situacao ==1) $objAgendamento->data_recebimento = Utilidades::formatarDataPraBr($objAgendamento->data_recebimento);
    
        return $objAgendamento;   
    }
}
