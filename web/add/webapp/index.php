<?php
error_reporting(NULL);
ob_start();
$TAB = 'WEB';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

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
    [ 'name'=>'Wordpress', 'group'=>'cms','version'=>'5.2.2', 'thumbnail'=>'/images/webapps/wp-thumb.png' ],
    [ 'name'=>'Drupal', 'group'=>'cms', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/drupal-thumb.png' ],
    [ 'name'=>'Joomla', 'group'=>'cms', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/joomla-thumb.png' ],

    [ 'name'=>'Opencart', 'group'=>'ecommerce', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/opencart-thumb.png' ],
    [ 'name'=>'Prestashop', 'group'=>'ecommerce', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/prestashop-thumb.png' ],
    [ 'name'=>'Magento', 'group'=>'ecommerce', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/magento-thumb.png' ],

    [ 'name'=>'Laravel', 'group'=>'starter', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/laravel-thumb.png' ],
    [ 'name'=>'Symfony', 'group'=>'starter', 'version'=>'1.2.3', 'thumbnail'=>'/images/webapps/symfony-thumb.png' ],
];

// Check GET request
if (!empty($_GET['app'])) {
    require 'installer.php';
    try {
        $hestia = new HestiaApp();
        $installer = new AppInstaller($_GET['app'], $v_domain, $hestia);
    } catch (Exception $e) {
        $_SESSION['error_msg'] = $e->getMessage();
        header('Location: /add/webapp/?domain=' . $v_domain);
        exit();
    }
    $GLOBALS['WebappInstaller'] = $installer;
}

// Check POST request
if (!empty($_POST['ok'])) {

    // Check token
    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
        header('location: /login/');
        exit();
    }

    if ($installer) {
        if (!$installer->execute($_POST)){
            $result = $installer->getStatus();
            $_SESSION['error_msg'] = implode(PHP_EOL, $result);
        }
    }
}

if($installer) {
    render_page($user, $TAB, 'setup_webapp');
} else {
    render_page($user, $TAB, 'add_webapp');
}


// Flush session messages
unset($_SESSION['error_msg']);
unset($_SESSION['ok_msg']);
