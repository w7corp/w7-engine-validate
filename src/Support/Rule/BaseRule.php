<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2021 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Validate\Support\Rule;

abstract class BaseRule implements RuleInterface
{
    /**
     * Error messages, support for format strings
     * @var string
     */
    protected $message = '';

    /**
     * Parameters for format error messages
     * @var array
     */
    protected $messageParam = [];

    public function setMessage(string $message): BaseRule
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return vsprintf($this->message, $this->messageParam);
    }

    public function message(): string
    {
        return $this->getMessage();
    }
}
