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

namespace W7\Validate\Support;

use Illuminate\Support\Arr;
use W7\Validate\RuleManager;
use W7\Validate\Support\Concerns\MessageProviderInterface;

class MessageProvider implements MessageProviderInterface
{
    /**
     * The array of custom attribute names.
     *
     * @var array
     */
    protected $customAttributes = [];

    /**
     * The array of custom error messages.
     *
     * @var array
     */
    protected $message = [];

    /**
     * Data for validate
     *
     * @var array
     */
    protected $data = [];

    /** @inheritDoc */
    public function setRuleManager(RuleManager $ruleManager): MessageProviderInterface
    {
        $this->message          = $ruleManager->getMessages();
        $this->customAttributes = $ruleManager->getCustomAttributes();
        return $this;
    }

    /** @inheritDoc */
    public function setMessage(array $messages): MessageProviderInterface
    {
        $this->message = $messages;
        return $this;
    }

    /** @inheritDoc */
    public function setCustomAttributes(array $customAttributes): MessageProviderInterface
    {
        $this->customAttributes = $customAttributes;
        return $this;
    }

    /** @inheritDoc */
    public function setData(array $data): MessageProviderInterface
    {
        $this->data = $data;
        return $this;
    }

    /** @inheritDoc */
    public function getInitialMessage(string $key, ?string $rule = null): ?string
    {
        if (null !== $rule) {
            $key = Common::makeMessageName($key, $rule);
        }

        return $this->message[$key] ?? '';
    }

    /** @inheritDoc */
    public function handleMessage($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as &$errorMessages) {
                if (is_array($errorMessages)) {
                    $errorMessages = array_map([$this, 'replacingFieldsInMessage'], $errorMessages);
                } else {
                    $errorMessages = $this->replacingFieldsInMessage($errorMessages);
                }
            }

            return $messages;
        }

        return $this->replacingFieldsInMessage($messages);
    }

    /** @inheritDoc */
    public function getMessage(string $key, ?string $rule = null): ?string
    {
        $error = $this->getInitialMessage($key, $rule);
        return $this->replacingFieldsInMessage($error);
    }

    /**
     * Replacing fields in error messages
     *
     * @param string $message
     * @return string|string[]
     */
    private function replacingFieldsInMessage(string $message)
    {
        if (preg_match_all('/{:(.*?)}/', $message, $matches) > 0) {
            foreach ($matches[0] as $index => $pregString) {
                $message = str_replace($pregString, Arr::get($this->data, $matches[1][$index], ''), $message);
            }
        }

        if (preg_match_all('/@{(.*?)}/', $message, $matches) > 0) {
            foreach ($matches[0] as $index => $pregString) {
                $message = str_replace($pregString, $this->customAttributes[$matches[1][$index]] ?? '', $message);
            }
        }

        return $message;
    }
}
