<?php
/**
 * Classe com os m�todos padr�es da aplica��o
 * 
 * @author alberto
 */
class Utilidades {
    
    function tiraMoeda($valor) {
        $pontos = array("_", ".");
        $result = str_replace($pontos, "", $valor);

        return $result;
    }
    
    public static function validarEmail($email) {
    
        return filter_var($email, FILTER_VALIDATE_EMAIL);
        //return preg_match('|^[^0-9][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[@][a-zA-Z0-9_]+([.][a-zA-Z0-9_]+)*[.][a-zA-Z]{2,4}$|', $email);
    }
    
    static function validaCPF($cpf = null) {

    	// Verifica se um n�mero foi informado
    	if(empty($cpf)) {
    		return false;
    	}
    
    	// Elimina possivel mascara
    	$cpf = preg_replace("/[^0-9]/", "", $cpf);
    	$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
    	
    	// Verifica se o numero de digitos informados � igual a 11 
    	if (strlen($cpf) != 11) {
    		return false;
    	}
    	// Verifica se nenhuma das sequ�ncias invalidas abaixo 
    	// foi digitada. Caso afirmativo, retorna falso
    	else if ($cpf == '00000000000' || 
    		$cpf == '11111111111' || 
    		$cpf == '22222222222' || 
    		$cpf == '33333333333' || 
    		$cpf == '44444444444' || 
    		$cpf == '55555555555' || 
    		$cpf == '66666666666' || 
    		$cpf == '77777777777' || 
    		$cpf == '88888888888' || 
    		$cpf == '99999999999') {
    		return false;
    	 // Calcula os digitos verificadores para verificar se o
    	 // CPF � v�lido
    	 } else {   
    		
    		for ($t = 9; $t < 11; $t++) {
    			
    			for ($d = 0, $c = 0; $c < $t; $c++) {
    				$d += $cpf{$c} * (($t + 1) - $c);
    			}
    			$d = ((10 * $d) % 11) % 10;
    			if ($cpf{$c} != $d) {
    				return false;
    			}
    		}
    
    		return true;
    	}
    }
    /**
     * Validando data no formato d/m/Y
     * 
     * @param string $data
     * @return boolean
     */
    static function validarData($data) {
        $bolRetorno = true;
        if(empty($data)) $bolRetorno = false;
        $d = DateTime::createFromFormat('d/m/Y', $data);
        if($d && $d->format('d/m/Y') == $data){
            $bolRetorno = true;
        }else{
            $bolRetorno = false;
        }
        return $bolRetorno;
    }
    
    /**
     *  M�todo que ir� enviar as notifica��es
     *  
     * @param array $arrDados
     * @return mixed
     */
    static function enviarNotificacao(array $arrDados){
        // informa��es padr�es
        $arrCampos = array(
            "small_icon" => "http://conexaovidaimip.com.br/logo.png",
            "ic_stat_onesignal_default" => "http://conexaovidaimip.com.br/logo.png",
            'app_id' => "08582d2d-8cb3-4ca3-9c3d-b86be7ec5e8b",
            'data' => array("foo" => "bar")
        );
        
        // Caso n�o tenha sido informado um segmento
        if(!isset($arrDados["include_player_ids"])){
            $arrCampos['included_segments'] = array('All');
        }
        // Setando os campos passados no par�metro
        foreach($arrDados as $strChave => $strValor){
            $arrCampos[$strChave] = $strValor;
        }
        // Padronizando os campos para o envio
        $arrCampos = json_encode($arrCampos);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
            'Authorization: Basic ZTFjNWZjOGQtY2M5NC00NWIxLThmNWItNDQ2MDFhYWI2NGM2'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrCampos);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // Executando o envio
        $response = curl_exec($ch);
        curl_close($ch);
        $objRetorno = json_decode($response);
        return $objRetorno;
    }
    
    /**
     * M�todo que ir� formatar a data para o formato de banco de dados yyy-mm-dd
     * 
     * @param string $strData
     */
    static function formatarDataPraBanco($strData){
        $date = DateTime::createFromFormat('d/m/Y', $strData);
        return $date->format('Y-m-d');
    }
    
    /**
     * M�todo que ir� formatar a data para o formato BR dd/mm/yyyy
     * 
     * @param unknown $strData
     * @param string $strFormt
     * @param string $stFormatReturn
     */
    static function formatarDataPraBr($strData, $strFormt = 'Y-m-d', $stFormatReturn = null){
        $date = DateTime::createFromFormat($strFormt, $strData);
        if($stFormatReturn != null)
            return $date->format($stFormatReturn);
        else
            return $date->format('d/m/Y');
    }
    
    /**
     * M�todo que ira realizar a compara��o entre as tadas
     * 
     * @param unknown $strDataInicio
     * @param unknown $strDataFim
     * @return boolean
     */
    static function diffData($strDataInicio, $strDataFim){
        $datetime1 = new DateTime($strDataInicio);
        $datetime2 = new DateTime($strDataFim);
        return ($datetime1 <= $datetime2);
    }
}
