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
	 * 当前验证场景
	 * @var string
	 */
	protected $sceneName;

	/**
	 * 当前控制器名称
	 * @var string
	 */
	protected $controller;

	/**
	 * 当前操作方法
	 * @var string
	 */
	protected $method;

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

	final public function setSceneName(?string $sceneName)
	{
		$this->sceneName = $sceneName;
		return $this;
	}

	final public function setController(string $controller)
	{
		$this->controller = $controller;
		return $this;
	}

	final public function setMethod(string $method)
	{
		$this->method = $method;
		return $this;
	}
}
