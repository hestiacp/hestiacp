<?php
error_reporting(NULL);
$TAB = 'SERVER';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check user
if ($_SESSION['user'] != 'admin') {
    header("Location: /list/user");
    exit;
}

// Get server hostname
$v_hostname = exec('hostname');

// List available timezones and get current one
$v_timezones = list_timezones();
exec (HESTIA_CMD."v-get-sys-timezone", $output, $return_var);
$v_timezone = $output[0];
unset($output);
if ($v_timezone == 'Etc/UTC' ) $v_timezone = 'UTC';
if ($v_timezone == 'Pacific/Honolulu' ) $v_timezone = 'HAST';
if ($v_timezone == 'US/Aleutian' ) $v_timezone = 'HADT';
if ($v_timezone == 'Etc/GMT+9' ) $v_timezone = 'AKST';
if ($v_timezone == 'America/Anchorage' ) $v_timezone = 'AKDT';
if ($v_timezone == 'America/Dawson_Creek' ) $v_timezone = 'PST';
if ($v_timezone == 'PST8PDT' ) $v_timezone = 'PDT';
if ($v_timezone == 'MST7MDT' ) $v_timezone = 'MDT';
if ($v_timezone == 'Canada/Saskatchewan' ) $v_timezone = 'CST';
if ($v_timezone == 'CST6CDT' ) $v_timezone = 'CDT';
if ($v_timezone == 'EST5EDT' ) $v_timezone = 'EDT';
if ($v_timezone == 'America/Puerto_Rico' ) $v_timezone = 'AST';
if ($v_timezone == 'America/Halifax' ) $v_timezone = 'ADT';

// List supported languages
exec (HESTIA_CMD."v-list-sys-languages json", $output, $return_var);
$languages = json_decode(implode('', $output), true);
unset($output);

// List dns cluster hosts
exec (HESTIA_CMD."v-list-remote-dns-hosts json", $output, $return_var);
$dns_cluster = json_decode(implode('', $output), true);
unset($output);
foreach ($dns_cluster as $key => $value) {
    $v_dns_cluster = 'yes';
}

// List Database hosts
exec (HESTIA_CMD."v-list-database-hosts json", $output, $return_var);
$db_hosts = json_decode(implode('', $output), true);
unset($output);
$v_mysql_hosts = array_values(array_filter($db_hosts, function($host){return $host['TYPE'] === 'mysql';}));
$v_mysql = count($v_mysql_hosts) ? 'yes' : 'no';
$v_pgsql_hosts = array_values(array_filter($db_hosts, function($host){return $host['TYPE'] === 'pgsql';}));
$v_pgsql = count($v_pgsql_hosts) ? 'yes' : 'no';
unset($db_hosts);

// List backup settings
$v_backup_dir = "/backup";
if (!empty($_SESSION['BACKUP'])) $v_backup_dir = $_SESSION['BACKUP'];
$v_backup_gzip = '5';
if (!empty($_SESSION['BACKUP_GZIP'])) $v_backup_gzip = $_SESSION['BACKUP_GZIP'];
$backup_types = explode(",",$_SESSION['BACKUP_SYSTEM']);
foreach ($backup_types as $backup_type) {
    if ($backup_type == 'local') {
        $v_backup = 'yes';
    } else {
        exec (HESTIA_CMD."v-list-backup-host ".$backup_type. " json", $output, $return_var);
        $v_remote_backup = json_decode(implode('', $output), true);
        unset($output);
        $v_backup_host = $v_remote_backup[$backup_type]['HOST'];
        $v_backup_type = $v_remote_backup[$backup_type]['TYPE'];
        $v_backup_username = $v_remote_backup[$backup_type]['USERNAME'];
        $v_backup_password = "";
        $v_backup_port = $v_remote_backup[$backup_type]['PORT'];
        $v_backup_bpath = $v_remote_backup[$backup_type]['BPATH'];
    }
}

// List ssl certificate info
exec (HESTIA_CMD."v-list-sys-hestia-ssl json", $output, $return_var);
$ssl_str = json_decode(implode('', $output), true);
unset($output);
$v_ssl_crt = $ssl_str['HESTIA']['CRT'];
$v_ssl_key = $ssl_str['HESTIA']['KEY'];
$v_ssl_ca = $ssl_str['HESTIA']['CA'];
$v_ssl_subject = $ssl_str['HESTIA']['SUBJECT'];
$v_ssl_aliases = $ssl_str['HESTIA']['ALIASES'];
$v_ssl_not_before = $ssl_str['HESTIA']['NOT_BEFORE'];
$v_ssl_not_after = $ssl_str['HESTIA']['NOT_AFTER'];
$v_ssl_signature = $ssl_str['HESTIA']['SIGNATURE'];
$v_ssl_pub_key = $ssl_str['HESTIA']['PUB_KEY'];
$v_ssl_issuer = $ssl_str['HESTIA']['ISSUER'];

// Check POST request
if (!empty($_POST['save'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    // Change hostname
    if ((!empty($_POST['v_hostname'])) && ($v_hostname != $_POST['v_hostname'])) {
        exec (HESTIA_CMD."v-change-sys-hostname ".escapeshellarg($_POST['v_hostname']), $output, $return_var);
        check_return_code($return_var,$output);
        unset($output);
        $v_hostname = $_POST['v_hostname'];
    }

    // Change timezone
    if (empty($_SESSION['error_msg'])) {
        if (!empty($_POST['v_timezone'])) {
            $v_tz = $_POST['v_timezone'];
            if ($v_tz == 'UTC' ) $v_tz = 'Etc/UTC';
            if ($v_tz == 'HAST' ) $v_tz = 'Pacific/Honolulu';
            if ($v_tz == 'HADT' ) $v_tz = 'US/Aleutian';
            if ($v_tz == 'AKST' ) $v_tz = 'Etc/GMT+9';
            if ($v_tz == 'AKDT' ) $v_tz = 'America/Anchorage';
            if ($v_tz == 'PST' ) $v_tz = 'America/Dawson_Creek';
            if ($v_tz == 'PDT' ) $v_tz = 'PST8PDT';
            if ($v_tz == 'MDT' ) $v_tz = 'MST7MDT';
            if ($v_tz == 'CST' ) $v_tz = 'Canada/Saskatchewan';
            if ($v_tz == 'CDT' ) $v_tz = 'CST6CDT';
            if ($v_tz == 'EDT' ) $v_tz = 'EST5EDT';
            if ($v_tz == 'AST' ) $v_tz = 'America/Puerto_Rico';
            if ($v_tz == 'ADT' ) $v_tz = 'America/Halifax';

            if ($v_timezone != $v_tz) {
                exec (HESTIA_CMD."v-change-sys-timezone ".escapeshellarg($v_tz), $output, $return_var);
                check_return_code($return_var,$output);
                $v_timezone = $v_tz;
                unset($output);
            }
        }
    }

    // Change default language
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_language'])) && ($_SESSION['LANGUAGE'] != $_POST['v_language'])) {
            exec (HESTIA_CMD."v-change-sys-language ".escapeshellarg($_POST['v_language']), $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $_SESSION['LANGUAGE'] = $_POST['v_language'];
        }
    }

    // Set disk_quota support
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_quota'])) && ($_SESSION['DISK_QUOTA'] != $_POST['v_quota'])) {
            if($_POST['v_quota'] == 'yes') {
                exec (HESTIA_CMD."v-add-sys-quota", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                if (empty($_SESSION['error_msg'])) $_SESSION['DISK_QUOTA'] = 'yes';
            } else {
                exec (HESTIA_CMD."v-delete-sys-quota", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                if (empty($_SESSION['error_msg'])) $_SESSION['DISK_QUOTA'] = 'no';
            }
        }
    }

    // Set firewall support
    if (empty($_SESSION['error_msg'])) {
        if ($_SESSION['FIREWALL_SYSTEM'] == 'iptables') $v_firewall = 'yes';
        if ($_SESSION['FIREWALL_SYSTEM'] != 'iptables') $v_firewall = 'no';
        if ((!empty($_POST['v_firewall'])) && ($v_firewall != $_POST['v_firewall'])) {
            if($_POST['v_firewall'] == 'yes') {
                exec (HESTIA_CMD."v-add-sys-firewall", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                if (empty($_SESSION['error_msg'])) $_SESSION['FIREWALL_SYSTEM'] = 'iptables';
            } else {
                exec (HESTIA_CMD."v-delete-sys-firewall", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                if (empty($_SESSION['error_msg'])) $_SESSION['FIREWALL_SYSTEM'] = '';
            }
        }
    }

    // Update mysql pasword
    if (empty($_SESSION['error_msg'])) {
        if (!empty($_POST['v_mysql_password'])) {
            exec (HESTIA_CMD."v-change-database-host-password mysql localhost root '".escapeshellarg($_POST['v_mysql_password'])."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_db_adv = 'yes';
        }
    }

    // Update webmail url
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_mail_url'] != $_SESSION['MAIL_URL']) {
            exec (HESTIA_CMD."v-change-sys-config-value MAIL_URL '".escapeshellarg($_POST['v_mail_url'])."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_mail_adv = 'yes';
        }
    }

    // Update phpMyAdmin url
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_mysql_url'] != $_SESSION['DB_PMA_URL']) {
            exec (HESTIA_CMD."v-change-sys-config-value DB_PMA_URL '".escapeshellarg($_POST['v_mysql_url'])."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_db_adv = 'yes';
        }
    }

    // Update phpPgAdmin url
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_pgsql_url'] != $_SESSION['DB_PGA_URL']) {
            exec (HESTIA_CMD."v-change-sys-config-value DB_PGA_URL '".escapeshellarg($_POST['v_pgsql_url'])."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            $v_db_adv = 'yes';
        }
    }

    // Disable local backup
    if (empty($_SESSION['error_msg'])) {
        if (($_POST['v_backup'] == 'no') && ($v_backup == 'yes' )) {
            exec (HESTIA_CMD."v-delete-backup-host local", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup = 'no';
            $v_backup_adv = 'yes';
        }
    }

    // Enable local backups
    if (empty($_SESSION['error_msg'])) {
        if (($_POST['v_backup'] == 'yes') && ($v_backup != 'yes' )) {
            exec (HESTIA_CMD."v-add-backup-host local", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup = 'yes';
            $v_backup_adv = 'yes';
        }
    }

    // Change backup gzip level
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_backup_gzip'] != $v_backup_gzip ) {
            exec (HESTIA_CMD."v-change-sys-config-value BACKUP_GZIP ".escapeshellarg($_POST['v_backup_gzip']), $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup_gzip = $_POST['v_backup_gzip'];
            $v_backup_adv = 'yes';
        }
    }

    // Change backup path
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_backup_dir'] != $v_backup_dir ) {
            exec (HESTIA_CMD."v-change-sys-config-value BACKUP ".escapeshellarg($_POST['v_backup_dir']), $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup_dir = $_POST['v_backup_dir'];
            $v_backup_adv = 'yes';
        }
    }

    // Add remote backup host
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_backup_host'])) && (empty($v_backup_host))) {
            $v_backup_host = escapeshellarg($_POST['v_backup_host']);
            $v_backup_type = escapeshellarg($_POST['v_backup_type']);
            $v_backup_username = escapeshellarg($_POST['v_backup_username']);
            $v_backup_password = escapeshellarg($_POST['v_backup_password']);
            $v_backup_bpath = escapeshellarg($_POST['v_backup_bpath']);
            exec (HESTIA_CMD."v-add-backup-host '". $v_backup_type ."' '". $v_backup_host ."' '". $v_backup_username ."' '". $v_backup_password ."' '". $v_backup_bpath ."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup_host = $_POST['v_backup_host'];
            if (empty($_SESSION['error_msg'])) $v_backup_type = $_POST['v_backup_type'];
            if (empty($_SESSION['error_msg'])) $v_backup_username = $_POST['v_backup_username'];
            if (empty($_SESSION['error_msg'])) $v_backup_password = $_POST['v_backup_password'];
            if (empty($_SESSION['error_msg'])) $v_backup_bpath = $_POST['v_backup_bpath'];
            $v_backup_new = 'yes';
            $v_backup_adv = 'yes';
            $v_backup_remote_adv = 'yes';
        }
    }

    // Change remote backup host type
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_backup_host'])) && ($_POST['v_backup_type'] != $v_backup_type)) {
            exec (HESTIA_CMD."v-delete-backup-host '". $v_backup_type ."'", $output, $return_var);
            unset($output);

            $v_backup_host = escapeshellarg($_POST['v_backup_host']);
            $v_backup_type = escapeshellarg($_POST['v_backup_type']);
            $v_backup_username = escapeshellarg($_POST['v_backup_username']);
            $v_backup_password = escapeshellarg($_POST['v_backup_password']);
            $v_backup_bpath = escapeshellarg($_POST['v_backup_bpath']);
            exec (HESTIA_CMD."v-add-backup-host '". $v_backup_type ."' '". $v_backup_host ."' '". $v_backup_username ."' '". $v_backup_password ."' '". $v_backup_bpath ."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup_host = $_POST['v_backup_host'];
            if (empty($_SESSION['error_msg'])) $v_backup_type = $_POST['v_backup_type'];
            if (empty($_SESSION['error_msg'])) $v_backup_username = $_POST['v_backup_username'];
            if (empty($_SESSION['error_msg'])) $v_backup_password = $_POST['v_backup_password'];
            if (empty($_SESSION['error_msg'])) $v_backup_bpath = $_POST['v_backup_bpath'];
            $v_backup_adv = 'yes';
            $v_backup_remote_adv = 'yes';
        }
    }

    // Change remote backup host
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_backup_host'])) && ($_POST['v_backup_type'] == $v_backup_type) && (!isset($v_backup_new))) {
            if (($_POST['v_backup_host'] != $v_backup_host) || ($_POST['v_backup_username'] != $v_backup_username) || ($_POST['v_backup_password'] != $v_backup_password) || ($_POST['v_backup_bpath'] != $v_backup_bpath)){
                $v_backup_host = escapeshellarg($_POST['v_backup_host']);
                $v_backup_type = escapeshellarg($_POST['v_backup_type']);
                $v_backup_username = escapeshellarg($_POST['v_backup_username']);
                $v_backup_password = escapeshellarg($_POST['v_backup_password']);
                $v_backup_bpath = escapeshellarg($_POST['v_backup_bpath']);
                exec (HESTIA_CMD."v-add-backup-host '". $v_backup_type ."' '". $v_backup_host ."' '". $v_backup_username ."' '". $v_backup_password ."' '". $v_backup_bpath ."'", $output, $return_var);
                check_return_code($return_var,$output);
                unset($output);
                if (empty($_SESSION['error_msg'])) $v_backup_host = $_POST['v_backup_host'];
                if (empty($_SESSION['error_msg'])) $v_backup_type = $_POST['v_backup_type'];
                if (empty($_SESSION['error_msg'])) $v_backup_username = $_POST['v_backup_username'];
                if (empty($_SESSION['error_msg'])) $v_backup_password = $_POST['v_backup_password'];
                if (empty($_SESSION['error_msg'])) $v_backup_bpath = $_POST['v_backup_bpath'];
                $v_backup_adv = 'yes';
                $v_backup_remote_adv = 'yes';
            }
        }
    }

    // Delete remote backup host
    if (empty($_SESSION['error_msg'])) {
        if ((empty($_POST['v_backup_host'])) && (!empty($v_backup_host))) {
            exec (HESTIA_CMD."v-delete-backup-host '". $v_backup_type ."'", $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);
            if (empty($_SESSION['error_msg'])) $v_backup_host = '';
            if (empty($_SESSION['error_msg'])) $v_backup_type = '';
            if (empty($_SESSION['error_msg'])) $v_backup_username = '';
            if (empty($_SESSION['error_msg'])) $v_backup_password = '';
            if (empty($_SESSION['error_msg'])) $v_backup_bpath = '';
            $v_backup_adv = '';
            $v_backup_remote_adv = '';
        }
    }

    // Update SSL certificate
    if ((!empty($_POST['v_ssl_crt'])) && (empty($_SESSION['error_msg']))) {
        if (($v_ssl_crt != str_replace("\r\n", "\n",  $_POST['v_ssl_crt'])) || ($v_ssl_key != str_replace("\r\n", "\n",  $_POST['v_ssl_key']))) {
            exec ('mktemp -d', $mktemp_output, $return_var);
            $tmpdir = $mktemp_output[0];

            // Certificate
            if (!empty($_POST['v_ssl_crt'])) {
                $fp = fopen($tmpdir."/certificate.crt", 'w');
                fwrite($fp, str_replace("\r\n", "\n",  $_POST['v_ssl_crt']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            // Key
            if (!empty($_POST['v_ssl_key'])) {
                $fp = fopen($tmpdir."/certificate.key", 'w');
                fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
                fwrite($fp, "\n");
                fclose($fp);
            }

            exec (HESTIA_CMD."v-change-sys-hestia-ssl ".$tmpdir, $output, $return_var);
            check_return_code($return_var,$output);
            unset($output);

            // List ssl certificate info
            exec (HESTIA_CMD."v-list-sys-hestia-ssl json", $output, $return_var);
            $ssl_str = json_decode(implode('', $output), true);
            unset($output);
            $v_ssl_crt = $ssl_str['HESTIA']['CRT'];
            $v_ssl_key = $ssl_str['HESTIA']['KEY'];
            $v_ssl_ca = $ssl_str['HESTIA']['CA'];
            $v_ssl_subject = $ssl_str['HESTIA']['SUBJECT'];
            $v_ssl_aliases = $ssl_str['HESTIA']['ALIASES'];
            $v_ssl_not_before = $ssl_str['HESTIA']['NOT_BEFORE'];
            $v_ssl_not_after = $ssl_str['HESTIA']['NOT_AFTER'];
            $v_ssl_signature = $ssl_str['HESTIA']['SIGNATURE'];
            $v_ssl_pub_key = $ssl_str['HESTIA']['PUB_KEY'];
            $v_ssl_issuer = $ssl_str['HESTIA']['ISSUER'];
        }
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }

// Check system configuration
exec (HESTIA_CMD . "v-list-sys-config json", $output, $return_var);
$data = json_decode(implode('', $output), true);
unset($output);

$sys_arr = $data['config'];
foreach ($sys_arr as $key => $value) {
    $_SESSION[$key] = $value;
}


// Render page
render_page($user, $TAB, 'edit_server');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
