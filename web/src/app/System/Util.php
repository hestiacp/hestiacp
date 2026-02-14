<?php

declare(strict_types=1);

namespace Hestia\System;

class Util
{
    /*
     * Method from: https://stackoverflow.com/a/15575293
     * https://stackoverflow.com/questions/1091107/how-to-join-filesystem-path-strings-in-php
     */
    public static function joinPaths()
    {
        $paths = [];
        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }
        return preg_replace('#/+#', '/', implode('/', $paths));
    }

    public static function generateString(int $length = 16, $full = true)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if ($full) {
            $chars .= '~`!@|#[]$%^&*() _-=+{}:;<>?,./';
        }
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $random_string;
    }
}
