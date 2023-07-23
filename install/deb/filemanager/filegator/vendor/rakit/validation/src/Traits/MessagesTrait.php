<?php

namespace Rakit\Validation\Traits;

trait MessagesTrait
{

    /** @var array */
    protected $messages = [];

    /**
     * Given $key and $message to set message
     *
     * @param mixed $key
     * @param mixed $message
     * @return void
     */
    public function setMessage(string $key, string $message)
    {
        $this->messages[$key] = $message;
    }

    /**
     * Given $messages and set multiple messages
     *
     * @param array $messages
     * @return void
     */
    public function setMessages(array $messages)
    {
        $this->messages = array_merge($this->messages, $messages);
    }

    /**
     * Given message from given $key
     *
     * @param string $key
     * @return string
     */
    public function getMessage(string $key): string
    {
        return array_key_exists($key, $this->messages) ? $this->messages[$key] : $key;
    }

    /**
     * Get all $messages
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}
