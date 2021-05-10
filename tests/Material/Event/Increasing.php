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

namespace W7\Tests\Material\Event;

use Closure;
use W7\Validate\Support\Event\ValidateEventAbstract;

class Increasing extends ValidateEventAbstract
{
    protected $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    public function afterValidate(array $data, Closure $next)
    {
        $data[$this->field] ++;
        return $next($data);
    }
}
