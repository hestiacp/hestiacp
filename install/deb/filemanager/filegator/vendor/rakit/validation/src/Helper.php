<?php

namespace Rakit\Validation;

class Helper
{

    /**
     * Determine if a given string matches a given pattern.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Str.php#L119
     *
     * @param  string $pattern
     * @param  string $value
     * @return bool
     */
    public static function strIs(string $pattern, string $value): bool
    {
        if ($pattern == $value) {
            return true;
        }

        $pattern = preg_quote($pattern, '#');

        // Asterisks are translated into zero-or-more regular expression wildcards
        // to make it convenient to check if the strings starts with the given
        // pattern such as "library/*", making any string check convenient.
        $pattern = str_replace('\*', '.*', $pattern);

        return (bool) preg_match('#^'.$pattern.'\z#u', $value);
    }

    /**
     * Check if an item or items exist in an array using "dot" notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L81
     *
     * @param  array $array
     * @param  string $key
     * @return bool
     */
    public static function arrayHas(array $array, string $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an item from an array using "dot" notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L246
     *
     * @param  array       $array
     * @param  string|null $key
     * @param  mixed       $default
     * @return mixed
     */
    public static function arrayGet(array $array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Flatten a multi-dimensional associative array with dots.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/Arr.php#L81
     *
     * @param  array  $array
     * @param  string $prepend
     * @return array
     */
    public static function arrayDot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, static::arrayDot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }

        return $results;
    }

    /**
     * Set an item on an array or object using dot notation.
     * Adapted from: https://github.com/illuminate/support/blob/v5.3.23/helpers.php#L437
     *
     * @param mixed             $target
     * @param string|array|null $key
     * @param mixed             $value
     * @param bool              $overwrite
     * @return mixed
     */
    public static function arraySet(&$target, $key, $value, $overwrite = true): array
    {
        if (is_null($key)) {
            if ($overwrite) {
                return $target = array_merge($target, $value);
            }
            return $target = array_merge($value, $target);
        }

        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (! is_array($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    static::arraySet($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (is_array($target)) {
            if ($segments) {
                if (! array_key_exists($segment, $target)) {
                    $target[$segment] = [];
                }

                static::arraySet($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || ! array_key_exists($segment, $target)) {
                $target[$segment] = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                static::arraySet($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }

    /**
     * Unset an item on an array or object using dot notation.
     *
     * @param  mixed        $target
     * @param  string|array $key
     * @return mixed
     */
    public static function arrayUnset(&$target, $key)
    {
        if (!is_array($target)) {
            return $target;
        }

        $segments = is_array($key) ? $key : explode('.', $key);
        $segment = array_shift($segments);

        if ($segment == '*') {
            $target = [];
        } elseif ($segments) {
            if (array_key_exists($segment, $target)) {
                static::arrayUnset($target[$segment], $segments);
            }
        } elseif (array_key_exists($segment, $target)) {
            unset($target[$segment]);
        }

        return $target;
    }

    /**
     * Get snake_case format from given string
     *
     * @param  string $value
     * @param  string $delimiter
     * @return string
     */
    public static function snakeCase(string $value, string $delimiter = '_'): string
    {
        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return $value;
    }

    /**
     * Join string[] to string with given $separator and $lastSeparator.
     *
     * @param  array        $pieces
     * @param  string       $separator
     * @param  string|null  $lastSeparator
     * @return string
     */
    public static function join(array $pieces, string $separator, string $lastSeparator = null): string
    {
        if (is_null($lastSeparator)) {
            $lastSeparator = $separator;
        }

        $last = array_pop($pieces);

        switch (count($pieces)) {
            case 0:
                return $last ?: '';
            case 1:
                return $pieces[0] . $lastSeparator . $last;
            default:
                return implode($separator, $pieces) . $lastSeparator . $last;
        }
    }

    /**
     * Wrap string[] by given $prefix and $suffix
     *
     * @param  array        $strings
     * @param  string       $prefix
     * @param  string|null  $suffix
     * @return array
     */
    public static function wraps(array $strings, string $prefix, string $suffix = null): array
    {
        if (is_null($suffix)) {
            $suffix = $prefix;
        }

        return array_map(function ($str) use ($prefix, $suffix) {
            return $prefix . $str . $suffix;
        }, $strings);
    }
}
