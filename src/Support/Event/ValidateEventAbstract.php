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

namespace W7\Validate\Support\Event;

use Closure;
use Psr\Http\Message\ServerRequestInterface;

abstract class ValidateEventAbstract implements ValidateEventInterface
{
	/**
	 * 场景验证前
	 * @param array $data 用户输入的数据
	 * @param ServerRequestInterface $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function beforeValidate(array $data, ServerRequestInterface $request, Closure $next)
	{
		return $next($data, $request);
	}
	
	/**
	 * 场景验证后
	 * @param array $data 验证后的数据
	 * @param ServerRequestInterface $request
	 * @param Closure $next
	 * @return mixed
	 */
	public function afterValidate(array $data, ServerRequestInterface $request, Closure $next)
	{
		return $next($data, $request);
	}
}
