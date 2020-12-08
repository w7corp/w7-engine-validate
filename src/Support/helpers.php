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

use Psr\Http\Message\ServerRequestInterface;
use W7\Facade\Context;
use W7\Validate\Support\Storage\ValidateCollection;

if (!function_exists('validate_collect')) {
	/**
	 * 数值转为验证器集合ValidateCollection类型
	 * @param null $value
	 * @return ValidateCollection
	 */
	function validate_collect($value = null): ValidateCollection
    {
		return new ValidateCollection($value);
	}
}

if (!function_exists('get_validate_data')) {
	/**
	 * 获取验证后的结果
	 * @param ServerRequestInterface|null $request 请求示例，如果为null，则自动从上下文中获取
	 * @return ValidateCollection 返回验证器集合ValidateCollection类型
	 */
	function get_validate_data(ServerRequestInterface $request = null): ValidateCollection
    {
		if (null === $request) {
			$request = Context::getRequest();
		}
		return validate_collect($request->getAttribute('validate'));
	}
}
