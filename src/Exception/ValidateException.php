<?php

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
