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
     * 错误消息，支持format字符串
     * @var string
     */
    protected string $message = '';

    /**
     * 用于format错误消息的参数
     * @var array
     */
    protected array $messageParam = [];

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
