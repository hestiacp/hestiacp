<?php
// Functions for internationalization
// I18N support information here

putenv("LANGUAGE=" . detect_user_language());
setlocale(LC_ALL, "C.UTF-8");

$domain = "hestiacp";
$localedir = "/usr/local/hestia/web/locale";
bindtextdomain($domain, $localedir);
textdomain($domain);

/**
 * Detects user language from Accept-Language HTTP header.
 * @param string Fallback language (default: 'en')
 * @return string Language code (such as 'en' and 'ja')
 */
function detect_user_language() {
	if (!empty($_SESSION["language"])) {
		return $_SESSION["language"];
	} elseif (!empty($_SESSION["LANGUAGE"])) {
		return $_SESSION["LANGUAGE"];
	} else {
		return "en";
	}
}
/**
 * Translate ISO2 to "Language"
 * nl = Dutch, de = German
 * @param string iso2 code
 * @return string Language
 */
function translate_json($string) {
	$json = file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/locale/languages.json");
	$json_a = json_decode($json, true);
	return $json_a[$string][0] . " (" . $json_a[$string . "_locale"][0] . ")";
}
/**
 * Support translation strings that contains html
 */
function htmlify_trans($string, $closingTag) {
	$arguments = func_get_args();
	return preg_replace_callback(
		"/{(.*?)}/", // Ungreedy (*?)
		function ($matches) use ($arguments, $closingTag) {
			static $i = 1;
			$i++;
			return $arguments[$i] . $matches[1] . $closingTag;
		},
		$string,
	);
}

function get_email_template($file, $language) {
	if (
		file_exists(
			$_SERVER["HESTIA"] . "/data/templates/email/" . $language . "/" . $file . ".html",
		)
	) {
		return file_get_contents(
			$_SERVER["HESTIA"] . "/data/templates/email/" . $language . "/" . $file . ".html",
		);
	}
	if (file_exists($_SERVER["HESTIA"] . "/data/templates/email/" . $file . ".html")) {
		return file_get_contents($_SERVER["HESTIA"] . "/data/templates/email/" . $file . ".html");
	}
	return false;
}

function translate_email($string, $replace) {
	$array1 = $array2 = [];
	foreach ($replace as $key => $value) {
		$array1[] = "{{" . $key . "}}";
		$array2[] = $value;
	}
	return str_replace($array1, $array2, $string);
}
/**
 * Detects user language .
 * @param string Fallback language (default: 'en')
 * @return string Language code (such as 'en' and 'ja')
 */

function detect_login_language() {
}
