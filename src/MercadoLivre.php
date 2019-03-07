<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MercadoLivre
 *
 * @author fapachecojr
 */
class MercadoLivre extends Base{
    
    public static $_model = 'mercadolivre';
    
    /*
    *
    * Função de login
    *
    */

    public function index(){

        try {
            $data   = parent::$request->data;
            
            $app_id   = Utils::clear($data->application_id);
            if(!isset($app_id)) throw new Exception("Você deve informar o Application ID.");

            $user_id   = $data->user_id;
            if(!isset($user_id)) throw new Exception("Você deve informar o User ID.");

            $access_token   = $data->access_token;
            if(!isset($access_token)) throw new Exception("Você deve informar o Access Token.");
            
            $internal = new \MercadoLivre\MercadoLivre(['app_id' => $app_id, 'user_id' => $user_id, 'access_token' => $access_token]);
            $dados['user'] = $internal->getInfoUser();
            $dados['app'] = $internal->getInfoApp();
            
            if($dados['user']['status'] == 'ok' && $dados['app']['status'] == 'ok')
            {
                $jsonApp = json_decode($dados['app']['data']);
                $insert = [
                    '_id'             => new MongoId(),
                    'account'         => new MongoId(parent::$account['_id']),
                    'model'           => self::$_model,
                    'user'            => [
                        'app_id'      => $app_id,
                        'use_id'      => $user_id,
                        'access_token'=> $access_token,
                        'code'        => ''
                    ],
                    'dealer_name'   => $jsonApp->company->corporate_name,
                    'sellers'       => [
                        'new'   => 1,
                        'used'  => 1,
                    ],
                    'active' => 1,
                    'create_at' => new MongoDate()
                ];

                // Verifica se pode inserir
                $find = [
                    'account'       => new MongoId(parent::$account['_id']),
                    'model'         => self::$_model,
                    'user.app_id'   => $app_id,
                    'active' => 1
                ];

                if(!parent::$db->accounts_integrations->findOne($find))
                {
                    //Faz inserção
                    parent::$db->accounts_integrations->insert($insert);

                    $action_string = parent::$user['full_name'] . ' ativou essa integração.';
                    $action_link    = '<a href="*|LINK_USER|*" >'. parent::$user['full_name'] . '</a> ativou essa integração.';

                    $push = [
                        'logs' => [
                            '_id'           => new MongoId(),
                            'type'          => 'insert',
                            'user'          => new MongoId(parent::$user['_id']),
                            'action_string' => $action_string,
                            'action_link'   => $action_link,
                            'create_at'     => new MongoDate()
                        ]
                    ];

                    parent::$db->accounts_integrations->update(['_id' => new MongoId($insert['_id'])], ['$push' => $push], ['upsert' => 1]);    
                    
                    Flight::json([
                        'status' => 'ok'
                    ]);
                    die();
                }
                else
                {
                    Flight::json([
                        'status' => 'fail',
                        'message' => 'A integração já existe.'
                    ]);
                    die();
                }
            }
            else
            {
                Flight::json([
                    'status' => 'fail',
                    'message' => 'Verifique os dados informados. Não foi possível realizar a integração.'
                ]);
            }
            
        } catch (Exception $e) {

            Flight::json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
            die();
            
        }
        
    }
    
    public function verifica() {
        try {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
            $data   = parent::$request->data;
            
            $meli = new MercadoLivre\Meli($data->app_id, "L2kfNrrU6RwxLmtQmhCYehd15Et9M4ZV");
            $urlGetCode = $meli->getAuthUrl("https://manager.veloccer.com/integration/mercadolivre/redirect/account/aa11bb22", MercadoLivre\Meli::$AUTH_URL['MLB']);
            echo $urlGetCode;die();
            // create a new cURL resource
            $ch = curl_init();

            // set URL and other appropriate options
            curl_setopt($ch, CURLOPT_URL, $urlGetCode);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            // grab URL and pass it to the browser
            $result = curl_exec($ch);
            
            print_r($result);die();

            // close cURL resource, and free up system resources
            curl_close($ch);
            
            echo $urlGetCode;die();
            echo 'lalala';
            $mCurl = curl_init($urlGetCode);
            curl_setopt($mCurl, CURLOPT_USERAGENT, 'Dalvik/2.1.0 (Linux; U; Android 6.0.1; XT1225 Build/MPGS24.107-70.2-7)');
            curl_setopt($mCurl, CURLOPT_RETURNTRANSFER, 1);
            echo 'bbbb';
            curl_setopt($mCurl, CURLOPT_COOKIEJAR, tempnam('/tmp','cookie-mercadolivre-'.date('YmdHis')));
            $page = curl_exec($mCurl);
            echo 'ccccc';
            print_r($page);die();
            curl_close($mCurl);
            
            print_r($redirectUrl);die();
            $user = $meli->authorize("APP_USR-1452048172760205-030620-045e5535f12efff451aedec1e8c312f4-72447107", "https://manager.veloccer.com");
            
            $access_token = $user['body']->access_token;
            $expires_in = time() + $user['body']->expires_in;
            $refresh_token = $user['body']->refresh_token;
            
            $internal = new \MercadoLivre\MercadoLivre(['app_id' => $data->app_id, 'user_id' => $data->user_id, 'access_token' => $access_token]);
            $dados['user'] = $internal->getInfoUser();
            $dados['app'] = $internal->getInfoApp();
            
            Flight::json([
                'status' => 'ok',
                'dados' => $dados
            ]);
        } catch (Exception $e) {

            Flight::json([
                'status'    => 'fail',
                'message'   => $e->getMessage()
            ]);
        }
       
    }
    
    public function setCode($id, $URI)
    {
        try{
            $content = str_replace("/integration/mercadolivre/redirect/account/".$id."?code=","",$URI);
            $file = filter_input(INPUT_SERVER,"DOCUMENT_ROOT") . "/temp/mercadolivre/account-".$id.".mlb";
            $fp = fopen($file,"wb");
            fwrite($fp,$content);
            fclose($fp);
        } catch (Exception $ex) {
            echo $ex->getMessage();
            die();
        }
    }
}
