<?php

namespace Rakit\Validation\Rules\Traits;

use Exception;

trait DateUtilsTrait
{

    /**
     * Check the $date is valid
     *
     * @param string $date
     * @return bool
     */
    protected function isValidDate(string $date): bool
    {
        return (strtotime($date) !== false);
    }

    /**
     * Throw exception
     *
     * @param string $value
     * @return Exception
     */
    protected function throwException(string $value)
    {
        // phpcs:ignore
        return new Exception("Expected a valid date, got '{$value}' instead. 2016-12-08, 2016-12-02 14:58, tomorrow are considered valid dates");
    }

    /**
     * Given $date and get the time stamp
     *
     * @param mixed $date
     * @return int
     */
    protected function getTimeStamp($date): int
    {
        return strtotime($date);
    }
}
