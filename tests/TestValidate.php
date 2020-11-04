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

namespace W7\Tests;

use W7\Tests\Material\TestBaseValidate;
use W7\Tests\Material\TestLoginValidate;
use W7\Validate\Exception\ValidateException;

class TestValidate extends TestBaseValidate
{
	public function testNotScene()
	{
		$v = new TestLoginValidate();
		$this->expectException(ValidateException::class);
		$v->check([]);
	}
}
