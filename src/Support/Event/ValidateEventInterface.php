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

use Closure;

interface ValidateEventInterface
{
    /**
     * Methods implemented prior to validation
     *
     * @param array $data
     * @param Closure $next
     * @return mixed
     */
    public function beforeValidate(array $data, Closure $next);

    /**
     * Methods implemented after validation
     * @param array $data Validated data
     * @param Closure $next
     * @return mixed
     */
    public function afterValidate(array $data, Closure $next);
}
