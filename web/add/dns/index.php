<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'DNS';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check POST request for dns domain
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit;
    }

    // Check empty fields
    if (empty($_POST['v_domain'])) $errors[] = __('domain');
    if (empty($_POST['v_ip'])) $errors[] = __('ip');
    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
    }

    // Protect input
    $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
    $v_domain = strtolower($v_domain);
    $v_ip = $_POST['v_ip'];
    if (!empty($_POST['v_ns1'])) $v_ns1 = $_POST['v_ns1'];
    if (!empty($_POST['v_ns2'])) $v_ns2 = $_POST['v_ns2'];
    if (!empty($_POST['v_ns3'])) $v_ns3 = $_POST['v_ns3'];
    if (!empty($_POST['v_ns4'])) $v_ns4 = $_POST['v_ns4'];
    if (!empty($_POST['v_ns5'])) $v_ns5 = $_POST['v_ns5'];
    if (!empty($_POST['v_ns6'])) $v_ns6 = $_POST['v_ns6'];
    if (!empty($_POST['v_ns7'])) $v_ns7 = $_POST['v_ns7'];
    if (!empty($_POST['v_ns8'])) $v_ns8 = $_POST['v_ns8'];

    // Add dns domain
    if (empty($_SESSION['error_msg'])) {
        v_exec('v-add-dns-domain', [$user, $v_domain, $v_ip, $v_ns1, $v_ns2, $v_ns3, $v_ns4, $v_ns5, $v_ns6, $v_ns7, $v_ns8, 'no']);
    }


    // Set expiriation date
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_exp'])) && ($_POST['v_exp'] != date('Y-m-d', strtotime('+1 year')))) {
            $v_exp = $_POST['v_exp'];
            v_exec('v-change-dns-domain-exp', [$user, $v_domain, $v_exp, 'no']);
        }
    }

    // Set ttl
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_ttl'])) && ($_POST['v_ttl'] != '14400') && (empty($_SESSION['error_msg']))) {
            $v_ttl = $_POST['v_ttl'];
            v_exec('v-change-dns-domain-ttl', [$user, $v_domain, $v_ttl, 'no']);
        }
    }

    // Restart dns server
    if (empty($_SESSION['error_msg'])) {
        v_exec('v-restart-dns');
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('DNS_DOMAIN_CREATED_OK', htmlentities($_POST[v_domain]), htmlentities($_POST[v_domain]));
        unset($v_domain);
    }
}


// Check POST request for dns record
if (!empty($_POST['ok_rec'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit;
    }

    // Check empty fields
    if (empty($_POST['v_domain'])) $errors[] = 'domain';
    if (empty($_POST['v_rec'])) $errors[] = 'record';
    if (empty($_POST['v_type'])) $errors[] = 'type';
    if (empty($_POST['v_val'])) $errors[] = 'value';
    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            if ( $i == 0 ) {
                $error_msg = $error;
            } else {
                $error_msg = $error_msg.", ".$error;
            }
        }
        $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
    }

    // Protect input
    $v_domain = $_POST['v_domain'];
    $v_rec = $_POST['v_rec'];
    $v_type = $_POST['v_type'];
    $v_val = $_POST['v_val'];
    $v_priority = $_POST['v_priority'];

    // Add dns record
    if (empty($_SESSION['error_msg'])) {
        v_exec('v-add-dns-record', [$user, $v_domain, $v_rec, $v_type, $v_val, $v_priority]);
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('DNS_RECORD_CREATED_OK',htmlentities($_POST[v_rec]),htmlentities($_POST[v_domain]));
        unset($v_domain);
        unset($v_rec);
        unset($v_val);
        unset($v_priority);
    }
}


// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

$v_ns1 = str_replace("'", "", $v_ns1);
$v_ns2 = str_replace("'", "", $v_ns2);
$v_ns3 = str_replace("'", "", $v_ns3);
$v_ns4 = str_replace("'", "", $v_ns4);
$v_ns5 = str_replace("'", "", $v_ns5);
$v_ns6 = str_replace("'", "", $v_ns6);
$v_ns7 = str_replace("'", "", $v_ns7);
$v_ns8 = str_replace("'", "", $v_ns8);


// Display body for dns domain
if (empty($_GET['domain'])) {
    if (empty($v_ttl)) $v_ttl = 14400;
    if (empty($v_exp)) $v_exp = date('Y-m-d', strtotime('+1 year'));
    if (empty($v_ns1)) {
        v_exec('v-list-user-ns', [$user, 'json'], false, $output);
        $nameservers = json_decode($output, true);
        $v_ns1 = str_replace("'", "", $nameservers[0]);
        $v_ns2 = str_replace("'", "", $nameservers[1]);
        $v_ns3 = str_replace("'", "", $nameservers[2]);
        $v_ns4 = str_replace("'", "", $nameservers[3]);
        $v_ns5 = str_replace("'", "", $nameservers[4]);
        $v_ns6 = str_replace("'", "", $nameservers[5]);
        $v_ns7 = str_replace("'", "", $nameservers[6]);
        $v_ns8 = str_replace("'", "", $nameservers[7]);
    }
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_dns.html');
}

// Display body for dns record
if (!empty($_GET['domain'])) {
    $v_domain = $_GET['domain'];
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_dns_rec.html');
}

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
