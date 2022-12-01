<?php
header("Content-Type: text/javascript");
session_start();

require_once $_SERVER["DOCUMENT_ROOT"] . "/inc/i18n.php";
?>

App.Constants.UNLIM_TRANSLATED_VALUE = '<?= _("unlimited") ?>';
App.Constants.NOTIFICATIONS_EMPTY    = '<?= _("no notifications") ?>';
App.Constants.NOTIFICATIONS_MARK_ALL = '<?= _("Mark all seen") ?>';
