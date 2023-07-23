<?php
declare (strict_types = 1);
namespace Hestiacp\quoteshellarg;

/**
 * quotes shell arguments
 * (doing a better job than escapeshellarg)
 *
 * @param string $arg
 * @throws UnexpectedValueException if $arg contains null bytes
 * @return string
 */

function quoteshellarg(string $arg): string
{
    static $isUnix = null;
    if ($isUnix === null) {
        $isUnix = in_array(PHP_OS_FAMILY, array('Linux', 'BSD', 'Darwin', 'Solaris'), true);
    }
    if ($isUnix) {
        // PHP's built-in escapeshellarg() for unix is kindof garbage: https://3v4l.org/Hkv7h
        // corrupting-or-stripping UTF-8 unicode characters like "æøå" and non-printable characters like "\x01",
        // both of which are fully legal in unix shell arguments.
        // In single-quoted-unix-shell-arguments there are only 2 bytes that needs special attention: \x00 and \x27
        if (false !== strpos($arg, "\x00")) {
            throw new UnexpectedValueException('unix shell arguments cannot contain null bytes!');
        }
        return "'" . strtr($arg, array("'" => "'\\''")) . "'";
    }
    // todo: quoteshellarg for windows? it's a nightmare though: https://docs.microsoft.com/en-us/archive/blogs/twistylittlepassagesallalike/everyone-quotes-command-line-arguments-the-wrong-way
    // fallback to php's builtin escapeshellarg
    return escapeshellarg($arg);
}
