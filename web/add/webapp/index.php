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

// Get all user domains 
exec (HESTIA_CMD."v-list-web-domains ".escapeshellarg($user)." json", $output, $return_var);
$user_domains = json_decode(implode('', $output), true);
$user_domains = array_keys($user_domains);
unset($output);

// List domain
$v_domain = $_GET['domain'];
if(!in_array($v_domain, $user_domains)) {
    header("Location: /list/web/");
    exit;
}

$v_web_apps = [
    [ 'name'=>'Wordpress', 'group'=>'cms', 'enabled'=>true, 'version'=>'latest', 'thumbnail'=>'/images/webapps/wp-thumb.png' ],
    [ 'name'=>'Drupal',    'group'=>'cms', 'enabled'=>false,'version'=>'latest', 'thumbnail'=>'/images/webapps/drupal-thumb.png' ],
    [ 'name'=>'Joomla',    'group'=>'cms', 'enabled'=>false,'version'=>'latest', 'thumbnail'=>'/images/webapps/joomla-thumb.png' ],

    [ 'name'=>'Opencart',   'group'=>'ecommerce', 'enabled'=>true,  'version'=>'3.0.3.3', 'thumbnail'=>'/images/webapps/opencart-thumb.png' ],
    [ 'name'=>'Prestashop', 'group'=>'ecommerce', 'enabled'=>true, 'version'=>'1.7.6.5', 'thumbnail'=>'/images/webapps/prestashop-thumb.png' ],

    [ 'name'=>'Laravel', 'group'=>'starter', 'enabled'=>true, 'version'=>'7.x', 'thumbnail'=>'/images/webapps/laravel-thumb.png' ],
    [ 'name'=>'Symfony', 'group'=>'starter', 'enabled'=>true, 'version'=>'4.3.x', 'thumbnail'=>'/images/webapps/symfony-thumb.png' ],
];

// Check GET request
if (!empty($_GET['app'])) {
    $app = basename($_GET['app']);
    
    $hestia = new \Hestia\System\HestiaApp();
    $app_installer_class = '\Hestia\WebApp\Installers\\' . $app . 'Setup';
    if(class_exists($app_installer_class)) {
        try {
            $app_installer = new $app_installer_class($v_domain, $hestia);
            $installer = new \Hestia\WebApp\AppWizard($app_installer, $v_domain, $hestia);
            $GLOBALS['WebappInstaller'] = $installer;
        } catch (Exception $e) {
            $_SESSION['error_msg'] = $e->getMessage();
            header('Location: /add/webapp/?domain=' . $v_domain);
            exit();
        }
    } else {
        $_SESSION['error_msg'] = sprintf(_('%s installer missing'),$app);
    }
}

// Check POST request
if (!empty($_POST['ok']) && !empty($app) ) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    if ($installer) {
        try{
            if (!$installer->execute($_POST)){
                $result = $installer->getStatus();
                if(!empty($result))
                    $_SESSION['error_msg'] = implode(PHP_EOL, $result);
            } else {
                $_SESSION['ok_msg'] = sprintf(_('%s App was installed succesfully!'),htmlspecialchars($app));
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

if(!empty($installer)) {
    render_page($user, $TAB, 'setup_webapp');
} else {
    render_page($user, $TAB, 'list_webapps');
}


// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
