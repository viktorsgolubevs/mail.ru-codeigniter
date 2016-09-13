<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Mail.ru Authentication - CodeIgniter
 * Copyright 2016 Viktor Golubev
 * @copyright Viktor Golubev
 * @website http://www.viktorsgolubevs.lv
 * @author me@viktorsgolubevs.lv
 * @requires Codeigniter - http://www.codeigniter.com
 */

class Auth_mail {

    const LIBRARY_VERSION = '0.1';

    const AUTH_URL = 'https://connect.mail.ru/oauth/authorize?client_id={client_id}&response_type={response_type}&redirect_uri={redirect_uri}';
    const ACCESS_TOKEN_URL = 'https://connect.mail.ru/oauth/token';
    const METHOD_URL = 'http://www.appsmail.ru/platform/api';

    function __construct()
	{
        //Get an Instance of CodeIgniter
		$this->ci =& get_instance();
        $this->ci->load->config('mail_ru', TRUE);
	}

    private $code;

    /**
     * Token Mail.ru
     * @var string
     */
    private $access_token = null;

    /**
     * Token expire time
     * @var string
     */
    private $expires_token = null;

	public function set_code($code) {
		$this->code = $code;
	}

	public function set_token($token) {
		$this->access_token = $token;
	}
    
    public function set_token_expire($token_expire) {
        $this->expires_token = $token_expire;
    }
    
    public function get_useragent() {
        return 'Mail.Ru API PHP5 Client v. ' . self::LIBRARY_VERSION . ' (curl) ' . phpversion();
    }

	public function redirect($url) {
		header('HTTP/1.1 301 Moved Permanently');
		header("Location:".$url);
		exit();
	}

    /**
     * Create URI for making access to the API
     * @return string
     */
    public function get_code($type='code') {

        is_array($this->ci->config->item('scope', 'mail_ru')) ? $scope = implode(',', $this->ci->config->item('scope', 'mail_ru')) : $scope = $this->ci->config->item('scope', 'mail_ru');

        $url = self::AUTH_URL;

        $url = str_replace('{client_id}', $this->ci->config->item('app_id','mail_ru'), $url);
        $url = str_replace('{response_type}', $type, $url);
        $url = str_replace('{redirect_uri}', $this->ci->config->item('redirect_uri','mail_ru'), $url);

        return $url;
    }

    /**
     * Makes a request to access token
     * Creates a cookie for a token
     * @throws Exception
     * @return string
     */
	public function get_token() {
	   
		if(!$this->code) {
			exit("Wrong code");
		}

        $url = self::ACCESS_TOKEN_URL;
        
        $params = array(
            'client_id'     => $this->ci->config->item('app_id','mail_ru'),
            'client_secret' => $this->ci->config->item('app_secret_key','mail_ru'),
            'grant_type'    => 'authorization_code',
            'code'          => $this->code,
            'redirect_uri'  => $this->ci->config->item('redirect_uri','mail_ru')
        );

        if (function_exists('curl_init')) {

            $curl = curl_init();

            curl_setopt($curl,CURLOPT_URL,$url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            
            $json = curl_exec($curl);
            curl_close($curl);
            
        } else {
           
            die('Curl not available');
        }

        $token = json_decode($json);
        
        if(isset($token->access_token)) {

            $this->set_token($token->access_token);
            $this->set_token_expire($token->expires_in);

            // $expire = 3600 /*  3600 - expire 1 hour */
            //setcookie("access_token", $token->access_token, $expire);

            return TRUE;

        } elseif($token->error) {
            // Session error
            return FALSE;
        }
	}
    
    private function api($method, $params = array()) {
        
        if ($sk = $this->access_token) {
            $params['session_key'] = $sk;
        }
        
        $params = array_merge($params, array(
            'app_id' => $this->ci->config->item('app_id','mail_ru'),
            'secure' => '1',
            'method' => $method,
        ));
        
        $params['sig'] = $this->calculateRequestSignature($params, $this->ci->config->item('app_secret_key','mail_ru'));
        
        $result = $this->get($params);
        
        return $result;
        
        $result = json_decode($result, true);
        
        if (is_array($result) && isset($result['error'])) {
            
            // Error codes
            
            // 1 - Unknown error: Please resubmit the request.
            // 2 - Service unavailable
            // 3 - Service Unavailable. Please try again later.
            // 4 - Method is deprecated
            // 201 - Rate limit Exceeded
            // 301 - Payments disabled
            // 100 - One of the parameters specified is missing or invalid.
            // 102 - Authentication failed
            // 103 - Application lookup failed: the application id is not correct.
            // 104 - Incorrect signature
            // 105 - Application not installed
            // 200 - Insufficient permissions
            // 401 - Incorrect request params
            
        }
        return $result;
    }
    
    private function calculateRequestSignature(array $requestParams, $secretKey) {
        ksort($requestParams);
        $params = '';
        foreach ($requestParams as $key => $value) {
            $params .= "$key=$value";
        }
        return md5($params . $secretKey);
    }
    
    private function get($params) {

        $url = $this->http_build_query($params);

        if (function_exists('curl_init')) {
            $json = $this->get_curl($url);
        } else {
            $json = file_get_contents($url);
        }

        return  json_decode($json, true);
    }
    
    private function get_curl($url) {
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->get_useragent());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    

    /**
     * Make a call Api method
     * @param string $method - method
     * @param array $vars - parameters 
     * @return array - get errors array
     */
    private function api2($method = '', $vars = array(), $access_token = false) {

        if ($access_token) {
            $vars['access_token'] = $this->private_access_token;
        }

        $url = $this->http_build_query($method, $vars);

        return $this->call($url);
    }

    /**
     * Make the final URI for the call
     * @param $method
     * @param string $params
     * @return string
     */
    private function http_build_query($params = array()) {

        return  self::METHOD_URL . '?' . http_build_query($params);
    }

    public function getUser() {
        
        $this->get_token();
        
        $users = $this->api('users.getInfo');
        
        return isset($users[0]) ? array_shift($users) : $users;
    }
    
    public function getUserId() {
        
        $this->get_token();
        
        $users = $this->api('users.getInfo');
        
        return !empty($users[0]) ? $users[0]['uid'] : false;
    }
    
    public function getUsers(array $ids) {
        
        if (count($ids) >= 200) {
            return 'You have exceeded limit for users in one users.getInfo call: 200';
        }
        
        return $users = $this->api('users.getInfo', array('uids' => join(',', $ids)));
    }
    
    /**
     * @return bool
     */
    public function isAppUser($id) {
        $result = $this->api('users.isAppUser', array('uid' => $id));
        return isset($result['isAppUser']) && $result['isAppUser'] == 1;
    }
    
    /**
     * @return array of uids
     */
    public function getFriendsIds($id, $returnOnlyApplicationUsers = false) {
        if ($returnOnlyApplicationUsers) {
            return $this->api('friends.getAppUsers', array('uid' => $id));
        } else {
            return $this->api('friends.get', array('uid' => $id));
        }
    }
    /**
     * @return array of MailRu_IUser
     */
    public function getFriends($id, $returnOnlyApplicationUsers = false) {
        if ($returnOnlyApplicationUsers) {
            $users = $this->api('friends.getAppUsers', array('uid' => $id, 'ext' => 1));
        } else {
            $users = $this->api('friends.get', array('uid' => $id, 'ext' => 1));
        }
        return $users;
    }
    
    /**
     * @return bool
     */
    public function hasPermission($id, $permissionName) {
        $result = $this->api('users.hasAppPermission', array('uid' => $id, 'ext_perm' => $permissionName));
        return in_array($permissionName, array_keys(array_filter($result)));
    }
    
    public function streamPost($params = array()) {
    
        //text
        //title
        //link1_text
        //link1_href
        //img_url
        
        $this->get_token();
        
        return $users = $this->api('stream.post', $params);
    }
    
    public function guestbookPost($params = array()) {
    
        $this->get_token();
        
        return $users = $this->api('guestbook.post', $params);
    }
    
    public function messagesPost($params = array()) {
    
        // uid - User id
        // message - Message (plaintext)
    
        $this->get_token();
        
        return $users = $this->api('messages.post', $params);
    }

}
?>