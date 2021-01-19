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

class AlphaNum extends BaseRule
{
	protected string $message = ':attribute的值只能具有英文字母，数字';
	
	public function passes($attribute, $value): bool
	{
		return is_scalar($value) && 1 === preg_match('/^[A-Za-z0-9]+$/', (string)$value);
	}
}
