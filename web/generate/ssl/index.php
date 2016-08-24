<?php
error_reporting(NULL);
$TAB = 'WEB';

// Main include
include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Prepare values
if (!empty($_GET['domain'])) {
    $v_domain = $_GET['domain'];
} else {
    $v_domain = 'example.ltd';
}
$v_email = 'admin@' . $v_domain;
$v_country = 'US';
$v_state = 'California';
$v_locality = 'San Francisco';
$v_org = 'MyCompany LLC';
$v_org_unit = 'IT';

// Back uri
$_SESSION['back'] = '';

// Check POST
if (!isset($_POST['generate'])) {
    render_page($user, $TAB, 'generate_ssl');
    exit;
}

// Check input
if (empty($_POST['v_domain'])) $errors[] = __('Domain');
if (empty($_POST['v_country'])) $errors[] = __('Country');
if (empty($_POST['v_state'])) $errors[] = __('State');
if (empty($_POST['v_locality'])) $errors[] = __('City');
if (empty($_POST['v_org'])) $errors[] = __('Organization');
if (empty($_POST['v_email'])) $errors[] = __('Email');
$v_domain = $_POST['v_domain'];
$v_email = $_POST['v_email'];
$v_country = $_POST['v_country'];
$v_state = $_POST['v_state'];
$v_locality = $_POST['v_locality'];
$v_org = $_POST['v_org'];

// Check for errors
if (!empty($errors[0])) {
    foreach ($errors as $i => $error) {
        if ( $i == 0 ) {
            $error_msg = $error;
        } else {
            $error_msg = $error_msg.", ".$error;
        }
    }
    $_SESSION['error_msg'] = __('Field "%s" can not be blank.',$error_msg);
    render_page($user, $TAB, 'generate_ssl');
    unset($_SESSION['error_msg']);
    exit;
}

// Protect input
$v_domain = escapeshellarg($_POST['v_domain']);
$v_email = escapeshellarg($_POST['v_email']);
$v_country = escapeshellarg($_POST['v_country']);
$v_state = escapeshellarg($_POST['v_state']);
$v_locality = escapeshellarg($_POST['v_locality']);
$v_org = escapeshellarg($_POST['v_org']);

exec (VESTA_CMD."v-generate-ssl-cert ".$v_domain." ".$v_email." ".$v_country." ".$v_state." ".$v_locality." ".$v_org." IT '' json", $output, $return_var);

// Revert to raw values
$v_domain = $_POST['v_domain'];
$v_email = $_POST['v_email'];
$v_country = $_POST['v_country'];
$v_state = $_POST['v_state'];
$v_locality = $_POST['v_locality'];
$v_org = $_POST['v_org'];

// Check return code
if ($return_var != 0) {
    $error = implode('<br>', $output);
    if (empty($error)) $error = __('Error code:',$return_var);
    $_SESSION['error_msg'] = $error;
    render_page($user, $TAB, 'generate_ssl');
    unset($_SESSION['error_msg']);
    exit;
}

// OK message
$_SESSION['ok_msg'] = __('SSL_GENERATED_OK');

// Parse output
$data = json_decode(implode('', $output), true);
unset($output);
$v_crt = $data[$v_domain]['CRT'];
$v_key = $data[$v_domain]['KEY'];
$v_csr = $data[$v_domain]['CSR'];

// Back uri
$_SESSION['back'] = $_SERVER['REQUEST_URI'];

// Render page
render_page($user, $TAB, 'list_ssl');

unset($_SESSION['ok_msg']);
