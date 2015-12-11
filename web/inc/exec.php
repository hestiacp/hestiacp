<?php
// Secure `exec` wrapper functions

define('SUDO_CMD', '/usr/bin/sudo');
define('VESTA_BIN_DIR', '/usr/local/vesta/bin/');

define('VESTA_CMD', SUDO_CMD.' '.VESTA_BIN_DIR);


function check_error($return_var) {
    if ($return_var > 0) {
        header('Location: /error/');
        exit;
    }
}

function check_return_code($return_var, $output) {
    if ($return_var != 0) {
        $error = implode('<br>', $output);
        if (empty($error)) $error = __('Error code:', $return_var);
        $_SESSION['error_msg'] = $error;
    }
}

/**
 * Build shell command arguments from a string array.
 * @param string[] $arguments Unescaped command line arguments. (eg. ['-a', "b'c"], default: [])
 * @return string Escaped arguments.
 */
function build_shell_args($arguments=[]) {
    $ret = [];
    // Convert $arguments to an array
    if (!is_array($arguments)) $arguments = !is_null($arguments) ? [$arguments] : [];
    foreach ($arguments as $arg) {
        // Convert $arg to a string if $arg is an array (for an argument like this: ?abc[def]=ghi)
        if (is_array($arg)) $arg = implode('', $arg);
        // Convert $arg to a string (just in case)
        if (!is_string($arg)) $arg = (string)$arg;
        // Append the argument
        $ret[] = escapeshellarg($arg);
    }
    return implode(' ', $ret);
}

/**
 * Execute a command.
 * @param string   $command   Command to execute. (eg. ls)
 * @param string[] $arguments (optional) Unescaped command line arguments. (eg. ['-a', '/'], default: [])
 * @param string   &$output   (optional) Variable to contain output from the command.
 * @return int Exit code (return status) of the executed command.
 */
function safe_exec($command, $arguments=[], &$output=null) {
    $cmd = build_shell_args($command);
    $arg = build_shell_args($arguments);
    if (!empty($arg)) {
        $cmd .= ' ' . $arg;
    }
    // Execute
    exec($cmd, $rawOutput, $status);
    $output = implode("\n", $rawOutput);
    return $status;
}

/**
 * Execute a vesta command line APIs (VESTA_CMD/v-*).
 * (Wrapper function of `safe_exec`.)
 * @see safe_exec
 * @param string   $command     Command to execute. (eg. v-search-object)
 * @param string[] $arguments   (optional) Unescaped command line arguments. (eg. ["We've", 'json'], default: [])
 * @param bool     $checkReturn (optional) If this set to true, check_return_code will be called after the command executes. (default: true)
 * @param string   &$output     (optional) Variable to contain output from the command.
 * @return int Exit code (return status) of the executed command.
 */
function v_exec($command, $arguments=[], $checkReturn=true, &$output=null) {
    // Check command
    if (preg_match('#^\.*$|/#', $command)) return -1;
    // Convert $arguments to an array
    if (!is_array($arguments)) $arguments = !is_null($arguments) ? [$arguments] : [];
    // Execute
    $status = safe_exec([SUDO_CMD, VESTA_BIN_DIR.$command], $arguments, $output);
    if ($checkReturn) {
        check_return_code($status, explode("\n", $output));
    }
    return $status;
}
