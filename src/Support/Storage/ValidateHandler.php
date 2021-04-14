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

namespace W7\Validate\Support\Storage;

use Closure;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Event\ValidateEventAbstract;

class ValidateHandler
{
    protected array $handlers = [];

    protected array $data = [];

    protected ?string $sceneName = null;

    public function __construct(array $data, array $handlers, string $sceneName = null)
    {
        $this->data      = $data;
        $this->handlers  = $handlers;
        $this->sceneName = $sceneName;
    }
    
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($data) use ($stack, $pipe) {
                return $pipe($data, $stack);
            };
        };
    }
    
    protected function pipes(string $method): array
    {
        return array_map(function ($middleware) use ($method) {
            return function ($data, $next) use ($middleware, $method) {
                list($callback, $param) = $middleware;
                if (class_exists($callback) && is_subclass_of($callback, ValidateEventAbstract::class)) {
                    /** @var ValidateEventAbstract $handler */
                    $handler = new $callback(...$param);
                    $handler->setSceneName($this->sceneName);
                    return call_user_func([$handler, $method], $data, $next);
                } else {
                    throw new ValidateException('Event error or nonexistence');
                }
            };
        }, $this->handlers);
    }
    
    protected function destination(): Closure
    {
        return function ($data) {
            return $data;
        };
    }
    
    public function handle(string $method)
    {
        $destination = $this->destination();
        $pipeline    = array_reduce(
            array_reverse($this->pipes($method)),
            $this->carry(),
            function ($data) use ($destination) {
                return $destination($data);
            }
        );
        
        return $pipeline($this->data);
    }
}
