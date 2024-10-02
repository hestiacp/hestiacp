<?php

declare(strict_types=1);
<<<'EXPLANATION'
We need to process all /home/*/conf/web/*/apache2.conf
replacing sections
    <FilesMatch \.php$>
        SetHandler "proxy:unix:/run/php/php8.2-fpm-idn-tést.eu.sock|fcgi://localhost"
    </FilesMatch>"
with
ProxyPassMatch ^/(.*\.php)(?:$|\?) "unix:/run/php/php8.2-fpm-idn-tést.eu.sock|fcgi://localhost/home/testhoster1/web/idn-tést.eu/public_html/$1" nocanon
EXPLANATION;
error_reporting(E_ALL);
ini_set("display_errors", "1");
set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
	if (error_reporting() & $errno) {
		throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
	}
});
function dd(...$args) {
	$trace = debug_backtrace();
	echo "dd() called in {$trace[0]["file"]} on line {$trace[0]["line"]}\n";
	var_export($args);
	var_dump(...$args);
	die();
}
function differ(string $old, string $new) {
	$oldFile = tmpfile();
	$newFile = tmpfile();
	fwrite($oldFile, $old);
	fwrite($newFile, $new);
	$oldPath = stream_get_meta_data($oldFile)["uri"];
	$newPath = stream_get_meta_data($newFile)["uri"];
	$diff = shell_exec(
		"git diff --no-index --color=always " .
			escapeshellarg($oldPath) .
			" " .
			escapeshellarg($newPath),
	);
	fclose($oldFile);
	fclose($newFile);
	return $diff;
}
// check if root
if (posix_getuid() !== 0) {
	echo "You need to run this script as root\n";
	exit(1);
}
// check if cli
if (PHP_SAPI !== "cli") {
	echo "You need to run this script from cli\n";
	exit(1);
}
$interactive = array_search("--non-interactive", $argv) === false;
echo "Interactive mode: " . ($interactive ? "yes" : "no") . "\n";
$conf_files = glob("/home/*/conf/web/*/apache2.conf", GLOB_NOSORT | GLOB_NOESCAPE | GLOB_MARK);
$conf_files = array_filter($conf_files, function (string $file): bool {
	$last_chr = substr($file, -1);
	if ($last_chr === "/" || $last_chr === "\\") {
		return false;
	}
	if (!is_file($file)) {
		// should never happen.
		throw new \LogicException(
			"Glob say this file exist, but php is_file does not, WTF? " . var_export($file, true),
		);
	}
	return true;
});
$conf_files = array_values($conf_files);
var_dump($conf_files);
foreach ($conf_files as $conf_file) {
	echo "processing '$conf_file'\n";
	$original_conf_string = file_get_contents($conf_file);
	$modified_conf_string = $original_conf_string;
	<<<'SAMPLE'
	    <FilesMatch \.php$>
	        SetHandler "proxy:unix:/run/php/php8.2-fpm-idn-tést.eu.sock|fcgi://localhost"
	    </FilesMatch>
	SAMPLE;
	$ret = preg_match_all(
		'/(?:$|\n)(?<PhpHandlerSection>\s*\\<FilesMatch\s+\\\\\\.php\\$\\>[\s\S]+?\\<\\/FilesMatch\\>)/',
		$original_conf_string,
		$matches,
		PREG_OFFSET_CAPTURE,
	);
	if (empty($matches["PhpHandlerSection"]) || count($matches["PhpHandlerSection"]) < 1) {
		echo "No php handler section found in $conf_file\n";
		echo "skipping\n";
		continue;
	}
	// DocumentRoot /home/testhoster1/web/idn-tést.eu/public_html
	$documentRootMatchRet = preg_match(
		"/^\s*DocumentRoot\s+([^\s]*)/m",
		$original_conf_string,
		$documentRootMatch,
	);
	if (
		$documentRootMatchRet === false ||
		$documentRootMatchRet === 0 ||
		empty($documentRootMatch[1])
	) {
		throw new \LogicException("Failed to extract DocumentRoot from '$conf_file' - investigate");
	}
	$documentRoot = $documentRootMatch[1]; // string(46) "/home/testhoster1/web/idn-tést.eu/public_html"
	$php_handler_sections = $matches["PhpHandlerSection"];
	$php_handler_sections = array_reverse($php_handler_sections, false); // we need to process them in reverse to not break the preg_match offsets
	foreach ($php_handler_sections as $php_handler_section) {
		$php_handler_section_sample = [
			0 => '
            <FilesMatch \\.php$>
                SetHandler "proxy:unix:/run/php/php8.2-fpm-idn-tést.eu.sock|fcgi://localhost"
            </FilesMatch>',
			1 => 1355,
		];
		preg_match('/proxy\:([^\\"\\\']+)/', $php_handler_section[0], $proxyMatch);
		if (empty($proxyMatch[1])) {
			throw new \LogicException(
				"Failed to extract proxy:unix:... from php handler section in '$conf_file' - investigate",
			);
		}
		$proxy = $proxyMatch[1]; // string(59) "unix:/run/php/php8.2-fpm-idn-tést.eu.sock|fcgi://localhost"
		$proxy = strtr($proxy, [
			"fcgi://localhost" => "fcgi://localhost{$documentRoot}/$1",
			"fcgi://127.0.0.1" => "fcgi://localhost{$documentRoot}/$1",
		]); // string(108) "unix:/run/php/php8.2-fpm-idn-tést.eu.sock|fcgi://localhost/home/testhoster1/web/idn-tést.eu/public_html/$1"
		$leading_spaces = strlen($php_handler_section[0]) - strlen(ltrim($php_handler_section[0]));
		$leading_spaces = substr($php_handler_section[0], 0, $leading_spaces);
		$proxyString =
			"\n{$leading_spaces}" .
			'ProxyPassMatch ^/(.*\\.php)(?:$|\\?) "' .
			$proxy .
			'" nocanon' .
			"\n";
		$modified_conf_string = substr_replace(
			$modified_conf_string,
			$proxyString,
			$php_handler_section[1],
			strlen($php_handler_section[0]),
		);
	}
	assert($original_conf_string !== $modified_conf_string);
	$diff = differ($original_conf_string, $modified_conf_string);
	echo $diff;
	if ($interactive) {
		echo "Do you want to apply this diff? [y/N] ";
		$answer = trim(fgets(STDIN));
	} else {
		$answer = "y";
	}
	if (strtolower($answer) === "y") {
		file_put_contents($conf_file, $modified_conf_string, LOCK_EX);
	}
}
passthru("/usr/sbin/service apache2 reload", $exitCode);
if ($exitCode !== 0) {
	echo "Failed to reload apache2: exit code $exitCode\n";
	exit(1);
}
