<?php

namespace Rakit\Validation\Rules;

use Rakit\Validation\Rule;

class Date extends Rule
{

    /** @var string */
    protected $message = "The :attribute is not valid date format";

    /** @var array */
    protected $fillableParams = ['format'];

    /** @var array */
    protected $params = [
        'format' => 'Y-m-d'
    ];

    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool
    {
        $this->requireParameters($this->fillableParams);

        $format = $this->parameter('format');
        return date_create_from_format($format, $value) !== false;
    }
}
