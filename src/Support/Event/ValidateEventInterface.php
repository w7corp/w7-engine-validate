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

interface ValidateEventInterface
{
    /**
     * Methods implemented prior to validation
     *
     * @return bool
     */
    public function beforeValidate(): bool;

    /**
     * Methods implemented after validation
     *
     * @return bool
     */
    public function afterValidate(): bool;
}
