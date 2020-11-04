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

namespace W7\Validate\Support\Rule;

use Illuminate\Contracts\Validation\Rule;

abstract class BaseRule implements Rule
{
	protected $message = '';
	
	public function setMessage(string $message)
	{
		$this->message = $message;
		return $this;
	}
	
	public function getMessage()
	{
		return $this->message;
	}
	
	public function message()
	{
		return $this->getMessage();
	}
}
