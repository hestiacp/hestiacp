<?php

ob_start();
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Check token
verify_csrf($_GET);

$snapshot = escapeshellarg($_GET["snapshot"]);

if (empty($_GET["object"])) {
    $_GET["object"] = "";
}

if (empty($_GET["type"])) {
    $cmd = HESTIA_CMD . "v-schedule-user-restore-restic " . $user . " " . $snapshot;
    exec($cmd, $output, $return_var);
    if ($return_var == 0) {
        $_SESSION["error_msg"] = _(
            "Task has been added to the queue. You will receive an email " .
                "notification when your restore has been completed.",
        );
    } else {
        $_SESSION["error_msg"] = implode("<br>", $output);
        if (empty($_SESSION["error_msg"])) {
            $_SESSION["error_msg"] = _("Error: Hestia did not return any output.");
        }
        if ($return_var == 4) {
            $_SESSION["error_msg"] = _(
                "An existing restoration task is already running. Please wait for it " .
                    "to finish before launching it again.",
            );
        }
    }
} else {
    $cmd =
        HESTIA_CMD .
        "v-schedule-user-restore-restic " .
        $user .
        " " .
        $snapshot .
        " " .
        escapeshellarg($_GET["type"]) .
        " " .
        escapeshellarg($_GET["object"]);
    exec($cmd, $output, $return_var);
}

header("Location: /list/backup/incremental/?snapshot=" . $_GET["snapshot"]);
