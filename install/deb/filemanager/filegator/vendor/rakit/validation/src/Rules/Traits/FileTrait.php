<?php

namespace Rakit\Validation\Rules\Traits;

use InvalidArgumentException;
use Rakit\Validation\Helper;

trait FileTrait
{

    /**
     * Check whether value is from $_FILES
     *
     * @param mixed $value
     * @return bool
     */
    public function isValueFromUploadedFiles($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $keys = ['name', 'type', 'tmp_name', 'size', 'error'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check the $value is uploaded file
     *
     * @param mixed $value
     * @return bool
     */
    public function isUploadedFile($value): bool
    {
        return $this->isValueFromUploadedFiles($value) && is_uploaded_file($value['tmp_name']);
    }

    /**
     * Resolve uploaded file value
     *
     * @param  mixed $value
     * @return array|null
     */
    public function resolveUploadedFileValue($value)
    {
        if (!$this->isValueFromUploadedFiles($value)) {
            return null;
        }

        // Here $value should be an array:
        // [
        //      'name'      => string|array,
        //      'type'      => string|array,
        //      'size'      => int|array,
        //      'tmp_name'  => string|array,
        //      'error'     => string|array,
        // ]

        // Flatten $value to it's array dot format,
        // so our array must be something like:
        // ['name' => string, 'type' => string, 'size' => int, ...]
        // or for multiple values:
        // ['name.0' => string, 'name.1' => string, 'type.0' => string, 'type.1' => string, ...]
        // or for nested array:
        // ['name.foo.bar' => string, 'name.foo.baz' => string, 'type.foo.bar' => string, 'type.foo.baz' => string, ...]
        $arrayDots = Helper::arrayDot($value);

        $results = [];
        foreach ($arrayDots as $key => $val) {
            // Move first key to last key
            // name.foo.bar -> foo.bar.name
            $splits = explode(".", $key);
            $firstKey = array_shift($splits);
            $key = count($splits) ? implode(".", $splits) . ".{$firstKey}" : $firstKey;

            Helper::arraySet($results, $key, $val);
        }
        return $results;
    }
}
