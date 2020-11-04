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

namespace W7\Tests\Material;

use W7\Validate\Validate;

class TestLoginValidate extends Validate
{
	protected $rule = [
		'user' => 'required|chsAlphaNum',
		'pass' => 'required|alphaNum',
		'mail' => 'required|email'
	];
	
	protected $message = [
		'user.required'    => '用户名必须填写',
		'user.chsAlphaNum' => '用户名的值只能具有中文，字母，数字',
		'pass.required'    => '密码必须填写',
		'pass.alphaNum'    => '密码的值只能具有英文字母，数字',
		'mail.required'    => '邮箱必须填写',
		'mail.email'       => '邮箱格式错误',
	];
}
