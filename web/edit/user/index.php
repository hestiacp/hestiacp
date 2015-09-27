<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'USER';

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
exec (VESTA_CMD."v-list-user ".$v_username." json", $output, $return_var);
check_return_code($return_var,$output);
$data = json_decode(implode('', $output), true);
unset($output);

// Parse user
$v_password = "";
$v_email = $data[$v_username]['CONTACT'];
$v_package = $data[$v_username]['PACKAGE'];
$v_language = $data[$v_username]['LANGUAGE'];
$v_fname = $data[$v_username]['FNAME'];
$v_lname = $data[$v_username]['LNAME'];
$v_shell = $data[$v_username]['SHELL'];
$v_ns = $data[$v_username]['NS'];
$nameservers = explode(", ", $v_ns);
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

// List packages
exec (VESTA_CMD."v-list-user-packages json", $output, $return_var);
$packages = json_decode(implode('', $output), true);
unset($output);

// List lanugages
exec (VESTA_CMD."v-list-sys-languages json", $output, $return_var);
$languages = json_decode(implode('', $output), true);
unset($output);

// List shells
exec (VESTA_CMD."v-list-sys-shells json", $output, $return_var);
$shells = json_decode(implode('', $output), true);
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
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        exec (VESTA_CMD."v-change-user-password ".$v_username." ".$v_password, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($v_password);
        $v_password = escapeshellarg($_POST['v_password']);
    }

    // Change package (admin only)
    if (($v_package != $_POST['v_package']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_package = escapeshellarg($_POST['v_package']);
        exec (VESTA_CMD."v-change-user-package ".$v_username." ".$v_package, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change language
    if (($v_language != $_POST['v_language']) && (empty($_SESSION['error_msg']))) {
        $v_language = escapeshellarg($_POST['v_language']);
        exec (VESTA_CMD."v-change-user-language ".$v_username." ".$v_language, $output, $return_var);
        check_return_code($return_var,$output);
        if (empty($_SESSION['error_msg'])) {
             if ((empty($_GET['user'])) || ($_GET['user'] == $_SESSION['user'])) $_SESSION['language'] = $_POST['v_language'];
        }
        unset($output);
    }

    // Change shell (admin only)
    if (($v_shell != $_POST['v_shell']) && ($_SESSION['user'] == 'admin') && (empty($_SESSION['error_msg']))) {
        $v_shell = escapeshellarg($_POST['v_shell']);
        exec (VESTA_CMD."v-change-user-shell ".$v_username." ".$v_shell, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change contact email
    if (($v_email != $_POST['v_email']) && (empty($_SESSION['error_msg']))) {
        if (!filter_var($_POST['v_email'], FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_msg'] = __('Please enter valid email address.');
        } else {
            $v_email = escapeshellarg($_POST['v_email']);
            exec (VESTA_CMD."v-change-user-contact ".$v_username." ".$v_email, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
    }

    // Change full name
    if (($v_fname != $_POST['v_fname']) || ($v_lname != $_POST['v_lname']) && (empty($_SESSION['error_msg']))) {
        $v_fname = escapeshellarg($_POST['v_fname']);
        $v_lname = escapeshellarg($_POST['v_lname']);
        exec (VESTA_CMD."v-change-user-name ".$v_username." ".$v_fname." ".$v_lname, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_fname = $_POST['v_fname'];
        $v_lname = $_POST['v_lname'];
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
        $ns_cmd = VESTA_CMD."v-change-user-ns ".$v_username." ".$v_ns1." ".$v_ns2;
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
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }
}


// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');


// Panel
if (!empty($_SESSION['look'])) {
    top_panel($user,$TAB);
} else {
    top_panel($_SESSION['user'],$TAB);
}

// Display body
if ($_SESSION['user'] == 'admin') {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_user.html');
} else {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/user/edit_user.html');
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
