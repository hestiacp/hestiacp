<?

error_reporting(NULL);
session_start();


include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
//    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
//        header('location: /login/');
//        exit();
//    }

    // Protect input
    $v_section = escapeshellarg($_REQUEST['v_section']);
    $v_unit_id = escapeshellarg($_REQUEST['v_unit_id']);

    $_SESSION['favourites'][strtoupper($_REQUEST['v_section'])][$_REQUEST['v_unit_id']] = 1;

    exec (VESTA_CMD."v-add-user-favourites ".$_SESSION['user']." ".$v_section." ".$v_unit_id, $output, $return_var);
//    check_return_code($return_var,$output);
?>