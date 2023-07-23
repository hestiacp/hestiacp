<?php

namespace Rakit\Validation\Rules;

use Rakit\Validation\Rule;

class Min extends Rule
{
    use Traits\SizeTrait;

    /** @var string */
    protected $message = "The :attribute minimum is :min";

    /** @var array */
    protected $fillableParams = ['min'];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool
    {
        $this->requireParameters($this->fillableParams);

        $min = $this->getBytesSize($this->parameter('min'));
        $valueSize = $this->getValueSize($value);

        if (!is_numeric($valueSize)) {
            return false;
        }

        return $valueSize >= $min;
    }
}
