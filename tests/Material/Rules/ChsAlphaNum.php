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

namespace W7\Tests\Material\Rules;

use W7\Validate\Support\Rule\BaseRule;

class ChsAlphaNum extends BaseRule
{
	protected $message = ':attribute的值只能具有中文，字母，数字';
	
	public function passes($attribute, $value)
	{
		return is_scalar($value) && 1 === preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', (string)$value);
	}
}
