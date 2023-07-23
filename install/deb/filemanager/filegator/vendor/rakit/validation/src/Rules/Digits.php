<?php

namespace Rakit\Validation\Rules;

use Rakit\Validation\Rule;

class Digits extends Rule
{

    /** @var string */
    protected $message = "The :attribute must be numeric and must have an exact length of :length";

    /** @var array */
    protected $fillableParams = ['length'];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool
    {
        $this->requireParameters($this->fillableParams);

        $length = (int) $this->parameter('length');

        return ! preg_match('/[^0-9]/', $value)
                    && strlen((string) $value) == $length;
    }
}
