<?php

namespace MercadoLivre;

/**
 * MercadoLivre API v1.
 *
 * TERMS OF USE:
 * - This code is in no way affiliated with, authorized, maintained, sponsored
 *   or endorsed by MercadoLivre or any of its affiliates or subsidiaries. This is
 *   an independent and unofficial API. Use at your own risk.
 * - We do NOT support or tolerate anyone who wants to use this API to send spam
 *   or commit other online crimes.
 *
 */
class MercadoLivre {

    /**
     * Rest url
     *
     * @var string
     * */
    protected $_url = 'https://api.mercadolibre.com/';
    
    protected $mCurl = null;

    protected $_userAgent = 'Dalvik/2.1.0 (Linux; U; Android 6.0.1; XT1225 Build/MPGS24.107-70.2-7)';    
    
    /**
     * config to all requests
     *
     * @var array
     * */
    private static $cfg = [];
    
    
    private $cookieJar = null;

    public function __construct($data = null) {


        if (empty($data))
            throw new Exception("Empty data in __construct");

        if (is_array($data)) {

            if (isset($data['app_id'])) {

                if (empty($data['app_id']))
                    throw new Exception("Empty data[Application ID]");
                if (empty($data['user_id']))
                    throw new Exception("Empty data[User ID]");
                if (empty($data['access_token']))
                    throw new Exception("Empty data[Access Token]");

                $this->cookieJar = tempnam('/tmp','cookie-mercado-livre-'.date('YmdHis'));
                
                self::$cfg['app_id'] = $data['app_id'];
                self::$cfg['user_id'] = $data['user_id'];
                self::$cfg['access_token'] = $data['access_token'];

            } else {
                throw new Exception("Error data in __construct");
            }
        }
    }
    
    public function getInfoUser()
    {
        $_request = $this->request("https://api.mercadolibre.com/users/".self::$cfg['user_id']."/")
                ->addHeader('Accept', 'application/json')
                ->getResponse();
        
        if($_request['status'] == 'ok')
        {
            return array(
                "status" => "ok",
                "nickname" => $_request['body']['nickname'],
                "id" => $_request['body']['id'],
                "permalink" => $_request['body']['permalink']
            );
        }
        else
        {
            return array("status"=>"fail","message"=>"API não encontro dados com o User ID informado.");
        }
    }
    
    public function getInfoApp()
    {         
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $this->_url."users/me?access_token=".self::$cfg['access_token']."&_MELI_SDK_RANDOM=0.05539141156116326",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          return array(
              'status' => "fail",
              'message' => "cURL Error #:" . $err
          );
        } else {
          return array(
              'status' => "ok",
              'data' => $response
          );
        }
    }
//
//    public function login() {
//        $this->mCurl = curl_init($this->_url .'restrito.index/index');
//        curl_setopt($this->mCurl, CURLOPT_USERAGENT, $this->_userAgent);
//        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($this->mCurl, CURLOPT_COOKIEJAR, $this->cookieJar);
//        curl_setopt($this->mCurl, CURLOPT_POSTFIELDS, 'email_usuario='.self::$cfg['email'].'&senha_usuario='.self::$cfg['password']);
//        $page = curl_exec($this->mCurl);
//        curl_close($this->mCurl);
//    }
//    
//    public function getMeusDados()
//    {            
//        $this->mCurl = curl_init($this->_url . 'restrito.alterardados');
//        curl_setopt($this->mCurl, CURLOPT_USERAGENT, $this->_userAgent);
//        curl_setopt($this->mCurl, CURLOPT_RETURNTRANSFER, 1);
//        curl_setopt($this->mCurl, CURLOPT_COOKIEFILE, $this->cookieJar);
//        $page = curl_exec($this->mCurl);
//        curl_close($this->mCurl);
//
//        //DOM Resp
//        $oDom = new \MercadoLivre\simple_html_dom();
//        $oDom->load($page);
//
//            $dados = [
//                'nome' => $oDom->find('[name="data[Usuario][email]]"', 0)->value,
//                'nome_fantasia' => $oDom->find('[nome_fantasia]', 0)->value,
//                'nome_cpf' => $oDom->find('[nome_cpf]', 0)->value,
//                'telefone_fixo' => $oDom->find('[name="telefone_fixo"]', 0)->value,
//                'telefone_whatsapp' => $oDom->find('[name="telefone_whatsapp"]', 0)->value,
//                'cliente_nome_celular1' => $oDom->find('[name="cliente_nome_celular1"]', 0)->value,
//                'cliente_nome_celular2' => $oDom->find('[name="cliente_nome_celular2"]', 0)->value,
//                'cliente_nome_nextel_fax' => $oDom->find('[name="cliente_nome_nextel_fax"]', 0)->value,
//                'cliente_cep' => $oDom->find('[name="cliente_cep"]', 0)->value,
//                'cliente_endereco' => $oDom->find('[name="cliente_endereco"]', 0)->value,
//                'cliente_numero' => $oDom->find('[name="cliente_numero"]', 0)->value,
//                'cliente_complemento' => $oDom->find('[name="cliente_complemento"]', 0)->value,
//                'cliente_bairro' => $oDom->find('[name="cliente_bairro"]', 0)->value
//            ];
//       
//        return $dados;
//    }
//
    public function removeCookieJar() {
        //unlink($this->cookieJar) or die("Can't unlink ".$this->cookieJar);
    }

    /**
     *
     * Used internally, but can also be used by end-users if they want
     * to create completely custom API queries without modifying this library.
     *
     * @param string $url
     *
     * @return array
     */
    public function request($url) {
        return new Request($this, $url);
    }
}
