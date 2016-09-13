<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Mail.ru API Settings
| -------------------------------------------------------------------
*/

$config['app_id'] = '735225';
$config['app_secret_key'] = 'e054912c6ad8699368368fd33a5b5b3e';

//$scope = "offline,notify,friends,photos,audio,video,wall,docs,groups";
$config['scope'] = array('wall,offline,photos,docs');

$config['redirect_uri'] = 'http://localhost/CodeIgniter-3.1.0/mailru';
