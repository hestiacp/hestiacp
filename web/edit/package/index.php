<?php
// Init
error_reporting(NULL);
ob_start();
session_start();

$TAB = 'PACKAGE';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {

    // Check user argument?
    if (empty($_GET['package'])) {
        header("Location: /list/package/");
        exit;
    }

    $v_package = escapeshellarg($_GET['package']);
    exec (VESTA_CMD."v-list-user-package ".$v_package." 'json'", $output, $return_var);
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = _('Error: vesta did not return any output.');
        $_SESSION['error_msg'] = $error;
    } else {
        $data = json_decode(implode('', $output), true);
        unset($output);

        $v_package = $_GET['package'];
        $v_template = $data[$v_package]['TEMPLATE'];
        $v_web_domains = $data[$v_package]['WEB_DOMAINS'];
        $v_web_aliases = $data[$v_package]['WEB_ALIASES'];
        $v_dns_domains = $data[$v_package]['DNS_DOMAINS'];
        $v_dns_records = $data[$v_package]['DNS_RECORDS'];
        $v_mail_domains = $data[$v_package]['MAIL_DOMAINS'];
        $v_mail_accounts = $data[$v_package]['MAIL_ACCOUNTS'];
        $v_databases = $data[$v_package]['DATABASES'];
        $v_cron_jobs = $data[$v_package]['CRON_JOBS'];
        $v_disk_quota = $data[$v_package]['DISK_QUOTA'];
        $v_bandwidth = $data[$v_package]['BANDWIDTH'];
        $v_shell = $data[$v_package]['SHELL'];
        $v_ns = $data[$v_package]['NS'];
        $nameservers = explode(", ", $v_ns);
        $v_ns1 = $nameservers[0];
        $v_ns2 = $nameservers[1];
        $v_ns3 = $nameservers[2];
        $v_ns4 = $nameservers[3];
        $v_backups = $data[$v_package]['BACKUPS'];
        $v_date = $data[$v_package]['DATE'];
        $v_time = $data[$v_package]['TIME'];
        $v_status =  'active';


        exec (VESTA_CMD."v-list-web-templates json", $output, $return_var);
        check_error($return_var);
        $templates = json_decode(implode('', $output), true);
        unset($output);

        exec (VESTA_CMD."v-list-sys-shells json", $output, $return_var);
        check_error($return_var);
        $shells = json_decode(implode('', $output), true);
        unset($output);

        // Action
        if (!empty($_POST['save'])) {
            // Check input
            if (empty($_POST['v_package'])) $errors[] = _('package');
            if (empty($_POST['v_template'])) $errors[] = _('template');
            if (empty($_POST['v_shell'])) $errrors[] = _('shell');
            if (!isset($_POST['v_web_domains'])) $errors[] = _('web domains');
            if (!isset($_POST['v_web_aliases'])) $errors[] = _('web aliases');
            if (!isset($_POST['v_dns_domains'])) $errors[] = _('dns domains');
            if (!isset($_POST['v_dns_records'])) $errors[] = _('dns records');
            if (!isset($_POST['v_mail_domains'])) $errors[] = _('mail domains');
            if (!isset($_POST['v_mail_accounts'])) $errors[] = _('mail accounts');
            if (!isset($_POST['v_databases'])) $errors[] = _('databases');
            if (!isset($_POST['v_cron_jobs'])) $errors[] = _('cron jobs');
            if (!isset($_POST['v_backups'])) $errors[] = _('backups');
            if (!isset($_POST['v_disk_quota'])) $errors[] = _('quota');
            if (!isset($_POST['v_bandwidth'])) $errors[] = _('bandwidth');
            if (empty($_POST['v_ns1'])) $errors[] = _('ns1');
            if (empty($_POST['v_ns2'])) $errors[] = _('ns2');

            // Protect input
            $v_package = escapeshellarg($_POST['v_package']);
            $v_template = escapeshellarg($_POST['v_template']);
            $v_shell = escapeshellarg($_POST['v_shell']);
            $v_web_domains = escapeshellarg($_POST['v_web_domains']);
            $v_web_aliases = escapeshellarg($_POST['v_web_aliases']);
            $v_dns_domains = escapeshellarg($_POST['v_dns_domains']);
            $v_dns_records = escapeshellarg($_POST['v_dns_records']);
            $v_mail_domains = escapeshellarg($_POST['v_mail_domains']);
            $v_mail_accounts = escapeshellarg($_POST['v_mail_accounts']);
            $v_databases = escapeshellarg($_POST['v_databases']);
            $v_cron_jobs = escapeshellarg($_POST['v_cron_jobs']);
            $v_backups = escapeshellarg($_POST['v_backups']);
            $v_disk_quota = escapeshellarg($_POST['v_disk_quota']);
            $v_bandwidth = escapeshellarg($_POST['v_bandwidth']);
            $v_ns1 = trim($_POST['v_ns1'], '.');
            $v_ns2 = trim($_POST['v_ns2'], '.');
            $v_ns3 = trim($_POST['v_ns3'], '.');
            $v_ns4 = trim($_POST['v_ns4'], '.');
            $v_ns = $v_ns1.",".$v_ns2;
            if (!empty($v_ns3)) $v_ns .= ",".$v_ns3;
            if (!empty($v_ns4)) $v_ns .= ",".$v_ns4;
            $v_ns = escapeshellarg($v_ns);
            $v_time = escapeshellarg(date('H:i:s'));
            $v_date = escapeshellarg(date('Y-m-d'));

            // Check for errors
            if (!empty($errors[0])) {
                foreach ($errors as $i => $error) {
                    if ( $i == 0 ) {
                        $error_msg = $error;
                    } else {
                        $error_msg = $error_msg.", ".$error;
                    }
                }
                $_SESSION['error_msg'] = _('Error: field "%s" can not be blank.',$error_msg);
            } else {
                exec ('mktemp -d', $output, $return_var);
                $tmpdir = $output[0];
                unset($output);

                // Create package
                $pkg = "TEMPLATE=".$v_template."\n";
                $pkg .= "WEB_DOMAINS=".$v_web_domains."\n";
                $pkg .= "WEB_ALIASES=".$v_web_aliases."\n";
                $pkg .= "DNS_DOMAINS=".$v_dns_domains."\n";
                $pkg .= "DNS_RECORDS=".$v_dns_records."\n";
                $pkg .= "MAIL_DOMAINS=".$v_mail_domains."\n";
                $pkg .= "MAIL_ACCOUNTS=".$v_mail_accounts."\n";
                $pkg .= "DATABASES=".$v_databases."\n";
                $pkg .= "CRON_JOBS=".$v_cron_jobs."\n";
                $pkg .= "DISK_QUOTA=".$v_disk_quota."\n";
                $pkg .= "BANDWIDTH=".$v_bandwidth."\n";
                $pkg .= "NS=".$v_ns."\n";
                $pkg .= "SHELL=".$v_shell."\n";
                $pkg .= "BACKUPS=".$v_backups."\n";
                $pkg .= "TIME=".$v_time."\n";
                $pkg .= "DATE=".$v_date."\n";

                // Write package
                $fp = fopen($tmpdir."/".$_POST['v_package'].".pkg", 'w');
                fwrite($fp, $pkg);
                fclose($fp);

                // Rewrite package
                if (empty($_SESSION['error_msg'])) {
                    exec (VESTA_CMD."v-add-user-package ".$tmpdir." ".$v_package." 'yes'", $output, $return_var);
                    if ($return_var != 0) {
                        $error = implode('<br>', $output);
                        if (empty($error)) $error = _('Error: vesta did not return any output.');
                        $_SESSION['error_msg'] = $error;
                    }
                    unset($output);
                }

                // Remove tmpdir
                exec ('rm -rf '.$tmpdir, $output, $return_var);
                unset($output);

                // Propogate new package
                exec (VESTA_CMD."v-update-user-package ".$v_package." 'json'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = _('Error: vesta did not return any output.');
                    $_SESSION['error_msg'] = $error;
                }

                if (empty($_SESSION['error_msg'])) {
                    $_SESSION['ok_msg'] = _('OK: changes has been saved.');
                }
            }
        }
    }

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_package.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
