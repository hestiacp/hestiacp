<?php

namespace Rakit\Validation\Rules\Traits;

use InvalidArgumentException;

trait SizeTrait
{

    /**
     * Get size (int) value from given $value
     *
     * @param int|string $value
     * @return float|false
     */
    protected function getValueSize($value)
    {
        if ($this->getAttribute()
            && ($this->getAttribute()->hasRule('numeric') || $this->getAttribute()->hasRule('integer'))
            && is_numeric($value)
        ) {
            $value = (float) $value;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        } elseif (is_string($value)) {
            return (float) mb_strlen($value, 'UTF-8');
        } elseif ($this->isUploadedFileValue($value)) {
            return (float) $value['size'];
        } elseif (is_array($value)) {
            return (float) count($value);
        } else {
            return false;
        }
    }

    /**
     * Given $size and get the bytes
     *
     * @param string|int $size
     * @return float
     * @throws InvalidArgumentException
     */
    protected function getBytesSize($size)
    {
        if (is_numeric($size)) {
            return (float) $size;
        }

        if (!is_string($size)) {
            throw new InvalidArgumentException("Size must be string or numeric Bytes", 1);
        }

        if (!preg_match("/^(?<number>((\d+)?\.)?\d+)(?<format>(B|K|M|G|T|P)B?)?$/i", $size, $match)) {
            throw new InvalidArgumentException("Size is not valid format", 1);
        }

        $number = (float) $match['number'];
        $format = isset($match['format']) ? $match['format'] : '';

        switch (strtoupper($format)) {
            case "KB":
            case "K":
                return $number * 1024;

            case "MB":
            case "M":
                return $number * pow(1024, 2);

            case "GB":
            case "G":
                return $number * pow(1024, 3);

            case "TB":
            case "T":
                return $number * pow(1024, 4);

            case "PB":
            case "P":
                return $number * pow(1024, 5);

            default:
                return $number;
        }
    }

    /**
     * Check whether value is from $_FILES
     *
     * @param mixed $value
     * @return bool
     */
    public function isUploadedFileValue($value): bool
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
}
