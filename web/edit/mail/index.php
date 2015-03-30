<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'MAIL';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Check domain argument
if (empty($_GET['domain'])) {
    header("Location: /list/mail/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}
$v_username = $user;

// List mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
    $v_domain = escapeshellarg($_GET['domain']);
    exec (VESTA_CMD."v-list-mail-domain ".$user." ".$v_domain." json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);

    // Parse domain
    $v_domain = $_GET['domain'];
    $v_antispam = $data[$v_domain]['ANTISPAM'];
    $v_antivirus = $data[$v_domain]['ANTIVIRUS'];
    $v_dkim = $data[$v_domain]['DKIM'];
    $v_catchall = $data[$v_domain]['CATCHALL'];
    $v_date = $data[$v_domain]['DATE'];
    $v_time = $data[$v_domain]['TIME'];
    $v_suspended = $data[$v_domain]['SUSPENDED'];
    if ( $v_suspended == 'yes' ) {
        $v_status =  'suspended';
    } else {
        $v_status =  'active';
    }
}

// List mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account'])))  {
    $v_domain = escapeshellarg($_GET['domain']);
    $v_account = escapeshellarg($_GET['account']);
    exec (VESTA_CMD."v-list-mail-account ".$user." ".$v_domain." ".$v_account." 'json'", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    unset($output);

    // Parse mail account
    $v_username = $user;
    $v_domain = $_GET['domain'];
    $v_account = $_GET['account'];
    $v_password = "••••••••";
    $v_aliases = str_replace(',', "\n", $data[$v_account]['ALIAS']);
    $valiases = explode(",", $data[$v_account]['ALIAS']);
    $v_fwd = str_replace(',', "\n", $data[$v_account]['FWD']);
    $vfwd = explode(",", $data[$v_account]['FWD']);
    $v_fwd_only = $data[$v_account]['FWD_ONLY'];
    $v_quota = $data[$v_account]['QUOTA'];
    $v_autoreply = $data[$v_account]['AUTOREPLY'];
    $v_suspended = $data[$v_account]['SUSPENDED'];
    if ( $v_suspended == 'yes' ) {
        $v_status =  'suspended';
    } else {
        $v_status =  'active';
    }
    $v_date = $data[$v_account]['DATE'];
    $v_time = $data[$v_account]['TIME'];

    // Parse autoreply
    if ( $v_autoreply == 'yes' ) {
        exec (VESTA_CMD."v-list-mail-account-autoreply ".$user." '".$v_domain."' '".$v_account."' json", $output, $return_var);
        $autoreply_str = json_decode(implode('', $output), true);
        unset($output);
        $v_autoreply_message = $autoreply_str[$v_account]['MSG'];
    }
}


// Check POST request for mail domain
if ((!empty($_POST['save'])) && (!empty($_GET['domain'])) && (empty($_GET['account']))) {
    $v_domain = escapeshellarg($_POST['v_domain']);

    // Delete antispam
    if (($v_antispam == 'yes') && (empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-mail-domain-antispam ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_antispam = 'no';
        unset($output);
    }

    // Add antispam
    if (($v_antispam == 'no') && (!empty($_POST['v_antispam'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-add-mail-domain-antispam ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_antispam = 'yes';
        unset($output);
    }

    // Delete antivirus
    if (($v_antivirus == 'yes') && (empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-mail-domain-antivirus ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_antivirus = 'no';
        unset($output);
    }

    // Add antivirs
    if (($v_antivirus == 'no') && (!empty($_POST['v_antivirus'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-add-mail-domain-antivirus ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_antivirus = 'yes';
        unset($output);
    }

    // Delete DKIM
    if (($v_dkim == 'yes') && (empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-mail-domain-dkim ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_dkim = 'no';
        unset($output);
    }

    // Add DKIM
    if (($v_dkim == 'no') && (!empty($_POST['v_dkim'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-add-mail-domain-dkim ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_dkim = 'yes';
        unset($output);
    }

    // Delete catchall
    if ((!empty($v_catchall)) && (empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-mail-domain-catchall ".$v_username." ".$v_domain, $output, $return_var);
        check_return_code($return_var,$output);
        $v_catchall = '';
        unset($output);
    }

    // Change catchall address
    if ((!empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
        if ($v_catchall != $_POST['v_catchall']) {
            $v_catchall = escapeshellarg($_POST['v_catchall']);
            exec (VESTA_CMD."v-change-mail-domain-catchall ".$v_username." ".$v_domain." ".$v_catchall, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
        }
    }

    // Add catchall
    if ((empty($v_catchall)) && (!empty($_POST['v_catchall'])) && (empty($_SESSION['error_msg']))) {
        $v_catchall = escapeshellarg($_POST['v_catchall']);
        exec (VESTA_CMD."v-add-mail-domain-catchall ".$v_username." ".$v_domain." ".$v_catchall, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }
}

// Check POST request for mail account
if ((!empty($_POST['save'])) && (!empty($_GET['domain'])) && (!empty($_GET['account']))) {
    $v_domain = escapeshellarg($_POST['v_domain']);
    $v_account = escapeshellarg($_POST['v_account']);

    // Change password
    if (($v_password != $_POST['v_password']) && (empty($_SESSION['error_msg']))) {
        $v_password = tempnam("/tmp","vst");
        $fp = fopen($v_password, "w");
        fwrite($fp, $_POST['v_password']."\n");
        fclose($fp);
        exec (VESTA_CMD."v-change-mail-account-password ".$v_username." ".$v_domain." ".$v_account." ".$v_password, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        unlink($v_password);
        $v_password = "••••••••";
    }

    // Change quota
    if (($v_quota != $_POST['v_quota']) && (empty($_SESSION['error_msg']))) {
        if (empty($_POST['v_quota'])) {
            $v_quota = 0; 
        } else {
            $v_quota = escapeshellarg($_POST['v_quota']);
        }
        exec (VESTA_CMD."v-change-mail-account-quota ".$v_username." ".$v_domain." ".$v_account." ".$v_quota, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
    }

    // Change account aliases
    if (empty($_SESSION['error_msg'])) {
        $waliases = preg_replace("/\n/", " ", $_POST['v_aliases']);
        $waliases = preg_replace("/,/", " ", $waliases);
        $waliases = preg_replace('/\s+/', ' ',$waliases);
        $waliases = trim($waliases);
        $aliases = explode(" ", $waliases);
        $v_aliases = str_replace(' ', "\n", $waliases);
        $result = array_diff($valiases, $aliases);
        foreach ($result as $alias) {
            if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                exec (VESTA_CMD."v-delete-mail-account-alias ".$v_username." ".$v_domain." ".$v_account." '".$alias."'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
        $result = array_diff($aliases, $valiases);
        foreach ($result as $alias) {
            if ((empty($_SESSION['error_msg'])) && (!empty($alias))) {
                exec (VESTA_CMD."v-add-mail-account-alias ".$v_username." ".$v_domain." ".$v_account." '".$alias."'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    // Change forwarders
    if (empty($_SESSION['error_msg'])) {
        $wfwd = preg_replace("/\n/", " ", $_POST['v_fwd']);
        $wfwd = preg_replace("/,/", " ", $wfwd);
        $wfwd = preg_replace('/\s+/', ' ',$wfwd);
        $wfwd = trim($wfwd);
        $fwd = explode(" ", $wfwd);
        $v_fwd = str_replace(' ', "\n", $wfwd);
        $result = array_diff($vfwd, $fwd);
        foreach ($result as $forward) {
            if ((empty($_SESSION['error_msg'])) && (!empty($forward))) {
                exec (VESTA_CMD."v-delete-mail-account-forward ".$v_username." ".$v_domain." ".$v_account." '".$forward."'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
        $result = array_diff($fwd, $vfwd);
        foreach ($result as $forward) {
            if ((empty($_SESSION['error_msg'])) && (!empty($forward))) {
                exec (VESTA_CMD."v-add-mail-account-forward ".$v_username." ".$v_domain." ".$v_account." '".$forward."'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
            }
        }
    }

    // Delete FWD_ONLY flag
    if (($v_fwd_only == 'yes') && (empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-mail-account-fwd-only ".$v_username." ".$v_domain." ".$v_account, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_fwd_only = '';
    }

    // Add FWD_ONLY flag
    if (($v_fwd_only != 'yes') && (!empty($_POST['v_fwd_only'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-add-mail-account-fwd-only ".$v_username." ".$v_domain." ".$v_account, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_fwd_only = 'yes';
    }

    // Delete autoreply
    if (($v_autoreply == 'yes') && (empty($_POST['v_autoreply'])) && (empty($_SESSION['error_msg']))) {
        exec (VESTA_CMD."v-delete-mail-account-autoreply ".$v_username." ".$v_domain." ".$v_account, $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_autoreply = 'no';
        $v_autoreply_message = '';
    }

    // Add autoreply
    if ((!empty($_POST['v_autoreply'])) && (empty($_SESSION['error_msg']))) {
        if ( $v_autoreply_message != str_replace("\r\n", "\n", $_POST['v_autoreply_message'])) {
            $v_autoreply_message = str_replace("\r\n", "\n", $_POST['v_autoreply_message']);
            $v_autoreply_message = escapeshellarg($v_autoreply_message);
            exec (VESTA_CMD."v-add-mail-account-autoreply ".$v_username." ".$v_domain." ".$v_account." ".$v_autoreply_message, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_autoreply = 'yes';
            $v_autoreply_message = $_POST['v_autoreply_message'];
        }
    }

    // Set success message
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }
}

// Display body for mail domain
if ((!empty($_GET['domain'])) && (empty($_GET['account'])))  {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_mail.html');
}

// Display body for mail account
if ((!empty($_GET['domain'])) && (!empty($_GET['account'])))  {
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_mail_acc.html');
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
