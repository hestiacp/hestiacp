<?php
error_reporting(E_ALL);
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check POST request
if (!empty($_POST['ok'])) {
    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }
    
    if (empty($_POST['v_key'])){ 
        $_SESSION['error_msg'] = _('Field SSH_KEY can not be blank.');
    }
    
    if(!$_SESSION['error_msg']){
        if($_POST){
            //key if key already exisits
            exec (HESTIA_CMD . "v-list-user-ssh-key ".$user." json", $output, $return_var);
            $data = json_decode(implode('', $output), true);
            unset($output);
            $keylist = array();
            foreach($data as $key => $value){
                $idlist[] = trim($data[$key]['ID']);
                $keylist[] = trim($data[$key]['KEY']);
            }
    
            $v_key_parts = explode(' ',$_POST['v_key']);
            $key_id = trim($v_key_parts[2]);
            if($v_key_parts[2] == ''){
                $v_key_parts[2] = md5(time());
                $_POST['v_key'] .= ' '.$v_key_parts[2];
            }
    
            //for deleting / revoking key the last part user@domain is used therefore needs to be unique
            //maybe consider adding random generated message or even an human read able string set by user?
            if(in_array($v_key_parts[2], $idlist)){
                $_SESSION['error_msg']  =  _('SSH KEY already exists');
            }
            if(in_array($v_key_parts[1], $keylist)){
                $_SESSION['error_msg']  =  _('SSH KEY already exists');
            }
            $v_key = escapeshellarg(trim($_POST['v_key']));
        }
    }
        
    if (empty($_SESSION['error_msg'])) {
        exec (HESTIA_CMD."v-add-user-ssh-key ".$user." ".$v_key, $output, $return_var);
        check_return_code($return_var,$output);
    }
    unset($output);
    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
    $_SESSION['ok_msg'] = _('SSH KEY created');
    }

}

render_page($user, $TAB, 'add_key');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);    