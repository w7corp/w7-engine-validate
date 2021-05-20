<?php


namespace W7\Tests\Material\Event;


use W7\Validate\Support\Event\ValidateEventAbstract;

class CheckIsChs extends ValidateEventAbstract
{
	protected $field;

	public $message = "不是中文";

	public function __construct($field)
	{
		$this->field = $field;
	}

	public function afterValidate(): bool
	{
		return is_scalar($this->data[$this->field]) && 1 === preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', (string)$this->data[$this->field]);
	}
}