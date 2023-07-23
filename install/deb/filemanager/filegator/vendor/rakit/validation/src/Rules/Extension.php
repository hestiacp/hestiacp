<?php

namespace Rakit\Validation\Rules;

use Rakit\Validation\Helper;
use Rakit\Validation\Rule;

class Extension extends Rule
{
    
    /** @var string */
    protected $message = "The :attribute must be a :allowed_extensions file";

     /**
     * Given $params and assign the $this->params
     *
     * @param array $params
     * @return self
     */
    public function fillParameters(array $params): Rule
    {
        if (count($params) == 1 && is_array($params[0])) {
            $params = $params[0];
        }
        $this->params['allowed_extensions'] = $params;
        return $this;
    }
    
    /**
     * Check the $value is valid
     *
     * @param mixed $value
     * @return bool
     */
    public function check($value): bool
    {
        $this->requireParameters(['allowed_extensions']);
        $allowedExtensions = $this->parameter('allowed_extensions');
        foreach ($allowedExtensions as $key => $ext) {
            $allowedExtensions[$key] = ltrim($ext, '.');
        }

        $or = $this->validation ? $this->validation->getTranslation('or') : 'or';
        $allowedExtensionsText = Helper::join(Helper::wraps($allowedExtensions, ".", ""), ', ', ", {$or} ");
        $this->setParameterText('allowed_extensions', $allowedExtensionsText);

        $ext = strtolower(pathinfo($value, PATHINFO_EXTENSION));
        return ($ext && in_array($ext, $allowedExtensions)) ? true : false;
    }
}
