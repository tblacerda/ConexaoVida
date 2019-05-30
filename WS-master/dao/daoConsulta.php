<?php
/**
 * Dao Padrão dos exames
 */

require_once(__DIR__ . '../../control/Consulta.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoExame
 *
 * @author Régis Perez
 */
class daoConsulta extends Dao {


    function __construct() {
        parent::__construct();
    }
    
    /**
     * Método que irá listar as áreas
     * 
     * @return mixed
     */
    function listarAreas(){
        try {
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          *
                        FROM area";
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá listas o total de exames por área
     * 
     * @return mixed
     */
    function listarTotalConsultaPorArea(){
        try {
            $this->sql ="SELECT
                          a.descricao,
                          count(s.id) total
                        FROM
                          solicitacao_agendamento s
                        INNER JOIN area a on s.id = e.area_id
                        GROUP BY a.descricao
                        WHERE s.data_recebimento is null";
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá calcular a previsão de entrega para o tipo de exame
     * 
     * @param integer $intIdTipoExame
     * @return mixed
     */
    function getPrevisaoPorTipoExame($intIdTipoExame, $strDataColeta){
        try {
            // Formatando a data para o banco de dados
            $strDataColeta = Utilidades::formatarDataPraBanco($strDataColeta);
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) AS total_dias,
                          COUNT(ID) AS qtd_exames,
                          ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0) AS mediaCalculada,
                          :data_coleta + INTERVAL ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0) DAY AS previsao
                        FROM exame
                        WHERE 
                            tipo_exame_id = :tipo_exame_id 
                            AND data_exame >= NOW() - INTERVAL 120 DAY
                            AND data_recebimento is not null";
            $this->prepare();
            // Realizando os bids para seguran�a
            $this->bind("tipo_exame_id", $intIdTipoExame);
            $this->bind("data_coleta", $strDataColeta);
            $this->executar();
            $arrPrevisao = $this->buscarDoResultadoAssoc(true);
            // Formatando a data
            if(!empty($arrPrevisao) && $arrPrevisao["qtd_exames"] > 0) $arrPrevisao["previsao"] = Utilidades::formatarDataPraBr($arrPrevisao["previsao"], 'Y-m-d');
            // Retornando a lista de cancer
            return $arrPrevisao;
        } catch (Exception $ex) {}
    }
    
    /**
     * Método que irá cadastrar o Agendamento
     * 
     * @param stdClass $objAgendamento
     * @throws Exception
     * @return boolean
     */
    function cadastrarAgendamento(stdClass &$objAgendamento){
        try {
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO solicitacao_agendamento
                        (
                            data_solicitada, 
                            usuario_id, 
                            aceito, 
                            descricao, 
                            area_id
                        )
                        VALUES
                        (
                            :data_solicitada, 
                            :usuario_id,
                            0,
                            :descricao,
                            :area_id
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_solicitada", Utilidades::formatarDataPraBanco($objAgendamento->data_solicitada));
            $this->bind("usuario_id", $objAgendamento->usuario_id);
            $this->bind("descricao", $objAgendamento->descricao);
            $this->bind("area_id", $objAgendamento->area_id);            
            // Recuperando o id do exame cadastrado
            $this->executar();
            // Recuperar id do exame
            $objAgendamento->id = $this->retornarUltimoIDInserido();
            $this->comitarTransacao();
            // Verificando se houve altera��es
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que ira realizar o recebimento do exame
     * 
     * @param stdClass $objAgendamento
     * @throws Exception
     * @return boolean
     */
    function confirmarAgendamento(stdClass &$objAgendamento){
        try {
            $objAgendamento->data_confirmada = Utilidades::formatarDataPraBanco($objAgendamento->data_confirmada);
            $this->iniciarTransacao();
            $this->sql ="UPDATE solicitacao_agendamento
                        SET data_confirmada = :data_confirmada,
                            usuario_responsavel = :usuario_id,
                            aceito = 1
                        WHERE 
                           id = :id ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_confirmada", $objAgendamento->data_confirmada);
            $this->bind("usuario_id", $objAgendamento->usuario_id);
            $this->bind("id", $objAgendamento->id);
            // Recuperando o id do exame cadastrado
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que irá retornar o agendamento pelo id
     * 
     * @param unknown $intIdAgendamento
     * @return mixed
     */
    function getAgendamentoPorId($intIdAgendamento){
        try {
            $intIdAgendamento = (int) $intIdAgendamento;
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          s.*,                    
                          a.descricao area,
                          u.nome,
                          u.numero_pep,
                          resp.nome responsavel,
                          (CASE WHEN aceito = 0 THEN 'Pendente'
                            WHEN aceito = 1 THEN 'Agendado'
                            ELSE 'Rejeitada'
                            END
                            ) as situacao
                        FROM
                          solicitacao_agendamento s
                        JOIN usuario u on u.id =  s.usuario_id
                        LEFT JOIN usuario resp on resp.id =  s.usuario_responsavel
                        JOIN area a on a.id = s.area_id
                        WHERE s.id = :id";
            $this->prepare();
            $this->bind("id", $intIdAgendamento);
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc(true);
        } catch (Exception $ex) {   throw new Exception($ex->getMessage(), 9999);   }
    }

    /**
     * Método que irá retornar os agendamentos pelo id do paciente (usuario)
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function listarAgendamentosDoPaciente($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="SELECT
                          a.descricao area,
                          s.*
                        FROM
                          solicitacao_agendamento s
                        INNER JOIN area a on a.id = s.area_id
                        WHERE usuario_id = :usuario_id
                        ORDER BY s.data_solicitada desc ";
            $this->prepare();
            $this->bind("usuario_id", $intIdUsuario);
            $this->executar();
            $arrAgendamentos = $this->buscarDoResultadoAssoc();
            if(empty($arrAgendamentos)) throw new Exception("Consultas não foram encontradas!");
            // Para cada agendamento 
            foreach($arrAgendamentos as $intChave => $exames){
                // Formatando as fatas
                $arrAgendamentos[$intChave]["data_solicitada"]  = Utilidades::formatarDataPraBr($exames["data_solicitada"]);
                $arrAgendamentos[$intChave]["data_agendamento"] = Utilidades::formatarDataPraBr($exames["data_agendamento"]);
                // Se a data de confirmação não for vazia
                if($exames["data_confirmada"] != null){
                    $arrAgendamentos[$intChave]["data_confirmada"] = Utilidades::formatarDataPraBr($exames["data_confirmada"]);
                    $arrAgendamentos[$intChave]["data_solicitada"] = Utilidades::formatarDataPraBr($exames["data_confirmada"]);
                }
            }
            // Retornando os agendamentos do paciente
            return $arrAgendamentos;
        } catch (Exception $ex) {  throw new Exception($ex->getMessage(), 9999);  }
    }

    /**
     * Método que irá retornar os agendamentos filtrados
     * 
     * @throws Exception
     * @return mixed
     */
    function filtrarAgendamento(array $arrDados){
        //filtra os exames de um determinado pep
        try{
            $this->sql ="SELECT
                          s.*,
                          u.nome,
                          u.numero_pep,
                          (CASE
                             WHEN aceito = 0 THEN 'Pendente'
                             WHEN aceito = 1 THEN 'Agendado'
                             ELSE 'Rejeitada'
                          END) as situacao,
                          (CASE
                             WHEN data_confirmada IS NOT NULL THEN data_confirmada
                             ELSE data_solicitada
                          END) as data_solicitada_banco
                        FROM solicitacao_agendamento s
                        INNER JOIN usuario u ON s.usuario_id = u.id
                        WHERE
                             1 = 1  ";
            
            /***** FILTROS CASO INFORMADOS ******/
            if(isset($arrDados["area_id"]) && !empty($arrDados["area_id"]))
                $this->sql .= " AND s.area_id = :area_id";
            
            if(isset($arrDados["situacao"]) && $arrDados["situacao"] != ""){
                $arrDados["situacao"] = (int) $arrDados["situacao"];
                $this->sql .= " AND aceito = {$arrDados["situacao"]}";
            }
            if(isset($arrDados["data_solicitada_inicio"]) && !empty($arrDados["data_solicitada_inicio"])){
                $this->sql .= " AND (
                                    s.data_solicitada BETWEEN '".Utilidades::formatarDataPraBanco($arrDados["data_solicitada_inicio"])."' AND '".Utilidades::formatarDataPraBanco($arrDados["data_solicitada_fim"])."'
                                    OR
                                    s.data_confirmada BETWEEN '".Utilidades::formatarDataPraBanco($arrDados["data_solicitada_inicio"])."' AND '".Utilidades::formatarDataPraBanco($arrDados["data_solicitada_fim"])."'
                                )";
            }
                
            
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                $this->sql .= " AND u.numero_pep= :numero_pep";
            
            $this->sql .= "   
                  ORDER BY
                    data_confirmada DESC,
                    data_solicitada DESC";
            // PREPARANDO A CONSULTA
            $this->prepare();
            /***** BIND NOS VALORES DOS FILTROS ******/
            if(isset($arrDados["area_id"]) && !empty($arrDados["area_id"]))
                $this->bind("area_id", $arrDados["area_id"]);
        
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                 $this->bind("numero_pep", $arrDados["pep"]);
            // EXECUTANDO A CONSULTA
            $this->executar();
            $arrAgendamentos = $this->buscarDoResultadoAssoc();
            if(empty($arrAgendamentos)) throw new Exception("Agendamentos não foram encontrados!");
            // Retornando os exames filtrados
            return $arrAgendamentos;
        } catch (Exception $ex) { throw new Exception($ex->getMessage(), 9999); }
    } 
    
    /**
     * Método que irá retornar a lista de agendamentos agrupados por data, area e usuário
     * @throws Exception
     * @return unknown[]|mixed[]
     */
    function filtrarAgendamentoGrafico(){
        try{
            // FIltrando agendamenros por área
            $this->sql ="SELECT
                            count(s.id) total,
                            a.descricao
                        FROM
                            solicitacao_agendamento s
                        INNER JOIN area a on a.id = s.area_id
                        WHERE ACEITO = 1
                        GROUP BY a.descricao";
            // PREPARANDO A CONSULTA
            $this->prepare();
            $this->executar();
            $arrAgendamentoPorAera = $this->buscarDoResultadoAssoc();
            
            // FIltrando agendamenros por mês / ano
            $this->sql ="SELECT
                          count(s.id) total,
                          CONCAT(EXTRACT(YEAR FROM s.data_confirmada),'-',EXTRACT(MONTH FROM s.data_confirmada), '-01')  mes
                        FROM
                          solicitacao_agendamento s
                        WHERE
                            s.data_confirmada > DATE_SUB(now(), INTERVAL 6 MONTH)
                            AND ACEITO = 1
                        group by mes";
            // PREPARANDO A CONSULTA
            $this->prepare();
            $this->executar();
            $arrAgendamentoPorData = $this->buscarDoResultadoAssoc();
            
            // FIltrando agendamenros por usuário
            $this->sql ="SELECT
                          count(s.id) total,
                          u.nome
                        FROM
                          solicitacao_agendamento s
                        INNER JOIN usuario u on u.id = s.usuario_id
                        WHERE ACEITO = 1
                        group by u.nome";
            // PREPARANDO A CONSULTA
            $this->prepare();
            $this->executar();
            $arrAgendamentoPorUsuario = $this->buscarDoResultadoAssoc();
            
            // retornando os dados dos gráficos
            return array(
                            "arrAgendamentoPorAera"=>$arrAgendamentoPorAera,
                            "arrAgendamentoPorData"=>$arrAgendamentoPorData,
                            "arrAgendamentoPorUsuario"=>$arrAgendamentoPorUsuario
                        );
            
        } catch (Exception $ex) { throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Informando qual o usuário é responsável pelo agendamento
     * 
     * @param int $intIdUsuario
     * @param int $intIdAgendamento
     * @throws Exception
     * @return boolean
     */
    function responsavelAgendamento($intIdUsuario, $intIdAgendamento){
        try {
            $this->iniciarTransacao();
            $this->sql ="UPDATE solicitacao_agendamento
                        SET usuario_responsavel = :usuario_responsavel
                        WHERE
                              id = :id ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("usuario_responsavel", $intIdUsuario);
            $this->bind("id", $intIdAgendamento);
            // atualizando o agendamento
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que irá realizar a recusa do agendamento
     * 
     * @param unknown $intIdUsuario
     * @param unknown $intIdAgendamento
     * @throws Exception
     * @return boolean
     */
    function recusarAgendamento($intIdUsuario, $intIdAgendamento){
        try {
            $this->iniciarTransacao();
            $this->sql ="UPDATE solicitacao_agendamento
                        SET usuario_responsavel = :usuario_responsavel,
                            aceito = 2
                        WHERE
                              id = :id ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("usuario_responsavel", $intIdUsuario);
            $this->bind("id", $intIdAgendamento);
            // atualizando o agendamento
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
}
