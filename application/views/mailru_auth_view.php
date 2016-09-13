<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Mail.ru Authentication - CodeIgniter</title>

	<style type="text/css">
        body {
            background: #168de2;
            font-family: arial;
        }
        *::-moz-selection {
            background-color: #e13300;
            color: white;
        }
        .auth-wrap {
            padding: 20px 0;
            text-align: center;
        }
        .auth-wrap .auth-block {
            background-color: #ffffff;
            max-width: 450px;
            margin: 20px auto 0 auto;
            padding: 25px 10px;
            box-sizing: border-box;
            border-radius: 6px;
        }
        .auth-wrap .auth-block .avatar {
            margin-bottom: 10px;
        }
        .auth-wrap .auth-block .avatar img {
            border-radius: 50%;
        }
        .auth-wrap .auth-block pre {
            text-align: left;
            overflow-x: scroll;
            background-color: #efefef;
            font-family: Consolas,Monaco,Courier New,Courier,monospace;
        }
        .auth-wrap .auth-block .user-names {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .auth-wrap .auth-block .btn {
            display: inline-block;
            padding: 10px 25px;
            border-radius: 10px;
        }
        .auth-wrap .auth-block a.btn {
            color: #fff;
            text-decoration: none;
        }
        .auth-wrap .auth-block .btn.blue {
            background: #168de2;
        }
        .auth-wrap .auth-block .btn.red {
            background: #f00808;
        }
	</style>
</head>
<body>

<div class="auth-wrap">
    <div>
        <img src="https://limg.imgsmail.ru/splash/v/i/logo_wide-b41947b93e.v5.png" alt="" title="">
    </div>
    
    <div class="auth-block">
    
        <?php $session_data = $this->session->userdata('user_data'); ?>
    
        <?php if (empty($session_data)): ?>
        
            <a href="<?php echo $auth_link; ?>" class="btn blue">Connect Mail.ru</a>
        
            <pre><?php echo $auth_link; ?></pre>
        
        <?php else: ?>
        
            <?php if (isset($session_data['pic_190'])): ?>
            <div class="avatar">
                <img src="<?php echo $session_data['pic_190']; ?>" alt="" title="">
            </div>
            <?php endif; ?>
        
            <?php if (isset($session_data['first_name']) || isset($session_data['last_name'])): ?>
            <div class="user-names">
            <?php echo !empty($session_data['first_name']) ? $session_data['first_name'] : ''; ?>
            <?php echo !empty($session_data['last_name']) ? $session_data['last_name'] : ''; ?>
            </div>
            <?php endif; ?>
        
            <a href="<?php echo site_url('mailru/logout'); ?>" class="btn red">Logout</a>
        
            <pre><?php print_r($session_data); ?></pre>
    
        <?php endif; ?>
    
    </div>
</div>

</body>
</html>