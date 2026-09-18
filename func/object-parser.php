<?php

declare(strict_types=1);

/**
 * Parse Hestia KEY=value object configuration syntax.
 *
 * Supported examples:
 * KEY1='value1' KEY2='value with spaces' KEY3=''
 * KEY=value
 * KEY=value\ with\ spaces
 * KEY=ab\ c'de f'ghi
 * KEY=
 * KEY= KEY2=value
 * KEY='abc'def
 * KEY=abc''
 * KEY=\'
 * KEY=\\
 */

function objectParserFail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(2);
}

function parseObjectKvLine(
    string $unparsed,
    array $reserved,
    array $exceptions
): array {
    $result = [];

    while ($unparsed !== '') {
        $unparsed = ltrim($unparsed);

        if ($unparsed === '') {
            break;
        }

        $keyNameExtracted = preg_match(
            '/^([a-zA-Z][a-zA-Z0-9_]*)=/',
            $unparsed,
            $matches
        );

        if ($keyNameExtracted !== 1) {
            objectParserFail(
                'Invalid key value format. Could not extract key name from: '
                . $unparsed
            );
        }

        $keyName = $matches[1];
        $unparsed = substr($unparsed, strlen($matches[0]));

        $skipReserved =
            !in_array($keyName, $exceptions, true)
            && (
                in_array($keyName, $reserved, true)
                || strpos($keyName, 'BASH_FUNC_') === 0
            );

        $keyValue = '';
        $isInQuote = false;

        while (true) {
            if ($isInQuote) {
                $pos = strpos($unparsed, "'");

                if ($pos === false) {
                    objectParserFail(
                        'Invalid key value format. No closing quote for key: '
                        . $keyName
                        . ' in: '
                        . $unparsed
                    );
                }

                $keyValue .= substr($unparsed, 0, $pos);
                $unparsed = substr($unparsed, $pos + 1);
                $isInQuote = false;

                if ($unparsed === '') {
                    break;
                }

                continue;
            }

            $match = preg_match(
                '/\s|\'|\\\\/u',
                $unparsed,
                $matches,
                PREG_OFFSET_CAPTURE
            );

            if ($match !== 1) {
                $keyValue .= $unparsed;
                $unparsed = '';
                break;
            }

            $matchedChar = $matches[0][0];
            $pos = $matches[0][1];

            $keyValue .= substr($unparsed, 0, $pos);
            $unparsed = substr(
                $unparsed,
                $pos + strlen($matchedChar)
            );

            if ($matchedChar === "'") {
                if ($unparsed === '') {
                    objectParserFail(
                        'Invalid key value format. No closing quote for key: '
                        . $keyName
                    );
                }

                $isInQuote = true;
                continue;
            }

            if ($matchedChar === '\\') {
                if ($unparsed === '') {
                    objectParserFail(
                        'Invalid key value format. Escape character cannot '
                        . 'be the last character in value for key: '
                        . $keyName
                    );
                }

                $nextChar = mb_substr(
                    $unparsed,
                    0,
                    1,
                    'UTF-8'
                );

                $keyValue .= $nextChar;
                $unparsed = substr(
                    $unparsed,
                    strlen($nextChar)
                );

                continue;
            }

            $unparsed = ltrim($unparsed);
            break;
        }

        if ($skipReserved) {
            continue;
        }

        if (array_key_exists($keyName, $result)) {
            $message = 'Warning: Duplicate key name: '
                . $keyName
                . '. ';

            if ($result[$keyName] === $keyValue) {
                $message .= 'value is identical.';
            } else {
                $message .= var_export([
                    'old_value' => $result[$keyName],
                    'new_value' => $keyValue,
                ], true);
            }

            fwrite(STDERR, $message . PHP_EOL);
        }

        $result[$keyName] = $keyValue;
    }

    return $result;
}

function parseObjectKvList(string $value): array
{
    return preg_split(
        '/\s+/',
        trim($value),
        -1,
        PREG_SPLIT_NO_EMPTY
    ) ?: [];
}


function parseObjectKvFile(
    string $file,
    array $reserved,
    array $exceptions
): array {
    $lines = file(
        $file,
        FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
    );

    if ($lines === false) {
        objectParserFail('Could not read file: ' . $file);
    }

    $records = [];

    foreach ($lines as $line) {
        $records[] = parseObjectKvLine(
            $line,
            $reserved,
            $exceptions
        );
    }

    return $records;
}

function objectParserEncodeJson(array $data): string
{
    try {
        return json_encode(
            (object) $data,
            JSON_PRETTY_PRINT
            | JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
            | JSON_THROW_ON_ERROR
        ) . PHP_EOL;
    } catch (JsonException $exception) {
        objectParserFail(
            'Could not encode JSON: ' . $exception->getMessage()
        );
    }
}

if (
    PHP_SAPI === 'cli'
    && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)
) {
    if ($argc < 5 || $argv[1] !== 'line-shell') {
        objectParserFail(
            'Usage: object-parser.php line-shell '
            . '"KEY=value ..." RESERVED_KEYS EXCEPTIONS'
        );
    }

    $result = parseObjectKvLine(
        $argv[2],
        parseObjectKvList($argv[3]),
        parseObjectKvList($argv[4])
    );

    $assignments = [];

    foreach ($result as $key => $value) {
        $quoted = "'" . strtr(
            $value,
            ["'" => "'\\''"]
        ) . "'";

        $assignments[] = $key . '=' . $quoted;
    }

    echo implode(' ', $assignments);
}
