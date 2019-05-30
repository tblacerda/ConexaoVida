<?php
/**
 * Dao Padrão dos exames
 */

require_once(__DIR__ . '../../control/Exame.php');
require_once(__DIR__ . '/dao.php');

/**
 * Description of daoExame
 *
 * @author Régis Perez
 */
class daoExame extends Dao {


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
                        FROM area
                        WHERE
                            1 = 1
                            ";
            // Caso haja a limitação para buscar somente algumas áreas            
            if(isset($_GET["todos"]) && $_GET["todos"] != "true")
                $this->sql .= " AND excluido = 0";
                
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá listar os tipos de exames
     * 
     * @return mixed
     */
    function listarTiposExames(){
        try {
            // Filtrando todos os cancers
            $this->sql ="SELECT
                          *
                        FROM tipo_exame";
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
    function listarTotalExamePorArea(){
        try {
            $this->sql ="SELECT
                          a.descricao,
                          count(e.id) total
                        FROM
                          `exame` e
                        INNER JOIN area a on a.id = e.area_id
                        WHERE e.data_recebimento is null
                        GROUP BY a.descricao";
            $this->prepare();
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc();
        } catch (Exception $ex) { }
    }
    
    /**
     * Método que irá listar o total dos exames pelo tipo
     * 
     * @return mixed
     */
    function listarTotalExamePorTipoExame(){
        try {
            $this->sql ="SELECT
                          te.descricao,
                          count(e.id) total
                        FROM
                          `exame` e
                        INNER JOIN tipo_exame te on te.id = e.tipo_exame_id
                        WHERE e.data_recebimento is null
                        GROUP BY te.descricao";
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
     * Método que irá cadastrar o exame
     * 
     * @param stdClass $objExame
     * @throws Exception
     * @return boolean
     */
    function cadastrarExame(stdClass &$objExame){
        try {
            $this->iniciarTransacao();
            $this->sql ="INSERT INTO exame
                        (
                            data_exame, 
                            data_previsao,
                            usuario_id,
                            tipo_exame_id,
                            area_id
                        )
                        VALUES
                        (
                            :data_exame, 
                            :data_previsao,
                            :usuario_id,
                            :tipo_exame_id,
                            :area_id
                        )
                        ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_exame", $objExame->data_exame);
            $this->bind("data_previsao", Utilidades::formatarDataPraBanco($objExame->data_previsao));
            $this->bind("data_exame", Utilidades::formatarDataPraBanco($objExame->data_exame));
            $this->bind("usuario_id", $objExame->usuario_id);
            $this->bind("tipo_exame_id", $objExame->tipo_exame_id);
            $this->bind("area_id", $objExame->area_id);            
            
            // Recuperando o id do exame cadastrado
            $this->executar();
            // Recuperar id do exame
            $objExame->id = $this->retornarUltimoIDInserido();
            $this->comitarTransacao();
            // Verificando se houve altera��es
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que ira realizar o recebimento do exame
     * 
     * @param stdClass $objExame
     * @throws Exception
     * @return boolean
     */
    function confirmarRecebimento(stdClass &$objExame){
        try {
            $objExame->data_recebimento = Utilidades::formatarDataPraBanco($objExame->data_recebimento);
            $this->iniciarTransacao();
            $this->sql ="UPDATE exame
                        SET data_recebimento = :data_recebimento
                        WHERE 
                              id = :id ";
            // Preparando a consulta
            $this->prepare();
            // Realizando os bids para segurança
            $this->bind("data_recebimento", $objExame->data_recebimento);
            $this->bind("id", $objExame->id);
            // Recuperando o id do exame cadastrado
            $this->executar();
            $this->comitarTransacao();
            // Verificando se houve alterações
            return ($this->rowCount() > 0);
        } catch (Exception $ex) {$this->desfazerTransacao(); throw new Exception($ex->getMessage(), 9999); }
    }
    
    /**
     * Método que irá retornar o exame pelo id
     * 
     * @param unknown $intIdExame
     * @return mixed
     */
    function getExamePorId($intIdExame){
        try {
            $intIdExame = (int) $intIdExame;
            // Filtrando todos os cancers
            $this->sql ="SELECT
                        	e.*,
                        	a.descricao as area,
                        	tp.descricao as tipo_exame,
                        	u.nome,
                        	u.contato,
                        	u.contato_dois,
                        	u.numero_pep,
                        	CASE
                        	  WHEN data_recebimento IS NULL THEN 0
                        	  ELSE 1
                        	END AS situacao,
                        	TIMESTAMPDIFF(DAY,data_exame,
                        		(CASE
                        		  WHEN data_recebimento IS NOT NULL THEN data_recebimento
                        		  ELSE NOW()
                        		END)
                        	) dias_atraso
                         FROM exame e
                         INNER JOIN area a on a.id = e.area_id
                         INNER JOIN tipo_exame tp on tp.id = e.tipo_exame_id
                         INNER JOIN usuario u on u.id = e.usuario_id
                         WHERE e.id = :id ";
            $this->prepare();
            $this->bind("id", $intIdExame);
            $this->executar();
            // Retornando a lista de cancer
            return $this->buscarDoResultadoAssoc(true);
        } catch (Exception $ex) { }
    }

    /**
     * Método que irá retornar os exames pelo id do paciente (usuario)
     * 
     * @param int $intIdUsuario
     * @throws Exception
     * @return mixed
     */
    function listarExamesDoPaciente($intIdUsuario){
        try {
            // Realizando um cast para garantir a integridade
            $intIdUsuario = (int) $intIdUsuario;
            $this->sql ="SELECT
                            e.*,
                            a.descricao as area,
                            tp.descricao as tipo_exame,
                            CASE
                              WHEN data_recebimento IS NULL THEN 0
                              ELSE 1
                            END AS situacao,
                            (
                            SELECT
                                ROUND(SUM(TIMESTAMPDIFF(DAY,data_exame,data_recebimento)) / COUNT(ID), 0)
                             FROM 
                                exame 
                             WHERE 
                                tipo_exame_id = e.tipo_exame_id 
                                AND data_exame >= NOW() - INTERVAL 120 DAY
                                AND data_recebimento is not null
                           ) AS mediaCalculada
                         FROM exame e
                         INNER JOIN area a on a.id = e.area_id
                         INNER JOIN tipo_exame tp on tp.id = e.tipo_exame_id
                         WHERE
                            e.usuario_id = :usuario_id 
                         ORDER BY
                            (CASE
                              WHEN data_recebimento IS NULL THEN 0
                              ELSE 1
                            END) ASC,
                            e.data_exame ASC,
                            e.data_previsao ASC ";
            $this->prepare();
            $this->bind("usuario_id", $intIdUsuario);
            $this->executar();
            $arrExames = $this->buscarDoResultadoAssoc();
            if(empty($arrExames)) throw new Exception("Exames não foram encontrados!");
            // Para cada exame 
            foreach($arrExames as $intChave => $exames){
                // Formatando as fatas
                $arrExames[$intChave]["data_exame"] = Utilidades::formatarDataPraBr($exames["data_exame"]);
                $arrExames[$intChave]["data_previsao"] = Utilidades::formatarDataPraBr($exames["data_previsao"]);
                // Se a data do recebimento não for vazia
                if(!empty($exames["data_recebimento"]))
                    $arrExames[$intChave]["data_recebimento"] = Utilidades::formatarDataPraBr($exames["data_recebimento"]);
            }
            // Retornando os exames do paciente
            return $arrExames;
        } catch (Exception $ex) { }
    }

    /**
     * M�todo que ir� retornar os exames filtrados
     * 
     * @param int $intIdArea,$intIdTipoExame,$intPep
     * @throws Exception
     * @return mixed
     */
    function filtrarExames(array $arrDados){
        //filtra os exames de um determinado pep
        try{
            $this->sql ="SELECT
                            e.*,
                            u.nome,
                            TIMESTAMPDIFF(DAY,data_exame,
                            (CASE
                              WHEN data_recebimento IS NOT NULL THEN data_recebimento
                              ELSE NOW()
                            END)
                            ) dias_atraso
                        FROM exame e
                        INNER JOIN usuario u ON e.usuario_id = u.id
                        WHERE
                             1 = 1  ";
            
            /***** FILTROS CASO INFORMADOS ******/
            if(isset($arrDados["area_id"]) && !empty($arrDados["area_id"]))
                $this->sql .= " AND e.area_id = :area_id";
            
            if(isset($arrDados["situacao"]) && $arrDados["situacao"] != ""){
                $strSituacao = ($arrDados["situacao"] == 0) ? " is null " : " is not null ";
                $this->sql .= " AND data_recebimento {$strSituacao}";
            }
            
            if(isset($arrDados["tipo_exame_id"]) && !empty($arrDados["tipo_exame_id"]))
                $this->sql .= " AND e.tipo_exame_id = :tipo_exame_id";
            
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                $this->sql .= " AND u.numero_pep= :numero_pep";
            
            $this->sql .= "   
                  ORDER BY
                    TIMESTAMPDIFF(DAY,data_exame,
                    (CASE
                        WHEN data_recebimento IS NOT NULL THEN data_recebimento
                        ELSE NOW()
                        END)
                    ) DESC";
            // PREPARANDO A CONSULTA
            $this->prepare();
            /***** BIND NOS VALORES DOS FILTROS ******/
            if(isset($arrDados["area_id"]) && !empty($arrDados["area_id"]))
                $this->bind("area_id", $arrDados["area_id"]);
            
            if(isset($arrDados["tipo_exame_id"]) && !empty($arrDados["tipo_exame_id"]))
                 $this->bind("tipo_exame_id", $arrDados["tipo_exame_id"]);
        
            if(isset($arrDados["pep"]) && !empty($arrDados["pep"]))
                 $this->bind("numero_pep", $arrDados["pep"]);
            // EXECUTANDO A CONSULTA
            $this->executar();
            $arrExames = $this->buscarDoResultadoAssoc();
            if(empty($arrExames)) throw new Exception("Exames não foram encontrados!");
            // Retornando os exames filtrados
            return $arrExames;
        } catch (Exception $ex) { throw new Exception($ex->getMessage()); }
    } 
}
