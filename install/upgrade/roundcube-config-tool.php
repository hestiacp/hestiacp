#!/usr/bin/env php
<?php

/*
 * Roundcube configuration helper for the Hestia 1.10.7 apt-to-upstream migration.
 *
 * Parsing and merging the old configuration in PHP avoids fragile bash string
 * handling. The tool only reads and writes files; all destructive operations
 * (database, packages, moving directories) stay in migrate_roundcube.sh.
 */

declare(strict_types=1);

const EXCLUDED_KEYS = [
    'db_dsn',
    'db_dsnr',
    'db_dsnw',
    'db_prefix',
    'des_key',
    'plugins',
];

const MANAGED_CONFIG_PLUGINS = [
    'newmail_notifier',
    'password',
    'zipdownload',
];

// Plugins bundled with upstream Roundcube releases. Old distro installs may
// list additional plugins that were shipped as separate packages; those are
// copied but not enabled automatically unless they are in this list.
const KNOWN_COMPATIBLE_PLUGINS = [
    'archive',
    'attachment_reminder',
    'autologon',
    'emoticons',
    'filesystem_attachments',
    'hide_blockquote',
    'identity_select',
    'jqueryui',
    'managesieve',
    'markasjunk',
    'newmail_notifier',
    'password',
    'redundant_attachments',
    'show_additional_headers',
    'squirrelmail_usercopy',
    'userinfo',
    'vcard_attachments',
    'zipdownload',
];

const EXAMPLE_DES_KEY = 'rcmail-!24ByteDESkey*Str';

function stderr(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
}

function warning(string $message): void
{
    stderr('Warning: ' . $message);
}

function fail(string $message): void
{
    stderr('ERROR: ' . $message);
    exit(1);
}

function usage(): void
{
    stderr(
        "Usage:\n" .
            "  roundcube-config-tool.php db-config <config-file>\n" .
            "  roundcube-config-tool.php merge-config --old <file> --new <file> --install-dir <dir> --config-dir <dir>\n" .
            "  roundcube-config-tool.php migrate-assets --old-config-dir <dir> --new-config-dir <dir> --old-install-dir <dir> --new-install-dir <dir>"
    );
    exit(2);
}

function read_file(string $path): string
{
    if (!is_file($path) || !is_readable($path)) {
        fail("Cannot read $path");
    }

    $contents = file_get_contents($path);
    if ($contents === false) {
        fail("Cannot read $path");
    }

    return $contents;
}

function shell_quote(string $value): string
{
    return "'" . str_replace("'", "'\\''", $value) . "'";
}

function token_text($token): string
{
    return is_array($token) ? $token[1] : $token;
}

/**
 * Find top-level conditional statements whose body is not wrapped in braces.
 *
 * The main extractor skips braced bodies through brace-depth tracking. PHP also
 * allows a single statement without braces (and the alternative "if (...):"
 * syntax), and an assignment inside such a body must not be treated as an
 * unconditional custom setting.
 *
 * @return list<array{0: int, 1: int}> byte ranges that must not be copied
 */
function conditional_ranges(string $source, array $tokens, array $offsets): array
{
    $count = count($tokens);
    $ranges = [];
    $depth = 0;

    $is_ignorable = static fn ($token): bool => is_array($token)
        && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);

    for ($index = 0; $index < $count; $index++) {
        $text = token_text($tokens[$index]);

        if ($text === '{') {
            $depth++;
            continue;
        }
        if ($text === '}') {
            if ($depth > 0) {
                $depth--;
            }
            continue;
        }

        if (
            $depth !== 0
            || !is_array($tokens[$index])
            || !in_array(
                $tokens[$index][0],
                [T_IF, T_ELSEIF, T_ELSE, T_FOR, T_FOREACH, T_WHILE, T_SWITCH, T_TRY, T_DECLARE],
                true
            )
        ) {
            continue;
        }

        $start = $offsets[$index];
        $cursor = $index + 1;
        $parentheses = 0;
        $brackets = 0;
        $terminator = null;

        while ($cursor < $count) {
            if ($is_ignorable($tokens[$cursor])) {
                $cursor++;
                continue;
            }

            $token_text = token_text($tokens[$cursor]);

            if ($token_text === '(') {
                $parentheses++;
            } elseif ($token_text === ')') {
                if ($parentheses > 0) {
                    $parentheses--;
                }
            } elseif ($token_text === '[') {
                $brackets++;
            } elseif ($token_text === ']') {
                if ($brackets > 0) {
                    $brackets--;
                }
            }

            if ($parentheses === 0 && $brackets === 0) {
                if ($token_text === '{') {
                    $terminator = 'braced';
                    break;
                }
                if ($token_text === ':') {
                    $terminator = 'alternative';
                    break;
                }
                if ($token_text === ';') {
                    $terminator = 'single';
                    break;
                }
            }

            $cursor++;
        }

        if ($terminator === 'braced') {
            // Brace-depth handling in extract_config_statements covers this.
            continue;
        }

        if ($terminator === null) {
            $ranges[] = [$start, strlen($source)];
            continue;
        }

        if ($terminator === 'alternative') {
            $end_tokens = [
                T_IF => T_ENDIF,
                T_ELSEIF => T_ENDIF,
                T_ELSE => T_ENDIF,
                T_FOR => T_ENDFOR,
                T_FOREACH => T_ENDFOREACH,
                T_WHILE => T_ENDWHILE,
                T_SWITCH => T_ENDSWITCH,
                T_DECLARE => T_ENDDECLARE,
            ];
            $opening = $tokens[$index][0];
            $closing = $end_tokens[$opening] ?? null;
            $end_offset = strlen($source);

            if ($closing !== null) {
                $nesting = 1;
                for ($scan = $cursor + 1; $scan < $count; $scan++) {
                    if (!is_array($tokens[$scan])) {
                        continue;
                    }
                    if ($tokens[$scan][0] === $opening && $opening !== T_ELSE && $opening !== T_ELSEIF) {
                        $nesting++;
                    } elseif ($tokens[$scan][0] === $closing) {
                        $nesting--;
                        if ($nesting === 0) {
                            $end_offset = $offsets[$scan] + strlen(token_text($tokens[$scan]));
                            break;
                        }
                    }
                }
            }

            $ranges[] = [$start, $end_offset];
            continue;
        }

        $ranges[] = [$start, $offsets[$cursor] + 1];
    }

    return $ranges;
}

function unquote_php_string(string $value): string
{
    $length = strlen($value);
    if ($length < 2) {
        return $value;
    }

    $quote = $value[0];
    $body = substr($value, 1, -1);

    if ($quote === "'") {
        return str_replace(["\\\\", "\\'"], ["\\", "'"], $body);
    }

    if ($quote === '"') {
        return stripcslashes($body);
    }

    return $body;
}

/**
 * Extract top-level "$config['key'] = ...;" statements from a Roundcube config.
 *
 * Statements nested in if/foreach blocks are ignored: they are conditional in
 * the old installation and cannot be copied safely without their conditions.
 *
 * @return list<array{key: string, text: string, value: mixed}>
 */
function extract_config_statements(string $source): array
{
    $tokens = token_get_all($source);
    $count = count($tokens);
    $offsets = [];
    $offset = 0;

    foreach ($tokens as $index => $token) {
        $offsets[$index] = $offset;
        $offset += strlen(token_text($token));
    }

    $is_ignorable = static fn ($token): bool => is_array($token)
        && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);

    $conditional_ranges = conditional_ranges($source, $tokens, $offsets);
    $statements = [];
    $depth = 0;
    $index = 0;

    while ($index < $count) {
        $text = token_text($tokens[$index]);

        if ($text === '{') {
            $depth++;
            $index++;
            continue;
        }

        if ($text === '}') {
            if ($depth > 0) {
                $depth--;
            }
            $index++;
            continue;
        }

        if (
            $depth !== 0
            || !is_array($tokens[$index])
            || $tokens[$index][0] !== T_VARIABLE
            || $tokens[$index][1] !== '$config'
        ) {
            $index++;
            continue;
        }

        $start = $offsets[$index];
        $cursor = $index + 1;

        while ($cursor < $count && $is_ignorable($tokens[$cursor])) {
            $cursor++;
        }

        if ($cursor >= $count || token_text($tokens[$cursor]) !== '[') {
            $index++;
            continue;
        }
        $cursor++;

        while ($cursor < $count && $is_ignorable($tokens[$cursor])) {
            $cursor++;
        }

        if (
            $cursor >= $count
            || !is_array($tokens[$cursor])
            || $tokens[$cursor][0] !== T_CONSTANT_ENCAPSED_STRING
        ) {
            $index++;
            continue;
        }

        $key = unquote_php_string($tokens[$cursor][1]);
        $cursor++;

        // Allow nested keys such as $config['imap_conn_options']['ssl']['verify_peer'].
        // The first key is used for the managed-key denylist; the complete
        // statement is copied verbatim.
        $bracket_depth = 1;
        $found_equals = false;
        while ($cursor < $count) {
            if ($is_ignorable($tokens[$cursor])) {
                $cursor++;
                continue;
            }
            $token_text = token_text($tokens[$cursor]);
            if ($token_text === '[') {
                $bracket_depth++;
            } elseif ($token_text === ']') {
                $bracket_depth--;
            } elseif ($token_text === '=' && $bracket_depth === 0) {
                $found_equals = true;
                break;
            } elseif ($token_text === ';' && $bracket_depth === 0) {
                break;
            }
            $cursor++;
        }

        if (!$found_equals) {
            $index++;
            continue;
        }

        $cursor++;
        $first_value_token = null;
        $parentheses = 0;
        $brackets = 0;
        $braces = 0;
        $end = null;

        while ($cursor < $count) {
            $token = $tokens[$cursor];
            $token_text = token_text($token);

            if ($first_value_token === null && !$is_ignorable($token)) {
                $first_value_token = $token;
            }

            if ($token_text === '(') {
                $parentheses++;
            } elseif ($token_text === ')') {
                if ($parentheses > 0) {
                    $parentheses--;
                }
            } elseif ($token_text === '[') {
                $brackets++;
            } elseif ($token_text === ']') {
                if ($brackets > 0) {
                    $brackets--;
                }
            } elseif ($token_text === '{') {
                $braces++;
            } elseif ($token_text === '}') {
                if ($braces > 0) {
                    $braces--;
                }
            } elseif (
                $token_text === ';'
                && $parentheses === 0
                && $brackets === 0
                && $braces === 0
            ) {
                $end = $offsets[$cursor] + 1;
                break;
            }

            $cursor++;
        }

        if ($end === null) {
            warning("Ignoring malformed config statement for '$key'");
            $index++;
            continue;
        }

        $inside_conditional = false;
        foreach ($conditional_ranges as $range) {
            if ($start >= $range[0] && $start < $range[1]) {
                $inside_conditional = true;
                break;
            }
        }

        if ($inside_conditional) {
            warning("Not copying conditional config setting '$key'");
            $index = $cursor + 1;
            continue;
        }

        $statements[] = [
            'key' => $key,
            'text' => substr($source, $start, $end - $start),
            'value' => $first_value_token,
        ];

        $index = $cursor + 1;
    }

    return $statements;
}

function statement_literal_value(array $statement): ?string
{
    $value = $statement['value'];

    if (is_array($value) && $value[0] === T_CONSTANT_ENCAPSED_STRING) {
        return unquote_php_string($value[1]);
    }

    return null;
}

/**
 * Parse $var = 'value'; assignments from a Debian dbconfig-common include.
 *
 * @return array<string, string>
 */
function parse_php_assignments(string $source): array
{
    $values = [];
    $tokens = token_get_all($source);
    $count = count($tokens);

    $is_ignorable = static fn ($token): bool => is_array($token)
        && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true);

    for ($index = 0; $index < $count; $index++) {
        if (
            !is_array($tokens[$index])
            || $tokens[$index][0] !== T_VARIABLE
            || !preg_match('/^\$([A-Za-z_][A-Za-z0-9_]*)$/', $tokens[$index][1], $name)
        ) {
            continue;
        }

        $cursor = $index + 1;
        while ($cursor < $count && $is_ignorable($tokens[$cursor])) {
            $cursor++;
        }
        if ($cursor >= $count || token_text($tokens[$cursor]) !== '=') {
            continue;
        }
        $cursor++;
        while ($cursor < $count && $is_ignorable($tokens[$cursor])) {
            $cursor++;
        }
        if (
            $cursor >= $count
            || !is_array($tokens[$cursor])
            || $tokens[$cursor][0] !== T_CONSTANT_ENCAPSED_STRING
        ) {
            continue;
        }
        $value = unquote_php_string($tokens[$cursor][1]);
        $cursor++;
        while ($cursor < $count && $is_ignorable($tokens[$cursor])) {
            $cursor++;
        }
        if ($cursor < $count && token_text($tokens[$cursor]) === ';') {
            $values[$name[1]] = $value;
        }
    }

    return $values;
}

function dsn_to_credentials(string $dsn): ?array
{
    if (!preg_match('~^(mysql|mysqli)://~i', $dsn)) {
        return null;
    }

    $parts = parse_url($dsn);
    if ($parts === false || empty($parts['scheme'])) {
        return null;
    }

    $backend = strtolower($parts['scheme']);
    if (!in_array($backend, ['mysql', 'mysqli'], true)) {
        return null;
    }

    $database = ltrim($parts['path'] ?? '', '/');
    if ($database === '') {
        return null;
    }

    return [
        'backend' => 'mysql',
        'name' => rawurldecode($database),
        'user' => rawurldecode($parts['user'] ?? ''),
        'pass' => rawurldecode($parts['pass'] ?? ''),
        'host' => $parts['host'] ?? 'localhost',
        'port' => isset($parts['port']) ? (string) $parts['port'] : '',
    ];
}

function db_config_command(string $config_path): int
{
    $source = read_file($config_path);
    $credentials = null;

    foreach (extract_config_statements($source) as $statement) {
        if (strtolower($statement['key']) !== 'db_dsnw') {
            continue;
        }

        $value = statement_literal_value($statement);
        if ($value !== null) {
            $credentials = dsn_to_credentials($value);
        } else {
            // db_dsnw may be array('mysql://...') or an expression.
            if (preg_match('~[\'"](mysql(?:i)?://[^\'"]+)[\'"]~i', $statement['text'], $match)) {
                $credentials = dsn_to_credentials($match[1]);
            }
        }

        if ($credentials !== null) {
            break;
        }
    }

    // Debian generates /etc/roundcube/debian-db.php and includes it from the
    // Roundcube config. The values are not visible as config statements.
    if ($credentials === null) {
        $dbconfig_file = dirname($config_path) . '/debian-db.php';
        if (is_file($dbconfig_file)) {
            $values = parse_php_assignments(read_file($dbconfig_file));
            $backend = strtolower($values['dbtype'] ?? '');

            if ($backend === 'mysql' || $backend === 'mysqli') {
                $credentials = [
                    'backend' => 'mysql',
                    'name' => $values['dbname'] ?? '',
                    'user' => $values['dbuser'] ?? '',
                    'pass' => $values['dbpass'] ?? '',
                    'host' => $values['dbserver'] ?? 'localhost',
                    'port' => $values['dbport'] ?? '',
                ];
            }
        }
    }

    if ($credentials === null) {
        fail('Unable to determine a MySQL/MariaDB database configuration');
    }

    if ($credentials['name'] === '') {
        fail('Unable to determine the Roundcube database name');
    }

    if (
        $credentials['host'] === ''
        || preg_match('~^unix\(~i', $credentials['host'])
    ) {
        $credentials['host'] = 'localhost';
    }

    $db_prefix = '';
    $db_prefix_unknown = 0;
    foreach (extract_config_statements($source) as $statement) {
        if (strtolower($statement['key']) !== 'db_prefix') {
            continue;
        }
        $prefix = statement_literal_value($statement);
        if ($prefix !== null) {
            $db_prefix = $prefix;
        } else {
            $db_prefix_unknown = 1;
        }
    }

    foreach (['name', 'user', 'pass', 'host', 'port', 'backend'] as $field) {
        echo 'RC_DB_' . strtoupper($field) . '=' . shell_quote($credentials[$field]) . PHP_EOL;
    }
    echo "RC_DB_PREFIX='" . str_replace("'", "'\\''", $db_prefix) . "'" . PHP_EOL;
    echo 'RC_DB_PREFIX_UNKNOWN=' . $db_prefix_unknown . PHP_EOL;

    return 0;
}

function parse_options(array $arguments, array $required): array
{
    $options = [];
    $count = count($arguments);

    for ($index = 0; $index < $count; $index++) {
        $argument = $arguments[$index];
        if (strpos($argument, '--') !== 0 || $index + 1 >= $count) {
            usage();
        }
        $options[substr($argument, 2)] = $arguments[++$index];
    }

    foreach ($required as $option) {
        if (!isset($options[$option]) || $options[$option] === '') {
            fail("Missing --$option");
        }
    }

    return $options;
}

/**
 * @return list<string>
 */
function plugin_names_from_statement(string $statement): array
{
    $equals = strpos($statement, '=');
    if ($equals === false) {
        return [];
    }

    $rhs = substr($statement, $equals + 1);
    // Ignore commented-out plugin entries in the distribution config.
    $rhs = preg_replace('~/\*.*?\*/~s', '', $rhs) ?? $rhs;
    $rhs = preg_replace('~//[^\n]*~', '', $rhs) ?? $rhs;
    $rhs = preg_replace('~#[^\n]*~', '', $rhs) ?? $rhs;

    if (!preg_match_all('~[\'"]([^\'"]+)[\'"]~', $rhs, $matches)) {
        return [];
    }

    return array_values(array_unique($matches[1]));
}

function export_php_array(array $values): string
{
    $items = array_map(static fn (string $value): string => var_export($value, true), $values);

    return '[' . implode(', ', $items) . ']';
}

function merge_config_command(string $old_path, string $new_path, string $install_dir, string $config_dir): int
{
    $old_source = read_file($old_path);
    $new_source = read_file($new_path);
    $old_statements = extract_config_statements($old_source);
    $new_statements = extract_config_statements($new_source);

    $new_plugins = [];
    foreach ($new_statements as $statement) {
        if (strtolower($statement['key']) === 'plugins') {
            $new_plugins = array_merge($new_plugins, plugin_names_from_statement($statement['text']));
        }
    }
    $new_plugins = array_values(array_unique($new_plugins));

    $old_plugins = [];
    $custom_statements = [];
    $des_key_statement = null;

    foreach ($old_statements as $statement) {
        $key = strtolower($statement['key']);

        if ($key === 'plugins') {
            $old_plugins = array_merge($old_plugins, plugin_names_from_statement($statement['text']));
            continue;
        }

        if ($key === 'des_key') {
            $value = statement_literal_value($statement);
            if ($value === null) {
                warning('Old des_key is not a literal string; keeping the new key');
            } elseif ($value === '') {
                warning('Old des_key is empty; keeping the newly generated key');
            } elseif ($value === EXAMPLE_DES_KEY) {
                warning('Old des_key still has the distribution example value; keeping the new key');
            } else {
                $des_key_statement = trim($statement['text']);
            }
            continue;
        }

        if (in_array($key, EXCLUDED_KEYS, true)) {
            continue;
        }

        $custom_statements[] = trim($statement['text']);
    }

    $old_plugins = array_values(array_unique($old_plugins));
    $merged_plugins = $new_plugins;

    foreach ($old_plugins as $plugin) {
        if (in_array($plugin, $merged_plugins, true)) {
            continue;
        }

        $available = is_dir($install_dir . '/plugins/' . $plugin)
            || is_dir($config_dir . '/plugins/' . $plugin);

        if (!$available) {
            warning("Plugin '$plugin' is not available in the new Roundcube install; not enabling it");
        } elseif (in_array($plugin, KNOWN_COMPATIBLE_PLUGINS, true)) {
            $merged_plugins[] = $plugin;
        } else {
            warning(
                "Plugin '$plugin' was copied but is not a known-compatible upstream plugin; "
                . 'not enabling it automatically'
            );
        }
    }

    $append = PHP_EOL . PHP_EOL
        . '/* --- Settings preserved from the distribution-packaged Roundcube by the Hestia migration --- */' . PHP_EOL;

    foreach ($custom_statements as $statement) {
        $append .= $statement . PHP_EOL . PHP_EOL;
    }

    if ($des_key_statement !== null) {
        $append .= '/* Preserve des_key so existing session data remains readable */' . PHP_EOL
            . $des_key_statement . PHP_EOL . PHP_EOL;
    }

    $append .= '/* Hestia plugins plus compatible plugins from the distribution install */' . PHP_EOL
        . '$config["plugins"] = ' . export_php_array($merged_plugins) . ';' . PHP_EOL;

    if (file_put_contents($new_path, $append, FILE_APPEND) === false) {
        fail("Cannot append to $new_path");
    }

    echo 'Preserved ' . count($custom_statements) . ' custom config setting(s)' . PHP_EOL;
    echo 'des_key: ' . ($des_key_statement === null ? 'new key kept' : 'old key preserved') . PHP_EOL;
    echo 'Plugins: ' . implode(', ', $merged_plugins) . PHP_EOL;

    return 0;
}

function copy_missing_tree(string $source, string $destination): int
{
    $copied = 0;

    if (is_link($source)) {
        if (file_exists($destination) || is_link($destination)) {
            return 0;
        }
        $link_target = readlink($source);
        if ($link_target !== false && symlink($link_target, $destination)) {
            $copied++;
        }
        return $copied;
    }

    if (!is_dir($source)) {
        if (file_exists($destination) || is_link($destination)) {
            return 0;
        }
        if (copy($source, $destination)) {
            chmod($destination, fileperms($source) & 0777);
            $copied++;
        } else {
            fail("Cannot copy $source");
        }
        return $copied;
    }

    if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
        fail("Cannot create $destination");
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relative = substr($item->getPathname(), strlen($source) + 1);
        $target = $destination . '/' . $relative;

        if ($item->isLink()) {
            if (file_exists($target) || is_link($target)) {
                continue;
            }
            $link_target = readlink($item->getPathname());
            if ($link_target !== false && symlink($link_target, $target)) {
                $copied++;
            }
            continue;
        }

        if ($item->isDir()) {
            if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                fail("Cannot create $target");
            }
            continue;
        }

        if (file_exists($target)) {
            continue;
        }

        if (copy($item->getPathname(), $target)) {
            chmod($target, $item->getPerms() & 0777);
            $copied++;
        } else {
            fail("Cannot copy $relative");
        }
    }

    return $copied;
}

function migrate_assets_command(
    string $old_config_dir,
    string $new_config_dir,
    string $old_install_dir,
    string $new_install_dir
): int {
    $copied_configs = 0;
    $copied_plugins = 0;
    $copied_skins = 0;

    $old_plugin_configs = $old_config_dir . '/plugins';
    if (is_dir($old_plugin_configs)) {
        foreach (scandir($old_plugin_configs) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            if (in_array($entry, MANAGED_CONFIG_PLUGINS, true)) {
                continue;
            }

            $source = $old_plugin_configs . '/' . $entry;
            $target = $new_config_dir . '/plugins/' . $entry;

            if (file_exists($target) || is_link($target)) {
                warning("Keeping existing plugin configuration for '$entry' (old copy is in the backup)");
                continue;
            }

            $copied_configs += copy_missing_tree($source, $target);
        }
    }

    foreach (['plugins', 'skins'] as $type) {
        $old_path = $old_install_dir . '/' . $type;
        if (!is_dir($old_path)) {
            continue;
        }

        foreach (scandir($old_path) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $source = $old_path . '/' . $entry;
            $target = $new_install_dir . '/' . $type . '/' . $entry;

            // Never overwrite files shipped by the new upstream release.
            if (file_exists($target) || is_link($target)) {
                continue;
            }

            $count = copy_missing_tree($source, $target);
            if ($type === 'plugins') {
                $copied_plugins += $count;
            } else {
                $copied_skins += $count;
            }
        }
    }

    echo "Copied $copied_configs plugin configuration file(s), "
        . "$copied_plugins custom plugin file(s) and $copied_skins custom skin file(s)" . PHP_EOL;

    return 0;
}

$arguments = array_slice($argv, 1);
if ($arguments === []) {
    usage();
}

$command = array_shift($arguments);

switch ($command) {
    case 'db-config':
        if (count($arguments) !== 1) {
            usage();
        }
        exit(db_config_command($arguments[0]));

    case 'merge-config':
        $options = parse_options($arguments, ['old', 'new', 'install-dir', 'config-dir']);
        exit(
            merge_config_command(
                $options['old'],
                $options['new'],
                rtrim($options['install-dir'], '/'),
                rtrim($options['config-dir'], '/')
            )
        );

    case 'migrate-assets':
        $options = parse_options(
            $arguments,
            ['old-config-dir', 'new-config-dir', 'old-install-dir', 'new-install-dir']
        );
        exit(
            migrate_assets_command(
                rtrim($options['old-config-dir'], '/'),
                rtrim($options['new-config-dir'], '/'),
                rtrim($options['old-install-dir'], '/'),
                rtrim($options['new-install-dir'], '/')
            )
        );

    default:
        usage();
}
