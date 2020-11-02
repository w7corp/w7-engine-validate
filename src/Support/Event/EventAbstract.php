<?php

namespace W7\Validate\Support\Event;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

abstract class EventAbstract implements EventInterface
{
	public function process(array $data, ServerRequestInterface $request, Closure $next)
	{
		return $next($data, $request);
	}
}
