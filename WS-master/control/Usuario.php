<?php

/**
 * Controlador Usuário
 *
 * @author Alberto Medeiros
 */
class Usuario {
    
    /**
     * Irá conter o objeto  daoUsuário
     *
     * @var daoUsuario
     */
    private $objDaoUsuario;
    
    
    public function get_usuarioPorId(){
        return $this->getResposta();
    }
    
    public function get_listaCancer(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        
        return $this->objDaoUsuario->listaCancer();
    }
    
    public function get_listaPerfis(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
    
        return $this->objDaoUsuario->listaPerfil();
    }
    
    public function post_login(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados        
        if(empty($_POST["strLogin"])) throw new Exception("Login ou Senha Não Informado");
        if(empty($_POST["strSenha"])) throw new Exception("Login ou Senha Não Informado");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strLogin"] = preg_replace("/[^0-9]/", "", $_POST["strLogin"]);
        $arrDados["strSenha"] = $_POST["strSenha"];
        $arrDados["strCodigoOnesignal"] = $_POST["onesignal"];
        // Realizando o login e senha
        $arrRetorno = $this->objDaoUsuario->loginUsuario($arrDados);
        // Caso o usuário não seja encontrado
        if(empty($arrRetorno)) throw new Exception("Usuário não encontrado!");
        // retornando o usuário
        return $arrRetorno;
    }
    
    public function post_esqueciSenha(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strEmail"])) throw new Exception("E-mail Não Informado");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strEmail"] = $_POST["strEmail"];
        // Alterando a senha do usuário caso encontre
        $arrUsuario =  $this->objDaoUsuario->enviarSenha($arrDados);
        // Caso a senha tenha sido alterada
        if(!empty($arrUsuario)){
            $this->enviarEmailSenha($arrUsuario);
            $bolRetorno = true;
        }
        return $bolRetorno;
    }
    
    public function post_existeEmail(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strEmail"])) throw new Exception("E-mail Não Informado!");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strEmail"] = $_POST["strEmail"];
        // Realizando o login e senha
        return $this->objDaoUsuario->existeEmail($arrDados);
    }
    
    /**
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_existeCpf(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strCPF"])) throw new Exception("CPF Não Informado!");
        // Criando os parametros para login
        $arrDados = array();
        $arrDados["strCPF"] = $_POST["strCPF"];
        // Realizando o login e senha
        return $this->objDaoUsuario->existeCPF($arrDados);
    }
    
    /**
     *
     * @throws Exception
     * @return boolean
     */
    public function post_validarCadastro(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strCPF"])) throw new Exception("CPF Não Informado!");
        if(empty($_POST["strEmail"])) throw new Exception("E-mail Não Informado!");
        // Criando os parametros para validar 
        $arrDados = array();
        $arrDados["strCPF"]     = preg_replace("/[^0-9]/", "", $_POST["strCPF"]);
        $arrDados["strEmail"]   = $_POST["strEmail"];
        // Realizando o login e senha
        if($this->objDaoUsuario->existeCPF($arrDados))      throw new Exception("CPF já cadastrado!");
        if($this->objDaoUsuario->existeEmail($arrDados))    throw new Exception("E-mail já cadastrado!");
        return true;
    }
    
    /**
     * Método que irá cadastrar o usuário
     *
     * @throws Exception
     * @return boolean
     */
    public function post_pesquisarUsuarios(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["filtroBusca"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objFiltro = json_decode($_POST["filtroBusca"]);
        // Validações
        if(!isset($objFiltro->perfil_id) || empty($objFiltro->perfil_id)) throw new Exception("Perfil é Obrigatório");
        if($objFiltro->perfil_id == 1 && (isset($objFiltro->cpf) && !empty($objFiltro->cpf) && !Utilidades::validaCPF($objFiltro->cpf))) throw new Exception("CPF invalido!");
        // Formatando
        if(isset($objFiltro->cpf) && !empty($objFiltro->cpf)) $objFiltro->cpf = preg_replace("/[^0-9]/", "", $objFiltro->cpf);
        // Cadastrando o paciente
        $arrUsuarios = $this->objDaoUsuario->pesquisarUsuarios((array) $objFiltro);// cadastrando o paciente na base
        if(empty($arrUsuarios)) throw new Exception("Nenhum Usuário Encontrado!");
        // Formatando o retorno
        foreach($arrUsuarios as &$arrUsuario){
            $arrUsuario["id"] = "<a class='links' href='" . Constantes::$ULR_EDITAR_USUARIO.$arrUsuario["id"] . "'><i class='fa fa-edit'></i></a>";
        }
        return $arrUsuarios;
    }
    
    /**
     * Método que irá retornar o usuário pelo id
     * @throws Exception
     * @return mixed
     */
    public function get_recuperarUsuarioPorId(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_GET["intIdUsuario"])) throw new Exception("Id Não Informado!");
        // Recuperando os dados do paciente
        $intIdUsuario = (int) $_GET["intIdUsuario"];
        // Validações
        if($intIdUsuario == 0) throw new Exception("Usuário Inválido!");
        // Cadastrando o paciente
        $arrUsuarios = $this->objDaoUsuario->getUsuarioPorId($intIdUsuario);
        if(empty($arrUsuarios)) throw new Exception("Nenhum Usuário Encontrado!");
        // Realizando o cast do usuário
        $objUsuario = (object) $arrUsuarios;
        // Retornando o usuário
        return $objUsuario;
    }
    
    /**
     * Método que irá cadastrar o usuário
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarPaciente(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["dadosPaciente"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objPaciente = json_decode($_POST["dadosPaciente"]);
        $objPaciente->onesignal = $_POST["onesignal"];// Recuperando o id do onesignal
        // Validando os dados postados
        $this->validarCadastroPaciente($objPaciente, false, true);
        $objPaciente->perfil_id = 1;// Setando o id perfil para paciente
        $objPaciente->cpf = preg_replace("/[^0-9]/", "", $objPaciente->cpf);// Removendo formatação do cpf
        $objPaciente->data_nascimento = Utilidades::formatarDataPraBanco($objPaciente->data_nascimento);// Formatando a data para o formato de banco de dados
        // Cadastrando o paciente
        $bolCadastro = $this->objDaoUsuario->cadastrarUsuario($objPaciente);// cadastrando o paciente na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o usuário!");
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
            "headings" => array("en" => "Cadastro de Paciente Pendente"),
            'contents' => array("en" => "Paciente com o nº do PEP: " . $objPaciente->pep),
            'data' => array(
                            "foo"=>"bar",
                            "acao"=>Constantes::$ULR_EDITAR_USUARIO_NOTIFICACAO,
                            "parametros"=>array("usuarioId"=>$objPaciente->id)
                        )
        );
        // Enviando a notificação
        $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        return $objRerotno;
    }
    
    /**
     * Método que irá cadastrar o usuário
     *
     * @throws Exception
     * @return boolean
     */
    public function post_cadastrarUsuario(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["dadosUsuario"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objPaciente = json_decode($_POST["dadosUsuario"]);
        $objPaciente->onesignal = "";// Recuperando o id do onesignal
        $objPaciente->cpf = $objPaciente->login;
        // Formatando o cpf
        $objPaciente->cpf = preg_replace("/[^0-9]/", "", $objPaciente->cpf);// Removendo formatação do cpf
        // Validando os dados postados
        if($objPaciente->perfil_id == 1)
            $this->validarCadastroPaciente($objPaciente, false, true);
        else {
            $this->validarCadastro($objPaciente, false, true);
            $objPaciente->cancer_id = 7;// Setando o cancer para nenhum
        }
        // Formatando a data de nascimento
        $objPaciente->data_nascimento = Utilidades::formatarDataPraBanco($objPaciente->data_nascimento);// Formatando a data para o formato de banco de dados
        // Cadastrando o paciente
        $bolCadastro = $this->objDaoUsuario->cadastrarUsuario($objPaciente);// cadastrando o paciente na base
        if(!$bolCadastro) throw new Exception("Não foi possível cadastrar o usuário!");
        
        return true;
    }
    
    /**
     * Método que irá cadastrar o usuário
     *
     * @throws Exception
     * @return boolean
     */
    public function post_editarUsuario(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["dadosUsuario"])) throw new Exception("Dados Não Informados!");
        // Recuperando os dados do paciente
        $objUsuario = json_decode($_POST["dadosUsuario"]);
        $bolValidarSenha = (int) $_GET["validarSenha"];
        // Validando os dados postados de paciente
        if($objUsuario->perfil_id == 1)
            $this->validarCadastroPaciente($objUsuario, true, $bolValidarSenha);
        else 
            $this->validarCadastro($objUsuario, true, $bolValidarSenha);
        // Formatando o cpf
        $objUsuario->cpf = preg_replace("/[^0-9]/", "", $objUsuario->cpf);// Removendo formatação do cpf
        $objUsuario->data_nascimento = Utilidades::formatarDataPraBanco($objUsuario->data_nascimento);// Formatando a data para o formato de banco de dados
        // Recuperando o usuário da base para realizar comparações
        $objUsuarioBase = (object) $this->objDaoUsuario->getUsuarioPorId($objUsuario->id);
        if(empty($objUsuarioBase)) throw new Exception("Usuário não encontrado!");
        // Cadastrando o paciente
        $bolEditado = $this->objDaoUsuario->cadastrarEditarUsuario($objUsuario);// Editar o usuário
        if(!$bolEditado) throw new Exception("Não foi possível editar o usuário!");
        $objRerotno =  null;
        // Caso o usuário tenha sido ativado
        if(@$objUsuarioBase->ativo == 0 && @$objUsuario->ativo ==1){
            // Recuperando todos os usuários admin
            $arrIds = array(@$objUsuarioBase->codigo_onesignal);
            // Criando os dados de notificação
            $arrDadosNotificacao = array(
                'include_player_ids' => $arrIds,
                "headings" => array("en" => "Cadastro Aprovado!"),
                'contents' => array("en" => "Olá {$objUsuarioBase->nome}, seu cadastro foi aprovado! Agora você poderá acessar o Conexão Vida - IMIP." )
            );
            // Enviando a notificação
            $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        }
        return $objRerotno;
    }
    
    /**
     * Método para ativar o usuário
     * 
     * @throws Exception
     * @return boolean
     */
    public function post_ativarPaciente(){
        $bolRetorno = false;
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_POST["strCpf"])) throw new Exception("Usuário Não Informados!");
        // Recuperando o login do paciente
        $intCpf = $_POST["strCpf"];
        // Cadastrando o paciente
        $arrUsuario = $this->objDaoUsuario->ativarPaciente($intCpf);// cadastrando o paciente na base
        $objUsuario = (object) $arrUsuario;
        $arrIds = array($objUsuario->codigo_onesignal);
        // Criando os dados de notificação
        $arrDadosNotificacao = array(
            'include_player_ids' => $arrIds,
            "headings" => array("en" => "Ativação"),
            'contents' => array("en" => "Olá, {$objUsuario->login}! Seu cadastro foi aprovado, agora você poderá utilizar o Conexão Vida!")
        );
        // Enviando a notificação
        $objRerotno = Utilidades::enviarNotificacao($arrDadosNotificacao);
        return true;
    }
    
    /**
     * Enviará o email para o usuário
     * @param array $arrUsuario
     */
    public function enviarEmailSenha(array $arrUsuario){
        // Criando o email
        $mail = new PHPMailer();
        $mail->IsSMTP();		// Ativar SMTP
        //$mail->SMTPDebug = 3;		// Debugar: 1 = erros e mensagens, 2 = mensagens apenas
        $mail->SMTPAuth = true;		// Autenticação ativada
        $mail->SMTPSecure = 'ssl';	// SSL REQUERIDO pelo GMail
        $mail->Host = 'smtp.gmail.com';	// SMTP utilizado
        $mail->Port = 465;  		// A porta 587 deverá estar aberta em seu servidor
        $mail->SMTPSecure = 'ssl';
        $mail->Username = 'conexaovidaimip@gmail.com';
        $mail->Password = 'conexaovida';
        $mail->FromName = 'Conexão Vida - IMIP';
        $mail->IsHTML(true);
        // Caso seja um único email
        $mail->addAddress($arrUsuario["email"]);
        // Add a recipient
        $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
        $mail->Subject = "Recuperar Senha";
        // Criando o corpo do email
        $strBody = "Olá, " . $arrUsuario["nome"] . "! <br />";
        $strBody .= "Conforme solicitado, segue nova senha: <br />";
        $strBody .= "<b>Senha:</b> " . $arrUsuario["novaSenha"] . " <br />";
        // Colocando o corpo do email        
        $mail->Body = $strBody;
        if(!$mail->send()) throw new Exception("Não foi possível enviar e-mail!", 9999);
    }
    
    /**
     * Método que irá validar os dados de cadastro do Paciente
     * 
     * @param stdClass $objPaciente
     * @param string $bolEdit
     * @throws Exception
     */
    function validarCadastroPaciente(stdClass $objPaciente, $bolEdit = false, $bolValidarSenha = true){
        // Validação dos dados de usuário
        if(empty($objPaciente->sexo))       throw new Exception("Sexo Não Informado!");
        if(empty($objPaciente->endereco))   throw new Exception("Endereço Não Informado!");
        if(empty($objPaciente->cidade))     throw new Exception("Cidade Não Informada!");
        if(empty($objPaciente->uf))         throw new Exception("UF Não Informado!");
        if(empty($objPaciente->contato))    throw new Exception("Nº de Contato Não Informado!");
        if(strlen(preg_replace("/[^0-9]/", "", $objPaciente->contato))<10 || strlen(preg_replace("/[^0-9]/", "", $objPaciente->contato))>11) throw new Exception("Contato Inválido!");
        if(isset($objPaciente->contato_dois)){
            if(!empty($objPaciente->contato_dois) 
                && strlen(preg_replace("/[^0-9]/", "", $objPaciente->contato_dois))<10 
                || strlen(preg_replace("/[^0-9]/", "", $objPaciente->contato_dois))>11)
                throw new Exception("Segundo Contato Inválido!");
        }
        if(isset($objPaciente->contato_dois)){
            if($objPaciente->contato == $objPaciente->contato_dois) throw new Exception("Os números dos contatos não porem ser iguais!");
        }
        
        if($bolEdit){// caso seja edição
            if(empty($objPaciente->numero_pep))        throw new Exception("Nº PEP Não Informado!");
            if(!empty($objNotificacao->numero_pep) && strlen($objNotificacao->numero_pep) != 7)        throw new Exception("Número de pep Inválido!");
        }else{
            if(empty($objPaciente->pep))        throw new Exception("Nº PEP Não Informado!");
            if(!empty($objNotificacao->pep) && strlen($objNotificacao->pep) != 7)        throw new Exception("Número de pep Inválido!");
            if($this->objDaoUsuario->existePep(array("strPep"=>$objPaciente->pep))) throw new Exception("Pep já cadastrado!");
        }
        if(!Utilidades::validarData($objPaciente->data_nascimento))    throw new Exception("Data Nascimento Inválida!");
        // validando os dados que são comuns ao paciente e a equipe médica
        $this->validarCadastro($objPaciente, $bolEdit, $bolValidarSenha);
    }
    
    /**
     * Método que irá validar os dados de acesso do usuário seja paciente ou equipe médica
     * 
     * @param stdClass $objUsuario
     * @param string $bolEdit
     * @throws Exception
     */
    function validarCadastro(stdClass $objUsuario, $bolEdit = false, $bolValidarSenha = true){
        // Validação dos dados comuns
        if(empty($objUsuario)) throw new Exception("Dados Não Informados!");
        if(empty($objUsuario->cpf)) throw new Exception("CPF Não Informado!");
        if(empty($objUsuario->email)) throw new Exception("E-mail Não Informado!");
        if(!Utilidades::validaCPF($objUsuario->cpf)) throw new Exception("CPF invalido!");
        if(preg_match("/[^A-Za-záéíóúâãõê\s]/", $objUsuario->nome)) throw new Exception("Nome inválido!");
        if(!Utilidades::validarEmail($objUsuario->email)) throw new Exception("E-mail invalido!");
        if(empty($objUsuario->nome)) throw new Exception("Nome Não Informado!");
        
        // Validando a data de nascimeento
        if(!Utilidades::diffData(Utilidades::formatarDataPraBanco($objUsuario->data_nascimento), date("Y-m-d")))    
            throw new Exception("Data Nascimento Maior que a Data Atual!");
        
        // se for necessário validar senha
        if($bolValidarSenha){
            if(empty($objUsuario->senha)) throw new Exception("Senha Não Informada!");
            if(strlen($objUsuario->senha) < 6 || strlen($objUsuario->senha) > 8) throw new Exception("Senha inválida! Sua senha deve conter entre 6 e 8 caracteres!");
            if($objUsuario->senha != $objUsuario->confirmacao_senha) throw new Exception("Senhas são diferentes!", 9999);
        }
        
        // Caso não seja editar
        if(!$bolEdit){
            if($this->objDaoUsuario->existeCPF(array("strCPF"=>$objUsuario->cpf))) throw new Exception("CPF já cadastrado!", 9999);
            if($this->objDaoUsuario->existeEmail(array("strEmail"=>$objUsuario->email))) throw new Exception("Email já cadastrado!", 9999);
        }
    }

    public function get_listarCidades(){
        // Criando o dao
        $this->objDaoUsuario = new daoUsuario();
        // Validando os dados postados
        if(empty($_GET["UF"])) throw new Exception("UF Não Informada!");
        // Recuperando os dados do paciente
        $UF = $_GET["UF"];
        // Listando as cidades do estado selecionado
        $arrCidades = $this->objDaoUsuario->carregarCidades($UF);
        if(empty($arrCidades)) throw new Exception("Cidades não foram Encontrados!");        
        // Retornando a lista de exames do paciente
        return $arrCidades;
    }
    
}