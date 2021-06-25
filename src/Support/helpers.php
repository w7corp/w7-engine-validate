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

use W7\Validate\Support\Storage\ValidateCollection;

if (!function_exists('validate_collect')) {
    /**
     * Create a ValidateCollection from the given value.
     * @param null $value
     * @return ValidateCollection
     */
    function validate_collect($value = null): ValidateCollection
    {
        return new ValidateCollection($value);
    }
}
