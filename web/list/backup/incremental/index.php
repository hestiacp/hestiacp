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
		$panel[$user_plain]["U_BACKUPS_INCREMENTAL"] = count($data);
		if ($_SESSION["userSortOrder"] == "name") {
			usort($data, function ($a, $b) {
				return strcmp($a["short_id"] ?? "", $b["short_id"] ?? "");
			});
		} else {
			$data = array_reverse($data);
		}
		if (!empty($_GET["q"])) {
			$q = $_GET["q"];
			$data = array_values(
				array_filter($data, function ($value) use ($q) {
					$haystack = implode(" ", [
						$value["short_id"] ?? "",
						$value["time"] ?? "",
						convert_datetime($value["time"] ?? ""),
						$value["hostname"] ?? "",
						"Restic",
					]);
					return stripos($haystack, $q) !== false;
				}),
			);
		}
	} else {
		$data = [];
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
		$q = $_GET["q"] ?? "";
		foreach ($output as $key => $value) {
			$item = json_decode($value, true);
			if ($q !== "" && $key !== 1 && ($item["path"] ?? "") !== $_GET["folder"]) {
				$haystack = implode(" ", [
					$item["name"] ?? "",
					$item["type"] ?? "",
					$item["ctime"] ?? "",
				]);
				if (stripos($haystack, $q) === false) {
					continue;
				}
			}
			$files[] = $item;
		}
		render_page($user, $TAB, "list_files_incremental");
	}
}
