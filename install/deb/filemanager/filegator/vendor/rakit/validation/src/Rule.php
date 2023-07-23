<?php

namespace Rakit\Validation;

use Rakit\Validation\MissingRequiredParameterException;

abstract class Rule
{
    /** @var string */
    protected $key;

    /** @var \Rakit\Validation\Attribute|null */
    protected $attribute;

    /** @var \Rakit\Validation\Validation|null */
    protected $validation;

    /** @var bool */
    protected $implicit = false;

    /** @var array */
    protected $params = [];

    /** @var array */
    protected $paramsTexts = [];

    /** @var array */
    protected $fillableParams = [];

    /** @var string */
    protected $message = "The :attribute is invalid";

    abstract public function check($value): bool;

    /**
     * Set Validation class instance
     *
     * @param \Rakit\Validation\Validation $validation
     * @return void
     */
    public function setValidation(Validation $validation)
    {
        $this->validation = $validation;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return void
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * Get key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key ?: get_class($this);
    }

    /**
     * Set attribute
     *
     * @param \Rakit\Validation\Attribute $attribute
     * @return void
     */
    public function setAttribute(Attribute $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Get attribute
     *
     * @return \Rakit\Validation\Attribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters(): array
    {
        return $this->params;
    }

    /**
     * Set params
     *
     * @param array $params
     * @return \Rakit\Validation\Rule
     */
    public function setParameters(array $params): Rule
    {
        $this->params = array_merge($this->params, $params);
        return $this;
    }

    /**
     * Set parameters
     *
     * @param string $key
     * @param mixed $value
     * @return \Rakit\Validation\Rule
     */
    public function setParameter(string $key, $value): Rule
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Fill $params to $this->params
     *
     * @param array $params
     * @return \Rakit\Validation\Rule
     */
    public function fillParameters(array $params): Rule
    {
        foreach ($this->fillableParams as $key) {
            if (empty($params)) {
                break;
            }
            $this->params[$key] = array_shift($params);
        }
        return $this;
    }

    /**
     * Get parameter from given $key, return null if it not exists
     *
     * @param string $key
     * @return mixed
     */
    public function parameter(string $key)
    {
        return isset($this->params[$key])? $this->params[$key] : null;
    }

    /**
     * Set parameter text that can be displayed in error message using ':param_key'
     *
     * @param string $key
     * @param string $text
     * @return void
     */
    public function setParameterText(string $key, string $text)
    {
        $this->paramsTexts[$key] = $text;
    }

    /**
     * Get $paramsTexts
     *
     * @return array
     */
    public function getParametersTexts(): array
    {
        return $this->paramsTexts;
    }

    /**
     * Check whether this rule is implicit
     *
     * @return boolean
     */
    public function isImplicit(): bool
    {
        return $this->implicit;
    }

    /**
     * Just alias of setMessage
     *
     * @param string $message
     * @return \Rakit\Validation\Rule
     */
    public function message(string $message): Rule
    {
        return $this->setMessage($message);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return \Rakit\Validation\Rule
     */
    public function setMessage(string $message): Rule
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Check given $params must be exists
     *
     * @param array $params
     * @return void
     * @throws \Rakit\Validation\MissingRequiredParameterException
     */
    protected function requireParameters(array $params)
    {
        foreach ($params as $param) {
            if (!isset($this->params[$param])) {
                $rule = $this->getKey();
                throw new MissingRequiredParameterException("Missing required parameter '{$param}' on rule '{$rule}'");
            }
        }
    }
}
