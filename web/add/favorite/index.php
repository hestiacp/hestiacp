<?

echo 'adding favorite <br><br>';

// Init
error_reporting(NULL);
ob_start();
session_start();


// mail_acc
// firewall

include($_SERVER['DOCUMENT_ROOT']."/inc/main.php");

// Check POST request
//if (!empty($_POST['ok'])) {

    // Check token
//    if ((!isset($_POST['token'])) || ($_SESSION['token'] != $_POST['token'])) {
//        header('location: /login/');
//        exit();
//    }


    // v-list-user-favourites
    // v-delete-user-favourites admin web test0011.com

    // Protect input
//    $v_section = escapeshellarg($_POST['v_section']);
//    $v_unit_id = escapeshellarg($_POST['v_unit_id']);

    $v_section = escapeshellarg($_REQUEST['v_section']);
    $v_unit_id = escapeshellarg($_REQUEST['v_unit_id']);

//    $v_section = 'web';
//    $v_unit_id = 'test0011.com';


    echo VESTA_CMD."v-add-user-favourites ".$_SESSION['user']." ".$v_section." ".$v_unit_id;

    echo ' - ';

    // Add cron job
    exec (VESTA_CMD."v-add-user-favourites ".$_SESSION['user']." ".$v_section." ".$v_unit_id, $output, $return_var);
    check_return_code($return_var,$output);

    var_dump($return_var);
    echo '<br> -------------------- <br>';

    var_dump($output);
    echo '<br> -------------------- <br>';


/*
    echo '<br>favorites:<br>';

    // Data
    exec (VESTA_CMD."v-list-user-favourites $user json", $output, $return_var);
    $data = json_decode(implode('', $output), true);
    $data = array_reverse($data,true);
    print_r($data);
*/
//}
?>