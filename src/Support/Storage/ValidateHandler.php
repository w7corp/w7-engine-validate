<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2019 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Validate\Support\Storage;

use Psr\Http\Message\RequestInterface;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Event\ValidateEventAbstract;
use W7\Validate\Support\Event\ValidateResult;

class ValidateHandler
{
	/** @var array  */
	protected $handlers = [];
	
	/** @var array */
	protected $data = [];
	
	/** @var RequestInterface */
	protected $request;
	
	public function __construct(array $data, array $handlers, RequestInterface $request)
	{
		$this->data     = $data;
		$this->request  = $request;
		$this->handlers = $handlers;
	}
	
	protected function carry()
	{
		return function ($stack, $pipe) {
			return function ($data, $request) use ($stack, $pipe) {
				return $pipe($data, $request, $stack);
			};
		};
	}
	
	protected function pipes(string $method)
	{
		return array_map(function ($middleware) use ($method) {
			return function ($data, $request, $next) use ($middleware,$method) {
				list($callback, $param) = $middleware;
				if (class_exists($callback) && is_subclass_of($callback, ValidateEventAbstract::class)) {
					return call_user_func([new $callback(...$param), $method], $data, $request, $next);
				} else {
					throw new ValidateException('Event error or nonexistence');
				}
			};
		}, $this->handlers);
	}
	
	protected function destination()
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
