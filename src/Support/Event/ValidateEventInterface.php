<?php

namespace W7\Validate\Support\Event;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

interface ValidateEventInterface
{
	public function beforeValidate(array $data, ServerRequestInterface $request, Closure $next);
	public function afterValidate(array $data, ServerRequestInterface $request, Closure $next);
}
