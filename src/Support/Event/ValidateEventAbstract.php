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

abstract class ValidateEventAbstract implements ValidateEventInterface
{
    /**
     * Current validation scenarios
     * @var ?string
     */
    protected $sceneName;

    /** @inheritDoc */
    public function beforeValidate(array $data, Closure $next)
    {
        return $next($data);
    }

    /** @inheritDoc */
    public function afterValidate(array $data, Closure $next)
    {
        return $next($data);
    }

    /**
     * Write the name of the current validation scenario
     *
     * @param string|null $sceneName
     * @return $this
     */
    final public function setSceneName(?string $sceneName): ValidateEventAbstract
    {
        $this->sceneName = $sceneName;
        return $this;
    }
}
