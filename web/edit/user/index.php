<?php
error_reporting(NULL);
ob_start();
$TAB = 'USER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");


// Check user argument
if (empty($_GET['user'])) {
    header("Location: /list/user/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=$_GET['user'];
    $v_username=$_GET['user'];
} else {
    $user=$_SESSION['user'];
    $v_username=$_SESSION['user'];
}

// List user
exec (HESTIA_CMD."v-list-user ".escapeshellarg($v_username)." json", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse user
$v_password = "";
$v_email = $data[$v_username]['CONTACT'];
$v_package = $data[$v_username]['PACKAGE'];
$v_language = $data[$v_username]['LANGUAGE'];
$v_name = $data[$v_username]['NAME'];
$v_shell = $data[$v_username]['SHELL'];
$v_twofa = $data[$v_username]['TWOFA'];
$v_qrcode = $data[$v_username]['QRCODE'];
$v_phpcli = $data[$v_username]['PHPCLI'];
$v_role = $data[$v_username]['ROLE'];
$v_ns = $data[$v_username]['NS'];
$nameservers = explode(",", $v_ns);
$v_ns1 = $nameservers[0];
$v_ns2 = $nameservers[1];
$v_ns3 = $nameservers[2];
$v_ns4 = $nameservers[3];
$v_ns5 = $nameservers[4];
$v_ns6 = $nameservers[5];
$v_ns7 = $nameservers[6];
$v_ns8 = $nameservers[7];

$v_suspended = $data[$v_username]['SUSPENDED'];
if ( $v_suspended == 'yes' ) {
    $v_status =  'suspended';
} else {
    $v_status =  'active';
}
$v_time = $data[$v_username]['TIME'];
$v_date = $data[$v_username]['DATE'];

if(empty($v_phpcli)){
   $v_phpcli = substr(DEFAULT_PHP_VERSION,4);
}

// List packages
exec (HESTIA_CMD."v-list-user-packages json", $output, $return_var);
$packages = json_decode(implode('', $output), true);
unset($output);

// List languages
exec (HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
$language = json_decode(implode('', $output), true);
foreach($language as $lang){
    $languages[$lang] = translate_json($lang);
}
asort($languages);
unset($output);

// List shells
exec (HESTIA_CMD."v-list-sys-shells json", $output, $return_var);
$shells = json_decode(implode('', $output), true);
unset($output);

//List PHP Versions
// List supported php versions
exec (HESTIA_CMD."v-list-sys-php json", $output, $return_var);
$php_versions = json_decode(implode('', $output), true);
unset($output);



// Are you admin?

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Change password
    if ((!empty($_POST['v_password'])) && (empty($_SESSION['error_msg']))) {
        // Check password length
        $pw_len = strlen($_POST['v_password']);
        if (!validate_password($_POST['v_password'])){ 
            $_SESSION['error_msg'] = _('Password does not match the minimum requirements');
        } 
        if (empty($_SESSION['error_msg'])) {
            $v_password = tempnam("/tmp","vst");
            $fp = fopen($v_password, "w");
            fwrite($fp, $_POST['v_password']."\n");
            fclose($fp);
            exec (HESTIA_CMD."v-change-user-password ".escapeshellarg($v_username)." ".$v_password, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            unlink($v_password);
            $v_password = escapeshellarg($_POST['v_password']);
        }
    }

    // Enable twofa
    if ((!empty($_POST['v_twofa'])) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-add-user-2fa ".escapeshellarg($v_username), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        
        // List user
        exec (HESTIA_CMD."v-list-user ".escapeshellarg($v_username)." json", $output, $return_var);
        check_return_code($return_var,$output);
        $data = json_decode(implode('', $output), true);
        unset($output);

        // Parse user twofa
        $v_twofa = $data[$v_username]['TWOFA'];
        $v_qrcode = $data[$v_username]['QRCODE'];
    }

    // Disable twofa
    if ((empty($_POST['v_twofa'])) && (!empty($v_twofa)) && (empty($_SESSION['error_msg']))) {
        exec (HESTIA_CMD."v-delete-user-2fa ".escapeshellarg($v_username), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_twofa = '';
        $v_qrcode = '';
    }

    // Change package (admin only)
    if (($v_package != $_POST['v_package']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_package = escapeshellarg($_POST['v_package']);
        exec (HESTIA_CMD."v-change-user-package ".escapeshellarg($v_username)." ".$v_package, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change phpcli (admin only)
    if (($v_phpcli != $_POST['v_phpcli']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_phpcli = escapeshellarg($_POST['v_phpcli']);
        exec (HESTIA_CMD."v-change-user-php-cli ".escapeshellarg($v_username)." ".$v_phpcli, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }
    // Change Role (admin only)
    if (($v_role != $_POST['$v_role']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_role = escapeshellarg($_POST['v_role']);
        exec (HESTIA_CMD."v-change-user-role ".escapeshellarg($v_username)." ".$v_role, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_role = $_POST['v_role'];
    }
    // Change language
    if (($v_language != $_POST['v_language']) && (empty($_SESSION['error_msg']))) {
        $v_language = escapeshellarg($_POST['v_language']);
        exec (HESTIA_CMD."v-change-user-language ".escapeshellarg($v_username)." ".$v_language, $output, $return_var);
        check_return_code($return_var,$output);
        if (empty($_SESSION['error_msg'])) {
             if ((empty($_GET['user'])) || ($_GET['user'] == $_SESSION['user'])) $_SESSION['language'] = $_POST['v_language'];
        }
        unset($output);
    }

    // Change shell (admin only)
    if (($v_shell != $_POST['v_shell']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_shell = escapeshellarg($_POST['v_shell']);
        exec (HESTIA_CMD."v-change-user-shell ".escapeshellarg($v_username)." ".$v_shell, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change contact email
    if (($v_email != $_POST['v_email']) && (empty($_SESSION['error_msg']))) {
        if (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = _('Please enter valid email address.');
        } else {
            $v_email = escapeshellarg($_POST['v_email']);
            exec (HESTIA_CMD."v-change-user-contact ".escapeshellarg($v_username)." ".$v_email, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
    }

    // Change full name
    if ($v_name != $_POST['v_name']){
            if (empty($_POST['v_name'])) {
                 $_SESSION['error_msg'] = _('Please enter a valid name');
            }else{
                $v_name = escapeshellarg($_POST['v_name']);
                exec (HESTIA_CMD."v-change-user-name ".escapeshellarg($v_username). " ".$v_name, $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                $v_name = $_POST['v_name'];
            }
    }

    // Change NameServers
    if (($v_ns1 != $_POST['v_ns1']) || ($v_ns2 != $_POST['v_ns2']) || ($v_ns3 != $_POST['v_ns3']) || ($v_ns4 != $_POST['v_ns4']) || ($v_ns5 != $_POST['v_ns5'])
 || ($v_ns6 != $_POST['v_ns6']) || ($v_ns7 != $_POST['v_ns7']) || ($v_ns8 != $_POST['v_ns8']) && (empty($_SESSION['error_msg']))) {
        $v_ns1 = escapeshellarg($_POST['v_ns1']);
        $v_ns2 = escapeshellarg($_POST['v_ns2']);
        $v_ns3 = escapeshellarg($_POST['v_ns3']);
        $v_ns4 = escapeshellarg($_POST['v_ns4']);
        $v_ns5 = escapeshellarg($_POST['v_ns5']);
        $v_ns6 = escapeshellarg($_POST['v_ns6']);
        $v_ns7 = escapeshellarg($_POST['v_ns7']);
        $v_ns8 = escapeshellarg($_POST['v_ns8']);
        $ns_cmd = HESTIA_CMD."v-change-user-ns ".escapeshellarg($v_username)." ".$v_ns1." ".$v_ns2;
        if (!empty($_POST['v_ns3'])) $ns_cmd = $ns_cmd." ".$v_ns3;
        if (!empty($_POST['v_ns4'])) $ns_cmd = $ns_cmd." ".$v_ns4;
        if (!empty($_POST['v_ns5'])) $ns_cmd = $ns_cmd." ".$v_ns5;
        if (!empty($_POST['v_ns6'])) $ns_cmd = $ns_cmd." ".$v_ns6;
        if (!empty($_POST['v_ns7'])) $ns_cmd = $ns_cmd." ".$v_ns7;
        if (!empty($_POST['v_ns8'])) $ns_cmd = $ns_cmd." ".$v_ns8;
        exec ($ns_cmd, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);

        $v_ns1 = str_replace("'","", $v_ns1);
        $v_ns2 = str_replace("'","", $v_ns2);
        $v_ns3 = str_replace("'","", $v_ns3);
        $v_ns4 = str_replace("'","", $v_ns4);
        $v_ns5 = str_replace("'","", $v_ns5);
        $v_ns6 = str_replace("'","", $v_ns6);
        $v_ns7 = str_replace("'","", $v_ns7);
        $v_ns8 = str_replace("'","", $v_ns8);
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = _('Changes has been saved.');
    }
}

// Render page
render_page($user, $TAB, 'edit_user');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);