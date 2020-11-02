<?php


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
