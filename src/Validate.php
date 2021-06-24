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

namespace W7\Validate;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationData;
use Illuminate\Validation\ValidationException;
use LogicException;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Exception\ValidateRuntimeException;
use W7\Validate\Support\Concerns\DefaultInterface;
use W7\Validate\Support\Concerns\FilterInterface;
use W7\Validate\Support\Concerns\MessageProviderInterface;
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Support\MessageProvider;
use W7\Validate\Support\Storage\ValidateCollection;
use W7\Validate\Support\Storage\ValidateConfig;
use W7\Validate\Support\ValidateScene;

class Validate extends RuleManager
{
    /**
     * Global Event Handler
     * @var array
     */
    protected $event = [];

    /**
     * Whether to stop running after the first verification failure
     * @var bool
     */
    protected $bail = true;

    /**
     * All validated fields cannot be empty when present
     * @var bool
     */
    protected $filled = true;

    /**
     * The filter. This can be a global function name, anonymous function, etc.
     * @var array
     */
    protected $filter = [];

    /**
     * Sets the specified property to the specified default value.
     * @var array
     */
    protected $default = [];

    /**
     * Event Priority
     * @var bool
     */
    private $eventPriority = true;

    /**
     * Events to be processed for this validate
     * @var array
     */
    private $events = [];

    /**
     * Methods to be executed before this validate
     * @var array
     */
    private $befores = [];

    /**
     * Methods to be executed after this validate
     * @var array
     */
    private $afters = [];

    /**
     * This validation requires a default value for the value
     * @var array
     */
    private $defaults = [];

    /**
     * Filters to be passed for this validation
     * @var array
     */
    private $filters = [];

    /**
     * Error Message Provider
     * @var MessageProviderInterface
     */
    private $messageProvider = null;

    /**
     * Data to be validated
     * @var array
     */
    private $checkData = [];

    private $validatedData = [];

    private $validateFields = [];

    /**
     * Create a validator
     *
     * @param array $rules               Validation rules
     * @param array $messages            Error message
     * @param array $customAttributes    Field Name
     * @return Validate
     */
    public static function make(array $rules = [], array $messages = [], array $customAttributes = []): Validate
    {
        if (empty($rules)) {
            return new static();
        }
        return (new static())->setRules($rules)->setMessages($messages)->setCustomAttributes($customAttributes);
    }

    /**
     * Get Validator Factory
     *
     * @return Factory
     */
    private static function getValidationFactory(): Factory
    {
        return ValidateConfig::instance()->getFactory();
    }
    
    /**
     * Auto validate
     *
     * @param array $data Data to be verified
     * @return array
     * @throws ValidateException
     */
    public function check(array $data): array
    {
        try {
            $this->init();
            $this->checkData = $data;
            $this->addEvent($this->event);
            $rule           = $this->getCheckRules($this->getInitialRules());
            $fields         = array_merge(array_keys($rule), $this->validateFields);
            $this->defaults = array_merge($this->default, $this->defaults);
            $this->filters  = array_merge($this->filter, $this->filters);
            $data           = $this->handleDefault($data, $fields);

            if ($this->filled) {
                $rule = $this->addFilledRule($rule);
            }

            if ($this->bail) {
                $rule = $this->addBailRule($rule);
            }

            if ($this->eventPriority) {
                $this->handleEvent($data, 'beforeValidate');
                $this->handleCallback($data, 1);
            } else {
                $this->handleCallback($data, 1);
                $this->handleEvent($data, 'beforeValidate');
            }

            $data = $this->getValidationFactory()->make($data, $rule, $this->message, $this->customAttributes)->validate();
            $data = array_merge($this->validatedData, $data);
            $data = $this->handlerFilter($data, $fields);

            if ($this->eventPriority) {
                $this->handleCallback($data, 2);
                $this->handleEvent($data, 'afterValidate');
            } else {
                $this->handleEvent($data, 'afterValidate');
                $this->handleCallback($data, 2);
            }

            return $data;
        } catch (ValidationException $e) {
            $errors       = $this->getMessageProvider()->handleMessage($e->errors());
            $errorMessage = '';

            foreach ($errors as $message) {
                $errorMessage = $message[0];
                break;
            }

            throw new ValidateException($errorMessage, 403, $errors, $e);
        }
    }

    /**
     * Get initial rules provided
     *
     * @param string|null $sceneName The scene name, or the current scene name if not provided.
     * @return array
     * @throws ValidateException
     */
    public function getInitialRules(?string $sceneName = ''): array
    {
        if ('' === $sceneName) {
            $sceneName = $this->getCurrentSceneName();
        }

        if (empty($sceneName)) {
            return $this->rule;
        }

        if (method_exists($this, 'scene' . ucfirst($sceneName))) {
            $scene = new ValidateScene($this->rule, $this->checkData);
            call_user_func([$this, 'scene' . ucfirst($sceneName)], $scene);
            $this->event         = array_merge($this->event, $scene->events);
            $this->afters        = array_merge($this->afters, $scene->afters);
            $this->befores       = array_merge($this->befores, $scene->befores);
            $this->defaults      = array_merge($this->defaults, $scene->defaults);
            $this->filters       = array_merge($this->filters, $scene->filters);
            $this->eventPriority = $scene->eventPriority;
            return $scene->getRules();
        }

        if (isset($this->scene[$sceneName])) {
            $sceneRule = $this->scene[$sceneName];

            // Determine if an event is defined
            if (isset($sceneRule['event'])) {
                $events = $sceneRule['event'];
                $this->addEvent($events);
                unset($sceneRule['event']);
            }

            // Methods to be executed before determining the presence or absence of authentication
            if (isset($sceneRule['before'])) {
                $callback = $sceneRule['before'];
                $this->addBefore($callback);
                unset($sceneRule['before']);
            }

            // Methods to be executed after determining the existence of validation
            if (isset($sceneRule['after'])) {
                $callback = $sceneRule['after'];
                $this->addAfter($callback);
                unset($sceneRule['after']);
            }

            if (isset($sceneRule['next']) && !empty($sceneRule['next'])) {
                $next = $sceneRule['next'];
                if ($next === $sceneName) {
                    throw new LogicException('The scene used cannot be the same as the current scene.');
                }
                unset($sceneRule['next']);

                // Pre-validation
                if (!empty($sceneRule)) {
                    // Validated fields are not re-validated
                    $checkFields          = array_diff($sceneRule, $this->validateFields);
                    $checkRules           = $this->getCheckRules(array_intersect_key($this->rule, array_flip($checkFields)));
                    $data                 = $this->getValidationFactory()->make($this->checkData, $checkRules, $this->message, $this->customAttributes)->validate();
                    $this->validateFields = array_merge($this->validateFields, $checkFields);
                    $this->validatedData  = array_merge($this->validatedData, $data);
                }

                // If a scene selector exists
                if (method_exists($this, lcfirst($next) . 'Selector')) {
                    $next = call_user_func([$this, lcfirst($next) . 'Selector'], $this->validatedData);
                    if (is_array($next)) {
                        return array_intersect_key($this->rule, array_flip($next));
                    }
                }
                return $this->getInitialRules($next);
            } else {
                return array_intersect_key($this->rule, array_flip($sceneRule));
            }
        }

        return $this->rule;
    }

    /**
     * Processing method
     *
     * @param array $data
     * @param int $type 1 before method 2 after method
     * @throws ValidateException
     */
    private function handleCallback(array $data, int $type)
    {
        switch ($type) {
            case 1:
                $callbacks = $this->befores;
                $typeName  = 'before';
                break;
            case 2:
                $callbacks = $this->afters;
                $typeName  = 'after';
                break;
            default:
                throw new LogicException('Type Error');
        }

        if (empty($callbacks)) {
            return;
        }

        foreach ($callbacks as $callback) {
            list($callback, $param) = $callback;
            $callback               = $typeName . ucfirst($callback);
            if (!method_exists($this, $callback)) {
                throw new LogicException('Method Not Found');
            }
            if (($result = call_user_func([$this, $callback], $data, ...$param)) !== true) {
                if (isset($this->message[$result])) {
                    $result = $this->getMessageProvider()->handleMessage($this->message[$result]);
                }
                throw new ValidateException($result, 403);
            }
        }
    }

    /**
     * validate event handling
     *
     * @param array $data    Validated data
     * @param string $method Event Name
     * @throws ValidateException
     */
    private function handleEvent(array $data, string $method)
    {
        if (empty($this->events)) {
            return;
        }

        foreach ($this->events as $events) {
            list($callback, $param) = $events;
            if (class_exists($callback) && is_subclass_of($callback, ValidateEventAbstract::class)) {
                /** @var ValidateEventAbstract $handler */
                $handler            = new $callback(...$param);
                $handler->sceneName = $this->getCurrentSceneName();
                $handler->data      = $data;
                if (true !== call_user_func([$handler, $method])) {
                    $message = $handler->message;
                    if (isset($this->message[$message])) {
                        $message = $this->getMessageProvider()->handleMessage($this->message[$message]);
                    }
                    throw new ValidateException($message, 403);
                }
            } else {
                throw new ValidateRuntimeException('Event error or nonexistence');
            }
        }
    }

    /**
     * Filters for processing settings
     *
     * @param array $data
     * @param array $fields
     * @return array
     */
    private function handlerFilter(array $data, array $fields): array
    {
        if (empty($this->filters)) {
            return $data;
        }

        $newData = validate_collect($data);
        $filters = array_intersect_key($this->filters, array_flip($fields));
        foreach ($filters as $field => $callback) {
            if (null === $callback) {
                continue;
            }
            if (false !== strpos($field, '*')) {
                $flatData = ValidationData::initializeAndGatherData($field, $data);
                $pattern  = str_replace('\*', '[^\.]*', preg_quote($field));
                foreach ($flatData as $key => $value) {
                    if (Str::startsWith($key, $field) || preg_match('/^' . $pattern . '\z/', $key)) {
                        $this->filterValue($key, $callback, $newData);
                    }
                }
            } else {
                $this->filterValue($field, $callback, $newData);
            }
        }

        return $newData->toArray();
    }

    /**
     * Filter the given value
     *
     * @param string                           $field    Name of the data field to be processed
     * @param callable|Closure|FilterInterface $callback The filter. This can be a global function name, anonymous function, etc.
     * @param ValidateCollection $data
     */
    private function filterValue(string $field, $callback, ValidateCollection $data)
    {
        if (!$data->has($field)) {
            return;
        }
        $value = $data->get($field);

        if (is_callable($callback)) {
            $value = call_user_func($callback, $value);
        } elseif ((is_string($callback) || is_object($callback)) && class_exists($callback) && is_subclass_of($callback, FilterInterface::class)) {
            /** @var FilterInterface $filter */
            $filter = new $callback;
            $value  = $filter->handle($value);
        } elseif (is_string($callback) && method_exists($this, 'filter' . ucfirst($callback))) {
            $value = call_user_func([$this, 'filter' . ucfirst($callback)], $value);
        } else {
            throw new ValidateRuntimeException('The provided filter is wrong');
        }

        $data->set($field, $value);
    }

    /**
     * Defaults for processing settings
     *
     * @param array $data
     * @param array $fields
     * @return array
     */
    private function handleDefault(array $data, array $fields): array
    {
        if (empty($this->defaults)) {
            return $data;
        }

        $newData  = validate_collect($data);
        $defaults = array_intersect_key($this->defaults, array_flip($fields));
        foreach ($defaults as $field => $value) {
            // Skip array members
            if (null === $value || false !== strpos($field, '*')) {
                continue;
            }

            if (is_array($value) && isset($value['any']) && isset($value['value'])) {
                $this->setDefaultData($field, $value['value'], $newData, (bool)$value['any']);
            } else {
                $this->setDefaultData($field, $value, $newData);
            }
        }

        return $newData->toArray();
    }

    /**
     * Applying default settings to data
     *
     * @param string                                   $field    Name of the data field to be processed
     * @param callable|Closure|DefaultInterface|mixed  $callback The default value or an anonymous function that returns the default value which will
     * @param ValidateCollection                       $data     Data to be processed
     * @param bool                                     $any      Whether to handle arbitrary values, default only handle values that are not null
     */
    private function setDefaultData(string $field, $callback, ValidateCollection $data, bool $any = false)
    {
        $isEmpty = function ($value) {
            return null === $value || [] === $value || '' === $value;
        };
        $value = $data->get($field);
        if ($isEmpty($value) || true === $any) {
            if (is_callable($callback)) {
                $value = call_user_func($callback, $value, $field, $this->checkData);
            } elseif ((is_string($callback) || is_object($callback)) && class_exists($callback) && is_subclass_of($callback, DefaultInterface::class)) {
                /** @var DefaultInterface $default */
                $default = new $callback();
                $value   = $default->handle($value, $field, $this->checkData);
            } elseif (is_string($callback) && method_exists($this, 'default' . ucfirst($callback))) {
                $value = call_user_func([$this, 'default' . ucfirst($callback)], $value, $field, $this->checkData);
            } else {
                $value = $callback;
            }
        }
        $data->set($field, $value);
    }

    /**
     * Initialization validate
     */
    private function init()
    {
        $this->events         = [];
        $this->afters         = [];
        $this->befores        = [];
        $this->defaults       = [];
        $this->filters        = [];
        $this->validatedData  = [];
        $this->validateFields = [];
        $this->eventPriority  = true;
    }

    /**
     * Set the message provider for the validator.
     *
     * @param MessageProviderInterface|string|callable $messageProvider
     * @return $this
     * @throws ValidateException
     */
    public function setMessageProvider($messageProvider): RuleManager
    {
        if (is_string($messageProvider) && is_subclass_of($messageProvider, MessageProviderInterface::class)) {
            $this->messageProvider = new $messageProvider();
        } elseif (is_object($messageProvider) && is_subclass_of($messageProvider, MessageProviderInterface::class)) {
            $this->messageProvider = $messageProvider;
        } elseif (is_callable($messageProvider)) {
            $messageProvider = call_user_func($messageProvider);
            $this->setMessageProvider($messageProvider);
            return $this;
        } else {
            throw new ValidateRuntimeException('The provided message processor needs to implement the MessageProviderInterface interface');
        }

        return $this;
    }

    /**
     * Get the message provider for the validator.
     *
     * @return MessageProviderInterface
     */
    public function getMessageProvider(): MessageProviderInterface
    {
        if (empty($this->messageProvider)) {
            $this->messageProvider = new MessageProvider();
        }

        $messageProvider = $this->messageProvider;
        $messageProvider->setMessage($this->message);
        $messageProvider->setCustomAttributes($this->customAttributes);
        $messageProvider->setData($this->checkData);
        return $messageProvider;
    }

    /**
     * Add Event
     *
     * @param $handlers
     */
    private function addEvent($handlers)
    {
        $this->addCallback(0, $handlers);
    }

    /**
     * Methods to be executed before adding validation
     *
     * @param $callback
     */
    private function addBefore($callback)
    {
        $this->addCallback(1, $callback);
    }

    /**
     * Add the method that needs to be executed after verification
     *
     * @param $callback
     */
    private function addAfter($callback)
    {
        $this->addCallback(2, $callback);
    }

    /**
     * Add method
     *
     * @param int $intType 0 event 1 before 2 after
     * @param $callback
     */
    private function addCallback(int $intType, $callback)
    {
        switch ($intType) {
            case 0:
                $type = 'events';
                break;
            case 1:
                $type = 'befores';
                break;
            case 2:
                $type = 'afters';
                break;
            default:
                throw new LogicException('Type Error');
        }

        if (is_string($callback)) {
            $this->$type[] = [$callback, []];
        } else {
            foreach ($callback as $classOrMethod => $param) {
                if (is_int($classOrMethod)) {
                    $this->$type[] = [$param, []];
                } elseif (is_string($classOrMethod)) {
                    if (is_array($param)) {
                        $this->$type[] = [$classOrMethod, $param];
                    } else {
                        $this->$type[] = [$classOrMethod, [$param]];
                    }
                }
            }
        }
    }
}
