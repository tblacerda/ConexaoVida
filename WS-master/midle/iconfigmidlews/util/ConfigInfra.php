<?php
 
/******************************************************************************
 Nome do Arquivo   : ConfigInfra.php
 Descrição         : Classe que carrega o arquivo de configuração
 Programador       : José Gabriel
 CRC               : 49682	
 Data              : 19/01/2015
 Diretório         : ./util/
 Alteração  : Nome - Data - numero crc
              # Descrição das alteração...
******************************************************************************/

class ConfigInfra {
    private $arquivoIni;
    
    public function __construct() {
        $this->arquivoIni = $this->carregarConfig();
    }
    
    private function carregarConfig(){
        return parse_ini_file(__DIR__."/../config.ini", TRUE);
    }
    
    public function getArquivoIni() {
        return $this->arquivoIni;
    }


}
