<?php
// Init
error_reporting(NULL);
ob_start();
session_start();
$TAB = 'WEB';
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Header
include($_SERVER['DOCUMENT_ROOT'].'/templates/header.html');

// Panel
top_panel($user,$TAB);

// Are you admin?
if ($_SESSION['user'] == 'admin') {
    if (!empty($_POST['ok'])) {
        // Check input
        if (empty($_POST['v_domain'])) $errors[] = 'domain';
        if (empty($_POST['v_ip'])) $errors[] = 'ip';
        if (empty($_POST['v_template'])) $errors[] = 'template';
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_crt']))) $errors[] = 'ssl certificate';
        if ((!empty($_POST['v_ssl'])) && (empty($_POST['v_ssl_key']))) $errors[] = 'ssl key';
        if ((!empty($_POST['v_aliases'])) || (!empty($_POST['v_elog'])) || (!empty($_POST['v_ssl'])) || (!empty($_POST['v_ssl_crt'])) || (!empty($_POST['v_ssl_key'])) || (!empty($_POST['v_ssl_ca'])) || ($_POST['v_stats'] != 'none')) $v_adv = 'yes';

        // Protect input
        $v_domain = preg_replace("/^www./i", "", $_POST['v_domain']);
        $v_domain = escapeshellarg($v_domain);
        $v_ip = escapeshellarg($_POST['v_ip']);
        $v_template = escapeshellarg($_POST['v_template']);
        if (empty($_POST['v_dns'])) $v_dns = 'off';
        if (empty($_POST['v_mail'])) $v_mail = 'off';
        $v_aliases = $_POST['v_aliases'];
        $v_elog = $_POST['v_elog'];
        $v_nginx = $_POST['v_nginx'];
        $v_ssl = $_POST['v_ssl'];
        $v_ssl_crt = $_POST['v_ssl_crt'];
        $v_ssl_key = $_POST['v_ssl_key'];
        $v_ssl_ca = $_POST['v_ssl_ca'];
        $v_stats = escapeshellarg($_POST['v_stats']);

        // Check for errors
        if (!empty($errors[0])) {
            foreach ($errors as $i => $error) {
                if ( $i == 0 ) {
                    $error_msg = $error;
                } else {
                    $error_msg = $error_msg.", ".$error;
                }
            }
            $_SESSION['error_msg'] = "Error: field ".$error_msg." can not be blank.";
        } else {
            // Add WEB
            exec (VESTA_CMD."v_add_web_domain ".$user." ".$v_domain." ".$v_ip." ".$v_template." 'no'", $output, $return_var);
            if ($return_var != 0) {
                $error = implode('<br>', $output);
                if (empty($error)) $error = 'Error: vesta did not return any output.';
                $_SESSION['error_msg'] = $error;
            }
            unset($output);
        
            // Add DNS
            if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_add_dns_domain ".$user." ".$v_domain." ".$v_ip, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add Mail
            if (($_POST['v_mail'] == 'on') && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_add_mail_domain ".$user." ".$v_domain, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add Aliases
            if ((!empty($_POST['v_aliases'])) && (empty($_SESSION['error_msg']))) {
                $valiases = preg_replace("/\n/", " ", $_POST['v_aliases']);
                $valiases = preg_replace("/,/", " ", $valiases);
                $valiases = preg_replace('/\s+/', ' ',$valiases);
                $valiases = trim($valiases);
                $aliases = explode(" ", $valiases);
                foreach ($aliases as $alias) {
                    $alias = escapeshellarg($alias);
                    if (empty($_SESSION['error_msg'])) {
                        exec (VESTA_CMD."v_add_web_domain_alias ".$user." ".$v_domain." ".$alias." 'no'", $output, $return_var);
                        if ($return_var != 0) {
                            $error = implode('<br>', $output);
                            if (empty($error)) $error = 'Error: vesta did not return any output.';
                            $_SESSION['error_msg'] = $error;
                        }
                    }
                    unset($output);
                }
                if (($_POST['v_dns'] == 'on') && (empty($_SESSION['error_msg']))) {
                    exec (VESTA_CMD."v_add_dns_on_web_alias ".$user." ".$v_domain." 'no'", $output, $return_var);
                    if ($return_var != 0) {
                        $error = implode('<br>', $output);
                        if (empty($error)) $error = 'Error: vesta did not return any output.';
                        $_SESSION['error_msg'] = $error;
                    }
                    unset($output);
                }
            }

            // Add ErrorLog
            if ((!empty($_POST['v_elog'])) && (empty($_SESSION['error_msg']))) {
                exec (VESTA_CMD."v_add_web_domain_elog ".$user." ".$v_domain." 'no'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add Nginx
            if ((!empty($_POST['v_nginx'])) && (empty($_SESSION['error_msg']))) {
                $nginx_ext = "'jpg,jpeg,gif,png,ico,css,zip,tgz,gz,rar,bz2,doc,xls,exe,pdf,ppt,txt,tar,wav,bmp,rtf,js,mp3,avi,mpeg,html,htm'";
                exec (VESTA_CMD."v_add_web_domain_nginx ".$user." ".$v_domain." 'default' ".$nginx_ext." 'no'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add SSL
            if (!empty($_POST['v_ssl'])) {
                exec ('mktemp -d', $output, $return_var);
                $tmpdir = $output[0];

                // Certificate
                if (!empty($_POST['v_ssl_crt'])) {
                    $fp = fopen($tmpdir."/".$_POST['v_domain'].".crt", 'w');
                    fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_crt']));
                    fclose($fp);
                }

                // Key
                if (!empty($_POST['v_ssl_key'])) {
                    $fp = fopen($tmpdir."/".$_POST['v_domain'].".key", 'w');
                    fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_key']));
                    fclose($fp);
                }

                // CA
                if (!empty($_POST['v_ssl_ca'])) {
                    $fp = fopen($tmpdir."/".$_POST['v_domain'].".ca", 'w');
                    fwrite($fp, str_replace("\r\n", "\n", $_POST['v_ssl_ca']));
                    fclose($fp);
                }

                exec (VESTA_CMD."v_add_web_domain_ssl ".$user." ".$v_domain." ".$tmpdir." 'same' 'no'", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            // Add WebStats
            if ((!empty($_POST['v_stats'])) && ($_POST['v_stats'] != 'none' ) && (empty($_SESSION['error_msg']))) {
                $v_stats = escapeshellarg($_POST['v_stats']);
                exec (VESTA_CMD."v_add_web_domain_stats ".$user." ".$v_domain." ".$v_stats, $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
            }

            if (empty($_SESSION['error_msg'])) {
                exec (VESTA_CMD."v_restart_web", $output, $return_var);
                if ($return_var != 0) {
                    $error = implode('<br>', $output);
                    if (empty($error)) $error = 'Error: vesta did not return any output.';
                    $_SESSION['error_msg'] = $error;
                }
                unset($output);
                $_SESSION['ok_msg'] = "OK: domain <b>".$_POST[v_domain]."</b> has been created successfully.";
                unset($v_domain);
                unset($v_aliases);
                unset($v_ssl);
                unset($v_ssl_crt);
                unset($v_ssl_key);
                unset($v_ssl_ca);
            }
        }
    }

    exec (VESTA_CMD."v_list_user_ips ".$user." json", $output, $return_var);
    $ips = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v_list_web_templates ".$user." json", $output, $return_var);
    $templates = json_decode(implode('', $output), true);
    unset($output);

    exec (VESTA_CMD."v_list_web_stats json", $output, $return_var);
    $stats = json_decode(implode('', $output), true);
    unset($output);

    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/menu_add_web.html');
    include($_SERVER['DOCUMENT_ROOT'].'/templates/admin/add_web.html');
    unset($_SESSION['error_msg']);
    unset($_SESSION['ok_msg']);
}

// Footer
include($_SERVER['DOCUMENT_ROOT'].'/templates/footer.html');
