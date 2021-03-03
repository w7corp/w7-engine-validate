<?php

/**
 * WeEngine System
 *
 * (c) We7Team 2021 <https://www.w7.cc>
 *
 * This is not a free software
 * Using it under the license terms
 * visited https://www.w7.cc for more details
 */

namespace W7\Validate\Support\Event;

use Psr\Http\Message\RequestInterface;

class ValidateResult
{
    protected array $data;

    protected RequestInterface $request;
    
    public function __construct(array $data, RequestInterface $request)
    {
        $this->data    = $data;
        $this->request = $request;
    }
    
    public function getData(): array
    {
        return $this->data;
    }
    
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
