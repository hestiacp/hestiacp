<?php

ob_start();
$TAB = 'WEB';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");
require_once $_SERVER['DOCUMENT_ROOT']."/src/init.php";

// Check domain argument
if (empty($_GET['domain'])) {
    header("Location: /list/web/");
    exit;
}

// Edit as someone else?
if (($_SESSION['user'] == 'admin') && (!empty($_GET['user']))) {
    $user=escapeshellarg($_GET['user']);
}

// Check if domain belongs to the user
$v_domain = $_GET['domain'];
exec(HESTIA_CMD."v-list-web-domain ".$user." ".escapeshellarg($v_domain)." json", $output, $return_var);
if ($return_var > 0){
    check_return_code_redirect($return_var, $output, '/list/web/');
}

unset($output);

// Check GET request
if (!empty($_GET['app'])) {
    $app = basename($_GET['app']);

    $hestia = new \Hestia\System\HestiaApp();
    $app_installer_class = '\Hestia\WebApp\Installers\\'.$app.'\\' . $app . 'Setup';
    if (class_exists($app_installer_class)) {
        try {
            $app_installer = new $app_installer_class($v_domain, $hestia);
            $info = $app_installer -> info();
            if ($info['enabled'] != true) {
                $_SESSION['error_msg'] = sprintf(_('%s installer missing'), $app);
            } else {
                $installer = new \Hestia\WebApp\AppWizard($app_installer, $v_domain, $hestia);
                $GLOBALS['WebappInstaller'] = $installer;
            }
        } catch (Exception $e) {
            $_SESSION['error_msg'] = $e->getMessage();
            header('Location: /add/webapp/?domain=' . $v_domain);
            exit();
        }
    } else {
        $_SESSION['error_msg'] = sprintf(_('%s installer missing'), $app);
    }
}

// Check POST request
if (!empty($_POST['ok']) && !empty($app)) {

    // Check token
    verify_csrf($_POST);

    if ($installer) {
        try {
            if (!$installer->execute($_POST)) {
                $result = $installer->getStatus();
                if (!empty($result)) {
                    $_SESSION['error_msg'] = implode(PHP_EOL, $result);
                }
            } else {
                $_SESSION['ok_msg'] = sprintf(_('%s App was installed succesfully!'), htmlspecialchars($app));
                header('Location: /add/webapp/?domain=' . $v_domain);
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['error_msg'] = $e->getMessage();
            header('Location: /add/webapp/?app='.rawurlencode($app).'&domain=' . $v_domain);
            exit();
        }
    }
}

if (!empty($installer)) {
    render_page($user, $TAB, 'setup_webapp');
} else {
    $appInstallers = glob(__DIR__.'/../../src/app/WebApp/Installers/*/*.php');
    $v_web_apps = array();
    foreach ($appInstallers as $app) {
        $hestia = new \Hestia\System\HestiaApp();
        if (preg_match('/Installers\/([a-zA-Z][a-zA-Z0,9].*)\/([a-zA-Z][a-zA-Z0,9].*).php/', $app, $matches)) {
            if ($matches[1] != "Resources") {
                $app_installer_class = '\Hestia\WebApp\Installers\\'.$matches[1].'\\' . $matches[1] . 'Setup';
                $app_installer = new $app_installer_class($v_domain, $hestia);
                $v_web_apps[] = $app_installer -> info();
            }
        }
    }
    render_page($user, $TAB, 'list_webapps');
}


// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
