<?php
/**
 * !Warning!
 * this file must be included, not required.
 */
if (!isset($dist_config)) {
	require_once "index.php";
	exit();
}
/**
 * get_language_from_system()
 *
 * isolate the hestiacp commands from this file
 *
 * @return string the language code
 */
function get_language_from_system() {
	include $_SERVER["DOCUMENT_ROOT"] . "/inc/main.php";
	/**
	 * get All supported language
	 */
	exec(HESTIA_CMD . "v-list-sys-languages json", $output, $return_var);
	$languages = json_decode(implode("", $output), true);

	/**
	 * - check if the session language exists
	 * - check if language is supported by hestia,
	 * - return default if both are false
	 */
	return isset($_SESSION["language"]) && in_array($_SESSION["language"], $languages)
		? $_SESSION["language"]
		: "en";
}

/**
 * Language Switch for filegator
 *
 * @see https://docs.filegator.io/translations/default.html#rtl-support
 */
switch (get_language_from_system()) {
	case "es":
		$dist_conf["language"] = "spanish";
		break;
	case "de":
		$dist_conf["language"] = "german";
		break;
	case "id":
		$dist_conf["language"] = "indonesian";
		break;
	case "tr":
		$dist_conf["language"] = "turkish";
		break;
	case "lt":
		$dist_conf["language"] = "lithuanian";
		break;
	case "pt":
	case "pt-pt":
		$dist_conf["language"] = "portuguese";
		break;
	case "nl":
		$dist_conf["language"] = "dutch";
		break;
	case "zh":
	case "zh-cn":
	case "zh-tw":
		$dist_conf["language"] = "chinese";
		break;
	case "bg":
		$dist_conf["language"] = "bulgarian";
		break;
	case "sr":
		$dist_conf["language"] = "serbian";
		break;
	case "fr":
		$dist_conf["language"] = "french";
		break;
	case "sk":
		$dist_conf["language"] = "slovak";
		break;
	case "pl":
		$dist_conf["language"] = "polish";
		break;
	case "it":
		$dist_conf["language"] = "italian";
		break;
	case "ko":
		$dist_conf["language"] = "korean";
		break;
	case "cs":
		$dist_conf["language"] = "czech";
		break;
	case "gl":
		$dist_conf["language"] = "galician";
		break;
	case "ru":
		$dist_conf["language"] = "russian";
		break;
	case "hu":
		$dist_conf["language"] = "hungarian";
		break;
	case "sv":
		$dist_conf["language"] = "swedish";
		break;
	case "ja":
		$dist_conf["language"] = "japanese";
		break;
	case "sl":
		$dist_conf["language"] = "slovenian";
		break;
	case "he":
		$dist_conf["language"] = "hebrew";
		break;
	case "ro":
		$dist_conf["language"] = "romanian";
		break;
	case "ar":
		$dist_conf["language"] = "arabic";
		break;
	case "pt-br":
		$dist_conf["language"] = "portuguese_br";
		break;
	case "fa":
		$dist_conf["language"] = "persian";
		break;
	case "et":
		$dist_conf["language"] = "estonian";
		break;
	default:
		$dist_conf["language"] = "english";
		break;
}
