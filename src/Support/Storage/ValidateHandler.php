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
use Psr\Http\Message\RequestInterface;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Support\Event\ValidateResult;

class ValidateHandler
{
    protected array $handlers = [];

    protected array $data = [];

    protected RequestInterface $request;

    protected ?string $sceneName = null;

    public function __construct(array $data, array $handlers, RequestInterface $request, string $sceneName = null)
    {
        $this->data      = $data;
        $this->request   = $request;
        $this->handlers  = $handlers;
        $this->sceneName = $sceneName;
    }
    
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($data, $request) use ($stack, $pipe) {
                return $pipe($data, $request, $stack);
            };
        };
    }
    
    protected function pipes(string $method): array
    {
        return array_map(function ($middleware) use ($method) {
            return function ($data, $request, $next) use ($middleware, $method) {
                list($callback, $param) = $middleware;
                if (class_exists($callback) && is_subclass_of($callback, ValidateEventAbstract::class)) {
                    /** @var ValidateEventAbstract $handler */
                    $handler = new $callback(...$param);
                    $handler->setSceneName($this->sceneName);
                    return call_user_func([$handler, $method], $data, $request, $next);
                } else {
                    throw new ValidateException('Event error or nonexistence');
                }
            };
        }, $this->handlers);
    }
    
    protected function destination(): Closure
    {
        return function ($data, $request) {
            return new ValidateResult($data, $request);
        };
    }
    
    public function handle(string $method)
    {
        $destination = $this->destination();
        $pipeline    = array_reduce(
            array_reverse($this->pipes($method)),
            $this->carry(),
            function ($data, $request) use ($destination) {
                return $destination($data, $request);
            }
        );
        
        return $pipeline($this->data, $this->request);
    }
}
