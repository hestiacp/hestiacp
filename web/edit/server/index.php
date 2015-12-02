<?php
// Init
error_reporting(NULL);
$TAB = 'SERVER';

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
v_exec('v-get-sys-timezone', [], false, $output);
$v_timezone = strtok($output, "\n");
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
v_exec('v-list-sys-languages', ['json'], false, $output);
$languages = json_decode($output, true);

// List dns cluster hosts
v_exec('v-list-remote-dns-hosts', ['json'], false, $output);
$dns_cluster = json_decode($output, true);
if (count($dns_cluster) >= 1) $v_dns_cluster = 'yes';

// List MySQL hosts
v_exec('v-list-database-hosts', ['mysql', 'json'], false, $output);
$v_mysql_hosts = json_decode($output, true);
if (count($v_mysql_hosts) >= 1) $v_mysql = 'yes';

// List PostgreSQL hosts
v_exec('v-list-database-hosts', ['pgsql', 'json'], false, $output);
$v_pgsql_hosts = json_decode($output, true);
if (count($v_pgsql_hosts) >= 1) $v_psql = 'yes';

// List backup settings
$v_backup_dir = '/backup';
if (!empty($_SESSION['BACKUP'])) $v_backup_dir = $_SESSION['BACKUP'];
$v_backup_gzip = '5';
if (!empty($_SESSION['BACKUP_GZIP'])) $v_backup_gzip = $_SESSION['BACKUP_GZIP'];
$backup_types = explode(',', $_SESSION['BACKUP_SYSTEM']);
foreach ($backup_types as $backup_type) {
    if ($backup_type == 'local') {
        $v_backup = 'yes';
    } else {
        v_exec('v-list-backup-host', [$backup_type, 'json'], false, $output);
        $v_remote_backup = json_decode($output, true);
        $v_backup_host = $v_remote_backup[$backup_type]['HOST'];
        $v_backup_type = $v_remote_backup[$backup_type]['TYPE'];
        $v_backup_username = $v_remote_backup[$backup_type]['USERNAME'];
        $v_backup_password = '';
        $v_backup_port = $v_remote_backup[$backup_type]['PORT'];
        $v_backup_bpath = $v_remote_backup[$backup_type]['BPATH'];
    }
}

// Check POST request
if (!empty($_POST['save'])) {
    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit;
    }

    // Change hostname
    if ((!empty($_POST['v_hostname'])) && ($v_hostname != $_POST['v_hostname'])) {
        $v_hostname = $_POST['v_hostname'];
        v_exec('v-change-sys-hostname', [$v_hostname]);
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
                $v_timezone = $v_tz;
                v_exec('v-change-sys-timezone', [$v_timezone]);
            }
        }
    }

    // Change default language
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_language'])) && ($_SESSION['LANGUAGE'] != $_POST['v_language'])) {
            v_exec('v-change-sys-language', [$_POST['v_language']]);
            if (empty($_SESSION['error_msg'])) $_SESSION['LANGUAGE'] = $_POST['v_language'];
        }
    }

    // Set disk_quota support
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_quota'])) && ($_SESSION['DISK_QUOTA'] != $_POST['v_quota'])) {
            if($_POST['v_quota'] == 'yes') {
                v_exec('v-add-sys-quota');
                if (empty($_SESSION['error_msg'])) $_SESSION['DISK_QUOTA'] = 'yes';
            } else {
                v_exec('v-delete-sys-quota');
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
                v_exec('v-add-sys-firewall');
                if (empty($_SESSION['error_msg'])) $_SESSION['FIREWALL_SYSTEM'] = 'iptables';
            } else {
                v_exec('v-delete-sys-firewall');
                if (empty($_SESSION['error_msg'])) $_SESSION['FIREWALL_SYSTEM'] = '';
            }
        }
    }

    // Update mysql pasword
    if (empty($_SESSION['error_msg'])) {
        if (!empty($_POST['v_mysql_password'])) {
            v_exec('v-change-database-host-password', ['mysql', 'localhost', 'root', $_POST['v_mysql_password']]);
            $v_db_adv = 'yes';
        }
    }


    // Update webmail url
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_mail_url'] != $_SESSION['MAIL_URL']) {
            v_exec('v-change-sys-config-value', ['MAIL_URL', $_POST['v_mail_url']]);
            $v_mail_adv = 'yes';
        }
    }

    // Update phpMyAdmin url
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_mysql_url'] != $_SESSION['DB_PMA_URL']) {
            v_exec('v-change-sys-config-value', ['DB_PMA_URL', $_POST['v_mysql_url']]);
            $v_db_adv = 'yes';
        }
    }

    // Update phpPgAdmin url
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_psql_url'] != $_SESSION['DB_PGA_URL']) {
            v_exec('v-change-sys-config-value', ['DB_PGA_URL', $_POST['v_pgsql_url']]);
            $v_db_adv = 'yes';
        }
    }

    // Disable local backup
    if (empty($_SESSION['error_msg'])) {
        if (($_POST['v_backup'] == 'no') && ($v_backup == 'yes')) {
            v_exec('v-delete-backup-host', ['local']);
            if (empty($_SESSION['error_msg'])) $v_backup = 'no';
            $v_backup_adv = 'yes';
        }
    }

    // Enable local backups
    if (empty($_SESSION['error_msg'])) {
        if (($_POST['v_backup'] == 'yes') && ($v_backup != 'yes' )) {
            v_exec('v-add-backup-host', ['local']);
            if (empty($_SESSION['error_msg'])) $v_backup = 'yes';
            $v_backup_adv = 'yes';
        }
    }


    // Change backup gzip level
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_backup_gzip'] != $v_backup_gzip ) {
            v_exec('v-change-sys-config-value', ['BACKUP_GZIP', $_POST['v_backup_gzip']]);
            if (empty($_SESSION['error_msg'])) $v_backup_gzip = $_POST['v_backup_gzip'];
            $v_backup_adv = 'yes';
        }
    }

    // Change backup path
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_backup_dir'] != $v_backup_dir ) {
            v_exec('v-change-sys-config-value', ['BACKUP', $_POST['v_backup_dir']]);
            if (empty($_SESSION['error_msg'])) $v_backup_dir = $_POST['v_backup_dir'];
            $v_backup_adv = 'yes';
        }
    }

    // Add remote backup host
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_backup_host'])) && (empty($v_backup_host))) {
            $v_backup_host = $_POST['v_backup_host'];
            $v_backup_type = $_POST['v_backup_type'];
            $v_backup_username = $_POST['v_backup_username'];
            $v_backup_password = $_POST['v_backup_password'];
            $v_backup_bpath = $_POST['v_backup_bpath'];
            v_exec('v-add-backup-host', [$v_backup_type, $v_backup_host, $v_backup_username, $v_backup_password, $v_backup_bpath]);
            $v_backup_new = 'yes';
            $v_backup_adv = 'yes';
            $v_backup_remote_adv = 'yes';
        }
    }

    // Change remote backup host type
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_backup_host'])) && ($_POST['v_backup_type'] != $v_backup_type)) {
            v_exec('v-delete-backup-host', [$v_backup_type], false);

            $v_backup_host = $_POST['v_backup_host'];
            $v_backup_type = $_POST['v_backup_type'];
            $v_backup_username = $_POST['v_backup_username'];
            $v_backup_password = $_POST['v_backup_password'];
            $v_backup_bpath = $_POST['v_backup_bpath'];
            v_exec('v-add-backup-host', [$v_backup_type, $v_backup_host, $v_backup_username, $v_backup_password, $v_backup_bpath]);
            $v_backup_adv = 'yes';
            $v_backup_remote_adv = 'yes';
        }
    }

    // Change remote backup host
    if (empty($_SESSION['error_msg'])) {
        if ((!empty($_POST['v_backup_host'])) && ($_POST['v_backup_type'] == $v_backup_type) && (!isset($v_backup_new))) {
            if (($_POST['v_backup_host'] != $v_backup_host) || ($_POST['v_backup_username'] != $v_backup_username) || ($_POST['v_backup_password'] || $v_backup_password) || ($_POST['v_backup_bpath'] == $v_backup_bpath)){
                $v_backup_host = $_POST['v_backup_host'];
                $v_backup_type = $_POST['v_backup_type'];
                $v_backup_username = $_POST['v_backup_username'];
                $v_backup_password = $_POST['v_backup_password'];
                $v_backup_bpath = $_POST['v_backup_bpath'];
                v_exec('v-add-backup-host', [$v_backup_type, $v_backup_host, $v_backup_username, $v_backup_password, $v_backup_bpath]);
                $v_backup_adv = 'yes';
                $v_backup_remote_adv = 'yes';
            }
        }
    }


    // Delete remote backup host
    if (empty($_SESSION['error_msg'])) {
        if ((empty($_POST['v_backup_host'])) && (!empty($v_backup_host))) {
            v_exec('v-delete-backup-host', [$v_backup_type]);
            if (empty($_SESSION['error_msg'])) {
                $v_backup_host = '';
                $v_backup_type = '';
                $v_backup_username = '';
                $v_backup_password = '';
                $v_backup_bpath = '';
            }
            $v_backup_adv = '';
            $v_backup_remote_adv = '';
        }
    }

    // Flush field values on success
    if (empty($_SESSION['error_msg'])) {
        $_SESSION['ok_msg'] = __('Changes has been saved.');
    }

    // Activate sftp licence
    if (empty($_SESSION['error_msg'])) {
        if ($_SESSION['SFTPJAIL_KEY'] != $_POST['v_sftp_licence'] && $_POST['v_sftp'] == 'yes') {
            $module = 'sftpjail';
            $licence_key = $_POST['v_sftp_licence'];
            v_exec('v-activate-vesta-license', [$module, $licence_key]);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Licence Activated');
                $_SESSION['SFTPJAIL_KEY'] = $licence_key;
            }
        }
    }

    // Cancel sftp licence
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_sftp'] == 'cancel' && $_SESSION['SFTPJAIL_KEY']) {
            $module = 'sftpjail';
            $licence_key = $_SESSION['SFTPJAIL_KEY'];
            v_exec('v-deactivate-vesta-license', [$module, $licence_key]);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Licence Deactivated');
                unset($_SESSION['SFTPJAIL_KEY']);
            }
        }
    }


    // Activate filemanager licence
    if (empty($_SESSION['error_msg'])) {
        if ($_SESSION['FILEMANAGER_KEY'] != $_POST['v_filemanager_licence'] && $_POST['v_filemanager'] == 'yes') {
            $module = 'filemanager';
            $licence_key = $_POST['v_filemanager_licence'];
            v_exec('v-activate-vesta-license', [$module, $licence_key]);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Licence Activated');
                $_SESSION['FILEMANAGER_KEY'] = $licence_key;
            }
        }
    }

    // Cancel filemanager licence
    if (empty($_SESSION['error_msg'])) {
        if ($_POST['v_filemanager'] == 'cancel' && $_SESSION['FILEMANAGER_KEY']) {
            $module = 'filemanager';
            $licence_key = $_SESSION['FILEMANAGER_KEY'];
            v_exec('v-deactivate-vesta-license', [$module, $licence_key]);
            if (empty($_SESSION['error_msg'])) {
                $_SESSION['ok_msg'] = __('Licence Deactivated');
                unset($_SESSION['FILEMANAGER_KEY']);
            }
        }
    }
}

// Check system configuration
v_exec('v-list-sys-config', ['json'], false, $output);
$data = json_decode($output, true);
$sys_arr = $data['config'];
foreach ($sys_arr as $key => $value) {
    $_SESSION[$key] = $value;
}

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Display body
include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/edit_server.html');

// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
