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

namespace W7\Validate\Exception;

use Exception;
use Throwable;

class ValidateException extends Exception
{
	/** @var array  */
	protected $data = [];

	public function __construct($message = '', $code = 0, array $data = [], Throwable $previous = null)
	{
		$this->data = $data;
		parent::__construct($message, $code, $previous);
	}

	public function getData()
	{
		return $this->data;
	}
}
