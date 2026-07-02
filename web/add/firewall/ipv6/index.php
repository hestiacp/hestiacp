<?php
use function Hestiacp\quoteshellarg\quoteshellarg;

ob_start();
$TAB = "FIREWALL";

include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

if ($_SESSION["userContext"] != "admin") {
    header("Location: /list/user");
    exit();
}

// Get IPv6 ipset lists
exec(HESTIA_CMD . "v-list-firewall-ipset-ipv6 'json'", $output, $return_var);
$ipset_data = json_decode(implode("", $output), true) ?? [];
unset($output);

$ipset_lists = [];
foreach ($ipset_data as $key => $value) {
    if (isset($value["SUSPENDED"]) && $value["SUSPENDED"] === "yes") continue;
    $ipset_lists[] = ["name" => $key];
}
$ipset_lists_json = json_encode($ipset_lists);

if (!empty($_POST["ok"])) {
    verify_csrf($_POST);

    if (empty($_POST["v_action"]))   $errors[] = _("Action");
    if (empty($_POST["v_protocol"])) $errors[] = _("Protocol");
    if (empty($_POST["v_port"]) && strlen($_POST["v_port"]) == 0) $errors[] = _("Port");
    if (empty($_POST["v_ip"]))       $errors[] = _("IPv6 Address");

    if (!empty($errors[0])) {
        foreach ($errors as $i => $error) {
            $error_msg = ($i == 0) ? $error : $error_msg . ", " . $error;
        }
        $_SESSION["error_msg"] = sprintf(_('Field "%s" can not be blank.'), $error_msg);
    }

    $v_action   = quoteshellarg($_POST["v_action"]);
    $v_protocol = quoteshellarg($_POST["v_protocol"]);
    $v_port     = str_replace(" ", ",", $_POST["v_port"]);
    $v_port     = trim(preg_replace("/\,+/", ",", $v_port), ",");
    $v_port     = quoteshellarg($v_port);
    $v_ip       = quoteshellarg($_POST["v_ip"]);
    $v_comment  = quoteshellarg($_POST["v_comment"]);

    if (empty($_SESSION["error_msg"])) {
        exec(HESTIA_CMD . "v-add-firewall-rule-ipv6 $v_action $v_ip $v_port $v_protocol $v_comment",
            $output, $return_var);
        check_return_code($return_var, $output);
        unset($output);
    }

    if (empty($_SESSION["error_msg"])) {
        $_SESSION["ok_msg"] = _("IPv6 rule has been created successfully.");
        unset($v_port, $v_ip, $v_comment);
    }
}

$v_action   = $v_action   ?? "";
$v_protocol = $v_protocol ?? "";
$v_port     = $v_port     ?? "";
$v_ip       = $v_ip       ?? "";
$v_comment  = $v_comment  ?? "";

render_page($user, $TAB, "add_firewall_ipv6");

unset($_SESSION["error_msg"]);
unset($_SESSION["ok_msg"]);
