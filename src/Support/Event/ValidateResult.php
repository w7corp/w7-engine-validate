<?php

namespace W7\Validate\Support\Event;

use Psr\Http\Message\RequestInterface;

class ValidateResult
{
	/** @var array */
	protected $data;
	
	/** @var RequestInterface */
	protected $request;
	
	public function __construct(array $data, RequestInterface $request)
	{
		$this->data    = $data;
		$this->request = $request;
	}
	
	public function getData()
	{
		return $this->data;
	}
	
	public function getRequest()
	{
		return $this->request;
	}
}
