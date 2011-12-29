<?php


/**
 * Change password functionality
 */
class ChangePassword
{

    public function dispatch()
    {
        //print_r($_SERVER);
        if (empty($_GET['v'])) {
            return $this->renderError('General error');
        }
        
        $key = $_GET['v'];
        $real_key = sha1($_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR']);                        
        $key_sha1 = substr($key, 0, 10) . substr($key, 20, strlen($key));
        $stamp    = substr($key, 10, 10);
        $allowed  = time() - 60 * 5; // - 5 mins
                
        if (strcmp($real_key, $key_sha1) != 0) {
            return $this->renderError('Invalid keys');      
        }
        
        /*if ($stamp < $allowed) {
            return $this->renderError('Key is expired');      
        }*/
        
        $this->showResetForm();
        print $key_sha1 . "<br />" . $real_key;
    }
    
    public function showResetForm()
    {
        print <<<HTML
            <form action="" >
                <input type="hidden" name="action" value="change" />
                <label>Enter secret code:</label>
                <input type="text" name="secret_code" value="" />
                <label>Enter new password:</label>
                <input type="text" name="secret_code" value="" />
            </form>
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
