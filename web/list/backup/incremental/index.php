<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$TAB = "BACKUP";

// Main include
include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";

function getTransByType($type) {
	switch ($type) {
		case "dir":
			echo _("Directory");
			break;
		case "file":
			echo _("File");
			break;
		case "symlink":
			echo _("Symlink");
			break;
		default:
			echo _("Unknown");
	}
}

// Data & Render page
if (empty($_GET["snapshot"])) {
	exec(HESTIA_CMD . "v-list-user-backups-restic $user json", $output, $return_var);
	$data = json_decode(implode("", $output), true);
	if (is_array($data)) {
		$data = array_reverse($data);
	} else {
		$data = "";
	}

	render_page($user, $TAB, "list_backup_incremental");
} else {
	if (empty($_GET["browse"])) {
		$snapshot = quoteshellarg($_GET["snapshot"]);
		exec(HESTIA_CMD . "v-list-user-backup-restic $user $snapshot json", $output, $return_var);
		$data = json_decode(implode("", $output), true);
		render_page($user, $TAB, "list_backup_detail_incremental");
	} else {
		if (empty($_GET["folder"])) {
			$_GET["folder"] = "/home/" . $user_plain;
		}
		$folder = quoteshellarg($_GET["folder"]);
		$snapshot = quoteshellarg($_GET["snapshot"]);
		exec(HESTIA_CMD . "v-list-user-files-restic $user $snapshot $folder", $output, $return_var);
		$info = json_decode($output[0], true);
		unset($output[0]);
		$files = [];
		foreach ($output as $value) {
			$files[] = json_decode($value, true);
		}
		render_page($user, $TAB, "list_files_incremental");
	}
}
