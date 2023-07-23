<?php

namespace Rakit\Validation;

class Validator
{
    use Traits\TranslationsTrait, Traits\MessagesTrait;

    /** @var array */
    protected $translations = [];

    /** @var array */
    protected $validators = [];

    /** @var bool */
    protected $allowRuleOverride = false;

    /** @var bool */
    protected $useHumanizedKeys = true;

    /**
     * Constructor
     *
     * @param array $messages
     * @return void
     */
    public function __construct(array $messages = [])
    {
        $this->messages = $messages;
        $this->registerBaseValidators();
    }

    /**
     * Register or override existing validator
     *
     * @param mixed $key
     * @param \Rakit\Validation\Rule $rule
     * @return void
     */
    public function setValidator(string $key, Rule $rule)
    {
        $this->validators[$key] = $rule;
        $rule->setKey($key);
    }

    /**
     * Get validator object from given $key
     *
     * @param mixed $key
     * @return mixed
     */
    public function getValidator($key)
    {
        return isset($this->validators[$key]) ? $this->validators[$key] : null;
    }

    /**
     * Validate $inputs
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return Validation
     */
    public function validate(array $inputs, array $rules, array $messages = []): Validation
    {
        $validation = $this->make($inputs, $rules, $messages);
        $validation->validate();
        return $validation;
    }

    /**
     * Given $inputs, $rules and $messages to make the Validation class instance
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return Validation
     */
    public function make(array $inputs, array $rules, array $messages = []): Validation
    {
        $messages = array_merge($this->messages, $messages);
        $validation = new Validation($this, $inputs, $rules, $messages);
        $validation->setTranslations($this->getTranslations());

        return $validation;
    }

    /**
     * Magic invoke method to make Rule instance
     *
     * @param string $rule
     * @return Rule
     * @throws RuleNotFoundException
     */
    public function __invoke(string $rule): Rule
    {
        $args = func_get_args();
        $rule = array_shift($args);
        $params = $args;
        $validator = $this->getValidator($rule);
        if (!$validator) {
            throw new RuleNotFoundException("Validator '{$rule}' is not registered", 1);
        }

        $clonedValidator = clone $validator;
        $clonedValidator->fillParameters($params);

        return $clonedValidator;
    }

    /**
     * Initialize base validators array
     *
     * @return void
     */
    protected function registerBaseValidators()
    {
        $baseValidator = [
            'required'                  => new Rules\Required,
            'required_if'               => new Rules\RequiredIf,
            'required_unless'           => new Rules\RequiredUnless,
            'required_with'             => new Rules\RequiredWith,
            'required_without'          => new Rules\RequiredWithout,
            'required_with_all'         => new Rules\RequiredWithAll,
            'required_without_all'      => new Rules\RequiredWithoutAll,
            'email'                     => new Rules\Email,
            'alpha'                     => new Rules\Alpha,
            'numeric'                   => new Rules\Numeric,
            'alpha_num'                 => new Rules\AlphaNum,
            'alpha_dash'                => new Rules\AlphaDash,
            'alpha_spaces'              => new Rules\AlphaSpaces,
            'in'                        => new Rules\In,
            'not_in'                    => new Rules\NotIn,
            'min'                       => new Rules\Min,
            'max'                       => new Rules\Max,
            'between'                   => new Rules\Between,
            'url'                       => new Rules\Url,
            'integer'                   => new Rules\Integer,
            'boolean'                   => new Rules\Boolean,
            'ip'                        => new Rules\Ip,
            'ipv4'                      => new Rules\Ipv4,
            'ipv6'                      => new Rules\Ipv6,
            'extension'                 => new Rules\Extension,
            'array'                     => new Rules\TypeArray,
            'same'                      => new Rules\Same,
            'regex'                     => new Rules\Regex,
            'date'                      => new Rules\Date,
            'accepted'                  => new Rules\Accepted,
            'present'                   => new Rules\Present,
            'different'                 => new Rules\Different,
            'uploaded_file'             => new Rules\UploadedFile,
            'mimes'                     => new Rules\Mimes,
            'callback'                  => new Rules\Callback,
            'before'                    => new Rules\Before,
            'after'                     => new Rules\After,
            'lowercase'                 => new Rules\Lowercase,
            'uppercase'                 => new Rules\Uppercase,
            'json'                      => new Rules\Json,
            'digits'                    => new Rules\Digits,
            'digits_between'            => new Rules\DigitsBetween,
            'defaults'                  => new Rules\Defaults,
            'default'                   => new Rules\Defaults, // alias of defaults
            'nullable'                  => new Rules\Nullable,
        ];

        foreach ($baseValidator as $key => $validator) {
            $this->setValidator($key, $validator);
        }
    }

    /**
     * Given $ruleName and $rule to add new validator
     *
     * @param string $ruleName
     * @param \Rakit\Validation\Rule $rule
     * @return void
     */
    public function addValidator(string $ruleName, Rule $rule)
    {
        if (!$this->allowRuleOverride && array_key_exists($ruleName, $this->validators)) {
            throw new RuleQuashException(
                "You cannot override a built in rule. You have to rename your rule"
            );
        }

        $this->setValidator($ruleName, $rule);
    }

    /**
     * Set rule can allow to be overrided
     *
     * @param boolean $status
     * @return void
     */
    public function allowRuleOverride(bool $status = false)
    {
        $this->allowRuleOverride = $status;
    }

    /**
     * Set this can use humanize keys
     *
     * @param boolean $useHumanizedKeys
     * @return void
     */
    public function setUseHumanizedKeys(bool $useHumanizedKeys = true)
    {
        $this->useHumanizedKeys = $useHumanizedKeys;
    }

    /**
     * Get $this->useHumanizedKeys value
     *
     * @return void
     */
    public function isUsingHumanizedKey(): bool
    {
        return $this->useHumanizedKeys;
    }
}
