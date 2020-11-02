<?php

if (!function_exists('validate_collect')) {
	function validate_collect($value = null)
	{
		return new \W7\Validate\Support\Storage\ValidateCollection($value);
	}
}
