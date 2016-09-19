<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mailru extends CI_Controller {
    
    /**
     * Mail.ru Authentication - CodeIgniter
     * Copyright 2016 Viktor Golubev
     * @copyright Viktor Golubev
     * @author me@viktorsgolubevs.lv
     * @requires Codeigniter - http://www.codeigniter.com
     */

	/**
	 * Mail.ru OAuth api
	 * @see http://api.mail.ru/docs/guides/oauth/sites/
     * For application registration
     * @see http://api.mail.ru/sites/my/add
	 */
    public function __construct()
    {
		parent::__construct();

        // To use site_url and redirect on this controller.
        $this->load->helper('url');
        
        // Load library
        // Load Mail.ru library
        // Automatically picks appId and secret from config
		$this->load->library('auth_mail');
        $this->load->library('session');
        
        $this->data = array();
	}
     
	public function index()
	{
       $this->connect();
	}
    
    public function connect()
    {
        if (!isset($_GET['code']))
        {
            $this->data['auth_link'] = $this->auth_mail->get_code();
        }

        if (isset($_GET['code']))
        {
            $this->auth_mail->set_code($_GET['code']);
            
            $user_data = $this->auth_mail->getUser();
        
            $this->session->set_userdata('user_data', $user_data);
        }
        
        $this->load->view('mailru_auth_view', $this->data);
    }
    
    public function logout()
    {
        $this->session->set_userdata(array('user_data' => ''));
        
		$this->session->sess_destroy();
        
        redirect('mailru');
    }
}
