<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'PACKAGE';

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit;
    }

    // Check empty fields
    if (empty($_POST['v_package'])) $errors[] = __('package');
    if (empty($_POST['v_web_template'])) $errors[] = __('web template');
    if (!empty($_SESSION['WEB_BACKEND'])) {
        if (empty($_POST['v_backend_template'])) $errors[] = __('backend template');
    }
    if (!empty($_SESSION['PROXY_SYSTEM'])) {
        if (empty($_POST['v_proxy_template'])) $errors[] = __('proxy template');
    }
    if (empty($_POST['v_dns_template'])) $errors[] = __('dns template');
    if (empty($_POST['v_shell'])) $errrors[] = __('shell');
    if (!isset($_POST['v_web_domains'])) $errors[] = __('web domains');
    if (!isset($_POST['v_web_aliases'])) $errors[] = __('web aliases');
    if (!isset($_POST['v_dns_domains'])) $errors[] = __('dns domains');
    if (!isset($_POST['v_dns_records'])) $errors[] = __('dns records');
    if (!isset($_POST['v_mail_domains'])) $errors[] = __('mail domains');
    if (!isset($_POST['v_mail_accounts'])) $errors[] = __('mail accounts');
    if (!isset($_POST['v_databases'])) $errors[] = __('databases');
    if (!isset($_POST['v_cron_jobs'])) $errors[] = __('cron jobs');
    if (!isset($_POST['v_backups'])) $errors[] = __('backups');
    if (!isset($_POST['v_disk_quota'])) $errors[] = __('quota');
    if (!isset($_POST['v_bandwidth'])) $errors[] = __('bandwidth');
    if (empty($_POST['v_ns1'])) $errors[] = __('ns1');
    if (empty($_POST['v_ns2'])) $errors[] = __('ns2');
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

    $v_package = $_POST['v_package'];
    $v_web_template = $_POST['v_web_template'];
    $v_backend_template = $_POST['v_backend_template'];
    $v_proxy_template = $_POST['v_proxy_template'];
    $v_dns_template = $_POST['v_dns_template'];
    $v_shell = $_POST['v_shell'];
    $v_web_domains = $_POST['v_web_domains'];
    $v_web_aliases = $_POST['v_web_aliases'];
    $v_dns_domains = $_POST['v_dns_domains'];
    $v_dns_records = $_POST['v_dns_records'];
    $v_mail_domains = $_POST['v_mail_domains'];
    $v_mail_accounts = $_POST['v_mail_accounts'];
    $v_databases = $_POST['v_databases'];
    $v_cron_jobs = $_POST['v_cron_jobs'];
    $v_backups = $_POST['v_backups'];
    $v_disk_quota = $_POST['v_disk_quota'];
    $v_bandwidth = $_POST['v_bandwidth'];
    $v_ns1 = trim($_POST['v_ns1'], '.');
    $v_ns2 = trim($_POST['v_ns2'], '.');
    $v_ns3 = trim($_POST['v_ns3'], '.');
    $v_ns4 = trim($_POST['v_ns4'], '.');
    $v_ns5 = trim($_POST['v_ns5'], '.');
    $v_ns6 = trim($_POST['v_ns6'], '.');
    $v_ns7 = trim($_POST['v_ns7'], '.');
    $v_ns8 = trim($_POST['v_ns8'], '.');
    $v_ns = $v_ns1.",".$v_ns2;
    if (!empty($v_ns3)) $v_ns .= ",".$v_ns3;
    if (!empty($v_ns4)) $v_ns .= ",".$v_ns4;
    if (!empty($v_ns5)) $v_ns .= ",".$v_ns5;
    if (!empty($v_ns6)) $v_ns .= ",".$v_ns6;
    if (!empty($v_ns7)) $v_ns .= ",".$v_ns7;
    if (!empty($v_ns8)) $v_ns .= ",".$v_ns8;
    $v_time = date('H:i:s');
    $v_date = date('Y-m-d');

    // Create temporary dir
    if (empty($_SESSION['error_msg'])) {
        exec('mktemp -d', $output, $return_var);
        $tmpdir = $output[0];
        check_return_code($return_var, $output);
        unset($output);
    }

    // Create package file
    if (empty($_SESSION['error_msg'])) {
        $a_pkg = [
            'WEB_TEMPLATE'     => $v_web_template,
            'BACKEND_TEMPLATE' => !empty($_SESSION['WEB_BACKEND']) ? $v_backend_template : null,
            'PROXY_TEMPLATE'   => !empty($_SESSION['PROXY_SYSTEM']) ? $v_proxy_template : null,
            'DNS_TEMPLATE'     => $v_dns_template,
            'WEB_DOMAINS'      => $v_web_domains,
            'WEB_ALIASES'      => $v_web_aliases,
            'DNS_DOMAINS'      => $v_dns_domains,
            'DNS_RECORDS'      => $v_dns_records,
            'MAIL_DOMAINS'     => $v_mail_domains,
            'MAIL_ACCOUNTS'    => $v_mail_accounts,
            'DATABASES'        => $v_databases,
            'CRON_JOBS'        => $v_cron_jobs,
            'DISK_QUOTA'       => $v_disk_quota,
            'BANDWIDTH'        => $v_bandwidth,
            'NS'               => $v_ns,
            'SHELL'            => $v_shell,
            'BACKUPS'          => $v_backups,
            'TIME'             => $v_time,
            'DATE'             => $v_date,
        ];

        $pkg = '';
        foreach ($a_pkg as $key => $value) {
            if (is_null($value)) continue;
            $pkg .= $key . '=' . escapeshellarg($value) . "\n";
        }

        $fp = fopen($tmpdir."/".$_POST['v_package'].".pkg", 'w');
        fwrite($fp, $pkg);
        fclose($fp);
    }

    // Add new package
    if (empty($_SESSION['error_msg'])) {
        v_exec('v-add-user-package', [$tmpdir, $v_package]);
    }

    // Remove tmpdir
    safe_exec('rm', ['-rf', $tmpdir]);

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('PACKAGE_CREATED_OK', htmlentities($_POST['v_package']), htmlentities($_POST['v_package']));
        unset($v_package);
    }

}


// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// List web temmplates
v_exec('v-list-web-templates', ['json'], false, $output);
$web_templates = json_decode($output, true);

// List web templates for backend
if (!empty($_SESSION['WEB_BACKEND'])) {
    v_exec('v-list-web-templates-backend', ['json'], false, $output);
    $backend_templates = json_decode($output, true);
}

// List web templates for proxy
if (!empty($_SESSION['PROXY_SYSTEM'])) {
    v_exec('v-list-web-templates-proxy', ['json'], false, $output);
    $proxy_templates = json_decode($output, true);
}

// List DNS templates
v_exec('v-list-dns-templates', ['json'], false, $output);
$dns_templates = json_decode($output, true);

// List system shells
v_exec('v-list-sys-shells', ['json'], false, $output);
$shells = json_decode($output, true);

// Set default values
if (empty($v_web_template)) $v_web_template = 'default';
if (empty($v_backend_template)) $v_backend_template = 'default';
if (empty($v_proxy_template)) $v_proxy_template = 'default';
if (empty($v_dns_template)) $v_dns_template = 'default';
if (empty($v_shell)) $v_shell = 'nologin';
if (empty($v_web_domains)) $v_web_domains = '1';
if (empty($v_web_aliases)) $v_web_aliases = '1';
if (empty($v_dns_domains)) $v_dns_domains = '1';
if (empty($v_dns_records)) $v_dns_records = '1';
if (empty($v_mail_domains)) $v_mail_domains = '1';
if (empty($v_mail_accounts)) $v_mail_accounts = '1';
if (empty($v_databases)) $v_databases = '1';
if (empty($v_cron_jobs)) $v_cron_jobs = '1';
if (empty($v_backups)) $v_backups = '1';
if (empty($v_disk_quota)) $v_disk_quota = '1000';
if (empty($v_bandwidth)) $v_bandwidth = '1000';
if (empty($v_ns1)) $v_ns1 = 'ns1.example.ltd';
if (empty($v_ns2)) $v_ns2 = 'ns2.example.ltd';

// Display body
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_package.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
