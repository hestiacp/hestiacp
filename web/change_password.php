<?php


define('VESTA_DIR',  dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);
define('V_ROOT_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR . 'vesta' . DIRECTORY_SEPARATOR);

require_once V_ROOT_DIR . 'config/Config.class.php';
require_once V_ROOT_DIR . 'core/utils/Utils.class.php';
require_once V_ROOT_DIR . 'core/VestaSession.class.php';
require_once V_ROOT_DIR . 'core/Vesta.class.php';
require_once V_ROOT_DIR . 'core/exceptions/SystemException.class.php';
require_once V_ROOT_DIR . 'core/exceptions/ProtectionException.class.php';
require_once V_ROOT_DIR . 'core/utils/Message.class.php';
require_once V_ROOT_DIR . 'core/Request.class.php';
require_once V_ROOT_DIR . 'api/AjaxHandler.php';
require_once V_ROOT_DIR . 'api/MAIN.class.php';


class ChangePassword
{

    public function dispatch()
    {
        if (empty($_GET['v'])) {
            return $this->renderError('General error');
        }
        
        $key = addslashes(htmlspecialchars($_GET['v']));

        $cmd = Config::get('sudo_path')." ".Config::get('vesta_functions_path').Vesta::V_LIST_SYS_USERS." 'json'";
        exec($cmd, $output, $return);
 
        $users = json_decode(implode('', $output), true);

        $email_matched_count = array();
        
        foreach ($users as $username => $user) {           
            if ($user['RKEY'] == trim($key)) {
                $email_matched_count[] = array_merge(array('USERNAME' => $username), $user);
            }
        }

        if (isset($_POST['action']) && $_POST['action'] == 'change') {
            return $this->doChangePassword($email_matched_count);
        }
        
        return $this->showResetForm();
    }
    
    protected function doChangePassword($users)
    {
        if ($_POST['secret_code'] != $_POST['confirm_secret_code']) {
            return $this->showResetForm('Passwords don\'t match');
        }
        
        if (strlen($_POST['secret_code']) < 6) {
            return $this->showResetForm('Passwords is too short');
        }
        
        if (strlen($_POST['secret_code']) > 255) {
            return $this->showResetForm('Passwords is too long');
        }
        
        $success = true;
        foreach ($users as $user) {
            $cmd = Config::get('sudo_path')." ".Config::get('vesta_functions_path').Vesta::V_CHANGE_SYS_USER_PASSWORD." ".$user['USERNAME']." ".$_POST['secret_code'];
            exec($cmd, $output, $return);

            if (!$return) {
                $success = false;
            }
        }
        
        if (!$success) {
            $main = new MAIN();
            $about = json_decode($main->aboutExecute(), TRUE);
    
            return $this->showResetForm('Something went wrong. Please contact support: '.$about['data']['company_email']);
        }
        
        return $this->showSuccessTpl();
    }
    
    public function showSuccessTpl()
    {
        $main = new MAIN();
        $about = json_decode($main->aboutExecute(), TRUE);
        $current_year = date("Y");      

        print <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
          <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
    <head>
        <title>Vesta Control Panel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="imagetoolbar" content="false" />
        
        <link rel="shortcut icon" href="images/fav.ico" type="image/x-icon">
        <link rel="stylesheet" media="all" type="text/css" href="css/reset2.css" />
        <link rel="stylesheet" media="all" type="text/css" href="css/main.css" />        
        <link rel="stylesheet" media="all" type="text/css" href="css/vesta-login-form.css" />

        <!--[if lt IE 8]>
            <link rel="stylesheet" type="text/css" href="http://dl.dropbox.com/u/1750887/projects/vesta2/css/ie.css" />
        <![endif]-->
    </head>

    <body class="page-auth">
            <div id="change-psw-block" class="page2">
                <div class="b-auth-form">
                    <div class="b-auth-form-wrap">
                        <img width="72" height="24" alt="" src="/images/vesta-logo-2011-12-14.png" class="vesta-logo">
            <span style="color: #5E696B; float: right; margin-top: -48px;">{$about['data']['version_name']}</span>
                        <div class="b-client-title">
            <span class="client-title-wrap">Control Panel<i class="planets">&nbsp;</i></span>
                        </div>
                        <form id="change_psw-form" method="post" action="" class="auth">
                <input type="hidden" value="change" name="action">

                            <div class="success-box" id="change-psw-success">Password successfully changed.</div>

                        </form>
                        <p class="forgot-pwd"><a href="/" class="forgot-pwd-url">Back to login?</a></p>
                        <div class="footnotes cc">
                        <p class="additional-info">For questions please contact <a href="mailto:{$about['data']['company_email']}" class="questions-url">{$about['data']['company_email']}</a></p> 
                        <address class="imprint">&copy; {$current_year} Vesta Control Panel</address>
                        </div>
                    </div>
                 </div>
            </div>
    </body>
</html>

HTML;

    }
    
    public function showResetForm($error_msg = '')
    {
        if (!empty($error_msg)) {
            $error_msg = '<div class="error-box" id="auth-error">'.$error_msg.'</div>';
        }

        $main = new MAIN();
        $about = json_decode($main->aboutExecute(), TRUE);

        $current_year = date("Y");      

        print <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
          <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru">
    <head>
        <title>Vesta Control Panel</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="imagetoolbar" content="false" />
        
        <link rel="shortcut icon" href="images/fav.ico" type="image/x-icon">
        <link rel="stylesheet" media="all" type="text/css" href="css/reset2.css" />
        <link rel="stylesheet" media="all" type="text/css" href="css/main.css" />        
        <link rel="stylesheet" media="all" type="text/css" href="css/vesta-login-form.css" />

        <!--[if lt IE 8]>
            <link rel="stylesheet" type="text/css" href="http://dl.dropbox.com/u/1750887/projects/vesta2/css/ie.css" />
        <![endif]-->
    </head>

    <body class="page-auth">
            <div id="change-psw-block" class="page2">
                <div class="b-auth-form">
                    <div class="b-auth-form-wrap">
                        <a href="/">
                        <img width="72" height="24" alt="" src="/images/vesta-logo-2011-12-14.png" class="vesta-logo">
                        </a>
          <span style="color: #5E696B; float: right; margin-top: -48px;">{$about['data']['version_name']}</span>
                        <div class="b-client-title">
            <span class="client-title-wrap">Control Panel<i class="planets">&nbsp;</i></span>
                        </div>
                        <form id="change_psw-form" method="post" action="" class="auth">
                        <input type="hidden" value="change" name="action">

                            <div class="form-row cc">
                                <label for="password" class="field-label">New Password</label>
                                <input type="password" tabindex="1" id="password" class="field-text" name="secret_code">
                            </div>

                            <div class="form-row cc">
                                <label for="confirm_password" class="field-label">ONE MORE TIME</label>
                                <input type="password" tabindex="1" id="confirm_password" class="field-text" name="confirm_secret_code">
                            </div>
                            {$error_msg}
                            <div class="form-row cc last-row">
                                <input type="submit" tabindex="4" value="Change Password" class="sumbit-btn">
                            </div>
                        </form>

                        <p class="forgot-pwd"><a href="/" class="forgot-pwd-url">Back to login?</a></p>\

                        <div class="footnotes cc">
                        <p class="additional-info">For questions please contact <a href="mailto:{$about['data']['company_email']}" class="questions-url">{$about['data']['company_email']}</a></p>
                        <address class="imprint">&copy; {$current_year} Vesta Control Panel</address>
                        </div>
                    </div>
                 </div>
            </div>
    </body>
</html>
HTML;
    }
    
    public function renderError($message)
    {
        print <<<HTML
            {$message}
HTML;
    
    }
}

$changePassword = new ChangePassword();
$changePassword->dispatch();

?>