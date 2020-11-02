<?php

namespace W7\Validate\Support\Storage;

use Psr\Http\Message\RequestInterface;
use W7\Validate\Exception\ValidateException;
use W7\Validate\Support\Event\EventAbstract;
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
	
	protected function pipes()
	{
		return array_map(function ($middleware) {
			return function ($data, $request, $next) use ($middleware) {
				list($callback, $param) = $middleware;
				if (class_exists($callback) && is_subclass_of($callback, EventAbstract::class)) {
					return call_user_func([new $callback(...$param),'process'], $data, $request, $next);
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
	
	public function handle()
	{
		$destination = $this->destination();
		$pipeline    = array_reduce(
			array_reverse($this->pipes()),
			$this->carry(),
			function ($data, $request) use ($destination) {
				return $destination($data, $request);
			}
		);
		
		return $pipeline($this->data, $this->request);
	}
}
