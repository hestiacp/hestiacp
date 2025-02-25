<?php
use function Hestiacp\quoteshellarg\quoteshellarg;
$dist_config = require __DIR__ . "/configuration_sample.php";
session_start();
$dist_config["public_path"] = "/fm/";
$dist_config["frontend_config"]["app_name"] = "File Manager - Hestia Control Panel";
$dist_config["frontend_config"]["logo"] = "../images/logo.svg";
$dist_config["frontend_config"]["editable"] = [
	".txt",
	".css",
	".js",
	".ts",
	".html",
	".php",
	".py",
	".yml",
	".xml",
	".md",
	".log",
	".csv",
	".conf",
	".config",
	".ini",
	".scss",
	".sh",
	".env",
	".example",
	".htaccess",
	".twig",
	".tpl",
	".yaml",
];
$dist_config["frontend_config"]["date_format"] = "YY/MM/DD H:mm:ss";
$dist_config["frontend_config"]["guest_redirection"] = "/login/";
$dist_config["frontend_config"]["upload_max_size"] = 1024 * 1024 * 1024;
$dist_config["frontend_config"]["pagination"] = [100, 50, 25];
if (!empty($_SESSION["language"])) {
	$lang = $_SESSION["language"];
} elseif (!empty($_SESSION["LANGUAGE"])) {
	$lang = $_SESSION["LANGUAGE"];
} else {
	$lang = "en";
}
// Update list of languages when new language is added on Hestia or Filegator side
switch ($lang) {
	case "es":
		$dist_config["frontend_config"]["language"] = "spanish";
		break;
	case "de":
		$dist_config["frontend_config"]["language"] = "german";
		break;
	case "id":
		$dist_config["frontend_config"]["language"] = "indonesian";
		break;
	case "tr":
		$dist_config["frontend_config"]["language"] = "turkish";
		break;
	case "lt":
		$dist_config["frontend_config"]["language"] = "lithuanian";
		break;
	case "pt":
	case "pt-pt":
		$dist_config["frontend_config"]["language"] = "portuguese";
		break;
	case "nl":
		$dist_config["frontend_config"]["language"] = "dutch";
		break;
	case "zh":
	case "zh-cn":
	case "zh-tw":
		$dist_config["frontend_config"]["language"] = "chinese";
		break;
	case "bg":
		$dist_config["frontend_config"]["language"] = "bulgarian";
		break;
	case "sr":
		$dist_config["frontend_config"]["language"] = "serbian";
		break;
	case "fr":
		$dist_config["frontend_config"]["language"] = "french";
		break;
	case "sk":
		$dist_config["frontend_config"]["language"] = "slovak";
		break;
	case "pl":
		$dist_config["frontend_config"]["language"] = "polish";
		break;
	case "it":
		$dist_config["frontend_config"]["language"] = "italian";
		break;
	case "ko":
		$dist_config["frontend_config"]["language"] = "korean";
		break;
	case "cs":
		$dist_config["frontend_config"]["language"] = "czech";
		break;
	case "gl":
		$dist_config["frontend_config"]["language"] = "galician";
		break;
	case "ru":
		$dist_config["frontend_config"]["language"] = "russian";
		break;
	case "hu":
		$dist_config["frontend_config"]["language"] = "hungarian";
		break;
	case "sv":
		$dist_config["frontend_config"]["language"] = "swedish";
		break;
	case "ja":
		$dist_config["frontend_config"]["language"] = "japanese";
		break;
	case "sl":
		$dist_config["frontend_config"]["language"] = "slovenian";
		break;
	case "he":
		$dist_config["frontend_config"]["language"] = "hebrew";
		break;
	case "ro":
		$dist_config["frontend_config"]["language"] = "romanian";
		break;
	case "ar":
		$dist_config["frontend_config"]["language"] = "arabic";
		break;
	case "pt-br":
		$dist_config["frontend_config"]["language"] = "portuguese_br";
		break;
	case "fa":
		$dist_config["frontend_config"]["language"] = "persian";
		break;
	case "et":
		$dist_config["frontend_config"]["language"] = "estonian";
		break;
	case "uk":
		$dist_config["frontend_config"]["language"] = "ukrainian";
		break;
	default:
		$dist_config["frontend_config"]["language"] = "english";
		break;
}

$dist_config["services"]["Filegator\Services\Storage\Filesystem"]["config"][
	"adapter"
] = function () {
	if (!empty($_SESSION["INACTIVE_SESSION_TIMEOUT"])) {
		if ($_SESSION["INACTIVE_SESSION_TIMEOUT"] * 60 + $_SESSION["LAST_ACTIVITY"] < time()) {
			$v_user = quoteshellarg($_SESSION["user"]);
			$v_session_id = quoteshellarg($_SESSION["token"]);
			exec(
				"/usr/local/hestia/bin/v-log-user-logout " . $v_user . " " . $v_session_id,
				$output,
				$return_var,
			);
			unset($_SESSION);
			session_unset();
			session_destroy();
			session_start();
			echo '<meta http-equiv="refresh" content="0; url=/">';
			exit();
		} else {
			$_SESSION["LAST_ACTIVITY"] = time();
		}
	} else {
		echo '<meta http-equiv="refresh" content="0; url=/">';
	}
	if (isset($_SESSION["user"])) {
		$v_user = $_SESSION["user"];
	}
	if (!empty($_SESSION["look"])) {
		if (isset($_SESSION["look"]) && $_SESSION["userContext"] === "admin") {
			$v_user = $_SESSION["look"];
		}
		if (
			isset($_SESSION["look"]) &&
			$_SESSION["look"] == "admin" &&
			$_SESSION["POLICY_SYSTEM_PROTECTED_ADMIN"] == "yes"
		) {
			header("Location: /");
		}
	}
	# Create filemanager sftp key if missing and trash it after 30 min
	if (!file_exists("/home/" . basename($v_user) . "/.ssh/hst-filemanager-key")) {
		exec(
			"sudo /usr/local/hestia/bin/v-add-user-sftp-key " .
				quoteshellarg(basename($v_user)) .
				" 30",
			$output,
			$return_var,
		);
		// filemanager also requires .ssh chmod o+x ... hopefully we can improve it to g+x or u+x someday
		// current minimum for filemanager: chmod 0701 .ssh
		shell_exec("sudo chmod o+x " . quoteshellarg("/home/" . basename($v_user) . "/.ssh"));
	}

	if (!isset($_SESSION["SFTP_PORT"])) {
		exec("sudo /usr/local/hestia/bin/v-list-sys-sshd-port json", $output, $result);
		$port = json_decode(implode("", $output));
		if (is_numeric($port[0]) && $port[0] > 0) {
			$_SESSION["SFTP_PORT"] = $port[0];
		} elseif (
			preg_match('/^\s*Port\s+(\d+)$/im', file_get_contents("/etc/ssh/sshd_config"), $matches)
		) {
			$_SESSION["SFTP_PORT"] = $matches[1] ?? 22;
		} else {
			$_SESSION["SFTP_PORT"] = 22;
		}
	}

	$root = "/home/" . $v_user;

	return new \League\Flysystem\Sftp\SftpAdapter([
		"host" => "127.0.0.1",
		"port" => intval($_SESSION["SFTP_PORT"]),
		"username" => basename($v_user),
		"privateKey" => "/home/" . basename($v_user) . "/.ssh/hst-filemanager-key",
		"root" => $root,
		"timeout" => 10,
		"directoryPerm" => 0755,
	]);
};

$dist_config["services"]["Filegator\Services\Archiver\ArchiverInterface"] = [
	"handler" => "\Filegator\Services\Archiver\Adapters\HestiaZipArchiver",
	"config" => [],
];

$dist_config["services"]["Filegator\Services\Auth\AuthInterface"] = [
	"handler" => "\Filegator\Services\Auth\Adapters\HestiaAuth",
	"config" => [
		"permissions" => ["read", "write", "upload", "download", "batchdownload", "zip", "chmod"],
		"private_repos" => false,
	],
];

$dist_config["services"]["Filegator\Services\View\ViewInterface"]["config"] = [
	"add_to_head" => '
	<link rel="stylesheet" href="/fm/css/hst-custom.css">
    <style>
        .logo {
            width: 46px;
        }
    </style>
    ',
	"add_to_body" => '
<script>
    var checkVueLoaded = setInterval(function() {
        if (document.getElementsByClassName("container").length) {
            clearInterval(checkVueLoaded);
            var navProfile = document.getElementsByClassName("navbar-item profile")[0]; navProfile.replaceWith(navProfile.cloneNode(true))
            document.getElementsByClassName("navbar-item logout")[0].text="Exit to Control Panel \u00BB";
            div = document.getElementsByClassName("container")[0];
            callback = function(){
                if (document.getElementsByClassName("navbar-item logout")[0]){
                    if ( document.getElementsByClassName("navbar-item logout")[0].text != "Exit to Control Panel \u00BB" ){
                        var navProfile = document.getElementsByClassName("navbar-item profile")[0]; navProfile.replaceWith(navProfile.cloneNode(true))
                        document.getElementsByClassName("navbar-item logout")[0].text="Exit to Control Panel \u00BB";
                    }
                }
            }
            config = {
                childList:true,
                subtree:true
            }
            observer = new MutationObserver(callback);
            observer.observe(div,config);
        }
    }, 200);
</script>',
];

return $dist_config;
