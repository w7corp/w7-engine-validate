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

class SetDefault extends ValidateEventAbstract
{
    protected array $default;

    public function __construct(array $default)
    {
        $this->default = $default;
    }

    public function beforeValidate(array $data, Closure $next)
    {
        $data = array_merge($this->default, $data);
        return $next($data);
    }
}
