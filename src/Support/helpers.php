<?php

if (!function_exists('validate_collect')) {
	/**
	 * @param null $value
	 * @return \W7\Validate\Support\Storage\ValidateCollection
	 */
	function validate_collect($value = null)
	{
		return new \W7\Validate\Support\Storage\ValidateCollection($value);
	}
}

if (!function_exists('get_validate_data')) {
	/**
	 * @param \Psr\Http\Message\ServerRequestInterface $request
	 * @return \W7\Validate\Support\Storage\ValidateCollection
	 */
	function get_validate_data(\Psr\Http\Message\ServerRequestInterface $request)
	{
		return validate_collect($request->getAttribute('validate'));
	}
}
