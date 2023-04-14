<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$TAB = "BACKUP";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

// Data & Render page
if (empty($_GET["snapshot"])) {
	exec(HESTIA_CMD . "v-list-user-backups-restic $user json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	$data = array_reverse($data);

	render_page($user, $TAB, "list_backup_incremental");
} else {
	if (empty($_GET["browse"])) {
		$snapshot = quoteshellarg($_GET["snapshot"]);
		exec(HESTIA_CMD . "v-list-user-backup-restic $user $snapshot json", $output, $return_var);
		$data = json_decode(implode("", $output), true);
		render_page($user, $TAB, "list_backup_detail_incremental");
	} else {
		echo "test";
	}
}
