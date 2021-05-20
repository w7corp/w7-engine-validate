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

abstract class ValidateEventAbstract implements ValidateEventInterface
{
    /**
     * Current validation scene name
     * @var ?string
     */
    public $sceneName;

    /**
     * Current validated data
     * @var array
     */
    public $data;

    /**
     * Error message
     * @var string
     */
    public $message;

    /** @inheritDoc */
    public function beforeValidate(): bool
    {
        return true;
    }

    /** @inheritDoc */
    public function afterValidate(): bool
    {
        return true;
    }
}
