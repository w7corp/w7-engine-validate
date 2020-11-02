<?php

namespace W7\Validate\Support\Event;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

interface EventInterface
{
	public function process(array $data,ServerRequestInterface $request, Closure $next);
}
