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


class ChangePassword
{

    public function dispatch()
    {
        if (empty($_GET['v'])) {
            return $this->renderError('General error');
        }
        
        $key = addslashes(htmlspecialchars($_GET['v']));

        $users = Vesta::execute(Vesta::V_LIST_SYS_USERS, 'json');
        $email_matched_count = array();
        
        /*if (strcmp($real_key, $key_sha1) != 0) {
            return $this->renderError('Invalid keys');      
        }*/
        
        foreach ($users['data'] as $username => $user) {           
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
            $rs = Vesta::execute(Vesta::V_CHANGE_SYS_USER_PASSWORD, array('USER' => $user['USERNAME'], 
                                                                             'PASSWORD' => $_POST['secret_code']));
            if (!$rs) {
                $success = false;
            }
        }
        
        if (!$success) {
            return $this->showResetForm('Something went wrong. Please contact support.');
        }
        
        return $this->showSuccessTpl();
    }
    
    public function showSuccessTpl()
    {
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
            <span style="color: #5E696B; float: right; margin-top: -48px;">~!:VERSION~!</span>
                        <div class="b-client-title">
            <span class="client-title-wrap">Control Panel<i class="planets">&nbsp;</i></span>
                        </div>
                        <form id="change_psw-form" method="post" action="" class="auth">
                <input type="hidden" value="change" name="action">

                            <div class="success-box" id="change-psw-success">Password successfully changed.</div>

                        </form>
                        <p class="forgot-pwd">&nbsp;</p>
                        <div class="footnotes cc">
                        <p class="additional-info">For questions please contact <a href="mailto:info@vestacp.com" class="questions-url">info@vestacp.com</a></p>
                        <address class="imprint">&copy; 2011 Vesta Control Panel</address>
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
            $error_msg = '<i>'.$error_msg.'</i>';
        }
        
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
            <span style="color: #5E696B; float: right; margin-top: -48px;">~!:VERSION~!</span>
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

                            <div class="form-row cc last-row">
                                <input type="submit" tabindex="4" value="Change Password" class="sumbit-btn">
                            </div>
                        </form>
                        <p class="forgot-pwd">&nbsp;</p>
                        <div class="footnotes cc">
                        <p class="additional-info">For questions please contact <a href="mailto:info@vestacp.com" class="questions-url">info@vestacp.com</a></p>
                        <address class="imprint">&copy; 2011 Vesta Control Panel</address>
                        </div>
                    </div>
                 </div>
            </div>
    </body>
</html>

<!--

            <center>
            vesta password reset form 
            <hr />
            {$error_msg}
            <form action="" method="POST">
                <table>
                    <tr>
                        <td>
                            <input type="hidden" name="action" value="change" />
                            <label>Enter secret code:</label>
                        </td>
                        <td>
                            <input type="password" name="secret_code" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label>Enter new password:</label>
                        </td>
                        <td>
                            <input type="password" name="confirm_secret_code" value="" />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" name="Apply" />
                        </td>
                    </tr>
                </table>
            </form>
            </center> --> 

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
