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
use RuntimeException;
use W7\Validate\Support\Rule\BaseRule;
use W7\Validate\Support\Storage\ValidateCollection;

/**
 * Class ValidateScene
 * @package W7\Validate\Support
 *
 * @property-read array $events       Events to be processed
 * @property-read array $afters         Methods to be executed after validation
 * @property-read array $befores        Methods to be executed before validation
 * @property-read bool  $eventPriority  Event Priority
 */
class ValidateScene extends RuleManagerScene
{
    /**
     * Data to be validated
     * @var array
     */
    protected $checkData = [];

    /**
     * Events to be processed
     * @var array
     */
    protected $events = [];

    /**
     * Methods to be executed after validation
     * @var array
     */
    protected $afters = [];

    /**
     * Methods to be executed before validation
     * @var array
     */
    protected $befores = [];

    /**
     * Event Priority
     * @var bool
     */
    private $eventPriority;

    /**
     * ValidateScene constructor.
     * @param array $checkRules
     * @param array $checkData
     */
    public function __construct(array $checkRules = [], array $checkData = [])
    {
        parent::__construct($checkRules);
        $this->checkData = $checkData;
    }

    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new RuntimeException('Unknown property:' . $name);
    }

    /**
     * Add conditions to a given field based on a Closure.
     *
     * @param string|string[]       $attribute field name
     * @param string|array|BaseRule $rules     rules
     * @param callable              $callback  Closure,method provides a {@see ValidateCollection} $data parameter,
     *                                         which is the current validation data,
     *                                         if the Closure passed as the third argument returns true, the rules will be added.
     * @return $this
     */
    public function sometimes($attribute, $rules, callable $callback): ValidateScene
    {
        $data   = $this->getValidateData();
        $result = call_user_func($callback, $data);

        if (!$result) {
            return $this;
        }

        if (is_array($attribute)) {
            foreach ($attribute as $filed) {
                $this->append($filed, $rules);
            }
        } else {
            $this->append($attribute, $rules);
        }

        return $this;
    }

    /**
     * Join the event
     *
     * @param string $handler  Full class name of the event, full namespace string or add ::class
     * @param mixed ...$params Parameters to be passed to the event
     * @return $this
     */
    public function event(string $handler, ...$params): ValidateScene
    {
        $this->events[] = [$handler, $params];
        return $this;
    }

    /**
     * Add a method that needs to be executed before validation
     *
     * @param string $callbackName Validate the method name in the class
     * @param mixed  ...$params    Parameters to be passed to the method
     * @return $this
     */
    public function before(string $callbackName, ...$params): ValidateScene
    {
        $this->befores[] = [$callbackName, $params];
        return $this;
    }

    /**
     * Add a method that needs to be executed after validation
     *
     * @param string $callbackName Validate the method name in the class
     * @param mixed  ...$params    Parameters to be passed to the method
     * @return $this
     */
    public function after(string $callbackName, ...$params): ValidateScene
    {
        $this->afters[] = [$callbackName, $params];
        return $this;
    }

    /**
     * Set event priority
     *
     * @param bool $priority
     * @return $this
     */
    public function setEventPriority(bool $priority): ValidateScene
    {
        $this->eventPriority = $priority;
        return $this;
    }

    /**
     * Provide the data to be validated
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data = []): ValidateScene
    {
        $this->checkData = $data;
        return $this;
    }

    /**
     * Get the current validation data
     *
     * @param string $key
     * @param mixed $default
     * @return array|mixed
     */
    public function getData(string $key = '', $default = null)
    {
        if (!empty($key)) {
            return Arr::get($this->checkData, $key, $default);
        }
        return $this->checkData;
    }

    /**
     * Get the current validation data,Return the {@see ValidateCollection} type
     *
     * @return ValidateCollection
     */
    public function getValidateData(): ValidateCollection
    {
        return validate_collect($this->getData());
    }
}
