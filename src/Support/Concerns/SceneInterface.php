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

interface SceneInterface
{
    /**
     * Specify the list of fields to be validated
     *
     * @link https://v.neww7.com/en/3/Scene.html#only
     * @param array $fields
     * @return $this
     */
    public function only(array $fields): SceneInterface;

    /**
     * Adding a validation rule for a field
     *
     * @link https://v.neww7.com/en/3/Scene.html#append
     * @param string       $field
     * @param string|array $rules
     * @return $this
     */
    public function append(string $field, $rules): SceneInterface;

    /**
     * Remove the validation rule for a field
     *
     * @link https://v.neww7.com/en/3/Scene.html#remove
     * @param string            $field
     * @param array|string|null $rule  Validate rules.if $rule is null,remove all rules from the current field
     * @return $this
     */
    public function remove(string $field, $rule = null): SceneInterface;

    /**
     * Add fields to the validation list
     *
     * @link https://v.neww7.com/en/3/Scene.html#appendcheckfield
     * @param string $field
     * @return $this
     */
    public function appendCheckField(string $field): SceneInterface;

    /**
     * Delete fields from the validation list
     *
     * @link https://v.neww7.com/en/3/Scene.html#removecheckfield
     * @param string $field
     * @return $this
     */
    public function removeCheckField(string $field): SceneInterface;

    /**
     * Get validate rules
     *
     * @return array
     */
    public function getRules(): array;
}
