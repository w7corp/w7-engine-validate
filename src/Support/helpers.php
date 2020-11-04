<?php

use Psr\Http\Message\ServerRequestInterface;
use W7\Core\Facades\Context;
use W7\Validate\Support\Storage\ValidateCollection;

if (!function_exists('validate_collect')) {
	/**
	 * 数值转为验证器集合ValidateCollection类型
	 * @param null $value
	 * @return ValidateCollection
	 */
	function validate_collect($value = null)
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
	function get_validate_data(ServerRequestInterface $request = null)
	{
		if (null === $request) {
			$request = Context::getRequest();
		}
		return validate_collect($request->getAttribute('validate'));
	}
}
