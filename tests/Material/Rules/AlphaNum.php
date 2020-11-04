<?php


namespace W7\Tests\Material\Rules;


use W7\Validate\Support\Rule\BaseRule;

class AlphaNum extends BaseRule
{
	protected $message = ':attribute的值只能具有英文字母，数字';
	
	public function passes($attribute, $value)
	{
		return is_scalar($value) && 1 === preg_match('/^[A-Za-z0-9]+$/', (string)$value);
	}
}