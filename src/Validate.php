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

use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use LogicException;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Concerns\MessageProviderInterface;
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Support\MessageProvider;
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

    protected $filter = [];

    protected $default = [];

    /**
     * Event Priority
     * @var bool
     */
    private $eventPriority = true;

    /**
     * Validator event handling class
     * @var array
     */
    private $events = [];

    /**
     * Methods to be executed before validation
     * @var array
     */
    private $befores = [];

    /**
     * Methods to be executed after validation
     * @var array
     */
    private $afters = [];

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
            $rule = $this->getCheckRules($this->getInitialRules());

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
            $scene = new ValidateScene($this->rule);
            call_user_func([$this, 'scene' . ucfirst($sceneName)], $scene);
            $this->event         = array_merge($this->event, $scene->events);
            $this->afters        = array_merge($this->afters, $scene->afters);
            $this->befores       = array_merge($this->befores, $scene->befores);
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

            // Determine if other authentication scenarios are specified for the authentication scenario
            if (isset($sceneRule['use']) && !empty($sceneRule['use'])) {
                $use = $sceneRule['use'];
                if ($use === $sceneName) {
                    throw new LogicException('The scene used cannot be the same as the current scene.');
                }
                unset($sceneRule['use']);
                // If the specified `use` is a method
                if (method_exists($this, 'use' . ucfirst($use))) {
                    // Pre-validation, where the values to be passed to the closure are validated according to the specified rules
                    $data = [];
                    if (!empty($sceneRule)) {
                        $randScene = md5(rand(1, 1000000) . time());
                        $data      = (clone $this)->setScene(
                            [$randScene => $sceneRule]
                        )->scene($randScene)->check($this->checkData);
                    }

                    $use = call_user_func([$this, 'use' . ucfirst($use)], $data);
                    if (is_array($use)) {
                        return array_intersect_key($this->rule, array_flip($use));
                    }
                }
                return $this->getInitialRules($use);
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
                if (($result = call_user_func([$handler, $method])) !== true) {
                    $message = $handler->message;
                    if (isset($this->message[$message])) {
                        $message = $this->getMessageProvider()->handleMessage($this->message[$message]);
                    }
                    throw new ValidateException($message, 403);
                }
            } else {
                throw new ValidateException('Event error or nonexistence');
            }
        }
    }

    /**
     * Initialization validate
     */
    private function init()
    {
        $this->handlers      = [];
        $this->afters        = [];
        $this->befores       = [];
        $this->eventPriority = true;
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
            throw new ValidateException('The provided message processor needs to implement the MessageProviderInterface interface');
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
