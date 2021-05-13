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

use W7\Validate\Support\Concerns\SceneInterface;

class RuleManagerScene implements SceneInterface
{
    /**
     * The rules to be applied to the data.
     * @var array
     */
    protected $checkRules;

    /**
     * RuleManagerScene constructor.
     * @param array $checkRules The rules to be applied to the data.
     */
    public function __construct(array $checkRules = [])
    {
        $this->checkRules = $checkRules;
    }

    /** @inheritDoc */
    public function only(array $fields): SceneInterface
    {
        $this->checkRules = array_intersect_key($this->checkRules, array_flip($fields));
        return $this;
    }

    /** @inheritDoc */
    public function append(string $field, $rules): SceneInterface
    {
        if (isset($this->checkRules[$field])) {
            if (!is_array($this->checkRules[$field])) {
                $this->checkRules[$field] = explode('|', $this->checkRules[$field]);
            }

            if (!is_array($rules)) {
                $rules = explode('|', $rules);
            }
            array_push($this->checkRules[$field], ...$rules);
        }

        return $this;
    }

    /** @inheritDoc */
    public function remove(string $field, $rule = null): SceneInterface
    {
        $removeRule = $rule;
        if (is_string($rule) && false !== strpos($rule, '|')) {
            $removeRule = explode('|', $rule);
        }

        if (is_array($removeRule)) {
            foreach ($removeRule as $rule) {
                $this->remove($field, $rule);
            }

            return $this;
        }

        if (isset($this->checkRules[$field])) {
            if (null === $rule) {
                $this->checkRules[$field] = [];
                return $this;
            }

            $rules = $this->checkRules[$field];

            if (is_string($rules)) {
                $rules = explode('|', $rules);
            }

            if (false !== strpos($rule, ':')) {
                $rule = substr($rule, 0, strpos($rule, ':'));
            }

            $rules = array_filter($rules, function ($value) use ($rule) {
                if (false !== strpos($value, ':')) {
                    $value = substr($value, 0, strpos($value, ':'));
                }
                return $value !== $rule;
            });

            $this->checkRules[$field] = $rules;
        }

        return $this;
    }

    /** @inheritDoc */
    public function appendCheckField(string $field): SceneInterface
    {
        $rule             = $this->rule[$field] ?? '';
        $this->checkRules = array_merge($this->checkRules, [$field => $rule]);
        return $this;
    }

    /** @inheritDoc */
    public function removeCheckField(string $field): SceneInterface
    {
        unset($this->checkRules[$field]);
        return $this;
    }

    /** @inheritDoc */
    public function getRules(): array
    {
        return $this->checkRules;
    }
}
