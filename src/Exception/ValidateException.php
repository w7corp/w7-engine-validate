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

namespace W7\Validate\Exception;

use Exception;
use Throwable;

class ValidateException extends Exception
{
    protected $attribute;

    public function __construct($message = '', $code = 0, string $attribute = '', Throwable $previous = null)
    {
        $this->attribute = $attribute;
        parent::__construct($message, $code, $previous);
    }

    public function getAttribute(): string
    {
        return $this->attribute;
    }
}
