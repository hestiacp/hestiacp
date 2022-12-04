<?php
header("Content-Type: text/javascript");
session_start();

require_once $_SERVER["DOCUMENT_ROOT"] . "/inc/i18n.php";
?>

App.setConstant('UNLIM_TRANSLATED_VALUE', '<?= _("unlimited") ?>');
App.setConstant('NOTIFICATIONS_EMPTY', '<?= _("no notifications") ?>');
