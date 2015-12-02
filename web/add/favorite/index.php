<?

error_reporting(NULL);
session_start();


include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check token
//    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
//        header('location: /login/');
//        exit;
//    }

    $v_section = $_REQUEST['v_section'];
    $v_unit_id = $_REQUEST['v_unit_id'];

    $_SESSION['favourites'][strtoupper((string)$v_section)][(string)$v_unit_id] = 1;

    v_exec('v-add-user-favourites', [$_SESSION['user'], $v_section, $v_unit_id], false/*true*/);
?>