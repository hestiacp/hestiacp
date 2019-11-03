<?php

declare(strict_types=1);

namespace Hestia\System;

class Util
{
    public static function join_paths()
    {
        $paths = array();

        foreach (func_get_args() as $arg) {
            if ($arg !== '') {
                $paths[] = $arg;
            }
        }

        return preg_replace('#/+#', '/', join('/', $paths));
    }

    public static function generate_string(int $length = 16)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~`!@|#[]$%^&*() _-=+{}:;<>?,./';
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $random_string;
    }
}
