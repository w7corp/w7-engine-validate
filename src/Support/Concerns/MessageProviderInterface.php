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

namespace W7\Validate\Support\Concerns;

use W7\Validate\RuleManager;

interface MessageProviderInterface
{
    /**
     * Handling a message
     *
     * @param string|string[]$messages
     * @return string|string[]
     */
    public function handleMessage($messages);

    /**
     * Get a validation message for the processed attributes and rules.
     *
     * @param string $key
     * @param string|null $rule
     * @return string
     */
    public function getMessage(string $key, ?string $rule = null): ?string;

    /**
     * Get the Initial validation message for an attribute and rule.
     *
     * @param string $key
     * @param string|null $rule
     * @return string
     */
    public function getInitialMessage(string $key, ?string $rule = null): ?string;

    /**
     * Provide array of error messages.
     *
     * @param array $messages
     * @return mixed
     */
    public function setMessage(array $messages): MessageProviderInterface;

    /**
     * Provide array of custom attribute names.
     *
     * @param array $customAttributes
     * @return MessageProviderInterface
     */
    public function setCustomAttributes(array $customAttributes): MessageProviderInterface;

    /**
     * Provide data for validation
     *
     * @param array $data
     * @return MessageProviderInterface
     */
    public function setData(array $data): MessageProviderInterface;

    /**
     * Provide RuleManager for validation
     *
     * @param RuleManager $ruleManager
     * @return MessageProviderInterface
     */
    public function setRuleManager(RuleManager $ruleManager): MessageProviderInterface;
}
