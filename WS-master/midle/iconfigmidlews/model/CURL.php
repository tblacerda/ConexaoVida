<?php

class CURL
{
//    public $strURLWS       = "https://192.168.0.15:8443/InfoWSImdlog";
    //public $strURLWS       = "http://192.168.0.75:8081/iconfigws/";
    public $strURLWS       = "http://192.168.0.232/iconfigws/";
    
    private $info;
    
    /**
     * Resource cURL
     *
     * @access private
     * @var resource
     */
    private $ch;

    /**
     * String contains a cookie file
     *
     * @access private
     * @var string
     */
    private $strCookieFile;

    /**
     * Flag of temp cookie file
     *
     * @access private
     * @var boolean
     */
    private $bolCookieTemp;

    /**
     * String contains a User Agent
     *
     * @access private
     * @var string
     */
    private $strUserAgent;

    /**
     * String contains a error menssage
     *
     * @access private
     * @var string
     */
    private $strErrorMessage;

    /**
     * Timeout parameter
     *
     * @access private
     * @var int
     */
    private $intTimeout;

    /**
     * Flag of redirect automatic
     *
     * @access private
     * @var boolean
     */
    private $bolRedirect;

    /**
     * User for autentication
     *
     * @access private
     * @var string
     */
    private $strAuthUser;

    /**
     * Password for autentication
     *
     * @access private
     * @var string
     */
    private $strAuthPass;

    /**
     * Construct method
     *
     * @access public
     */
    public function __construct($intTimeout = 30) {
        // Initializing attributes
        $this->bolCookieTemp = false;
        $this->strCookieFile = "";
        $this->strErrorMessage = "";
        $this->intTimeout = $intTimeout;
        $this->bolRedirect = true;
        $this->strAuthUser = "infows";
        $this->strAuthPass = "Od6PGv06KcdbV6aTK4551q3pJ9Z97Y83";

        // Starting cURL
        $this->ch = curl_init();
    }

    /**
     * Destruct method
     *
     * @access public
     */
    public function __destruct() {
        $this->removeTempCookie();
        curl_close($this->ch);
    }

    /**
     * Send to server data contents to URL via GET
     *
     * @access public
     * @param string $strUrl
     * @param array $arrData=array()
     * @param int $intRetries=1
     * @param boolean $bolIsAspX=false
     * @return string
     */
    public function get($strUrl, $arrData = array(), $intRetries = 1, $bolIsAspX = false) {
        if (!empty($arrData) && is_array($arrData)) {
            $strUrl .= (!strstr($strUrl, "?")) ? "?" : "&";
            $strData = http_build_query($arrData);
            // Replace array data to single line (only ASP.net)
            if ($bolIsAspX)
                $strData = preg_replace("/%5B([0-9]+)%5D=/i", "=", $strData);
            $strUrl .= $strData;
        }

        // Setup cURL for configs
        $this->setup($this->strURLWS . $strUrl);
        
        
        //curl_setopt($this->ch, CURLOPT_POSTFIELDS, array());
        for ($intCount = 1; $intCount <= $intRetries; $intCount++) {
            $strResponse = curl_exec($this->ch);
            
            $this->setInfo(curl_getinfo($this->ch));
            
            if ($strResponse == false)
                $this->strErrorMessage = curl_error($this->ch) . " (" . curl_errno($this->ch) . ")";
            else {
                $this->strErrorMessage = "";
                break;
            }
        }
        
        return $strResponse;
    }

    /**
     * Send to server data contents to URL via POST
     *
     * @access public
     * @param string $strUrl
     * @param array $arrData=array()
     * @param int $intRetries=1
     * @param boolean $bolIsAspX=false
     * @return string
     */
    public function post($strUrl, $arrData = array(), $intRetries = 1, $bolIsAspX = false) {
        // Setup cURL for configs
        $this->setup($this->strURLWS . $strUrl, true);

        // Define POST configuration
        curl_setopt($this->ch, CURLOPT_POST, true);

        if (!empty($arrData) && is_array($arrData)) {
            $bolFileExists = false;
            foreach ($arrData as $strData) {
                if (!is_array($strData) && strpos($strData, "@") === 0) {
                    $bolFileExists = true;
                    break;
                }
            }
            if ($bolFileExists) {
                // Make POST with files, and checking if returns false
                if (!$this->postWithFiles($arrData))
                    return false;
            }
            else {
                $strData = http_build_query($arrData);
                // Replace array data to single line (only ASP.net)
                if ($bolIsAspX)
                    $strData = preg_replace("/%5B([0-9]+)%5D=/i", "=", $strData);
                
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $strData);
                curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            }
        }else{
            
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $arrData);
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($arrData))
            );
            
//            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: Application/json', $arrData));
//            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $arrData);
        }

        for ($intCount = 1; $intCount <= $intRetries; $intCount++) {
            $strResponse = curl_exec($this->ch);
            
            $this->setInfo(curl_getinfo($this->ch));
            
            if ($strResponse == false)
                $this->strErrorMessage = curl_error($this->ch) . " (" . curl_errno($this->ch) . ")";
            else {
                $this->strErrorMessage = "";
                break;
            }
        }
        return $strResponse;
    }

    /**
     * WORKAROUND - To fix Content-Type of uploaded files especified by ";type="
     * This method simulate the use of ";type=" in upload files
     * 
     * @access private
     * @param array $arrData
     * @return boolean
     */
    private function postWithFiles($arrData) {
        $arrHeaders = array();
        $strContents = "";

        // Generate unique ID
        $strUniqueId = substr(md5(uniqid(rand(), true)), 0, 12);
        $strBoundary = "----------------------------" . $strUniqueId;

        foreach ($arrData as $strName => $strData) {
            // Add Boundary
            $strContents .= "--" . $strBoundary . "\r\n";

            // Check if is file and if contents content type tag
            if (preg_match("/@([^;]*)(;type=(.*))?/", $strData, $arrFile)) {
                $strFileName = $arrFile[1];

                // Creating contents
                $bolFileContents = false;
                if (!empty($strFileName)) {
                    if (file_exists($strFileName))
                        $bolFileContents = true;
                    else {
                        $this->strErrorMessage = "failed creating formpost data (file '" . $strFileName . "' not found)";
                        return false;
                    }
                }
                $strContentType = (!empty($arrFile[3])) ? $arrFile[3] : $this->mimeType($arrFile[1]);

                $strContents .= 'Content-Disposition: form-data; name="' . $strName . '"; filename="' . basename($strFileName) . '"' . "\r\n";
                $strContents .= 'Content-Type: ' . $strContentType . "\r\n";
                $strContents .= "\r\n";
                $strContents .= ($bolFileContents) ? file_get_contents($strFileName) : "";
                $strContents .= "\r\n";
                $strFileContents = "";
            } else {
                $strContents .= 'Content-Disposition: form-data; name="' . $strName . '"' . "\r\n";
                $strContents .= "\r\n";
                $strContents .= $strData . "\r\n";
            }
        }
        // Last Boundary
        $strContents .= "--" . $strBoundary . "--\r\n";

        // Overwrite Content-Type header for multipart/form-data - First Boundary
        $arrHeaders[] = "Content-Type: multipart/form-data; boundary=" . $strBoundary;

        // Setup cURL with Headers and Fields
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $arrHeaders);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $strContents);
        return true;
    }

    /**
     * Prepare cURL to POST or GET an URL
     * 
     * @access private
     * @param string $strUrl
     * @return void
     */
    private function setup($strUrl, $bolPost = false) {
        // Starting cURL
        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_URL, $strUrl);
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);

        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADER, false);

        curl_setopt($this->ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($this->ch, CURLOPT_FORBID_REUSE, true);
        curl_setopt($this->ch, CURLOPT_FRESH_CONNECT, true);

        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->getCookieFile());
        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $this->getCookieFile());
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->getUserAgent());
        curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->getTimeout());
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $this->getRedirect());
         
        if(!$bolPost)
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Content-Type: Application/json', @$additionalHeaders));
        
        
        
        // Define Username and Password for autentications
        if (!empty($this->strAuthUser) && !empty($this->strAuthPass)) {
            curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($this->ch, CURLOPT_USERPWD, sprintf("%s:%s", $this->strAuthUser, $this->strAuthPass));
        }
        
    }

    /**
     * Define the cookie file
     * 
     * @access public
     * @param $strCookieFile=""
     * @return void
     */
    public function setCookieFile($strCookieFile = "") {
        // Verificando se o cookie est� vazio para a cria��o de um cookie tempor�rio
        $bolCookieTemp = empty($strCookieFile);
        if ($bolCookieTemp)
            $strCookieFile = tempnam("/tmp", "CURL");

        // Verificando o caminho completo do cookie e retornando caso este seja inv�lido
        $strCookieDir = realpath(dirname($strCookieFile));
        if (empty($strCookieDir))
            return false;
        $strCookieFile = $strCookieDir . "/" . basename($strCookieFile);

        // Removendo o cookie tempor�rio caso exista
        $this->removeTempCookie();

        // Definindo o novo cookie
        $this->bolCookieTemp = $bolCookieTemp;
        $this->strCookieFile = $strCookieFile;
        return true;
    }

    /**
     * Return a value of cookie file
     * 
     * @access public
     * @return string
     */
    public function getCookieFile() {
        if (empty($this->strCookieFile))
            $this->setCookieFile();
        return $this->strCookieFile;
    }

    /**
     * Clear the Cookie Temp File, if exists
     * 
     * @access public
     * @return void
     */
    private function removeTempCookie() {
        if ($this->bolCookieTemp && !empty($this->strCookieFile)) {
            unlink($this->strCookieFile);
            $this->bolCookieTemp = false;
            $this->strCookieFile = "";
        }
    }
    
    public function setInfo($arrInfo) {
        $this->info = $arrInfo;
    }

    public function getInfo() {
        return $this->info;
    }
    
    /**
     * Define an User Agent
     * 
     * @access public
     * @param string $strUserAgent=""
     * @return void
     */
    public function setUserAgent($strUserAgent = "") {
        if (empty($strUserAgent) || $strUserAgent == "IE" || $strUserAgent == "IExplorer" || $strUserAgent == "Internet Explorer") {
            $strUserAgent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en)";
        } else if ($strUserAgent == "FF" || $strUserAgent == "Firefox") {
            $strUserAgent = "Mozilla/5.0 (Windows; U; Windows NT 5.2; en-US; rv:1.8.1.8) Gecko/20071008 Firefox/2.0.0.8";
        }
        $this->strUserAgent = $strUserAgent;
    }

    /**
     * Return a defined User Agent
     *
     * @access public
     * @return string
     */
    public function getUserAgent() {
        if (empty($this->strUserAgent))
            $this->setUserAgent();
        return $this->strUserAgent;
    }

    /**
     * Define timeout for cURL
     *
     * @access public
     * @param int $intTimeout=30
     * @return string
     */
    public function setTimeout($intTimeout = 30) {
        $this->intTimeout = $intTimeout;
        //echo ini_get("max_execution_time");
    }

    /**
     * Return a defined Timeout
     *
     * @access public
     * @return int
     */
    public function getTimeout() {
        return $this->intTimeout;
    }

// Enable or disable redirect
    public function setRedirect($bolRedirect = true) {
        $this->bolRedirect = $bolRedirect;
    }

    public function getRedirect() {
        return $this->bolRedirect;
    }

    public function setAuth($strAuthUser = "", $strAuthPass = "") {
        $this->resetAuth();
        if (!empty($strAuthUser) && !empty($strAuthPass)) {
            $this->strAuthUser = $strAuthUser;
            $this->strAuthPass = $strAuthPass;
        }
    }

    /**
     * Clear username and password for autentication
     *
     * @access public
     * @return void
     */
    public function resetAuth() {
        $this->strAuthUser = "";
        $this->strAuthPass = "";
    }

    /**
     * Get all headers sent in last use of GET / POST
     *
     * @access public
     * @return string
     */
    public function getSentHeaders() {
        return curl_getinfo($this->ch, CURLINFO_HEADER_OUT);
    }

    /**
     * Get error message
     *
     * @access public
     * @return string
     */
    public function getErrorMessage() {
        return $this->strErrorMessage;
    }

    /**
     * Detects the MIME Type based on extension
     *   List of MIME Types provide by "http://snipplr.com/view/1937/array-of-mime-types/"
     *
     * @access private
     * @param string $strFileName
     * @return string
     */
    private function mimeType($strFileName) {
        // Getting extension of filename
        $strExtension = pathinfo($strFileName, PATHINFO_EXTENSION);

        // Finding mime type based on extension
        switch ($strExtension) {
            case "evy" : return "application/envoy";
            case "fif" : return "application/fractals";
            case "spl" : return "application/futuresplash";
            case "hta" : return "application/hta";
            case "acx" : return "application/internet-property-stream";
            case "hqx" : return "application/mac-binhex40";
            case "doc" :
            case "dot" : return "application/msword";
            case "oda" : return "application/oda";
            case "axs" : return "application/olescript";
            case "pdf" : return "application/pdf";
            case "prf" : return "application/pics-rules";
            case "p10" : return "application/pkcs10";
            case "crl" : return "application/pkix-crl";
            case "ai" :
            case "eps" :
            case "ps" : return "application/postscript";
            case "rtf" : return "application/rtf";
            case "setpay" : return "application/set-payment-initiation";
            case "setreg" : return "application/set-registration-initiation";
            case "xla" :
            case "xlc" :
            case "xlm" :
            case "xls" :
            case "xlt" :
            case "xlw" : return "application/vnd.ms-excel";
            case "sst" : return "application/vnd.ms-pkicertstore";
            case "cat" : return "application/vnd.ms-pkiseccat";
            case "stl" : return "application/vnd.ms-pkistl";
            case "pot" :
            case "pps" :
            case "ppt" : return "application/vnd.ms-powerpoint";
            case "mpp" : return "application/vnd.ms-project";
            case "wcm" :
            case "wdb" :
            case "wks" :
            case "wps" : return "application/vnd.ms-works";
            case "hlp" : return "application/winhlp";
            case "bcpio" : return "application/x-bcpio";
            case "cdf" : return "application/x-cdf";
            case "z" : return "application/x-compress";
            case "tgz" : return "application/x-compressed";
            case "cpio" : return "application/x-cpio";
            case "csh" : return "application/x-csh";
            case "dcr" :
            case "dir" :
            case "dxr" : return "application/x-director";
            case "dvi" : return "application/x-dvi";
            case "gtar" : return "application/x-gtar";
            case "gz" : return "application/x-gzip";
            case "hdf" : return "application/x-hdf";
            case "ins" :
            case "isp" : return "application/x-internet-signup";
            case "iii" : return "application/x-iphone";
            case "js" : return "application/x-javascript";
            case "latex" : return "application/x-latex";
            case "mdb" : return "application/x-msaccess";
            case "crd" : return "application/x-mscardfile";
            case "clp" : return "application/x-msclip";
            case "dll" : return "application/x-msdownload";
            case "m13" :
            case "m14" :
            case "mvb" : return "application/x-msmediaview";
            case "wmf" : return "application/x-msmetafile";
            case "mny" : return "application/x-msmoney";
            case "pub" : return "application/x-mspublisher";
            case "scd" : return "application/x-msschedule";
            case "trm" : return "application/x-msterminal";
            case "wri" : return "application/x-mswrite";
            case "pma" :
            case "pmc" :
            case "pml" :
            case "pmr" :
            case "pmw" : return "application/x-perfmon";
            case "p12" :
            case "pfx" : return "application/x-pkcs12";
            case "p7b" :
            case "spc" : return "application/x-pkcs7-certificates";
            case "p7r" : return "application/x-pkcs7-certreqresp";
            case "p7c" :
            case "p7m" : return "application/x-pkcs7-mime";
            case "p7s" : return "application/x-pkcs7-signature";
            case "sh" : return "application/x-sh";
            case "shar" : return "application/x-shar";
            case "sit" : return "application/x-stuffit";
            case "sv4cpio": return "application/x-sv4cpio";
            case "sv4crc" : return "application/x-sv4crc";
            case "tar" : return "application/x-tar";
            case "tcl" : return "application/x-tcl";
            case "tex" : return "application/x-tex";
            case "texi" :
            case "texinfo": return "application/x-texinfo";
            case "roff" :
            case "t" :
            case "tr" : return "application/x-troff";
            case "man" : return "application/x-troff-man";
            case "me" : return "application/x-troff-me";
            case "ms" : return "application/x-troff-ms";
            case "ustar" : return "application/x-ustar";
            case "src" : return "application/x-wais-source";
            case "cer" :
            case "crt" :
            case "der" : return "application/x-x509-ca-cert";
            case "pko" : return "application/ynd.ms-pkipko";
            case "zip" : return "application/zip";
            case "au" :
            case "snd" : return "audio/basic";
            case "mid" :
            case "rmi" : return "audio/mid";
            case "mp3" : return "audio/mpeg";
            case "aif" :
            case "aifc" :
            case "aiff" : return "audio/x-aiff";
            case "m3u" : return "audio/x-mpegurl";
            case "ra" :
            case "ram" : return "audio/x-pn-realaudio";
            case "wav" : return "audio/x-wav";
            case "bmp" : return "image/bmp";
            case "cod" : return "image/cis-cod";
            case "gif" : return "image/gif";
            case "ief" : return "image/ief";
            case "jpe" :
            case "jpeg" :
            case "jpg" : return "image/jpeg";
            case "jfif" : return "image/pipeg";
            case "svg" : return "image/svg+xml";
            case "tif" :
            case "tiff" : return "image/tiff";
            case "ras" : return "image/x-cmu-raster";
            case "cmx" : return "image/x-cmx";
            case "ico" : return "image/x-icon";
            case "pnm" : return "image/x-portable-anymap";
            case "pbm" : return "image/x-portable-bitmap";
            case "pgm" : return "image/x-portable-graymap";
            case "ppm" : return "image/x-portable-pixmap";
            case "rgb" : return "image/x-rgb";
            case "xbm" : return "image/x-xbitmap";
            case "xpm" : return "image/x-xpixmap";
            case "xwd" : return "image/x-xwindowdump";
            case "mht" :
            case "mhtml" :
            case "nws" : return "message/rfc822";
            case "css" : return "text/css";
            case "323" : return "text/h323";
            case "htm" :
            case "html" :
            case "stm" : return "text/html";
            case "uls" : return "text/iuls";
            case "bas" :
            case "c" :
            case "h" :
            case "txt" : return "text/plain";
            case "rtx" : return "text/richtext";
            case "sct" : return "text/scriptlet";
            case "tsv" : return "text/tab-separated-values";
            case "htt" : return "text/webviewhtml";
            case "htc" : return "text/x-component";
            case "etx" : return "text/x-setext";
            case "vcf" : return "text/x-vcard";
            case "mp2" :
            case "mpa" :
            case "mpe" :
            case "mpeg" :
            case "mpg" :
            case "mpv2" : return "video/mpeg";
            case "mov" :
            case "qt" : return "video/quicktime";
            case "lsf" :
            case "lsx" : return "video/x-la-asf";
            case "asf" :
            case "asr" :
            case "asx" : return "video/x-ms-asf";
            case "avi" : return "video/x-msvideo";
            case "movie" : return "video/x-sgi-movie";
            case "flr" :
            case "vrml" :
            case "wrl" :
            case "wrz" :
            case "xaf" :
            case "xof" : return "x-world/x-vrml";
            default : return "application/octet-stream";
        }
    }

    public function processarInputs($strConteudo) {
        // Processando os campos do formul�rio
        preg_match_all("/<input ([^>]*)>/i", $strConteudo, $arrConteudo);

        // Montando o array de post
        $arrPost = array();
        foreach ($arrConteudo[1] as $strCampo) {
            $strNomeCampo = "";
            $strValorCampo = "";
            $strTipoCampo = "";
            if (preg_match('/name="([^"]*)"/i', $strCampo, $arrCampo))
                $strNomeCampo = $arrCampo[1];
            if (preg_match('/value="([^"]*)"/i', $strCampo, $arrCampo))
                $strValorCampo = $arrCampo[1];

            // Montando o array com os valores
            $arrPost[$strNomeCampo] = $strValorCampo;
        }
        return $arrPost;
    }

}

